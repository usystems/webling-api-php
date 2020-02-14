<?php

namespace Webling\CacheAdapters;

interface ICacheAdapter {
	/**
	 * Create a new ICacheAdapter instance
	 * @param array $options - any optional cache specific options
	 */
	function __construct($options = []);

	/**
	 * Clears the whole cache
	 * @return void
	 */
	public function clearCache();

	/**
	 * @param $data array data to be written to the cache state, usually contains the revision and a timestamp
	 * @return void
	 */
	public function setCacheState($data);

	/**
	 * get the current state of the cache, usually contains the revision and a timestamp
	 * @return array|null
	 */
	public function getCacheState();

	/**
	 * delete the cache state
	 * @return void
	 */
	public function deleteCacheState();

	/**
	 * @param $id number|string id of the object to retrieve from the cache
	 * @return array|null the cached response from the api
	 */
	public function getObject($id);

	/**
	 * @param $id number|string id of the object to write to the cache
	 * @param $data array|object|string data to be written to the cache (response from the api)
	 * @return void
	 */
	public function setObject($id, $data);

	/**
	 * delete the cached version of an object
	 * @param $id number|string id of the object to delete from the cache
	 * @return void
	 */
	public function deleteObject($id);

	/**
	 * @param $id number|string id of the object to retrieve from the cache
	 * @param $url string - url of the binary file
	 * @param $options array - extra options such as width/height for images
	 * @return string|null the cached response from the api
	 */
	public function getObjectBinary($id, $url, $options = []);

	/**
	 * @param $id number|string id of the object to write to the cache
	 * @param $url string - url of the binary file
	 * @param $data string data to be written to the cache (response from the api)
	 * @return void
	 */
	public function setObjectBinary($id, $url, $data);

	/**
	 * delete the cached version of an object
	 * @param $id number|string id of the object to delete from the cache
	 * @return void
	 */
	public function deleteObjectBinaries($id);

	/**
	 * @param $type string name of the root type to retrieve from the cache (e.g "membergroup")
	 * @return array|null the cached response from the api
	 */
	public function getRoot($type);

	/**
	 * @param $type string name of the root type to write to the cache (e.g "membergroup")
	 * @param $data array|object|string data to be written to the cache
	 * @return void
	 */
	public function setRoot($type, $data);

	/**
	 * delete the cache of a specific root request
	 * @param $type string name of the root type to delete from the cache (e.g "membergroup")
	 * @return void
	 */
	public function deleteRoot($type);

	/**
	 * delete all root request caches
	 * @return void
	 */
	public function deleteAllRoots();
}
