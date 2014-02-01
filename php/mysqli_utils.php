<?php

class mysqli_utils
{
	public static function options_for_result($value_field, $label_field, &$result, $selected = array())
	{
		$str = '';
		while($row = $result->fetch_object())
		{
			$str .= sprintf('<option value="%1$s"%3$s>%2$s</option>',
				isset($row->$value_field) ? $row->$value_field : '',
				isset($row->$label_field) ? $row->$label_field : 'Default Label',
				isset($selected[$row->$value_field]) ? ' selected="selected"' : ''
			);
		}
		return $str;
	}
}

?>