<?php
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
	// Check for needed classes from the library folder
	if(file_exists(CORE_PATH . DS .  'library' . DS . strtolower($className) . '.php')) 
	{
		require_once(CORE_PATH . DS .  'library' . DS . strtolower($className) . '.php');
	}
	
	// Check application controllers
	elseif(file_exists(APP_PATH . DS . 'controllers' . DS . strtolower($className) . '.php')) 
	{
		require_once(APP_PATH . DS . 'controllers' . DS . strtolower($className) . '.php');
	}
	
	// Check Module controllers
	elseif(@file_exists(APP_PATH . DS . 'modules' . DS . strtolower($className) . '.php')) 
	{
		require_once(APP_PATH . DS . 'modules' . DS . strtolower($className) . '.php');
	}
	
	// Check application models
	elseif(file_exists(APP_PATH . DS .'models' . DS . strtolower($className) . '.php')) 
	{
		require_once(APP_PATH . DS .'models' . DS . strtolower($className) . '.php');
	}
	
	// We have an error as there is no classname
	else 
	{
		Core::trigger_error(1, 'Autoload failed to load class: '. $className, __FILE__, __LINE__);
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
	function &get_instance()
	{
		return Controller::get_instance();
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