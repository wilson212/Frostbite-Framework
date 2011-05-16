<?php

class Page_Model extends Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function get_page_contents($uri)
	{
		$uri = func_get_args();
		$uri = implode(",", $uri);
		//$this->RDB = $this->load->database('R');
		
		/*
			$content = $this->RDB->selectRow("SELECT * FROM `table_name` WHERE `page_url`='". $uri ."'");
			if(!content)
			{
				Core::trigger_error(404);
			}
			else
			{
				return $content;
			}
		*/
		return TRUE;
	}
}
// EOF