<?php

class html
{
	public static function label($label)
	{
		return sprintf('<span class="%1$s">%2$s</span>', strtolower(str_replace(' ', '-', $label)), $label);
	}
	
	public static function options($options)
	{
		$str = '';

		foreach($options as $key => $val)
		{
			$str .= sprintf('<option value="%1$s">%2$s</option>', $key, $val);
		}
		
		return $str;
	}
}

?>