<?php


namespace Neuron\DB;

use Iterator;
use ArrayAccess;
use Countable;


class Result implements Iterator, ArrayAccess, Countable
{
	private $result;
	
	// iterator
	private $rowId;
	private $current;
	
	private $cache;

	public function __construct ($result)
	{
		$this->result = $result;
		
		// Move to the first row
		/*
		$this->rowId = 0;
		$this->current = $this->result->fetch_assoc ();
		*/
	}
	
	public function getNumRows ()
	{
		return $this->result->num_rows;
	}
	
	/**************************
		ARRAY ACCESS
	***************************/
	public function offsetExists($offset)
	{
		return $offset >= 0 && $offset < $this->getNumRows ();
	}
	
	public function offsetGet($offset)
	{
		// Only numeric values
		$offset = intval ($offset);
	
		// Cache these calls
		if (!isset ($this->cache[$offset]))
		{
			// Move mysql data stream to offset
			$this->result->data_seek ($offset);
			$this->cache[$offset] = $this->result->fetch_assoc ();
		
			// Move back to current position
			$this->result->data_seek ($this->rowId);
		}
		
		return $this->cache[$offset];
	}
	
	public function offsetUnset($offset)
	{
		// Doesn't do anything here.
	}
	
	public function offsetSet($offset, $value)
	{
		// Doesn't do anything here.
	}

	
	/**************************
		ITERATOR
	***************************/
	public function current()
	{
		return $this->current;
	}
	
	public function key()
	{
		return $this->rowId;
	}
	
	public function next()
	{
		$this->rowId ++;
		$this->current = $this->result->fetch_assoc ();
		
		return $this->current;
	}
	
	public function rewind()
	{
		$this->rowId = 0;
		$this->result->data_seek (0);
		$this->current = $this->result->fetch_assoc ();
	}
	
	public function valid()
	{
		return is_array ($this->current ());
	}
	
	/**************************
		COUNTABLE
	***************************/
	public function count ()
	{
		return $this->getNumRows ();
	}
	
	// Destruct
	public function __destruct ()
	{
		$this->result->close ();
	}
}
?>
