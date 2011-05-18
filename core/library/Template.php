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
	
	function __construct($args) 
	{
		$this->template_name = 'default';
		$this->_controller = $args[0];
		$this->_action = $args[1];
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
	
    function render($doNotRenderHeader = 0) 
	{
		extract($this->variables);
		
		// Start output bffering
		ob_start();
		
		// Load the header
		if($doNotRenderHeader == 0) 
		{			
			if(file_exists(ROOT . DS . 'templates' . DS . $this->template_name . DS . 'header.php')) 
			{
				include(ROOT . DS . 'templates' . DS . $this->template_name . DS . 'header.php');
			} 
			else 
			{
				include(ROOT . DS . 'application' . DS . 'views' . DS . 'header.php');
			}
		}

		// Load the view (Temp... Will actually be alittle more dynamic then this)
		if(file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php')) 
		{
			include(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php');		 
		}
			
		// Load the footer
		if ($doNotRenderHeader == 0) 
		{
			if(file_exists(ROOT . DS . 'templates' . DS . $this->template_name . DS . 'footer.php')) 
			{
				include(ROOT . DS . 'templates' . DS . $this->template_name . DS . 'footer.php');
			}
			else 
			{
				include(ROOT . DS . 'application' . DS . 'views' . DS . 'footer.php');
			}
		}
		
		// End output buffering, spit out the page.
		ob_end_flush();
    }
}
// EOF