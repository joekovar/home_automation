<?php

$str = file_get_contents('http://' . ARDUINO_IP . "/outputs?39=1");
add_log('Activated garage door', "IP:{$_SERVER['REMOTE_ADDR']}", 39, 1);
message::display("??Toggled state of {$pins[39]->name}");

?>