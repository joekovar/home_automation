<?php

class weather
{
	public static $forecast_url = 'http://api.wunderground.com/api/%1$s/forecast/q/%2$s.xml';
	public static $forecast_cache = '/var/www/cache/weather/forecast.xml';

	public static $hourly_url = 'http://api.wunderground.com/api/%1$s/hourly/q/%2$s.xml';
	public static $hourly_cache = '/var/www/cache/weather/hourly.xml';
	
	public static $today_low = false;

	public static function today_low()
	{
		if(weather::$today_low !== false)
		{
			return weather::$today_low;
		}
		
		global $config;

		if( ! filemtime(weather::$forecast_cache) || (time() - filemtime(weather::$forecast_cache) >= 86400))
		{
			if($xml = file_get_contents(sprintf(weather::$forecast_url, $config['wunderground-api-key'], $config['wunderground-api-location'])))
			{
				file_put_contents(weather::$forecast_cache, $xml);
			}
		}
		else
		{
			$xml = file_get_contents(weather::$forecast_cache);
		}

		$xml = new SimpleXMLElement($xml);
		
		foreach($xml->forecast->simpleforecast->forecastdays->forecastday as $val)
		{
			if($val->date->year == date('Y') && $val->date->month == date('n') && $val->date->day == date('j'))
			{
				weather::$today_low = $val->low->fahrenheit;
				break;
			}
		}
		
		return weather::$today_low;
	}
}

?>