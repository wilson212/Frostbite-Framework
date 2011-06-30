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

class Router
{
	var $_controler = FALSE;
	var $_action = FALSE;
	var $_queryString = FALSE;
	var $_is_module = FALSE;
	
/*
| ---------------------------------------------------------------
| Method: routeUrl()
| ---------------------------------------------------------------
|
| This method analyzes the url to determine the controller / action
| and query string
|
*/	
	function routeUrl() 
	{
		global $routes;
		
		// Get our current url, which is passed on by the htaccess file
		$url = (isset($_GET['url']) ? $_GET['url'] : '');
		
		// Init our initial querystring array.
		$queryString = array();
	
		// If the URI is empty, then load defaults
		if(empty($url)) 
		{
			$controller = $routes['default_controller']; // Default Controller
			$action = $routes['default_action']; // Default Action
		}
		
		// There is a URI, Lets load our controller and action
		else 
		{
			$urlArray = array();
			$urlArray = explode("/",$url);
			$controller = $urlArray[0];
			
			// If there is an action, then lets set that in a variable
			array_shift($urlArray);
			if(isset($urlArray[0]) && !empty($urlArray[0])) 
			{
				$action = $urlArray[0];
				array_shift($urlArray);
			}
			
			// If there is no action, load the default 'index'.
			else 
			{
				$action = $routes['default_action']; // Default Action
			}
			
			// $queryString is what remains
			$queryString = $urlArray;
		}
		
		// Make sure the first character of the controller is not an _ !
		if( strncmp($controller, '_', 1) == 0 || strncmp($action, '_', 1) == 0 )
		{
			show_404();
		}
		
		// DO a controller check, make sure it exists. If not, then we have a 404
		if(!$this->controller_exists($controller))
		{
			show_404();
		}
		
		// Set static Variables
		$this->_controller = $controller;
		$this->_action = $action;
		$this->_queryString = $queryString;
	}
	
/*
| ---------------------------------------------------------------
| Method: get_class()
| ---------------------------------------------------------------
|
| Returns the controller name from the routeUrl method.
|
*/	
	function get_class()
	{		
		return $this->_controller;
	}
	
/*
| ---------------------------------------------------------------
| Method: get_method()
| ---------------------------------------------------------------
|
| Returns the action name from the routeUrl method.
|
*/	
	function get_method()
	{		
		return $this->_action;
	}
	
/*
| ---------------------------------------------------------------
| Method: ge_queryString()
| ---------------------------------------------------------------
|
| Returns the query string name from the routeUrl method.
|
*/	
	function get_queryString()
	{		
		return $this->_queryString;
	}
	
/*
| ---------------------------------------------------------------
| Method: get_type()
| ---------------------------------------------------------------
|
| Returns TRUE of the controller belongs to a module
|
*/	
	function get_type()
	{		
		return $this->_is_module;
	}
	
/*
| ---------------------------------------------------------------
| Method: controller_exists()
| ---------------------------------------------------------------
|
| Checks the controller and Module folders for a certain controller
| returns TRUE if a controller was found, FALSE otherwise
|
| @Param: $name - Name of the controller being searched for.
|
*/
	function controller_exists($name)
	{
		if(file_exists(APP_PATH . DS . 'controllers' . DS . strtolower($name) . '.php')) 
		{
			return TRUE;
		}
		elseif(@file_exists(APP_PATH . DS . 'modules' . DS . strtolower($name) . DS . 'controller.php'))
		{
			$this->_is_module = TRUE;
			return TRUE;
		}
		
		// Neither exists, then no controller found.
		return FALSE;
	}
}
// EOF