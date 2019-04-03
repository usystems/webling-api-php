<?php

namespace Webling\Cache\Adapters;

use Webling\Cache\CacheException;

class FileCacheAdapter implements ICacheAdapter {

	protected $options;

	function __construct($options = []) {
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
	}

	private function getCacheDir() {
		return $this->options['directory'];
	}

	private function cacheStateFile() {
		return $this->getCacheDir().'/index.json';
	}

	public function clearCache() {
		array_map('unlink', glob($this->options['directory']."/obj_*.json"));
		$this->deleteAllRoots();
		$this->deleteCacheState();
	}

	public function setCacheState($data) {
		file_put_contents($this->cacheStateFile(), json_encode($data));
	}

	public function getCacheState() {
		if (file_exists($this->cacheStateFile())) {
			return json_decode(file_get_contents($this->cacheStateFile()), true);
		} else {
			return null;
		}
	}

	public function deleteCacheState() {
		if (file_exists($this->cacheStateFile())) {
			unlink($this->cacheStateFile());
		}
	}

	public function getObject($id) {
		$id = intval($id);
		$file = $this->getCacheDir().'/obj_'.$id.'.json';
		if (file_exists($file)) {
			return file_get_contents($file);
		} else {
			return null;
		}
	}

	public function setObject($id, $data) {
		$id = intval($id);
		$file = $this->getCacheDir().'/obj_'.$id.'.json';
		file_put_contents($file, json_encode($data));
	}

	public function deleteObject($id) {
		$id = intval($id);
		$file = $this->getCacheDir().'/obj_'.$id.'.json';
		if (file_exists($file)) {
			@unlink($file);
			if (file_exists($file)) {
				throw new CacheException('Could not delete cache file: ' . $file);
			}
		}
	}

	public function getRoot($type) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$file = $this->getCacheDir().'/root_'.$type.'.json';
		if (file_exists($file)) {
			return file_get_contents($file);
		} else {
			return null;
		}
	}

	public function setRoot($type, $data) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$file = $this->getCacheDir().'/root_'.$type.'.json';
		file_put_contents($file, json_encode($data));
	}

	public function deleteRoot($type) {
		$file = $this->getCacheDir()."/root_".strtolower($type).".json";
		@unlink($file);
		if (file_exists($file)) {
			throw new CacheException('Could not delete cache file: ' . $file);
		}
	}

	public function deleteAllRoots() {
		array_map('unlink', glob($this->getCacheDir()."/root_*.json"));
	}
}