<?php

include_once('/var/www/php/common.php');

$action = _GET('action');

switch($action)
{
	case 'boot':
		if(IS_ARDUINO)
		{
			$db->query('INSERT INTO `config` (`key`, `val`) VALUES ("startup-time", ' . time() . ') ON DUPLICATE KEY UPDATE val = VALUES(val)');
			add_log('Startup signal sent from Arduino', "IP:{$_SERVER['REMOTE_ADDR']}");
		}
		else
		{
			add_log('Startup signal received from unknown source.', "IP:{$_SERVER['REMOTE_ADDR']}");
		}
	break;
	
	case 'pin-change':
		if(_GET('multi'))
		{
			if(IS_ARDUINO)
			{
				$key = _GET('pin');
				if(preg_match('#^(,\d{1,2}-\d)+#', $key))
				{
					$key = explode(',', substr($key, 1));
					foreach($key as $val)
					{
						$val = explode('-', $val);
						add_log('Pin state changed', "IP:{$_SERVER['REMOTE_ADDR']}", (int)$val[0], (int)$val[1]);
					}
				}
			}
		}
		else
		{
			if(IS_ARDUINO)
			{
				add_log('Pin state changed', "IP:{$_SERVER['REMOTE_ADDR']}", (int)_GET('pin'), (int)_GET('state'));
			}
			else
			{
				add_log('Pin change from unknown source.', $_SERVER['REMOTE_ADDR'], (int)_GET('pin'), (int)_GET('state'));
			}
		}
	break;
	
	default:
		// something's broken
		add_log('Actionless request to arduino.php ', $_SERVER['REMOTE_ADDR']);
	break;
}

?>