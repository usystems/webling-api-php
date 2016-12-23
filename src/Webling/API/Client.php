<?php

namespace Webling\API;

class Client implements IClient
{
	protected $domain;

	protected $apikey;

	/**
	 * @var int The default number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
	 */
	protected $CURLOPT_CONNECTTIMEOUT = 4;

	/**
	 * @var int The default maximum number of seconds to allow cURL functions to execute.
	 */
	protected $CURLOPT_TIMEOUT = 30;

	/**
	 * @var string The default HTTP user-agent header
	 */
	protected $CURLOPT_USERAGENT = 'Webling-API-PHP/1.1';

	const API_VERSION = '1';

	/**
	 * Client constructor.
	 * @param string $domain - your webling address, e.g: "demo.webling.ch"
	 * @param string $apikey - your API-Key
	 * @param array $options - array of optional options
	 *          'connecttimeout' => int The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
	 *          'timeout' => int The maximum number of seconds to allow cURL functions to execute.
	 *          'useragent' => string The HTTP user-agent header.
	 *
	 * @throws ClientException
	 */
	function __construct($domain, $apikey, $options = []) {
		$this->domain = $domain;
		$this->apikey = $apikey;
		if (!$domain) {
			throw new ClientException('Invalid domain');
		}
		if (!$apikey || strlen($apikey) != 32) {
			throw new ClientException('Invalid apikey, the apikey must be 32 chars');
		}

		$this->setOptions($options);
	}

	/**
	 * @param array $options - for details see the constructors $options param
	 * @see __construct
	 */
	public function setOptions($options) {
		if (is_array($options)) {
			if (isset($options['connecttimeout'])) {
				$this->CURLOPT_CONNECTTIMEOUT = $options['connecttimeout'];
			}
			if (isset($options['timeout'])) {
				$this->CURLOPT_TIMEOUT = $options['timeout'];
			}
			if (isset($options['useragent'])) {
				$this->CURLOPT_USERAGENT = $options['useragent'];
			}
		}
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
				if (function_exists('json_last_error_msg')) {
					// json_last_error_msg() only exists in PHP >= 5.5
					throw new ClientException('Could not encode JSON: ' . json_last_error_msg());
				} else {
					// PHP < 5.5
					throw new ClientException('Could not encode JSON');
				}
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
				if (function_exists('json_last_error_msg')) {
					// json_last_error_msg() only exists in PHP >= 5.5
					throw new ClientException('Could not encode JSON: ' . json_last_error_msg());
				} else {
					// PHP < 5.5
					throw new ClientException('Could not encode JSON');
				}
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

	protected function applyOptionsToCurl(CurlHttp $curl) {
		if ($this->CURLOPT_CONNECTTIMEOUT != null) {
			$curl->curl_setopt(CURLOPT_CONNECTTIMEOUT, $this->CURLOPT_CONNECTTIMEOUT);
		}
		if ($this->CURLOPT_TIMEOUT != null) {
			$curl->curl_setopt(CURLOPT_TIMEOUT, $this->CURLOPT_TIMEOUT);
		}
		if ($this->CURLOPT_USERAGENT != null) {
			$curl->curl_setopt(CURLOPT_USERAGENT, $this->CURLOPT_USERAGENT);
		}
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
		$this->applyOptionsToCurl($curl);
		return $curl;
	}
}