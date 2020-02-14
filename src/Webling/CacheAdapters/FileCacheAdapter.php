<?php

namespace Webling\CacheAdapters;

use Webling\Cache\CacheException;

class FileCacheAdapter implements ICacheAdapter {

	protected $options;

	function __construct($options = []) {
		if (!is_array($options)) {
			throw new CacheException('Invalid options');
		}

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
		array_map(array($this, 'deleteFile'), glob($this->options['directory'].'/obj_*.json'));
		array_map(array($this, 'deleteFile'), glob($this->options['directory'].'/bin_*'));
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
		$this->deleteFile($this->cacheStateFile());
	}

	public function getObject($id) {
		$id = intval($id);
		$file = $this->getCacheDir().'/obj_'.$id.'.json';
		if (file_exists($file)) {
			return json_decode(file_get_contents($file), true);
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
		$this->deleteFile($file);
	}

	public function getObjectBinary($id, $url, $options = []) {
		$id = intval($id);
		$file = $this->getCacheDir().'/bin_'.$id.'_'.md5(strtolower($url));
		if (file_exists($file)) {
			return file_get_contents($file);
		} else {
			return null;
		}
	}

	public function setObjectBinary($id, $url, $data) {
		$id = intval($id);
		$file = $this->getCacheDir().'/bin_'.$id.'_' . md5(strtolower($url));
		file_put_contents($file, $data);
	}

	public function deleteObjectBinaries($id) {
		$id = intval($id);
		array_map(array($this, 'deleteFile'), glob($this->options['directory'].'/bin_'.$id.'_*'));
	}

	public function getRoot($type) {
		$type = preg_replace('/[^a-z]/i', '', strtolower($type));
		$file = $this->getCacheDir().'/root_'.$type.'.json';
		if (file_exists($file)) {
			return json_decode(file_get_contents($file), true);
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
		$file = $this->getCacheDir().'/root_'.strtolower($type).'.json';
		$this->deleteFile($file);
	}

	public function deleteAllRoots() {
		array_map(array($this, 'deleteFile'), glob($this->getCacheDir().'/root_*.json'));
	}

	private function deleteFile($filename) {
		if (file_exists($filename)) {
			@unlink($filename);
			if (file_exists($filename)) {
				throw new CacheException('Could not delete cache file: ' . $filename);
			}
		}
	}
}
