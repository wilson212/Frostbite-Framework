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

class FB_Config
{	
	var $data = array();
	protected $DB_configs;
	protected $Core_configs;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/	
	public function __construct() 
	{
		// Set defaults
		$this->configFile = APP_PATH . DS . 'config' . DS . 'config.php';
		$this->Core_configFile = APP_PATH . DS . 'config' . DS . 'core.config.php';
		$this->DB_configFile = APP_PATH . DS . 'config' . DS . 'database.config.php';
		$this->Load();
	}
	
/*
| ---------------------------------------------------------------
| Method: Load()
| ---------------------------------------------------------------
|
| Gets the defined variables in the config files
|
*/
	protected function Load() 
	{
		// Load the APP config.php and add the defined
		// vars to $this->data
		if(file_exists($this->configFile)) 
		{
			include( $this->configFile );
			$vars = get_defined_vars();
			foreach( $vars as $key => $val ) 
			{
				if($key != 'this' && $key != 'data') 
				{
					$this->data[$key] = $val;
				}
			}
		}
		
		// Load the core.config.php and add the defined
		// vars to $this->Core_configs
		if(file_exists($this->Core_configFile)) 
		{
			include( $this->Core_configFile );
			foreach( $config as $key => $val ) 
			{
				$this->Core_configs[$key] = $val;
			}
		}
		
		// Load the database.config.php and add the defined
		// vars to $this->DB_configs
		if(file_exists($this->DB_configFile)) 
		{
			include( $this->DB_configFile );
			foreach( $DB_configs as $key => $val ) 
			{
				$this->DB_configs[$key] = $val;
			}
		}
		
	}
	
/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Returns the variable ($key) value in the config file.
|
| @Param: $key - variable name. Value is returned
|
*/
	function get($key, $type = 'App') 
	{
		switch($type)
		{
			case 'App':
				if(isset($this->data[$key])) 
				{
					return $this->data[$key];
				}
				else
				{
					$key = "TEMP_". $key;
					if(isset($this->data[$key])) 
					{
						return $this->data[$key];
					}
				}
				break;
				
			case 'Core':
				if(isset($this->Core_configs[$key])) 
				{
					return $this->Core_configs[$key];
				}
				break;
		}
		return FALSE;
	}
	
/*
| ---------------------------------------------------------------
| Method: getDbInfo()
| ---------------------------------------------------------------
|
| Returns the variable ($key) value in the database config file.
|
| @Param: $key - variable name. Value is returned
|
*/
	function getDbInfo($key, $value = NULL) 
	{
		if($value == NULL)
		{
			return $this->DB_configs[$key];
		}
		return $this->DB_configs[$key][$value];
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
| @Param: $combine - Combine this data with the config.php vars?
|	If FALSE, the data will mot be saved to the config.php via 
|	the Save() method.
|
*/
	function set($key, $val, $combine = TRUE) 
	{
		// Add a prefix if $combine is set to FALSE
		if($combine == FALSE)
		{
			$key = "TEMP_". $key;
		}
		$this->data[$key] = $val;
	}

/*
| ---------------------------------------------------------------
| Method: getDbInfo()
| ---------------------------------------------------------------
|
| Saves all set config variables to the config file, and makes 
| a backup of the current config file
|
*/
	function Save() 
	{
		$cfg  = "<?php\n";
		foreach( $this->data as $key => $val ) 
		{
			// Donot store temporary variables
			if(strpos($key, 'TEMP_') === FALSE)
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
		}
		$cfg .= "?>";
		
		// Copy the current config file for backup, 
		// and write the new config values to the new config
		@copy($this->configFile, $this->configFile.'.bak');
		
		if(file_put_contents( $this->configFile, $cfg )) 
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