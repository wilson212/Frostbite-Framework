<?php

class Welcome_Model extends System\Core\Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function test_array()
	{
		return array('test_var' => 'This is a dynamically loaded page using the Frostbite MVC Framework!');
	}
}
// EOF