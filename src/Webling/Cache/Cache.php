<?php

namespace Webling\Cache;

use Webling\API\IClient;
use Webling\Cache\Adapters\ICacheAdapter;

class Cache implements ICache {

	protected $client;

	protected $cache;

	protected $options;

	function __construct(IClient $client, ICacheAdapter $cacheAdapter, $options = []) {
		$this->client = $client;
		$this->options = $options;
		$this->cache = $cacheAdapter;

		if (!isset($this->options['chunk_size'])) {
			// how many objects to fetch at once
			$this->options['chunk_size'] = 250;
		}

		$this->updateCache();
	}

	public function updateCache() {
		$index = $this->cache->getIndex();
		if ($index) {
			if (isset($index['revision'])) {

				$replicate = $this->client->get('/replicate/'.$index['revision'])->getData();
				if (isset($replicate['revision'])) {

					if ($replicate['revision'] < 0) {
						// if revision is set to -1, clear cache and make a complete sync
						// this happens when the users permission have changed
						$this->cache->clearCache();
					} else if(count($replicate['definitions']) > 0) {
						// if definitions changed, clear cache and make a complete sync
						// because member data may be invalid now
						$this->cache->clearCache();
					} else {

						// delete cached objects
						foreach ($replicate['objects'] as $objCategory) {
							foreach ($objCategory as $objId) {
								$this->cache->deleteObject($objId);
							}
						}

						// delete all root cache objects if the revision has changed
						// this could be done more efficient, but lets keep it simple for simplicity
						// For example additions won't be detected if we don't delete the roots
						if ($index['revision'] != $replicate['revision']) {
							$this->cache->deleteAllRoots();
						}

						// update index file
						$index['revision'] = $replicate['revision'];
						$index['timestamp'] = time();
						$this->cache->setIndex($index);
					}

				} else {
					throw new CacheException('Error in replication. No revision found.');
				}
				return;
			}
		}
		// write initial index file
		$replicate = $this->client->get('/replicate')->getData();
		$data = [
			'revision' => $replicate['revision'],
			'timestamp' => time(),
		];
		$this->cache->setIndex($data);
	}

	public function clearCache() {
		$this->cache->clearCache();
	}

	public function getObject($type, $objectId) {
		$cached = $this->cache->getObject($objectId);
		if ($cached != null) {
			return json_decode($cached, true);
		} else {
			$response = $this->client->get($type.'/'.$objectId);

			// only cache 2XX responses
			if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
				$data = $response->getData();
				$this->cache->setObject($objectId, $data);
				return $data;
			} else {
				return null;
			}
		}
	}

	public function getObjects($type, $objectIds) {
		if (is_array($objectIds)) {
			$cached_objects = array();
			$uncached_objects = array();
			foreach ($objectIds as $objectId) {
				$cached = $this->cache->getObject($objectId);
				if ($cached != null) {
					$cached_objects[$objectId] = json_decode($cached, true);
				} else {
					$uncached_objects[] = $objectId;
				}
			}
			$uncached_objects = array_unique($uncached_objects);

			if (count($uncached_objects) > 0) {
				$chunks = array_chunk($uncached_objects, $this->options['chunk_size']);
				foreach ($chunks as $chunk) {
					if (count($chunk) > 1) {
						$response = $this->client->get($type . '/' . implode(',', $chunk));

						// only cache 2XX responses
						if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
							$data = $response->getData();
							foreach ($data as $object) {
								if (isset($object['id'])) {
									$this->cache->setObject($object['id'], $object);
									$cached_objects[$object['id']] = $object;
								}
							}
						}
					} else {
						// chunk with one object has a different response format (no array)
						$cached_objects[$chunk[0]] = $this->getObject($type, $chunk[0]);
					}
				}
			}
			return $cached_objects;
		} else {
			return null;
		}
	}

	public function getRoot($type) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$cached = $this->cache->getRoot($type);
		if ($cached != null) {
			return json_decode($cached, true);
		} else {
			$response = $this->client->get($type);

			// only cache 2XX responses
			if ($response->getStatusCode() <= 200 && $response->getStatusCode() < 300) {
				$data = $response->getData();
				$this->cache->setRoot($type, $data);
				return $data;
			} else {
				return null;
			}
		}
	}
}
