<?php

if($xml = file_get_contents('http://' . ARDUINO_IP . '/status'))
{
  echo '<table class="config">';
  if($xml = new SimpleXMLElement($xml))
  {
    $status = $xml->attributes();
    printf('<tr><td class="label">Free RAM</td><td>%1$s bytes</td></tr><tr><td class="label">Arduino millis()</td><td >%2$s</td></tr>',
      $status['freeram'],
      elapsed_time(bcdiv($status['millis'], 1000), false)
    );
  }
}
else
{
  echo '<h3>Unable to contact Arduino at ' . ARDUINO_IP . '</h3><table class="config">';
}
printf('<tr><td class="label">Startup Time</td><td>%1$s</td></tr><tr><td class="label">Elapsed</td><td>%2$s</td></tr>',
	date($config['date-format'], $config['startup-time']),
	elapsed_time($config['startup-time'])
);

$inputs = array('0' => 0, '1' => 0);
if($result = $db->query('SELECT `input`, COUNT(`input`) AS total FROM `pin_info` GROUP BY `input` ORDER BY `input` ASC'))
{
	while($obj = $result->fetch_object())
	{
		$inputs[$obj->input] = $obj->total;
	}
	printf('<tr><td class="label">Inputs</td><td >%1$s</td></tr><tr><td class="label">Outputs</td><td>%2$s</td></tr>',
		$inputs['1'],
		$inputs['0']
	);
}

$implemented = array('0' => 0, '1' => 0);
if($result = $db->query('SELECT `implemented`, COUNT(`implemented`) AS total FROM `pin_info` GROUP BY `implemented`'))
{
	while($obj = $result->fetch_object())
	{
		$implemented[$obj->implemented] = $obj->total;
	}
	printf('<tr><td class="label">Implemented</td><td >%1$s%%</td></tr>',
		round(($implemented['1'] / ($implemented['0'] + $implemented['1'])) * 100)
	);
}

echo '</table>';

?></table>