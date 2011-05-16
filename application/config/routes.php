<?php
/*
| ---------------------------------------------------------------
| Routes.php
| ---------------------------------------------------------------
|
| This file specifies the default routing of controllers
| and actions when there is no URI ( /welcome/home/ ), as
| well as the routes when loading a page from the DB.
|
*/

/* 
| default_controller: Name says it all.
| default_action: default method to load in a controller
*/
$routes['default_controller'] = 'welcome';
$routes['default_action'] = 'index';

/* 
| custom_controller: Controller to load for DB saved, custom pages
| custom_action: Default method to load in the custom controller
*/
$routes['custom_controller'] = 'page';
$routes['custom_action'] = 'index';

// EOF