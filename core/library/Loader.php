<?php
/*
| ---------------------------------------------------------------
| Class: Loader()
| ---------------------------------------------------------------
|
| This class is used to load classes and librarys into the calling
| class / method.
|
*/

class Loader
{	
	// List of loaded DB connections
	protected static $DB = FALSE;
	
	function Loader()
	{
		// $this->RDB = FALSE;
	}
	
/*
| ---------------------------------------------------------------
| Method: model()
| ---------------------------------------------------------------
|
| This method is used to call in a model
|
| @Param: $name - The name of the model
| @Param: $folder - Location (if not default) of the model
|
*/
	function model($name, $folder = '')
	{
		if($folder != '')
		{
			require(ROOT . DS . $folder . DS . $name .'.php');
		}
		else
		{
			require(APP_PATH . DS . 'models' . DS . $name .'.php');
		}
		
		$name = ucfirst($name);
		$FB = get_instance();
		$FB->$name = new $name();
	}
	
/*
| ---------------------------------------------------------------
| Method: library()
| ---------------------------------------------------------------
|
| This method is used to call in a class from either the APP
| library, of the core library.
|
| @Param: $name - The name of the class
| @Param: $args - Arguments to be passed to the class
| @Param: $return - If set to 1, it will return the instance
|	instead of adding it to the super class.
|
*/
	function library($name, $args = NULL, $return = 0)
	{
		if($args !== NULL && !empty($args))
		{
			$class = load_class($name, $args);
		}
		else
		{
			$class = load_class($name);
		}
		
		if($return == 0)
		{
			$name = strtolower($name);
			$FB = get_instance();
			$FB->$name = $class;
			return;
		}
		else
		{
			return $class;
		}
	}

/*
| ---------------------------------------------------------------
| Method: database()
| ---------------------------------------------------------------
|
| This method is used to setup a database connection
|
| @Param: $args - Either R (Realm DB), C (Character DB), W (World DB)
| or and array to connect to a none default Database:
|	$args = array( $host, $port, $DB Username, $DB Password, $DB Name);
|
*/	
	function database($args = NULL)
	{
		$Config = load_class('Config');
		
		// If is an array, then its a new DB connection
		if(is_array($args))
		{
			// Init. seperate vars for DB connection.
			$db_host = $args[0];
			$db_port = $args[1];
			$db_user = $args[2];
			$db_pass = $args[3];
			$db_name = $args[4];
			
			// Connect up to the DB
			$DB = new Database($db_host, $db_port, $db_user, $db_pass, $db_name);
			return $DB;
		}
		else
		{
			$DB = new Database(
				$Config->getDbInfo('db_host'),
				$Config->getDbInfo('db_port'),
				$Config->getDbInfo('db_username'),
				$Config->getDbInfo('db_password'),
				$Config->getDbInfo('db_name')
			);
			return $DB;
		}
	}
}