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
	protected $data = array();
	var $files = array();

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/	
	public function __construct() 
	{
		// Set default files
		$this->files['app'] = APP_PATH . DS . 'config' . DS . 'config.php';
		$this->files['core'] = APP_PATH . DS . 'config' . DS . 'core.config.php';
		$this->files['db'] = APP_PATH . DS . 'config' . DS . 'database.config.php';
		
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
*/
	protected function Init() 
	{
		// Load the APP config.php and add the defined vars
		$this->Load($this->files['app'], 'app');
		
		// Load the core.config.php and add the defined vars
		$this->Load($this->files['core'], 'core', 'config');
		
		// Load the database.config.php and add the defined vars
		$this->Load($this->files['db'], 'db', 'DB_configs');
	}
	
/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Returns the variable ($key) value in the config file.
|
| @Param: $key - variable name. Value is returned
| @Param: $type - config variable container name
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
| @Param: $key - variable name. Value is returned
| @Param: $pointer - the key in the array. Ex: ['db1']['host'],
|	The 'host' is the pointer
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
| @Param: $key - variable name to be set
| @Param: $value - new value of the variable
| @Param: $name - The container name for the $key variable
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
| @Param: $file - Full path to the config file, includeing name
| @Param: $name - The container name we are storing this configs
|	variables to.
| @Param: $array - If the config vars are stored in an array, whats
|	the array variable name?
|
*/
	function Load($file, $name, $array = FALSE) 
	{
		// Lowercase the $name
		$name = strtolower($name);
		
		// Include file and add it to the $files array
		if(!file_exists($file)) return FALSE;
		include( $file );
		$this->files[$name] = $file;
		
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
| @Param: $name - Name of the container holding the variables
|
*/
	function Save($name) 
	{
		// Lowercase the $name
		$name = strtolower($name);
		
		// Create our new file content
		$cfg  = "<?php\n";
		foreach( $this->data[$name] as $key => $val ) 
		{
			if(is_numeric($val)) 
			{
				$cfg .= "\$$key = " . $val . ";\n";
			} 
			else 
			{
				$cfg .= "\$$key = '" . addslashes( $val ) . "';\n";
			}
		}
		$cfg .= "?>";
		
		// Copy the current config file for backup, 
		// and write the new config values to the new config
		@copy($this->files[$name], $this->files[$name].'.bak');
		
		if(file_put_contents( $this->files[$name], $cfg )) 
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