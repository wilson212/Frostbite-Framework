<?php
/* 
| --------------------------------------------------------------
| File: class.database.php
| Description: Main CMS database class
| Written by: Steven Wilson
| --------------------------------------------------------------
|
*/

class Database
{

	// Queries statistics.
    var $_statistics = array(
        'time'  => 0,
        'count' => 0,
    );
    private $mysql;

/*
| ---------------------------------------------------------------
| Constructer: Database(host, port, user, pass, database name)
| ---------------------------------------------------------------
|
|Creates the connection to the mysql database, selects the posted DB
|
*/
    public function Database($db_host, $db_port, $db_user, $db_pass, $db_name)
    {
        $this->mysql = @mysql_connect($db_host.":".$db_port, $db_user, $db_pass, true) or Core::trigger_error(2, 'Cant connect to "'.$db_host.'" using port "'.$db_port.'"!', __FILE__, __LINE__);
        @mysql_select_db($db_name, $this->mysql) or Core::trigger_error(2, 'Cant connect to database: "'.$db_name.'"', __FILE__, __LINE__);
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
| Function: query(query)
| ---------------------------------------------------------------
|
| Query function is best used for INSERT and UPDATE functions
|
| @Param: $query - the query
|
*/
    public function query($query)
    {
        $sql = @mysql_query($query, $this->mysql) or $this->trigger_error($query);
		$this->_statistics['count']++;
		return TRUE;
    }
 
/*
| ---------------------------------------------------------------
| Function: select(query)
| ---------------------------------------------------------------
|
| Select function is great for getting huge arrays of multiple rows and tables
|
| @Param: $query - the query
|
*/
    public function select($query)
    {
        $sql = @mysql_query($query,$this->mysql) or $this->trigger_error($query);
		$this->_statistics['count']++;
		$i = 1;
		if(mysql_num_rows($sql) == 0)
		{
			$result = FALSE;
		}
		else
		{
			while($row = mysql_fetch_assoc($sql))
			{
				foreach($row as $colname => $value)
				{
					$result[$i][$colname] = $value;
				}
				$i++;
			}
		}
		return $result;
    }

/*
| ---------------------------------------------------------------
| Function: selectRow(query)
| ---------------------------------------------------------------
|
| selectRow is perfect for getting 1 row of data. Technically can 
| be used for multiple rows though select function is better 
| for more then 1 row
|
| @Param: $query - the query
|
*/
	public function selectRow($query)
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
| Function: selectCell(query)
| ---------------------------------------------------------------
|
| selectCell returns 1 cell of data, Not recomended unless you 
| want data from a specific cell in a table
|
| @Param: $query - the query
|
*/
	public function selectCell($query)
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
| Function: trigger_error(query)
| ---------------------------------------------------------------
|
| Trigger a Core error using Mysql custom error message
|
| @Param: $query - the query
|
*/

	function trigger_error($query) 
	{
		$msg  = mysql_error($this->mysql) . "<br /><br />";
		$msg .= "<b>MySql Error No:</b> ". mysql_errno($this->mysql) ."<br />";
		$msg .= '<b>Query String:</b> ' . $query;
		Core::trigger_error(2, $msg);
	}
}
?>