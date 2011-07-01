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

class Mysqli_driver
{

	// Our Link Identifier
	private $mysqli;
	
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

	// All sql statement that have been ran
	public $queries = array();
	
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
		if(is_object($this->mysqli)) return;
		
		// Connect
		$this->mysqli = new \mysqli($this->hostname, $this->user, $this->pass, $this->database, $this->port);

		// Make the connection, or spit an error out
		if(mysqli_connect_errno())
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
| @Param: (String) $name - The database name
|
*/
    public function select_db($name)
    {
		// Make sure we are already connected
		if(!is_object($this->mysqli)) $this->connect();

		// Select our Database or spit out an error
		if(!$this->mysqli->select_db($name))
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
		if(!is_object($this->mysqli)) return TRUE;
		
		// Kill first to make sure we close the TCP
		$this->mysqli->kill( $this->mysqli->thread_id );

		// Othereise clost the connection
		if(!$this->mysqli->close())
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
| @Param: $query - The full query statement
| @Param: $sprints - An array or replacemtnts of (?)'s in the $query
|
*/
    public function query($query, $sprints = NULL)
    {
		// Check to see if we need to do replacing
		if(is_array($sprints))
		{
			$query = str_replace('?', '%s', $query);
			$query = vsprintf($query, $sprints);
		}
		
		// Create our benchmark array
		$bench['query'] = $query;
		
		// Clear old query
		$this->result = NULL;

		// Time our query
		$start = microtime();
		$result = $this->mysqli->query($query);
		$end = microtime();

		// Get our benchmark time
		$bench['time'] = round($end - $start, 5);

		// Add the query to the list of queries
		$this->queries[] = $bench;

		// Check for errors
		if($this->mysqli->errno !== 0)
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
		$rows = $this->num_rows();

		// No rows mean a false to be returned!
		if($rows == 0)
		{
			return FALSE;
		}

		// More then 1 row, process as big array
		else
		{
			return $this->result->fetch_array($type);
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
		$rows = $this->num_rows();

		// No rows mean a false to be returned!
		if($rows == 0)
		{
			return FALSE;
		}
		else
		{
			return $this->result->fetch_row();
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
		$res = $this->result->fetch_row();
		($res !== FALSE) ? $res = $res[$result] : '';
		return $res;
    }
	
/*
| ---------------------------------------------------------------
| Function: insert()
| ---------------------------------------------------------------
|
| An easy method that will insert data into a table
|
| @Param: (String) $table - The table name we are inserting into
| @Param: (String) $data - An array of "column => value"'s
| @Return: (Bool) Returns TRUE on success of FALSE on error
|
*/
	public function insert($table, $data)
	{
		// enclose the column names in grave accents
        $cols = '`' . implode('`,`', array_keys($data)) . '`';

        // question marks for escaping values later on
        $values = rtrim(str_repeat('?,', count($data)), ',');

        // run the query
        $this->mysqli->query('INSERT INTO ' . $table . '(' . $cols . ') VALUES (' . $values . ')', array_values($data));

		// Return TRUE or FALSE
        return $this->result;
	}
	
/*
| ---------------------------------------------------------------
| Function: update()
| ---------------------------------------------------------------
|
| An easy method that will update data in a table
|
| @Param: (String) $table - The table name we are inserting into
| @Param: (Array) $data - An array of "column => value"'s
| @Param: (String) $where - The where statement Ex: "id = 5"
| @Return: (Bool) Returns TRUE on success of FALSE on error
|
*/	
	function update($table, $data, $where = '')
    {
		// Our string of columns
        $cols = '';

        // start creating the SQL string and enclose field names in `
        foreach($data as $key => $value) 
		{
			if(is_numeric($value))
			{
				$cols .= ', `' . $key . '` = '.$value.'';
				continue;
			}
			$cols .= ', `' . $key . '` = \''.$value.'\'';
        }
		
		// Trim the first comma, dont worry. ltrim is really quick :)
		$cols = ltrim($cols, ', ');

        // run the query
        $this->mysqli->query('UPDATE ' . $table . ' SET ' . $cols . ($where != '' ? ' WHERE ' . $where : ''));
		
		// Return TRUE or FALSE
        return $this->result;
    }
	
/*
| ---------------------------------------------------------------
| Function: delete()
| ---------------------------------------------------------------
|
| An easy method that will delete data from a table
|
| @Param: (String) $table - The table name we are inserting into
| @Param: (String) $where - The where statement Ex: "id = 5"
| @Return: (Bool) Returns TRUE on success of FALSE on error
|
*/
	public function delete($table, $where = '')
	{
        // run the query
        $this->mysqli->query('DELETE FROM ' . $table . ($where != '' ? ' WHERE ' . $where : ''));

		// Return TRUE or FALSE
        return $this->result;
	}
	
/*
| ---------------------------------------------------------------
| Function: reset()
| ---------------------------------------------------------------
|
| Clears out and resets the query statistics
|
*/
    public function reset()
    {
		$this->queries = array();
		$this->statistics = array(
			'time'  => 0,
			'count' => 0
		);
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
| Function: insert_id()
| ---------------------------------------------------------------
|
| The equivelant to mysqli_insert_id(); This functions get the last
| primary key from a previous insert
|
| @Param: $query - the query
|
*/
	public function insert_id()
	{
		return $this->mysqli->insert_id;
	}
	
/*
| ---------------------------------------------------------------
| Function: affected_rows()
| ---------------------------------------------------------------
|
| The equivelant to mysqli_affected_rows();
|
*/
	public function affected_rows()
	{
		return $this->mysqli->affected_rows;
	}
	
/*
| ---------------------------------------------------------------
| Function: num_rows()
| ---------------------------------------------------------------
|
| The equivelant to mysqli_num_rows();
|
*/
	public function num_rows()
	{
		return $this->result->num_rows;
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
		$query = $temp['query'] = end($this->queries);
		$msg  = $this->mysqli->error . "<br /><br />";
		$msg .= "<b>MySql Error No:</b> ". $this->mysql->errno ."<br />";
		$msg .= '<b>Query String:</b> ' . $query;
		show_error($msg, false, E_ERROR);
	}
}
// EOF