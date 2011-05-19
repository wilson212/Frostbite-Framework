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
		$this->RDB = $this->load->database();
		
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