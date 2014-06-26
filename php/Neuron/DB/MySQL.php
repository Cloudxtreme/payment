<?php
namespace Neuron\DB;

use bmgroup\Cloudwalkers\Models\Logger;
use Exception;
use mysqli;
use MySQLi_Result;
use Neuron\Core\Error;
use Neuron\Exceptions\DbException;

class MySQL extends Database
{
	/** @var  MySQLi */
	private $connection;
	
	
	public function connect ()
	{
		if (!isset ($this->connection))
		{
			try
			{
				$this->connection = new MySQLi (DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

				$this->connection->query ('SET names "' . DB_CHARSET . '"');
				//$this->connection->query ("SET time_zone = '+00:00'");
			}
			catch (Exception $e)
			{
				echo $e;
			}
			
			if (mysqli_connect_errno ()) 
			{
				printf ("Connect failed: %s\n", mysqli_connect_error());
				exit();
			}
		}
	}

	public function disconnect ()
	{
		Logger::getInstance ()->log ('Disconnecting database.');
		if (isset ($this->connection))
		{
			$this->connection->close ();
		}
		$this->connection = null;
	}
	
	public function getConnection ()
	{
		return $this->connection;
	}

	public function multiQuery ($sSQL)
	{
		$start = microtime (true);

		$this->connect ();

		// Increase the counter
		$this->query_counter ++;

		$result = $this->connection->multi_query (trim ($sSQL));

		// FLUSH RESULTS
		// @TODO make these usable
		do  {
			$r = $this->connection->store_result ();
			if ($r)
			{
				$r->free ();
			}

			if (!$this->connection->more_results ())
			{
				break;
			}

			//$this->connection->next_result();
		} while ($this->connection->next_result ());

		$duration = microtime (true) - $start;
		$this->addQueryLog ($sSQL, $duration);

		if (!$result)
		{
			//var_dump (debug_backtrace ());
			//$data = debug_backtrace ();
			//print_r ($data);


			//echo $sSQL;
			$ex = new DbException ('MySQL Error: '.$this->connection->error);
			$ex->setQuery ($sSQL);

			throw $ex;
		}

		elseif ($result instanceof MySQLi_Result)
		{
			return new Result ($result);
		}

		// Insert ID will return zero if this query was not insert or update.
		$this->insert_id = intval ($this->connection->insert_id);

		// Affected rows
		$this->affected_rows = intval ($this->connection->affected_rows);

		if ($this->insert_id > 0)
			return $this->insert_id;

		if ($this->affected_rows > 0)
			return $this->affected_rows;

		return $result;
	}
	
	/*
		Execute a query and return a result
	*/
	public function query ($sSQL)
	{
		$start = microtime (true);
		
		$this->connect ();
		
		// Increase the counter
		$this->query_counter ++;
		
		$result = $this->connection->query (trim ($sSQL));
		
		$duration = microtime (true) - $start;
		$this->addQueryLog ($sSQL, $duration);
		
		if (!$result)
		{
			//var_dump (debug_backtrace ());
			//$data = debug_backtrace ();
			//print_r ($data);


			echo $sSQL;
			throw new DbException ('MySQL Error: '.$this->connection->error);
		}
		
		elseif ($result instanceof MySQLi_Result)
		{
			return new Result ($result);
		}
		
		// Insert ID will return zero if this query was not insert or update.
		$this->insert_id = intval ($this->connection->insert_id);
		
		// Affected rows
		$this->affected_rows = intval ($this->connection->affected_rows);
		
		if ($this->insert_id > 0)
			return $this->insert_id;
		
		if ($this->affected_rows > 0)
			return $this->affected_rows;
		
		return $result;
	}
	
	public function escape ($txt)
	{
		if (is_array ($txt))
		{
			throw new Error ('Invalid parameter: escape cannot handle arrays.');
		}
		$this->connect ();
		return $this->connection->real_escape_string ($txt);
	}
	
	public function fromUnixtime ($timestamp)
	{
		$query = $this->query ("SELECT FROM_UNIXTIME('{$timestamp}') AS datum");
		return $query[0]['datum'];
	}
	
	public function toUnixtime ($date)
	{
		$query = $this->query ("SELECT UNIX_TIMESTAMP('{$date}') AS datum");
		return $query[0]['datum'];
	}
}
?>
