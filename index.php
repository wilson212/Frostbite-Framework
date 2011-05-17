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
| License: 		???
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

// Get our current url, which is passed on by the htaccess file
$url = (isset($_GET['url']) ? $_GET['url'] : '');

// Include our 4 main required files, including the bootstrap
require_once (CORE_PATH . DS . 'library' . DS . 'Registry.php');
require_once (APP_PATH . DS . 'config' . DS . 'routes.php');
require_once (CORE_PATH . DS . 'library' . DS . 'Common.php');
require_once (CORE_PATH . DS . 'library' . DS . 'Router.php');
require_once (CORE_PATH . DS . 'library' . DS . 'Frostbite.php');

// Initiate Registry
$Registry = new Registry();

// Initiate the framework and let it do the rest ;)
$Frostbite = new Frostbite();
$Frostbite->Init();

?>