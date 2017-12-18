<?php
/**
 * MethodDB MYSQL driver v1.1
 * ========
 * ORM using method
 *
 * This file is part of the MethodDB.
 * 
 * Wallace Rio <wallrio@gmail.com>
 * 
 */


class methoddb_driver_mysql implements MethodDBDRIVERS {

	private $instance;

	public function connect(array $config){
		$host = isset($config['host'])?$config['host']:null;
		$username = isset($config['username'])?$config['username']:null;
		$password = isset($config['password'])?$config['password']:null;
		$base = isset($config['base'])?$config['base']:null;	

		$this->instance = new PDO('mysql:host='.$host.';dbname='.$base, $username, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	}

	public function insert($tableName,$array){

		$string = '';
		$val = '';
		$index = 0;
		foreach ($array as $key => $value) {
			if($index>0){
				$string .= ',';
				$val .= ',';
			}
			$string .= $key;
			$val .= ':'.$key;
			$index++;
		}
		
		$sql = "INSERT INTO ".$tableName."(".$string.") VALUES(".$val.")";
	
		$db = $this->instance;
		$stmt = $db->prepare( $sql );
		foreach ($array as $key => $value) {	
			$arrayExec[":".$key] = $value;		
		}
		$result = $stmt->execute($arrayExec);
		
	}


	public function update($tableName,$array){

		$where = isset($array['_filter_']['where'])?' WHERE '.$array['_filter_']['where']:'';
		unset($array['_filter_']);
		$array = array_values($array);
		$array = $array[0];

		$string = '';
		$val = '';
		$index = 0;
		foreach ($array as $key => $value) {
			if($index>0){
				$string .= ',';
				$val .= ',';
			}	
			$val .= $key.'=:'.$key;
			$index++;
		}
		
		$sql = "UPDATE ".$tableName." SET ".$val." ".$where;

		$db = $this->instance;
		$stmt = $db->prepare( $sql );
		foreach ($array as $key => $value) {	
			$arrayExec[":".$key] = $value;		
		}

		$result = $stmt->execute($arrayExec);
		
	}


	public function delete($tableName = null,$parameters = null){		
	
		$where = isset($parameters['where'])?' WHERE '.$parameters['where']:'';
	
		$sql = "DELETE FROM ".$tableName." ".$where;
		
		$db = $this->instance;
		$result = $db->query( $sql );		
		$rows = (object) $result->fetchAll( PDO::FETCH_OBJ );
		return  $rows;
	}


	

	public function select($tableName = null, $parameters = null){		
		$par0 = isset($parameters[0])?$parameters[0]:array();
		
		$where = isset($par0['where'])?''.$par0['where']:'';
		$limit = isset($par0['limit'])?' LIMIT '.$par0['limit']:'';
		$order = isset($par0['order'])?' ORDER BY '.$par0['order']:'';

		$prePrepare = $this->autoPrepare($where);
		$query = $prePrepare['query'];
		$arrayExec = $prePrepare['array'];
	
		$sql = "SELECT * FROM ".$tableName.( ($prePrepare!==false)?" WHERE ".$query:"");	

		$db = $this->instance;
		$stmt = $db->prepare( $sql );
		
		$result = $stmt->execute($arrayExec);
		$rows = $stmt->fetchAll( PDO::FETCH_OBJ );

		
		if(count($rows)<1)
			return false;

		return  (object)  $rows;
	}


	public function tables(){
		$sql = "show tables";
		$db = $this->instance;
		$result = $db->query( $sql );		
		$rows = $result->fetchAll( PDO::FETCH_ASSOC );

		$tablesList = Array();
		foreach ($rows as $key => $value) {
			foreach ($value as $key2 => $value2) {
				$tablesList[] = $value2;				
			}
		}

		return  $tablesList;
	}



	public function autoPrepare($condition = null){

		if($condition == null){
			return false;
		}

		$array = array();

		$conditionSplit = preg_split('/ (and|or) /i', $condition,null,PREG_SPLIT_DELIM_CAPTURE);
				
		$query ='';
		foreach ($conditionSplit as $key => $value) {
			if(strtolower($value) == 'and'){
				$query .= ' AND ';
				continue;
			}elseif(strtolower($value) == 'or'){
				$query .= ' OR ';
				continue;
			}

			$userParentClose = false;
			$unitConditionSplit = preg_split('/ *?(!=|=|like|<>) *?/i', $value,null,PREG_SPLIT_DELIM_CAPTURE);

			$condKey = $unitConditionSplit[0];
			$condOperator = $unitConditionSplit[1];
			$condVal = $unitConditionSplit[2];
			
			$wrapperVal = (substr($condVal,0,1) == "'" || substr($condVal,0,1) == '"')?substr($condVal,0,1):'';

			if(strpos($condKey, '(')!== false){
				$condKey = str_replace('(', '', $condKey);
				$condVal = "(".$condVal;				
			}

			if(strpos($condVal, ')')!== false){
				$condVal = str_replace(')', '', $condVal);		
				$userParentClose = true;	
			}

			$operator = ' '.$condOperator.' ';				

			if(substr($condVal, 0,1)=='\'' || substr($condVal, 0,1)=='"')
				$condVal = substr($condVal, 1);
			if(substr($condVal, strlen($condVal)-1)=='\'' || substr($condVal, strlen($condVal)-1)=='"')
				$condVal = substr($condVal, 0,strlen($condVal)-1);
			
			$array[":".$condKey] = $condVal;

			$condVal2 = ":".$condKey;
			
			if($userParentClose == true){				
				$condVal2 = $condVal2.")";				
			}
			
			$query .= $condKey .$operator. $condVal2;		
								
		}

		$returns = array('query'=>$query,'array'=>$array);	
		return $returns;
	}


}