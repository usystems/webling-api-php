<?php

namespace Webling\API;


/**
 * CurlHttp is a wrapper for the curl functions to make the curl functions mockable
 */
class CurlHttpMock extends CurlHttp
{
	protected $options = array(
		CURLOPT_CUSTOMREQUEST => 'GET'
	);

	protected $responses = array(
		'GET' => array(
			'https://demo.webling.dev/api/1//member?apikey=6781b18c2616772de74043ed0c32f76f' => array(
				200,
				'{"objects": [469,470,471]}'
			),
			'https://demo.webling.dev/api/1//member?sort=ID&apikey=6781b18c2616772de74043ed0c32f76f' => array(
				200,
				'{"objects": [469,470,471]}'
			),
			'https://random.nonexisting.url.wbl/api/1//member?apikey=6781b18c2616772de74043ed0c32f76f' => array(
				0,
				''
			)

		)
	);

	/** @noinspection PhpMissingParentConstructorInspection */
	function __construct() {}

	public function curl_setopt($option, $value) {
		$this->options[$option] = $value;
	}

	public function curl_exec() {
		$method = $this->options[CURLOPT_CUSTOMREQUEST];
		$url = $this->options[CURLOPT_URL];
		if (isset($this->responses[$method][$url])) {
			return $this->responses[$method][$url][1];
		}
		throw new \Exception('Could not find mock response for: '. $method .' ' . $url);
	}

	public function curl_getinfo() {
		$method = $this->options[CURLOPT_CUSTOMREQUEST];
		$url = $this->options[CURLOPT_URL];
		if (isset($this->responses[$method][$url])) {
			return array(
				'http_code' => $this->responses[$method][$url][0]
			);
		}
		throw new \Exception('Could not find mock response for: '. $method .' ' . $url);
	}

	public function curl_close() {}
}