<?php
/**
 * MethodDB
 * ========
 * ORM using method
 *
 * This file is part of the MethodDB.
 * 
 * Wallace Rio <wallrio@gmail.com>
 * 
 */

class MethodDB{
	
	private $status;
	private $config;

	function __construct(){}

	/**
	 * Configuration initial
	 * @param  array|null $options [description]
	 * @return null
	 */
	public function config(array $options = null){
		if($options == null) return $this->config;
		$this->config = $this->config;
		foreach ($options as $key => $value) {
			$this->config[$key] = $value;
		}
	}

	/**
	 * Connect to database
	 * @return [class] return a class(Entity) to comunicate with driver
	 */
	public function connect(){
		try{
			return $this->connectEffective();
		}catch(Exception $e){
			echo '<strong>MethodDB Message:</strong> ' .$e->getMessage().'';	
			return new MethodDBEntity($this->config);				
		}
	}
	public function connectEffective(){

		if(!isset($this->config['driver'])) throw new Exception("driver missing");

		if(gettype($this->config['driver'])=='string'){
			$driveName = 'methoddb_driver_'.$this->config['driver'];
			
			$driverDir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'driver'.DIRECTORY_SEPARATOR.$driveName.".php";
			require_once $driverDir;
			
			$this->config['driveObject'] = new $driveName();
		}else{
			$this->config['driveObject'] = $this->config['driver'];
		}
		return new MethodDBEntity($this->config);
	}

}	