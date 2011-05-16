<?php	
/* 
| --------------------------------------------------------------
| 
| Frostbite CMS, Powered by The New Frostbite Engine
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

// Include the Bootstrap File and let that take this thing away!
require_once (CORE_PATH . DS . 'library' . DS . 'Frostbite.php');

?>