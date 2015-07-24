<?php

define('ROOT_PATH', dirname($_SERVER['SCRIPT_FILENAME']));
include_once(ROOT_PATH . '/php/common.php');

$pin = (int)_GET('pin');

if($pin && $pin == 39)
{
	$xml = @file_get_contents('http://' . ARDUINO_IP . '/outputs?39=0');
	add_log('Activated garage door', "IP:{$_SERVER['REMOTE_ADDR']}", 39, 0);
	$meta = '<script type="text/javascript">window.location = "/mobile.php";</script>';
}
else if($pin)
{
	$xml = @file_get_contents('http://' . ARDUINO_IP . '/outputs?' . $pin . '=2');
	get_pin_states(true);
	add_log("Toggled pin state", "IP:{$_SERVER['REMOTE_ADDR']}", $pin, (int)$pins[$pin]->state);
}

get_pin_states();


?><!DOCTYPE html>
<html>
<head>
<?php if( !empty($meta)){echo $meta;} ?>
<style type="text/css">
body {background:#111; color:#fff; font-size:20px; margin:0; padding:0;}
h3 {font-size:1.5em; margin:0px; padding:10px 0 10px 10px;
background-image: linear-gradient(bottom, #262626 0%, #737373 57%, #8F8F8F 79%);
background-image: -o-linear-gradient(bottom, #262626 0%, #737373 57%, #8F8F8F 79%);
background-image: -moz-linear-gradient(bottom, #262626 0%, #737373 57%, #8F8F8F 79%);
background-image: -webkit-linear-gradient(bottom, #262626 0%, #737373 57%, #8F8F8F 79%);
background-image: -ms-linear-gradient(bottom, #262626 0%, #737373 57%, #8F8F8F 79%);
background-image: -webkit-gradient(
	linear,
	left bottom,
	left top,
	color-stop(0, #262626),
	color-stop(0.57, #737373),
	color-stop(0.79, #8F8F8F)
);
}

ul, li {list-style:none; margin:0; padding:0;}
li {padding:5px 0 5px 5px;}
li > a {padding: 10px 0 10px 5px; display:block; background:#000; color:#fff; text-decoration:none;}
.on {color:#0c0;}
.off {color:#900;}
p {font-size:0.75em;}
</style>
</head>
<body>
<div class="section">
<h3>Sprinklers</h3>
<p>Tap the area you want to turn on/off. The current state is shown beside each area.</p>
<ul>
	<?php
	
	for($i = 46; $i <= 49; $i++)
	{
		printf('<li><a href="/mobile.php?pin=%2$s">%1$s (%3$s)</a></li>', $pins[$i]->name, $i, (!$pins[$i]->state ? '<span class="on">On</span>' : '<span class="off">Off</span>'));
	}
	
	?>
</ul>
</div>
<div class="section">
<h3>Garage</h3>
<p>Tap "door" to open/close the garage door.</p>
<ul>
	<li><a href="/mobile.php?pin=39">Car Door (<?php  echo (!$pins[25]->state ? '<span class="on">Closed</span>' : '<span class="off">Open</span>'); ?>)</a></li>
</ul>
</div>
<div class="section">
<h3>Garage Camera</h3>
<p>
<?php

	shell_exec('rm ' . ROOT_PATH . '/cache/cameras/screenshot-1a.jpg');
	
	$screenshot	= "cache/cameras/screenshot-1a";
	$_screenshot	= ROOT_PATH . "/{$screenshot}";

	shell_exec(ROOT_PATH . "/backup/somagic-easycap_1.1/somagic-capture -i 1 -f 1 --vo={$_screenshot}.raw");
	shell_exec("convert -size 720x480 'UYVY:{$_screenshot}.raw' -size 320x240 {$_screenshot}.jpg");

	echo "<img src='{$screenshot}.jpg' style='width:320px; height:240px;'/>\n";


?>
</p>
</div>

</body>
</html>
