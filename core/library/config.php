<?php
// Config Handling class
class Config
{	
	var $data = array();
	
	function Config() 
	{
		$this->configFile = APP_PATH . DS . 'config' . DS . 'config.php';
		$this->path_protectedconf = APP_PATH . DS . 'config' . DS . 'database.config.php';
		$this->Load();
	}
	
//	************************************************************
//	Loads the config files.

	function Load() 
	{
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
			return true;
		} 
		else 
		{
			return FALSE;
		}
	}
	
//	************************************************************
// Returns the config variable requested

	function get($key) 
	{
		if(isset($this->data[$key])) 
		{
			return $this->data[$key];
		}
	}
	
//	************************************************************
// Returns the requested DB key from the DB config file

	function getDbInfo($key) 
	{
		include($this->path_protectedconf);
		return $db[$key];
	}
	
//	************************************************************
// Sets a variable

	function set($key, $val) 
	{
		$this->data[$key] = $val;
	}

//	************************************************************
// Saves all set config variables to the config file, and makes a backup of the current config file

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
		
		if(phpversion() < 5) # If php version is less than 5
		{
			$file = @fopen($this->configFile, 'w');
			if($file === false) 
			{
				return false;
			} 
			else 
			{
				@fwrite($file, $cfg);
				@fclose($file);
				return true;
			}
		} 
		else 
		{
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
}
?>