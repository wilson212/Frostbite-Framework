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
namespace System\Core;

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
		
		// Include the model page
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
| @Param: $name - The name of the class, with or without namespacing
| @Param: $instance - Do we instance the class?
|
*/
	function library($name, $instance = TRUE)
	{
		// Make sure periods are replaced with slahes if there is any
		$name = str_replace('.', '\\', $name);
		
		// explode backslahes just in case
		if(strpos('\\', $name) !== FALSE)
		{
			$full_name = $name;
		}
		else
		{
			$full_name = "Library\\".$name;
		}
		
		// Load the Class
		$class = load_class($full_name);
		
		// Do we instance this class?
		if($instance == TRUE) // && ( class_exists('\\System\\Core\\Controller') || class_exists('\\Application\\Core\\Controller') ))
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
| @Param: $instance - If you want to instance the connection
|	in the controller, set to TRUE.
|
*/	
	function database($args = 0, $instance = FALSE)
	{
		$Config = load_class("Core\\Config");
		
		// Check to see if our connection Id is numeric
		if(is_numeric($args))
		{
			$args = $this->_get_db_key();
		}
		
		// Check our registry to see if we already loaded this connection
		$Obj = \Registry::singleton();
		if($Obj->load("DBC_".$args) != NULL)
		{
			return $Obj->load("DBC_".$args);
		}
		
		// Check for a DB class in the Application, and system core folder
		if(file_exists(APP_PATH. DS . 'core' . DS . 'Database.php')) 
		{
			require_once(APP_PATH. DS . 'core' . DS . 'Database.php');
			$first = "Application\\";
		}
		elseif(file_exists(SYSTEM_PATH. DS . 'core' . DS . 'Database.php')) 
		{
			require_once(SYSTEM_PATH. DS . 'core' . DS . 'Database.php');
			$first = "System\\";
		}
		else // Nither file exists, we cant continue
		{
			show_error(3, 'No database class found!', __FILE__, __LINE__);
		}
		
		// Not in the registry, so istablish a new connection
		$dispatch = $first . "Core\\Database";
		$info = config($args, 'DB');
		$DB = new $dispatch(
			$info['host'],
			$info['port'],
			$info['username'],
			$info['password'],
			$info['database']
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