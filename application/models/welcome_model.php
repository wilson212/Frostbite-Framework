<?php

class Welcome_Model extends Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function return_default_array()
	{
		return array('test_var' => 'This is a dynamically loaded page using the Frostbite MVC Framework!');
	}
}
// EOF