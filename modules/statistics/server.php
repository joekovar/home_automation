<p>Information about the PC/Server.</p>
<?php

include_once(ROOT_PATH . '/php/common.php');

if($key = scandir('/home'))
{
	if(count($key) > 2)
	{
		echo '<h3 class="subtitle">Users</h3><table class="config"><tr><th>Username</th><th>Disk Space Used</th></tr>';
		foreach($key as $val)
		{
			if($val[0] == '.')
			{
				continue;
			}
			if($size = shell_exec("du -sb /home/$val"))
			{
				preg_match('/^\d+/', $size, $size);
				printf('<tr><td class="label">%1$s</td><td>%2$s</td></tr>', $val, new large_filesize($size[0]));
			}
		}
		echo '</table>';
	}
}

if($key = shell_exec('df -h'))
{
	if(preg_match_all('#^(/.+?)\s+([\.0-9]+[KGMT])\s+([\.0-9]+[KGMT])\s+([\.0-9]+[KGMT])\s+(\d{1,2}%)\s+(\S+)$#mi', $key, $val, PREG_SET_ORDER))
	{
		echo '<h3 class="subtitle">Filesystem</h3><table class="config"><tr><th>Filesystem</th><th>Size</th><th>Used</th><th>Available</th><th>% Used</th><th>Mounted On</th></tr>';
		foreach($val as $key)
		{
			$key[7] = (int)str_replace('%', '', $key[5]);
			printf('<tr><td class="label">%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td><td>%5$s</td><td>%6$s</td></tr>',
				$key[1],
				$key[2],
				$key[3],
				$key[4],
				$key[5],
				$key[6]
			);
		}
		echo '</table>';
	}
}

if($key = shell_exec('free -mo'))
{
	if(preg_match_all('#^(Mem|Swap):\s+(\d+)\s+(\d+)\s+(\d+)\s.*$#mi', $key, $val, PREG_SET_ORDER))
	{
		echo '<h3 class="subtitle">Memory &amp; Swapfile</h3><table class="config"><tr><th>Type</th><th>Total</th><th>Used</th><th>Free</th></tr>';
		foreach($val as $key)
		{
			printf('<tr><td class="label">%1$s</td><td>%2$sM</td><td>%3$sM</td><td>%4$sM</td></tr>',
				$key[1],
				$key[2],
				$key[3],
				$key[4]
			);
		}
		echo '</table>';
	}
}

?>