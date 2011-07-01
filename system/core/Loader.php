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

		// Instnace the Model in the controller
		if($instance_as !== NULL)
		{
			get_instance()->$instance_as = new $class();
		}
		else
		{
			get_instance()->$name = new $class();
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
			get_instance()->$name = $class;
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
	function database($args, $instance = FALSE)
	{
		// Check our registry to see if we already loaded this connection
		$Obj = \Registry::singleton()->load("DBC_".$args);
		if($Obj != NULL)
		{
			return $Obj;
		}
		
		// Get the DB connection information
		$info = config($args, 'DB');
		if($info === NULL)
		{
			show_error('db_key_not_found', array($args), E_ERROR);
		}
		$info['driver'] = ucfirst($info['driver']."_driver");
		
		// Check for a DB class in the Application, and system core folder
		if(file_exists(APP_PATH. DS . 'database' . DS . $info['driver'] . '.php')) 
		{
			require_once(APP_PATH. DS . 'database' . DS . $info['driver'] . '.php');
			$first = "Application\\";
		}
		elseif(file_exists(SYSTEM_PATH. DS . 'database' . DS . $info['driver'] . '.php')) 
		{
			require_once(SYSTEM_PATH. DS . 'database' . DS . $info['driver'] . '.php');
			$first = "System\\";
		}
		
		// Not in the registry, so istablish a new connection
		$dispatch = $first ."Database\\".$info['driver'];
		$DB = new $dispatch(
			$info['host'],
			$info['port'],
			$info['username'],
			$info['password'],
			$info['database']
		);
		
		// If user wants to instance this, then we do that
		if($instance != FALSE && (!is_numeric($args)))
		{
			if($instance === TRUE)
			{
				$instance = $args;
			}
			
			// Easy way to instance the connection is like this
			get_instance()->$instance = $DB;
		}
		
		// Store the connection in the registry
		\Registry::singleton()->store("DBC_".$args, $DB);
		
		// Return the object!
		return \Registry::singleton()->load("DBC_".$args);
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
}
// EOF