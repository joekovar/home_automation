<?php include_once(ROOT_PATH . '/php/common.php'); ?>

<p>Use the modules listed in the menu on the right side to control and monitor the house.</p>
<p><strong>Control Modules</strong> are for turning things off/on and adjusting settings related to those specific modules.</p>
<p><strong>Statistics Modules</strong> are for looking at statistics related to just about anything in the system.</p>
<p><strong>Admin Modules</strong> are typically system-wide settings such as the format used to display dates, manual configuration editing, etc.</p>

<?php
	echo '<p>Today\'s low/high temperatures: ' . weather::today_low() . 'F/' . weather::today_high() . 'F</p>';
?>

<h3 class="subtitle">Quick Links</h3>
<ul class="quick-links">
	<li><a href="/index.php?modules[]=events&start=today">Today's Events</a></li>
	<li><a href="https://192.168.0.253:631/jobs?which_jobs=all">Print Jobs</a></li>
</ul>