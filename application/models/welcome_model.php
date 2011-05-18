<?php

class Welcome_Model extends Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function return_default_array()
	{
		return array('test_var' => 'No Contents In The Body! This is a test variable btw ;)');
	}
}
// EOF