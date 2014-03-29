<?php

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

?>