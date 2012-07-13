<?php

class Loggr_LogClient {
	protected $host = 'post.loggr.net';
	protected $path = '/1/logs/{log_key}/events';

	protected $logKey;
	protected $apiKey;

	public function __construct($logKey, $apiKey) {
		$this->logKey = $logKey;
		$this->apiKey = $apiKey;
	}

	public function post($event) {
		// format data
		$data = $this->createQuerystring($event);

		$out = 'POST ' . str_replace('{log_key}', $this->logKey, $this->path) . ' HTTP/1.1' . "\r\n";
		$out.= 'Host: ' . $this->host . "\r\n";
		$out.= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
		$out.= 'Content-Length: ' . strlen($data) . "\r\n";
		$out.= 'Connection: Close' . "\r\n\r\n";
		$out.= isset($data) ? $data : '';

		// write without waiting for a response
		$fp = fsockopen($this->host, 80, $errno, $errstr, 30);
		fwrite($fp, $out);
		fclose($fp);
	}

	public function createQuerystring($event) {
		$res  =                             'apikey='  . $this->apiKey;
		$res .=                             '&text='   . urlencode($event->text);
		$res .= isset($event->source)    ?  '&source=' . urlencode($event->source) : '';
		$res .= isset($event->user)      ?  '&user='   . urlencode($event->user)   : '';
		$res .= isset($event->link)      ?  '&link='   . urlencode($event->link)   : '';
		$res .= isset($event->value)     ?  '&value='  . urlencode($event->value)  : '';
		$res .= isset($event->tags)      ?  '&tags='   . urlencode($event->tags)   : '';
		$res .= isset($event->geo)       ?  '&geo='    . urlencode($event->geo)    : '';

		if (isset($event->data)) {
			if ($event->dataType == Loggr_DataType::html) {
				$res .= '&data=@html' . "\r\n" . urlencode($event->data);
			} else {
				$res .= '&data=' . urlencode($event->data);
			}
		}

		return $res;
	}
}