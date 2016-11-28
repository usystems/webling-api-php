<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/Mocks/ClientMock.php';
require_once __DIR__ . '/Mocks/CurlHttpMock.php';

use Webling\API\CurlHttp;

class CurlHttpTest extends PHPUnit_Framework_TestCase
{

	public function testCurlHttp()
	{
		$curl = new CurlHttp();
		$curl->curl_setopt(CURLOPT_URL, 'http://localhost');
		$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		$reponse = $curl->curl_exec();
		$error = $curl->curl_error();
		$info = $curl->curl_getinfo();
		$curl->curl_close();
	}

	/**
	 * @expectedException Webling\API\ClientException
	 */
	public function testCurlHttps()
	{
		$curl = new CurlHttp();
		$curl->curl_setopt(CURLOPT_URL, 'https://localhost');
		$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		$reponse = $curl->curl_exec();
	}

}
