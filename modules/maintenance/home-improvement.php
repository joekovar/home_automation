<?php

include_once(ROOT_PATH . '/php/common.php');

if(_GET('submit-project-complete', false))
{
	if( !empty($_POST['project']) && is_array($_POST['project']))
	{
		foreach($_POST['project'] as $key => $val)
		{
			$_POST['project'][$key] = (int)$val;
			if($_POST['project'][$key] <= 0)
			{
				unset($_POST['project'][$key]);
			}
		}
		if( !empty($_POST['project']))
		{
			if($db->query('UPDATE `house_projects` SET `completed` = 1 WHERE `id` IN (' . implode(',', $_POST['project']) . ')'))
			{
				message::display('Successfully updated projects');
			}
		}
	}
	else
	{
		message::display('No projects selected.');
	}
}

if(_GET('submit-project-delete', false))
{
	if( !empty($_POST['project']) && is_array($_POST['project']))
	{
		foreach($_POST['project'] as $key => $val)
		{
			$_POST['project'][$key] = (int)$val;
			if($_POST['project'][$key] <= 0)
			{
				unset($_POST['project'][$key]);
			}
		}
		if( !empty($_POST['project']))
		{
			if($db->query('DELETE FROM `house_projects_materials` WHERE `project_id` IN (' . implode(',', $_POST['project']) . ')'))
			{
				if($db->query('DELETE FROM `house_projects` WHERE `id` IN (' . implode(',', $_POST['project']) . ')'))
				{
					message::display('Successfully deleted selected projects');
				}
			}
		}
	}
	else
	{
		message::display('No projects selected.');
	}
}

if(_GET('submit-new-project', false))
{
	if($result = $db->query(sprintf('INSERT INTO `house_projects` (`name`, `notes`) VALUES ("%1$s", "%2$s")',
		$db->real_escape_string(_GET('project-name')),
		$db->real_escape_string(_GET('project-notes'))
	)))
	{
		$project_id = $db->insert_id;
		if(! empty($_POST['materials']['name']) && (count($_POST['materials']['name']) === count($_POST['materials']['cost'])))
		{
			$sql = 'INSERT INTO `house_projects_materials` (`project_id`, `name`, `cost`) VALUES ';
			for($i = 0, $j = count($_POST['materials']['name']); $i < $j; $i++)
			{
				$sql .= sprintf('("%1$s", "%2$s", "%3$s"),',
					$project_id,
					$db->real_escape_string($_POST['materials']['name'][$i]),
					floatval($_POST['materials']['cost'][$i])
				);
			}
			if($db->query(substr($sql, 0, -1)))
			{
				message::display('Added new project and materials successfully');
			}
		}
	}
}

if($result = $db->query('SELECT `id`, `name`, `last_action`, `notes`, `completed` FROM `house_projects` ORDER BY `name` ASC'))
{
	$house_stats			= array(
		'by_year'	=> array()
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
		if($result = $db->query('SELECT `id`, `project_id`, `name`, `cost`, `obtained` FROM `house_projects_materials`'))
		{
			while($obj = $result->fetch_object())
			{
				$house_projects[$obj->project_id]->materials[$obj->id]	= $obj;
				$house_projects[$obj->project_id]->estimated_cost		+= $obj->cost;
				$house_projects[$obj->project_id]->materials_obtained	+= $obj->obtained ? 1 : 0;
				$house_projects[$obj->project_id]->material_count++;
			}
		}
		
		printf('<p>Jump to: <a href="#new-project">New Project</a> &bull; <a href="#active-projects">Active Projects</a> &bull; <a href="#completed-projects">Completed Projects</a> &bull; <a href="#statistics">Statistics</a><br/>Tracking <strong>%1$s</strong> projects, <strong>%2$s</strong> active and <strong>%3$s</strong> completed.</p>',
			count($house_projects),
			count($project_statuses['active']),
			count($project_statuses['completed'])
		);

		if( !empty($project_statuses['active']))
		{
			echo '<form action="" method="post"><h3 class="subtitle" id="active-projects" style="">Active Projects</h3><table class="events" cellpadding="3"><tr><th>Last Action</th><th>Cost</th><th>Tools</th><th>Mark</th></tr>';
			foreach($project_statuses['active'] as $key => $obj)
			{
				printf('<tr><td colspan="5" class="label">%1$s <span class="note">%6$s</span></td></tr><tr><td>%2$s</td><td>%3$s</td><td class="tools">%5$s</td><td>%7$s</td></tr>',
					$obj->name,
					date($config['date-only-format'], $obj->last_action),
					'$' . number_format($obj->estimated_cost, 2, '.', ','), // cost
					'', //sprintf('%1$s/%2$s', $obj->materials_obtained, $obj->material_count), // materials obtained
						'<img title="Add Materials/Costs" src="./style/img/ico/cart_put.png" style="cursor:pointer;" onclick="add_materials(' . $obj->id . ')"/>'
						. '<img title="View Materials/Costs" src="./style/img/ico/cart.png" style="cursor:pointer;" onclick="view_materials(' . $obj->id . ', \'' . $obj->name . '\')"/>',
					$obj->notes,
					sprintf('<input type="checkbox" name="project[%1$s]" value="%1$s"/>', $obj->id)
				);
			}
			echo '</table><input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit-project-complete" value="Mark Completed"/>&nbsp;&nbsp;<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit-project-delete" value="Delete Selected"/></form><br class="clearfix"/>';
		}

		if( !empty($project_statuses['completed']))
		{
			echo '<h3 class="subtitle" id="completed-projects" style="">Completed Projects</h3><table class="events" cellpadding="3"><tr><th>Date Completed</th><th>Cost</th><th>Tools</th></tr>';
			foreach($project_statuses['completed'] as $key => $obj)
			{
				printf('<tr><td colspan="3" class="label">%1$s <span class="note">%6$s</span></td></tr><tr><td>%2$s</td><td>%3$s</td><td class="tools">%5$s</td></tr>',
					$obj->name,
					date($config['date-only-format'], $obj->last_action),
					'$' . number_format($obj->estimated_cost, 2, '.', ','), // cost
					'', // materials obtained
						'<img title="Add Materials/Costs" src="./style/img/ico/cart_put.png" style="cursor:pointer;" onclick="add_materials(' . $obj->id . ')"/>'
						. '<img title="View Materials/Costs" src="./style/img/ico/cart.png" style="cursor:pointer;" onclick="view_materials(' . $obj->id . ', \'' . $obj->name . '\')"/>'
						. '<img title="Change Completion Date" src="./style/img/ico/calendar_edit.png" style="cursor:pointer;" onclick="backdate(' . $obj->id . ', \'' . $obj->name . '\')"/>', // tool buttons
					$obj->notes,
					''
				);

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
			}
			echo '</table><br class="clearfix"/>';
		}
	}
}

?>

<form action="" method="post">
	<h3 class="subtitle" id="new-project">New Project</h3>
	<p>Enter the details of the new home improvement project to keep track of.</p>
	<fieldset>
	<dl style="float:left; ">
		<dt>Project Name</dt>
		<dd><input type="text" class="text" name="project-name" id="service-name" value=""/></dd>
		
		<dt>Notes</dt>
		<dd><textarea class="text" name="project-notes" id="project-notes" rows="3"></textarea></dd>
		
		<dt>Materials</dt>
		<dd><br/><br/>
			<div style="float:left; width:330px;">Name&nbsp;<input type="text" class="text" style="width:230px;" name="materials[name][]" value=""/></div>
			<div style="float:left; width:150px;">Cost&nbsp;<input type="text" class="text" style="width:70px;" name="materials[cost][]" value=""/></div>
			<input type="button" class="button1" style="margin:10px 0 0 150px;" onclick="this.parentNode.parentNode.appendChild(this.parentNode.cloneNode(true)); this.parentNode.removeChild(this);" value="Add More"/>
		</dd>
	</dl>
	<br class="clearfix"/>
	<input type="submit" class="button1" style="float:right; margin:50px 10px 0 0;" name="submit-new-project" value="Track Project"/>
	</fieldset>
</form>

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

function backdate(project_id, project_name)
{
	var _dialog = $('<div>YYYY-MM-DD<br/><input type="text" id="new-project-date"/></div>');
	_dialog.dialog({
			width: 500,
			title: 'Change Completion Date: ' + project_name,
			modal: true,
			buttons: [
				{text: "Update", click: function(){
					$('<div></div>').load("./action.php?action=home-improvement&mode=backdate&project_id=" + project_id + "&new_date=" + _dialog.find('#new-project-date').val()).dialog();
					_dialog.dialog("close");
				}}
			]
		});
}

function add_materials(project_id)
{
	$('#form-dialog').load('./html-forms/new-house-project-materials.html', function(){
		var _dialog = $(this);
		
		_dialog.dialog({
			width: 500,
			title: 'Add Materials / Costs',
			modal: true,
			buttons: [
				{text: "Finished", click: function(){
					var str = '';
					_dialog.find('input[type="text"]').each(function(){
						str += '&' + $(this).attr('name') + '=' + escape($(this).val());
					});
					$('<div></div>').load("./action.php?action=home-improvement&mode=add-materials&project_id=" + project_id + str).dialog();
					_dialog.dialog("close");
				}}
			]
		});
	});
}
</script>