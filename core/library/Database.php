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
	protected $sql = '';
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
			show_error(2, "Query was empty. Please build a query before calling the 'query' method!");
		}
		
		// Add semi colon
		$this->end_sql();
		
		switch($this->queryType)
		{
			case "SELECT":
				$this->result = $this->fetch($this->sql);
				break;
			
			case "COUNT":
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
		
		// No rows mean a false to be returned!
		if(mysql_num_rows($sql) == 0)
		{
			$result = FALSE;
		}
		
		// Lets process the return
		if($this->queryType == 'COUNT')
		{
			$row = mysql_fetch_array($sql);
			return $row['0'];
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
| Function: count_results()
| ---------------------------------------------------------------
|
| Counts the number of results
|
*/
	public function count_results()
    {
		return count( $this->result() );
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
		if(is_array($data))
		{
			if(count($data) > 1)
			{
				$this->sql = "SELECT ". mysql_real_escape_string( implode(',', $data) );
			}
			else
			{
				$this->sql = "SELECT ". mysql_real_escape_string($data[0]);
			}
		}
		else
		{
			$this->sql = "SELECT ". mysql_real_escape_string($data);
		}
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: select_max()
| ---------------------------------------------------------------
|
| select_max is used to initiate a SELECT MAX($col) query
|
| @Param: $col - the columns being selected
|
*/
	public function select_max($col) 
	{
		$col = mysql_real_escape_string($col);
		
		$this->queryType = "SELECT";
		$this->sql = "SELECT MAX(". $col .")";
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: select_min()
| ---------------------------------------------------------------
|
| select_min is used to initiate a SELECT MIN($col) query
|
| @Param: $col - the columns being selected
|
*/
	public function select_min($col) 
	{
		$col = mysql_real_escape_string($col);
		
		$this->queryType = "SELECT";
		$this->sql = "SELECT MIN(". $col .")";
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: select_avg()
| ---------------------------------------------------------------
|
| select_avg is used to initiate a SELECT AVG($col) query
|
| @Param: $col - the columns being selected
|
*/
	public function select_avg($col) 
	{
		$col = mysql_real_escape_string($col);
		
		$this->queryType = "SELECT";
		$this->sql = "SELECT AVG(". $col .")";
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: select_sum()
| ---------------------------------------------------------------
|
| select_sum is used to initiate a SELECT SUM($col) query
|
| @Param: $col - the columns being selected
|
*/
	public function select_sum($col) 
	{
		$col = mysql_real_escape_string($col);
		
		$this->queryType = "SELECT";
		$this->sql = "SELECT SUM(". $col .")";
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: select_count()
| ---------------------------------------------------------------
|
| select_sum is used to initiate a SELECT COUNT($col) query
|
| @Param: $col - the columns being selected
|
*/
	public function select_count($col) 
	{
		$col = mysql_real_escape_string($col);
		
		$this->queryType = "COUNT";
		$this->sql = "SELECT COUNT(". $col .")";
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
		$this->table = mysql_real_escape_string($table);
		if(count($data) > 1)
		{
			foreach($data as $key => $value)
			{
				if(!is_numeric($key))
				{
					$this->columns[] = mysql_real_escape_string($key);
				}
				
				if(!is_numeric($key))
				{
					$this->values[] = "'". mysql_real_escape_string($value) ."'";
				}
				else
				{
					$this->values[] = mysql_real_escape_string($value);
				}
			}
			if(count($this->columns) >= 1)
			{
				$this->sql = "INSERT INTO ". $table ." (". implode(',', $this->columns) .") VALUES (". implode(',', $this->values) .")";
			}
			else
			{
				$this->sql = "INSERT INTO ". $table ." VALUES (". implode(',', $this->values) .")";
			}
		}
		else
		{
			$key = mysql_real_escape_string( key($data) );
			$value = mysql_real_escape_string($data[0]);
			$this->sql = "INSERT INTO ". $table ." (". $key .") VALUES (". $value.")";
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
				$this->columns[] = mysql_real_escape_string($key);
				$this->values[] = mysql_real_escape_string($value);
			}
		}
		else
		{
			$this->columns[] = mysql_real_escape_string( key($data[0]) );
			$this->values[] = mysql_real_escape_string($data[0]);
		}
		
		$this->sql = "UPDATE ". $this->table ." SET ";
	
		$count = count($this->columns);
		for($i = 0; $i < $count; $i++) 
		{
			$this->sql .= $this->columns[$i] ." = ". $this->values[$i];
			if($i < ($count - 1)) 
			{
				$this->sql.= ", ";
			}
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
		$this->table = mysql_real_escape_string($table);
		$this->sql = "DELETE FROM ". $this->table;
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
		$col = mysql_real_escape_string($col);
		$val = mysql_real_escape_string($val);
		
		if(!is_numeric($val))
		{
			$val = "'". $val ."'";
		}
		$this->sql .= " WHERE ". $col ." = ". $val;	
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: and_where()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "AND WHERE $col = $val" to the query being built
|
| @Param: $col - the column
| @Param: $val - value of the column
|
*/
	public function and_where($col, $val) 
	{
		$col = mysql_real_escape_string($col);
		$val = mysql_real_escape_string($val);
		
		if(!is_numeric($val))
		{
			$val = "'". $val ."'";
		}
		$this->sql .= " AND WHERE ". $col ." = ". $val;	
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: or_where()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "OR WHERE $col = $val" to the query being built
|
| @Param: $col - the column
| @Param: $val - value of the column
|
*/
	public function or_where($col, $val) 
	{
		$col = mysql_real_escape_string($col);
		$val = mysql_real_escape_string($val);
		
		if(!is_numeric($val))
		{
			$val = "'". $val ."'";
		}
		$this->sql .= " OR WHERE ". $col ." = ". $val;	
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
		$this->table = mysql_real_escape_string($table);
		$this->sql .= " FROM ". $this->table;
		return $this;		
	}
	
/*
| ---------------------------------------------------------------
| Function: like()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "LIKE $like" to the query being built
|
| @Param: $like - what we are comparing to
|
*/	
	public function like($like) 
	{
		$this->sql .= " LIKE ". mysql_real_escape_string($like);
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: not_like()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "NOT LIKE $like" to the query being built
|
| @Param: $like - what we are comparing to
|
*/	
	public function not_like($like) 
	{
		$this->sql .= " NOT LIKE ". mysql_real_escape_string($like);
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: and_like()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "AND $sub LIKE $like" to the query being built
|
| @Param: $like - what we are comparing to
|
*/	
	public function and_like($sub, $like) 
	{
		$sub = mysql_real_escape_string($sub);
		$like = mysql_real_escape_string($like);
		
		$this->sql .= " AND ". $sub ." LIKE ". $like;
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: and_not_like()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "AND $sub NOT LIKE $like" to the query being built
|
| @Param: $like - what we are comparing to
|
*/	
	public function and_not_like($sub, $like) 
	{
		$sub = mysql_real_escape_string($sub);
		$like = mysql_real_escape_string($like);
		
		$this->sql .= " AND ". $sub ." NOT LIKE ". $like;
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
		$this->sql .= " GROUP BY ". mysql_real_escape_string($groupBy);
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
		$this->sql .= " HAVING ". mysql_real_escape_string($having);
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
		$this->sql .= " ORDER BY ". mysql_real_escape_string($orderBy);
		return $this;
	}

/*
| ---------------------------------------------------------------
| Function: limit()
| ---------------------------------------------------------------
|
| Querybuilder: Adds "LIMIT $limit" to the query being built
|
| @Param: $start - start position of the query results
| @Param: $end - end position of the query results
|
*/
	public function limit($start, $end) 
	{
		$start = mysql_real_escape_string($start);
		$end = mysql_real_escape_string($end);
		
		$this->sql .= " LIMIT ". $start .",". $end;
		return $this;
	}
	
/*
| ---------------------------------------------------------------
| Function: end_sql()
| ---------------------------------------------------------------
|
| This method finishes the sql statement and can return the query.
|
| @Param: $return - Set to true if you want the sql query returned
|
*/
	public function end_sql($return = FALSE) 
	{
		if(empty($this->table))
		{
			show_error(2, "No table selected");
		}
		
		// Add semi colon
		$this->sql .= ";";
		
		if($return == TRUE)
		{
			return $this->sql;
		}
	}
}
// EOF