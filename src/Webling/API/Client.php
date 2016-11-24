<?php

namespace Webling\API;

class Client
{
	protected $domain;

	protected $apikey;

	const API_VERSION = '1';

	/**
	 * @param $domain - url of your webling instance, e.g "demo.webling.ch"
	 * @param $apikey - your api key
	 */
	function __construct($domain, $apikey) {
		$this->domain = $domain;
		$this->apikey = $apikey;
	}

	public function get($path) {
		return new Response(200, "{}");
	}

	public function put($path, $data) {
		return new Response(200, "{}");
	}

	public function post($path, $data) {
		return new Response(200, "{}");
	}

	public function delete($path) {
		return new Response(200, "{}");
	}
}