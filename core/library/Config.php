<?php
/*
| ---------------------------------------------------------------
| Class: Config()
| ---------------------------------------------------------------
|
| Main Config class. used to load, set, and save variables used
| in the config file.
|
*/

class Config
{	
	var $data = array();
	
	function Config() 
	{
		$this->configFile = APP_PATH . DS . 'config' . DS . 'config.php';
		$this->path_protectedconf = APP_PATH . DS . 'config' . DS . 'database.config.php';
		$this->Load();
	}
	
/*
| ---------------------------------------------------------------
| Method: Load()
| ---------------------------------------------------------------
|
| Gets the defined variables in the config file
|
*/
	function Load() 
	{
		if(file_exists($this->configFile)) 
		{
			include( $this->configFile );
			$vars = get_defined_vars();
			foreach( $vars as $key => $val ) 
			{

				$this->data[$key] = $val;
			}
			return true;
		} 
		else 
		{
			return FALSE;
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
	function get($key) 
	{
		if(isset($this->data[$key])) 
		{
			return $this->data[$key];
		}
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
	function getDbInfo($key) 
	{
		include($this->path_protectedconf);
		return $db[$key];
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
|
*/
	function set($key, $val) 
	{
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
		
		// Copy the current config file, and make a new config file for backup.
		// Put the current config contents in the backup config file
		@copy($this->configFile, $this->configFile.'.bak');

		if(@file_put_contents( $this->configFile, $cfg )) 
		{
			return true;
		} 
		else 
		{
			return false;
		}
	}
}
?>