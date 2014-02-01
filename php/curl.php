<?php

class curl
{
	protected $conn;
	public $opts = array(
		CURLOPT_HEADER			=> 0,
		CURLOPT_RETURNTRANSFER	=> 1,
		CURLOPT_FOLLOWLOCATION	=> 1,
		CURLOPT_NOBODY			=> 0,
		CURLOPT_FORBID_REUSE	=> true,
		CURLOPT_CONNECTTIMEOUT	=> 3,
		CURLOPT_LOW_SPEED_LIMIT	=> 1024,
		CURLOPT_LOW_SPEED_TIME	=> 5,
		CURLOPT_MAXREDIRS		=> 10,
		CURLOPT_TIMEOUT			=> 30
	);
	public $http_status;
	public function __construct($opts = array())
	{
		$this->conn	= curl_init();
		foreach($opts as $key => $val)
		{
			$this->opts[$key] = $val;
		}
		curl_setopt_array($this->conn, $this->opts);

		return $this;
	}
	public function get($url, $opts = array())
	{
		curl_setopt($this->conn, CURLOPT_URL, $url);
		foreach($opts as $key => $val)
		{
			$this->opts[$key] = $val;
			curl_setopt($this->conn, $key, $val);
		}
		$returns = curl_exec($this->conn);
		$this->http_status = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);
		return $returns;
	}
}

?>
