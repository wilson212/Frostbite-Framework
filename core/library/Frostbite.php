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
| License: 		???
|
| * USE THIS FILE AS THE BOOTSTRAP *
|
| We will use this very class, as the Super Object!
|
*/

class Frostbite
{
	function __construct()
	{
		// Setup the config class, the autoloader will auto include the class file
		$this->Config = new Config();

		// Initialize the router
		$this->Router = new Router();
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
		$controller = $this->Router->getController();
		$action = $this->Router->getAction();
		$queryString = $this->Router->getQueryString();
		
		// Let init a Controller Name
		$controllerName = $controller;
	
		// -------------------------------------------------------------
		// Here we init the actual controller / action into a variable.|
		// -------------------------------------------------------------
		$dispatch = new $controllerName($controller, $action);
		
		// After loading the controller, make sure it loaded correctly or spit an error
		if((int)method_exists($controllerName, $action)) 
		{
			// Check to see if there is a "beforeAction" method, if so call it!
			if((int)method_exists($controllerName, "beforeAction")) 
			{
				call_user_func_array(array($dispatch,"beforeAction"), $queryString);
			}
			
			// HERE is where the magic begins... call the Main APP Controller
			call_user_func_array(array($dispatch,$action), $queryString);
			
			// Check to see if there is a "afterAction" method, if so call it!
			if((int)method_exists($controllerName, "afterAction")) 
			{
				call_user_func_array(array($dispatch,"afterAction"), $queryString);
			}
		} 
		else 
		{
			show_error(3, 'Engine failed to initialize Controller: "'. $controllerName .'", Using action: "'. $action .'"', __FILE__, __LINE__);
		}
	}
}

// Please use EOF comments instead of closing php tags. For reason / more 
// info: http://codeigniter.com/user_guide/general/styleguide.html#php_closing_tag
// EOF