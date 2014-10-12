<?php

define('ROOT_PATH', dirname($_SERVER['SCRIPT_FILENAME']));
include_once(ROOT_PATH . '/php/common.php');

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
						
						if($config['doorbell-enabled'] && (int)$val[0] == 34 && (int)$val[1] == 1) // doorbell is pressed
						{
							if($result = $db->query('SELECT `module`, `attributes` FROM `cameras` WHERE `id` = 1'))
							{
								if($obj = $result->fetch_object())
								{
									$camera = new camera($obj->module, $obj->attributes);
									rename(ROOT_PATH . '/' . $camera->screenshot(), ROOT_PATH . '/cache/cameras/doorbell/' . time() . '.jpg');
								}
							}
							if(empty($config['doorbell-message']))
							{
								$config['doorbell-message'] = 'There is someone at the front door.';
							}
							audio::google_tts($config['doorbell-message']);
						}
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
	
	case 'water-heater':
		if(IS_ARDUINO)
		{
			if((int)_GET('status', -1) > -1)
			{
				if( ! $db->query("INSERT INTO `climate_log` (`pin`, `type`, `reading`, `analog`) VALUES (0, 'water-heater', " . (int)_GET('status') . ", 1)"))
				{
					add_log('Water heater logging failed', 'arduino.php');
				}
			}
			else
			{
				add_log('Missing water heater status argument.', "IP:{$_SERVER['REMOTE_ADDR']}");
			}
		}
		else
		{
			add_log('Water heater status change from unknown source.', "IP:{$_SERVER['REMOTE_ADDR']}");
		}
	break;
	
	default:
		// something's broken
		add_log('Actionless request to arduino.php ', "IP:{$_SERVER['REMOTE_ADDR']}");
	break;
}

?>