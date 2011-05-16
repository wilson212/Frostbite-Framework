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
	
	protected $_controller;
	protected $_action;
	protected $_template;
	private static $instance;

	public $doNotRenderHeader;
	public $render;

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
		// Important for the gateway
		self::$instance =& $this;
		
		// Lets variablize the controller and action globaly
		$this->_controller = ucfirst($controller);
		$this->_action = $action;
		
		// Load the Loader class, probably will move to bootstrap
		$this->load = new Loader;
		
		// Default template init.
		$this->doNotRenderHeader = 0;
		$this->render = 1;
		$this->_template = new Template($controller, $action);

	}
	
/*
| ---------------------------------------------------------------
| Function: &get_instance()
| ---------------------------------------------------------------
|
| Gateway to adding this controller class to an outside file
|
*/	
	public static function &get_instance()
	{
		return self::$instance;
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
		$this->_template->set($name, $value);
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
	function output() 
	{
		if($this->render) 
		{
			$this->_template->render($this->doNotRenderHeader);
		}
	}	
}
// EOF