<?php

class Page_Model extends Model 
{
	var $RDB = FALSE;
	
	function __construct()
	{
		parent::__construct();
	}
	
	function get_page_contents()
	{
		/* 
			Tell the loader to load the DB config, and instance 
			this as DDB in the  contoller. But, since we dont have
			access to controller variables, we have to define our own
			(RDB).
		*/
		$this->RDB = $this->load->database('DB', 'DDB');
		
		$this->RDB
			->select("*")
			->from("categories")
			->where("id", "1")
			->query();
		$contents = $this->RDB->result();
		print_r( $contents );
		return TRUE;
	}
}
// EOF