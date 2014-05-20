<?php

class config implements arrayaccess, Iterator
{
	private $a = array(), $updated = array();
	
	public function __destruct()
	{
		global $db;

		if( !empty($this->updated))
		{
			$sql = '';
			foreach($this->updated as $key)
			{
				$sql .= sprintf(',("%1$s", "%2$s")',
					$db->real_escape_string($key),
					$db->real_escape_string($this->a[$key])
				);
			}
			$sql = 'INSERT INTO `config` (`key`, `val`) VALUES ' . substr($sql, 1) . ' ON DUPLICATE KEY UPDATE `val` = VALUES(`val`)';
			$db->query($sql);
		}
	}

	public function __construct($a = array())
	{
		$this->a = $a;
	}
	
	/* arrayaccess methods */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
		{
			$this->a[] = $value;
		}
		else
		{
			$this->a[$offset] = $value;
			$this->updated[] = $offset;
		}
	}
	public function offsetGet($offset){return isset($this->a[$offset]) ? $this->a[$offset] : null;}
	public function offsetExists($offset){return isset($this->a[$offset]);}
	public function offsetUnset($offset){unset($this->a[$offset]);}
	
	/* Iterator methods */
	function rewind(){return reset($this->a);}
	function current(){return current($this->a);}
	function key(){return key($this->a);}
	function next(){return next($this->a);}
	function valid(){return key($this->a) !== null;}
}

?>