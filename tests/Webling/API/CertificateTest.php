<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/Mocks/ClientMock.php';
require_once __DIR__ . '/Mocks/CurlHttpMock.php';

use PHPUnit\Framework\TestCase;
use Webling\API\ClientMock;

class CertificateTest extends TestCase
{

	public function testSelfSignedCertificateError()
	{
		$this->expectException(Webling\API\ClientException::class);
		// self signed certificates should fail
		// this is just a random site with a self signed certificate
		$client = new ClientMock("https://www.pcwebshop.co.uk", "12345678901234567890123456789012");
		$client->get('/');
	}

	public function testSelfSignedCertificateErrorHTTPFallback()
	{
		// test http fallback for hosts with self signed cerificates
		$client = new ClientMock("http://www.pcwebshop.co.uk", "12345678901234567890123456789012");
		$response = $client->get('/');
		$this->assertEquals(301, $response->getStatusCode());
	}

}
