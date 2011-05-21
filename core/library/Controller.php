<?php
/*
| ---------------------------------------------------------------
| Class: Controller
| ---------------------------------------------------------------
|
| Main Controller file. This file will act as a base for the
| whole system
|
*/
class Controller
{
	
	public $_controller;
	public $_action;
	private static $instance;


/*
| ---------------------------------------------------------------
| Constructer: __construct()
| ---------------------------------------------------------------
|
| Initiates the self::instance for the ability to use the
| controller as a base for outside files, or.. as codeignitor
| puts it, a "superobject"
|
*/
	function __construct() 
	{		
		// Set the instance here
		self::$instance = $this;
		
		// Initiate the loader
		$this->load = load_class('Loader');
		
		// Autoload the config autoload_helpers
		$libs = config('autoload_helpers', 'Core');
		if(count($libs) > 1)
		{
			foreach($libs as $lib)
			{
				$this->load->helper($lib);
			}
		}
		elseif(count($libs) == 1)
		{
			$this->load->helper($libs[0]);
		}
		
		// Autoload the config autoload_libraries
		$libs = config('autoload_libraries', 'Core');
		if(count($libs) > 1)
		{
			foreach($libs as $lib)
			{
				$this->load->library($lib);
			}
		}
		elseif(count($libs) == 1)
		{
			$this->load->library($libs[0]);
		}
		
		/*
			load module config file if there is one
			config values will be merged with the site config
			and accessed as config(' $item ');
		*/
		if($GLOBALS['is_module'] == TRUE)
		{
			load_module_config($GLOBALS['controller']);
		}
		
		// Default Template Init.
		$this->load->library('Template');
	}
	
/*
| ---------------------------------------------------------------
| Function: get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding this controller class to an outside file
|
*/	
	public static function get_instance()
	{
		return self::$instance;
	}

/*
| ---------------------------------------------------------------
| Function: _beforeAction()
| ---------------------------------------------------------------
|
| Mini hook of code to be called right before the action
|
*/	
	function _beforeAction() {}

/*
| ---------------------------------------------------------------
| Function: _afterAction()
| ---------------------------------------------------------------
|
| Mini hook of code to be called right after the action
|
*/	
	function _afterAction() {}
	

/*
| ---------------------------------------------------------------
| Function: output()
| ---------------------------------------------------------------
|
| A convenient way to output the screen.
|
*/	
	function output($data = array()) 
	{
		$this->template->render($data);
	}	
}
// EOF