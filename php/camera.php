<?php

/*
	The php/cameras/README file explains how camera modules work.
*/

interface cameraInterface
{
	/*
		$attributes is a URL encoded string of attributes for the camera
	*/
	public function __construct($attributes);

	/*
		generates a screenshot from the camera,
		saves to the cache folder,
		returns the URI of the screenshot
	*/
	public function screenshot();
}

final class camera
{
	private $module;

	public function screenshot()
	{
		return $this->module->screenshot();
	}
	
	public function __get($attribute)
	{
		return $this->module->$attribute;
	}
	
	public static function modules()
	{
		static $modules = array();

		if(empty($modules))
		{
			foreach(glob(ROOT_PATH . '/php/cameras/*.php') as $module)
			{
				$module = substr(basename($module), 0, -4);
				$modules[$module] = $module;
			}
		}
		
		return $modules;
	}

	public function __construct($class, $attributes)
	{
		include_once(ROOT_PATH . '/php/cameras/' . preg_replace('#[^a-z0-9_]+#', '', $class) . '.php');

		if(class_exists($class))
		{
			$this->module =  new $class($attributes);

			if($this->module instanceof cameraInterface)
			{
				return $this;
			}
			else
			{
				trigger_error("'{$class}' does not implement cameraInterface", E_USER_WARNING);
				return false;
			}
		}

		trigger_error("No module exists for camera of type '${class}'", E_USER_WARNING);
		return false;
	}
	
}

?>