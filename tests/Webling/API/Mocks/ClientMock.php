<?php

namespace Webling\API;

class ClientMock extends Client
{
	const MOCK_ENABLED = true;

	protected function getCurlObject() {
		if (self::MOCK_ENABLED) {
			return new CurlHttpMock();
		} else {
			return new CurlHttp();
		}
	}
}