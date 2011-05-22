<?php

class Session
{
	static $started = FALSE;
	private $session_use_db;
	private $session_db_id;
	private $session_table_name;
	private $DB;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/	
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
		$this->input = $this->load->library('Input');
		
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
		$_SESSION['last_seen'] = time();
		$str = microtime(1);
		$_SESSION['token'] = sha1(base64_encode(pack("H*", md5(utf8_encode( $str )))));
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
			$this->update();
			return TRUE;
		}
		
		// If no session data, Check for a cookie
		elseif($this->input->cookie('data') != FALSE)
		{
			// Read cookie
			$cookie = explode(',', @unserialize( $this->input->cookie('data') ) );
			
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
				$this->DB->select('*')->from( $this->session_table_name )->where('token', $cookie[1])->query();
				$Get = $this->DB->result();
				
				if($Get !== FALSE)
				{
					// Check user agent
					if($Get['user_agent'] != $this->input->user_agent() )
					{
						return FALSE;
					}
					
					// check users IP address
					elseif($Get['ip_address'] != $this->input->ip_address() )
					{
						return FALSE;
					}
					
					// All is good, return the cookie ID
					else
					{
						$_SESSION['token'] = $cookie[1];
						return $cookie[0];
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

/*
| ---------------------------------------------------------------
| Method: save()
| ---------------------------------------------------------------
|
| Saves the current session token in a cookie, and in the DB for
| things like "Remeber Me" etc etc.
|
| @Param: $id - Any kind of information you want stored in the 
|		cookie such as a usename or userID.
|
*/	
	function save($id = NULL)
	{
		// Cant have an empty ID
		if($id === NULL)
		{
			show_error(1, 'You need to set some information to be stored in the cookie, before saving a session.', __FILE__, __LINE__);
		}
		
		// Combine the ID being set, and the session token
		$var = array($id, $_SESSION['token']);
		$ID = serialize( implode(',', $var) );
		
		// Set the cookie
		$this->input->set_cookie( 'data', $ID );
		
		// Are we storing session data in the database?
		if($this->session_use_db == TRUE)
		{
			// Get instance if we havent got it already
			if($this->DB == FALSE)
			{
				$FB = get_instance();
				$this->DB = $FB->load->database( $this->session_db_id );
			}
			
			// check to see if we already have this session token saved
			$this->DB->select('*')->from( $this->session_table_name )->where('token', $_SESSION['token'])->query();
			$Get = $this->DB->result();
			
			if($Get == FALSE)
			{		
				// Add session data to the DB only if it doesnt exists
				$data = array( 
					'token' => $_SESSION['token'], 
					'user_agent' => $this->input->user_agent(), 
					'ip_address' => $this->input->ip_address(),
					'last_seen' => $_SESSION['data']['last_seen']
				);
				$this->DB->insert( $this->session_table_name, $data )->query();
			}
			
			// Check out insert
			if($this->DB->affected_rows() == 1)
			{
				return TRUE;
			}
			return FALSE;
		}
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
		// Remove session variables and cookeis
		unset($_SESSION['data']);
		session_destroy();
		
		// Are we storing session data in the database? 
		// If so then remove the session from the DB
		if($this->session_use_db == TRUE)
		{
			// Get instance if we havent already
			if($this->DB == FALSE)
			{
				$FB = get_instance();
				$this->DB = $FB->load->database( $this->session_db_id );
			}
			
			// Delete data
			$this->DB->delete_from( $this->session_table_name )->where('token', $_SESSION['token'])->query();
		}
		
		// Start a new session
		$this->start_session();
		$this->create();
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

/*
| ---------------------------------------------------------------
| Method: update()
| ---------------------------------------------------------------
|
| Updates the "last_seen"
|
*/
	function update()
	{
		$_SESSION['data']['last_seen'] = time();
		
		// Are we storing session data in the database? 
		// If so then remove update the session last_seen
		if($this->session_use_db == TRUE)
		{
			// Get instance if we havent already
			if($this->DB == FALSE)
			{
				$FB = get_instance();
				$this->DB = $FB->load->database( $this->session_db_id );
			}
			
			// Update data
			$this->DB
				->update($this->session_table_name, array( 'last_seen' => time() ))
				->where('token', $_SESSION['token'])
				->query();
		}
		return TRUE;
	}
}
// EOF