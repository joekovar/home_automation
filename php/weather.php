<?php

class weather
{
	public static $forecast_url = 'http://api.wunderground.com/api/%1$s/forecast/q/%2$s.xml';
	public static $forecast_cache = '/cache/weather/forecast.xml';

	public static $hourly_url = 'http://api.wunderground.com/api/%1$s/hourly/q/%2$s.xml';
	public static $hourly_cache = '/cache/weather/hourly.xml';
	
	public static $today_low = false;
	public static $today_high = false;

	public static function today_low()
	{
		if(weather::$today_low === false && !weather::highlow())
		{
			return false;
		}
		return weather::$today_low;
	}

	public static function today_high()
	{
		if(weather::$today_high === false && !weather::highlow())
		{
			return false;
		}
		return weather::$today_high;
	}
	
	protected static function highlow()
	{
		global $config;

		if( ! filemtime(ROOT_PATH . weather::$forecast_cache) || (time() - filemtime(ROOT_PATH . weather::$forecast_cache) >= 86400))
		{
			if($xml = file_get_contents(sprintf(weather::$forecast_url, $config['wunderground-api-key'], $config['wunderground-api-location'])))
			{
				file_put_contents(ROOT_PATH . weather::$forecast_cache, $xml);
			}
		}
		else
		{
			$xml = file_get_contents(ROOT_PATH . weather::$forecast_cache);
		}

		$xml = new SimpleXMLElement($xml);
		
		foreach($xml->forecast->simpleforecast->forecastdays->forecastday as $val)
		{
			if($val->date->year == date('Y') && $val->date->month == date('n') && $val->date->day == date('j'))
			{
				weather::$today_low = $val->low->fahrenheit;
				weather::$today_high = $val->high->fahrenheit;
				
				return true;
			}
		}
		return false;
	}
}

?>