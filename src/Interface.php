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

/**
 * Contract for news drivers
 */
interface MethodDBDRIVERS{	

	/**
	 * connect with the database and define the parameters initial
	 * @param  array  $config 	Contains the definitions of '$methodDB->config' method
	 * @return null
	 */
	public function connect(array $config);

	/**
	 * select a or multiples register of entitie
	 * @param  [string] 	$tableName  	Name of entitie
	 * @param  array 		$parameters 	Filter to selection
	 * @return array 						list of registers founds
	 *
	 * @example-return: (object) array(
 	 *							"USER_ID"=> (object) array(
	 * 								"username"=>"fulano",
	 * 								"name"=>"Fulano da Silva",
	 * 								"password"=>"123"
 	 *							)
	 * 						);
	 * 
	 */
	public function select($tableName, $parameters);

	/**
	 * insert a register on database
	 * @param  string 	$tableName  	name of entitie
	 * @param  array 	$parameters 	data of register to save on database
	 * @return boolean	             	status on save
	 */
	public function insert($tableName, $parameters);

	/**
	 * updata a register
	 * @param  string 	$tableName  	name of entitie
	 * @param  array 	$parameters 	filter the register to update
	 * @return boolean             		status on save
	 */
	public function update($tableName, $parameters);

	/**
	 * [delete description]
	 * @param  string 	$tableName  	name of entitie
	 * @param  array 	$parameters 	filter the register to update
	 * @return boolean             		status on save
	 */	
	public function delete($tableName, $parameters);

	/**
	 * get list the entities
	 * @return [array] 
	 *
	 * @example-return: array("user","administrators","logs");
	 *
	 */
	public function tables();
}
