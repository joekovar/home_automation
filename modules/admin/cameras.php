<?php

include_once(ROOT_PATH . '/php/common.php');

if(_GET('submit-new-camera', false))
{
	if($db->query(sprintf('INSERT INTO `cameras` (`name`, `module`, `attributes`) VALUES("%1$s", "%2$s", "%3$s")',
		$db->real_escape_string(_GET('camera-name')),
		$db->real_escape_string(_GET('camera-module')),
		$db->real_escape_string(_GET('camera-attributes'))
	)))
	{
		message::display('Successfully added new camera');
	}
	else
	{
		message::display($db->error);
	}
}

?>

<p>Registered cameras.</p>
<table class="config">
	<tr>
		<th>Name</th>
		<th>Module</th>
		<th>Attributes</th>
	</tr>
	<?php
		if($result = $db->query('SELECT `name`, `module`, `attributes` FROM `cameras` ORDER BY `name` ASC'))
		{
			while($obj = $result->fetch_object())
			{
				printf('<tr><td class="label">%1$s</td><td>%2$s</td><td>%3$s</td></tr>', $obj->name, $obj->module, htmlentities($obj->attributes));
			}
			$result->close();
		}
	?>
</table>

<form action="" method="post">
	<h3 class="subtitle">New Camera</h3>
	<p>Enter the details of the new camera.</p>
	<fieldset>
	<dl style="float:left; width:300px;">
		<dt>Camera Name</dt>
		<dd><input type="text" class="text" name="camera-name" id="camera-name" value=""/></dd>
		
		<dt>Camera Module</dt>
		<dd><select name="camera-module" id="camera-module"><?php echo html::options(camera::modules()); ?></select></dd>
		
		<dt>Camera Attributes</dt>
		<dd><input type="text" class="text" name="camera-attributes" id="camera-attributes" value=""/></dd>
		
	</dl>
	<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit-new-camera" value="Register Camera"/>
	</fieldset>
</form>