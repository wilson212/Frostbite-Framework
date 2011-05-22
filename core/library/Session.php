<?php

class Session
{
	static $started = FALSE; 
	
	function __construct()
	{
		// start the session
		$this->start_session();
		
		// Init the loader class
		$this->load = load_class('Loader');
		
		// load the Input class
		$this->load->library('Input');
		
		// Check for session data. If there is none, create it.
		if(!$this->read())
		{
			$this->create();
		}
	}
	
/*
| ---------------------------------------------------------------
| Method: start_session()
| ---------------------------------------------------------------
|
| Only starts a session if its not already set
|
*/
	function start_session()
	{
		if(!$this::$started)
		{
			session_start();
			$this::$started = true;
		}
	}

/*
| ---------------------------------------------------------------
| Method: create()
| ---------------------------------------------------------------
|
| Creates session data.
|
*/	
	function create()
	{
		$_SESSION['data'] = array();
	}

/*
| ---------------------------------------------------------------
| Method: read()
| ---------------------------------------------------------------
|
| Read session data, and cookies to determine if our session
| is still alive
|
*/	
	function read()
	{
		// Here we check for session data
		if(isset($_SESSION['data']))
		{
			return TRUE;
		}
		
		// If no session data, Check for a cookie
		elseif($this->input->cookie('data') != FALSE)
		{
			// Read cookie
			list($cookie['id'], $cookie['session_token']) = @unserialize( stripslashes($this->input->cookie('data')) );
			
			// Are we storing session data in the database?
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
				
				// Check user agent
				if($Get['user_agent'] != $this->input->user_agent)
				{
					return FALSE;
				}
				
				// check users IP address
				elseif($Get['ip_address'] != $this->input->ip_address)
				{
					return FALSE;
				}
				
				// All is good, return the cookie ID
				else
				{
					return $cookie['id'];
				}
			}
			
			// No sessions in database
			else
			{
				return FALSE;
			}
		}
		
		// No cookie :(
		else
		{
			return FALSE;
		}
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

/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Returns a $_SESSION variable
|
| @Param: $name - variable name to be returned
|
*/	
	function get($name)
	{
        return $_SESSION['data'][$name]; 
	}
	
/*
| ---------------------------------------------------------------
| Method: set()
| ---------------------------------------------------------------
|
| Sets a $_SESSION variable
|
| @Param: $name - variable name to be set
| @Param: $value - value of the variable
|
*/
	function set($name, $value)
	{		
        $_SESSION['data'][$name] = $value;       
        return true; 
	}

/*
| ---------------------------------------------------------------
| Method: unset()
| ---------------------------------------------------------------
|
| Unsets a $_SESSION variable
|
*/	
	function unset($name)
	{		
        unset($_SESSION['data'][$name]);       
        return true; 
	}
}
// EOF