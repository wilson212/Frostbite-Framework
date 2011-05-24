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
| * USE THIS FILE AS THE BOOTSTRAP *
|
*/

class Frostbite
{
	public $Router;
	protected $dispatch;
	
	function __construct()
	{
		// Initialize the router
		$this->Router = load_class('Router');
	}
	
/*
| ---------------------------------------------------------------
| Method: Init()
| ---------------------------------------------------------------
|
| This is the function that runs the whole show!
|
*/
	function Init()
	{
		// Tell the router to process the URL for us
		$this->Router->routeUrl();
		
		// Initialize some important routing variables
		$controller   = $GLOBALS['controller']   = $this->Router->get_class();
		$action       = $GLOBALS['action']       = $this->Router->get_method();
		$queryString  = $GLOBALS['queryString']  = $this->Router->get_queryString();
		$is_module    = $GLOBALS['is_module']    = $this->Router->get_type();
		
		// Let init a Controller Name
		$controllerName = $controller;
	
		// -------------------------------------------------------------
		// Here we init the actual controller / action into a variable.|
		// -------------------------------------------------------------
		$this->dispatch = new $controllerName();
		
		// After loading the controller, make sure the method exists, or we have a 404
		if(method_exists($controllerName, $action)) 
		{
			// -------------------------------------------------------------------------
			// Here we call the contoller's before, requested, and after action methods.|
			// -------------------------------------------------------------------------
		
			// Call the beforeAction method in the controller.
			$this->performAction($controllerName, "_beforeAction", $queryString);
			
			// HERE is where the magic begins... call the Main APP Controller and method
			$this->performAction($controllerName, $action, $queryString);
			
			// Call the afterAction method in the controller.
			$this->performAction($controllerName, "_afterAction", $queryString);

		} 
		else 
		{
			// If the method didnt exist, then we have a 404
			show_error(404);
		}
	}
	
	/*
	| ---------------------------------------------------------------
	| Method: performAction()
	| ---------------------------------------------------------------
	|
	| @Param: $controller - Name of the controller being used
	| @Param: $action - Action method being used in the controller
	| @Param: $queryString - The query string, basically params for the Action
	| @Param: $render - Whether to render the page and close, or just return the contents
	|
	*/
	
	function performAction($controller, $action, $queryString = null) 
	{	
		if(method_exists($controller, $action)) 
		{
			return call_user_func_array( array($this->dispatch, $action), $queryString );
		}
		return FALSE;
	}
}

// Please use EOF comments instead of closing php tags. For reason / more 
// info: http://codeigniter.com/user_guide/general/styleguide.html#php_closing_tag
// EOF