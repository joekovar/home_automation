<?php

/*
	$test = new email('to@localhost', 'from@localhost', 'subject', 'message');
*/

class email
{
	public $to = '', $from = '', $subject = '', $message = '', $headers = array();

	public function __construct($to = '', $from = '', $subject = '', $message = '', $headers = array())
	{
		$this->to				= $to;
		$this->from			= $from;
		$this->subject		= $subject;
		$this->message	= $message;
		$this->headers		= $headers;
		
		return $this;
	}
	
	public function __destruct()
	{
		$_headers = '';
		$_ = '';

		$this->headers['From'] = $this->from;
		foreach($this->headers as $key => $val)
		{
			$_headers 	.= "{$key}: {$val}{$_}";
			$_				= "\r\n";
		}

		mail($this->to, $this->subject, $this->message, $_headers);
	}
}

?>