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
| @Param: $controller - The controller passed from the router
| @Param: $action - Method to be used in the controller
|
*/
	function __construct($controller, $action) 
	{
		// Set the instance here
		self::$instance = $this;
		
		// Initiate the loader
		$this->load = load_class('Loader');
		
		// Setup the config class
		$this->load->library('Config');
		
		// Lets variablize the controller and action globaly
		$this->_controller = ucfirst($controller);
		$this->_action = $action;
		
		// Default template init.
		$this->load->library('Template', array($controller, $action));
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
	function _afterAction() 
	{
		$this->output();
	}
	
/*
| ---------------------------------------------------------------
| Function: set()
| ---------------------------------------------------------------
|
| This method sets variables to be replace in the template system
|
| @Param: $name - Name of the variable to be set
| @Param: $value - The value of the variable
|
*/	
	function set($name, $value) 
	{
		$this->template->set($name, $value);
	}

/*
| ---------------------------------------------------------------
| Function: output()
| ---------------------------------------------------------------
|
| Will be removed! This will be set somewhere in the bootstrap file,
| or in the engine.
|
*/	
	function output($data = array()) 
	{
		$this->template->render($data);
	}	
}
// EOF