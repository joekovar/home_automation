<?php
class large_filesize
{
	public $size, $precision;
	protected static $suffixes = array(
		'Y' => '1208925819614629174706176',
		'Z' => '1180591620717411303424',
		'E' => '1152921504606846976',
		'P' => '1125899906842624',
		'T' => '1099511627776',
		'G' => '1073741824',
		'M' => '1048576',
		'K' => '1024'
	);
	
	public function add()
	{
		foreach(func_get_args() as $num)
		{
				switch(true)
				{
						case $num instanceof large_filesize:// Let the __toString methods of other objects handle themselves
								$this->size = bcadd($this->size, $num->size);
						break;
				
						default:
								$this->size = bcadd($this->size, $num);
						break;
				}
		}
		return $this;
	}
	
	public function sub()
	{
		foreach(func_get_args() as $num)
		{
				switch(true)
				{
						case $num instanceof large_filesize:// Let the __toString methods of other objects handle themselves
								$this->size = bcsub($this->size, $num->size);
						break;
				
						default:
								$this->size = bcsub($this->size, $num);
						break;
				}
		}
	}
	
	public function __construct($size = '0', $precision = 2)
	{
		$this->precision = (int)$precision;
		$this->size = (string)$size;
		return $this;
	}


	public function __toString()
	{
		foreach(self::$suffixes as $suffix => $divisor)
		{
				if(bccomp($this->size, $divisor) > -1)
				{
						return bcdiv($this->size, $divisor, (int)$this->precision) . $suffix;
				}
		}
		return $this->size . 'b';
	}
}
?>
