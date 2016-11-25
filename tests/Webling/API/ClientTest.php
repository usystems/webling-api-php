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

		// test with a param
		$response = $client->get('/member?sort=ID');
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('objects', $response->getData());
	}

	public function testGetWithProtocol()
	{
		$client = new ClientMock("http://demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->get('/member');
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('objects', $response->getData());
	}

	/**
	 * @expectedException Webling\API\ClientException
	 */
	public function testGetInvalidDomain()
	{
		$client = new ClientMock("random.nonexisting.url.wbl", "6781b18c2616772de74043ed0c32f76f");
		$client->get('/member');
	}

	public function testPut()
	{
		// test with an array data argument
		$data = [
			'properties' => [
				'Vorname' => 'Maria'
			]
		];
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->put('/member/477', $data);
		$this->assertEquals(204, $response->getStatusCode());

		// test with a string data argument
		$data = '{"properties": {"Vorname": "Maria"}}';
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->put('/member/477', $data);
		$this->assertEquals(204, $response->getStatusCode());
	}

	/**
	 * @expectedException Webling\API\ClientException
	 */
	public function testPutInvalidDomain()
	{
		$client = new ClientMock("random.nonexisting.url.wbl", "6781b18c2616772de74043ed0c32f76f");
		$client->put('/member/477', []);
	}

	public function testPost()
	{
		// test with an array data argument
		$data = [
			'properties' => [
				'Vorname' => 'Maria'
			],
			'parents' => [550]
		];
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->post('/member', $data);
		$this->assertEquals(201, $response->getStatusCode());

		// test with a string data argument
		$data = '{"properties": {"Vorname": "Peter"}, "parents": [550]}';
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->post('/member', $data);
		$this->assertEquals(201, $response->getStatusCode());
	}

	/**
	 * @expectedException Webling\API\ClientException
	 */
	public function testPostInvalidDomain()
	{
		$client = new ClientMock("random.nonexisting.url.wbl", "6781b18c2616772de74043ed0c32f76f");
		$client->post('/member', []);
	}

	public function testDelete()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->delete('/member/533');
		$this->assertEquals(204, $response->getStatusCode());
	}

	/**
	 * @expectedException Webling\API\ClientException
	 */
	public function testDeleteInvalidDomain()
	{
		$client = new ClientMock("random.nonexisting.url.wbl", "6781b18c2616772de74043ed0c32f76f");
		$client->delete('/member/123');
	}

}
