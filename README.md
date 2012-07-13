# `Loggr` plugin for Lithium with log adapter integration

The Loggr (li3_loggr) plugin allows you to easily integrate your Lithium applications with Loggr (http://loggr.net).

## Setup

Add the plugin to `app/config/bootstrap/libraries.php`.

	Libraries::add('li3_loggr', array(
        'api_key' => '__ADD_YOUR_API_KEY_HERE__',
        'log_key' => '__ADD_YOUR_LOG_KEY_HERE__'
    ));

Get your LOG_KEY and API_KEY from your Loggr log settings

## Basic Usage

	use li3_loggr\util\Loggr;

	// Log an exception
	try {
		throw new \Exception('Simple exception');
	} catch (\Exception $e) {
		Loggr::init()->events->createFromException($e)
			->text('Oops! Exception thrown in application')
			->post();
	}

	// Log custom event
	Loggr::init()->events->create()
		->text("An order has been placed")
		->tags("tag1 tag2 tag3")
		->link("http://example.com")
		->source("neal")
		->data("foobar")
		->value(3)
		->geoIp()
		->post();


## Log Adapter Usage

Add the following code in one of your bootstrap files.

	use lithium\analysis\Logger;

	// Set up the logger configuration to use the file adapter and Loggr.
	Logger::config(array(
		'default' => array(
			'adapter' => 'File'
		),
		'loggr' => array(
			'adapter' => 'Loggr'
		)
	));

Log events

	use lithium\analysis\Logger;

	// Log an exception
	try {
		throw new \Exception('Simple exception');
	} catch (\Exception $e) {
		Logger::error("Exception caught during order processing: {$e->getMessage()}", array(
			'loggr' => array(
				'exception' => $e,
				'tags' => 'order'
			)
		));
	}

	// Log custom event
	Logger::error("Sample error message", array(
		'loggr' => array(
			'tags' => 'error testing',
			'value' => 100,
			'data' => String::insert('<b>Hello</b>, {:world}', array('world' => 'World!')),
			'user' => 'neal',
			'source' => 'web',
			'geoIp' => true,
			'dataType' => 'html'
		)
	));

