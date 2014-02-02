<?php

define('ROOT_PATH', dirname($_SERVER['SCRIPT_FILENAME']));
include_once(ROOT_PATH . '/php/common.php');

$action = _GET('action');

switch($action)
{
	case 'tts':
		audio::tts(_GET('q'), true);
	break;

	case 'toggle-garage':
		$str = file_get_contents('http://' . ARDUINO_IP . "/outputs?39=1");
		add_log('Activated garage door', "IP:{$_SERVER['REMOTE_ADDR']}", 39, 1);
		message::display("??Toggled state of {$pins[39]->name}");
	break;

	case 'toggle-output':
		$pin = (int)_GET('pin');
		if(empty($pins[$pin]))
		{
			message::display('Unrecognized pin.');
			exit;
		}
		$str = file_get_contents('http://' . ARDUINO_IP . "/outputs?{$pin}=2");
		get_pin_states(true);
		add_log("Toggled pin state", "IP:{$_SERVER['REMOTE_ADDR']}", $pin, (int)$pins[$pin]->state);
		message::display("Toggled state of {$pins[$pin]->name}");
	break;

	case 'all-sprinklers-off':
		$str = file_get_contents('http://' . ARDUINO_IP . '/outputs?46=0&47=0&48=0&49=0');
		add_log("Turned all sprinklers off", "IP:{$_SERVER['REMOTE_ADDR']}", -1, 0);
		message::display('Off signal sent to all sprinkler zones. If they were in the middle of a scheduled watering they will turn back on within 60 seconds. To disable sprinklers turn them off using the switch on the black box in the garage.');
	break;

	case 'delete-schedule':
		$id = _GET('id');
		
		if(preg_match('#^\d+(,\d+)*$#', $id) && $db->query("DELETE FROM `pin_schedule` WHERE `id` IN({$id})"))
		{
			message::display('Deleted selected schedules.');
		}
	break;
	
	case 'add-schedule':
		$pin			= (int)_GET('pin');
		$pin_state		= (bool)_GET('state');
		$dow 		= (int)_GET('dow');
		$start 		= _GET('start');
		$length	= _GET('length');
		
		if($pin > 0 && $pin < 54 && !empty($pins[$pin]))
		{
			if($dow > 0 && $dow < 128)
			{
				if($start = strtotime($start))
				{
					if(preg_match('#^(\d{1,3}:)?(\d{1,2}:)?\d{1,2}$#', $length))
					{
						if(preg_match('#^\d{1,2}$#', $length))
						{
							$length = "00:{$length}:00";
						}
						if($db->query("INSERT INTO `pin_schedule` (pin, pin_state, start_time, runtime, days_of_week) VALUES (". $pin . "," . $pin_state . ",'" . date('H:i:00', $start) . "','" . $length . "'," . $dow . ")"))
						{
							message::display("Created new schedule for pin #{$pin} - {$pins[$pin]->name}");
							break;
						}
					}
				}
			}
		}
		message::display('Fail');
	break;

	default:
		message::display('Unrecognized action');
		exit;
	break;
}

?>