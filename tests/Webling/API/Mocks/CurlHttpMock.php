<?php

namespace Webling\API;


/**
 * CurlHttp is a wrapper for the curl functions to make the curl functions mockable
 */
class CurlHttpMock extends CurlHttp
{
	protected $options = array(
		CURLOPT_CUSTOMREQUEST => 'GET'
	);

	protected $responses = array(
		'GET' => array(
			'https://demo.webling.dev/api/1/member' => array(
				200,
				'{"objects": [469,470,471]}'
			),
			'https://demo.webling.dev/api/1/member?sort=ID' => array(
				200,
				'{"objects": [469,470,471]}'
			),
			'https://random.nonexisting.url.wbl/api/1/member' => array(
				0,
				''
			),
			'http://demo.webling.dev/api/1/member' => array(
				200,
				'{"objects": [469,470,471]}'
			),
			'https://www.pcwebshop.co.uk/api/1/' => array(
				0,
				'{"objects": [469,470,471]}'
			),
			'http://www.pcwebshop.co.uk/api/1/' => array(
				301,
				''
			),

			// FileCacheTests
			'https://demo.webling.dev/api/1/member/506' => array(
				200,
				'{"type":"member","readonly":false,"properties":{"ID":38,"Vorname":"Markus"},"children":[],"parents":[555],"links":{"debitor":[885,1138,1902]}}'
			),
			'https://demo.webling.dev/api/1/member/506/image/Mitgliederbild.jpeg' => array(
				200,
				'IMAGE_DATA'
			),
			'https://demo.webling.dev/api/1/membergroup/550' => array(
				200,
				'{"type":"membergroup","readonly":false,"properties":{"title":"Mitglieder"},"children":{"membergroup":[555,551,556,558,552]},"parents":[],"links":[]}'
			),

			'https://demo.webling.dev/api/1/replicate' => array(
				200,
				'{"revision": 1602,"version": 740}'
			),
			'https://demo.webling.dev/api/1/replicate/1600' => array(
				200,
				'{"objects":{},"context":[],"definitions":[],"settings":false,"quota":false,"subscription":false,"revision":1602,"version":740}'
			),
			'https://demo.webling.dev/api/1/replicate/1602' => array(
				200,
				'{"objects":{"member":[506],"membergroup":[550]},"context":[],"definitions":[],"settings":false,"quota":true,"subscription":false,"revision":1602,"version":740}'
			),
			'https://demo.webling.dev/api/1/member/999999' => array(
				404,
				''
			),
			'https://demo.webling.dev/api/1/nonexistingendpoint' => array(
				404,
				''
			),
			'https://demo.webling.dev/api/1/member/506,507,508' => array(
				200,
				'[
					{"id":506,"type":"member","readonly":false,"properties":{"ID":38,"Vorname":"Markus"},"children":[],"parents":[555],"links":{}},
					{"id":507,"type":"member","readonly":false,"properties":{"ID":39,"Vorname":"Hans"},"children":[],"parents":[555],"links":{}},
					{"id":508,"type":"member","readonly":false,"properties":{"ID":40,"Vorname":"Peter"},"children":[],"parents":[555],"links":{}}
				]'
			),
			'https://demo.webling.dev/api/1/member/506,507' => array(
				200,
				'[
					{"id":506,"type":"member","readonly":false,"properties":{"ID":38,"Vorname":"Markus"},"children":[],"parents":[555],"links":{}},
					{"id":507,"type":"member","readonly":false,"properties":{"ID":39,"Vorname":"Hans"},"children":[],"parents":[555],"links":{}}
				]'
			),
			'https://demo.webling.dev/api/1/member/508' => array(
				200,
				'{"id":508,"type":"member","readonly":false,"properties":{"ID":40,"Vorname":"Peter"},"children":[],"parents":[555],"links":{}}'
			),

		),
		'PUT' => array(
			'https://demo.webling.dev/api/1/member/477' => array(
				204,
				''
			),
			'https://random.nonexisting.url.wbl/api/1/member/477' => array(
				0,
				''
			)
		),
		'POST' => array(
			'https://demo.webling.dev/api/1/member' => array(
				201,
				'2500'
			),
			'https://random.nonexisting.url.wbl/api/1/member' => array(
				0,
				''
			)
		),
		'DELETE' => array(
			'https://demo.webling.dev/api/1/member/533' => array(
				204,
				''
			),
			'https://random.nonexisting.url.wbl/api/1/member/123' => array(
				0,
				''
			)
		)
	);

	/** @noinspection PhpMissingParentConstructorInspection */
	function __construct() {}

	public function curl_setopt($option, $value) {
		$this->options[$option] = $value;
	}

	public function curl_exec() {
		$method = $this->options[CURLOPT_CUSTOMREQUEST];
		$url = $this->options[CURLOPT_URL];
		if (isset($this->responses[$method][$url])) {
			if ($this->responses[$method][$url][0] == 0) {
				throw new ClientException('Could not connect to: ');
			}
			return $this->responses[$method][$url][1];
		}
		throw new \Exception('Could not find mock response for: '. $method .' ' . $url);
	}

	public function curl_getinfo() {
		$method = $this->options[CURLOPT_CUSTOMREQUEST];
		$url = $this->options[CURLOPT_URL];
		if (isset($this->responses[$method][$url])) {
			return array(
				'http_code' => $this->responses[$method][$url][0]
			);
		}
		throw new \Exception('Could not find mock response for: '. $method .' ' . $url);
	}

	public function curl_error() {
		return '';
	}

	public function curl_close() {}
}
