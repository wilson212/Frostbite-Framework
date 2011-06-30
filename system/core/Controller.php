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
| Class: Controller
| ---------------------------------------------------------------
|
| Main Controller file. This file will act as a base for the
| whole system
|
*/
namespace System\Core;

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
		
		// Defaults
		$this->_controller = $GLOBALS['controller'];
		$this->_action = $GLOBALS['action'];
		$this->_is_module = $GLOBALS['is_module'];
		
		// Initiate the loader
		$this->load = load_class('Core\\Loader');
		
		// --------------------------------------
		// Autoload the config autoload_helpers |
		// --------------------------------------
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
		
		//-----------------------------------------
		// Autoload the config autoload_libraries |
		//-----------------------------------------
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
		$this->output = load_class('Core\\Output');
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
		// Make sure our data is in an array format
		if(!is_array($data))
		{
			show_error('non_array', array('data', 'Controller::output'));
			$data = array();
		}
		
		// Add the passed variables to the template variables list
		if(count($data) > 0)
		{
			foreach($data as $key => $value)
			{
				$this->variables[$key] = $value;
			}
		}
		
		// Extract the variables so $this->variables[ $var ]
		// becomes just " $var "
		@extract($this->variables);
		
		// Start output bffering
		ob_start();

		// Load the view (Temp... Will actually be alittle more dynamic then this)
		if($this->_is_module == TRUE)
		{
			if(file_exists(APP_PATH . DS . 'modules' . DS . $this->_controller . DS . 'views' . DS . $this->_action . '.php')) 
			{
				include(APP_PATH . DS . 'modules' . DS . $this->_controller . DS . 'views' . DS . $this->_action . '.php');		 
			}
		}
		else
		{
			if(file_exists(APP_PATH . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php')) 
			{
				include(APP_PATH . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php');		 
			}
		}
		
		// End output buffering
		$page = ob_get_contents();
		@ob_end_clean();
		
		// Replace some Global values
		$page = str_replace('{PAGE_LOAD_TIME}', \Benchmark::showTimer('system', 4), $page);
		$page = str_replace('{MEMORY_USAGE}', \Benchmark::memory_usage(), $page);
		
		// Send to the Template parser
		$this->output = load_class('Core\\Output');
		$this->output->send($page);
	}	
}
// EOF