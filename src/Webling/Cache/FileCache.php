<?php

namespace Webling\Cache;

use Webling\API\IClient;

class FileCache implements ICache {

	protected $client;

	protected $options;

	function __construct(IClient $client, $options = []) {
		$this->client = $client;
		$this->options = $options;

		if (!isset($this->options['directory'])) {
			$this->options['directory'] = './webling_api_cache/';
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

					// delete cached objects
					foreach ($replicate['objects'] as $objCategory) {
						foreach ($objCategory as $obj) {
							$this->deleteObjectCache($obj);
						}
					}

					// update index file
					$index['revision'] = $replicate['revision'];
					$index['timestamp'] = time();
					file_put_contents($this->indexFile(), json_encode($index));

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
		array_map('unlink', glob($this->options['directory']."/obj*.json"));
		unlink($this->indexFile());
	}

	public function getObject($type, $objectId) {
		$cached = $this->getObjectCache($objectId);
		if ($cached != null) {
			return json_decode($cached, true);
		} else {
			$data = $this->client->get($type.'/'.$objectId)->getData();
			$this->setObjectCache($objectId, $data);
			return $data;
		}
	}

	public function getDefinitions() {
		// TODO: implement caching
		return $this->client->get('/definition')->getData();
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
}
