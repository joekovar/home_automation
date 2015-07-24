<?php

include_once(ROOT_PATH . '/php/common.php');


if($result = $db->query('SELECT `id`, `name`, `last_action`, `notes`, `completed` FROM `house_projects` ORDER BY `last_action` DESC'))
{
	$house_stats			= array(
		'by_year'			=> array(),
		'by_year_totals'	=> array(
			'costs'		=> 0,
			'projects'	=> 0
		)
	);
	$house_projects		= array();
	$project_statuses	= array(
		'completed'	=> array(),
		'active'			=> array()
	);

	while($obj = $result->fetch_object())
	{
		$obj->estimated_cost			= 0;
		$obj->material_count			= 0;
		$obj->materials_obtained	= 0;
		$obj->materials					= array();
		$obj->last_action				= strtotime($obj->last_action);
		$house_projects[$obj->id] = $obj;
		$project_statuses[$obj->completed ? 'completed' : 'active'][] =& $house_projects[$obj->id];
	}
	$result->free_result();
	
	if(! empty($house_projects))
	{
		$db->query('DELETE FROM `house_projects_materials` WHERE `name` = ""');
		if($result = $db->query('SELECT `id`, `project_id`, `name`, `cost`, `obtained` FROM `house_projects_materials` ORDER BY `name` ASC'))
		{
			while($obj = $result->fetch_object())
			{
				$_material = explode(' ', preg_replace('#\s{2,}#', ' ', preg_replace('#[^a-z ]+|s\b#', '', strtolower($obj->name))));
				foreach($_material as $val)
				{
					$val = trim($val);
					if(strlen($val) < 2)
					{
						continue;
					}
				}

				$house_projects[$obj->project_id]->materials[$obj->id]	= $obj;
				$house_projects[$obj->project_id]->estimated_cost		+= $obj->cost;
				$house_projects[$obj->project_id]->materials_obtained	+= $obj->obtained ? 1 : 0;
				$house_projects[$obj->project_id]->material_count++;
			}
		}
		
		$house_count = 0;
		if($result = $db->query('SELECT COUNT(`id`) AS `house_count` FROM `houses`'))
		{
			if($obj = $result->fetch_object())
			{
				$house_count = $obj->house_count;
			}
		}
		
		
		printf('<p>Tracking <strong>%1$s</strong> projects at <strong>%4$s</strong> houses, <strong>%2$s</strong> active and <strong>%3$s</strong> completed.</p>',
			count($house_projects),
			count($project_statuses['active']),
			count($project_statuses['completed']),
			$house_count
		);

		if( !empty($project_statuses['completed']))
		{
			foreach($project_statuses['completed'] as $key => $obj)
			{
				$_Y = date('Y', $obj->last_action);
				if(empty($house_stats['by_year'][$_Y]))
				{
					$house_stats['by_year'][$_Y] = array(
						'costs'		=> 0,
						'projects'	=> 0
					);
				}
				$house_stats['by_year'][$_Y]['costs'] += $obj->estimated_cost;
				$house_stats['by_year'][$_Y]['projects']++;
				
				$house_stats['by_year_totals']['costs'] += $obj->estimated_cost;
				$house_stats['by_year_totals']['projects']++;
			}
			echo '</table><br class="clearfix"/>';
		}
	}
}

?>

<h3 class="subtitle" id="statistics" style="">Statistics</h3>
<table class="events" cellpadding="3">
	<tr><th>Year</th><th>Completed Projects</th><th>Costs</th><th>Sales Taxes</th><th>Total</th></tr>
<?php

foreach($house_stats['by_year'] as $key => $val)
{
	printf('<tr><td class="label">%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td><td>%5$s</td></tr>',
		$key,
		$val['projects'],
		'$' . number_format($val['costs'], 2, '.', ','),
		'$' . number_format(($val['costs'] * $config['sales-tax-rate']), 2, '.', ','),
		'$' . number_format(($val['costs'] + ($val['costs'] * $config['sales-tax-rate'])), 2, '.', ',')
	);
}
printf('<tr><td class="label">%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td><td>%5$s</td></tr>',
	'Totals',
	$house_stats['by_year_totals']['projects'],
	'$' . number_format($house_stats['by_year_totals']['costs'], 2, '.', ','),
	'$' . number_format(($house_stats['by_year_totals']['costs'] * $config['sales-tax-rate']), 2, '.', ','),
	'$' . number_format(($house_stats['by_year_totals']['costs'] + ($house_stats['by_year_totals']['costs'] * $config['sales-tax-rate'])), 2, '.', ',')
);

?>
</table>
<div id="form-dialog"></div>
<script type="text/javascript">
function view_materials(project_id, project_name)
{
	$('<div></div>').load('./action.php?action=home-improvement&mode=show-materials&project_id=' + project_id, function(){
		var _dialog = $(this);
		_dialog.dialog({
			width: 500,
			title: 'Materials / Costs: ' + project_name,
			modal: false,
		});
	});
}

</script>
