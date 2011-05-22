<?php

class Cookie 
{

	var $time;
	var $cookie_path;
	var $cookie_domain;
    
    function __construct()
    {
        $this->time = ( time() + (60 * 60 * 24 * 365) );
        $this->cookie_path =  "/";
        $this->cookie_domain = $_SERVER['HTTP_HOST'];
    }
    
/*
| ---------------------------------------------------------------
| Method: set()
| ---------------------------------------------------------------
|
| Sets a cookie
|
| @Param: $key - Name of the cookie
| @Param: $val - Value of the cookie
|
*/	
    function set($key, $val)
    {
        setcookie( $key, $val, $this->time, $this->cookie_path, $this->cookie_domain, false, true);
    }

/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| Gets the contents of a cookie
|
| @Param: $key - Name of the cookie variable
|
*/    
    function get($key)
    {      
		if(isset($_COOKIE[$key]))
		{
			return $_COOKIE[$key];
		}
		return FALSE;
    }
 
 /*
| ---------------------------------------------------------------
| Method: add_data()
| ---------------------------------------------------------------
|
| Adds cookie data to the $_COOKIE global array
|
| @Param: $data - an array of $keys = $values
|
*/ 
    function add_data($data)
    {
        if(is_array($data))
        {
            foreach($data as $key => $val)
            {
				$_COOKIE[$key] = $val;
            }
        }
    }
  
}
// EOF