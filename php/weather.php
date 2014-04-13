<?php

class weather
{
	public static $forecast_url = 'http://api.wunderground.com/api/%1$s/forecast/q/%2$s.xml';
	public static $forecast_cache = '/cache/weather/forecast.xml';
	public static $forecast_xml = false;

	public static $hourly_url = 'http://api.wunderground.com/api/%1$s/hourly/q/%2$s.xml';
	public static $hourly_cache = '/cache/weather/hourly.xml';
	public static $hourly_xml = false;

	public static $today_low = false;
	public static $today_high = false;
	
	public static function say_forecast()
	{
		if(weather::get_forecast())
		{
			$fcttext = (string) weather::$forecast_xml->forecast->txt_forecast->forecastdays->forecastday->fcttext;
			$fcttext = preg_replace('#(\d+)F#', '$1', $fcttext);

			audio::google_tts($fcttext);
			return true;
		}
		return false;
	}

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
	
	protected static function get_forecast($skip_cache = false)
	{
		if(weather::$forecast_xml !== false && $skip_cache === false)
		{
			return true;
		}
		global $config;
		
		if($skip_cache || (! @filemtime(ROOT_PATH . weather::$forecast_cache)) || (date('Ymd', @filemtime(ROOT_PATH . weather::$forecast_cache)) < date('Ymd')))
		{
			if($xml = file_get_contents(sprintf(weather::$forecast_url, $config['wunderground-api-key'], $config['wunderground-api-location'])))
			{
				file_put_contents(ROOT_PATH . weather::$forecast_cache, $xml);
			}
			else
			{
				return false;
			}
		}
		else
		{
			$xml = file_get_contents(ROOT_PATH . weather::$forecast_cache);
		}
		
		weather::$forecast_xml = new SimpleXMLElement($xml);
		return true;
	}
	
	protected static function highlow()
	{
		if(weather::get_forecast() === false)
		{
			return false;
		}
		
		foreach(weather::$forecast_xml->forecast->simpleforecast->forecastdays->forecastday as $val)
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