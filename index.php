<?php

require_once('/var/www/php/common.php');
get_pin_states();

$modules = _GET('modules', array('home', 'statistics.arduino', 'statistics.server'));
ksort($modules);
$messages = array();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Home Status</title>
<link rel="stylesheet" type="text/css" media="all" href="jquery-ui-1.10.3/themes/vader/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="all" href="/style/default.css"  />
<script type="text/javascript" src="jquery-ui-1.10.3/jquery-1.9.1.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.3/ui/jquery-ui.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.3/ui/jquery.ui.timepicker.js"></script>
</head>
<body>
<div id="wrapper-gradiant">
	<div id="wrapper-bgshadow">
		<div id="wrapper" style="padding-top:10px;">
			<div id="page">
				<div class="bgtop"></div>
				<div class="bgcontent">
					<?php
						foreach($modules as $key => $val)
						{
							$modules[$val] = new module($val);
						}
						
						if( !empty($messages))
						{
							foreach($messages as $val)
							{
								echo new message($val);
							}
						}
					?>
				</div>
				<div class="bgbtm"></div>
			</div>
			<div id="sidebar">
				<div>
					<h2 class="title">Control Modules</h2>
					<ul>
						<?php
						foreach(glob('/var/www/modules/*.php') as $val)
						{
							$key = basename(strtolower($val), '.php');
							printf('<li %3$s><a href="/index.php?modules[]=%1$s">%2$s</a></li>', 
								$key,
								ucwords(preg_replace('#[^a-z0-9]+#', ' ', $key)),
								isset($modules[$key]) ? 'class="active-module"' : ''
							);
						}
						?>
					</ul>
				</div>
				<div>
					<h2 class="title">Statistics Modules</h2>
					<ul>
						<?php
						foreach(glob('/var/www/modules/statistics/*.php') as $val)
						{
							$key = basename(strtolower($val), '.php');
							printf('<li %3$s><a href="/index.php?modules[]=statistics.%1$s">%2$s</a></li>', 
								$key,
								ucwords(preg_replace('#[^a-z0-9]+#', ' ', $key)),
								isset($modules["statistics.{$key}"]) ? 'class="active-module"' : ''
							);
						}
						?>
					</ul>
				</div>
				<div>
					<h2 class="title">Admin Modules</h2>
					<ul>
						<?php
						foreach(glob('/var/www/modules/admin/*.php') as $val)
						{
							$key = basename(strtolower($val), '.php');
							printf('<li %3$s><a href="/index.php?modules[]=admin.%1$s">%2$s</a></li>', 
								$key,
								ucwords(preg_replace('#[^a-z0-9]+#', ' ', $key)),
								isset($modules["admin.{$key}"]) ? 'class="active-module"' : ''
							);
						}
						?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div id="footer">
		
	</div>
</div>
</body>
</html>
