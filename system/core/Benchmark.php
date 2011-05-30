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

class Benchmark
{
	// Start and stop timers
	public static $start = array(); 
	public static $stop = array();

/*
| ---------------------------------------------------------------
| Function: startTimer()
| ---------------------------------------------------------------
|
| Starts a new timer
|
| @Param: $key - Name of this start time
|
*/
    public static function startTimer($key)
	{
		self::$start[$key] = microtime(1);
	}

/*
| ---------------------------------------------------------------
| Function: stopTimer()
| ---------------------------------------------------------------
|
| Stops a defined timer
|
| @Param: $key - Name of this timer to be stopped
|
*/
    private static function stopTimer($key)
	{
		self::$stop[$key] = microtime(1);
	}

/*
| ---------------------------------------------------------------
| Function: showTimer()
| ---------------------------------------------------------------
|
| Displays the final time from start to finish
|
*/
    public static function showTimer($key, $round = 3)
	{
		if(count(self::$start[$key]) == 0)
		{
			show_error(1, 'Before displaying a timer, You need to start it first!');
		}
		else
		{
			if(!isset(self::$stop[$key]))
			{
				self::$stop[$key] = microtime(1);
			}
			return round( (self::$stop[$key] - self::$start[$key]), $round);
		}
	}
    
/*
| ---------------------------------------------------------------
| Function: memory_usage()
| ---------------------------------------------------------------
|
| Returns the amount of memory the system has used to load the page
|
*/
	public static function memory_usage() 
	{
		$usage = '';	 
		$mem_usage = memory_get_usage(true); 
		
		if($mem_usage < 1024) 
		{
			$usage =  $mem_usage." Bytes"; 
		}
		elseif($mem_usage < 1048576) 
		{
			$usage = round($mem_usage/1024, 2)." Kilobytes"; 
		}
		else
		{ 
			$usage = round($mem_usage/1048576, 2)." Megabytes"; 
		}	
		return $usage;
	}
}
// EOF 