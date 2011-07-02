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
namespace System\Library;

class Session
{
	// Have we already started the session?
	static $started = FALSE;
	
	// Array of session data
	public $data = array();
	
	// Database / cookie info
	protected $session_use_db;
	protected $session_db_id;
	protected $session_table_name;
	public $session_cookie_name;
	
	// Our DB and Querybuilder classes
	protected $DB;
	protected $QB;

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
*/	
	function __construct()
	{		
		// start the session
		$this->start_session();
		
		// Get our DB information
		$this->session_use_db = config('session_use_database', 'Core');
		$this->session_db_id = config('session_database_id', 'Core');
		$this->session_table_name = config('session_table_name', 'Core');
		$this->session_cookie_name = config('session_cookie_name', 'Core');
		
		// Init the loader class
		$this->load = load_class('Core\\Loader');
		
		// load the Input class
		$this->input = load_class('Core\\Input');
		
		// Are we storing session data in the database?
		// If so then load the DB and querybuilder
		if($this->session_use_db == TRUE)
		{
			$this->DB = $this->load->database( $this->session_db_id );
			$this->QB = $this->load->library('Querybuilder', FALSE);
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
	protected function start_session()
	{
		if(!self::$started)
		{
			session_start();
			self::$started = true;
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
	protected function create()
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
| is still alive. We also match the Users IP / User agent
| info to prevent cookie thiefs getting in as an unauth'd user.
|
*/	
	protected function check()
	{
		// Check for a session cookie
		if($this->input->cookie( $this->session_cookie_name ) == FALSE)
		{
			// No cookie means we have nothing to go off of, return FALSE
			return FALSE;
		}
		else
		{
			// Read cookie data
			$cookie = unserialize($this->input->cookie( $this->session_cookie_name ));
			
			// Are we storing session data in the database?
			if($this->session_use_db == TRUE)
			{				
				// Get the database result
				$this->QB->select('*')->from( $this->session_table_name )->where('token', $cookie['token']);
				$Get = $this->DB->query( $this->QB->sql )->fetch_array();
				
				// If we have a result, then data IS in the DB
				if($Get !== FALSE)
				{
					// Check user agent to prevent cookie stealing
					if($Get['user_agent'] != $this->input->user_agent() )
					{
						return FALSE;
					}
					
					// check users IP address to prevent cookie stealing
					elseif($Get['ip_address'] != $this->input->ip_address() )
					{
						return FALSE;
					}
					
					// All is good, Return TRUE
					else
					{
						$this->data['token'] = $Get['token'];
						$Get['user_data'] = unserialize( $Get['user_data'] );
						
						// Set data if we have any
						if(count($Get['user_data'] > 0))
						{
							foreach($Get['user_data'] as $key => $value)
							{
								// Set the data in the session
								$this->set($key, $value);
							}
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
				// Check user agent to prevent cookie stealing
				if($cookie['user_agent'] != $this->input->user_agent() )
				{
					return FALSE;
				}
				
				// check users IP address to prevent cookie stealing
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
	public function save($id = NULL)
	{
		// Cant have an empty ID
		if($id === NULL && $this->session_use_db == FALSE)
		{
			show_error('You need to set some information to be stored in the cookie, before saving a session.', false, E_WARNING);
		}
		
		// If we arent storing session Data in the DB, then we set a cookie only
		if($this->session_use_db == FALSE)
		{
			// Combine the ID being set, and the session token
			$stuff = array(
				'id' => $id, 
				'token' => $this->data['token'], 
				'user_agent' => $this->input->user_agent(),
				'ip_address' => $this->input->ip_address()
			);
			$ID = serialize( $stuff );
			
			// Set the cookie
			$this->input->set_cookie( $this->session_cookie_name, $ID );
			
			// Return
			return TRUE;
		}
		
		// If we are storing session data, then lets do that!
		else
		{	
			// Combine the ID being set, and the session token
			$cookie_data = serialize( array('token' => $this->data['token'], 'last_seen' =>  time()) );
			
			// Set the cookie
			$this->input->set_cookie( $this->session_cookie_name, $cookie_data );
		
			// check to see if we already have this session token saved
			$this->QB->select('*')->from( $this->session_table_name )->where('token', $this->data['token']);
			$Get = $this->DB->query( $this->QB->sql )->fetch_array();
			
			// No session exists, insert new data
			if($Get == FALSE)
			{						
				// Add session data to the DB only if it doesnt exists
				$data = array( 
					'token' => $this->data['token'], 
					'user_agent' => $this->input->user_agent(), 
					'ip_address' => $this->input->ip_address(),
					'last_seen' => time(),
					'user_data' => serialize( $this->data )
				);
				$this->DB->insert( $this->session_table_name, $data );
			}
			
			// Session data does exists for this token, so update.
			else
			{
				return $this->update();
			}
			
			// Return the the result
			return $this->DB->result(); 
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
	public function destroy()
	{		
		// Are we storing session data in the database? 
		// If so then remove the session from the DB
		if($this->session_use_db == TRUE)
		{			
			// Delete data
			$this->DB->delete( $this->session_table_name, '`token` = \''. $this->data['token'] .'\'');
		}
		
		// We must manually expire the cookie time
		else
		{
			$ID = serialize( array( 'token' => $this->data['token'] ) );
			$time = (time() - 1);
			$this->input->set_cookie( $this->session_cookie_name, $ID,  $time);
		}
		
		// Remove session variables and cookeis
		$this->data = array();
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
| Returns a session variable
|
| @Param: (String) $name - variable name to be returned
|
*/	
	public function get($name)
	{
		if(isset($this->data[$name]))
		{
			return $this->data[$name]; 
		}
		
		// Didnt exist, return NULL
		return NULL;
	}
	
/*
| ---------------------------------------------------------------
| Method: set()
| ---------------------------------------------------------------
|
| Sets a session variable.
|
| @Param: (String) $name - variable name to be set, OR an array 
|	of $names => $values
| @Param: (Mixed) $value - value of the variable, or NULL if $name 
|	is array.
|
*/
	public function set($name, $value = NULL)
	{
		// Check for an array
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
		
		// Return the sucess
        return TRUE; 
	}

/*
| ---------------------------------------------------------------
| Method: delete()
| ---------------------------------------------------------------
|
| Unsets a session variable
|
*/	
	public function delete($name)
	{
		// unset $name
        unset($this->data[$name]);

		// Return success		
        return TRUE; 
	}

/*
| ---------------------------------------------------------------
| Method: update()
| ---------------------------------------------------------------
|
| Updates the the session data thats in the database
|
*/
	public function update()
	{
		$this->data['last_seen'] = time();
		
		// Are we storing session data in the database? 
		// If so then remove update the session last_seen
		if($this->session_use_db == TRUE)
		{			
			// Update data
			$ID = serialize( $this->data );
			
			// Prep data
			$table = $this->session_table_name;
			$data = array( 'last_seen' => time(), 'user_data' => $ID );
			$where = '`token` = \''.$this->data['token'].'\'';
	
			// return the straight db result
			return $this->DB->update( $table, $data, $where );
		}
		return TRUE;
	}
}
// EOF