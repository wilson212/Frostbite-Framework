<?php

/*
| ---------------------------------------------------------------
| Error_display_level
| ---------------------------------------------------------------
|
| This is the error level of which you would like to show when
| viewing the website. This should be set to 3 (all errors) when
| doing enviorment testing, and just 1 ( Fetal errors ) or 2 
| ( Fetal errors and Mysql Errors) for live sites.
|
| Levels:
| 	3 = all errors (including MySQL), warnings, and notices.
|	2 = All Fetal errors, and Mysql errors
|	1 = Fetal errors only ( No mysql errors )
*/
$config['error_display_level'] = 3;

/*
| ---------------------------------------------------------------
| Log_errors
| ---------------------------------------------------------------
|
| Set to 1 to log errors in the error log. Set to 0 to disable
| error logging.
|
*/
$config['log_errors'] = 1;

/*
| ---------------------------------------------------------------
| Subclass_prefix
| ---------------------------------------------------------------
|
| Allows custom class prefixes for extended librarys
|
*/
$config['subclass_prefix'] = 'MY_';

/*
| ---------------------------------------------------------------
| Instance
| ---------------------------------------------------------------
|
| If you would like to change the SuperObject class to a custom
| controller class, specify the controller name here.
|
*/
$config['instance'] = 'Controller';

/*
| ---------------------------------------------------------------
| Auto load Libraries
| ---------------------------------------------------------------
|
| These are the classes located in the core/libraries folder
| or in your application/libraries folder. Use the format below
| to define which librarys are loaded. Donot prefix the classes
| as the prefixed classes will load automatically
|
| Format: array('Session', 'Database', 'Parser');
|
*/

$config['autoload_libraries'] = array();


/*
| ---------------------------------------------------------------
| Helpers
| ---------------------------------------------------------------
|
| These are the helper files located in the core/helpers folder
| or in your application/helpers folder.
|
| Format: array('helper_file', 'helper_file');
|
*/

$config['autoload_helpers'] = array();

/*
| ---------------------------------------------------------------
| Session: Use Database
| ---------------------------------------------------------------
|
| When useing the session class, do we allow session to be saved
| in the database ( for "Remeber Me's" ). NOTE, you must run
| the session_table.sql on your DB for this to be enabled!
|
| Format: TRUE or FALSE;
|
*/

$config['session_use_database'] = TRUE;

/*
| ---------------------------------------------------------------
| Session: Database Identifier
| ---------------------------------------------------------------
|
| Which Database is the Session Table located in? NOTE, you must
| have " $config['session_use_database'] " above Set to TRUE.
|
| Format: either numeric ( id in array ), or DB config array Key.
|
*/

$config['session_database_id'] = 'DB';

/*
| ---------------------------------------------------------------
| Session: Table Name
| ---------------------------------------------------------------
|
| Which is the session table name? NOTE, you must 
| " $config['session_use_database'] " above Set to TRUE.
|
| Format: String - Table name
|
*/

$config['session_table_name'] = 'session_table';

/*
| ---------------------------------------------------------------
| Session: Cookie Name
| ---------------------------------------------------------------
|
| Name of the cookie we are storing session information in
|
| Format: String - Cookie name
|
*/

$config['session_cookie_name'] = 'FB_session';

// EOF