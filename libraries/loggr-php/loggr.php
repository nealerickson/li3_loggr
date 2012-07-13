<?php

require_once('loggr_data_type.php');
require_once('loggr_event.php');
require_once('loggr_log_client.php');
require_once('loggr_fluent_event.php');
require_once('loggr_events.php');

class Loggr {
	public $events;

	public static function &LogClient($logKey, $apiKey) {
		$client = new Loggr_LogClient($logKey, $apiKey);
		return $client;
	}

	public static function &Event() {
		$event = new Loggr_Event();
		return $event;
	}

	public function __construct($logKey, $apiKey) {
		$this->events = new Loggr_Events($logKey, $apiKey);
	}

	public function trapExceptions() {
		set_error_handler(array($this, "errorHandler"));
		set_exception_handler(array($this, "exceptionHandler"));
	}

	public function errorHandler($code, $message, $file, $line) {
		if ($code == E_STRICT && $this->reportESTRICT === false) {
			return;
		}

		ob_start();
		var_dump(debug_backtrace());
		$stack = nl2br(ob_get_clean());

		$data  = "@html\r\n";
		$data .= "<b>MESSAGE:</b> " . $message . "<br>";
		$data .= "<b>FILE:</b> " . $file . ", " . $line . "<br>";
		$data .= "<b>CODE:</b> " . $code . "<br>";
		$data .= "<br><b>STACK TRACE:</b> " . $stack;

		$this->events->create()
			->text($message)
			->tags("error")
			->data($data)
			->post();
	}

	public function exceptionHandler($exception) {
		ob_start();
		var_dump($exception->getTrace());
		$stack = str_replace("\n", "<br>", ob_get_clean());

		$data = "@html\r\n";
		$data .= "<b>MESSAGE:</b> " . $exception->getMessage() . "<br>";
		$data .= "<b>FILE:</b> " . $exception->getFile() . ", " . $exception->getLine() . "<br>";
		$data .= "<b>CODE:</b> " . get_class($exception) . "<br>";
		$data .= "<br><b>STACK TRACE:</b> " . $stack;

		$this->events->create()
			->text($message)
			->tags("error exception")
			->data($data)
			->post();
	}

	/**
	 * __get, magic function
	 *
	 * For backwards compatibility
	 */
	public function __get($attribute) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			return $this->{$attribute};
		}
	}

	/**
	 * __set, magic function
	 *
	 * For backwards compatibility
	 */
	public function __set($attribute, $value) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			$this->{$attribute} = $value;
		}
	}
}

?>