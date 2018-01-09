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

class MethodDBEntity{

	public $updateObject = array();
	public $insertObject = array();

	public $__method__;
	public $__drive__;
	public $__target__;

	/**
	 * get tables/rows
	 * @param  [string] $table name of entitie (optional)
	 * @return [array]        list of entities
	 */
	public function entities($table = null){
		if($table != null){
			$this->__target__ = $table;
			return $this;
		}

		$entities = Array();
		foreach ($this->__method__ as $key => $value) {
			$entities[] = $key;
		}
		return $entities;
	}

	/**
	 * save effectively update/delete
	 * @return [boolean]  status of action
	 */
	public function save(){
		$insertObject = $this->insertObject;
		if(count($insertObject)>0)
		foreach ($insertObject as $key => $value) {
			$tableName = $key;
			foreach ($value as $key2 => $value2) {
				$this->__drive__->insert($tableName, $value2);
			}
		}

		$updateObject = $this->updateObject;
		if(count($updateObject)>0)
		foreach ($updateObject as $key => $value) {
			$tableName = $key;
			$this->__drive__->update($tableName,$value);
		}

		$this->insertObject = null;
		$this->updateObject = null;

		return true;
	}


	/**
	 * delete register
	 * @param  [array] $array [description]
	 * @return [type]        [description]
	 */
	public function delete($array){
		$table = $this->__target__;
		$this->__drive__->delete($table,$array);
	}

	/**
	 * [update description]
	 * @param  [type] $where [description]
	 * @return [type]        [description]
	 */
	public function update($where = null){
		$table = $this->__target__;
		if(!isset($this->updateObject[$table]))
			$this->updateObject[$table] = array();

		$this->updateObject[$table]['_filter_'] = $where;
		$coutRow = count($this->updateObject[$table]);
		$this->updateObject[$table][$coutRow] = (object) array();
		return $this->updateObject[$table][$coutRow];
	}

	/**
	 * [insert description]
	 * @return [type] [description]
	 */
	public function insert(){
		$table = $this->__target__;
		if(!isset($this->insertObject[$table]))
			$this->insertObject[$table] = array();

		$coutRow = count($this->insertObject[$table]);
		$this->insertObject[$table][$coutRow] = (object) array();
		return $this->insertObject[$table][$coutRow];
	}

	/**
	 * [__call description]
	 * @param  [type] $func   [description]
	 * @param  [type] $params [description]
	 * @return [type]         [description]
	 */
	function __call($func, $params){
		if($this->__method__[$func])
			return $this->__method__[$func]($func, $params,$this->__drive__);
	}

	/**
	 * [__construct description]
	 * @param array $config [description]
	 */
	function __construct(array $config ){

		if(!isset($config['driver'])) return false;

		$this->__drive__ = $config['driveObject'];
		$config['driveObject']->connect($config);
		$result = $config['driveObject']->tables();


		if( is_array($result) && count($result)>0)
		foreach ($result as $key => $value) {
			$nameTable = $value;
			$this->__method__[$nameTable] = function($methodName,$parameters,$drive){
				$result = $drive->select($methodName,$parameters);
				return  $result;
			};
		}
	}


}
