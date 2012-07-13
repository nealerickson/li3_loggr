<?php

namespace li3_loggr\util;

use Loggr as LoggrAgent;

class Loggr {
	
	public static function init($logKey = null, $apiKey = null) {
		
		if (!$logKey && defined('LOGGR_LOG_KEY')) {
			$logKey = LOGGR_LOG_KEY;
		}
		if (!$apiKey && defined('LOGGR_API_KEY')) {
			$apiKey = LOGGR_API_KEY;
		}
		if (!$logKey) {
			trigger_error('Loggr log key is not set', E_USER_ERROR);
		}
		if (!$apiKey) {
			trigger_error('Loggr API key is not set', E_USER_ERROR);
		}
		
		return new LoggrAgent($logKey, $apiKey);
	}
}

?>