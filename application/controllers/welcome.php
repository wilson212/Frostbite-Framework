<?php

class Welcome extends Controller 
{
	function Welcome($c, $a)
	{
		parent::__construct($c, $a);
	}
	
	function beforeAction() 
	{
		/*
		| You can call before and after actions, sorta like mini hooks
		| They arent nessesary, but convenient not having to make a full hook
		| Since they are loaded in the main controller, you dont need to 
		| include these functions at all in your controller.
		*/
	}
	
	function index() 
	{	
		$this->load->model('Welcome_Model');
	}

	function afterAction() 
	{
		// If you have a custom afterAction, you need
		// to do $this->output(); !
		$this->output();
	}
}
// EOF