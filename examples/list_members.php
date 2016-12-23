<?php

// Autoload files using Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// create a new client with your credentials
$client = new Webling\API\Client("YOUR_DOMAIN.webling.ch", "YOUR_API_KEY");

// get a list of members
$response = $client->get('/member');

// check if request was successful
if ($response->getStatusCode() == 200) {

	$data = $response->getData();

	// check if "objects" exists
	if (isset($data['objects']) && count($data['objects']) > 0) {

		// loop over all member ids and fetch data
		foreach ($data['objects'] as $memberId) {

			// fetch the member data
			$response_member = $client->get('/member/' . $memberId);

			// check if request was successful
			if ($response_member->getStatusCode() == 200) {

				// print memberdata
				$memberdata = $response_member->getData();
				echo $memberdata['properties']['Vorname'] . ' ' . $memberdata['properties']['Name'] . "<br>\n";
			} else {
				echo 'ERROR: ' . $response_member->getStatusCode() . ' ' . $response_member->getRawData() . "<br>\n";
			}
		}
	}
} else {
	echo 'ERROR: ' . $response->getStatusCode() . ' ' . $response->getRawData() . "<br>\n";
}
