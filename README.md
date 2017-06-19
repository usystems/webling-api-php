# Webling API Wrapper for PHP

[![Build Status](https://travis-ci.org/usystems/webling-api-php.svg?branch=master)](https://travis-ci.org/usystems/webling-api-php)
[![Coverage Status](https://coveralls.io/repos/github/usystems/webling-api-php/badge.svg?branch=master)](https://coveralls.io/github/usystems/webling-api-php?branch=master)

A Lightweight PHP Wrapper to query the [Webling](https://www.webling.ch/) API.

## Installation

Install with Composer:

    composer require usystems/webling-api-php

## Usage

### Simple usage

```php
$api = new Webling\API\Client('https://demo.webling.ch','MY_APIKEY')

$response = $api->get('member/123')

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
$api = new Webling\API\Client('https://demo.webling.ch','MY_APIKEY')
```

For more examples see the "examples" folder.

### Caching Data

If you are doing lots of GET requests, you may want to use a cache. The FileCache lets you cache Webling API requests on the filesystem. 
It does check which objects have changed and only fetches the changed objects.

This is how you use the FileCache:

```php
// create a cache object
$client = new Webling\API\Client('https://demo.webling.ch','MY_APIKEY')
$cache = new Webling\Cache\FileCache($client, [
    'directory' => './webling_cache'
]);

// get object
$cache->getObject('member', 506);

// get object lists
$cache->getObject('membergroup');

// check for updates and renew cache
$cache->updateCache();

// clear the whole cache
$cache->clearCache();
```

## API Documentation

You can find the Full Documentation of the Webling REST-API at [demo.webling.ch/api](https://demo.webling.ch/api)
