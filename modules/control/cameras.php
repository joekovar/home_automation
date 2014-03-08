<?php include_once(ROOT_PATH . '/php/common.php'); ?>

<p><?php echo date($config['date-format'], time()); ?></p>

<?php

if($result = $db->query('SELECT `name`, `module`, `attributes` FROM `cameras` ORDER BY `name` ASC'))
{
	while($obj = $result->fetch_object())
	{
		$camera = new camera($obj->module, $obj->attributes);
		
		printf('<div style="float:left; margin:5px;"><h3>%2$s</h3><img src="%1$s" style="width:280px; height:210px;"/></div>',
			$camera->screenshot(), $obj->name
		);
	}
}

?>
<br style="clear:both;"/>