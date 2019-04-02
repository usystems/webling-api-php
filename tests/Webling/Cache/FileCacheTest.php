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

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObject('member', 506);
		$membergroup = $cache->getObject('membergroup', 550);
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
		$membergroup = $cache->getObject('membergroup', 550);
		$this->assertArrayHasKey('properties', $member);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testLoadObjectFromCache()
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

	public function testUpdateObjectCache()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObject('member', 506);
		$this->assertArrayHasKey('properties', $member);
		$this->assertTrue(file_exists($CACHE_DIR.'/obj_506.json'));

		$cache->updateCache();

		if (ClientMock::MOCK_ENABLED) {
			// file should now be deleted (only works while mocking)
			$this->assertTrue(!file_exists($CACHE_DIR.'/obj_506.json'));
		}

		// load from server again
		$member = $cache->getObject('member', 506);
		$this->assertArrayHasKey('properties', $member);
		$this->assertTrue(file_exists($CACHE_DIR.'/obj_506.json'));

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testGetObjectNotFound()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObject('member', 999999);
		$this->assertEquals(null, $member);
		$this->assertFalse(file_exists($CACHE_DIR.'/obj_999999.json'));

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testLoadMultipleObjectsFromCache()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObjects('member', [506,507,508]);
		$this->assertEquals(3, count($member));
		$this->assertArrayHasKey('properties', $member[507]);

		// write some dummy content to check the file is loaded from cache
		$dummycache = ['loadedfrom' => 'cache'];
		file_put_contents($CACHE_DIR . '/obj_506.json', json_encode($dummycache));

		$member = $cache->getObject('member', 506);
		$this->assertEquals($dummycache, $member);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testLoadMultipleObjectsPartiallyFromCache()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		// write some dummy content to check the file is loaded from cache
		$dummycache = ['loadedfrom' => 'cache'];
		file_put_contents($CACHE_DIR . '/obj_508.json', json_encode($dummycache));

		$member = $cache->getObjects('member', [506,507,508]);
		$this->assertEquals(3, count($member));
		$this->assertArrayHasKey('properties', $member[507]);
		$this->assertEquals($dummycache, $member[508]);

		$cached = json_decode(file_get_contents($CACHE_DIR . '/obj_507.json'), true);
		$this->assertArrayHasKey('properties', $cached);

		$cached = json_decode(file_get_contents($CACHE_DIR . '/obj_508.json'), true);
		$this->assertEquals($dummycache, $cached);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testLoadMultipleObjectsFromCacheWithChunks()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client, array('chunk_size' => 2));

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObjects('member', [506,507,508]);
		$this->assertEquals(3, count($member));
		$this->assertArrayHasKey('properties', $member[507]);

		$cached = json_decode(file_get_contents($CACHE_DIR . '/obj_507.json'), true);
		$this->assertArrayHasKey('properties', $cached);

		$this->assertFileExists($CACHE_DIR . '/obj_506.json');
		$this->assertFileExists($CACHE_DIR . '/obj_508.json');

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testLoadMultipleObjectsInvalidArgument()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObjects('member', null);
		$this->assertNull($member);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testLoadMultipleObjectsEmptyArgument()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getObjects('member', []);
		$this->assertEquals(array(), $member);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testLoadRootFromCache()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getRoot('member');
		$this->assertArrayHasKey('objects', $member);

		$this->assertTrue(file_exists($CACHE_DIR.'/root_member.json'));

		// write some dummy content to check the file is loaded from cache
		$dummycache = ['loadedfrom' => 'cache'];
		file_put_contents($CACHE_DIR . '/root_member.json', json_encode($dummycache));

		$member = $cache->getRoot('member');
		$this->assertEquals($dummycache, $member);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testClearRootCacheOnRevisionChange()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$member = $cache->getRoot('member');
		$this->assertArrayHasKey('objects', $member);


		$index = [
			'revision' => 1600,
			'timestamp' => time() - 3600
		];
		file_put_contents($CACHE_DIR.'/index.json', json_encode($index));


		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$this->assertFalse(file_exists($CACHE_DIR.'/root_member.json'));


		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testGetRootNotFound()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$response = $cache->getRoot('nonexistingendpoint');
		$this->assertEquals(null, $response);
		$this->assertFalse(file_exists($CACHE_DIR.'/root_nonexistingendpoint.json'));

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

	public function testRootCacheSpecialChars()
	{
		$client = new ClientMock("demo.webling.dev", "6781b18c2616772de74043ed0c32f76f");
		$cache = new FileCache($client);

		$CACHE_DIR = $cache->getCacheDir();
		$this->assertTrue(file_exists($CACHE_DIR));

		$response = $cache->getRoot('non/exist/../../ing=<>:"/\|?*endpoint');
		$this->assertEquals(null, $response);

		// cleanup
		$cache->clearCache();
		rmdir($CACHE_DIR);
	}

}
