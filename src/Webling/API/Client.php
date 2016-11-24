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
		$url = $this->getApiUrl($path);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		curl_close($curl);

		if (!isset($info['http_code']) or empty($info['http_code'])) {
			throw new ClientException('Could not connect to: ' . $url);
		}
		return new Response($info['http_code'], $response);
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

	/**
	 * Build full URL and append Apikey
	 *
	 * @param $path
	 * @return string assembled url
	 */
	private function getApiUrl($path) {
		if (strpos($path, '?') === false) {
			$path_with_apikey = $path . '?apikey=' . $this->apikey;
		} else {
			$path_with_apikey = $path . '&apikey=' . $this->apikey;
		}
		return 'https://' . $this->domain . '/api/' . self::API_VERSION . '/' . $path_with_apikey;
	}
}