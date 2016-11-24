<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../../../vendor/autoload.php';

use Webling\API\Response;

class ResponseTest extends PHPUnit_Framework_TestCase
{

	public function testStatusCode()
	{
		$response = new Response(200, "{}");
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testGetDataJson()
	{
		$testdata = [
			"test" => [
				"abc" => 123,
				"def" => "true",
				"ghi" => [1,2,3]
			]
		];
		$response = new Response(200, '{"test": {"abc": 123, "def": "true", "ghi": [1,2,"3"]}}');
		$this->assertEquals($testdata, $response->getData());
	}

	public function testGetDataInvalidJson()
	{
		$response = new Response(200, 'some text');
		$this->assertEquals("some text", $response->getData());
	}

	public function testGetRawData()
	{
		$data = '{"test": {"abc": 123, "def": "true", "ghi": [1,2,"3"]}}';
		$response = new Response(200, $data);
		$this->assertEquals($data, $response->getRawData());
	}

}
