<?php

namespace Webling\API;

interface IClient
{
	/**
	 * IClient constructor.
	 * @param $domain - url of your webling instance, e.g "demo.webling.ch"
	 * @param $apikey - your api key
	 */
	function __construct($domain, $apikey);

	/**
	 * @param $path
	 * @return IResponse
	 */
	public function get($path);

	/**
	 * @param $path
	 * @param $data
	 * @return IResponse
	 */
	public function put($path, $data);

	/**
	 * @param $path
	 * @param $data
	 * @return IResponse
	 */
	public function post($path, $data);

	/**
	 * @param $path
	 * @return IResponse
	 */
	public function delete($path);
}