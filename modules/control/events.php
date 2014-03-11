<?php

include_once(ROOT_PATH . '/php/common.php');

global $network_map;

if(_GET('archive', false))
{
	$result = $db->query('SELECT `id` FROM `event_log` WHERE `notes` = "Startup signal sent from Arduino" ORDER BY `id` DESC LIMIT 1');
	if($obj = $result->fetch_object())
	{
		if($result = $db->query("SELECT * FROM `event_log` WHERE `id` < {$obj->id} INTO OUTFILE '" . ROOT_PATH . "/backup/events/" . time() . ".sql'"))
		{
			//shell_exec('gzip -9 ' . ROOT_PATH . '/backup/events/*.sql');
			if($result = $db->query("DELETE FROM `event_log` WHERE `id` < {$obj->id}"))
			{
				echo new message("Successfully archived all entries older than ID={$obj->id}");
			}
		}
		else if(strpos(strtolower($db->error), 'access denied') !== false)
		{
			message::display('Unable to backup events to file. Make sure the MySQL user has the FILE privledge in MySQL administrator.');
		}
		else
		{
			message::display($db->error);
		}
	}
}
?>
<p>Use the options below to look for specific events. When no options are selected, the last <?php echo $config['recent-events-count']; ?> events are displayed.</p>
<form action="" method="post">
	<fieldset class="left-block">
	<label for="source">Sources</label>
	<select name="source[]" multiple="multiple" size="6">
	<?php
		$result = $db->query('SELECT DISTINCT `source` FROM `event_log` ORDER BY `source` ASC');
		$val = array();
		while($obj = $result->fetch_object())
		{
			if(substr($obj->source, 0, 3) == 'IP:')
			{
				$obj->label = $network_map[substr($obj->source, 3)]->name;
			}
			else
			{
				$obj->label = $obj->source;
			}
			$val[$obj->label] = $obj;
		}
		ksort($val);
		foreach($val as $obj)
		{
			printf('<option value="%1$s">%2$s</option>',
				$obj->source,
				$obj->label
			);
		}
	?>
	</select>
	</fieldset>
	<fieldset class="left-block">
	<label for="pin">Pins</label>
	<select name="pin[]" multiple="multiple" size="6">
	<?php
		$result = $db->query('SELECT `pin`, `name` FROM `pin_info` ORDER BY `name` ASC');
		echo mysqli_utils::options_for_result('pin', 'name', $result);
	?>
	</select>
	</fieldset>
	<fieldset class="left-block">
	<label for="group">Groups</label>
	<select name="group[]" multiple="multiple" size="6">
	<?php
		$result = $db->query('SELECT `id`, `name` FROM `group_info` ORDER BY `name` ASC');
		echo mysqli_utils::options_for_result('id', 'name', $result);
	?>
	</select>
	</fieldset>
	<fieldset class="left-block">
	<label for="state">State</label>
	<select name="state[]" multiple="multiple" size="6">
	<?php
		$result = $db->query('SELECT DISTINCT `pin_state` FROM `event_log` ORDER BY `pin_state` ASC');
		echo mysqli_utils::options_for_result('pin_state', 'pin_state', $result);
	?>
	</select>
	</fieldset>
	
	<fieldset class="left-block">
	<label for="start">Start Date / Time</label>
	<input type="text" name="start" class="text" style="width:250px;"/>
	</fieldset>
	<fieldset class="left-block">
	<label for="end">End Date / Time</label>
	<input type="text" name="end" class="text" style="width:250px;"/>
	</fieldset>
	<div>
		<p style="clear:left; font-size:small; font-family:Tahoma;">
			Times are pretty flexible. Phrases such as <em>yesterday</em>, <em>last monday</em>, and <em>6 hours ago</em>
			are valid. When looking for specific dates, use the American (MM/DD/YY) format.
		</p>
		<input type="submit" name="submit" class="button1 lefty-button1" value="Filter Events"/>
	</div>
</form>
<br class="clearfix"/>

<h3 class="subtitle">Results</h3>
<?php

$sql = '';

if(_GET('source', false))
{
	$sql .= ' AND `source` IN(';
	$_sql = '';
	foreach(_GET('source') as $val)
	{
		$_sql .= sprintf(',"%1$s"', $db->real_escape_string($val));
	}
	$sql .= substr($_sql, 1) . ')';
	unset($_sql);
}

if(_GET('state', false))
{
	$sql .= ' AND `pin_state` IN(';
	$_sql = '';
	foreach(_GET('state') as $val)
	{
		$_sql .= sprintf(',"%1$s"', (int)$val);
	}
	$sql .= substr($_sql, 1) . ')';
	unset($_sql);
}

$group_pins = array();
if(_GET('group', false))
{
	$_sql = '';
	foreach(_GET('group') as $val)
	{
		$_sql .= sprintf(',"%1$s"', (int)$val);
	}	
	$result = $db->query('SELECT DISTINCT `pin_id` FROM `pin_relations` WHERE `group_id` IN(' . substr($_sql, 1) . ')');
	unset($_sql);
	
	while($obj = $result->fetch_object())
	{
		$group_pins[$obj->pin_id] = $obj->pin_id;
	}
}

if(_GET('pin', false))
{
	$sql .= ' AND `pin` IN(';
	$_sql = '';
	foreach(_GET('pin') as $val)
	{
		$val = (int)$val;
		if(isset($group_pins[$val]))
		{
			unset($group_pins[$val]);
		}
		$_sql .= sprintf(',"%1$s"', $val);
	}
	foreach($group_pins as $val)
	{
		$_sql .= sprintf(',"%1$s"', $val);
	}
	$sql .= substr($_sql, 1) . ')';
	unset($_sql, $group_pins);
}

if( !empty($group_pins))
{
	$sql .= ' AND `pin` IN(';
	$_sql = '';
	foreach($group_pins as $val)
	{
		$_sql .= sprintf(',"%1$s"', $val);
	}
	$sql .= substr($_sql, 1) . ')';
	unset($_sql, $group_pins);
}

if(($val = strtotime(_GET('start', ''))) !== false)
{
	$sql .= ' AND `event_time` >= FROM_UNIXTIME(' . $val . ')';
}

if(($val = strtotime(_GET('end', ''))) !== false)
{
	$sql .= ' AND `event_time` <= FROM_UNIXTIME(' . $val . ')';
}

if( !empty($sql))
{
	$sql = 'WHERE ' . substr($sql, 4);
}

$sql_limit = 'LIMIT ' . (max(0, (int)_GET('page', 0) - 1) * $config['recent-events-count']) . ", {$config['recent-events-count']}";

if($result = $db->query('SELECT SQL_CALC_FOUND_ROWS * FROM `event_log` ' . $sql . ' ORDER BY id DESC ' . $sql_limit))
{
	global $network_map;

	echo '<p style="font-family:Tahoma">Showing ' . ((max(0, (int)_GET('page', 0)) * $config['recent-events-count']) + 1) . '-' . (((max(0, (int)_GET('page', 0)) * $config['recent-events-count'])) + min($config['recent-events-count'], $result->num_rows)) . ' of ' . ($db->query('SELECT FOUND_ROWS() AS `total`')->fetch_object()->total) . '</p><table style="color:#fff; font-size:small; text-align:center; width:100%" cellpadding="3px;">'
		. '<tr><th>Time</th><th>Pin</th><th>State</th><th>Source</th></tr>';
	while($obj = $result->fetch_object())
	{
		//print_r($obj);
		printf('<tr style="text-align:left; background:#000; color:#04969c;"><td colspan="4">%5$s</td></tr><tr style="background:#111;"><td>%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td></tr>',
			date($config['date-format'], strtotime($obj->event_time)),
			!empty($pins[$obj->pin]) ? $pins[$obj->pin]->name : 'None',
			!empty($pins[$obj->pin]) ? ($pins[$obj->pin]->input ? ($obj->pin_state ? 'Close' : 'Open') : ($obj->pin_state ? 'On' : 'Off')) : 'N/A',
			(substr($obj->source, 0, 3) == 'IP:') ? $network_map[substr($obj->source, 3)]->name : $obj->source,
			$obj->notes
		);
	}
	echo '</table>';
}

?>

<h3 class="subtitle">Archiving</h3>
<form action="" method="post">

	<div>
		<p style="clear:left; font-size:small; font-family:Tahoma;">
			Archiving will compress event entries from before the most recent startus signal and save them to disk.
			These entries will then be removed from this event log.
		</p>
		<input type="hidden" name="archive" value="1"/>
		<input type="submit" name="submit-archive" class="button1 lefty-button1" value="Archive Events"/>
	</div>
</form>
<br class="clearfix"/>