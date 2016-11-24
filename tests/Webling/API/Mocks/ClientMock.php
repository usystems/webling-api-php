<?php

namespace Webling\API;

class ClientMock extends Client
{
	protected function getCurlObject() {
		return new CurlHttpMock();
	}
}