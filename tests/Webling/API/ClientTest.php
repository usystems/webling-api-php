<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/Mocks/ClientMock.php';
require_once __DIR__ . '/Mocks/CurlHttpMock.php';

use Webling\API\ClientMock;

class ClientTest extends PHPUnit_Framework_TestCase
{

	public function testGet()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->get('/member');
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('objects', $response->getData());

		$response = $client->get('/member?sort=ID');
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('objects', $response->getData());
	}

	/**
	 * @expectedException Webling\API\ClientException
	 */
	public function testGetException()
	{
		$client = new ClientMock("random.nonexisting.url.wbl", "6781b18c2616772de74043ed0c32f76f");
		$client->get('/member');
	}

	public function testPut()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->put('/member/123', "{}");
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testPost()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->post('/member', "{}");
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testDelete()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->delete('/member/123');
		$this->assertEquals(200, $response->getStatusCode());
	}

}
