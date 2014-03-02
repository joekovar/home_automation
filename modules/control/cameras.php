<?php include_once(ROOT_PATH . '/php/common.php'); ?>

<p><?php echo date($config['date-format'], time()); ?></p>

<?php

shell_exec('rm ' . ROOT_PATH . '/cache/cameras/screenshot-*');

for($i = 1; $i < 2; $i++)
{
	$screenshot	= "cache/cameras/screenshot-{$i}";
	$_screenshot	= ROOT_PATH . "/{$screenshot}";

	shell_exec(ROOT_PATH . "/backup/somagic-easycap_1.1/somagic-capture -i {$i} -f 1 --vo={$_screenshot}.raw");
	shell_exec("convert -size 720x480 'UYVY:{$_screenshot}.raw' {$_screenshot}.jpg");

	echo "<img src='{$screenshot}.jpg' style='width:320px; height:240px; margin:10px;'/>\n";
}

?>