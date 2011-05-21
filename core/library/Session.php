<?php

class Session
{
	static $started = FALSE; 
	
	function __construct()
	{
		// start the session
		$this->start_session();
		
		// Check for session data. If there is none, create it.
		if(!$this->read())
		{
			$this->create();
		}
	}
	
	function start_session()
	{
		if(!$this::$started)
		{
			session_start();
			$this::$started = true;
		}
	}
	
	function create()
	{
		$_SESSION['data'] = array();
	}
	
	function read()
	{
		if(isset($_SESSION['data']))
		{
			return TRUE;
		}
		elseif(isset($_COOKIE['data']))
		{
			// Read cookie
			list($cookie['user_id'], $cookie['session_token']) = @unserialize(stripslashes($_COOKIE['data']));
			
			// Do some checks
			if(config('session_use_database', 'Core'))
			{
				$id = config('session_database_id', 'Core');
				$table_name = config('session_table_name', 'Core');
				
				// Get instance
				$FB = get_instance();
				$DB = $FB->load->database($id);
				
				// Get the database result
				$DB->select('*')->from($table_name)->where('token', $cookie['session_token'])->query();
				$Get = $DB->result();
			}
			return FALSE;
		}
		return FALSE;
	}
	
	function write()
	{
	
	}
	
	function update()
	{
	
	}
	
	function destroy()
	{
	
	}
	
	function get($name)
	{
        return $_SESSION['data'][$name]; 
	}
	
	function set($name, $value)
	{		
        $_SESSION['data'][$name] = $value;       
        return true; 
	}
	
	function unset($name)
	{		
        unset($_SESSION['data'][$name]);       
        return true; 
	}
}