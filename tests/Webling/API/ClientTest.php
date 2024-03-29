<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/Mocks/ClientMock.php';
require_once __DIR__ . '/Mocks/CurlHttpMock.php';

use PHPUnit\Framework\TestCase;
use Webling\API\ClientMock;

class ClientTest extends TestCase
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

	public function testGetInvalidDomain()
	{
		$this->expectExceptionMessage("Could not connect to");
		$this->expectException(Webling\API\ClientException::class);

		$client = new ClientMock("random.nonexisting.url.wbl", "6781b18c2616772de74043ed0c32f76f");
		$client->get('/member');
	}

	public function testGetEmptyDomain()
	{
		$this->expectExceptionMessage("Invalid domain");
		$this->expectException(Webling\API\ClientException::class);
		$client = new ClientMock("", "6781b18c2616772de74043ed0c32f76f");
		$client->get('/member');
	}

	public function testGetInvalidApikey()
	{
		$this->expectExceptionMessage("Invalid apikey, the apikey must be 32 chars");
		$this->expectException(Webling\API\ClientException::class);
		$client = new ClientMock("http://demo.webling.dev", "abc");
		$client->get('/member');
	}

	public function testGetInitWithOptions()
	{
		$options = [
			'connecttimeout' => 0,
			'timeout' => 5,
			'useragent' => 'My Custom User-Agent'
		];
		$client = new ClientMock("http://demo.webling.dev", "6781b18c2616772de74043ed0c32f76f", $options);
		$response = $client->get('/member');
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('objects', $response->getData());
	}

	public function testGetSetOptions()
	{
		$options = [
			'connecttimeout' => 0,
			'timeout' => 5,
			'useragent' => 'My Custom User-Agent'
		];
		$client = new ClientMock("http://demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$client->setOptions($options);
		$response = $client->get('/member');
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertArrayHasKey('objects', $response->getData());
	}

	public function testPut()
	{
		// test with an array data argument
		$data = [
			'properties' => [
				'Vorname' => "Maria"
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

	public function testPutInvalidJson()
	{
		$this->expectException(Webling\API\ClientException::class);
		// test with an array data argument
		$data = [
			'properties' => [
				'Vorname' => "invalid sequen\xce utf8"
			]
		];
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->put('/member/477', $data);
		$this->assertEquals(204, $response->getStatusCode());
	}

	public function testPutInvalidDomain()
	{
		$this->expectException(Webling\API\ClientException::class);
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

	public function testPostInvalidJson()
	{
		$this->expectException(Webling\API\ClientException::class);
		// test with an array data argument
		$data = [
			'properties' => [
				'Vorname' => "invalid sequen\xce utf8"
			]
		];
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->post('/member', $data);
		$this->assertEquals(204, $response->getStatusCode());
	}

	public function testPostInvalidDomain()
	{
		$this->expectException(Webling\API\ClientException::class);
		$client = new ClientMock("random.nonexisting.url.wbl", "6781b18c2616772de74043ed0c32f76f");
		$client->post('/member', []);
	}

	public function testDelete()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$response = $client->delete('/member/533');
		if ($response->getStatusCode() == 404) {
			// for testing with the real api, the object may not exist
			$this->assertEquals(404, $response->getStatusCode());
		} else {
			$this->assertEquals(204, $response->getStatusCode());
		}
	}

	public function testDeleteInvalidDomain()
	{
		$this->expectException(Webling\API\ClientException::class);
		$client = new ClientMock("random.nonexisting.url.wbl", "6781b18c2616772de74043ed0c32f76f");
		$client->delete('/member/123');
	}

}
