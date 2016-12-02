<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../API/Mocks/ClientMock.php';
require_once __DIR__ . '/../API/Mocks/CurlHttpMock.php';

use Webling\API\ClientMock;
use Webling\Cache\FileCache;

class FileCacheTest extends PHPUnit_Framework_TestCase
{

	public function testSetup()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client, []);

		$CACHE_DIR = __DIR__. '/../../webling_api_cache/';
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObject('member', 506);
		$member = $cache->getObject('membergroup', 550);
		$this->assertArrayHasKey('properties', $member);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);

	}

	public function testSetupCustomCacheDir()
	{
		$CACHE_DIR = __DIR__. '/../../cache/';
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client, [
			'directory' => $CACHE_DIR
		]);

		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObject('member', 506);
		$member = $cache->getObject('membergroup', 550);
		$this->assertArrayHasKey('properties', $member);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testLoadFromCache()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObject('member', 506);
		$this->assertArrayHasKey('properties', $member);

		// write some dummy content to check the file is loaded from cache
		$dummycache = ['loadedfrom' => 'cache'];
		file_put_contents($CACHE_DIR . '/obj_506.json', json_encode($dummycache));

		$member = $cache->getObject('member', 506);
		$this->assertEquals($dummycache, $member);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testUpdateCache()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObject('member', 506);
		$this->assertArrayHasKey('properties', $member);
		$this->assertTrue(file_exists($CACHE_DIR.'/obj_506.json'));

		$cache->updateCache();

		// file should now be deleted
		$this->assertTrue(!file_exists($CACHE_DIR.'/obj_506.json'));

		// load from server again
		$member = $cache->getObject('member', 506);
		$this->assertArrayHasKey('properties', $member);
		$this->assertTrue(file_exists($CACHE_DIR.'/obj_506.json'));

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

}
