<?php

include_once('/var/www/php/common.php');

if(_GET('update-config-stats', false))
{
	$sql = '';
	foreach($config as $key => $val)
	{
		$val = (int)shell_exec("grep -R '{$key}' /var/www/ | wc -l");
		$sql .= ",('{$key}', {$val})";
	}
	if( !empty($sql))
	{
		if($result = $db->query('INSERT INTO `config` (`key`, `use_count`) VALUES ' . substr($sql, 1) . ' ON DUPLICATE KEY UPDATE `use_count` = VALUES(`use_count`)'))
		{
			$messages[] = new message('Scanned the filesystem and updated configuration option use counts');
		}
		else
		{
			$messages[] = new message($db->error);
		}
	}
	else
	{
		$messages[] = new message('fail');
	}
}

if(_GET('submit-new-config', false))
{
	$new = array(
		'config-key'	=> preg_replace('#[^a-z0-9_-]+#', '', str_replace(' ', '-', _GET('new-config-key', ''))),
		'config-val'		=> _GET('new-config-val', '')
	);
	if($new['config-key'])
	{
		$sql = sprintf('INSERT INTO `config` (`key`, `val`) VALUES ("%1$s", "%2$s") ON DUPLICATE KEY UPDATE `val` = VALUES(`val`);',
			$new['config-key'],
			$db->real_escape_string($new['config-val'])
		);
		if( ! $db->query($sql))
		{
			$messages[] = new message($db->error);
		}
		else
		{
			$config[$new['config-key']] = $new['config-val'];
		}
	}
}

?>
<p>These configuration options are used throughout the system. Be careful changing them.</p>
<table class="config">
	<tr>
		<th>Configuration Key</th>
		<th>Configuration Value</th>
		<th>Times Used</th>
	</tr>
	<?php
		if($result = $db->query('SELECT * FROM `config` ORDER BY `key` ASC'))
		{
			while($obj = $result->fetch_object())
			{
				printf('<tr><td class="label"><img title="Send to Editor" src="./style/images/cog_edit.png" style="float:right; cursor:pointer;" onclick="edit_config(\'%1$s\', \'%2$s\')"/>%1$s</td><td>%2$s</td><td>%3$s</td></tr>', $obj->key, $obj->val, $obj->use_count);
			}
			$result->close();
		}
	?>
</table>

<form action="" method="post">
	<fieldset>
	<input type="submit" class="button1" style="float:right; margin:0 10px 0 0;" name="update-config-stats" value="Update Use Stats"/>
	</fieldset>
</form>
<script type="text/javascript">
function edit_config(key, val)
{
	document.getElementById('new-config-key').value = key;
	document.getElementById('new-config-val').value = val;
}
</script>
<form action="" method="post">
	<h3 class="subtitle">Update Configuration</h3>
	<p>Type in the key of the configuration option you want to add or or click the little cog beside the one you want to update, and the new value for that key.</p>
	<fieldset>
	<dl style="float:left; width:300px;">
		<dt>Config Key</dt>
		<dd><input type="text" class="text" name="new-config-key" id="new-config-key" value=""/></dd>
		
		<dt>Config Value</dt>
		<dd><input type="text" class="text" name="new-config-val" id="new-config-val" value=""/></dd>
	</dl>
	<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit-new-config" value="Update Configuration"/>
	</fieldset>
</form>