<?php

namespace Webling\API;


/**
 * CurlHttp is a wrapper for the curl functions to make the curl functions mockable
 */
class CurlHttp
{
	protected $curl;

	function __construct() {
		$this->curl = curl_init();
	}

	public function curl_setopt($option, $value) {
		return curl_setopt($this->curl, $option, $value);
	}

	public function curl_exec() {
		return curl_exec($this->curl);
	}

	public function curl_getinfo() {
		return curl_getinfo($this->curl);
	}

	public function curl_close() {
		return curl_close($this->curl);
	}
}