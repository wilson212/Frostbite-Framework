<?php
class Core
{	
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
|
*/
	static function trigger_error($lvl, $message = 'Not Specified', $file = "none", $line = 0, $errno = 0)
	{
		$Config = new Config;		
		switch($lvl) 
		{
			case 0:
				$lvl_txt = 'NOTICE: ';
				$config_level = 3;
				break;
			case 1:
				$lvl_txt = 'WARNING: ';
				$config_level = 3;
				break;
			case 2:
				$lvl_txt = 'MySQL ERROR: ';
				$config_level = 2;
				break;
			case 3:
				$lvl_txt = 'ERROR: ';
				$config_level = 1;
				break;
			case 404:
				include(CORE_PATH . DS . 'pages' . DS .'404.php');
				die();
		}
		
		// Do we log the error?
		if( config('log_errors', 'Core') == 1 )
		{
			if($file != "none")
			{
				$err_message = date('Y-m-d H:i:s')." -- ".$lvl_txt . $message." - File: ".$file." on Line:".$line."\n";
			}
			else
			{
				$err_message = date('Y-m-d H:i:s')." -- ". $lvl_txt . $message."\n";
			}
			$log = @fopen(CORE_PATH . DS . 'logs' . DS . 'error.log', 'a');
			@fwrite($log, $err_message);
			@fclose($log);
		}
		
		// If the error is more severe then the config level, show it
		if(config('error_reporting_level', 'Core') >= $config_level)
		{
			// Empty out the buffers so we dont see what have processed
			@ob_end_clean();
			
			// Show the error page and end processing
			include(CORE_PATH . DS . 'pages' . DS .'error.php');
			die();
		}
	}
	
/*
| ---------------------------------------------------------------
| Function: custom_error_handler(args)
| ---------------------------------------------------------------
|
| Php uses this error handle instead of the default one
|
*/
	public static function custom_error_handler($errno, $errstr, $errfile, $errline)
	{
		if(!(error_reporting() & $errno)) 
		{
			// This error code is not included in error_reporting
			return;
		}

		/*
			NOTE: When using this function statically ( Outside this file as an error handler )
			You must use it as Core::trigger_error() instead of $this->trigger_error()!
		*/
		switch($errno) 
		{
			case E_USER_ERROR:
				Core::trigger_error(3, $errstr, $errfile, $errline);
				break;

			case E_USER_WARNING:
				Core::trigger_error(1, $errstr, $errfile, $errline);
				break;
				
			case E_USER_NOTICE:
				Core::trigger_error(0, $errstr, $errfile, $errline);
				break;
			
			case E_ERROR:
				Core::trigger_error(3, $errstr, $errfile, $errline);
				break;
				
			case E_WARNING:
				Core::trigger_error(1, $errstr, $errfile, $errline);
				break;
				
			case E_NOTICE:
				Core::trigger_error(0, $errstr, $errfile, $errline);
				break;
				
			case E_STRICT:
				Core::trigger_error(1, $errstr, $errfile, $errline);
				break;

			default:
				Core::trigger_error(3, $errstr, $errfile, $errline);
				break;
		}

		/* Don't execute PHP internal error handler */
		return true;
	}
}
// EOF