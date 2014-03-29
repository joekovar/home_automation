<?php

$id = _GET('id');
		
if(preg_match('#^\d+(,\d+)*$#', $id) && $db->query("DELETE FROM `pin_schedule` WHERE `id` IN({$id})"))
{
	message::display('Deleted selected schedules.');
}

?>