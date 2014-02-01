<?php

include_once('/var/www/php/common.php');

//
// Close garage door
//
if(date('G:i', strtotime($config['garage-auto-close-time'])) == date('G:i'))
{
	if(get_pin_states() &&  @$pins[25]->state == 0)
	{
		$xml = @file_get_contents('http://' . ARDUINO_IP . '/outputs?39=1');
		add_log("Garage Car Door closed automatically", 'scheduled-task', 39, 2);
	}
}

//
// Turn off finished scheduled tasks
//
if($result = $db->query('SELECT id, pin, pin_state FROM `pin_schedule`
	WHERE `started` > 0
	AND (' . time() . ' - `started`) >= TIME_TO_SEC(`runtime`)'))
{
	$arduino_url = '';

	while($obj = $result->fetch_object())
	{
		if($db->query("UPDATE `pin_schedule` SET `started` = 0 WHERE `id` = {$obj->id}"))
		{
			add_log("Stopped schedule #{$obj->id}", 'scheduled-task', $obj->pin, !$obj->pin_state);
			$arduino_url .= "&{$obj->pin}=" . (!$obj->pin_state);
		}
	}
	
	if(strlen($arduino_url) > 0)
	{
		@file_get_contents('http://' . ARDUINO_IP . '/outputs?' . substr($arduino_url, 1));
	}
}

//
// Process scheduled tasks
//
if($result = $db->query("SELECT * FROM `pin_schedule`
	WHERE `started` = 0
		AND (`days_of_week` & {$today_bitmask})
		AND TIME('" . TIME_NOW . "') >= `start_time`
		AND TIME('" . TIME_NOW . "') < ADDTIME(`start_time`, `runtime`)"))
{
	$arduino_url = '';

	while($obj = $result->fetch_object())
	{
		if(empty($pins[$obj->pin]) && $db->query('DELETE FROM `pin_schedule` WHERE pin =' . $obj->pin))
		{
			add_log('Deleted schedules for unknown pin.', 'scheduled-task', $obj->pin, $obj->pin_state);
		}
		else
		{
			if( ! empty($groups[4]->pins[$obj->pin]) && (weather::today_low() < $config['dont-water-below-temp']))
			{
				add_log('Lawn watering aborted due to freezing temperature today', $obj->pin, $obj->pin_state);
			}
			else if($db->query('UPDATE `pin_schedule` SET `started` = ' . time() . ' WHERE id = ' . $obj->id))
			{
				add_log("Started schedule #{$obj->id}", 'scheduled-task', $obj->pin, $obj->pin_state);
				$arduino_url .= "&{$obj->pin}={$obj->pin_state}";
			}
		}
	}
	
	if(strlen($arduino_url) > 0)
	{
		@file_get_contents('http://' . ARDUINO_IP . '/outputs?' . substr($arduino_url, 1));
	}
}

/*
if($result = $db->query('SELECT `id`, `name`, `last_performed`, `interval_days`, `notes`, DATE_ADD(`last_performed`, INTERVAL `interval_days` DAY) AS `next_due` FROM `maintenance_items` ORDER BY `next_due` ASC'))
{
	$maintenance_items = array(
		'past_due'		=> array(),
		'upcomming'	=> array(),
		'normal'			=> array()
	);
	while($obj = $result->fetch_object())
	{
		if( ! empty($obj->next_due))
		{
			$obj->next_due_unix	= strtotime($obj->next_due);
			$obj->weekday			= date('l', $obj->next_due_unix);
			$obj->days_until		= floor($obj->next_due_unix / 86400) - floor(time() / 86400);
		}

		switch(true)
		{
			case ($obj->days_until <= 0):
				$maintenance_items['past_due'][] = $obj->html;
			break;
			
			case ($obj->days_until <= $config['upcomming-maintenance-days']):
				$maintenance_items['upcomming'][] = $obj->html;
			break;
			
			default:
				$maintenance_items['normal'][] = $obj->html;
			break;
		}
	}
}
*/

//
// Log climate
/*
if($xml = new SimpleXMLElement(file_get_contents('http://' . ARDUINO_IP . '/humidity')))
{
	if(($status = $xml->attributes()) && ( ! empty($status['humidity'])))
	{
		//print_r($status);
		//pin, log_time, type, reading
		if( ! $db->query("INSERT INTO `climate_log` (`pin`, `type`, `reading`) VALUES (22, 'humidity', '{$status['humidity']}'), (22, 'temperature', '{$status['temperature']}')"))
		{
			add_log('Climate logging failed', 'scheduled-task');
		}
		
		get_pin_states();

		if($pins[44]->state) // fan is on
		{
			if(($config['master-bath-fan-on-when'] != 0) && ((time() - ($config['master-bath-fan-on-minutes'] * 60)) >= $config['master-bath-fan-on-when']))
			{
				$db->query('UPDATE `config` SET `val` = 0 WHERE `key` = "master-bath-fan-on-when"');
				@file_get_contents('http://' . ARDUINO_IP . '/outputs?44=0');
			}
		}
		else // fan is off
		{
			if($config['master-bath-fan-on-when'] != 0)
			{
				$db->query('UPDATE `config` SET `val` = 0 WHERE `key` = "master-bath-fan-on-when"');
			}

			if(((int)date('G') < date('G', strtotime($config['master-bath-fan-quiet-after']))) && ((int)date('G') >= date('G', strtotime($config['master-bath-fan-quiet-before']))) && floatval($status['humidity']) >= $config['master-bath-fan-on-humidity'])
			{
				$db->query('UPDATE `config` SET `val` = ' . time() . ' WHERE `key` = "master-bath-fan-on-when"');
				@file_get_contents('http://' . ARDUINO_IP . '/outputs?44=1');
			}
			else if(floatval($status['humidity']) >= 90) // late night shower
			{
				$db->query('UPDATE `config` SET `val` = ' . time() . ' WHERE `key` = "master-bath-fan-on-when"');
				@file_get_contents('http://' . ARDUINO_IP . '/outputs?44=1');
			}
		}
	}
}
*/
// delete old climate data
/*
$db->query("DELETE FROM `climate_log` WHERE `log_time` < ('" . MYSQL_NOW . "' - INTERVAL {$config['climate-log-archive-interval']})");
*/
?>