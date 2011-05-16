<?php
class Router
{
	var $_controler = FALSE;
	var $_action = FALSE;
	var $_queryString = FALSE;
	
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
	
	function performAction($controller, $action, $queryString = null, $render = 0) 
	{	
		$controllerName = $controller;
		$dispatch = new $controllerName($controller,$action);
		$dispatch->render = $render;
		return call_user_func_array(array($dispatch,$action),$queryString);
	}
	
	/*
	| ---------------------------------------------------------------
	| Method: Init_Engine()
	| ---------------------------------------------------------------
	|
	| Main Calling Funtion. This Method Runs The Whole Show!
	|
	*/
	
	function Init_Engine() 
	{
		global $url;
		global $routes;
	
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
			if(isset($urlArray[0])) 
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
		
		// DO a controller check
		if(!$this->controller_exists($controller))
		{
			$controller = $routes['custom_controller'];
			$action = $routes['custom_action'];
			if(count($queryString) == 0)
			{
				$queryString = array(0 => 'home');
			}
			
			// If custom_controller doesnt exist, then we have a 404
			if(!$this->controller_exists($controller))
			{
				Core::trigger_error(404);
			}
		}
		
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
			Core::trigger_error(3, 'Engine failed to initialize Controller: "'. $controllerName .'", Using action: "'. $action .'"', __FILE__, __LINE__);
		}
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
		elseif(@file_exists(APP_PATH . DS . 'modules' . DS . strtolower($name) . '.php'))
		{
			return TRUE;
		}
		
		// Neither exists, then no controller found.
		return FALSE;
	}
}
// EOF