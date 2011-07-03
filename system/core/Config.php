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
| Class: Config()
| ---------------------------------------------------------------
|
| Main Config class. used to load, set, and save variables used
| in the config file.
|
*/
namespace System\Core;

class Config
{
	// An array of all out stored containers / variables
	protected $data = array();
	
	// A list of our loaded config files
	var $files = array();

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/	
	public function __construct() 
	{
		// Set default files
		$this->files['app']['file_path'] = APP_PATH . DS . 'config' . DS . 'config.php';
		$this->files['core']['file_path'] = APP_PATH . DS . 'config' . DS . 'core.config.php';
		$this->files['db']['file_path'] = APP_PATH . DS . 'config' . DS . 'database.config.php';
		
		// Lets roll!
		$this->Init();
	}
	
/*
| ---------------------------------------------------------------
| Method: Init()
| ---------------------------------------------------------------
|
| Initiates the default config files (App, Core, and DB)
|
| @Return: (None)
|
*/
	protected function Init() 
	{
		// Load the APP config.php and add the defined vars
		$this->Load($this->files['app']['file_path'], 'app');
		
		// Load the core.config.php and add the defined vars
		$this->Load($this->files['core']['file_path'], 'core', 'config');
		
		// Load the database.config.php and add the defined vars
		$this->Load($this->files['db']['file_path'], 'db', 'DB_configs');
	}
	
/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Returns the variable ($key) value in the config file.
|
| @Param: (String) $key - variable name. Value is returned
| @Param: (Mixed) $type - config variable container name
| @Return: (Mixed) May return NULL if the var is not set
|
*/
	function get($key, $type = 'App') 
	{
		// Lowercase the type
		$type = strtolower($type);
		
		// Check if the variable exists
		if(isset($this->data[$type][$key])) 
		{
			return $this->data[$type][$key];
		}
		return NULL;
	}
	
/*
| ---------------------------------------------------------------
| Method: getDbInfo()
| ---------------------------------------------------------------
|
| Returns the variable ($key) value in the database config file.
|
| @Param: (String) $key - variable name. Value is returned
| @Param: (Mixed) $pointer - the key in the array. Ex: ['db1']['host'],
|	The 'host' is the pointer
| @Return: (Array) An array of the database config for this $key
|
*/
	function getDbInfo($key, $pointer = NULL) 
	{
		if($pointer == NULL)
		{
			return $this->DB_configs[$key];
		}
		return $this->DB_configs[$key][$pointer];
	}
	
/*
| ---------------------------------------------------------------
| Method: set()
| ---------------------------------------------------------------
|
| Sets the variable ($key) value. If not saved, default value
| will be returned as soon as page is re-loaded / changed.
|
| @Param: (String) $key - variable name to be set
| @Param: (Mixed) $value - new value of the variable
| @Param: (Mixed) $name - The container name for the $key variable
| @Return: (None)
|
*/
	function set($key, $val, $name = 'App') 
	{
		// Lowercase the $name
		$name = strtolower($name);
		$this->data[$name][$key] = $val;
	}
	
/*
| ---------------------------------------------------------------
| Method: Load()
| ---------------------------------------------------------------
|
| Load a config file, and adds its defined variables to the $data
|	array
|
| @Param: (String) $file - Full path to the config file, includeing name
| @Param: (String) $name - The container name we are storing this configs
|	variables to.
| @Param: (String) $array - If the config vars are stored in an array, whats
|	the array variable name?
| @Return: (None)
|
*/
	function Load($file, $name, $array = FALSE) 
	{
		// Lowercase the $name
		$name = strtolower($name);
		
		// Include file and add it to the $files array
		if(!file_exists($file)) return FALSE;
		include( $file );
		$this->files[$name]['file_path'] = $file;
		$this->files[$name]['config_key'] = $array;
		
		// Get defined variables
		$vars = get_defined_vars();
		if($array != FALSE) $vars = $vars[$array];
		
		// Add the variables to the $data[$name] array
		if(count($vars) > 0)
		{
			foreach( $vars as $key => $val ) 
			{
				if($key != 'this' && $key != 'data') 
				{
					$this->data[$name][$key] = $val;
				}
			}
		}
		return;
	}

/*
| ---------------------------------------------------------------
| Method: Save()
| ---------------------------------------------------------------
|
| Saves all set config variables to the config file, and makes 
| a backup of the current config file
|
| @Param: (String) $name - Name of the container holding the variables
| @Return: (Bool) TRUE on success, FALSE otherwise
|
*/
	function Save($name) 
	{
		// Lowercase the $name
		$name = strtolower($name);
		
		// Check to see if we need to put this in an array
		$ckey = $this->files[$name]['config_key'];
		if($ckey != FALSE)
		{
			$Old_Data = $this->data[$name];
			$this->data[$name] = array("$ckey" => $this->data[$name]);
		}
		
		// Create our new file content
		$cfg  = "<?php\n";
		
		// Loop through each var and write it
		foreach( $this->data[$name] as $key => $val ) 
		{
			if(is_numeric($val)) 
			{
				$cfg .= "\$$key = " . $val . ";\n";
			} 
			elseif(is_array($val))
			{
				$val = var_export($val, TRUE);
				$cfg .= "\$$key = " . $val . ";\n";
			}
			else
			{
				$cfg .= "\$$key = '" . addslashes( $val ) . "';\n";
			}
		}
	
		// Close the php tag
		$cfg .= "?>";
		
		// Add the back to non array if we did put it in one
		if($ckey != FALSE)
		{
			$this->data[$name] = $Old_Data;
		}
		
		// Copy the current config file for backup, 
		// and write the new config values to the new config
		@copy($this->files[$name]['file_path'], $this->files[$name]['file_path'].'.bak');
		
		if(file_put_contents( $this->files[$name]['file_path'], $cfg )) 
		{
			return TRUE;
		} 
		else 
		{
			return FALSE;
		}
	}
}
// EOF