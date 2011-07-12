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

class Driver extends \PDO
{
	// Holds our driver name
	public $driver;
	
	// The most recen query
	public $last_query = '';

	// All sql statement that have been ran
	public $queries = array();
	
	// result of the last query
	public $result;
	
	// Our last queries number of rows / affected rows
	public $num_rows;
	
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
| Creates the connection to the database using PDO
|
*/
	public function __construct($info)
	{
		// Fill Atributes
		$hostname = $info['host'];
		$port = $info['port'];
		$user = $info['username'];
		$pass = $info['password'];
		$database = $info['database'];
		$this->driver = $driver = $info['driver'];
		
		// Create our DSN based off our driver
		if($driver == 'sqlite')
		{
			$filepath = ROOT . DS . $database;
			$dsn = 'sqlite:dbname='.$filepath;
		}
		else
		{
			$dsn = $driver .':dbname='.$database .';host='.$hostname .';port='.$port;
		}
		
		// Try and Connect to the database
		try 
		{
			// Connect using the PDO constructer
			parent::__construct($dsn, $user, $pass);
		}
		catch (\PDOException $e)
		{
			// So we caught an error, depending on our driver, is the info we spit out
			if($driver == 'sqlite')
			{
				show_error('db_connect_error', array( $database, $dsn, '' ), E_ERROR);
			}
			else
			{
				show_error('db_connect_error', array( $database, $hostname, $port ), E_ERROR);
			}
		}
		
		// Connection was a sucess, set our error attributes
		$this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
		// Add query to the last query and benchmark
		$bench['query'] = $this->last_query = $query;
		
		// Prepare the statement
		$this->result = $this->prepare($query);
		
		// Process our sprints and bind parameters
		if(is_array($sprints))
		{
			foreach($sprints as $key => $value)
			{
				// Get our param type
				if(is_int($value)) 
				{ 
					$type = \PDO::PARAM_INT; 
				}
				else
				{ 
					$type = \PDO::PARAM_STR; 
				}
				
				// Check key type
				if(is_numeric($key))
				{
					$this->result->bindParam(++$key, $value, $type);
				}
				else
				{
					$this->result->bindParam($key, $value, $type);
				}
			}
		}

		// Time our query
		$start = microtime();
		try {
			$this->result->execute($sprints);
		}
		catch (\PDOException $e) { 
			$this->trigger_error();
		}
		$end = microtime();

		// Get our benchmark time
		$bench['time'] = round($end - $start, 5);
		
		// Get our number of rows
		$this->num_rows = $this->rowCount();

		// Add the query to the list of queries
		$this->queries[] = $bench;

		// Up our statistic count
		$this->statistics['count']++;
		$this->statistics['time'] = ($this->statistics['time'] + $bench['time']);

		// Return
		return $this;
    }
	
/*
| ---------------------------------------------------------------
| Function: exec()
| ---------------------------------------------------------------
|
| This method is the wrapper for PDO's exec method. We are intercepting
| so we can add the query to our statistics, and catch errors
|
| @Param: $query - The full query statement
|
*/
    public function exec($query)
    {
		// Add query to the last query and benchmark
		$bench['query'] = $this->last_query = $query;

		// Time our query
		$start = microtime();
		try {
			$result = parent::exec($query);
		}
		catch (\PDOException $e) { 
			$this->trigger_error();
		}
		$end = microtime();

		// Get our benchmark time
		$bench['time'] = round($end - $start, 5);

		// Add the query to the list of queries
		$this->queries[] = $bench;

		// Up our statistic count
		$this->statistics['count']++;
		$this->statistics['time'] = ($this->statistics['time'] + $bench['time']);

		// Return
		return $result;
    }

/*
| ---------------------------------------------------------------
| Function: rowCount()
| ---------------------------------------------------------------
|
| This method is a work around for getting the number of rows in
| a SELECT statement as most Databases dont return this value.
|
*/	
	public function rowCount() 
	{
		$regex = '/^SELECT (.*) FROM (.*)$/i';
		if(preg_match($regex, $this->last_query, $output) != FALSE) 
		{
			$stmt = parent::query("SELECT COUNT(*) FROM ". $output[2], \PDO::FETCH_NUM);
			++$this->statistics['count'];
			return $stmt->fetchColumn();
		}
		else
		{
			return $this->result->rowCount();
		}
	}
	
/*
| ---------------------------------------------------------------
| Function: fetch()
| ---------------------------------------------------------------
|
| fetch_row is equivilent to mysql_fetch_row()
|
*/
    public function fetch_array($type = 'ASSOC')
    {	
		// Make sure we dont have a false return
		if($this->result == FALSE) return FALSE;
		
		// Get our real type if we dont already have it
		if(strpos($type, '::') == FALSE)
		{
			$type = strtoupper($type);
			switch($type)
			{
				case "ASSOC":
					$type = \PDO::FETCH_ASSOC;
					break;
				case "NUM":
					$type = \PDO::FETCH_NUM;
					break;
				case "BOTH":
					$type = \PDO::FETCH_BOTH;
					break;
				case "COLUMN":
					$type = \PDO::FETCH_COLUMN;
					$argument = 0;
					break;
				case "CLASS":
					$type = \PDO::FETCH_CLASS;
					break;
				case "LAZY":
					$type = \PDO::FETCH_LAZY;
					break;
				case "INTO":
					$type = \PDO::FETCH_INTO;
					break;
				case "OBJ":
					$type = \PDO::FETCH_OBJ;
					break;
			}
		}
		
		// Get our row count
		$rows = $this->num_rows();
		
		// Check out our row count
		if($rows == 0)
		{
			// No rows
			return FALSE;
		}
		
		// Fetch the result array
		$result = $this->result->fetchAll($type);
		
		// Do we return just 1 row. or a multi-dem. array?
		if($rows == 1)
		{
			// Just 1 row, send a 1 dem. array
			return $result[0];
		}
		
		// Otheewise, return the whole array
		return $result;
    }
	
/*
| ---------------------------------------------------------------
| Function: fetch_column()
| ---------------------------------------------------------------
|
| fetchs the first column from the last array.
|
*/
    public function fetch_column()
    {		
		// Make sure we dont have a false return
		if($this->result == FALSE) return FALSE;
		
		return $this->result->fetch(\PDO::FETCH_COLUMN);
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
		$this->num_rows = $this->exec('INSERT INTO ' . $table . '(' . $cols . ') VALUES (' . $values . ')');

		// Return TRUE or FALSE
		if($this->num_rows > 0)
		{
			return TRUE;
		}
		return FALSE;
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
	public function update($table, $data, $where = '')
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
				$cols .= ', `' . $key . '` = '. $value;
				continue;
			}
			$cols .= ', `' . $key . '` = \''.$value.'\'';
		}

		// Trim the first comma, dont worry. ltrim is really quick :)
		$cols = ltrim($cols, ', ');

		// run the query
		$this->num_rows = $this->exec('UPDATE ' . $table . ' SET ' . $cols . $where);

		// Return TRUE or FALSE
		if($this->num_rows > 0)
		{
			return TRUE;
		}
		return FALSE;
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
		$this->num_rows = $this->exec('DELETE FROM ' . $table . ($where != '' ? ' WHERE ' . $where : ''));

		// Return TRUE or FALSE
		if($this->num_rows > 0)
		{
			return TRUE;
		}
		return FALSE;
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
| Function: last_insert_id()
| ---------------------------------------------------------------
|
| The equivelant to mysql_insert_id(); This functions get the last
| primary key from a previous insert
|
| @Return: (Int) Returns the insert id of the last insert
|
*/
	public function last_insert_id($colname = NULL)
	{
		return $this->lastInsertId($colname);
	}
	
/*
| ---------------------------------------------------------------
| Function: num_rows()
| ---------------------------------------------------------------
|
| This method returns 1 of 2 things. A) either the number of
| affected rows during the last insert/delete/update query. Or
| B) The number of rows (count) in the result array.
|
| @Return: (Int) Returns the number of rows in the last query
|
*/
	public function num_rows()
	{
		return $this->num_rows;
	}
	
/*
| ---------------------------------------------------------------
| Function: trigger_error()
| ---------------------------------------------------------------
|
| Trigger a Core error using a custom error message
|
*/

	protected function trigger_error() 
	{
		$errInfo = $this->result->errorInfo();
		$msg  = $errInfo[2] . "<br /><br />";
		$msg .= "<b>PDO Error No:</b> ". $errInfo[0] ."<br />";
		$msg .= "<b>". ucfirst($this->driver) ." Error No:</b> ". $errInfo[1] ."<br />";
		$msg .= "<b>Query String: </b> ". $this->last_query ."<br />";
		show_error($msg, false, E_ERROR);
	}
}
// EOF