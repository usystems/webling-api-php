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
		$curl = $this->getCurlObject();
		$curl->curl_setopt(CURLOPT_URL, $url);
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		$curl->curl_setopt(CURLOPT_SSL_VERIFYPEER, false);
		$response = $curl->curl_exec();
		$info = $curl->curl_getinfo();
		$curl->curl_close();

		if (!isset($info['http_code']) or empty($info['http_code']) or $info['http_code'] === 0) {
			throw new ClientException('Could not connect to: ' . $url);
		}
		return new Response($info['http_code'], $response);
	}

	public function put($path, $data) {
		if (!is_string($data)) {
			$data = json_encode($data);
			if (json_last_error() != JSON_ERROR_NONE) {
				throw new ClientException('Could not encode JSON: ' . json_last_error_msg());
			}
		}
		$url = $this->getApiUrl($path);
		$curl = $this->getCurlObject();
		$curl->curl_setopt(CURLOPT_URL, $url);
		$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, "PUT");
		$curl->curl_setopt(CURLOPT_POSTFIELDS, $data);
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		$curl->curl_setopt(CURLOPT_SSL_VERIFYPEER, false);
		$response = $curl->curl_exec();
		$info = $curl->curl_getinfo();
		$curl->curl_close();

		if (!isset($info['http_code']) or empty($info['http_code']) or $info['http_code'] === 0) {
			throw new ClientException('Could not connect to: ' . $url);
		}
		return new Response($info['http_code'], $response);
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
	protected function getApiUrl($path) {
		if (strpos($path, '?') === false) {
			$path_with_apikey = $path . '?apikey=' . $this->apikey;
		} else {
			$path_with_apikey = $path . '&apikey=' . $this->apikey;
		}
		return 'https://' . $this->domain . '/api/' . self::API_VERSION . '/' . $path_with_apikey;
	}

	/**
	 * Get a new curl instance, encapsulated to make it mockable
	 * @return CurlHttp get a new curl object
	 * @codeCoverageIgnore
	 */
	protected function getCurlObject() {
		return new CurlHttp();
	}
}