<?php

$str = file_get_contents('http://' . ARDUINO_IP . '/outputs?46=0&47=0&48=0&49=0');
add_log("Turned all sprinklers off", "IP:{$_SERVER['REMOTE_ADDR']}", -1, 0);
message::display('Off signal sent to all sprinkler zones. If they were in the middle of a scheduled watering they will turn back on within 60 seconds. To disable sprinklers turn them off using the switch on the black box in the garage.');
		
?>