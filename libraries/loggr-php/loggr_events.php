<?php

class Loggr_Events {
	private $_logKey;
	private $_apiKey;

	public function __construct($logKey, $apiKey) {
		$this->_logKey = $logKey;
		$this->_apiKey = $apiKey;
	}

	public function create() {
		return new Loggr_FluentEvent($this->_logKey, $this->_apiKey);
	}

	public function createFromException($exception) {
		ob_start();
		var_dump($exception->getTrace(), 5);
		$stack = str_replace("\t", "----", nl2br(ob_get_clean()));

		$data = "<b>MESSAGE:</b> " . $exception->getMessage() . "<br>";
		$data .= "<b>FILE:</b> " . $exception->getFile() . ", " . $exception->getLine() . "<br>";
		$data .= "<b>CODE:</b> " . get_class($exception) . "<br>";
		$data .= "<br><b>BACK TRACE:</b> " . $this->backtrace();

		return $this->create()
			->text($exception->getMessage())
			->tags("error " . get_class($exception))
			->data($data)
			->dataType(Loggr_DataType::html);
	}

	public function createFromVariable($var) {
		ob_start();
		var_dump($var);
		$trace = str_replace("\t", "----", nl2br(ob_get_clean()));

		$data = "<pre>" . $trace . "</pre>";

		return $this->create()
			->data($data)
			->dataType(Loggr_DataType::html);
	}

	protected function backtrace() {
		$output = "<div style='text-align: left; font-family: monospace;'>\n";
		$backtrace = debug_backtrace();

		foreach ($backtrace as $bt) {
			$args = '';
			foreach ($bt['args'] as $a) {
				if (!empty($args)) {
					$args .= ', ';
				}
				switch (gettype($a)) {
					case 'integer':
					case 'double':
						$args .= $a;
					break;
					case 'string':
						$a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
						$args .= "\"$a\"";
					break;
					case 'array':
						$args .= 'Array('.count($a).')';
					break;
					case 'object':
						$args .= 'Object('.get_class($a).')';
					break;
					case 'resource':
						$args .= 'Resource('.strstr($a, '#').')';
					break;
					case 'boolean':
						$args .= $a ? 'True' : 'False';
					break;
					case 'NULL':
						$args .= 'Null';
					break;
					default:
						$args .= 'Unknown';
				}
			}
			$output .= "<br />\n";
			$output .= "<b>file:</b> {$bt['line']} - {$bt['file']}<br />\n";
			$output .= "<b>call:</b> ".(isset($bt['class'])?$bt['class']:'').(isset($bt['type'])?$bt['type']:'').(isset($bt['function'])?$bt['function']:'')."($args)<br />\n";
		}
		$output .= "</div>\n";
		return $output;
	}

	public function __call($method, $params) {
		$method = lcfirst($method);
		if(method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}
	}
}

?>