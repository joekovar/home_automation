<?php include_once(ROOT_PATH . '/php/common.php'); ?>

<?php
	printf('<p>%1$s</p>', date($config['date-format'], time()));
	printf('<p>Today\'s low/high temperatures: %1$sF / %2$sF</p>', weather::today_low(), weather::today_high());
?>

<h3 class="subtitle">Quick Links</h3>
<ul class="quick-links">
	<?php if( !empty($config['events-module-name'])){printf('<li><a href="./index.php?start=today&modules[]=%1$s">Today\'s Events</a></li>', $config['events-module-name']);} ?>
	<?php if( !empty($config['print-server-url'])){printf('<li><a href="%1$s">Print Jobs</a></li>', $config['print-server-url']);} ?>
</ul>