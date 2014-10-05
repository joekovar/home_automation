<?php

switch(_GET('mode', ''))
{
	case 'show-materials':
		if($result = $db->query('SELECT `id`, `project_id`, `name`, `cost`, `obtained` FROM `house_projects_materials` WHERE `project_id` = ' . (int)_GET('project_id', 0) . ' ORDER BY `cost` DESC'))
		{
			echo '<table class="events" cellpadding="3"><tr><th>Name</th><th>Cost</th></tr>';
			while($obj = $result->fetch_object())
			{
				printf('<tr><td class="label">%3$s</td><td>%4$s</td></tr>',
					$obj->id,
					$obj->project_id,
					$obj->name,
					'$' . number_format($obj->cost, 2, '.', ','),
					$obj->obtained
				);
			}
			echo '</table>';
		}
	break;
	
	case 'add-materials':
		if(empty($_GET['project_id']))
		{
			message::display('Missing project ID');
		}
		else
		{
			if(! empty($_GET['materials']['name']) && (count($_GET['materials']['name']) === count($_GET['materials']['cost'])))
			{
				$sql = 'INSERT INTO `house_projects_materials` (`project_id`, `name`, `cost`) VALUES ';
				for($i = 0, $j = count($_GET['materials']['name']); $i < $j; $i++)
				{
					$sql .= sprintf('("%1$s", "%2$s", "%3$s"),',
						(int)_GET('project_id', 0),
						$db->real_escape_string($_GET['materials']['name'][$i]),
						floatval($_GET['materials']['cost'][$i])
					);
				}
				if($db->query(substr($sql, 0, -1)))
				{
					message::display('Added new materials / costs successfully');
				}
				else
				{
					message::display($db->error);
				}
			}
		}
	break;
	
	case 'backdate':
		$sql = 'UPDATE `house_projects`
			SET `last_action` = FROM_UNIXTIME(' . strtotime(_GET('new_date', date('r', time()))) . ')
			WHERE `id` = ' . (int)_GET('project_id', 0);
		if($result = $db->query($sql))
		{
			message::display('Backdated project successfully');
		}
		else
		{
			message::display($db->error);
			message::display($sql);
			message::display(strtotime(_GET('new_date')));
		}
	break;
	
	case 'edit-notes':
		$sql = sprintf('UPDATE `house_projects` SET `notes` = "%1$s", `last_action` = `last_action` WHERE `id` = %2$s',
			$db->real_escape_string(_GET('notes')),
			(int)_GET('project_id')
		);
		if($db->query($sql))
		{
			message::display('Updated notes successfully');
		}
		else
		{
			message::display($db->error);
			message::display($sql);
		}
	break;
	
	default:
		echo 'unknown mode';
	break;
}

?>