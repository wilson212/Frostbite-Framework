<?php
/*
| ---------------------------------------------------------------
| Class: Template
| ---------------------------------------------------------------
|
| Main template parsing / output file. This file is meant to be
| extended!
|
*/

class Template 
{	
	protected $variables = array();
	
	function __construct() 
	{
		$this->_controller = $GLOBALS['controller'];
		$this->_action = $GLOBALS['action'];
		$this->_is_module = $GLOBALS['is_module'];
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
		$this->variables[$name] = $value;
	}

/*
| ---------------------------------------------------------------
| Function: render()
| ---------------------------------------------------------------
|
| This method displays the page. It loads the header, footer, and
| view of the page.
|
| @Param: $data - An array of variables that are to be passed to 
| 	the View
|
*/
	
    function render($data = array()) 
	{
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
		
		// Load the header	
		include(APP_PATH . DS . 'views' . DS . 'header.php');

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
			
		// Load the footer
		include(APP_PATH . DS . 'views' . DS . 'footer.php');
		
		// End output buffering
		$page = ob_get_contents();
		@ob_end_clean();
		
		// Replace some Global values
		$page = str_replace('{PAGE_LOAD_TIME}', Benchmark::showTimer('system'), $page);
		$page = str_replace('{MEMORY_USAGE}', Benchmark::memory_usage(), $page);
		echo $page;
    }
}
// EOF