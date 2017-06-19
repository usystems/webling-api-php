<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// create a new client with your credentials
$client = new Webling\API\Client("YOUR_DOMAIN.webling.ch", "YOUR_API_KEY");

// create a cache Object
// make sure your cache directory is writeable
$cache = new Webling\Cache\FileCache($client, [
	'directory' => './webling_cache'
]);


// get a list of members
$response = $cache->getRoot('member');

// check if request was successful
if ($response != null) {

	// check if "objects" exists
	if (isset($response['objects']) && count($response['objects']) > 0) {

		// loop over all member ids and fetch data
		foreach ($response['objects'] as $memberId) {

			// fetch the member data
			$response_member = $cache->getObject('member', $memberId);

			// check if request was successful
			if ($response_member != null) {

				// print memberdata
				echo $response_member['properties']['Vorname'] . ' ' . $response_member['properties']['Name'] . "<br>\n";
			} else {
				echo "ERROR: Could not fetch member with ID: " . $memberId . "<br>\n";
			}
		}
	}
} else {
	echo "ERROR: Could not fetch memberlist<br>\n";
}
