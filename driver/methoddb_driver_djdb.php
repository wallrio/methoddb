<?php
/**
 * 
 * MethodDB DJDB driver v1.2
 * Database based in Directory structure and JSON
 * 
 * Autor: Wallace Rio <wallrio@gmail.com>
 * 
 */

class methoddb_driver_djdb implements MethodDBDRIVERS{
	
	private $baseDir = null;
	private $instance;

	/**
	 * [connect description]
	 * @param  array  $config [description]
	 * @return [type]         [description]
	 */
	public function connect(array $config){

		if(!isset($config['host'])) throw new Exception("host missing");
		if(!isset($config['base'])) throw new Exception("base missing");
		
		$this->config = $config;
		$host = $this->config['host'];
		$base = $this->config['base'];
		$baseDir = $host.DIRECTORY_SEPARATOR.$base;
		$this->baseDir = $baseDir;		
	}

	/**
	 * [whereApply description]
	 * @param  [type] $content   [description]
	 * @param  [type] $condition [description]
	 * @return [type]          
	 * 
	 *  OBS: no support sub parents char.
	 *  () > support
	 *  (()) > not support
	 *  
	 *  example:
	 *  ( val1 == val2 ) > support
	 *  ( (val1==val2) && val1 == val3 ) > not support
	 */
	public function whereApply($content = null,$condition = null){
		

		$conditionSplit = preg_split('/ (and|or) /i', $condition,null,PREG_SPLIT_DELIM_CAPTURE);
					
					
		$query ='';
		foreach ($conditionSplit as $key => $value) {
			if(strtolower($value) == 'and'){
				$query .= ' && ';
				continue;
			}elseif(strtolower($value) == 'or'){
				$query .= ' || ';
				continue;
			}

			$userParentClose = false;
			$unitConditionSplit = preg_split('/ *?(!=|=|like|<>) *?/i', $value,null,PREG_SPLIT_DELIM_CAPTURE);

			

			$condKey = $unitConditionSplit[0];
			$condOperator = $unitConditionSplit[1];
			$condVal = $unitConditionSplit[2];
			
			$condVal = trim($condVal);
			$condKey = trim($condKey);

			$wrapperVal = (substr($condVal,0,1) == "'" || substr($condVal,0,1) == '"')?substr($condVal,0,1):'';

			if(strpos($condKey, '(')!== false){
				$condKey = str_replace('(', '', $condKey);
				$condVal = "(".$condVal;				
			}

			if(strpos($condVal, ')')!== false){
				$condVal = str_replace(')', '', $condVal);		
				$userParentClose = true;	
			}

			if(strtolower($condOperator) == '='){
				$operator = ' === ';				
			}elseif(strtolower($condOperator) == '=='){
				$operator = ' != ';				
			}elseif(strtolower($condOperator) == '<>'){
				$operator = ' <> ';				
			}elseif(strtolower($condOperator) == '<='){
				$operator = ' <= ';	
			}elseif(strtolower($condOperator) == '>='){
				$operator = ' >= ';				
			}elseif(strtolower($condOperator) == 'like'){
				$operator = ' = ';				
			}elseif(strtolower($condOperator) == 'soundex'){
				$operator = ' === ';				
			}



			$condKey = str_replace('.', '->', $condKey);

			
			
			eval('$condVal2Pre = isset($content->'.$condKey.')?$content->'.$condKey.':null;');
			

			if(gettype($condVal2Pre) == 'integer'){
				eval('$condVal2 = $content->'.$condKey.';');			
			}elseif(gettype($condVal2Pre) == 'string'){
				eval('$condVal2 = $wrapperVal.$content->'.$condKey.'.$wrapperVal;');				
			}else{
				return false;
			}


			if($userParentClose == true){				
				$condVal2 = $condVal2.")";				
			}

			
			if(strtolower($condOperator) == 'like' || strtolower($condOperator) == 'soundex'){
				$query .= "soundex(".$condVal.")" ."===". "soundex(".$condVal2.")";		
			}else{
				$query .= $condVal .$operator. $condVal2;		
			}
			
			
		}
		
		
		eval('$return = '.$query.';');
		
		return $return;
	}


	/**
	 * [select description]
	 * @param  [type] $tableName  [description]
	 * @param  [type] $parameters [description]
	 * @return [type]             [description]
	 */
	public function select($tableName, $parameters){
		
		

		$where = isset($parameters[0]['where'])?$parameters[0]['where']:null;
		$limit = isset($parameters[0]['limit'])?$parameters[0]['limit']:null;
		$order = isset($parameters[0]['order'])?$parameters[0]['order']:null;

		if(strpos($limit, ',')!==false){
			$limitArray = explode(',', $limit);
			$limitStart = $limitArray[0];
			$limitEnd = $limitArray[1];
		}else{
			$limitStart = 0;
			$limitEnd = $limit;
		}
				
		$registerDir = $this->baseDir.DIRECTORY_SEPARATOR.$tableName;
		$list = $this->scanDir($registerDir);
		
		$index = 0;
		$foundList = Array();
		foreach ($list as $key => $value) {
			$filename = $registerDir.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR."methoddb_data.json";
			$contentJSON = file_get_contents($filename);
			$content = json_decode($contentJSON);
			
			if( ($where !=null && $this->whereApply($content,$where)) ||  $where ==null ){			
				if(($index) >= $limitStart  && ($index ) < ($limitStart + $limitEnd) || $limit == null)
				$foundList[$value] = $content;
			}
			$index++;
		}

		if(count($foundList)<1)
			return false;

		return (object) $foundList;
	}

	/**
	 * [insert description]
	 * @param  [type] $tableName  [description]
	 * @param  [type] $parameters [description]
	 * @return [type]             [description]
	 */
	public function insert($tableName, $parameters){
		$registerDir = $this->baseDir.DIRECTORY_SEPARATOR.$tableName;
	
		if(isset($parameters->id))
			$userid = $parameters->id;
		else
			$userid = md5(uniqid());
		
		$data = json_encode($parameters);

		$userDir = $registerDir.DIRECTORY_SEPARATOR.$userid.DIRECTORY_SEPARATOR;
		$userFile = $userDir.'methoddb_data.json';


		if(!file_exists($userDir))
			mkdir($userDir,0777,true);

		if(file_put_contents($userFile, $data))
			return true;

		return false;		
	}

	/**
	 * [update description]
	 * @param  [type] $tableName  [description]
	 * @param  [type] $parameters [description]
	 * @return [type]             [description]
	 */
	public function update($tableName, $parameters){		
		$where = isset($parameters['_filter_']['where'])?$parameters['_filter_']['where']:null;
		unset($parameters['_filter_']);
		$parameters = array_values($parameters);
		$parameters = $parameters[0];
		
		$registerDir = $this->baseDir.DIRECTORY_SEPARATOR.$tableName;
		$list = $this->scanDir($registerDir);
				
		$index = 0;
		$foundList = Array();
		foreach ($list as $key => $value) {
			$filename = $registerDir.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR."methoddb_data.json";
			$contentJSON = file_get_contents($filename);
			$content = json_decode($contentJSON);
			
			if( ($where !=null && $this->whereApply($content,$where)) ||  $where ==null ){		
				$foundList[$value] = $content;
				foreach ($parameters as $key2 => $value2) {
					$content->$key2 = $value2;
				}
				file_put_contents($filename,json_encode($content));				
			}
			$index++;
		}
		
		
	}

	/**
	 * [delete description]
	 * @param  [type] $tableName  [description]
	 * @param  [type] $parameters [description]
	 * @return [type]             [description]
	 */
	public function delete($tableName, $parameters){
		$found = false;
		$where = isset($parameters['where'])?$parameters['where']:'';
		
		$registerDir = $this->baseDir.DIRECTORY_SEPARATOR.$tableName;
		$list = $this->scanDir($registerDir);
		
		$index = 0;
		$foundList = Array();
		foreach ($list as $key => $value) {
			$dirItem = $registerDir.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR;
			$filename = $dirItem."methoddb_data.json";
			$contentJSON = file_get_contents($filename);
			$content = json_decode($contentJSON);
			
			if( ($where !=null && $this->whereApply($content,$where)) ||  $where ==null ){		
				$found = true;
				$foundList[$value] = $content;
				foreach ($parameters as $key2 => $value2) {
					$content->$key2 = $value2;
				}
				$this->rmdir($dirItem);						
			}
			$index++;
		}

		return $found;
	}

	/**
	 * [tables description]
	 * @return [type] [description]
	 */
	public function tables(){
		return $this->scanDir($this->baseDir);
	}


	/**
	 * [scanDir description]
	 * @param  [type] $dir [description]
	 * @return [type]      [description]
	 */
	private function scanDir($dir = null){
		if($dir == null)return false;
		$dirList = scandir($dir);
		foreach ($dirList as $key => $value) {
			if($value=='.' || $value=='..')
				unset($dirList[$key]);
		}
		$dirList = array_values($dirList);
		return $dirList;
	}

	/**
	 * [rmdir description]
	 * @param  [type] $dir [description]
	 * @return [type]      [description]
	 */
	public static function rmdir($dir) { 
       if (is_dir($dir)) { 
         $objects = scandir($dir); 
         foreach ($objects as $object) { 
           if ($object != "." && $object != "..") { 
             if (filetype($dir."/".$object) == "dir") self::rmdir($dir."/".$object); else unlink($dir."/".$object); 
           } 
         } 
         reset($objects); 
         rmdir($dir); 
       } 
     }
}