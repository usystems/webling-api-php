<?php

use Webling\CacheAdapters\FileCacheAdapter;

// Autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// create a new client with your credentials
$client = new Webling\API\Client("YOUR_DOMAIN.webling.ch", "YOUR_API_KEY");

// create a cache Object
// make sure your cache directory is writeable
$adapter = new FileCacheAdapter([
	'directory' => './webling_cache'
]);
$cache = new Webling\Cache\Cache($client, $adapter);


// get a list of members
$response = $cache->getRoot('member');

// check if request was successful
if ($response != null) {

	// check if "objects" exists
	if (isset($response['objects']) && count($response['objects']) > 0) {

		// fetch the member data
		$response_members = $cache->getObjects('member', $response['objects']);

		// check if request was successful
		if ($response_members != null) {

			// loop over all member objects and display data
			foreach ($response_members as $member) {
				// print memberdata
				echo $member['properties']['Vorname'] . ' ' . $member['properties']['Name'] . "<br>\n";
			}
		} else {
			echo "ERROR: Could not fetch members with IDs: " . json_encode($response['objects']) . "<br>\n";
		}
	}
} else {
	echo "ERROR: Could not fetch memberlist<br>\n";
}
