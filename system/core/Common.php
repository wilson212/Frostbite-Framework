<?php
/*
| ---------------------------------------------------------------
| Function: show_error()
| ---------------------------------------------------------------
|
| This function is used to simplify the showing of errors
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
		$Core = new FB_Core();
		return $Core->trigger_error($lvl, $message, $file, $line, $errno);
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
	// We will need to remove the prefixes from the core classes
	if( strncmp($className, 'FB_', 3) == 0)
	{
		$className = substr($className, 3);
	}
	
	// We have our list of folders
	$folders = array( 
		SYSTEM_PATH . DS .  'core',
		APP_PATH . DS .  'core',		
		SYSTEM_PATH . DS .  'library',
		APP_PATH . DS .  'library' /*,
		APP_PATH . DS .  'controllers',
		APP_PATH . DS . 'modules'
		*/
	);	
	
	// Start the loop, checking each folder for the class
	foreach($folders as $folder)
	{
		// If the file exists, then include it, and return
		if(file_exists($folder . DS . $className . '.php')) 
		{
			require_once($folder . DS . $className . '.php');
			return;
		}
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
| @Param: $type - Either App, or Core. Loads the respective
|		config file variable
|
*/

function config($item, $type = 'App')
{
	$Config = load_class('Config');	
	
	switch($type)
	{
		case "App":
			return $Config->get($item, $type);
			break;
		
		case "Core":
			return $Config->get($item, $type);
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
| This function is used to set site config values. This does not
| set core, or database values.
|
| @Param: $item - The config item we are setting a value for
| @Param: $value - the value of $item
| @Param: $combine - Combine this data with the config.php vars?
|	If FALSE, the data will mot be saved to the config.php via 
|	the Save() method.
|
*/

function config_set($item, $value, $combine = TRUE)
{
	$Config = load_class('Config');	
	$Config->set($item, $value, $combine);
}

/*
| ---------------------------------------------------------------
| Method: config_save()
| ---------------------------------------------------------------
|
| This function is used to save site config values to the condig.php. 
| This does not save core, module, or database values.
|
*/

function config_save()
{
	$Config = load_class('Config');	
	$Config->Save();
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
| @Param: $combine - add the config vars to the config data?
|
*/

function load_config($file, $combine = FALSE)
{	
	$data = array();
	$vars = array();
	
	// Include file
	include( $file );
	$vars = get_defined_vars();
	if(count($vars) > 1)
	{
		foreach( $vars as $key => $val ) 
		{
			$data[$key] = $val;
			config_set($key, $val, $combine);
		}
	}
	return $data;
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
|
*/

function load_module_config($module, $filename = 'config.php')
{	
	$file = APP_PATH . DS .'modules' . DS . $module . DS . 'config' . DS . $filename;
	if(file_exists($file))
	{
		$MC = get_config_vars($file);
		if(count($MC) > 1)
		{
			foreach($MC as $key => $value)
			{
				config_set($key, $value, FALSE);
			}
		}
		return $MC;
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
		return FB_Controller::get_instance();
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
|
*/

function load_class($class)
{	
	// Inititate the Registry singleton into a variable
    $Obj = Registry::singleton();
    
	// lowercase classname, and have a Uppercase first version
    $Class = strtolower($class);
	$className = ucfirst($Class);
	
	// Check the registry for the class, If its there, then return the class
    if ($Obj->load($Class) !== NULL)
    { 
        return $Obj->load($Class);        
    }
	
	// ---------------------------------------------------------
    // Class not in Registry, So we load it manually and then  | 
	// store it in the registry for future static use          |
	// ---------------------------------------------------------
	
	// Override the prefix checking IF this is the config class we are loading
	($Class != 'config') ? $prefix = config('subclass_prefix', 'Core') : $prefix = '';

	// Check for needed classes from the Application library folder
	if(file_exists(APP_PATH . DS .  'core' . DS . $prefix . $className . '.php')) 
	{
		require_once(APP_PATH . DS .  'core' . DS . $prefix . $className . '.php');
	}
	
	// Check for needed classes from the Core library folder
	elseif(file_exists(SYSTEM_PATH . DS .  'core' . DS . $className . '.php')) 
	{
		require_once(SYSTEM_PATH . DS .  'core' . DS . $className . '.php');
		$prefix = "FB_";
	}
    
	// -------------------------------------------------------------
    //  Add the prefix, and Initiate the new class into a variable |
	// -------------------------------------------------------------
	$className = $prefix . $className;
	$dispatch = new $className();
	
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