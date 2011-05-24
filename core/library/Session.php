<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author: 		Steven Wilson
| Copyright:	Copyright (c) 2011, Steven Wilson
| License: 		GNU GPL v3
|
*/

class Session
{
	static $started = FALSE;
	private $data;
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
		$this->session_cookie_name = config('session_cookie_name', 'Core');
		
		// Init the loader class
		$this->load = load_class('Loader');
		
		// load the Input and cookie class
		$this->input = $this->load->library('Input');
		
		// Are we storing session data in the database?
		// If so then load the DB
		if($this->session_use_db == TRUE)
		{
			$this->DB = $this->load->database( $this->session_db_id );
		}
		
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
		$time = microtime(1);
		$this->data['last_seen'] = time();
		$this->data['token'] = sha1(base64_encode(pack("H*", md5(utf8_encode( $time )))));
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
		// Check for a session cookie
		if($this->input->cookie( $this->session_cookie_name ) == FALSE)
		{
			return FALSE;
		}
		else
		{
			// Read cookie
			$cookie = @unserialize($this->input->cookie( $this->session_cookie_name ));
			
			// Are we storing session data in the database?
			if($this->session_use_db == TRUE)
			{				
				// Get the database result
				$this->DB->select('*')->from( $this->session_table_name )->where('token', $cookie['token'])->query();
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
						$this->data['token'] = $Get['token'];
						$Get['user_data'] = @unserialize( $Get['user_data'] );
						
						if(count($Get['user_data'] > 1))
						{
							foreach($Get['user_data'] as $key => $value)
							{
								$this->set($key, $value);
							}
						}
						elseif(count($Get['user_data']) == 1)
						{
							$key = key( $Get['user_data'][0] );
							$this->set( $key, $Get['user_data'][$key] );
						}
						
						return TRUE;
					}
				}
				
				// False return from DB
				else
				{
					return FALSE;
				}
			}
			
			// config says, No sessions in database, Load cookie manually
			else
			{
				// Check user agent
				if($cookie['user_agent'] != $this->input->user_agent() )
				{
					return FALSE;
				}
				
				// check users IP address
				elseif($cookie['ip_address'] != $this->input->ip_address() )
				{
					return FALSE;
				}
				
				// Everything looks good ;)
				else
				{
					$this->data['token'] = $cookie['token'];					
					return $cooke['id'];
				}	
			}
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
		if($id === NULL && $this->session_use_db == FALSE)
		{
			show_error(1, 'You need to set some information to be stored in the cookie, before saving a session.', __FILE__, __LINE__);
		}
		
		// If we arent storing session Data in the DB, then we set a cookie only
		if($this->session_use_db == FALSE)
		{
			// Combine the ID being set, and the session token
			$stuff = array(
				'id' => $id, 
				'token' => $this->data['token'], 
				'user_agent' => $this->input->user_agent(),
				'ip_address' => $this->input->ip_address(),
				'last_seen' => time()
			);
			$ID = serialize( $stuff );
			
			// Set the cookie
			$this->input->set_cookie( $this->session_cookie_name, $ID );
		}
		
		// If we are storing session data, then lets do that!
		else
		{	
			// Combine the ID being set, and the session token
			$ID = serialize( $this->data );
			
			// Set the cookie
			$this->input->set_cookie( $this->session_cookie_name, $ID );
		
			// check to see if we already have this session token saved
			$this->DB->select('*')->from( $this->session_table_name )->where('token', $this->data['token'])->query();
			$Get = $this->DB->result();
			
			// No session exists, insert new data
			if($Get == FALSE)
			{		
				// Add session data to the DB only if it doesnt exists
				$data = array( 
					'token' => $this->data['token'], 
					'user_agent' => $this->input->user_agent(), 
					'ip_address' => $this->input->ip_address(),
					'last_seen' => time(),
					'user_data' => $ID
				);
				$this->DB->insert( $this->session_table_name, $data )->query();
			}
			
			// Session data does exists for this token, so update.
			else
			{
				return $this->update();
			}
			
			// Check out insert
			if($this->DB->result() == TRUE) 
			{
				return TRUE;
			}
			return FALSE;
		}
		
		return TRUE;
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
		// Are we storing session data in the database? 
		// If so then remove the session from the DB
		if($this->session_use_db == TRUE)
		{			
			// Delete data
			$this->DB->delete_from( $this->session_table_name )->where('token', $this->data['token'])->query();
		}
		
		// Remove session variables and cookeis
		unset($this->data['token']);
		session_destroy();
		
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
        return $this->data[$name]; 
	}
	
/*
| ---------------------------------------------------------------
| Method: set()
| ---------------------------------------------------------------
|
| Sets a $_SESSION variable.
|
| @Param: $name - variable name to be set, OR an array of $names => $values
| @Param: $value - value of the variable, or NULL if $name is array.
|
*/
	function set($name, $value = NULL)
	{
		if(is_array($name))
		{
			foreach($name as $key => $value)
			{
				$this->data[$key] = $value;
			}
		}
		else
		{
			$this->data[$name] = $value;
		}
		
		// Update the DB, or cookie;
		$this->update();
        return TRUE; 
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
		// unset $name
        unset($this->data[$name]);
		
		// Update the DB, or cookie;
		$this->update();		
        return TRUE; 
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
		$this->data['last_seen'] = time();
		
		// Are we storing session data in the database? 
		// If so then remove update the session last_seen
		if($this->session_use_db == TRUE)
		{			
			// Update data
			$ID = serialize( $this->data );
			
			$this->DB
				->update($this->session_table_name, array( 'last_seen' => time(), 'user_data' => $ID ))
				->where('token', $this->data['token'])
				->query();
		}
		return TRUE;
	}
}
// EOF