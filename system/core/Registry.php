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
| ---------------------------------------------------------------
| Class: Registry()
| ---------------------------------------------------------------
|
| This class hold all of the other class object that have been 
| loaded, and stores them statically so they are only called once
|
*/

Class Registry
{

    // Registry array of objects  
    private static $objects = array();
    
    // The instance of the registry 
    private static $instance;

    // prevent clone 
    public function __clone(){}
 
/*
| ---------------------------------------------------------------
| Method: singlton()
| ---------------------------------------------------------------
|
| Prevents duplication of memory using the by Singleton Pattern.
|
*/ 

    public static function singleton() 
    {
        if(!isset(self::$instance))
        {
			self::$instance = new self();
        }
        return self::$instance;
    }

/*
| ---------------------------------------------------------------
| Method: get()
| ---------------------------------------------------------------
|
| This method is a privte method used to return a stored object
|
| @Param: $key - Object to be returned
|
*/
	
    protected function get($key)
    {
        if(isset(self::$objects[$key]))
        {
            return self::$objects[$key];
        }
        return NULL;
    }

/*
| ---------------------------------------------------------------
| Method: _set()
| ---------------------------------------------------------------
|
| This method is a privte method used to store an object
|
| @Param: $key - Object name to be stored
| @Param: $val - value of the object
|
*/

    protected function set($key,$val)
    {
        self::$objects[$key] = $val;
    }

/*
| ---------------------------------------------------------------
| Method: load()
| ---------------------------------------------------------------
|
| This method is used statically to get request handle
|
| @Param: $key - Object name to be loaded and returned
|
*/

    static function load($key)
    {

        return self::singleton()->get($key);
    }

/*
| ---------------------------------------------------------------
| Method: store()
| ---------------------------------------------------------------
|
| This method is used to store an object locally
|
| @Param: $key - Object name to be stored
| @Param: $val - value of the object
|
*/

    static function store($key, $instance)
    {

        return self::singleton()->set($key,$instance);
    }

}
// EOF