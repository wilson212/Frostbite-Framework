<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2011, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: Output
| ---------------------------------------------------------------
|
| Main output file. Pushes source to the browser
|
*/
namespace System\Core;

class Output 
{

/*
| ---------------------------------------------------------------
| Function: send()
| ---------------------------------------------------------------
|
| Sends the page to the browser, which is stored in the variable $page
|
| @Param: (String) $page - The source of the completed page
| @Param: (Array) $data - Variables to be extracted
| @Return: (None) Displays page
|
*/

    function send($page, $data = array()) 
    {
        // Make sure our data is in an array format
        if(!is_array($data))
        {
            show_error('non_array', array('data', 'Output::send'), E_ERROR);
            $data = array();
        }
        
        // extract variables
        if(count($data) > 0)
        {
            extract($data);
        }
        
        // Spit out the page
        eval('?>'.$page.'<?');
    }
}
// EOF