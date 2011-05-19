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
		
		// Default template init.
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