<?php
/* 
| --------------------------------------------------------------
| 
| SimpleCMS Template Engine
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:	Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|
| ---------------------------------------------------------------
| Class: MY_Template
| ---------------------------------------------------------------
|
| Main template parsing / output file.
|
*/
namespace Application\Library;

class Template
{	
	protected $variables = array();
	var $template = array();
	var $trigger = "FB";
	var $l_delim = '{';
	var $r_delim = '}';
	var $parsed = false;
	
	function __construct() 
	{
		$this->_controller = $GLOBALS['controller'];
		$this->_action = $GLOBALS['action'];
		$this->_is_module = $GLOBALS['is_module'];
		
		// Can be set later
		$this->set_template( config('default_template') );
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
		$template_file = $this->template['path'] . DS . $file;
		
		// Fix a correction with some servers being real sensative to the DS
		// As well as having different DS's
		$template_file = str_replace(array('\\', '/'), DS, $template_file);
		
		// Make sure the file exists!
		if(!file_exists($template_file)) 
		{
			show_error(3, "Template file \"". $template_file ." not found \"", __FILE__, __LINE__);
		}

		// Get the file contents and return
		return file_get_contents($template_file);
	}
	
/*
| ---------------------------------------------------------------
| Function: set_template()
| ---------------------------------------------------------------
|
| Sets the template
|
| @Param: $name - The defined Template name
| @Param: $type - 'site' or 'acp'
| @Param: $path - The path to the template folder, does NOT include 
|	type, or template name. No trailing slash
|
*/
	function set_template($name, $type = 'site', $path = NULL) 
	{
		$this->template['name'] = $name;
		if($path !== NULL)
		{
			// DS correction for the users server
			$path = str_replace(array('\\', '/'), DS, $path);
			$this->template['path'] = ROOT . DS . $path . DS . $type . DS . $name;
			$this->template['http_path'] = BASE_URL . $path ."/". $type ."/" . $name ."/";
		}
		else
		{
			$this->template['path'] = APP_PATH . DS . 'templates' . DS . $type . DS . $name;
			$this->template['http_path'] = BASE_URL . "application/templates/". $type ."/". $name ."/";
		}
		
		// Make sure the file exists!
		if(!file_exists($this->template['path'] . DS . 'template.php')) 
		{
			show_error(3, 
				"Template \"".$name."\" does not exist in the \"".$type."\" folder located in \""
				.$this->template['path']."\", or is missing the \"template.php\" file.", 
				__FILE__,
				__LINE__
			);
			return FALSE;
		}
		return TRUE;
	}

/*
| ---------------------------------------------------------------
| Function: set_delimiters()
| ---------------------------------------------------------------
|
| Sets the template delimiters for psuedo blocks
|
| @Param: $l - The left delimiter
| @Param: $r - The right delimiter
|
*/	
	function set_delimiters($l = '{', $r = '}')
	{
		$this->l_delim = $l;
		$this->r_delim = $r;
	}
	
/*
| ---------------------------------------------------------------
| Function: get_page_contents()
| ---------------------------------------------------------------
|
| Gets the current page contents, checks if the template has
| a custom view for the page we are viewing
|
*/	
	function get_page_contents()
	{
		// First we check to see if the template has a custom view for this page
		$file = $this->template['path'] . DS . 'views' . DS . $this->_controller . DS . $this->_action .'.php';
		if(file_exists($file))
		{
			return file_get_contents($file);
		}
		
		// No template custom view, load default
		else
		{
			return file_get_contents(APP_PATH . DS . 'views' . DS . $this->_controller . DS . $this->_action .'.php');
		}
	}
	
/*
| ---------------------------------------------------------------
| Function: compile()
| ---------------------------------------------------------------
|
| This method compiles the template page by processing the template
| trigger events such as partial loading etc etc
|
*/
	function compile() 
	{	
		// Get our skeleton file
		$source = $this->load('template.php');
		
		// Strip custom comment blocks
		while(preg_match('/<!--#.*#-->/iUs', $source, $replace)) 
		{
			$source = str_replace($replace[0], '', $source);
		}
		
		// Load page contents so they can be parsed as well!
		$source = str_replace("{PAGE_CONTENTS}", $this->get_page_contents(), $source); 
		
		// Loop through each match of { TRIGGER : ... }
		while(preg_match("~".$this->l_delim . $this->trigger .":(.*)". $this->r_delim ."~iUs", $source, $replace))
		{	
			// Assign the matches as $main
			$main = trim($replace[1]);
			
			// === Here we figure out what and how we are replacing === //
			
			// Check for another : ... If there is one, its a task, else a var
			if(strpos($main, ":") !== false)
			{
				$exp = explode(":", $main);
				
				// Figure out what the task is EI: load
				switch($exp[0])
				{
					case "load":
						$content = $this->load($exp[1]);
						break;
						
					case "template":
						switch($exp[1])
						{
							case "name":	
								$content = $this->template['name'];
								break;
								
							case "path":	
								$content = $this->template['path'];
								break;
								
							default:
								show_error(3, "Unknown command \" ". $exp[1] ." \" ", __FILE__, __LINE__);
								break;
								
						}
						break;
						
					case "constant":
						( defined($exp[1]) ) ? $content = constant($exp[1]) : $content = $this->l_delim . $exp[1] . $this->r_delim;
						break;
	
					default:
						show_error(3, "Unknown command \" ". $exp[0] ." \" ", __FILE__, __LINE__);
						break;
				}
			}
			
			// strip parsed Template block
			$source = str_replace($replace[0], $content, $source);
		}
		
		$this->source = $source;
		return;
	}

/*
| ---------------------------------------------------------------
| Function: parse()
| ---------------------------------------------------------------
|
| This method uses all defined template assigned variables
| to loop through and replace the Psuedo blocks that contain
| variable names
|
*/	
	function parse()
	{
		// store the vars into $data, as its easier then $this->variables
		$data = $this->variables;
		
		// Do a search and destroy or psuedo blocks
		foreach($data as $key => $value)
		{
			// If $value is an array, we need to process it as so
			if(is_array($value))
			{
				// Create our array block regex
				$regex = $this->l_delim . $key . $this->r_delim . "(.*)". $this->l_delim . '/' . $key . $this->r_delim;
				
				// Check for array blocks, if so then parse_pair
				if(preg_match("~" . $regex . "~iUs", $this->source, $matches))
				{
					while(preg_match("~" . $regex . "~iUs", $this->source, $match))
					{
						// Parse pair: Source, Match to be replaced, With what are we replacing?
						$replacement = $this->parse_pair($match[1], $key, $value);
						$this->source = str_replace($match[0], $replacement, $this->source);
					}
				}
				
				// Create our array regex
				$key = $key .".";
				$regex = $this->l_delim . $key . "(.*)".$this->r_delim;

				// now see if there are any arrays
				if(preg_match("~" . $regex . "~iUs", $this->source, $matches))
				{
					while(preg_match("~" . $regex . "~iUs", $this->source, $match))
					{
						$replacement = $this->parse_array($match[1], $value);
						
						// Check for a false reading
						if($replacement === FALSE)
						{
							$replacement = "<<!". $key . $match[1] ."!>>";
						}
						$this->source = str_replace($match[0], $replacement, $this->source);
					}
				}
			}
			
			// Parse single
			else
			{
				while(preg_match("~" . $this->l_delim . $key . $this->r_delim . "~iUs", $this->source, $match))
				{
					$this->source = str_replace($match[0], $value, $this->source);
				}
			}
		}

		// Lets find and replace constants
		$const = get_defined_constants(true);
		if(count($const['user'] > 0))
		{
			foreach($const['user'] as $key => $value)
			{
				if(preg_match("~" . $this->l_delim .  $key . $this->r_delim . "~iUs", $this->source, $match))
				{
					$this->source = str_replace($match[0], $value, $this->source);
				}
			}
		}
		
		// Refresh of tags that were changed as a result of unset / mis-typed vars
		$this->source = str_replace("<<!", $this->l_delim, $this->source);
		$this->source = str_replace("!>>", $this->r_delim, $this->source);
		
		// we are done
		return;
	}
	
/*
| ---------------------------------------------------------------
| Function: parse_array()
| ---------------------------------------------------------------
|
| Parses an array such as {user.userinfo.username}
|
| @Param: $key - The full unparsed array ( { something.else} )
| @Param: $array - The actual array that holds the value of $key
|
*/		
	function parse_array($key, $array)
	{
		// Have the default return as $key
		$replacement = false;
		
		// Check to see if this is even an array first
		if(!is_array($array))
		{
			return $array;
		}

		// Check if this is a multi-dimensional array
		if(strpos($key, '.') !== false)
		{
			$args = explode('.', $key);
			$s_key = '';
			foreach($args as $arg)
			{
				if(!is_numeric($arg))
				{
					$s_key .= '[\''. $arg .'\']';
				}
				else
				{
					$s_key .= '['. $arg .']';
				}
			}
			
			// Check if variable exists in $val
			$isset = eval('if(isset($array'. $s_key .')) return 1; return 0;');
			if($isset == 1)
			{
				$replacement = eval('return $array'. $s_key .';');
			}
			else
			{
				$last = array_reverse($args);
				show_error(1, "Unknown template variable \"". $last[0] ."\" in array \"".$args[0]."\"", __FILE__, __LINE__);
			}
		}
		
		// Just a simple 1 stack array
		else
		{	
			// Check if variable exists in $array
			if(isset($array[$key]))
			{
				$replacement = $array[$key];
			}
			else
			{
				show_error(1, "Unknown template variable ".$key."", __FILE__, __LINE__);
			}
		}
		
		return $replacement;
	}
	
/*
| ---------------------------------------------------------------
| Function: parse_pair()
| ---------------------------------------------------------------
|
| Parses array blocks (  {key} ... {/key} ), sort of acts like 
| a foreach loop
|
| @Param: $match - The preg_match of the block {key} (what we need) {/key}
| @Param: $key_finder - The {key} ...
| @Param: $val - The array that contains the variables inside the blocks
|
*/		
	function parse_pair($match, $key_finder, $val)
	{		
		// Init the emtpy main block replacment
		$str = '';
		$key_finder = $key_finder .".";
		
		// Process the block loop here, We need to process each array $val
		foreach($val as $k => $v)
		{
			// Setup a few variables to tell what loop number we are on
			$block = str_replace("{#}", ++$k, $match);
			$v['#'] = ++$k;
			
			// Now we have an individiual block match foreach $val, now
			// lets loop through this block and replace psuedo blocks
			while(preg_match("~".$this->l_delim . $key_finder . "(.*)". $this->r_delim ."~iUs", $block, $replace))
			{
				// Assign the matches as $main
				$key = trim($replace[1]);
				
				// Parse as an array just in case it is. If not, it will return the value
				// of the non array anyways, so its a win win situation
				$main = $this->parse_array($key, $v);
				
				/*
					If we got a false return, the variable does not exists at all!
					We need to at least replace with something so we dont get an 
					infininte loop. So we add << $main >> to be replaced later
				*/
				if($main === false) 
				{ 
					$block = str_replace($replace[0], "<<!".$key."!>>", $block); 
				}
				else
				{
					$block = str_replace($replace[0], $main, $block);
				}
			}
			
			$str .= $block;
		}
		return $str;
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
		
		// Default constant for the http path to the template root folder
		define('TEMPLATE_URL', $this->template['http_path']);

		// Extract the variables so $this->variables[ $var ]
		// becomes just " $var "
		@extract($this->variables);

		// Compile the page
		$this->compile();

		// Run through the parser if wanted
		if( config('enable_template_parser', 'Core') == TRUE )
		{
			$this->parse();
		}

		// Replace some Global values
		$page = $this->source;
		$page = str_replace('{PAGE_LOAD_TIME}', Benchmark::showTimer('system'), $page);
		$page = str_replace('{MEMORY_USAGE}', Benchmark::memory_usage(), $page);

		// Spit out the page
		eval('?>'.$page.'<?');
	}
}
// EOF