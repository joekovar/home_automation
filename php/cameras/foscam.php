<?php

class foscam implements cameraInterface
{
	public $ip, $port;
	protected $user, $pwd;
	
	public function __construct($attributes)
	{
		parse_str($attributes, $attributes);
		foreach($attributes as $key => $val)
		{
			switch($key)
			{
				case 'ip':
				case 'port':
				case 'user':
				case 'pwd':
					$this->$key = $val;
				break;
			}
		}
	}
	
	public function screenshot()
	{
		return sprintf('http://%1$s:%2$s/snapshot.cgi?resolution=32&user=%3$s&pwd=%4$s',
			$this->ip,
			$this->port,
			$this->user,
			$this->pwd
		);
	}
}

?>