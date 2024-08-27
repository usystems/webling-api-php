# Webling API Wrapper for PHP

[![Build](https://github.com/usystems/webling-api-php/actions/workflows/ci.yml/badge.svg)](https://github.com/usystems/webling-api-php/actions/workflows/ci.yml)

A Lightweight PHP Wrapper to query the [Webling](https://www.webling.ch/) API.

## Installation

Install with Composer:

    composer require usystems/webling-api-php

## Usage

### Simple usage

```php
$api = new Webling\API\Client('https://demo.webling.ch','MY_APIKEY');

$response = $api->get('member/123');

if ($response->getStatusCode() < 400) {
    var_dump($response->getData());    // returns the parsed JSON
    var_dump($response->getRawData()); // returns the raw response string
}

$response = $api->put('/member', $data);
$response = $api->post('/member', $data);
$response = $api->delete('/member/123');
```

Create a new client with some options:

```php
$options = [
    'connecttimeout' => 5, // connection timeout in seconds
    'timeout' => 10, // transfer timeout
    'useragent' => 'My Custom User-Agent' // custom user agent
];
$api = new Webling\API\Client('https://demo.webling.ch','MY_APIKEY', $options);
```

For more examples see the "examples" folder.

### Caching Data

If you are doing lots of GET requests, you may want to use a cache. The Cache Class in combination with the FileCacheAdapter lets you cache Webling API requests on the filesystem. 
It does check which objects have changed and only fetches the changed objects.

This is how you use the Cache:

```php
// create a cache object
$client = new Webling\API\Client('https://demo.webling.ch','MY_APIKEY');
$adapter = new Webling\CacheAdapters\FileCacheAdapter([
    'directory' => './webling_cache'
]);
$cache = new Webling\Cache\Cache($client, $adapter);

// get single object
$member = $cache->getObject('member', 506);

// get binary data of object
$cache->getObjectBinary('member', 506, $member['properties']['Mitgliederbild']['href']);

// get multiple objects
$cache->getObjects('member', [506, 507, 508]);

// get object lists
$cache->getRoot('membergroup');

// check for updates and renew cache
$cache->updateCache();

// clear the whole cache
$cache->clearCache();
```
## Requirements

To use this library you need at least:

 * PHP >=5.6
 * PHP cURL Extension
 * PHP JSON Extension
 * A [Webling](https://www.webling.ch) Account with API enabled

## API Documentation

You can find the Full Documentation of the Webling REST-API at [demo.webling.ch/api](https://demo.webling.ch/api)

## Changelog

##### v1.3.1
 * Fixed a bug that prevented response caching from working properly

##### v1.3.0
Support for binary files caching has been added.

 * The interface `ICache` now has a new function `getObjectBinary()`. 
 * The `FileCacheAdapter` and `IFileCacheAdapter` has been updated to support the new functions.
 * Starting this release, only PHP >= 5.6 is tested and supported

##### v1.2.0
The `Webling\Cache\FileCache` has been marked as deprecated and will be removed in the future. Use the more generic `Webling\Cache\Cache` with the `Webling\CacheAdapters\FileCacheAdapter` instead.
