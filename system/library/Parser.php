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
namespace System\Library;

class Parser
{	
	var $l_delim = '{';
	var $r_delim = '}';
	
	function __construct() 
	{
		$this->_controller = $GLOBALS['controller'];
		$this->_action = $GLOBALS['action'];
		$this->_is_module = $GLOBALS['is_module'];
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
| Function: parse()
| ---------------------------------------------------------------
|
| This method uses all defined template assigned variables
| to loop through and replace the Psuedo blocks that contain
| variable names
|
| @Param: $source - Source of the page that we are parsing
| @Param: $data - An array of variables to be parsed
|
*/	
	function parse($source, $data = array())
	{
		// Do a search and destroy or psuedo blocks
		foreach($data as $key => $value)
		{
			// If $value is an array, we need to process it as so
			if(is_array($value))
			{
				// Create our array block regex
				$regex = $this->l_delim . $key . $this->r_delim . "(.*)". $this->l_delim . '/' . $key . $this->r_delim;
				
				// Check for array blocks, if so then parse_pair
				if(preg_match("~" . $regex . "~iUs", $source, $matches))
				{
					while(preg_match("~" . $regex . "~iUs", $source, $match))
					{
						// Parse pair: Source, Match to be replaced, With what are we replacing?
						$replacement = $this->parse_pair($match[1], $key, $value);
						$source = str_replace($match[0], $replacement, $source);
					}
				}
				
				// Create our array regex
				$key = $key .".";
				$regex = $this->l_delim . $key . "(.*)".$this->r_delim;

				// now see if there are any arrays
				if(preg_match("~" . $regex . "~iUs", $source, $matches))
				{
					while(preg_match("~" . $regex . "~iUs", $source, $match))
					{
						$replacement = $this->parse_array($match[1], $value);
						
						// Check for a false reading
						if($replacement === FALSE)
						{
							$replacement = "<<!". $key . $match[1] ."!>>";
						}
						$source = str_replace($match[0], $replacement, $source);
					}
				}
			}
			
			// Parse single
			else
			{
				while(preg_match("~" . $this->l_delim . $key . $this->r_delim . "~iUs", $source, $match))
				{
					$source = str_replace($match[0], $value, $source);
				}
			}
		}

		// Lets find and replace constants
		$const = get_defined_constants(true);
		if(count($const['user'] > 0))
		{
			foreach($const['user'] as $key => $value)
			{
				if(preg_match("~" . $this->l_delim .  $key . $this->r_delim . "~iUs", $source, $match))
				{
					$source = str_replace($match[0], $value, $source);
				}
			}
		}
		
		// Refresh of tags that were changed as a result of unset / mis-typed vars
		$source = str_replace("<<!", $this->l_delim, $source);
		$source = str_replace("!>>", $this->r_delim, $source);
		
		// we are done
		return $source;
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
}
// EOF