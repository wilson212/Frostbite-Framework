<?php
/*
| ---------------------------------------------------------------
| Function: show_error()
| ---------------------------------------------------------------
|
| This function is used to simplify the showing of errors
|
| @Param: (String) $err_message - Error message code
| @Param: (Array) $args - An array for vsprintf to replace in the 
| @Param: (Int) $lvl - Level of the error
| @Return: (None)
|
*/	
	function show_error($err_message = 'none', $args = NULL, $lvl = E_ERROR)
	{
		// Let get a backtrace for deep debugging
		$backtrace = debug_backtrace();
		$calling = $backtrace[0];
		
		// Load language
		$lang = load_class('Core.Language');
		$lang->set_language( config('core_language', 'Core') );
		$lang->load('core_errors');
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
| Function: show_404()
| ---------------------------------------------------------------
|
| Displays the 404 Page
|
| @Return: (None)
|
*/	
	function show_404()
	{		
		// Init and spit the error
		$E = new \System\Core\Error_Handler();
		$E->trigger_error(404);
	}
	
/*
| ---------------------------------------------------------------
| Method: __autoload()
| ---------------------------------------------------------------
|
| This function is used to autoload files of delcared classes
| that have not been included yet
|
| @Param: (String) $className - Class name to autoload ofc silly
| @Return: (None)
|
*/

function __autoload($className) 
{	
	// We will need to lowercase everything except for the filename
	$parts = explode('\\', $className);
	$parts[2] = ucfirst( $parts[2] );
	$class_path = implode($parts, DS);
	
	// Lets make our file path
	$file = ROOT . DS . $class_path .'.php';
	
	// If the file exists, then include it, and return
	if(!include $file)
	{
		// Failed to load class all together.
		show_error('autoload_failed', array( addslashes($className) ), E_ERROR);
	}
	return;
}


/*
| ---------------------------------------------------------------
| Method: config()
| ---------------------------------------------------------------
|
| This function is used to return a config value from a config
| file.
|
| @Param: (String) $item - The config item we are looking for
| @Param: (Mixed) $type - Name of the config variables, this is set 
|	when you load the config, defaults are Core, App and Mod
| @Return: (Mixed) - Returns the config vaule of $item
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
| @Param: (String) $item - The config item we are setting a value for
| @Param: (Mixed) $value - the value of $item
| @Param: (Mixed) $name - The name of this config variables container
| @Return: (None)
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
| @Param: (Mixed) $name - Which config are we saving? App? Core? Module?
| @Return: (None)
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
| @Param: (String) $file - full path and filename to the config file being loaded
| @Param: (Mixed) $name - The name of this config variables, for later access. Ex:
| 	if $name = 'test', the to load a $var -> config( 'var', 'test');
| @Param: (String) $array - If the config vars are stored in an array, whats
|	the array variable name?
| @Return: (None)
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
| @Param: (String) $module - Name of the module
| @Param: (String) $filename - name of the file if not 'config.php'
| @Param: (String) $array - If the config vars are stored in an array, whats
|	the array variable name?
| @Return: (None)
|
*/

function load_module_config($module, $filename = 'config.php', $array = FALSE)
{	
	// Get our filename and use the load_config method
	$file = APP_PATH . DS .'modules' . DS . $module . DS . 'config' . DS . $filename;
	load_config($file, 'mod', $array);
}	

/*
| ---------------------------------------------------------------
| Function: get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding the controller into your current working class
|
| @Return: (Object) - Return the instnace of the Controller
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
| @Param: (String) $className - Class needed to be loaded / returned
| @Return: (Object) - Returns the loaded class
|
*/

function load_class($className)
{
	// Make sure periods are replaced with slahes
	if(strpos($className, '.'))
	{
		$className = str_replace('.', '\\', $className);
	}
	
	// Inititate the Registry singleton into a variable
	$Obj = Registry::singleton();

	// Make a lowercase version, and a storage name
	$class = strtolower($className);
	$temp = str_replace('\\', '_', $class);

	// Check the registry for the class, If its there, then return the class
	if($Obj->load($temp) !== NULL)
	{ 
		return $Obj->load($temp);        
	}

	// ---------------------------------------------------------
	// Class not in Registry, So we load it manually and then  | 
	// store it in the registry for future static use          |
	// ---------------------------------------------------------

	// We need to find the file the class is stored in. Good thing the
	// Namespaces are pretty much paths to the class ;)
	$parts = explode('\\', $class);
	
	// Do we already have our full path?
	if($parts[0] == 'system' || $parts[0] == 'application')
	{
		// Uppercase the filename
		$parts[2] = ucfirst($parts[2]);
		$file = $parts[0] . DS . $parts[1] . DS . $parts[2];
		require_once(ROOT . DS .  $file . '.php');
	}
	
	// We dont, So we need to create our path
	else
	{
		// Uppercase the filename
		$parts[1] = ucfirst($parts[1]);
		$file = $parts[0] . DS . $parts[1];

		// Check for needed classes from the Application library folder
		if(file_exists(APP_PATH. DS . $file . '.php')) 
		{
			$className = '\\Application\\'. $className;
			include_once(APP_PATH . DS . $file . '.php');
		}

		// Check for needed classes from the Core library folder
		else 
		{
			$className = '\\System\\'. $className;
			include_once(SYSTEM_PATH . DS . $file . '.php');
		}
	}

	// -----------------------------------------
	//  Initiate the new class into a variable |
	// -----------------------------------------
	$dispatch = new $className();

	// Store this new object in the registery
	$Obj->store($temp, $dispatch); 

	//return singleton object.
	return $Obj->load($temp);
}

/*
| ---------------------------------------------------------------
| Method: redirect()
| ---------------------------------------------------------------
|
| This function is used to easily redirect and refresh pages
|
| @Param: (String) $url - Where were going
| @Param: (Int) $wait - How many sec's we wait till the redirect.
| @Return: (None)
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
	if($wait > 0)
	{
		header("Refresh:". $wait .";url=". $url);
		return;
	}
	header("Location: ".$url);
}
// EOF