<?php

class Page extends System\Core\Controller 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function index() 
	{	
		// Just for fun, we are going to instance this model as 'TeSt'
		$this->load->model('Page_Model', 'TeSt');
		$contents = $this->TeSt->get_page_contents();
		$this->output($contents);
	}
}
// EOF