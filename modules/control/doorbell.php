<p>Doorbell control</p>
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
	<tr><td class="label">Doorbell</td><td>
		<select onchange="$('<div></div>').load('./action.php?action=doorbell&mode=enable-disable').dialog();">
			<option value="Enabled" <?php echo @$config['doorbell-enabled'] ? 'selected' : ''; ?> >Enabled</option>
			<option value="Disabled" <?php echo @$config['doorbell-enabled'] ? '' : 'selected'; ?> >Disabled</option>
		</select>
	</td></tr>
	<tr><td class="label">Auto Delete</td><td>
		<select id="auto-delete-days" onchange="$('<div></div>').load('./action.php?action=doorbell&mode=auto-delete&days=' + $('#auto-delete-days').val()).dialog();">
			<?php
			$auto_delete_options = array('1' => '1 Day', '3' => '3 Days', '7' => '1 Week', '14' => '2 Weeks', '30' => '1 Month', '0' => 'Never');
			foreach($auto_delete_options as $key => $val)
			{
				printf('<option value="%1$s"%3$s>%2$s</option>', $key, $val, ($key == @$config['doorbell-auto-delete-days'] ? 'selected="selected"' : ''));
			}
			?>
		</select>
	</td></tr>
	
</table>

<?php

$screenshots = scandir(ROOT_PATH . '/cache/cameras/doorbell');
$last_screenshot_date = '';
for($i = count($screenshots) - 1; $i > -1; $i--)
{
	if(substr($screenshots[$i], -3, 3) == 'jpg')
	{
		$screenshot_date = date($config['date-only-format'], basename($screenshots[$i]));
		if($screenshot_date != $last_screenshot_date)
		{
			$last_screenshot_date = $screenshot_date;
			printf('<br style="clear:both;"/><h3 class="subtitle">%1$s</h3>', $screenshot_date);
		}

		printf('<div style="width:180px; float:left; margin:5px 0 0 2px;"><h6 style="padding:0; margin:0 0 3px 2px;">%2$s</h6><img style="width:180px; cursor:pointer;" src="cache/cameras/doorbell/%1$s" onclick="%3$s"/></div>',
			$screenshots[$i],
			date($config['date-format'], basename($screenshots[$i])),
			"$('<div><img src=\'' + this.src + '\' style=\'margin:10px;\'/></div>').dialog({width:780})"
		);
	}
}

?>
<br style="clear:both;"/>