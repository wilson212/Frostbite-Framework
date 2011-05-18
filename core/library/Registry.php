<?php
/*
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
| Method: _get()
| ---------------------------------------------------------------
|
| This method is a privte method used to return a stored object
|
| @Param: $key - Object to be returned
|
*/
	
    protected function _get($key)
    {
        if(isset($this::$objects[$key]))
        {
            return $this::$objects[$key];
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

    protected function _set($key,$val)
    {
        $this::$objects[$key] = $val;
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

        return self::singleton()->_get($key);
    }

/*
| ---------------------------------------------------------------
| Method: _set()
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

        return self::singleton()->_set($key,$instance);
    }

}


/*
| *********** END OF REGISTRY CLASS *********** |
*/

/*
| ---------------------------------------------------------------
| Function: load_class()
| ---------------------------------------------------------------
|
| This function is used to load and store core classes statically 
| that need to be loaded for use, but not reset next time the class
| is called.
|
| @Param: $class - Class needed to be loaded / returned
| @Param: $args - Suppossed to be the args passed to the class method
|	experementatl!
|
*/

function load_class($class, $args = NULL)
{
    $Obj = Registry::singleton();
    
	// lowercase classname
    $Class = strtolower($class);
	$className = ucfirst($Class);
    
    //if class already stored, then just return the class  
    if ($Obj->load($Class) !== NULL)
    { 
        return $Obj->load($Class);        
    }

	// Check for needed classes from the Core library folder
	if(file_exists(CORE_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(CORE_PATH . DS .  'library' . DS . $className . '.php');
	}
	
	// Check for needed classes from the Application library folder
	elseif(@file_exists(APP_PATH . DS .  'library' . DS . $className . '.php')) 
	{
		require_once(APP_PATH . DS .  'library' . DS . $className . '.php');
	}
	else
	{
		return FALSE;
	}
    
    // Initiate the new class
	if($args !== NULL) 
	{
		$dispatch = new $className($args);
	}
	else
	{
		$dispatch = new $className();
	}
	
	// Store this new object in the registery
    $Obj->store($Class, $dispatch); 
    
    //return singleton object.
    $Object = $Obj->load($Class);

    if(is_object($Object))
	{
		return $Object;
	}
}
// EOF