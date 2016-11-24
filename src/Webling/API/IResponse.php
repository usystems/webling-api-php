<?php

namespace Webling\API;

interface IResponse
{
	/**
	 * Returns the HTTP Status Code
	 * @return int
	 */
	public function getStatusCode();

	/**
	 * Tries to use json_decode on raw string and returns object if possible.
	 * If the raw string can't be parsed as JSON, the raw string is returned.
	 * @return object|string
	 */
	public function getData();

	/**
	 * Resturns the raw string from the response.
	 * @return string
	 */
	public function getRawData();
}
