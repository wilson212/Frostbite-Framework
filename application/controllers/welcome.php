<?php
class Welcome extends System\Core\Controller 
{
	function Welcome()
	{
		parent::__construct();
	}
	
	function _beforeAction() 
	{
		/*
		| You can call before and after actions, sorta like mini hooks
		| They arent nessesary, but convenient not having to make a full hook
		| Since they are loaded in the main controller, you DO NOT need to 
		| include these functions at all in your controller if you dont want to.
		*/
	}
	
	function index() 
	{	
		// Load a Welcome Model
		$this->load->model('Welcome_Model');
		
		/*
		| We will use this newly loaded model to return an array.
		| This array is the "No Contents In Body...." message you see 
		| when you load this page in the browser.
		*/
		$data = $this->Welcome_Model->test_array();
		
		// Load the page, and we are done :)
		$this->load->view('welcome', $data);
	}

	function _afterAction() 
	{
        /*
		| You can call before and after actions, sorta like mini hooks
		| They arent nessesary, but convenient not having to make a full hook
		| Since they are loaded in the main controller, you DO NOT need to 
		| include these functions at all in your controller if you dont want to.
		*/
	}
}
// EOF