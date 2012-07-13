<?php

class Loggr_FluentEvent {
	public $event;

	protected $logKey;
	protected $apiKey;

	public function __construct($logKey, $apiKey) {
		$this->logKey = $logKey;
		$this->apiKey = $apiKey;
		$this->event = new Loggr_Event();
	}

	/**
	 * post, post the event to Loggr
	 *
	 * Requires text to have been set otherwise does not post
	 */
	public function &post() {
		if (empty($this->event->text)) {
			if($this->suppress_errors) {
				return $this;
			}

			throw new Exception('Loggr error, cannot post without setting event text');
		}

		$client = new Loggr_LogClient($this->logKey, $this->apiKey);
		$client->post($this->event);
		return $this;
	}

	/**
	 * text, set text of event
	 *
	 * @param string text of the event
	 */
	public function &text($text) {
		if(func_num_args() > 1) {
			$args = func_get_args();
			$text = vsprintf(array_shift($args), $args);
		}

		$this->event->text = $this->assignWithMacro(trim($text), $this->event->text);
		return $this;
	}

	/**
	 * text with formating, set text of event with formating
	 *
	 * @deprecated use text
	 */
	public function &textF() {
		call_user_func_array(array($this, 'text'), func_get_args());
	    return $this;
	}

	/**
	 * add text, append text of event with or without formating
	 */
	public function &addText($text) {
		if (func_num_args() > 1) {
			$args = func_get_args();
			$text = vsprintf(array_shift($args), $args);
		}

		$this->event->text .= $this->assignWithMacro(trim($text), $this->event->text);
		return $this;
	}

	/**
	 * add text with formating, append text and use formating
	 *
	 * @deprecated use addText
	 */
	public function &addTextF() {
		call_user_func_array(array($this, 'text'), func_get_args());
	    return $this;
	}

	/**
	 * source, set the source of event
	 */
	public function &source($source) {
		if(func_num_args() > 1) {
			$args = func_get_args();
			$source = vsprintf(array_shift($args), $args);
		}

		$this->event->source = $this->assignWithMacro(trim($source), $this->event->source);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function &sourceF() {
		call_user_func_array(array($this, 'source'), func_get_args());
	    return $this;
	}

	/**
	 * user, set user of event with or without formating
	 */
	public function &user($user) {
		if(func_num_args() > 1) {
			$args = func_get_args();
			$user = vsprintf(array_shift($args), $args);
		}

		$this->event->user = $this->assignWithMacro(trim($user), $this->event->user);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function &userF() {
		call_user_func_array(array($this, 'user'), func_get_args());
		return $this;
	}

	/**
	 * link, set link of event with or without formating
	 */
	public function &link($link) {
		if(func_num_args() > 1) {
			$args = func_get_args();
			$link = vsprintf(array_shift($args), $args);
		}

		$this->event->link = $this->assignWithMacro(trim($link), $this->event->link);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function &linkF() {
		call_user_func_array(array($this, 'link'), func_get_args());
	    return $this;
	}

	/**
	 * data, set data of event with or without formatting
	 */
	public function &data($data) {
		if (func_num_args() > 1) {
			$args = func_get_args();
			$data = vsprintf(array_shift($args), $args);
		}

		$this->event->data = $this->assignWithMacro(trim($data), $this->event->data);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function &dataF() {
		call_user_func_array(array($this, 'data'), func_get_args());
	    return $this;
	}

	/**
	 * add data, append data of event with or without formating
	 */
	public function &addData($data) {
		if (func_num_args() > 1) {
			$args = func_get_args();
			$data = vsprintf(array_shift($args), $args);
		}

		$this->event->data .= $this->assignWithMacro(trim($data), $this->event->data);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function &addDataF() {
		call_user_func_array(array($this, 'data'), func_get_args());
	    return $this;
	}

	/**
	 * value, set value of event
	 */
	public function &value($value) {
		$this->event->value = (float) $value;
		return $this;
	}

	/**
	 * add value, add to value of event
	 */
	public function &addValue($value) {
		$this->event->value += (float) $value;
	}

	/**
	 * sub value, subtract from value of event
	 */
	public function &subValue($value) {
		$this->event->value -= (float) $value;
	}

	/**
	 * value, clear value
	 */
	public function &valueClear() {
		$this->event->value = '';
		return $this;
	}

	/**
	 * tags, set tags of event
	 */
	public function &tags($tags) {
		if (is_array($tags)) {
			$tags = join(' ', $tags);
		}

		$this->event->tags = trim($tags);
		return $this;
	}

	/**
	 * add tags, add to tags of event
	 */
	public function &addTags($tags) {
		$this->event->tags .= " " . trim($tags);
		return $this;
	}

	/**
	 * geo, set geography (by lattitude and longitude) of event
	 */
	public function &geo($lat, $lon) {
		$this->event->geo = trim($lat) . ',' . trim($lon);
		return $this;
	}

	/**
	 * geo, set geography (by ip address) of event
	 */
	public function &geoIp($ip = '') {
		$ip = trim($ip);
		$ip = !empty($ip) && preg_match('/^\d+\.\d+\.\d+\.\d+$/', $ip) ? $ip : $_SERVER['REMOTE_ADDR'];
		$this->event->geo = 'ip:' . $ip;
		return $this;
	}

	/**
	 * data type, set data type for data of event
	 */
	public function &dataType($datatype) {
		$this->event->dataType = strcasecmp(trim((string) $datatype), 'html') === 0 ? Loggr_DataType::html : Loggr_DataType::plaintext;
		return $this;
	}

	private function assignWithMacro($input, $baseStr) {
		return str_replace("$$", $baseStr, $input);
	}

	/**
	 * _call, magic method
	 *
	 * Used for backwards compatibility
	 **/
	public function __call($method, $params) {
		$method = lcfirst($method);
		if (method_exists($this, $method)) {
			return call_user_func_array(array($this, $method), $params);
		}
	}

	public function __get($attribute) {
		$attribute = lcfirst($attribute);
		if (isset($this->{$attribute})) {
			return $this->{$attribute};
		}
	}

	public function __set($attribute, $value) {
		$attribute = lcfirst($attribute);
		if (isset($this->{$attribute})) {
			$this->{$attribute} = $value;
		}
	}
}

?>