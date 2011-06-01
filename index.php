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
| * You are authorized to change or remove this comment box only
|	in the index.php file.
|
*/

// Default Constants
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('APP_PATH', ROOT . DS .'application');
define('SYSTEM_PATH', ROOT . DS .'system');
define('INDEX_DIR', dirname( $_SERVER['PHP_SELF'] ).'/');
define('SITE_DIR', stripslashes(str_replace('//', '/', INDEX_DIR)));
define('BASE_URL', 'http://'.$_SERVER["HTTP_HOST"]. SITE_DIR);

/*
| Lets speed to core up by not using the autoloader 
| to load these system files, that are NEEDED :p
| These classes are not extendable, or replacable
*/
require (APP_PATH . DS . 'config' . DS . 'routes.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Benchmark.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Common.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Config.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Core.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Frostbite.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Registry.php');

// Initiate the system start time
Benchmark::startTimer('system');

/*
| Important to Init the config here! So other classes can use the 
| Custom class prefix, which is stored in the config.
*/
load_class('Config');
 
// Register the Core to process errors with the custom_error_handler method
set_error_handler( array( 'Core', 'custom_error_handler' ), E_ALL );

// Initiate the framework and let it do the rest ;)
$Frostbite = load_class('Frostbite');
$Frostbite->Init();
?>