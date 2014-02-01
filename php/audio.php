<?php

if( !defined('AUDIO_CACHE_PATH'))
{
	define('AUDIO_CACHE_PATH', '/var/www/cache/audio');
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
}

?>