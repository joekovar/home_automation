<?php

include_once('/var/www/php/common.php');

if(_GET('submit-new-device', false))
{
	if($db->query(sprintf('INSERT INTO `network_map` (`name`, `ip`, `mac`) VALUES("%1$s", %2$s, "%3$s") ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `ip` = VALUES(`ip`), `mac` = VALUES(`mac`)',
		$db->real_escape_string(_GET('device-name')),
		ip2long(_GET('device-ip')),
		$db->real_escape_string(_GET('device-mac'))
	)))
	{
		message::display('Successfully added new device');
	}
	else
	{
		message::display($db->error);
	}
}

?>

<p>These are registered devices connected to the home network. Click the cog to send them to the editor for updating the IP/MAC.</p>
<table class="config">
	<tr>
		<th>Name</th>
		<th>IP Address</th>
		<th>MAC Address</th>
	</tr>
	<?php
		if($result = $db->query('SELECT `ip`, `mac`, `name` FROM `network_map` ORDER BY `name` ASC'))
		{
			while($obj = $result->fetch_object())
			{
				printf('<tr><td class="label"><img title="Send to Editor" src="./style/images/cog_edit.png" style="float:right; cursor:pointer;" onclick="edit_device(\'%4$s\', \'%2$s\', \'%3$s\')"/>%1$s</td><td>%2$s</td><td>%3$s</td></tr>', $obj->name, long2ip($obj->ip), $obj->mac, addslashes($obj->name));
			}
			$result->close();
		}
	?>
</table>

<script type="text/javascript">
function edit_device(name, ip, mac)
{
	document.getElementById('device-name').value = name;
	document.getElementById('device-ip').value = ip;
	document.getElementById('device-mac').value = mac;
}
</script>

<form action="" method="post">
	<h3 class="subtitle">New Device</h3>
	<p>Enter the details of the new device, or the name of the device you want to edit and the new IP/MAC.</p>
	<fieldset>
	<dl style="float:left; width:300px;">
		<dt>Device Name</dt>
		<dd><input type="text" class="text" name="device-name" id="device-name" value=""/></dd>
		
		<dt>Device IP</dt>
		<dd><input type="text" class="text" name="device-ip" id="device-ip" value=""/></dd>
		
		<dt>Device MAC</dt>
		<dd><input type="text" class="text" name="device-mac" id="device-mac" value=""/></dd>
		
	</dl>
	<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit-new-device" value="Register Device"/>
	</fieldset>
</form>