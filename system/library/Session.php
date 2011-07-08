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
	public $db_session_exists;
	
	// Our DB connection
	protected $DB;

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
		
		// Are we storing session data in the database? If so, Load the DB connction
		if($this->session_use_db == TRUE)
		{
			$this->DB = $this->load->database( $this->session_db_id );
			$this->db_session_exists = FALSE; // default
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
		$cookie = $this->input->cookie( $this->session_cookie_name );
		
		// If the cookie doesnt exists, then neither does the session
		if($cookie == FALSE)
		{
			// No cookie means we have nothing to go off of, return FALSE
			return FALSE;
		}
		
		// Read cookie data
		$cookie = unserialize($cookie);
		
		// Are we storing session data in the database?
		if($this->session_use_db == TRUE)
		{				
			// Get the database result
			$query = "SELECT * FROM `". $this->session_table_name ."` WHERE `token` = '". $cookie['token'] ."'";
			$result = $this->DB->query( $query )->fetch_array();
			
			// If we have a result, then data IS in the DB
			if($result !== FALSE)
			{
				// check users IP address to prevent cookie stealing
				if($result['ip_address'] == $this->input->ip_address() )
				{	
					$this->data['token'] = $result['token'];
					$result['user_data'] = unserialize( $result['user_data'] );
				
					// Set data if we have any
					if(count($result['user_data']) > 0)
					{
						foreach($result['user_data'] as $key => $value)
						{
							// Set the data in the session
							$this->set($key, $value);
						}
					}
				
					// Update last seen
					$this->data['last_seen'] = time();
				
					// Set our local variable to true and return
					$this->db_session_exists = TRUE;
					return TRUE;
				}
			}
			
			// If we are here, validation checks failed
			return FALSE;
		}
		
		// config says, No sessions in database, Load cookie manually
		else
		{
			// check users IP address to prevent cookie stealing
			if($cookie['ip_address'] == $this->input->ip_address() )
			{
				$this->data['token'] = $cookie['token'];					
				return $cooke['id'];
			}
			
			// If we are here, the cookie is no good
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
|	cookie such as a usename or userID. ONLY needed if user
|	is not using a database session
|
*/	
	public function save($id = NULL)
	{
		// Cant have an empty ID
		if($id === NULL && $this->session_use_db == FALSE)
		{
			show_error('You need to set some information to be stored in the cookie, before saving a session.', false, E_WARNING);
			return FALSE;
		}
		
		// If we arent storing session Data in the DB, then we set a cookie only
		if($this->session_use_db == FALSE)
		{
			// Combine the ID being set, and the session token
			$stuff = array(
				'id' => $id, 
				'token' => $this->data['token'], 
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
			// Set a cookie with the session token
			$cookie_data = serialize( array('token' => $this->data['token']) );
			
			// Set the cookie
			$this->input->set_cookie( $this->session_cookie_name, $cookie_data );

			// No session exists, insert new data
			if ($this->db_session_exists == FALSE)
			{						
				// Add session data to the DB only if it doesnt exists
				$data = array( 
					'token' => $this->data['token'],  
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
			if($this->DB->num_rows() > 0)
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
			$time = time() - 1;
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
        unset($this->data[$name]);
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
		// Are we storing session data in the database? 
		// If so then update the session last_seen
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
	}
}
// EOF