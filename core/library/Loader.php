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
| library, of the core library.
|
| @Param: $name - The name of the class
| @Param: $args - Arguments to be passed to the class
| @Param: $return - If set to 1, it will return the instance
|	instead of adding it to the super class.
|
*/
	function library($name, $args = NULL, $instance = 1)
	{
		// Get the class prefix
		$prefix = config('subclass_prefix', 'Core');
		
		// Check args to be passed, and load the class
		if($args !== NULL && !empty($args))
		{
			$class = load_class($name, $args);
		}
		else
		{
			$class = load_class($name);
		}
		
		// Do we instance this class?
		if($instance == 1 && ( class_exists('Controller') || class_exists($prefix . 'Controller') ))
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
				$instance = $args;
			}
			$FB = get_instance();
			$FB->$instance = $DB;
		}
		
		// $Obj->store("DBC_".$args, $DB);
		return $DB;
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
		elseif(file_exists(CORE_PATH . DS .  'helpers' . DS . $name . '.php')) 
		{
			require_once(CORE_PATH . DS .  'helpers' . DS . $name . '.php');
		}
	}
}