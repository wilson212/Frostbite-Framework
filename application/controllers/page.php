<?php

class Page extends Controller 
{
	function Page($c, $a)
	{
		parent::__construct($c, $a);
	}
	
	function index($uri) 
	{	
		$this->load->model('Page_Model');
		$this->Page_Model->get_page_contents($uri);
	}

	function afterAction() 
	{
		$this->output();
	}
}
// EOF