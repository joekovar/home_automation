<?php

class html
{
	public static function label($label)
	{
		return sprintf('<span class="%1$s">%2$s</span>', strtolower(str_replace(' ', '-', $label)), $label);
	}
}

?>