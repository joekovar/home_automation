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
	
</table>

<?php

$screenshots = scandir(ROOT_PATH . '/cache/cameras/doorbell');
//print_pre($screenshots);
foreach($screenshots as $val)
{
	if(substr($val, -3, 3) == 'jpg')
	{
		printf('<img style="width:280px; height:210px;" src="cache/cameras/doorbell/%1$s"/>', $val);
	}
}

?>