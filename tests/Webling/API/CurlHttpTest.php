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
		$curl->curl_setopt(CURLOPT_URL, 'https://www.google.com');
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
		$curl->curl_setopt(CURLOPT_URL, 'https://any-non-existing-domain.tv');
		$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		$reponse = $curl->curl_exec();
	}

	/**
	 * @expectedException Webling\API\ClientException
	 * @expectedExceptionMessage Could not connect to: GET https://any-non-existing-domain.tv?apikey=__removed__ Error: Couldn't resolve host 'any-non-existing-domain.tv'
	 */
	public function testReplaceApikeyInExceptionMessage()
	{
		$curl = new CurlHttp();
		$curl->curl_setopt(CURLOPT_URL, 'https://any-non-existing-domain.tv?apikey=thisShouldBeReplaced123');
		$curl->curl_setopt(CURLOPT_CUSTOMREQUEST, 'GET');
		$curl->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		$reponse = $curl->curl_exec();
	}

}
