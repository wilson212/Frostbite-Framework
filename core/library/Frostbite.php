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
| * USE THIS FILE AS THE BOOTSTRAP *
|
*/

define('ENGINE', 'Frostbite');
define('ENGINE_VERSION', '0.1');

// Get our current url, which is passed on by the htaccess file
$url = (isset($_GET['url']) ? $_GET['url'] : '');

// Include our 4 main required files
require_once (CORE_PATH . DS . 'library' . DS . 'Registry.php');
require_once (CORE_PATH . DS . 'library' . DS . 'Common.php');
require_once (CORE_PATH . DS . 'library' . DS . 'Router.php');
require_once (APP_PATH . DS . 'config' . DS . 'routes.php');

// Setup the config class, the autoloader will auto include the class file
$Config = load('Config');

// Fill in the config with the proper directory info if the directory info is wrong
define('SITE_DIR', dirname( $_SERVER['PHP_SELF'] ).'/');
define('SITE_HREF', stripslashes(str_replace('//', '/', SITE_DIR)));
define('SITE_BASE_HREF', 'http://'.$_SERVER["HTTP_HOST"]. SITE_HREF);

// If the site href doesnt match whats in the config, we need to set it
if($Config->get('site_base_href') != SITE_BASE_HREF)
{
	$Config->set('site_base_href', SITE_BASE_HREF);
	$Config->set('site_href', SITE_HREF);
	$Config->Save();
}	

// Setup the cache system	
$Cache = load('Cache');

// Start the engine and get this thing rolling!
$Router = load('Router');
$Router->Init_Engine();

// Please use EOF comments instead of closing php tags. For reason / more 
// info: http://codeigniter.com/user_guide/general/styleguide.html#php_closing_tag
// EOF