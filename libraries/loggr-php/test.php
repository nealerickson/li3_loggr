<?php

	//--- CONFIG ---//

	$log_key = "myfirstlog";
	$api_key = "db961642e48e48e4ab00ef60c90fa29e";

	//--- END CONFIG ---//

	require_once 'loggr.php';

	// creating class for using fluent syntax
	$loggr = new Loggr($log_key, $api_key);

	// create a simple event
	$loggr->events->create()
		->text("Simple fluent event")
		->post();

	// more complex event
	$world = "World";
	$loggr->events->create()
		->text("hello world")
		->tags(array('tag1', 'tag2', 'tag3'))
		->link("http://google.com")
		->source("dave")
		->data("foobar")
		->value(3)
		->geo(-14.456, 73.6879)
		->post();

	// trace a variable
	$var = "TEST VAR";
	$loggr->events->createFromVariable($var)
		->text("Tracing TEST VAR")
		->post();

	// trace an exception
	try {
		$error = 'Always throw this error';
		throw new Exception($error);
	} catch (Exception $e) {
		$loggr->events->CreateFromException($e)
			->text("Exception")
			->post();
	}

	// alternatively you can use a non-fluent syntax
	$client = Loggr::LogClient($log_key, $api_key);

	// create a simple event
	$ev = Loggr::Event();
	$ev->text = "Simple non-fluent event";
	$client->post($ev);

?>