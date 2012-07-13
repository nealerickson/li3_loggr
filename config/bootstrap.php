<?php

use lithium\core\Libraries;

Libraries::add('loggr', array(
	'path' => dirname(__DIR__) . '/libraries/loggr-php',
	'prefix' => false,
	'bootstrap'=> false,
	'transform' => function($class, $config) {
		$map = array(
			'Loggr'				=> 'loggr',
			'Loggr_DataType'    => 'loggr_data_type',
			'Loggr_Event'		=> 'loggr_event',
			'Loggr_Events'      => 'loggr_events',
			'Loggr_FluentEvent' => 'loggr_fluent_event',
			'Loggr_LogClient'   => 'loggr_log_client'
		);
		if (!isset($map[$class])) {
			return false;
		}

		return "{$config['path']}/{$map[$class]}{$config['suffix']}";
	}
));

$config['api_key'] = isset($config['api_key']) ? $config['api_key'] : null;
$config['log_key'] = isset($config['log_key']) ? $config['log_key'] : null;

define('LOGGR_API_KEY', $config['api_key']);
define('LOGGR_LOG_KEY', $config['log_key']);

?>