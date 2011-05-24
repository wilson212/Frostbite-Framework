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
define('CORE_PATH', ROOT . DS .'core');
define('SITE_DIR', dirname( $_SERVER['PHP_SELF'] ).'/');
define('SITE_HREF', stripslashes(str_replace('//', '/', SITE_DIR)));
define('BASE_URL', 'http://'.$_SERVER["HTTP_HOST"]. SITE_HREF);

// Include our 4 main required files, and routes config file.
require (APP_PATH . DS . 'config' . DS . 'routes.php');
require (CORE_PATH . DS . 'library' . DS . 'Benchmark.php');
require (CORE_PATH . DS . 'library' . DS . 'Registry.php');
require (CORE_PATH . DS . 'library' . DS . 'Common.php');
require (CORE_PATH . DS . 'library' . DS . 'Frostbite.php');

// Initiate the system start time
Benchmark::startTimer('system');

// Init the config
$Config = new Config();
 
// Register the Core to process errors with the custom_error_handler method
set_error_handler( array( 'Core', 'custom_error_handler' ), E_ALL );

// Initiate the framework and let it do the rest ;)
$Frostbite = load_class('Frostbite');
$Frostbite->Init();
?>