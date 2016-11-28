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
		$curl = $this->getCurlObject($url, 'GET');
		$response = $curl->curl_exec();
		$info = $curl->curl_getinfo();
		$curl->curl_close();
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
		$curl = $this->getCurlObject($url, 'PUT');
		$curl->curl_setopt(CURLOPT_POSTFIELDS, $data);
		$response = $curl->curl_exec();
		$info = $curl->curl_getinfo();
		$curl->curl_close();
		return new Response($info['http_code'], $response);
	}

	public function post($path, $data) {
		if (!is_string($data)) {
			$data = json_encode($data);
			if (json_last_error() != JSON_ERROR_NONE) {
				throw new ClientException('Could not encode JSON: ' . json_last_error_msg());
			}
		}
		$url = $this->getApiUrl($path);
		$curl = $this->getCurlObject($url, 'POST');
		$curl->curl_setopt(CURLOPT_POST, 1);
		$curl->curl_setopt(CURLOPT_POSTFIELDS, $data);
		$response = $curl->curl_exec();
		$info = $curl->curl_getinfo();
		$curl->curl_close();
		return new Response($info['http_code'], $response);
	}

	public function delete($path) {
		$url = $this->getApiUrl($path);
		$curl = $this->getCurlObject($url, 'DELETE');
		$response = $curl->curl_exec();
		$info = $curl->curl_getinfo();
		$curl->curl_close();
		return new Response($info['http_code'], $response);
	}

	/**
	 * Build full URL and append Apikey
	 *
	 * @param $path
	 * @return string assembled url
	 */
	protected function getApiUrl($path) {
		// remove extra / at the beginning
		$path = ltrim($path, '/');

		// append apikey
		if (strpos($path, '?') === false) {
			$path_with_apikey = $path . '?apikey=' . $this->apikey;
		} else {
			$path_with_apikey = $path . '&apikey=' . $this->apikey;
		}

		// prepend protocol if not passed
		$protocol = '';
		if(strpos($this->domain, 'http://') !== 0 && strpos($this->domain, 'https://') !== 0) {
			$protocol = 'https://';
		}

		// assemble final url
		return $protocol . $this->domain . '/api/' . self::API_VERSION . '/' . $path_with_apikey;
	}

	/**
	 * Get a new curl instance, encapsulated to make it mockable
	 * @param string $url - Request url
	 * @param string $method - HTTP method
	 * @return CurlHttp get a new curl object
	 */
	protected function getCurlObject($url, $method = 'GET') {
		$curl = new CurlHttp();
		$curl->curl_setopt(CURLOPT_URL, $url);
		$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, $method);
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		return $curl;
	}
}