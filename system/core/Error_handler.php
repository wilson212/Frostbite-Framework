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
*/
namespace System\Core;

class Error_Handler
{
	// The instance of this class 
	private static $instance;

	// Error message,
	var $ErrorMessage;

	// Error file.
	var $ErrorFile;

	// Error line.
	var $ErrorLine;
	
	// Error Level Text.
	var $ErrorLevel;
	
	// Our current language
	var $lang;
	
/*
| ---------------------------------------------------------------
| Function: trigger_error()
| ---------------------------------------------------------------
|
| Main error handler. Triggers, logs, and shows the error message
|
| @Param: $lvl - Level of the error
| @Param: $message - Error message
| @Param: $file - The file reporting the error
| @Param: $line - Error line number
| @Param: $errno - Error number
| @Param: $backtrace - Backtrace information if any
|
*/
	function trigger_error($lvl, $message = 'none', $file = "", $line = 0, $backtrace = NULL)
	{
		// Language setup
		$this->lang = strtolower( config('core_language', 'Core') );
		
		// fill attributes
		$this->ErrorMessage = $message;
		$this->ErrorFile = $file; //str_replace(ROOT . DS, '', $file);
		$this->ErrorLine = $line;

		// Get our level text
		switch($lvl) 
		{
			case 0:
				$this->ErrorLevel = 'NOTICE';
				$config_level = 3;
				break;
				
			case 1:
				$this->ErrorLevel = 'WARNING';
				$config_level = 3;
				break;
				
			case 2:
				$this->ErrorLevel = 'MySQL ERROR';
				$config_level = 2;
				break;
				
			case 3:
				$this->ErrorLevel = 'ERROR';
				$config_level = 1;
				break;
				
			case 404:
				include(SYSTEM_PATH . DS . 'pages' . DS . $this->lang . DS . '404.php');
				die();
		}

		// log error if enabled
		if( config('log_errors', 'Core') == 1 )
		{
			$this->log_error();
		}
		
		// If the error is more severe then the config level, show it
		if( config('error_display_level', 'Core') >= $config_level )
		{
			// build nice error page
			$this->build_error_page();			
		}
	}
	
/*
| ---------------------------------------------------------------
| Function: build_error_page()
| ---------------------------------------------------------------
|
| Builds the error page and displays it
|
*/	
	function build_error_page()
	{
		// Capture the template using Output Buffering
		ob_start();
		include(SYSTEM_PATH . DS . 'pages' . DS . $this->lang . DS . 'error.php');
		$page = ob_get_contents();
		@ob_end_clean();
		
		// alittle parsing
		$page = str_replace("{ERROR_LEVEL}", ucfirst(strtolower($this->ErrorLevel)), $page);
		$page = str_replace("{MESSAGE}", $this->ErrorMessage, $page);
		$page = str_replace("{FILE}", $this->ErrorFile, $page);
		$page = str_replace("{LINE}", $this->ErrorLine, $page);
		
		// Spit the page out
		eval('?>'.$page.'<?');		
		die();
	}

/*
| ---------------------------------------------------------------
| Function: log_error()
| ---------------------------------------------------------------
|
| Logs the error message in the error log
|
*/	
	function log_error()
	{
		// Check for a error file, determines our log message
		if($this->ErrorFile != "")
		{
			$err_message = date('Y-m-d H:i:s')." -- ".$this->ErrorLevel . $this->ErrorMessage ." - File: ".$this->ErrorFile." on Line:".$this->ErrorLine."\n";
		}
		else
		{
			$err_message = date('Y-m-d H:i:s')." -- ". $this->ErrorLevel . $this->ErrorMessage ."\n";
		}
		$log = @fopen(SYSTEM_PATH . DS . 'logs' . DS . 'error.log', 'a');
		@fwrite($log, $err_message);
		@fclose($log);
	}
	
/*
| ---------------------------------------------------------------
| Function: custom_error_handler()
| ---------------------------------------------------------------
|
| Php uses this error handle instead of the default one
|
*/
	public static function custom_error_handler($errno, $errstr, $errfile, $errline)
	{
		if(!$errno) 
		{
			// This error code is not included in error_reporting
			return;
		}
		
		$self = self::singleton();

		// Pass the error onto the internal trigger_error method
		switch($errno) 
		{
			case E_USER_ERROR:
				$self->trigger_error(3, $errstr, $errfile, $errline);
				break;

			case E_USER_WARNING:
				$self->trigger_error(1, $errstr, $errfile, $errline);
				break;
				
			case E_USER_NOTICE:
				$self->trigger_error(0, $errstr, $errfile, $errline);
				break;
			
			case E_ERROR:
				$self->trigger_error(3, $errstr, $errfile, $errline);
				break;
				
			case E_WARNING:
				$self->trigger_error(1, $errstr, $errfile, $errline);
				break;
				
			case E_NOTICE:
				$self->trigger_error(0, $errstr, $errfile, $errline);
				break;
				
			case E_STRICT:
				$self->trigger_error(1, $errstr, $errfile, $errline);
				break;

			default:
				$self->trigger_error(3, $errstr, $errfile, $errline);
				break;
		}

		// Don't execute PHP internal error handler
		return true;
	}
	
/*
| ---------------------------------------------------------------
| Method: singlton()
| ---------------------------------------------------------------
|
| Allows access to the none static methods in the class
|
*/ 

    public static function singleton() 
    {
        if(!isset(self::$instance))
        {
			self::$instance = new self();
        }
        return self::$instance;
    }
}
// EOF