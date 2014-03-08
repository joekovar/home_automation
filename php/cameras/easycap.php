<?php

class easycap implements cameraInterface
{
	protected $input;

	public function __construct($attributes)
	{
		parse_str($attributes, $attributes);
		foreach($attributes as $key => $val)
		{
			switch($key)
			{
				case 'input':
					$this->$key = $val;
				break;
			}
		}
	}

	public function screenshot()
	{
		$screenshot	= "cache/cameras/screenshot-{$this->input}";
		$_screenshot	= ROOT_PATH . "/{$screenshot}";

		shell_exec('rm ' . "{$_screenshot}.*");
		shell_exec(ROOT_PATH . "/backup/somagic-easycap_1.1/somagic-capture -i {$this->input} -f 1 --vo={$_screenshot}.raw");
		shell_exec("convert -size 720x480 'UYVY:{$_screenshot}.raw' {$_screenshot}.jpg");

		return "{$screenshot}.jpg";
	}
}

?>