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
| @Param: $page - The source of the completed page
|
*/

	function send($page) 
	{
		eval('?>'.$page.'<?');
	}
}
// EOF