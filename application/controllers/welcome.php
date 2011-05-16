<?php

class Welcome extends Controller 
{
	function Welcome($c, $a)
	{
		parent::__construct($c, $a);
	}
	
	function beforeAction() 
	{

	}
	
	function index() 
	{	
		$this->load->model('Welcome_Model');
	}

	function afterAction() 
	{
		$this->output();
	}
}
// EOF