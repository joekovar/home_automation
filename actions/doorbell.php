<?php

switch(_GET('mode', ''))
{
	case 'enable-disable':
		if($db->query('INSERT INTO `config` (`key`, `val`) VALUES ("doorbell-enabled", ' . (@$config['doorbell-enabled'] ? 0 : 1) . ') ON DUPLICATE KEY UPDATE `val` = VALUES(`val`)'))
		{
			message::display('Successfully ' . ($config['doorbell-enabled'] ? 'disabled' : 'enabled') . ' doorbell');
		}
		else
		{
			message::display($db->error);
		}
	break;
	
	case 'auto-delete':
		if($db->query('INSERT INTO `config` (`key`, `val`) VALUES ("doorbell-auto-delete-days", ' . (int)_GET('days', 0) . ') ON DUPLICATE KEY UPDATE `val` = VALUES(`val`)'))
		{
			message::display('Successfully changed doorbell auto delete timeframe.');
		}
		else
		{
			message::display($db->error);
		}
	break;
	
	default:
		message::display('Unknown mode');
	break;
}

?>