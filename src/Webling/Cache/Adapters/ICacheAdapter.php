<?php

namespace Webling\Cache\Adapters;

interface ICacheAdapter
{
	function __construct($options = []);

	public function clearCache();

	public function setCacheState($data);
	public function getCacheState();
	public function deleteCacheState();

	public function getObject($id);
	public function setObject($id, $data);
	public function deleteObject($id);

	public function getRoot($type);
	public function setRoot($type, $data);
	public function deleteRoot($type);

	public function deleteAllRoots();
}