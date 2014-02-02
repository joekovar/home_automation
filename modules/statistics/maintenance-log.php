<p>The <strong>green</strong> bar represents which percentage of the logged maintenance occured ahead of schedule.<br/>
The <strong>red</strong> bar represents maintenance which was past due.<br/>
The <strong>dark gray</strong> bar represents maintenance performed right on time.</p><?php

include_once(ROOT_PATH . '/php/common.php');

$maintenance_items = array();

if($result = $db->query('SELECT * FROM `maintenance_items` ORDER BY `name` ASC'))
{
	while($obj = $result->fetch_object())
	{
		$obj->timing = array('-1' => 0, '0' => 0, '1' => 0, 'total' => 0);
		$maintenance_items[$obj->id] = $obj;
	}
}

if($result = $db->query('SELECT `item_id`, `timing`, COUNT(`timing`) AS `total` FROM `maintenance_log` GROUP BY `item_id`, `timing`'))
{
	while($obj = $result->fetch_object())
	{
		if(empty($maintenance_items[$obj->item_id]))
		{
			continue;
		}
		$maintenance_items[$obj->item_id]->timing[$obj->timing]	= (int)$obj->total;
		$maintenance_items[$obj->item_id]->timing['total']				+= (int)$obj->total;
	}
}

//print_pre($maintenance_items);


echo '<table class="events" cellpadding="3"><tr><th>Item</th><th>Timing</th><th>Count</th></tr>';
foreach($maintenance_items as $key => $val)
{
	printf('<tr><td class="label">%1$s</td><td><div style="height:20px; float:right; width:%2$spx; background:#b00;"></div><div style="height:20px; float:right; width:%3$spx; background:#222;"></div><div style="height:20px; float:right; width:%4$spx; background:#393;"></div></td><td>%5$s</td></tr>',
		$val->name,
		($val->timing['-1'] / max(1, $val->timing['total'])) * 300,
		($val->timing['0'] / max(1, $val->timing['total'])) * 300,
		($val->timing['1'] / max(1, $val->timing['total'])) * 300,
		$val->timing['total']
	);
}
echo '</table>';

$maintenance_stats = array(
	'days' => array(
		'Monday'		=> 0,
		'Tuesday'		=> 0,
		'Wednesday'	=> 0,
		'Thursday'		=> 0,
		'Friday'			=> 0,
		'Saturday'		=> 0,
		'Sunday'		=> 0
	)
);

if($result = $db->query('SELECT * FROM `maintenance_log`'))
{
	while($obj = $result->fetch_object())
	{
		$maintenance_stats['days'][date('l', strtotime($obj->performed))]++;
	}

	$_key	= '';
	$_val	= '';
	foreach($maintenance_stats['days'] as $key => $val)
	{
		$_key	.= "<th>{$key}</th>";
		$_val	.= "<td>{$val}</td>";
	}
	echo "<h3 class='subtitle'>Maintenance Days</h3><p>The following is a record of which days have had the most maintenance performed.</p><table class='config'><tr>{$_key}</tr><tr>{$_val}</tr></table>";
}

?>