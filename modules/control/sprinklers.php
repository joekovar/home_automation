<script type="text/javascript">
function delete_schedule(title, selectors)
{
	var str = "";
	$(selectors).each(function () {
		str += $(this).val() + ",";
		$(this).remove();
	});
	$($('<div title="' + title + ': ' + (new Date()) + '"></div>').load("./action.php?action=delete-schedule&id=" + str.replace(/,$/, ''))).dialog();
}

function new_sprinkler_schedule(pin)
{
	$('#form-dialog').load('./html-forms/new-sprinkler-schedule.html', function(){
		var _dialog = $(this);
		$('.ui-buttonset').buttonset();
		$('#start-time-timepicker').timepicker({
			altField: '#watering-start-time',
			showPeriod: true,
			showLeadingZero: false,
			defaultTime: '7:00 AM',
			minutes: {starts:0, ends:58, interval:2},
			rows: 4
		});
		$('#watering-length-timepicker').timepicker({
			altField: '#watering-length',
			showPeriod: false,
			showLeadingZero: true,
			showHours: false,
			showMinutesLeadingZero: true,
			minutes: {starts:1, ends:60, interval:1},
			rows: 4
		});

		$('#sprinkler-schedule-finished').button({
			icons: {primary: "ui-icon-check"}
		});
		
		_dialog.dialog({
			width: 500,
			title: 'New Sprinkler Schedule',
			modal: true,
			buttons: [
				{text: "Finished", click: function(){
					var dow = 0;
					_dialog.find('input[type="checkbox"]:checked').each(function(){
						dow += parseInt($(this).val());
					});
					$('<div></div>').load("./action.php?action=add-schedule&state=1&pin=" + pin + "&dow=" + dow + "&start=" + escape($("#watering-start-time").val()) + "&length=" + escape($("#watering-length").val())).dialog();
					_dialog.dialog("close");
				}}
			]
		});
	});
}

function all_sprinklers_off()
{
	$('<div title="Sprinklers: ' + (new Date()) + '"></div>').load("./action.php?action=all-sprinklers-off").dialog();
}

function toggle_sprinkler(pin, button)
{
	$(button).val(/off/i.test($(button).val()) ? 'Turn On' : 'Turn Off');
	$('<div title="Sprinklers: ' + (new Date()) + '"></div>').load("./action.php?action=toggle-output&pin=" + pin).dialog();
}
</script>

<?php

include_once(ROOT_PATH . '/php/common.php');
global $day_of_week_bitmask;

$sprinklers = array();

if($result = $db->query('SELECT `pin_id` FROM `pin_relations` WHERE `group_id` = 4'))
{
	while($obj = $result->fetch_object())
	{
		$sprinklers[$obj->pin_id] =& $pins[$obj->pin_id];
		$sprinklers[$obj->pin_id]->schedule = array();
		$sprinklers[$obj->pin_id]->last_run = false;
	}
}

foreach($sprinklers as $key => $val)
{
	if(($result = $db->query('SELECT `event_time` FROM `event_log` WHERE `pin_state` = 1 AND `pin` = ' . $key . ' ORDER BY `event_time` DESC LIMIT 1')) && $obj = $result->fetch_object())
	{
		$sprinklers[$key]->last_run = $obj->event_time;
	}
}

if($result = $db->query('SELECT * FROM `pin_schedule` WHERE `pin` IN(' . implode(',', array_keys($sprinklers)) . ') ORDER BY pin ASC, start_time ASC'))
{
	while($obj = $result->fetch_object())
	{
		$sprinklers[$obj->pin]->schedule[$obj->id] = $obj;
	}
}

get_pin_states();
//print_pre($sprinklers);

echo '<table class="config"><tr><th>Sprinkler Zone</th><th>Last Run</th><th>Control</th></tr>';
foreach($sprinklers as $key => $val)
{
	printf('<tr><td class="label">%1$s</td><td style="text-align:center">%3$s</td><td><input type="button" class="button1 lefty-button2" title="Turn %2$s" onclick="toggle_sprinkler(%4$s, this)" value="Turn %2$s"/></td></tr>',
		$val->name,
		$val->state ? 'Off' : 'On',
		$val->last_run ? date($config['date-format'], strtotime($val->last_run)) : 'Unknown',
		$val->pin
	);
}
?>

</table><h3 class="title">Watering Schedules</h3>
<p>Sprinklers will automatically skip scheduled watering if the low temperature forcasted for that day is below <strong><?php echo $config['dont-water-below-temp']; ?> &deg;F</strong></p>
<p>The three days per week watering schedule is mandatory during Florida's traditional dry seasons (<strong>April 1 to June 30 and October 1 to November 30</strong>) and voluntary the rest of the year.</p>

<?php

foreach($sprinklers as $val)
{
	printf('<h3 class="subtitle clearfix">%1$s</h3>
		<select id="sprinklers-zone-%2$s" multiple="multiple" class="schedule-list" style="width:575px;">',
		$val->name,
		$val->pin
	);
	if( !empty($val->schedule))
	{
		foreach($val->schedule as $schedule_id => $obj)
		{
			$option = '<option value="' . $obj->id . '">DAYS: ';
			foreach($day_of_week_bitmask as $dow => $mask)
			{
				if($obj->days_of_week & $mask)
				{
					$option .= "{$dow}, ";
				}
			}
			echo "{$option}&nbsp;&bull;&nbsp; START: " . date('g:i A', strtotime($obj->start_time)) . " &nbsp;&bull;&nbsp; DURATION: {$obj->runtime}</option>";
		}
	}
	printf('</select>
		<input type="button" class="button1 lefty-button1" title="Delete Selected Schedules" onclick="delete_schedule(\'Sprinklers\', \'#sprinklers-zone-%1$s option:selected\')" value="Delete Selected"/>
		<input type="button" class="button1 lefty-button1" title="Add New Schedule" onclick="new_sprinkler_schedule(%1$s)" value="New Schedule"/>',
		$val->pin
	);
}


?><div id="form-dialog"></div>