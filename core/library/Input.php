<?php
class Input
{

	var $time;
	var $cookie_path;
	var $cookie_domain;
	var $user_agent = FALSE;
	var $ip_address = FALSE;

	public function __construct()
	{
		// Set Cookie Defaults
		$this->time = ( time() + (60 * 60 * 24 * 365) );
		$this->cookie_path =  "/";
		$this->cookie_domain = $_SERVER['HTTP_HOST'];
		
		// This is truely not needed!
		$_GET = array();
	}
   

/*
| ---------------------------------------------------------------
| Method: post()
| ---------------------------------------------------------------
|
| Returns a $_POST variable
|
| @Param: $var - variable name to be returned
|
*/
	public function post($var)
	{
		if(isset($_POST[$var]))
		{
			return $_POST[$var];
		}
		return FALSE;
	}
	
/*
| ---------------------------------------------------------------
| Method: cookie()
| ---------------------------------------------------------------
|
| Returns a $_COOKIE variable
|
| @Param: $name - variable name to be returned
|
*/
	public function cookie($name)
	{
		if(isset($_COOKIE[$name]))
		{
			return $_COOKIE[$name];
		}
		return FALSE;
    }
	
/*
| ---------------------------------------------------------------
| Method: set_cookie()
| ---------------------------------------------------------------
|
| Sets a cookie
|
| @Param: $key - Name of the cookie
| @Param: $val - Value of the cookie
|
*/	
    function set_cookie($key, $val)
    {
        setcookie( $key, $val, $this->time, $this->cookie_path, $this->cookie_domain, false, true);
    }
	
/*
| ---------------------------------------------------------------
| Method: user_agent()
| ---------------------------------------------------------------
|
| Returns the browser name the user is using
|
*/
	public function user_agent()
    {
		if($this->user_agent == FALSE)
        {
			$this->user_agent = ( isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : FALSE );
		}
        return $this->user_agent;
    }

/*
| ---------------------------------------------------------------
| Method: ip_address()
| ---------------------------------------------------------------
|
| Returns the users IP address
|
*/	
	public function ip_address()
    {
		// Return it if we already determined the IP
        if($this->ip_address == FALSE)
        {       
			// Check to see if the server has the IP address
			if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '')
			{
				$this->ip_address = $_SERVER['REMOTE_ADDR'];
			}
			elseif(isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != '')
			{
				$this->ip_address = $_SERVER['HTTP_CLIENT_IP'];
			}

			// If we still have a FALSE IP address, then set to 0's
			if ($this->ip_address === FALSE)
			{
				$this->ip_address = '0.0.0.0';
			}
		}
        return $this->ip_address;
	}
}
// EOF 