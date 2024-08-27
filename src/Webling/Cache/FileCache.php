<?php

namespace Webling\Cache;

use Webling\API\IClient;

/**
 * @deprecated deprecated since 1.2.0, use the more generic Cache class with the FileCacheAdapter instead
 */
class FileCache {

	protected $client;

	protected $options;

	function __construct(IClient $client, $options = []) {
		$this->client = $client;
		$this->options = $options;

		if (!isset($this->options['directory'])) {
			$this->options['directory'] = './webling_api_cache/';
		}

		if (!isset($this->options['chunk_size'])) {
			// how many objects to fetch at once
			$this->options['chunk_size'] = 250;
		}

		if (!file_exists($this->options['directory'])) {
			$success = mkdir($this->options['directory'], 0755, false);
			if (!$success) {
				throw new CacheException('Could not create cache directory: '. $this->options['directory']);
			}
		}

		if (!is_writeable($this->options['directory'])) {
			$success = chmod($this->options['directory'], 0755);
			if (!$success) {
				throw new CacheException('Cache directory is not writeable: '. $this->options['directory']);
			}
		}

		$this->updateCache();
	}

	public function updateCache() {
		if (file_exists($this->indexFile())) {
			$index = json_decode(file_get_contents($this->indexFile()), true);
			if (isset($index['revision'])) {

				$replicate = $this->client->get('/replicate/'.$index['revision'])->getData();
				if (isset($replicate['revision'])) {

					if ($replicate['revision'] < 0) {
						// if revision is set to -1, clear cache and make a complete sync
						// this happens when the users permission have changed
						$this->clearCache();
					} else if(count($replicate['definitions']) > 0) {
						// if definitions changed, clear cache and make a complete sync
						// because member data may be invalid now
						$this->clearCache();
					} else {

						// delete cached objects
						foreach ($replicate['objects'] as $objCategory) {
							foreach ($objCategory as $obj) {
								$this->deleteObjectCache($obj);
							}
						}

						// delete all root cache objects if the revision has changed
						// this could be done more efficient, but lets keep it simple for simplicity
						if ($index['revision'] != $replicate['revision']) {
							$this->deleteRootCache();
						}

						// update index file
						$index['revision'] = $replicate['revision'];
						$index['timestamp'] = time();
						file_put_contents($this->indexFile(), json_encode($index));
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
		file_put_contents($this->indexFile(), json_encode($data));
	}

	public function clearCache() {
		array_map('unlink', glob($this->options['directory']."/obj_*.json"));
		$this->deleteRootCache();
		unlink($this->indexFile());
	}

	public function getObject($type, $objectId) {
		$cached = $this->getObjectCache($objectId);
		if ($cached != null) {
			return json_decode($cached, true);
		} else {
			$response = $this->client->get($type.'/'.$objectId);

			// only cache 2XX responses
			if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
				$data = $response->getData();
				$this->setObjectCache($objectId, $data);
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
				$cached = $this->getObjectCache($objectId);
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
									$this->setObjectCache($object['id'], $object);
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
		$cached = $this->getRootCache($type);
		if ($cached != null) {
			return json_decode($cached, true);
		} else {
			$response = $this->client->get($type);

			// only cache 2XX responses
			if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
				$data = $response->getData();
				$this->setRootCache($type, $data);
				return $data;
			} else {
				return null;
			}
		}
	}

	public function getCacheDir() {
		return $this->options['directory'];
	}

	private function indexFile() {
		return $this->options['directory'].'/index.json';
	}

	private function getObjectCache($id) {
		$id = intval($id);
		$file = $this->options['directory'].'/obj_'.$id.'.json';
		if (file_exists($file)) {
			return file_get_contents($file);
		} else {
			return null;
		}
	}

	private function setObjectCache($id, $data) {
		$id = intval($id);
		$file = $this->options['directory'].'/obj_'.$id.'.json';
		file_put_contents($file, json_encode($data));
	}

	private function deleteObjectCache($id) {
		$id = intval($id);
		$file = $this->options['directory'].'/obj_'.$id.'.json';
		if (file_exists($file)) {
			@unlink($file);
			if (file_exists($file)) {
				throw new CacheException('Could not delete cache file: ' . $file);
			}
		}
	}

	private function getRootCache($type) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$file = $this->options['directory'].'/root_'.$type.'.json';
		if (file_exists($file)) {
			return file_get_contents($file);
		} else {
			return null;
		}
	}

	private function setRootCache($type, $data) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$file = $this->options['directory'].'/root_'.$type.'.json';
		file_put_contents($file, json_encode($data));
	}

	private function deleteRootCache() {
		array_map('unlink', glob($this->options['directory']."/root_*.json"));
	}
}
