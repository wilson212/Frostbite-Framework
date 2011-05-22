<?php

class Session
{
	static $started = FALSE;
	private $session_use_db;
	private $session_db_id;
	private $session_table_name;
	private $DB;
	
	function __construct()
	{
		$this->DB = FALSE;
		
		// start the session
		$this->start_session();
		
		// Get our DB information
		$this->session_use_db = config('session_use_database', 'Core');
		$this->session_db_id = config('session_database_id', 'Core');
		$this->session_table_name = config('session_table_name', 'Core');
		
		// Init the loader class
		$this->load = load_class('Loader');
		
		// load the Input and cookie class
		$this->load->library('Input');
		
		// Check for session data. If there is none, create it.
		if(!$this->check())
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
		$_SESSION['token'] = session_id();
	}

/*
| ---------------------------------------------------------------
| Method: check()
| ---------------------------------------------------------------
|
| Read session data, and cookies to determine if our session
| is still alive
|
*/	
	function check()
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
			if($this->session_use_db == TRUE)
			{
				
				// Get instance if we havent got it already
				if($this->DB == FALSE)
				{
					$FB = get_instance();
					$this->DB = $FB->load->database( $this->session_db_id );
				}
				
				// Get the database result
				$this->DB->select('*')->from( $this->session_table_name )->where('token', $cookie['session_token'])->query();
				$Get = $this->DB->result();
				
				if($Get !== FALSE)
				{
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
				
				// False return from DB
				else
				{
					return FALSE;
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
	
	function save($id = TRUE)
	{
		// Combine the ID being set, and the session token
		$vars = array($id, $_SESSION['token']);
		$ID = serialize( $vars );
		
		// Set the cookie
		$this->input->set_cookie( 'data', $ID );
		
		// Get instance if we havent got it already
		if($this->DB == FALSE)
		{
			$FB = get_instance();
			$this->DB = $FB->load->database( $this->session_db_id );
		}
		
		// Add session data to the DB
		$data = array( 
			'token' => $_SESSION['token'], 
			'user_agent' => $this->input->user_agent, 
			'ip_address' => $this->input->ip_address 
		);
		$this->DB->insert( $this->session_table_name, $data );
		
		// Check out insert
		if($this->DB->affected_rows() == 1)
		{
			return TRUE;
		}
		return FALSE;
	}
	
	function update()
	{
	
	}

/*
| ---------------------------------------------------------------
| Method: destroy()
| ---------------------------------------------------------------
|
| Ends the current Session|
|
*/	
	function destroy()
	{
		unset($_SESSION['data']);
		session_destroy();
		$this->start_session();
		$this->check();
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
| Method: delete()
| ---------------------------------------------------------------
|
| Unsets a $_SESSION variable
|
*/	
	function delete($name)
	{		
        unset($_SESSION['data'][$name]);       
        return true; 
	}
}
// EOF