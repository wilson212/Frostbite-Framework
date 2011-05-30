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

// Lets get some basics down
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('APP_PATH', ROOT . DS .'application');
define('SYSTEM_PATH', ROOT . DS .'system');
define('SITE_DIR', dirname( $_SERVER['PHP_SELF'] ).'/');
define('SITE_HREF', stripslashes(str_replace('//', '/', SITE_DIR)));
define('BASE_URL', 'http://'.$_SERVER["HTTP_HOST"]. SITE_HREF);

// Include our 4 main required files, and routes config file.
require (APP_PATH . DS . 'config' . DS . 'routes.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Benchmark.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Registry.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Common.php');
require (SYSTEM_PATH . DS . 'core' . DS . 'Frostbite.php');

// Initiate the system start time
Benchmark::startTimer('system');

// Important to Init the config here! So other classes can use the 
// Custom class prefix which is stored in the config.
load_class('Config');
 
// Register the Core to process errors with the custom_error_handler method
set_error_handler( array( 'Core', 'custom_error_handler' ), E_ALL );

// Initiate the framework and let it do the rest ;)
$Frostbite = load_class('Frostbite');
$Frostbite->Init();
?>