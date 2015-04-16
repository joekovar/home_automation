<?php

switch(_GET('mode', ''))
{
	case 'find-scanner':
		$str = shell_exec('scanimage -f "%d"');
		if(preg_match('#^[^:]+:/#', $str))
		{
			$sql = sprintf('INSERT INTO `config` (`key`, `val`) VALUES ("%1$s", "%2$s") ON DUPLICATE KEY UPDATE `val` = VALUES(`val`);',
				'scanner-device-id',
				$db->real_escape_string($str)
			);
			if($db->query($sql))
			{
				message::display("Found Scanner: $str");
			}
			else
			{
				message::display($db->error);
			}
		}
		else
		{
			message::display('Unable to find scanner');
		}
	break;
	
	case 'scan':
		$filename = preg_replace('#[^A-Za-z0-9_-]+#', '', _GET('filename', 'last-scan'));

		if(empty($filename))
		{
			message::display('Invalid filename specified');
		}
		else
		{
			$filepath				= ROOT_PATH . '/cache/scanner/' . $filename;
			$output_format	= _GET('format', 'pdf');

			shell_exec('scanimage --format=tiff > ' . escapeshellarg("{$filepath}.tiff"));
			//$__result = shell_exec('scanimage -b --format=tiff --batch-scan=yes --batch="' . escapeshellarg("{$filepath}%d") . '.tiff" > /var/www/cache/scanner/' . $filename . '.pdf');
			//message::display('scanimage --format=tiff --batch="' . escapeshellarg("{$filepath}%d") . '"');
			//exit;
			
			switch($output_format)
			{
				case 'pdf':
				case 'jpg':
					shell_exec('convert ' . escapeshellarg("{$filepath}.tiff") . ' ' . escapeshellarg("{$filepath}.{$output_format}"));
				break;

				default:
					$output_format	= 'tiff';
				break;
			}
			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary");
			header("Content-disposition: attachment; filename=\"" . $filename . '.' . $output_format . "\"");
			readfile("{$filepath}.{$output_format}");
			exit;
		}
	break;
	
	default:
		echo 'unknown mode';
	break;
}

?>