<?php

namespace Webling\API;

class Client implements IClient
{
	protected $domain;

	protected $apikey;

	const API_VERSION = '1';

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