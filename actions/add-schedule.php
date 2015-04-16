<?php

$pin			= (int)_GET('pin');
$pin_state		= (int)_GET('state');
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
print_pre($_GET);
message::display('Fail');

?>
