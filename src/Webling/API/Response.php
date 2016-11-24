<?php

namespace Webling\API;

class Response implements IResponse
{
	protected $code;

	protected $data;

	function __construct($code, $data) {
		$this->code = $code;
		$this->data = $data;
	}

	public function getStatusCode() {
		return $this->code;
	}

	public function getData() {
		$object = json_decode($this->data, true);
		if (json_last_error() == JSON_ERROR_NONE) {
			return $object;
		} else {
			return $this->data;
		}
	}

	public function getRawData() {
		return $this->data;
	}
}
