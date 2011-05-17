<?php
/*
| ---------------------------------------------------------------
| Class: Loader()
| ---------------------------------------------------------------
|
| This class is used to load classes and librarys into the calling
| class / method.
|
*/

class Loader
{	
	function Loader()
	{
		$this->RDB = FALSE;
	}
	
/*
| ---------------------------------------------------------------
| Method: model()
| ---------------------------------------------------------------
|
| This method is used to call in a model
|
| @Param: $name - The name of the model
| @Param: $folder - Location (if not default) of the model
|
*/
	function model($name, $folder = '')
	{
		if($folder != '')
		{
			require(ROOT . DS . $folder . DS . $name .'.php');
		}
		else
		{
			require(APP_PATH . DS . 'models' . DS . $name .'.php');
		}
		
		$name = ucfirst($name);
		$FB = get_instance();
		$FB->$name = new $name();
	}

/*
| ---------------------------------------------------------------
| Method: database()
| ---------------------------------------------------------------
|
| This method is used to setup a database connection
|
| @Param: $args - Either R (Realm DB), C (Character DB), W (World DB)
| or and array to connect to a none default Database:
|	$args = array( $host, $port, $DB Username, $DB Password, $DB Name);
|
*/	
	function database($args = 'R')
	{
		$Config = new Config();
		
		// If is an array, then its a new DB connection
		if(is_array($args))
		{
			$db_host = @func_get_arg(0);
			$db_port = @func_get_arg(1);
			$db_user = @func_get_arg(2);
			$db_pass = @func_get_arg(3);
			$db_name = @func_get_arg(4);
			$DB = new Database($db_host, $db_port, $db_user, $db_pass, $db_name);
			return $DB;
		}
		else
		{
			switch($args)
			{
				case 'R':
					if(!$this->RDB)
					{
						$this->RDB = new Database(
							$Config->getDbInfo('db_host'),
							$Config->getDbInfo('db_port'),
							$Config->getDbInfo('db_username'),
							$Config->getDbInfo('db_password'),
							$Config->getDbInfo('db_name')
						);
					}
					return $this->RDB;
				break;
			}
		}
	}
}