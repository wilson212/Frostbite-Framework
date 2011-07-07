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

class Sqlite_driver
{
	// The sqlite class
	public $sqlite;
	
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
		// Connect
		$filepath = SYSTEM_PATH . DS . 'database' . DS  . $this->database;
		if( !$this->sqlite = new \SQLiteDatabase($filepath, 0666, $error) )
		{
			show_error('db_connect_error', array( $this->database, 'local' ), E_ERROR);
		}
    }
	
/*
| ---------------------------------------------------------------
| Function: close
| ---------------------------------------------------------------
|
| Closes the SQLite DB connection
|
*/
    public function close()
    {	
		// Connect
		$this->sqlite = NULL;
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
		$result = $this->sqlite->query($query);
		$end = microtime();

		// Get our benchmark time
		$bench['time'] = round($end - $start, 5);

		// Add the query to the list of queries
		$this->queries[] = $bench;

		// Check for errors
		if($this->lastError() !== 0)
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
| @Param: $type - Basically the second parameter of sqlite_fetch_array
|	http://www.php.net/manual/en/function.sqlite-fetch-array.php
|	but without the SQLITE_ part. 
|
*/
    public function fetch_array($type = 'ASSOC')
    {		
		// Get our real type
		$type = strtoupper($type);
		switch($type)
		{
			case "ASSOC":
				$type = SQLITE_ASSOC;
				break;
			case "NUM":
				$type = SQLITE_NUM;
				break;
			default:
				$type = SQLITE_BOTH;
				break;
		}
	
		// More then 1 row, process as big array
		if($this->result !== FALSE)
		{
			return $this->sqlite->fetch($type);
		}
		return FALSE		
    }
	
/*
| ---------------------------------------------------------------
| Function: fetch_row()
| ---------------------------------------------------------------
|
| fetch_row is equivilent to mysql_fetch_row(), but for sqlite
|
*/
    public function fetch_row()
    {		
		if($this->result !== FALSE)
		{
			// Remember, we want 1 row so return after first loop
			$row = $this->result->fetch();
			return $row[0];
		}
		return FALSE;
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
    public function fetch_result()
    {		
		return $this->result->fetchSingle();
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
| The equivelant to sqlite_last_insert_rowid(); This functions get 
| the last primary key from a previous insert
|
| @Return: (Int) Returns the insert id of the last insert
|
*/
	public function insert_id()
	{
		return $this->sqlite->lastInsertRowid();
	}
	
/*
| ---------------------------------------------------------------
| Function: affected_rows()
| ---------------------------------------------------------------
|
| The equivelant to sqlite_chages();
|
| @Return: (Int) Returns the number of affected row in the last query
|
*/
	public function affected_rows()
	{
		if($this->result !== FALSE)
		{
			return $this->sqlite->changes();
		}
		return FALSE
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
		if($this->result !== FALSE)
		{
			return $this->result->numRows();
		}
		return FALSE;
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
		$msg  = sqlite_error_string( $this->sqlite->lastError() ). "<br /><br />";
		$msg .= "<b>Sqlite Error No:</b> ". $this->sqlite->lastError() ."<br />";
		$msg .= '<b>Query String:</b> ' . $query['query'];
		show_error($msg, false, E_ERROR);
	}
}
// EOF