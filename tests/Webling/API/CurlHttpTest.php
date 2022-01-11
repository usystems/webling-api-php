<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/Mocks/ClientMock.php';
require_once __DIR__ . '/Mocks/CurlHttpMock.php';

use PHPUnit\Framework\TestCase;
use Webling\API\CurlHttp;

class CurlHttpTest extends TestCase
{

	public function testCurlHttp()
	{
		$curl = new CurlHttp();
		$curl->curl_setopt(CURLOPT_URL, 'https://www.google.com');
		$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		$reponse = $curl->curl_exec();
		$error = $curl->curl_error();
		$info = $curl->curl_getinfo();
		$this->assertGreaterThanOrEqual(200, $info['http_code']);
		$curl->curl_close();
	}

	public function testCurlHttps()
	{
		$this->expectExceptionMessage("Could not connect to: GET https://any-non-existing-domain.tv");
		$this->expectException(Webling\API\ClientException::class);
		$curl = new CurlHttp();
		$curl->curl_setopt(CURLOPT_URL, 'https://any-non-existing-domain.tv');
		$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		$reponse = $curl->curl_exec();
	}

}
