<?php

// this is an image proxy to load member images
// usage: image_proxy.php?id=MEMBER_ID&property=PROPERTY_NAME

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

// load image from cache
$image_data = null;
if (isset($_REQUEST['id']) && isset($_REQUEST['property'])) {
	$memberId = intval($_REQUEST['id']);
	$member = $cache->getObject('member', $memberId);
	if ($member && isset($member['properties'][$_REQUEST['property']]) && $member['properties'][$_REQUEST['property']] !== null) {
		$data = $cache->getObjectBinary('member', $memberId, $member['properties'][$_REQUEST['property']]['href']);
		if (strlen($data)) {
			$image_data = $data;
		}
	}
}

// display image
if ($image_data !== null) {
	$finfo = new finfo(FILEINFO_MIME);
	$mime = $finfo->buffer($image_data);
	header("Content-type: " . $mime);
	echo $image_data;
} else {
	http_response_code(404);
	echo '404 Not Found';
}
