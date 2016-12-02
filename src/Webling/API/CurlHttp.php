<?php

namespace Webling\API;


/**
 * CurlHttp is a wrapper for the curl functions to make the curl functions mockable
 */
class CurlHttp
{
	protected $curl;

	protected $options = array();

	function __construct() {
		$this->curl = curl_init();
	}

	public function curl_setopt($option, $value) {
		$this->options[$option] = $value;
		return curl_setopt($this->curl, $option, $value);
	}

	public function curl_exec() {
		$response = curl_exec($this->curl);
		$info = $this->curl_getinfo();
		$error = $this->curl_error();
		if (strlen($error) > 0 or empty($info['http_code'])) {
			throw new ClientException('Could not connect to: '
				. (isset($this->options[CURLOPT_CUSTOMREQUEST]) ? $this->options[CURLOPT_CUSTOMREQUEST] : '') . ' '
				. (isset($this->options[CURLOPT_URL]) ? $this->options[CURLOPT_URL] : '')
				. ' Error: '. $error);
		}
		return $response;
	}

	public function curl_getinfo() {
		return curl_getinfo($this->curl);
	}

	public function curl_close() {
		return curl_close($this->curl);
	}

	public function curl_error() {
		return curl_error($this->curl);
	}
}