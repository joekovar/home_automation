<?php

require_once(ROOT_PATH . '/php/common.php');

class module
{
	public function __construct($name = '')
	{
		$name		= preg_replace('#[^\.a-z0-9_-]+#', '', strtolower($name));

		if(empty($name))
		{
			return 0;
		}
		
		$_name = explode('.', $name);
		if(count($_name) == 1)
		{
			$_name = $_name[0];
		}
		else
		{
			$name		= array_pop($_name);
			$_name	= implode('/', $_name) . "/{$name}";
		}
		
		if( ! file_exists(ROOT_PATH . "/modules/{$_name}.php"))
		{
			return 0;
		}
		
		global $db, $config, $pins;
		
		printf('<div class="post"><h3 class="title">%1$s</h3>', ucwords(str_replace('-', ' ', $name)));
		include(ROOT_PATH . "/modules/{$_name}.php");
		print('<div class="divider"></div></div>');
		
		return 1;
	}
}

?>