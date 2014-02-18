<?php

if( !defined('AUDIO_CACHE_PATH'))
{
	define('AUDIO_CACHE_PATH', ROOT_PATH . '/cache/audio');
}
if( !defined('AUDIO_HASH_ALGO'))
{
	define('AUDIO_HASH_ALGO', 'md5');
}

class audio
{
	public static function tts($text, $skip_cache = false)
	{
		global $config;

		$hash = hash(AUDIO_HASH_ALGO, $text);
		$wav = AUDIO_CACHE_PATH . "/{$hash}.wav";
		
		if($skip_cache || ! file_exists($wav))
		{
			$key = exec('pico2wave -w "' . $wav . '" "' . str_replace('"', ' ', $text) . '"');
		}
		
		if((bool)$config['audio-enabled'])
		{
			$key = exec('aplay -q "' . $wav . '"');
		}
		
		return;
	}
	
	public static function google_tts($text, $lang = 'en')
	{
		global $config;

		$gurl = 'http://translate.google.com/translate_tts?tl=%2$s&q=%1$s';
		
		if(strlen($text) > 100)
		{
			trigger_error('$text must be 100 characters or less.', E_USER_WARNING);
			return false;
		}
		
		$hash	= hash(AUDIO_HASH_ALGO, $text);
		$mp3	= AUDIO_CACHE_PATH . "/{$hash}.mp3";
		
		if(! file_exists($mp3))
		{
			if($key = file_get_contents(sprintf($gurl, urlencode($text), $lang)))
			{
				file_put_contents($mp3, $key);
				unset($key);
			}
		}
		
		if((bool)$config['audio-enabled'])
		{
			$key = exec('mpg123 -q "' . $mp3 . '"');
		}
		
		return;
	}
}

?>