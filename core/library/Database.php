<?php

class Database
{

	// Queries statistics.
    var $_statistics = array(
        'time'  => 0,
        'count' => 0,
    );
    private $mysql;
	public $queryType;
	public $result;
	protected $sql;
	protected $query;
	protected $table;
	protected $where;
	protected $groupBy;
	protected $having;
	protected $orderBy;
	protected $limit;
	protected $columns = array(); 
	protected $values  = array();

/*
| ---------------------------------------------------------------
| Constructer: Database(host, port, user, pass, database name)
| ---------------------------------------------------------------
|
| Creates the connection to the mysql database, selects the posted DB
|
*/
    public function Database($db_host, $db_port, $db_user, $db_pass, $db_name)
    {
        $this->mysql = @mysql_connect($db_host.":".$db_port, $db_user, $db_pass, true) or show_error(2, 'Cant connect to "'.$db_host.'" using port "'.$db_port.'"!', __FILE__, __LINE__);
        @mysql_select_db($db_name, $this->mysql) or show_error(2, 'Cant connect to database: "'.$db_name.'"', __FILE__, __LINE__);
		return TRUE;
    }

/*
| ---------------------------------------------------------------
| Function: __destruct
| ---------------------------------------------------------------
|
| Closes the database connection
|
*/
    public function __destruct()
    {
        @mysql_close($this->mysql) or die(mysql_error());
    }
 
/*
| ---------------------------------------------------------------
| Function: query()
| ---------------------------------------------------------------
|
| Query function is best used for INSERT and UPDATE functions
|
*/
    public function query()
    {
		if(empty($this->sql))
		{
			$this->build();
		}
		
		switch($this->queryType)
		{
			case "SELECT":
				$this->result = $this->fetch($this->sql);
				break;
				
			case "SELECT MAX":
				$this->result = $this->fetch($this->sql);
				break;
				
			case "SELECT MIN":
				$this->result = $this->fetch($this->sql);
				break;
				
			case "SELECT AVG":
				$this->result = $this->fetch($this->sql);
				break;
			
			default:
				$this->result = @mysql_query($this->sql, $this->mysql) or $this->trigger_error($query);
				$this->_statistics['count']++;
				break;
		}
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
| Function: fetch(query)
| ---------------------------------------------------------------
|
| fetch function is great for getting huge arrays of multiple rows and tables
|
| @Param: $query - the query
|
*/
    public function fetch($query)
    {
        $sql = @mysql_query($query, $this->mysql) or $this->trigger_error($query);
		$this->_statistics['count']++;
		$i = 1;
		if(mysql_num_rows($sql) == 0)
		{
			$result = FALSE;
		}
		elseif(mysql_num_rows($sql) > 1)
		{
			while($row = mysql_fetch_assoc($sql))
			{
				foreach($row as $colname => $value)
				{
					$result[$i][$colname] = $value;
				}
				$i++;
			}
			return $result;
		}
		else
		{
			$row = mysql_fetch_array($sql);
			return $row;
		}
    }

/*
| ---------------------------------------------------------------
| Function: fetchRow(query)
| ---------------------------------------------------------------
|
| fetchRow is perfect for getting 1 row of data. Technically can 
| be used for multiple rows though select function is better 
| for more then 1 row
|
| @Param: $query - the query
|
*/
	public function fetchRow($query)
    {
        $sql = @mysql_query($query,$this->mysql) or $this->trigger_error($query);
		$this->_statistics['count']++;
		if(mysql_num_rows($sql) == 0)
		{
			return FALSE;
		}
		else
		{
			$row = mysql_fetch_array($sql);
			return $row;
		}
    }

/*
| ---------------------------------------------------------------
| Function: fetchCell(query)
| ---------------------------------------------------------------
|
| fetchCell returns 1 cell of data, Not recomended unless you 
| want data from a specific cell in a table
|
| @Param: $query - the query
|
*/
	public function fetchCell($query)
    {
        $sql = @mysql_query($query,$this->mysql) or $this->trigger_error($query);
		$this->_statistics['count']++;
		if(mysql_num_rows($sql) == 0)
		{
			return FALSE;
		}
		else
		{
			$row = mysql_fetch_array($sql);
			return $row['0'];
		}
    }

/*
| ---------------------------------------------------------------
| Function: count(query)
| ---------------------------------------------------------------
|
| count is a perfect function for counting the num of rows, or results in a table 
| returns the direct count, for ex: 5
|
| @Param: $query - the query
|
*/
	public function count($query)
    {
        $sql = @mysql_query($query, $this->mysql) or $this->trigger_error($query);
		$this->_statistics['count']++;
		return mysql_result($sql, 0);
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
		return mysql_insert_id();
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
		return mysql_affected_rows();
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
		return mysql_num_rows();
	}

/*
| ---------------------------------------------------------------
| Function: trigger_error(query)
| ---------------------------------------------------------------
|
| Trigger a Core error using Mysql custom error message
|
| @Param: $query - the query
|
*/

	function trigger_error() 
	{
		$msg  = mysql_error($this->mysql) . "<br /><br />";
		$msg .= "<b>MySql Error No:</b> ". mysql_errno($this->mysql) ."<br />";
		$msg .= '<b>Query String:</b> ' . $this->query;
		Core::trigger_error(2, $msg);
	}

	
/*
|----------------------------------------------------------------
| 				START OF QUERY BUILDING METHODS
|----------------------------------------------------------------
*/



/*
| ---------------------------------------------------------------
| Function: select()
| ---------------------------------------------------------------
|
| select is used to initiate a SELECT query
|
| @Param: $data - the columns being selected
|
*/
	public function select($data) 
	{
		$this->queryType = "SELECT";
		if(count($data) > 1)
		{
			foreach($data as $key)
			{
				$this->columns[] = $key;
			}
		}
		else
		{
			$this->columns[] = $data;
		}
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: selectMax()
| ---------------------------------------------------------------
|
| selectMax is used to initiate a SELECT MAX($col) query
|
| @Param: $data - the columns being selected
|
*/
	public function selectMax($col) 
	{
		$this->queryType = "SELECT MAX";
		$this->columns[] = $col;
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: selectMin()
| ---------------------------------------------------------------
|
| selectMin is used to initiate a SELECT MIN($col) query
|
| @Param: $data - the columns being selected
|
*/
	public function selectMin($col) 
	{
		$this->queryType = "SELECT MIN";
		$this->columns[] = $col;
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: selectAvg()
| ---------------------------------------------------------------
|
| selectAvg is used to initiate a SELECT AVG($col) query
|
| @Param: $data - the columns being selected
|
*/
	public function selectAvg($col) 
	{
		$this->queryType = "SELECT AVG";
		$this->columns[] = $col;
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: insert()
| ---------------------------------------------------------------
|
| insert is used to initiate an INSERT query
|
| @Param: $table - the table we are inserting into
| @Param: $data - an array of ( column => value )
|
*/	
	public function insert($table, $data) 
	{
		$this->queryType = "INSERT";
		$this->table = $table;
		if(count($data) > 1)
		{
			foreach($data as $key => $value)
			{
				$this->columns[] = $key;
				$this->values[] = mysql_real_escape_string($value);
			}
		}
		else
		{
			$this->columns[] = key($data);
			$this->values[] = mysql_real_escape_string($data[0]);
		}
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: update()
| ---------------------------------------------------------------
|
| Update is used to initiate an UPDATE query
|
| @Param: $table - the table we are updating
| @Param: $data - an array of ( column => value )
|
*/	
	public function update($table, $data) 
	{
		$this->queryType = "UPDATE";
		$this->table = $table;
		if(count($data) > 1)
		{
			foreach($data as $key => $value)
			{
				$this->columns[] = $key;
				$this->values[] = mysql_real_escape_string($value);
			}
		}
		else
		{
			$this->columns[] = key($data[0]);
			$this->values[] = mysql_real_escape_string($data[0]);
		}
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: delete()
| ---------------------------------------------------------------
|
| delete is used to delete from a table
|
| @Param: $table - the table we are deleting data from
|
*/	
	public function delete($table) 
	{
		$this->queryType = "DELETE";
		$this->table = $table;
		return $this;
	}
		
/*
| ---------------------------------------------------------------
| Function: where()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "WHERE $col = $val" to the query being built
|
| @Param: $col - the column
| @Param: $val - value of the column
|
*/
	public function where($col, $val) 
	{
		$this->where = $col ." = ". mysql_real_escape_string($val);	
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: from()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "FROM $table" to the query being built
|
| @Param: $table - the table name
|
*/	
	public function from($table) 
	{
		$this->table = $table;
		return $this;		
	}

/*
| ---------------------------------------------------------------
| Function: groupBy()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "GROUP BY $groupby" to the query being built
|
| @Param: $groupBy - What we are grouping by
|
*/	
	public function groupBy($groupBy) 
	{
		$this->groupBy = $groupBy;
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: having()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "HAVING $having" to the query being built
|
| @Param: $having - what the table needs to have
|
*/	
	public function having($having) 
	{
		$this->having = $having;
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: orderBy()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "ORDER BY $orderBy" to the query being built
|
| @Param: $orderBy - How we are ording the result
|
*/	
	public function orderBy($orderBy) 
	{
		$this->orderBy = $orderBy;
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: limit()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "LIMIT $limit" to the query being built
|
| @Param: $limit - sets our limit of how many results are returned
|
*/
	public function limit($limit) 
	{
		$this->limit = $limit;
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: build()
| ---------------------------------------------------------------
|
| This method builds all of our query builder parts into a querystring
| This method isnt required as the 'query' method will build it for
| us if we choose not to.
|
| @Param: $return - Set to true if you want the sql query returned
|
*/
	public function build($return = FALSE) 
	{
		if(empty($this->table))
		{
			show_error(2, "No table selected");
		}
		
		$this->sql = "";
		switch ($this->queryType) 
		{
			case "SELECT":
				$this->sql .= "SELECT ";
				$this->sql .= implode(", ", $this->columns);
				$this->sql .= " FROM ".$this->table;
				
				// Add aditional parts if they are set
				if($this->where)   $this->sql .= " WHERE ". $this->where;
				if($this->groupBy) $this->sql .= " GROUP BY ". $this->groupBy;
				if($this->having)  $this->sql .= " HAVING " .$this->having;
				if($this->orderBy) $this->sql .= " ORDER BY ". $this->orderBy;
				if($this->limit)   $this->sql .= " LIMIT ". $this->limit;				
				break;
				
			case "SELECT MAX":
				$this->sql .= "SELECT MAX(";
				$this->sql .= implode(", ", $this->columns);
				$this->sql .= ") FROM ".$this->table;
				
				// Add aditional parts if they are set
				if($this->where)   $this->sql .= " WHERE ". $this->where;				
				break;
				
			case "SELECT MIN":
				$this->sql .= "SELECT MIN(";
				$this->sql .= implode(", ", $this->columns);
				$this->sql .= ") FROM ".$this->table;
				
				// Add aditional parts if they are set
				if($this->where)   $this->sql .= " WHERE ". $this->where;				
				break;
				
			case "SELECT AVG":
				$this->sql .= "SELECT AVG(";
				$this->sql .= implode(", ", $this->columns);
				$this->sql .= ") FROM ".$this->table;
				
				// Add aditional parts if they are set
				if($this->where)   $this->sql .= " WHERE ". $this->where;				
				break;
				
			case "INSERT":
				$this->sql .= "INSERT INTO ". $this->table;
				
				$this->sql .= " (";
				$this->sql .= implode(", ", $this->columns);
				$this->sql .= ") ";
				
				$this->sql .= "VALUES";
				
				$this->sql .= " (";
				$this->sql .= implode(", ", $this->values);
				$this->sql .= ")";
				break;
				
			case "UPDATE":
				$this->sql .= "UPDATE ". $this->table ." SET ";
				
				$count = count($this->columns);
				for($i = 0; $i < $count; $i++) 
				{
					$this->sql .= $this->columns[$i] ." = ". $this->values[$i];
					if($i < ($count - 1)) 
					{
						$this->sql.= ", ";
					}
				}
				
				// Add aditional parts if they are set
				if($this->where) $this->sql .= " WHERE ". $this->where;
				if($this->limit) $this->sql .= " LIMIT ". $this->limit;				
				break;
				
			case "DELETE":
				$this->sql .= "DELETE FROM ". $this->table;
				
				// Add aditional parts if they are set
				if ($this->where) $this->sql .= " WHERE ". $this->where;
				break;
				
		}
		
		$this->sql .= ";";
		
		if($return == TRUE)
		{
			return $this->sql;
		}
	}
}
?>