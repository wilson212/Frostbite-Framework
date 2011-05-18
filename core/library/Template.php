<?php
/*
| ---------------------------------------------------------------
| Class: Template
| ---------------------------------------------------------------
|
| Main template parsing / output file. NEEDS ALOT OF WORK!
|
*/

class Template 
{	
	protected $variables = array();
	
	function __construct() 
	{
		$this->template_name = 'default';
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
| Function: view()
| ---------------------------------------------------------------
|
| Main Output / Template Building Class
|
| @Param: $name - Name of the variable to be set
| @Param: $data - Specific data to be passed to the parser.
|
*/
	function view( $name, $data = null ) 
	{
		extract($this->variables);
		if( is_array($data) ) 
		{
			extract($data);
		}
		
		// Start Output buffering to catch the execution of the view file.
		ob_start();
		include( APP_PATH . DS .'views'. DS . $this->_controller . DS . $name );
		$buffer = ob_get_contents();
		@ob_end_clean();
		
		// Parser the Data if enabled!
		// 
	}

/*
| ---------------------------------------------------------------
| Function: render()
| ---------------------------------------------------------------
|
| This method displays the output. NOTE: this function is temporary
| until i work on the template class some more.
|
| @Param: $doNotRenderHeader - if 1, the header / footer will not show
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
		
		// Just fo testing purposes
		$render = 1;
		
		// Start output bffering
		ob_start();
		
		// Load the header
		if($render == 1) 
		{			
			if(file_exists(APP_PATH . DS . 'templates' . DS . $this->template_name . DS . 'header.php')) 
			{
				include(APP_PATH . DS . 'templates' . DS . $this->template_name . DS . 'header.php');
			} 
			else 
			{
				include(APP_PATH . DS . 'views' . DS . 'header.php');
			}
		}

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
		if($render == 1) 
		{
			if(file_exists(APP_PATH . DS . 'templates' . DS . $this->template_name . DS . 'footer.php')) 
			{
				include(APP_PATH . DS . 'templates' . DS . $this->template_name . DS . 'footer.php');
			}
			else 
			{
				include(APP_PATH . DS . 'views' . DS . 'footer.php');
			}
		}
		
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