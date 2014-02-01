<?php

class message
{
	protected static $last_message;
	public $message, $css_class;
	
	public static function display($message, $css_class = 'info')
	{
		echo new message($message, $css_class);
	}

	public function __construct($message, $css_class = 'info')
	{
		$this->message		= $message;
		$this->css_class	= $css_class;
	}
	public function __toString()
	{
		if($this->message == self::$last_message)
		{
			return '';
		}
		self::$last_message = $this->message;
		return sprintf('<p class="%1$s">%2$s</p>', $this->css_class, $this->message);
	}
}

?>
