<?php

namespace Webling\Cache;

use Webling\API\IClient;
use Webling\CacheAdapters\ICacheAdapter;

class Cache implements ICache {

	protected $client;

	protected $adapter;

	protected $options;

	function __construct(IClient $client, ICacheAdapter $cacheAdapter, $options = []) {
		$this->client = $client;
		$this->options = $options;
		$this->adapter = $cacheAdapter;

		if (!isset($this->options['chunk_size'])) {
			// how many objects to fetch at once
			$this->options['chunk_size'] = 250;
		}

		if (!isset($this->options['pause_between_sync'])) {
			// seconds to wait until next /replicate call is made
			$this->options['pause_between_sync'] = 60;
		}

		$this->updateCache();
	}

	public function updateCache($force = false) {
		$cache_state = $this->adapter->getCacheState();
		if ($cache_state) {
			if (isset($cache_state['revision'])) {
				if (isset($cache_state['timestamp'])) {
					if (!$force && $this->options['pause_between_sync'] > 0 && $cache_state['timestamp'] > time() - $this->options['pause_between_sync']) {
						// wait a bit more for the next replication
						return;
					}
				}

				$replicate = $this->client->get('/replicate/'.$cache_state['revision'])->getData();
				if (isset($replicate['revision'])) {

					if ($replicate['revision'] < 0) {
						// if revision is set to -1, clear cache and make a complete sync
						// this happens when the users permission have changed
						$this->adapter->clearCache();
					} else if(count($replicate['definitions']) > 0) {
						// if definitions changed, clear cache and make a complete sync
						// because member data may be invalid now
						$this->adapter->clearCache();
					} else {

						// delete cached objects
						foreach ($replicate['objects'] as $objCategory) {
							foreach ($objCategory as $objId) {
								$this->adapter->deleteObject($objId);
								$this->adapter->deleteObjectBinaries($objId);
							}
						}

						// delete all root cache objects if the revision has changed
						// this could be done more efficient, but lets keep it simple for simplicity
						// For example additions won't be detected if we don't delete the roots
						if ($cache_state['revision'] != $replicate['revision']) {
							$this->adapter->deleteAllRoots();
						}

						// update cache state file
						$cache_state['revision'] = $replicate['revision'];
						$cache_state['timestamp'] = time();
						$this->adapter->setCacheState($cache_state);
					}

				} else {
					$this->clearCache();
					throw new CacheException('Error in replication. No revision found.');
				}
				return;
			}
		}
		// write initial cache state file
		$replicate = $this->client->get('/replicate')->getData();
		if (isset($replicate['revision'])) {
			$data = [
				'revision' => $replicate['revision'],
				'timestamp' => time(),
			];
			$this->adapter->setCacheState($data);
		}
	}

	public function clearCache() {
		$this->adapter->clearCache();
	}

	public function getObject($type, $objectId) {
		$cached = $this->adapter->getObject($objectId);
		if ($cached != null) {
			return $cached;
		} else {
			$response = $this->client->get($type.'/'.$objectId);

			// only cache 2XX responses
			if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
				$data = $response->getData();
				// the single response does not have the id attribute, set it here for consistency
				$data['id'] = $objectId;
				$this->adapter->setObject($objectId, $data);
				return $data;
			} else {
				return null;
			}
		}
	}
	
	public function getObjectBinary($type, $objectId, $url, $options = []) {
		if ($url) {
			$cached = $this->adapter->getObjectBinary($objectId, $url, $options);
			if ($cached != null) {
				return $cached;
			} else {
				// remove /api/1/ from the beginning of the url, if present
				$prepared_url = preg_replace('/^(\/api\/\d*\/)/','', $url);
				$response = $this->client->get($prepared_url);

				// only cache 2XX responses
				if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
					$data = $response->getData();
					$this->adapter->setObjectBinary($objectId, $url, $data);
					return $this->adapter->getObjectBinary($objectId, $url, $options);
				}
			}
		}
		return null;
	}

	public function getObjects($type, $objectIds) {
		if (is_array($objectIds)) {
			$cached_objects = array();
			$uncached_objects = array();
			foreach ($objectIds as $objectId) {
				$cached = $this->adapter->getObject($objectId);
				if ($cached != null) {
					$cached_objects[$objectId] = $cached;
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
									$this->adapter->setObject($object['id'], $object);
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
		$cached = $this->adapter->getRoot($type);
		if ($cached != null) {
			return $cached;
		} else {
			$response = $this->client->get($type);

			// only cache 2XX responses
			if ($response->getStatusCode() <= 200 && $response->getStatusCode() < 300) {
				$data = $response->getData();
				$this->adapter->setRoot($type, $data);
				return $data;
			} else {
				return null;
			}
		}
	}
}
