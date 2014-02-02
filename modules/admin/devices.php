<p>This is a list of devices currently in the database for the system. The <strong>Used</strong> column
	notes whether the device is actually hooked up to the system and running.</p>
<?php

include_once(ROOT_PATH . '/php/common.php');


?>
<table class="config">
	<tr>
		<th>Pin</th>
		<th>Name</th>
		<th>I/O</th>
		<th>Special I/O</th>
		<th>Default</th>
		<th>State</th>
		<th>Used</th>
	</tr>
<?php
/*
(
    [pin] => 36
    [name] => Dining Room Motion
    [input] => 1
    [default_state] => 0
    [special_input] => 0
    [implemented] => 0
    [state] => 0
)
*/

foreach($pins as $key => $obj)
{
	printf('<tr><td class="label">%1$s</td><td class="label">%2$s</td><td class="label">%3$s</td><td class="label">%4$s</td><td class="label">%5$s</td><td class="label">%6$s</td><td>%7$s</td></tr>',
		$obj->pin,
		$obj->name,
		$obj->input ? 'I' : 'O',
		html::label($obj->special_input ? 'Yes' : 'No'),
		$obj->default_state ? 'N/C' : 'N/O',
		html::label($obj->state ? ($obj->input ? 'Closed' : 'On') : ($obj->input ? 'Open' : 'Off')),
		html::label($obj->implemented ? 'Yes' : 'No')
	);
}

?>
</table>