<?php

namespace Webling\API;

class ClientMock extends Client
{
	const MOCK_ENABLED = true;

	protected function getCurlObject($url, $method = 'GET') {
		if (self::MOCK_ENABLED) {
			$curl = new CurlHttpMock();
			$curl->curl_setopt(CURLOPT_URL, $url);
			$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, $method);
			$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
			$curl->curl_setopt(CURLOPT_HTTPHEADER, ['apikey:' . '6781b18c2616772de74043ed0c32f76f'] );
			$this->applyOptionsToCurl($curl);
			return $curl;
		} else {
			$curl = new CurlHttp();
			$curl->curl_setopt(CURLOPT_URL, $url);
			$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, $method);
			$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
			$curl->curl_setopt(CURLOPT_HTTPHEADER, ['apikey:' . '6781b18c2616772de74043ed0c32f76f'] );
			$this->applyOptionsToCurl($curl);
			return $curl;
		}
	}
}
