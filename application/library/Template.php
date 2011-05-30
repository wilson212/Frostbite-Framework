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
		
		// Set defaults
		$this->path = APP_PATH . DS . 'templates';
		$this->template = "default";
		$this->trigger = "FB";
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
| Function: load(path)
| ---------------------------------------------------------------
|
| Checks whether there is a template file and if its readable.
| Stores contents of file if read is successfull
|
| @Param: $file - Full file name. Can also be: "path/to/file.ext"
|
*/
	function load($file) 
	{
		$template_file = $this->path . $this->template . DS . $file;
		if(!file_exists($template_file)) 
		{
			show_error(3, "Template file \"". $template_file ." not found \"", __FILE__, __LINE__);
		}
		
		$lines = @file($template_file);
		if(!$lines) 
		{
			show_error(3, "Template file \"". $template_file ."\" is empty or can not be read", __FILE__, __LINE__);
		}

		$file = join('' , $lines);
		return $file;
	}
	
/*
| ---------------------------------------------------------------
| Function: set_template(template)
| ---------------------------------------------------------------
|
| Sets the template
|
| @Param: $key - The defined Template name
|
*/
	function set_template($template) 
	{
		$this->template = $template;
		return TRUE;
	}
	
/*
| ---------------------------------------------------------------
| Function: compile()
| ---------------------------------------------------------------
|
| This function pre-builds the page and sets the order at which
| things are loaded. This method will load the modules set in
| the template, load the widgets and order them correctly, and
| process the <TEMPLATE> blocks.
|
*/
	function compile() 
	{
		
		$source = ob_get_contents;
		while(preg_match("~".$this->l_delim . $this->trigger.":(.*)". $this->r_delim ."~iUs", $loop, $M))
		{	
			// Assign the code as $main
			$main = $replace[1];
			
			// Check for another :
			if(strpos($main, ":") !== false)
			{
				$exp = explode(":", $main);
				
				switch($main[0])
				{
					case "load":
						$content = $this->load($main[1]);
						break;
				}
			}
			
			// --------- Start Finding Stuff! --------- //								
			
			// strip parsed Template block
			$source = str_replace($replace[0], $main, $source);
		}
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