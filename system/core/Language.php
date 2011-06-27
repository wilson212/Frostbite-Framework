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

class Language
{
	public $language_vars = array();
	public $loaded_files = array();

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/	
	function __construct()
	{
		// Set the default Language
		$this->set_language( config('core_language', 'Core') );
	}
	
/*
| ---------------------------------------------------------------
| Function: set_language()
| ---------------------------------------------------------------
|
| Sets the langauge. Does not reload already loaded files
|
| @Param: $lang - Name of the language we are loading
|
*/	
	function set_language($lang)
	{
		// Set the default Language
		$this->Language = strtolower($lang);
	}

/*
| ---------------------------------------------------------------
| Function: load()
| ---------------------------------------------------------------
|
| Loads the lanugage file
|
| @Param: $file - Name of the language file, without the extension
| @Param: $lang - Language we are loading
| @Param: $return - Set to TRUE to return the $lang array, FALSE
|		to just save the variables here.
|
*/
	public function load($file, $return = FALSE)
	{
		// Add the extension
		$file = $file . '.php';
		
		// Make sure we havent loaded this already
		if(isset($this->loaded_files[$file]))
		{
			return TRUE;
		}
		
		// Make this a bit easier to type
		$lang = $this->Language;

		// Determine where the language file is and load it
		if(file_exists(APP_PATH . DS .'language' . DS . $lang . DS . $file))
		{
			include(APP_PATH . DS .'language' . DS . $lang . DS . $file);
		}
		elseif (file_exists(SYSTEM_PATH . DS .'language' . DS . $lang . DS . $file))
		{
			include(SYSTEM_PATH . DS .'language' . DS . $lang . DS . $file);
		}
		else
		{
			// Only show an error if we arent able to return FALSE
			if($return == FALSE)
			{
				trigger_error('Unable to load the requested language file: '.$lang.'/'.$file);
			}
		}

		// If the array "$language" is none existant in the language file, we have an error
		if(!isset($language) || !is_array($language))
		{
			return FALSE;
		}

		// Do we return the array?
		if($return == TRUE)
		{
			return $language;
		}

		// Without a return, we need to store what we have here.
		$this->loaded_files[] = $file;
		$this->language_vars = array_merge($this->language_vars, $language);
		unset($language);
		return TRUE;
	}

/*
| ---------------------------------------------------------------
| Function: get()
| ---------------------------------------------------------------
|
| Returns the variable from the config array
|
| @Param: $var - the key of the lang array value, needed to be returned
|
*/
	public function get($var)
	{
		if(isset($this->language_vars[$var]))
		{
			return $this->language_vars[$var];
		}
		return FALSE;
	}
}
// EOF