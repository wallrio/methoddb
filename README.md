MethodDB
====
ORM using method


## Create the object

    $methodDB = new MethodDB();


## Setup the base

###  with MySQL

    $methodDB->config(array(
    	'driver'=>'mysql',
    	'host'=>'host',
    	'username'=>'username',
    	'password'=>'password',
    	'base'=>'base_name'
    ));

### with DJDB

$methodDB = new MethodDB();
    $methodDB->config(array(
        'driver'=>'djdb',
        'host'=> __DIR__,
        'base'=>'base_name'
    ));

    ### Detail:

        - host: directory to record the data
        - base: sub-directory of 'host' to base


## Connect with the database

    $database = $methodDB->connect();

## Check the entities

    $tablesArray =  $database->entities();

## Request data of a table (SELECT)

    $array = $database->TABLE_NAME();

        example:
            $array = $database->users();    

## Request data of a table with filter (SELECT)

    $array = $database->users(array(
        'where'=>' name LIKE "%user_name%" ',
        'order'=>'ASC',
        'limit'=>'0,2'          
    ));


## Insert a register to table (INSERT)

    $users = $database->entities('users')->insert();

    $users->name = "user name";
    $users->login = "username@domain.com";
    $users->password = "123456";

    $database->save();


## Insert multiple register to multiple table (INSERT)

    $users = $database->entities('users')->insert();

    $users->name = "user name";
    $users->login = "username@domain.com";
    $users->password = "123456";


    $users = $database->entities('company')->insert();

    $users->name = "Company name";
    $users->login = "company@domain.com";

    $database->save();    


## Delete a register from table (DELETE)

    $admin = $database->entities('users')->delete(array(
        'where'=>'id = 0123'   
    ));    


## Update a register (UPDATE)

    $admin = $database->entities('users')->update(array(
        'where'=>'id = 0123'   
    ));

    $admin->name = "user name";
    $admin->login = "username@domain.com";
    $admin->password = "123";

    $database->save();    


## To create a new driver

    To create a new driver, you must follow the interface below.

    interface MethodDBDRIVERS{  

    /**
     * connect with the database and define the parameters initial
     * @param  array  $config   Contains the definitions of '$methodDB->config' method
     * @return null
     */
    public function connect(array $config);

    /**
     * select a or multiples register of entitie
     * @param  [string]     $tableName      Name of entitie
     * @param  array        $parameters     Filter to selection
     * @return array                        list of registers founds
     *
     * @example-return: (object) array(
     *                          "USER_ID"=> (object) array(
     *                              "username"=>"fulano",
     *                              "name"=>"Fulano da Silva",
     *                              "password"=>"123"
     *                          )
     *                      );
     *
     */
    public function select($tableName, $parameters);

    /**
     * insert a register on database
     * @param  string   $tableName      name of entitie
     * @param  array    $parameters     data of register to save on database
     * @return boolean                  status on save
     */
    public function insert($tableName, $parameters);

    /**
     * updata a register
     * @param  string   $tableName      name of entitie
     * @param  array    $parameters     filter the register to update
     * @return boolean                  status on save
     */
    public function update($tableName, $parameters);

    /**
     * [delete description]
     * @param  string   $tableName      name of entitie
     * @param  array    $parameters     filter the register to update
     * @return boolean                  status on save
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


# Example to call driver:
    $methodDB = new MethodDB();
    $methodDB->config(array(
        'driver'=> new MyNewDrive(),
        'host'=>'host',
        'username'=>'username',
        'password'=>'password',
        'base'=>'base_name'
    ))
