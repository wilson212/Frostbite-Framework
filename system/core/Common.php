<?php
/*
| ---------------------------------------------------------------
| Function: show_error()
| ---------------------------------------------------------------
|
| This function is used to simplify the showing of errors
|
| @Param: $lvl - Level of the error
| @Param: $err_message - Error message code
| @Param: $args - An array for vsprintf to replace in the message
|
*/	
	function show_error($lvl, $err_message = 'none', $args = NULL)
	{
		// Let get a backtrace for deep debugging
		$backtrace = debug_backtrace();
		$calling = $backtrace[0];
		
		// Load language
		$lang = load_class('Core.Language');
		$lang->set_language( config('core_language', 'Core') );
		$lang->load('errors');
		$message = $lang->get($err_message);
		
		// Allow custom messages
		if($message === FALSE)
		{
			$message = $err_message;
		}
		
		// do replacing
		if(is_array($args))
		{
			$message = vsprintf($message, $args);
		}
		
		// Init and spit the error
		$E = new \System\Core\Error_Handler();
		$E->trigger_error($lvl, $message, $calling['file'], $calling['line'], $backtrace);
	}
	
/*
| ---------------------------------------------------------------
| Method: __autoload()
| ---------------------------------------------------------------
|
| This function is used to autoload files of delcared classes
| that have not been included yet
|
| @Param: $className - Class name to autoload ofc silly
|
*/

function __autoload($className) 
{	
	// We will need to lowercase everything except for the filename
	$class_path = str_replace('\\', '|', strtolower($className));
	$parts = explode('|', $class_path);
	$parts[2] = strtoupper( $parts[2] );
	$class_path = implode($parts, DS);
	
	$file = ROOT . DS . $class_path .'.php';
	
	// If the file exists, then include it, and return
	if(file_exists($file)) 
	{
		require_once($file);
		return;
	}
	
	// If we are at this point, then we didnt find the class file.
	show_error(3, 'Autoload failed to load class: '. $className, __FILE__, __LINE__);
}


/*
| ---------------------------------------------------------------
| Method: config()
| ---------------------------------------------------------------
|
| This function is used to return a config value from a config
| file.
|
| @Param: $item - The config item we are looking for
| @Param: $type - Name of the config variables, this is set 
|	when you load the config, defaults are Core, App and Mod
|
*/

function config($item, $type = 'App')
{
	$Config = load_class('Core\\Config');		
	return $Config->get($item, $type);
}

/*
| ---------------------------------------------------------------
| Method: config_set()
| ---------------------------------------------------------------
|
| This function is used to set site config values. This does not
| set core, or database values.
|
| @Param: $item - The config item we are setting a value for
| @Param: $value - the value of $item
| @Param: $combine - The name of this config variables
|
*/

function config_set($item, $value, $name = 'App')
{
	$Config = load_class('Core\\Config');	
	$Config->set($item, $value, $name);
}

/*
| ---------------------------------------------------------------
| Method: config_save()
| ---------------------------------------------------------------
|
| This function is used to save site config values to the condig.php. 
| *Warning - This will remove any and ALL comments in the config file
|
| @Param: $name - Which config are we saving? App? Core? Module?
|
*/

function config_save($name)
{
	$Config = load_class('Core\\Config');	
	$Config->Save($name);
}

/*
| ---------------------------------------------------------------
| Method: load_config()
| ---------------------------------------------------------------
|
| This function is used to get all defined variables from a config
| file.
|
| @Param: $file - full path and filename to the config file being loaded
| @Param: $name - The name of this config variables, for later access. Ex:
| 	if $name = 'test', the to load a $var -> config( 'var', 'test');
| @Param: $array - If the config vars are stored in an array, whats
|	the array variable name?
|
*/

function load_config($file, $name, $array = FALSE)
{	
	$Config = load_class('Core\\Config');	
	$Config->Load($file, $name, $array);
}	

/*
| ---------------------------------------------------------------
| Method: load_module_config()
| ---------------------------------------------------------------
|
| This function is used to load a modules config file, and add
| those config values to the site config.
|
| @Param: $module - Name of the module
| @Param: $filename - name of the file if not 'config.php'
| @Param: $array - If the config vars are stored in an array, whats
|	the array variable name?
|
*/

function load_module_config($module, $filename = 'config.php')
{	
	$file = APP_PATH . DS .'modules' . DS . $module . DS . 'config' . DS . $filename;
	if(file_exists($file))
	{
		load_config($file, 'mod');
	}
}	

/*
| ---------------------------------------------------------------
| Function: get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding the controller into your current working class
|
*/	
	function get_instance()
	{
		return System\Core\Controller::get_instance();
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
| @Param: $className - Class needed to be loaded / returned
|
*/

function load_class($className)
{
	// Make sure periods are replaced with slahes
	$className = str_replace('.', '\\', $className);
	
	// Inititate the Registry singleton into a variable
	$Obj = Registry::singleton();

	// Make a lowercase version
	$Class = strtolower($className);
	$temp = str_replace('\\', '_', $Class);

	// Check the registry for the class, If its there, then return the class
	if($Obj->load($temp) !== NULL)
	{ 
		return $Obj->load($temp);        
	}

	// ---------------------------------------------------------
	// Class not in Registry, So we load it manually and then  | 
	// store it in the registry for future static use          |
	// ---------------------------------------------------------
	
	///echo $className ."<br />";

	// explode our backslashes!
	$parts = explode('\\', $Class);
	if($parts[0] == 'system' || $parts[0] == 'application')
	{
		// Uppercase the filename
		$parts[2] = ucfirst($parts[2]);
		$file = $parts[0] . DS . $parts[1] . DS . $parts[2];
		require_once(ROOT . DS .  $file . '.php');
	}
	else
	{
		// Uppercase the filename
		$parts[1] = ucfirst($parts[1]);
		$file = $parts[0] . DS . $parts[1];

		// Check for needed classes from the Application library folder
		if(file_exists(APP_PATH. DS . $file . '.php')) 
		{
			$className = '\\Application\\'. $className;
			require_once(APP_PATH . DS . $file . '.php');
		}

		// Check for needed classes from the Core library folder
		elseif(file_exists(SYSTEM_PATH . DS . $file . '.php')) 
		{
			$className = '\\System\\'. $className;
			require_once(SYSTEM_PATH . DS . $file . '.php');
		}
	}

	// -----------------------------------------
	//  Initiate the new class into a variable |
	// -----------------------------------------
	$dispatch = new $className();

	// Store this new object in the registery
	$Obj->store($temp, $dispatch); 

	//return singleton object.
	$Object = $Obj->load($temp);

	if(is_object($Object))
	{
		return $Object;
	}
}

/*
| ---------------------------------------------------------------
| Method: redirect()
| ---------------------------------------------------------------
|
| This function is used to easily redirect and refresh pages
|
| @Param: $url - Where were going
| @Param: $wait - How many sec's we wait till the redirect.
|
*/

function redirect($url, $wait = 0)
{
	// Check for a valid URL. If not then add our current BASE_URL to it.
	if(!preg_match('|^http(s)?://|i', $url))
	{
		$url = BASE_URL . $url;
	}
	
	// Check for refresh or straight redirect
	if($wait >= 1)
	{
		header("Refresh:". $wait .";url=". $url);
	}
	else
	{
		header("Location: ".$url);
	}
}
// EOF