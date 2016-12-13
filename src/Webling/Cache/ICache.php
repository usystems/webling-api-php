<?php

namespace Webling\Cache;

use Webling\API\IClient;

interface ICache
{
	/**
	 * IClient constructor.
	 * @param IClient $client - an instance of Webling\API\IClient
	 * @param array $options - any optional cache specific options
	 */
	function __construct(IClient $client, $options = []);

	/**
	 * @return void
	 * @throws CacheException
	 */
	public function updateCache();

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
	 * Get a root object of an endpoint (e.g "/member", "/config")
	 *
	 * @param $type string - the name of the root node (e.g "member", "usergroup", ..)
	 * @return array|null
	 * @throws CacheException
	 */
	public function getRoot($type);
}