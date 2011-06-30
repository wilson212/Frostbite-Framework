<?php

class Page_Model extends System\Core\Model 
{	
	function __construct()
	{
		parent::__construct();
	}
	
	function get_page_contents()
	{
		/* 
			Tell the loader to load the DB config, and instance 
			this as DB in the  contoller. But, since we dont have
			access to controller variables, we have to define our own
			(RDB).
		*/
		$this->RDB = $this->load->database('DB', TRUE);
		
		// use the querybuilder to build AND clean out sql statement
		$qb = $this->load->library('querybuilder');
		$qb->select("*")->from("categories")->where("id", "1");
		
		// Query the DB
		$this->RDB->query( $qb->sql );
		$contents = $this->RDB->fetch_array();
		
		// Return out array of contents
		return $contents;
	}
}
// EOF