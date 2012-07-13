<?php

class Loggr_Event {
	public $text;
	public $source;
	public $user;
	public $link;
	public $data;
	public $value;
	public $tags;
	public $geo;
	public $dataType = Loggr_DataType::plaintext;

	public function __get($attribute) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			return $this->{$attribute};
		}
	}

	public function __set($attribute, $value) {
		$attribute = lcfirst($attribute);
		if(isset($this->{$attribute})) {
			$this->{$attribute} = $value;
		}
	}
}

?>