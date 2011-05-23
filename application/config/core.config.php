<?php

/*
| ---------------------------------------------------------------
| enable_hooks
| ---------------------------------------------------------------
|
| If you would like to use the 'hooks' feature you must enable it by
| setting this variable to TRUE (boolean).
|
*/
$config['enable_hooks'] = FALSE;

/*
| ---------------------------------------------------------------
| subclass_prefix
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

/*
| ---------------------------------------------------------------
| Parse Pages
| ---------------------------------------------------------------
|
| If you would like files to be parsed in the template parser,
| Set this value to TRUE (boolean), or FALSE otherwise.
|
*/
$config['parse_pages'] = TRUE;

// EOF