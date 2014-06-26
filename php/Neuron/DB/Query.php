<?php
/**
 * A little helper class for mysql query
 *
 * @package Neuron\DB\Query
 */

namespace Neuron\DB;

use Neuron\Exceptions\InvalidParameter;
use Neuron\Models\Geo\Point;

class Query 
{
	const PARAM_NUMBER = 1;
	const PARAM_DATE = 2;
	const PARAM_STR = 3;
	const PARAM_STRING = 3;
	const PARAM_NULL = 4;

	const PARAM_POINT = 10;

	private $query;
	private $values = array ();

	/**
	 * Generate an insert query
	 * @param $table: table to insert data to
	 * @param $set: a 2 dimensional array with syntax: { column_name : [ value, type, nullOnEmpty ]}
	 * @return Query
	 */
	public static function insert ($table, array $set)
	{
		$query = 'INSERT INTO `' . $table . '` SET ';
		$values = array ();		
		foreach ($set as $k => $v)
		{
			$query .= $k . ' = ?, ';

			// No array? Then it's a simple string.
			if (is_array ($v))
			{
				$values[] = $v;
			}
			else
			{
				$values[] = array ($v);
			}
		}

		$query = substr ($query, 0, -2);

		$query = new self ($query);
		$query->bindValues ($values);

		return $query;
	}

	/**
	 * Generate an replace query
	 * @param $table: table to insert data to
	 * @param $set: a 2 dimensional array with syntax: { column_name : [ value, type, nullOnEmpty ]}
	 * @return Query
	 */
	public static function replace ($table, array $set)
	{
		$query = 'REPLACE INTO `' . $table . '` SET ';
		$values = array ();
		foreach ($set as $k => $v)
		{
			$query .= $k . ' = ?, ';

			// No array? Then it's a simple string.
			if (is_array ($v))
			{
				$values[] = $v;
			}
			else
			{
				$values[] = array ($v);
			}
		}

		$query = substr ($query, 0, -2);

		$query = new self ($query);
		$query->bindValues ($values);

		return $query;
	}

	/**
	 * Generate an insert query
	 * @param $table: table to insert data to
	 * @param $set: a 2 dimensional array with syntax: { column_name : [ value, type, nullOnEmpty ]}
	 * @param $where: a 2 dimensional array with syntax: { column_name : [ value, type, nullOnEmpty ]}
	 * nullOnEmpty may be omitted.
	 * @return Query
	 */
	public static function update ($table, array $set, array $where)
	{
		$query = 'UPDATE `' . $table . '` SET ';
		$values = array ();
		foreach ($set as $k => $v)
		{
			$query .= $k . ' = ?, ';

			// No array? Then it's a simple string.
			if (is_array ($v))
			{
				$values[] = $v;
			}
			else
			{
				$values[] = array ($v);
			}
		}

		$query = substr ($query, 0, -2) . ' ';
		$query .= self::processWhere ($where, $values);

		$query = new self ($query);
		$query->bindValues ($values);

		return $query;
	}

	private static function processWhere (array $where, &$values)
	{
		$query = '';

		if (count ($where) > 0)
		{
			$query .= 'WHERE ';
			foreach ($where as $k => $v)
			{
				// No array? Then it's a simple string.
				if (is_array ($v))
				{
					$tmp = $v;
				}
				else
				{
					$tmp = array ($v, self::PARAM_STR);
				}

				if (!is_array ($tmp[0]) && substr ($tmp[0], 0, 1) == '!')
				{
					$query .= $k . ' != ? AND ';
					$tmp[0] = substr ($tmp[0], 1);
				}

				else if (isset ($tmp[2]) && strtoupper ($tmp[2]) == 'LIKE')
				{
					$query .= $k . ' LIKE ? AND ';
					$tmp = array ($tmp[0], $tmp[1]);
				}

				else if (isset ($tmp[2]) && strtoupper ($tmp[2]) == 'NOT')
				{
					$query .= $k . ' != ? AND ';
					$tmp = array ($tmp[0], $tmp[1]);
				}

				else if (isset ($tmp[2])
					&& (
						strtoupper ($tmp[2]) == '>'
						|| strtoupper ($tmp[2]) == '<'
						|| strtoupper ($tmp[2]) == '>='
						|| strtoupper ($tmp[2]) == '<='
						|| strtoupper ($tmp[2]) == '!='
					)
				)
				{
					$query .= $k . ' ' . $tmp[2] . ' ? AND ';
					$tmp = array ($tmp[0], $tmp[1]);
				}

				else if (isset ($tmp[2]) && strtoupper ($tmp[2]) == 'IN')
				{
					$query .= $k . ' ' . $tmp[2] . ' ? AND ';
					$tmp = array ($tmp[0], $tmp[1]);
				}

				else if (is_array ($tmp[0]))
				{
					$query .= $k . ' IN ? AND ';
				}

				else
				{
					$query .= $k . ' = ? AND ';
				}

				$values[] = $tmp;
			}

			$query = substr ($query, 0, -5);
		}

		return $query;
	}

	/**
	 * Select data from a message
	 * @param $table
	 * @param array $data : array of column names [ column1, column2 ]
	 * @param array $where : a 2 dimensional array with syntax: { column_name : [ value, type, nullOnEmpty ]}
	 * @param array $order
	 * @param null $limit
	 * @return Query
	 */
	public static function select ($table, array $data = array (), array $where = array (), $order = array (), $limit = null)
	{
		$query = 'SELECT ';
		$values = array ();

		if (count ($data) > 0)
		{
			foreach ($data as $v)
			{
				$query .= $v . ', ';
			}
			$query = substr ($query, 0, -2) . ' ';
		}
		else
		{
			$query .= '* ';
		}

		$query .= 'FROM `' . $table . '` ';
		$query .= self::processWhere ($where, $values);

		// Order
		if (count ($order) > 0)
		{
			$query .= " ORDER BY ";
			foreach ($order as $v)
			{
				$query .= $v . ", ";
			}
			$query = substr ($query, 0, -2);
		}

		// Limit
		if ($limit)
		{
			$query .= " LIMIT " . $limit;
		}

		$query = new self ($query);
		$query->bindValues ($values);

		return $query;
	}

	/**
	 * @param $table
	 * @param array $where
	 * @return Query|string
	 */
	public static function delete ($table, array $where)
	{
		$query = 'DELETE FROM `' . $table . '`';

		$values = array ();
		$query .= self::processWhere ($where, $values);

		$query = new self ($query);
		$query->bindValues ($values);

		return $query;
	}

	/**
	* And construct.
	*/
	public function __construct ($query)
	{
		$this->query = $query;
	}

	public function bindValues ($values)
	{
		$this->values = $values;
	}

	public function bindValue ($index, $value, $type = self::PARAM_STR, $canBeNull = false)
	{
		$this->values[$index] = array ($value, $type, $canBeNull);

		// Chaining
		return $this;
	}

	public function getParsedQuery ()
	{
		$db = Database::getInstance ();

		$keys = array ();
		$values = array ();

		foreach ($this->values as $k => $v)
		{
			// Column type?
			if (!isset ($v[1]))
			{
				// Check for known "special types"
				if ($v[0] instanceof Point)
				{
					$v[1] = self::PARAM_POINT;
				}
				else
				{
					$v[1] = self::PARAM_STR;
				}
			}

			// NULL on empty?
			if (!isset ($v[2]))
			{
				$v[2] = false;
			}

			// Empty and should set NULL?
			if ($v[2] && empty ($v[0]))
			{
				$value = "NULL";
			}
			else
			{
				// Array?
				if (is_array ($v[0]))
				{
					switch ($v[1])
					{
						case self::PARAM_NUMBER:
							foreach ($v[0] as $kk => $vv)
							{
								if (!is_numeric ($vv))
								{
									throw new InvalidParameter ("Parameter " . $k . "[" . $kk . "] should be numeric in query " . $this->query);
								}
							}
							$value = '(' . implode (',', $v[0]) . ')';
						break;

						case self::PARAM_DATE:

							$tmp = array ();
							foreach ($v[0] as $kk => $vv)
							{
								if (!is_numeric ($vv))
								{
									throw new InvalidParameter ("Parameter " . $k . "[" . $kk . "] should be a valid timestamp in query " . $this->query);
								}
								$tmp[] = "FROM_UNIXTIME(" . $vv . ")";
							}
							$value = '(' . implode (',', $tmp) . ')';

						break;

						case self::PARAM_POINT:
							$tmp = array ();
							foreach ($v[0] as $kk => $vv)
							{
								if (! ($vv instanceof Point))
								{
									throw new InvalidParameter ("Parameter " . $k . "[" . $kk . "] should be a valid \\Neuron\\Models\\Point " . $this->query);
								}
								$tmp[] = "POINT(" . $vv->getLongtitude() . "," . $vv->getLatitude() .")";
							}
							$value = '(' . implode (',', $tmp) . ')';
						break;

						case self::PARAM_STR:
						default:
							$tmp = array ();
							foreach ($v[0] as $kk => $vv)
							{
								$tmp[] = "'" . $db->escape (strval ($vv)) . "'";
							}
							$value = '(' . implode (',', $tmp) . ')';
						break;
					}
				}
				else
				{
					switch ($v[1])
					{
						case self::PARAM_NUMBER:
							if (!is_numeric ($v[0]))
							{
								throw new InvalidParameter ("Parameter " . $k . " should be numeric in query " . $this->query);
							}
							$value = $v[0];
						break;

						case self::PARAM_DATE:
							if (!is_numeric ($v[0]))
							{
								throw new InvalidParameter ("Parameter " . $k . " should be a valid timestamp in query " . $this->query);
							}
							$value = "FROM_UNIXTIME(" . $v[0] . ")";
						break;

						case self::PARAM_POINT:
							if (! ($v[0] instanceof Point))
							{
								throw new InvalidParameter ("Parameter " . $k . " should be a valid \\Neuron\\Models\\Point " . $this->query);
							}
							else
							{
								$value = "POINT(" . $v[0]->getLongtitude() . "," . $v[0]->getLatitude() .")";
							}
						break;

						case self::PARAM_STR:
						default:
							$value = "'" . $db->escape (strval ($v[0])) . "'";
						break;
					}
				}
			}

			$values[$k] = $value;

			// Replace question marks or tokens?
			if (is_string ($k))
			{
				$keys[] = '/:'.$k.'/';
			}
			else
			{
				$keys[] = '/[?]/';
			}
		}

		// First we make a list with placeholders which we will later repalce with values
		$fakeValues = array ();
		foreach ($values as $k => $v)
		{
			$fakeValues[$k] = '{{{ctlb-custom-placeholder-' . $k . '}}}';
		}

		// And replace
		$query = preg_replace ($keys, $fakeValues, $this->query, 1);

		// And now replace the tokens with the actual values
		foreach ($values as $k => $v)
		{
			$query = str_replace ($fakeValues[$k], $v, $query);
		}

		return $query;
	}

	public function execute ()
	{
		$db = Database::getInstance ();
		$query = $this->getParsedQuery ();
		return $db->query ($query);
	}
}
?>
