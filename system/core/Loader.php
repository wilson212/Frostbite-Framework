<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:	Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|
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
	
/*
| ---------------------------------------------------------------
| Method: model()
| ---------------------------------------------------------------
|
| This method is used to call in a model
|
| @Param: $name - The name of the model
| @Param: $instance_as - How you want to access it as in the 
|	controller (IE: $instance_as = test; In controller: $this->test)
|
*/
	function model($name, $instance_as = NULL)
	{
		$class = ucfirst($name);
		$name = strtolower($name);
		
		if($GLOBALS['is_module'] == TRUE)
		{
			require(APP_PATH . DS .'modules'. DS . $GLOBALS['controller'] . DS .'models'. DS . $name .'.php');
		}
		else
		{
			require(APP_PATH . DS . 'models' . DS . $name .'.php');
		}
		
		// instance it in the controller
		$FB = get_instance();
		
		if($instance_as !== NULL)
		{
			$FB->$instance_as = new $class();
		}
		else
		{
			$FB->$name = new $class();
		}
	}
	
/*
| ---------------------------------------------------------------
| Method: library()
| ---------------------------------------------------------------
|
| This method is used to call in a class from either the APP
| library, or the system library folders.
|
| @Param: $name - The name of the class
| @Param: $instance - Do we instance the class?
|
*/
	function library($name, $instance = TRUE)
	{
		// Get the class prefix
		$prefix = config('subclass_prefix', 'Core');
		
		// Load the Class
		$class = load_class($name);
		
		// Do we instance this class?
		if($instance == TRUE && ( class_exists('Controller') || class_exists($prefix . 'Controller') ))
		{
			$name = strtolower($name);
			$FB = get_instance();
			$FB->$name = $class;
		}
		return $class;
	}

/*
| ---------------------------------------------------------------
| Method: database()
| ---------------------------------------------------------------
|
| This method is used to setup a database connection
|
| @Param: $args - The indentifier of the DB connection in the DB 
| 	config file.
| @Param: $instance_as - If you want to instance the connection
|	in the controller, set to TRUE.
|
*/	
	function database($args = 0, $instance = FALSE)
	{
		$Config = load_class("Config");
		
		// Check to see if our connection Id is numeric
		if(is_numeric($args))
		{
			$args = $this->_get_db_key();
		}
		
		// Check our registry to see if we already loaded this connection
		$Obj = Registry::singleton();
		if($Obj->load("DBC_".$args) != NULL)
		{
			return $Obj->load("DBC_".$args);
		}
		
		// Not in the registry, so istablish a new connection
		$DB = new Database(
			$Config->getDbInfo($args, 'host'),
			$Config->getDbInfo($args, 'port'),
			$Config->getDbInfo($args, 'username'),
			$Config->getDbInfo($args, 'password'),
			$Config->getDbInfo($args, 'database')
		);
		
		// If user wants to instance this, then we do that
		if($instance != FALSE && (!is_numeric($args) || !is_numeric($instance)))
		{
			if($instance === TRUE || is_numeric($instance))
			{
				$instance = $args;
			}
			$FB = get_instance();
			$FB->$instance = $DB;
		}
		
		// Store the connection in the registry
		$Obj->store("DBC_".$args, $DB);
		
		// Return the object!
		return $Obj->load("DBC_".$args);
	}
	
/*
| ---------------------------------------------------------------
| Method: helper()
| ---------------------------------------------------------------
|
| This method is used to call in a helper file from either the 
| application/helpers, or the core/helpers folders.
|
| @Param: $name - The name of the helper file
|
*/
	function helper($name)
	{		
		// Check the application/helpers folder
		if(file_exists(APP_PATH . DS .  'helpers' . DS . $name . '.php')) 
		{
			require_once(APP_PATH . DS .  'helpers' . DS . $name . '.php');
		}
		
		// Check the core/helpers folder
		elseif(file_exists(SYSTEM_PATH . DS .  'helpers' . DS . $name . '.php')) 
		{
			require_once(SYSTEM_PATH . DS .  'helpers' . DS . $name . '.php');
		}
	}
	
/*
| ---------------------------------------------------------------
| Method: _get_db_key()
| ---------------------------------------------------------------
|
| This method returns the correct key identifier for database 0
|
*/
	function _get_db_key()
	{		
		include(APP_PATH . DS .  'config' . DS . 'database.config.php');
		$keys = array_keys($DB_configs);

		return $keys[0];
	}
}