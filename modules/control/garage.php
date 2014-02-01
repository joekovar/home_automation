<p>The following is the <em>current</em> state of each door, and at what time the garage car door should close automatically if accidentally left open.</p>
<script type="text/javascript">
function toggle_garage()
{
	var xml = $('<div/>').load("./action.php?action=toggle-garage");
	var is_open = $('#garage-door-opener').val() == 'Open Garage Door' ? false : true;
	$('#garage-door-opener').val(is_open ? 'Close Garage Door' : 'Open Garage Door');
	$('#garage-door-state').html(is_open ? 'Open' : 'Closed');
}
</script>
<table class="config">
	<tr><td class="label">Side Door</td><td><?php echo @$pins[24]->state ? 'Closed' : 'Open'; ?></td></tr>
	<tr><td class="label">Car Door</td><td id="garage-door-state"><?php echo @$pins[25]->state ? 'Closed' : 'Open'; ?></td></tr>
	<tr><td class="label">Time Car Door Automatically Closes</td><td ><?php echo $config['garage-auto-close-time']; ?></td></tr>
	<tr>
		<td class="label">Last Open</td>
		<td>
		<?php
			$result = $db->query("SELECT `event_time` FROM `event_log` WHERE `pin_state` = 0 AND `pin` = 25 ORDER BY `event_time` DESC LIMIT 1;");
			if($obj = $result->fetch_object())
			{
				echo date($config['date-format'], strtotime($obj->event_time));
			}
			else
			{
				echo 'Unknown';
			}
		?>
		</td>
	</tr>
	<tr>
		<td class="label">Last Close</td>
		<td>
		<?php
			$result = $db->query("SELECT `event_time` FROM `event_log` WHERE `pin_state` = 1 AND `pin` = 25 ORDER BY `event_time` DESC LIMIT 1;");
			if($obj = $result->fetch_object())
			{
				echo date($config['date-format'], strtotime($obj->event_time));
			}
			else
			{
				echo 'Unknown';
			}
		?>
		</td>
	</tr>
	<tr>
		<td class="label">Last Auto-Close</td>
		<td>
		<?php
			$result = $db->query("SELECT `event_time` FROM `event_log` WHERE `pin_state` = 2 AND `pin` = 39 AND `source` = 'scheduled-task' ORDER BY `event_time` DESC LIMIT 1;");
			if($obj = $result->fetch_object())
			{
				echo date($config['date-format'], strtotime($obj->event_time));
			}
			else
			{
				echo 'Unknown';
			}
		?>
		</td>
	</tr>
	<tr>
		<td class="label">Total Auto-Closes</td>
		<td>
		<?php
		if($result = $db->query("SELECT COUNT(*) AS `total` FROM `event_log` WHERE `pin_state` = 2 AND `pin` = 39 AND `source` = 'scheduled-task'"))
		{
			$obj = $result->fetch_object();
			echo $obj->total;
		}
		else
		{
			echo 'None';
		}
		?>
		</td>
	</tr>
</table>

<form>
	<div><input type="button" class="button1 lefty-button1" id="garage-door-opener" title="Open / Close Garage Door" onclick="toggle_garage()" value="<?php echo @$pins[25]->state ? 'Open' : 'Close'; ?> Garage Door"></div>
	<br class="clearfix"/>
</form>