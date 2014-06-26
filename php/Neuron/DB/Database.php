<?php
namespace Neuron\DB;

// Let's just point the database to MySQL
use Neuron\Interfaces\Logger;

abstract class Database
{
	protected $insert_id = 0;
	protected $affected_rows = 0;
	protected $query_counter = 0;

	protected $query_log = array ();

	protected $origin_counter = array ();

	/** @var Logger */
	private $logger;

	/**
	 * @param string $id
	 * @return Database
	 */
	public static function __getInstance ($id = 'general')
	{
		static $in;

		if (!isset ($in))
		{
			$in = array ();
		}

		if (!isset ($in[$id]))
		{
			$tmp = new MySQL ();
			$in[$id] = $tmp;
		}

		return $in[$id];
	}

	public function connect ()
	{

	}

	public function disconnect ()
	{

	}

	public function setLogger (Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @return Database
	 */
	public static function getInstance ()
	{
		return self::__getInstance ();
	}

	public function getInsertId ()
	{
		return $this->insert_id;
	}

	public function getAffectedRows ()
	{
		return $this->affected_rows;
	}

	public function getQueryCounter ()
	{
		return $this->query_counter;
	}

	// Abstract functions
	public abstract function query ($sSQL);
	public abstract function multiQuery ($sSQL);
	public abstract function escape ($txt);

	public function start ()
	{
		$this->query ("START TRANSACTION");
	}

	public function commit ()
	{
		$this->query ("COMMIT");
	}

	public function rollback ()
	{
		$this->query ("ROLLBACK");
	}

	/**
	 * Just put a comment in the query log.
	 * Does not connect to database.
	 * @param $txt
	 */
	public function log ($txt)
	{
		$this->addQueryLog ("/* " . $txt . " */");
	}

	protected function addQueryLog ($sSQL, $duration = null)
	{
		/*
		if (! (defined ('DEBUG') && DEBUG) && !isset ($_GET['debug']))
		{
			return;
		}
		*/

		if (count ($this->query_log) > 5000)
		{
			array_shift ($this->query_log);
		}

		$stacktrace = debug_backtrace ();
		$origin = $stacktrace[1];

		//var_dump ($stacktrace[2]);

		if (isset ($stacktrace[2]['class']) && $stacktrace[2]['class'] == 'Neuron\DB\Query')
		{
			$origin = $stacktrace[2];
		}

		$txt = '[' . number_format ($duration, 3) . ' s] ';
		//$txt .= $origin['class'] . ' ';

		// QUery took longer than 1 second?
		/*
		if ($duration > 0.1)
		{
			$txt = '<span style="color: red; font-weight: bold;">' . $txt . '</span>';
		}
		*/

		$txt .= trim ($sSQL);

		$this->increaseOriginCounter ($origin['file'], $origin['line']);
		//$txt .= '<br />' . $origin['file'] . ':' . $origin['line'];

		if (isset ($this->logger))
		{
			$color = 'green';
			if (strpos ($txt, 'START') !== false || strpos ($txt, 'COMMIT') !== false || strpos ($txt, 'ROLLBACK') !== false)
			{
				$color = 'red';
			}

			$this->logger->log ('DB: ' . preg_replace('!\s+!', ' ', str_replace ("\t", " ", str_replace ("\n", "", $txt))), false, $color);
		}

		$this->query_log[] = $txt;
	}

	public function getLastQuery ()
	{
		return $this->query_log[count ($this->query_log) - 1];
	}

	public function getAllQueries ()
	{
		return $this->query_log;
	}

	public function getConnection ()
	{

	}

	private function increaseOriginCounter ($file, $line)
	{
		if (!isset ($this->origin_counter[$file . ':' . $line]))
		{
			$this->origin_counter[$file . ':' . $line] = 1;
		}
		else
		{
			$this->origin_counter[$file . ':' . $line] ++;
		}
	}

	public function getOriginCounters ()
	{
		arsort ($this->origin_counter, SORT_NUMERIC);

		$out = array ();
		foreach ($this->origin_counter as $k => $v)
		{
			$out[] = '<strong>Queries: ' . $v . '</strong>: ' . $k;
		}
		return $out;
	}

	// Functions that should not be used... but well, we can't do without them at the moment
	public abstract function fromUnixtime ($timestamp);
	public abstract function toUnixtime ($date);
}
?>
