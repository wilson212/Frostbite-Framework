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
| @Param: (String) $name - The database name
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
| @Param: (String) $query - The full query statement
| @Param: (Array) $sprints - An array or replacemtnts of (?)'s 
|	in the $query
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

		// Time our query
		$start = microtime();
		$result = mysql_query($query, $this->mysql);
		$end = microtime();

		// Get our benchmark time
		$bench['time'] = round($end - $start, 5);

		// Add the query to the list of queries
		$this->queries[] = $bench;

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
|	but without the MYSQL_ part. 
|
*/
    public function fetch_array($type = 'ASSOC')
    {
		// Get our real type
		$type = strtoupper($type);
		switch($type)
		{
			case "ASSOC":
				$type = MYSQL_ASSOC;
				break;
			case "NUM":
				$type = MYSQL_NUM;
				break;
			default:
				$type = MYSQL_BOTH;
				break;
		}
		
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
| @Param: (Int) $result - The result number to be returned
|
*/
    public function fetch_result($result = 0)
    {		
		return mysql_result($this->result, $result);
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
		$values = '';

		// question marks for escaping values later on
		foreach(array_values($data) as $value)
		{
			if(is_numeric($value))
			{
				$values .= $value .", ";
				continue;
			}
			$values .= "'".$value ."', ";
		}

		// Remove the last comma
		$values = rtrim($values, ', ');

		// run the query
		$this->query('INSERT INTO ' . $table . '(' . $cols . ') VALUES (' . $values . ')');

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

		// Do we have a where tp process?
		($where != '') ? $where = ' WHERE ' . $where : '';

		// start creating the SQL string and enclose field names in `
		foreach($data as $key => $value) 
		{
			if(is_numeric($value))
			{
				$cols .= ', `' . $key . '` = '.$value;
				continue;
			}
			$cols .= ', `' . $key . '` = \''.$value.'\'';
		}

		// Trim the first comma, dont worry. ltrim is really quick :)
		$cols = ltrim($cols, ', ');

		// run the query
		$this->query('UPDATE ' . $table . ' SET ' . $cols . $where);

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
		$this->query('DELETE FROM ' . $table . ($where != '' ? ' WHERE ' . $where : ''));

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
| @Return: (None)
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
| @Return: (Object) Returns the result of the last query
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
| The equivelant to mysql_insert_id(); This functions get the last
| primary key from a previous insert
|
| @Return: (Int) Returns the insert id of the last insert
|
*/
	public function insert_id()
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
| @Return: (Int) Returns the number of affected row in the last query
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
| @Return: (Int) Returns the number of rows in the last result
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
		$query = end($this->queries);
		$msg  = mysql_error($this->mysql) . "<br /><br />";
		$msg .= "<b>MySql Error No:</b> ". mysql_errno($this->mysql) ."<br />";
		$msg .= '<b>Query String:</b> ' . $query['query'];
		show_error($msg, false, E_ERROR);
	}
}
// EOF