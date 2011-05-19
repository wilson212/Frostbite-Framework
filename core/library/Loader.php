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
		$class = ucfirst($name);
		
		if($folder != '')
		{
			require(ROOT . DS . $folder . DS . $name .'.php');
		}
		elseif($GLOBALS['is_module'] == TRUE)
		{
			require(APP_PATH . DS .'modules'. DS . $GLOBALS['controller'] . DS .'models'. DS . strtolower($name) .'.php');
		}
		else
		{
			require(APP_PATH . DS . 'models' . DS . $name .'.php');
		}
		
		$FB = get_instance();
		$FB->$class = new $class();
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
	function database($args = 0, $instance = FALSE)
	{
		global $Config;
		
		/*
		$Obj = Registry::singleton();
		if($Obj->load("DBC_".$args) != NULL)
		{
			return $Obj->load("DBC_".$args);
		}
		*/
		
		$DB = new Database(
			$Config->getDbInfo($args, 'host'),
			$Config->getDbInfo($args, 'port'),
			$Config->getDbInfo($args, 'username'),
			$Config->getDbInfo($args, 'password'),
			$Config->getDbInfo($args, 'database')
		);
		
		// If user wants to instance this, then we do that
		if($instance != FALSE && $instance != 0)
		{
			if($instance === TRUE || is_numeric($instance))
			{
				$instnace = $args;
			}
			$FB = get_instance();
			$FB->$as = $DB;
		}
		
		// $Obj->store("DBC_".$args, $DB);
		return $DB;
	}
}