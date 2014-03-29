<?php

define('ROOT_PATH', dirname($_SERVER['SCRIPT_FILENAME']));
include_once(ROOT_PATH . '/php/common.php');

$action = preg_replace('#[^a-z0-9_-]+#', '', _GET('action', ''));

if(file_exists(ROOT_PATH . "/actions/{$action}.php"))
{
	require(ROOT_PATH . "/actions/{$action}.php");
	exit;
}

message::display('Unrecognized action');

?>