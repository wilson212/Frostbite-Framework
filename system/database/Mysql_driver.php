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
namespace System\Database;

class Mysql_driver
{

	// Our Link Identifier
	private $mysql;
	
	// Mysql Hostname / Ip
	private $hostname;

	// Mysql Port
	private $port;

	// Mysql Username
	private $user;

	// Mysql Password
	private $pass;

	// Mysql Database Name
	public $database;

	// result of the last query
	public $result;

	// All sql statement ran
	public $sql = array();
	
	// Queries statistics.
	public $statistics = array(
		'time'  => 0,
		'count' => 0,
	);

/*
| ---------------------------------------------------------------
| Constructer
| ---------------------------------------------------------------
|
| Creates the connection to the mysql database, then selects the
| database.
|
*/
    public function __construct($host, $port, $user, $pass, $name)
    {
		// Fill Atributes
		$this->hostname = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
		$this->database = $name;
		
		// Connection time
		$this->connect();

		// Select DB
		$this->select_db($this->database);
		
		return TRUE;
    }
	
/*
| ---------------------------------------------------------------
| Function: connect
| ---------------------------------------------------------------
|
| Opens the database connection
|
*/
    public function connect()
    {
		// Make sure we arent already connected
		if(is_resource($this->mysql)) return;

		// Make the connection, or spit an error out
		if(!$this->mysql = mysql_connect($this->hostname.":".$this->port, $this->user, $this->pass, true))
		{
			show_error('db_connect_error', array( $host, $port ), E_ERROR);
		}
    }
	
/*
| ---------------------------------------------------------------
| Function: select_db
| ---------------------------------------------------------------
|
| Selects a database in the current connection
|
*/
    public function select_db($name)
    {
		// Make sure we are already connected
		if(!is_resource($this->mysql)) $this->connect();

		// Select our Database or spit out an error
		if(!mysql_select_db($name, $this->mysql))
		{
			show_error('db_select_error', array( $name ), E_ERROR);
		}

		// Return
		$this->database = $name;
		return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: close
| ---------------------------------------------------------------
|
| Closes the database connection
|
*/
    public function close()
    {
		// Make sure we are already connected
		if(!is_resource($this->mysql)) return TRUE;

		// Othereise clost the connection
		if(!@mysql_close($this->mysql))
		{
			return FALSE;
		}
		return TRUE;
    }
 
/*
| ---------------------------------------------------------------
| Function: query()
| ---------------------------------------------------------------
|
| The main method for querying the database. This method also
| benchmarks times for each query, as well as stores the query
| in the $sql array.
|
*/
    public function query($query)
    {
		// Create our benchmark array
		$bench['query'] = $query;

		// Time our query
		$start = microtime();
		$result = mysql_query($query, $this->mysql);
		$end = microtime();

		// Get our benchmark time
		$bench['time'] = round($end - $start, 5);

		// Add the query to the list of queries
		$this->sql[] = $bench;

		// Check for errors
		if(mysql_errno($this->mysql) !== 0)
		{
			$this->trigger_error();
		}

		// Up our statistic count
		$this->statistics['count']++;
		$this->statistics['time'] = ($this->statistics['time'] + $bench['time']);


		// Set result and return
		$this->result = $result;
		return $this;
    }
 
/*
| ---------------------------------------------------------------
| Function: fetch_array()
| ---------------------------------------------------------------
|
| fetch_array is great for getting the result that holds huge 
| arrays of multiple rows and tables
|
| @Param: $type - Basically the second parameter of mysql_fetch_array
|	http://www.php.net/manual/en/function.mysql-fetch-array.php 
|
*/
    public function fetch_array($type = MYSQL_ASSOC)
    {		
		// Get our number of rows
		$rows = $this->num_rows($this->result);

		// No rows mean a false to be returned!
		if($rows == 0)
		{
			return FALSE;
		}

		// More then 1 row, process as big array
		else
		{
			return mysql_fetch_array($this->result, $type);
		}		
    }
	
/*
| ---------------------------------------------------------------
| Function: fetch_row()
| ---------------------------------------------------------------
|
| fetch_row is equivilent to mysql_fetch_row()
|
*/
    public function fetch_row()
    {		
		// Get our number of rows
		$rows = $this->num_rows($this->result);

		// No rows mean a false to be returned!
		if($rows == 0)
		{
			return FALSE;
		}
		else
		{
			return mysql_fetch_row($this->result);
		}
    }
	
/*
| ---------------------------------------------------------------
| Function: fetch_result()
| ---------------------------------------------------------------
|
| fetch is equivalent to mysql_result()
|
| @Param: $result - The result number to be returned
|
*/
    public function fetch_result($result = 0)
    {		
		return mysql_result($this->result, $result);
    }
	
/*
| ---------------------------------------------------------------
| Function: clear_query()
| ---------------------------------------------------------------
|
| clears out the query. Not really needed to be honest as a new
| query will automatically call this method.
|
*/
    public function clear()
    {
		$this->sql = '';
    }
	
/*
| ---------------------------------------------------------------
| Function: result()
| ---------------------------------------------------------------
|
| Retunrs the result of the last query
|
*/
    public function result()
    {
		return $this->result;
    }

/*
| ---------------------------------------------------------------
| Function: get_insert_id(query)
| ---------------------------------------------------------------
|
| The equivelant to mysql_insert_id(); This functions get the last
| primary key from a previous insert
|
| @Param: $query - the query
|
*/
	public function get_insert_id()
	{
		return mysql_insert_id($this->mysql);
	}
	
/*
| ---------------------------------------------------------------
| Function: affected_rows()
| ---------------------------------------------------------------
|
| The equivelant to mysql_affected_rows();
|
*/
	public function affected_rows()
	{
		return mysql_affected_rows($this->result);
	}
	
/*
| ---------------------------------------------------------------
| Function: num_rows()
| ---------------------------------------------------------------
|
| The equivelant to mysql_num_rows();
|
*/
	public function num_rows()
	{
		return mysql_num_rows($this->result);
	}
	
/*
| ---------------------------------------------------------------
| Function: trigger_error()
| ---------------------------------------------------------------
|
| Trigger a Core error using Mysql custom error message
|
*/

	function trigger_error() 
	{
		$msg  = mysql_error($this->mysql) . "<br /><br />";
		$msg .= "<b>MySql Error No:</b> ". mysql_errno($this->mysql) ."<br />";
		$msg .= '<b>Query String:</b> ' . $this->sql;
		show_error($msg, false, E_ERROR);
	}
}
// EOF