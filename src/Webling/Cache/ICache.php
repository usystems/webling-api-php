<?php

namespace Webling\Cache;

use Webling\API\IClient;
use Webling\CacheAdapters\ICacheAdapter;

interface ICache
{
	/**
	 * IClient constructor.
	 * @param IClient $client - an instance of Webling\API\IClient
	 * @param ICacheAdapter $cacheAdapter - an instance of Webling\Cache\ICacheAdapter
	 * @param array $options - any optional cache specific options
	 */
	function __construct(IClient $client, ICacheAdapter $cacheAdapter, $options = []);

	/**
	 * @param bool $force force an update
	 * @return void
	 */
	public function updateCache($force = false);

	/**
	 * @return void
	 * @throws CacheException
	 */
	public function clearCache();

	/**
	 * Returns the object data based on the objectId
	 * - Checks if the data exists in the cache and returns the data
	 * - If the object is not in the cache, request data from the Webling api
	 * - Return data if request was successful
	 *
	 * @param $type string - the object type (e.g "member", "usergroup", ..)
	 * @param $objectId number - the id of the requested object
	 * @return array|null
	 * @throws CacheException
	 */
	public function getObject($type, $objectId);

	/**
	 * Returns the object data based on the objectId
	 * - Checks if the data exists in the cache and returns the data
	 * - If the object is not in the cache, request data from the Webling api
	 * - Return data if request was successful
	 *
	 * @param $type string - the object type (e.g "member", "usergroup", ..)
	 * @param $objectId number - the id of the requested object
	 * @param $url string - url of the binary file
	 * @param $options array - extra options such as width/height for images
	 * @return array|null
	 * @throws CacheException
	 */
	public function getObjectBinary($type, $objectId, $url, $options = []);

	/**
	 * Returns multiple object data based on the objectIds
	 * - Checks if the data exists in the cache and returns the data
	 * - If the object is not in the cache, request data from the Webling api
	 * - Return all data if the request were successful
	 * - Requests objects in chunks for better performance
	 *
	 * @param $type string - the object type (e.g "member", "usergroup", ..)
	 * @param $objectIds array - the ids of the objects to request
	 * @return array|null
	 * @throws CacheException
	 */
	public function getObjects($type, $objectIds);


	/**
	 * Get a root object of an endpoint (e.g "/member", "/config")
	 *
	 * @param $type string - the name of the root node (e.g "member", "usergroup", ..)
	 * @return array|null
	 * @throws CacheException
	 */
	public function getRoot($type);
}
