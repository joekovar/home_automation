<p>Pin groups are used to simplify management of multiple similar devices. New groups can be created for use with new
control and statistics modules.</p>
<?php

$groups = array();

include_once('/var/www/php/common.php');

if(_GET('submit'))
{
	switch(_GET('action'))
	{
		case 'new-group':
			$db->query(sprintf('INSERT INTO `group_info` (`name`, `type`) VALUES ("%1$s", "%2$s")', _GET('name'), _GET('type')));
		break;
		
		case 'group-pins':
			$sql = '';
			foreach(_GET('pins') as $key)
			{
				foreach(_GET('groups') as $_key)
				{
					$sql .= sprintf(',(%1$s, %2$s)', $_key, $key);
				}
			}
			$db->query('INSERT INTO `pin_relations` (`group_id`, `pin_id`) VALUES ' . substr($sql, 1) . ' ON DUPLICATE KEY UPDATE `group_id` = `group_id`');
			unset($sql);
		break;
	}
}

$result = $db->query("SELECT * FROM `group_info` ORDER BY `name` ASC;");
if($result)
{
	while($obj = $result->fetch_object())
	{
		$obj->pins = array();
		$groups[$obj->id] = $obj;
	}
	$result->free_result();
	
	$result = $db->query('SELECT `group_id`, `pin_id` FROM `pin_relations`');
	while($obj = $result->fetch_object())
	{
		$groups[$obj->group_id]->pins[$obj->pin_id] =& $pins[$obj->pin_id];
	}
}

?>
	<h3 class="subtitle">Groups</h3>
	<p>Click on the existing group names below to display pins associated with that group.</p>
	<ul>
	<?php
		foreach($groups as $key => $val)
		{
			echo '<li style="list-style:none;"><span style="cursor:pointer; padding:4px; color:#ccc; font-weight:bold;" onclick="$(this.nextSibling).slideToggle();">' . $val->name . ' (#' . $key . ')</span><ul class="slide-toggle">';
			foreach($val->pins as $_val)
			{
				printf('<li>%1$s</li>', $_val->name);
			}
			echo '</ul></li>';
		}
	?>
	</ul>
	<script type="text/javascript">$('.slide-toggle').slideUp(0);</script>

	<form action="" method="post">
		<h3 class="subtitle">New Group</h3>
		<p>Enter a name, and select the type for the new group.</p>
		<fieldset>
			<dl style="float:left; width:300px;">
				<dt><label for="name">Group Name</label></dt>
				<dd><input type="text" class="text" name="name" value=""/></dd>
				
				<dt><label for="type">Group Type</label></dt>
				<dd><select name="type">
						<?php
							foreach(str_getcsv(substr($db->query("SHOW COLUMNS FROM `group_info` WHERE Field = 'type';")->fetch_object()->Type, 5, -1), ',', "'") as $key)
							{
								printf('<option>%1$s</option>', $key);
							}
						?>
					</select></dd>
			</dl>
			<input type="hidden" name="action" value="new-group"/>
			<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit" value="Create Group"/>
		</fieldset>
	</form>
<br style="clear:both;"/>
	<form action="" method="post">
		<h3 class="subtitle">Pin Grouping</h3>
		<p><span style="color:red;">red</span> options are unimplemented</p>
		<fieldset class="left-block">
			<label for="pins[]">Pins</label>
			<select name="pins[]" size="30" multiple="multiple">
				<?php
					foreach($pins as $val)
					{$test = $config['date-format'];
						printf('<option value="%1$s" style="%3$s">%2$s</option>', $val->pin, $val->name, $val->implemented ? '' : 'color:red;');
					}
				?>
			</select>
		</fieldset>
		<fieldset class="left-block">
			<label for="groups[]">Groups</label>
			<select name="groups[]" size="30" multiple="multiple">
				<?php
					foreach($groups as $val)
					{
						if($val->type == 'pin')
						{
							printf('<option value="%1$s">%2$s</option>', $val->id, $val->name);
						}
					}
				?>
			</select>
		</fieldset>
		<input type="hidden" name="action" value="group-pins"/>
		<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit" value="Group Selected Pins"/>
	</form>
<br style="clear:both;"/>