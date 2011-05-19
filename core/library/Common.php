<?php
/*
| ---------------------------------------------------------------
| Function: show_error()
| ---------------------------------------------------------------
|
| @Param: $lvl - Level of the error
| @Param: $message - Error message
| @Param: $file - The file reporting the error
| @Param: $line - Error line number
| @Param: $errno - Error number
|
*/	
	function show_error($lvl, $message = 'Not Specified', $file = "none", $line = 0, $errno = 0)
	{
		$Core = new Core();
		return $Core->trigger_error($lvl, $message, $file, $line, $errno);
	}
	
/*
| ---------------------------------------------------------------
| Method: __autoload()
| ---------------------------------------------------------------
|
| @Param: $className - Class name to autoload ofc silly
|
*/

function __autoload($className) 
{	
	// Check the core library folder
	if(file_exists(CORE_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(CORE_PATH . DS .  'library' . DS . $className . '.php');
	}
	
	// Check the application library folder
	elseif(@file_exists(APP_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(APP_PATH . DS .  'library' . DS . $className . '.php');
	}
	
	// Check application controllers
	elseif(file_exists(APP_PATH . DS . 'controllers' . DS . strtolower($className) . '.php')) 
	{
		require_once(APP_PATH . DS . 'controllers' . DS . strtolower($className) . '.php');
	}
	
	// Check Module controllers
	elseif(@file_exists(APP_PATH . DS . 'modules' . DS . strtolower($className) . DS . 'controller.php')) 
	{
		require_once(APP_PATH . DS . 'modules' . DS . strtolower($className) . DS . 'controller.php');
	}
	
	// Check application models
	elseif(file_exists(APP_PATH . DS .'models' . DS . strtolower($className) . '.php')) 
	{
		require_once(APP_PATH . DS .'models' . DS . strtolower($className) . '.php');
	}
	
	// We have an error as there is no classname
	else 
	{
		show_error(3, 'Autoload failed to load class: '. $className, __FILE__, __LINE__);
	}
}

/* 
| Register this file to process errors with the custom_error_handler method
| We use this right after autoload so we can get these errors as quick as possible
*/
set_error_handler( array( 'Core', 'custom_error_handler' ), E_ALL );

/*
| ---------------------------------------------------------------
| Function: &get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding an outside class or file into the base controller
|
*/	
	function get_instance()
	{
		return Controller::get_instance();
	}

/*
| ---------------------------------------------------------------
| Function: load_class()
| ---------------------------------------------------------------
|
| This function is used to load and store core classes statically 
| that need to be loaded for use, but not reset next time the class
| is called.
|
| @Param: $class - Class needed to be loaded / returned
| @Param: $args - Suppossed to be the args passed to the class method
|	experementatl!
|
*/

function load_class($class, $args = NULL)
{
    $Obj = Registry::singleton();
    
	// lowercase classname
    $Class = strtolower($class);
	$className = ucfirst($Class);
    
    //if class already stored, then just return the class  
    if ($Obj->load($Class) !== NULL)
    { 
        return $Obj->load($Class);        
    }

	// Check for needed classes from the Core library folder
	if(file_exists(CORE_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(CORE_PATH . DS .  'library' . DS . $className . '.php');
	}
	
	// Check for needed classes from the Application library folder
	elseif(@file_exists(APP_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(APP_PATH . DS .  'library' . DS . $className . '.php');
	}
	else
	{
		return FALSE;
	}
    
    // Initiate the new class
	if($args !== NULL) 
	{
		$dispatch = new $className($args);
	}
	else
	{
		$dispatch = new $className();
	}
	
	// Store this new object in the registery
    $Obj->store($Class, $dispatch); 
    
    //return singleton object.
    $Object = $Obj->load($Class);

    if(is_object($Object))
	{
		return $Object;
	}
}

/*
| ---------------------------------------------------------------
| Method: config()
| ---------------------------------------------------------------
|
| @Param: $item - The config item we are looking for
| @Param: $type - Either App, DB, or Core. Loads the respective
|		config file
|
*/

function config($item, $type = 'App')
{
	global $Config;
	
	switch($type)
	{
		case "App":
			return $Config->get($item, $type);
			break;
		
		case "Core":
			return $Config->get($item, $type);
			break;
			
		case "DB":
			return $Config->getDbInfo($item);
			break;
			
		default:
			show_error(1, "Unknown config type: \"". $type ."\"");
			break;
	}
}

/*
| ---------------------------------------------------------------
| Method: config_set()
| ---------------------------------------------------------------
|
| @Param: $item - The config item we are setting a value for
| @Param: $value - the value of $item
|
*/

function config_set($item, $value)
{
	global $Config;
	
	$Config->set($item, $value);
}	

/*
| ---------------------------------------------------------------
| Method: redirect()
| ---------------------------------------------------------------
|
| @Param: $linkto - Where were going
| @Param: $type - 1 - direct header, 0 - meta refresh
| @Param: $wait - Only if $type = 0, then how many sec's we wait
|
*/

function redirect($linkto, $type = 0, $wait = 0)
{
	// preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	if($type == 0)
	{
		echo '<meta http-equiv=refresh content="'.$wait_sec.';url='.$linkto.'">';
	}
	else
	{
		header("Location: ".$linkto);
	}
}
// EOF