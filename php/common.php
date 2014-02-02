<?php

define('ARDUINO_IP', '192.168.0.254');
define('IS_ARDUINO', @$_SERVER['REMOTE_ADDR'] == ARDUINO_IP);
define('TIME_NOW', date('G:i:s', time()));
define('MYSQL_NOW', date('Y-m-d G:i:s'));

date_default_timezone_set('America/New_York');

function __autoload($class)
{
	require_once(ROOT_PATH . '/php/' . preg_replace('#[^a-z0-9_-]+#', '', $class) . '.php');
}

$db = new mysqli(null, 'home_automation', 'password', 'home_automation', null, '/var/run/mysqld/mysqld.sock');

$day_of_week_bitmask = array(
	'sun'	=> 1,
	'mon'	=> 2,
	'tue'	=> 4,
	'wed'	=> 8,
	'thu'	=> 16,
	'fri'	=> 32,
	'sat'	=> 64,
	//'all'	=> 127
);
$today_bitmask = $day_of_week_bitmask[strtolower(date('D', time()))];

$config = array();
if($result = $db->query('SELECT * FROM `config`'))
{
	while($obj = $result->fetch_object())
	{
		$config[$obj->key] = $obj->val;
	}
	$result->close();
}

$pins = array();
if($result = $db->query('SELECT * FROM `pin_info` ORDER BY `name` ASC'))
{
	while($obj = $result->fetch_object())
	{
		$pins[$obj->pin] = $obj;
	}
	$result->close();
}
function get_pin_states($skip_cache = false)
{
	if( ! $skip_cache && defined('PINS_LOADED'))
	{
		return true;
	}

	global $pins;

  if($xml = file_get_contents('http://' . ARDUINO_IP . '/pins.xml'))
  {
    if($xml = new SimpleXMLElement($xml))
    {
      foreach($xml->digital->pin as $item)
      {
        $item = $item->attributes();
        if( !empty($pins[(int)$item['id']]))
        {
          $pins[(int)$item['id']]->state = (int)$item['state'];
        }
      }
      if( ! defined('PINS_LOADED'))
      {
        define('PINS_LOADED', true);
      }
      return true;
    }
   }
	return false;
}

$groups = array();
$result = $db->query("SELECT * FROM `group_info` ORDER BY `name` ASC;");
if($result)
{
	while($obj = $result->fetch_object())
	{
		$obj->pins = array();
		$groups[$obj->id] = $obj;
	}
	$result->free_result();
	
	$result = $db->query('SELECT `group_id`, `pin_id` FROM `pin_relations`');
	while($obj = $result->fetch_object())
	{
		$groups[$obj->group_id]->pins[$obj->pin_id] =& $pins[$obj->pin_id];
	}
}

$network_map = array();
$result = $db->query("SELECT `name`, `ip`, `mac` FROM `network_map` ORDER BY `name` ASC;");
if($result)
{
	while($obj = $result->fetch_object())
	{
		$network_map[long2ip($obj->ip)] = $obj;
	}
	$result->free_result();
}

function _GET($key, $default = '')
{
	return empty($_GET[$key]) ? (empty($_POST[$key]) ? $default : $_POST[$key]) : $_GET[$key];
}

function add_log($notes, $source = 'php', $pin = null, $pin_state = null)
{
	global $config, $db, $groups;

	$db->query("INSERT INTO `event_log` (notes, source, pin, pin_state) VALUES ('{$notes}', '{$source}', '{$pin}', '{$pin_state}')");
	
	if($source != 'scheduled-task') // ignore scheduled tasks, as the task itself will handle annoucements
	{
		if(isset($groups[19]->pins[$pin])) // Make sure pin is part of the "Audible" group
		{
			$pin =& $groups[19]->pins[$pin];

			if($pin->input && (($pin_state && $config['announce-closes']) || (!$pin_state && $config['announce-opens'])))
			{
				audio::tts("{$pin->name} " . ($pin_state ? 'Closed' : 'Open'));
			}
			else if(!$pin->input)
			{
				audio::tts("{$pin->name} " . ($pin_state ? 'On' : 'Off'));
			}
		}
	}
}

function elapsed_time($since, $do_math = true)
{
	$time = $do_math ? (time() - $since) : $since;
	$str = '';
	$tokens = array (
		86400 => 'd',
		3600 => 'h',
		60 => 'm',
		1 => 's'
	);

	foreach ($tokens as $unit => $text)
	{
		if ($time < $unit) continue;
		$str .= floor($time / $unit) . $text;
		$time = $time % $unit;
	}
	return $str;
}

function humidity($val = '0')
{
	return number_format($val, 1) . ' %';
}
function temperature($val = '0', $out = 'F', $in = 'C')
{
	$out = strtoupper($out);
	$in	= strtoupper($in);
	
	if($out == 'F' && $in == 'C')
	{
		$val = ($val  *  (9 / 5)) + 32;
	}
	else if($out == 'C' && $in == 'F')
	{
		$val = ($val  -  32)  *  (5 / 9);
	}
	
	return number_format($val, 1) . "&deg; {$out}";
}

function print_pre($obj)
{
	echo '<pre>' . htmlentities(print_r($obj, true)) . '</pre>';
}

?>