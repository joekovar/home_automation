<p>The maintenance module is for keeping track of maintenance around the home. Things like
changing the air conditioning filter, flushing the water heater, etc.</p>
<?php

echo date('r', time());

include_once('/var/www/php/common.php');

if(_GET('submit-new-service', false))
{
	if($db->query(sprintf('INSERT INTO `maintenance_items` (`name`, `last_performed`, `interval_days`, `notes`) VALUES("%1$s", "%2$s", %3$s, "%4$s") ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `interval_days` = VALUES(`interval_days`), `notes` = VALUES(`notes`)',
		$db->real_escape_string(_GET('service-name')),
		$db->real_escape_string(_GET('service-last')),
		(int)_GET('service-interval'),
		$db->real_escape_string(_GET('service-notes'))
	)))
	{
		message::display('Successfully added new maintenance schedule');
	}
	else
	{
		message::display($db->error);
	}
}

if(_GET('submit-service-performed', false) && count(_GET('maintenance', array())))
{
	$sql = array();
	foreach(_GET('maintenance') as $val)
	{
		if((int)$val == $val)
		{
			$sql[$val] = $val;
		}
	}
	if(count($sql))
	{
		if($result = $db->query('SELECT `id`, UNIX_TIMESTAMP(CURRENT_DATE) AS `now`, UNIX_TIMESTAMP(DATE_ADD(`last_performed`, INTERVAL `interval_days` DAY)) AS `next_due` FROM `maintenance_items` WHERE `id` IN(' . implode(',', array_keys($sql)) . ')'))
		{
			while($obj = $result->fetch_object())
			{
				/* item_id, performed, timing, timing_offset */
				$sql[$obj->id] = sprintf('(%1$s, "%2$s", %3$s, %4$s)',
					$obj->id,
					date('Y-m-d'),
					(($obj->now > $obj->next_due) ? -1 : ($obj->now < $obj->next_due ? 1 : 0)),
					abs(($obj->next_due - $obj->now) / 86400)
				);
			}
			if($db->query('INSERT INTO `maintenance_log` (`item_id`, `performed`, `timing`, `timing_offset`) VALUES ' . implode(',', $sql)))
			{
				message::display('Logged maintenance completions.');
			}
			if($db->query('UPDATE `maintenance_items` SET `last_performed` = "' . date('Y-m-d') . '" WHERE `id` IN(' . implode(',', array_keys($sql)) . ')'))
			{
				message::display('Updated maintenance schedules.');
			}
		}
	}
}

/*
| id             				| int(10) unsigned
| name         			| varchar(64)
| last_performed		| date
| interval_days		| smallint
| notes          			| text 
| next_due				| DATE_ADD(`last_performed`, INTERVAL `interval_days` DAY)
*/

if($result = $db->query('SELECT `id`, `name`, `last_performed`, `interval_days`, `notes`, DATE_ADD(`last_performed`, INTERVAL `interval_days` DAY) AS `next_due` FROM `maintenance_items` ORDER BY `next_due` ASC'))
{
	$maintenance_items = array(
		'past_due'		=> array(),
		'upcomming'	=> array(),
		'normal'			=> array()
	);
	while($obj = $result->fetch_object())
	{
		if(empty($obj->next_due))
		{
			$obj->days_until = 0;
			$obj->html = sprintf('<tr><td colspan="5" class="label">%1$s <span class="note">%6$s</span></td></tr><tr><td>%2$s Days</td><td>%3$s</td><td>%4$s</td><td>%5$s</td><td>%7$s</td></tr>',
				$obj->name,
				$obj->interval_days,
				'0',
				'Unknown',
				'Today',
				$obj->notes,
				sprintf('<input type="checkbox" name="maintenance[]" value="%1$s"/>', $obj->id)
			);
		}
		else
		{
			$obj->next_due_unix	= strtotime($obj->next_due);
			$obj->weekday			= date('l', $obj->next_due_unix);
			//$obj->days_until		= floor(($obj->next_due_unix - time()) / 86400);
			$obj->days_until		= floor($obj->next_due_unix / 86400) - floor(time() / 86400);
			
			$obj->html = sprintf('<tr><td colspan="5" class="label"><img title="Send to Editor" src="./style/images/cog_edit.png" style="cursor:pointer;" onclick="update_maintenance_item(\'%1$s\', \'%2$s\', \'%4$s\', \'%6$s\')"/> %1$s <span class="note">%6$s</span></td></tr><tr><td>%2$s Days</td><td>%3$s Days</td><td>%4$s</td><td>%5$s</td><td>%7$s</td></tr>',
				$obj->name,
				$obj->interval_days,
				abs($obj->days_until),
				date($config['date-only-format'], strtotime($obj->last_performed)),
				sprintf('<acronym title="%2$s">%1$s</acronym>',
					date($config['date-only-format'], $obj->next_due_unix),
					"A {$obj->weekday}"
				),
				$obj->notes,
				sprintf('<input type="checkbox" name="maintenance[]" value="%1$s"/>', $obj->id)
			);
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
	
	if(count($maintenance_items, COUNT_RECURSIVE))
	{
		echo '<form action="" method="post">';
		
		if(count($maintenance_items['past_due']))
		{
			echo '<h3 class="subtitle" style="color:#900;">Due Maintenance</h3><table class="events" cellpadding="3"><tr><th>Interval</th><th>Past Due</th><th>Last Performed</th><th>Next Due</th><th>Mark</th></tr>', implode('', $maintenance_items['past_due']), '</table>';
		}
		
		if(count($maintenance_items['upcomming']))
		{
			echo '<h3 class="subtitle" style="color:#990;">Upcomming Maintenance</h3><table class="events" cellpadding="3"><tr><th>Interval</th><th>When</th><th>Last Performed</th><th>Next Due</th><th>Mark</th></tr>', implode('', $maintenance_items['upcomming']), '</table>';
		}
		
		if(count($maintenance_items['normal']))
		{
			echo '<h3 class="subtitle" style="color:#090;">Maintenance</h3><table class="events" cellpadding="3"><tr><th>Interval</th><th>When</th><th>Last Performed</th><th>Next Due</th><th>Mark</th></tr>', implode('', $maintenance_items['normal']), '</table>';
		}
		echo '<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit-service-performed" value="Mark Performed"/></form><br class="clearfix"/>';
	}
}else{echo $db->error;}

?>
<script type="text/javascript">
function update_maintenance_item(name, interval, last_performed, notes)
{
	$("#service-name").val(name);
	$("#service-interval").val(interval);
	$("#service-last").val(last_performed);
	$("#service-notes").val(notes);
}
</script>
<form action="" method="post">
	<h3 class="subtitle">New Maintenance Item</h3>
	<p>Enter the details of the new maintenance item you want to keep track of.</p>
	<fieldset>
	<dl style="float:left; width:300px;">
		<dt>Service Name</dt>
		<dd><input type="text" class="text" name="service-name" id="service-name" value=""/></dd>
		
		<dt>Service Interval (in DAYS)</dt>
		<dd><input type="text" class="text" name="service-interval" id="service-interval" value=""/></dd>
		
		<dt>Last Performed (YYYY-MM-DD)</dt>
		<dd><input type="text" class="text" name="service-last" id="service-last" value=""/></dd>
		
		<dt>Notes</dt>
		<dd><textarea class="text" name="service-notes" id="service-notes" rows="3"></textarea></dd>
	</dl>
	<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit-new-service" value="Schedule Maintenance"/>
	</fieldset>
</form>