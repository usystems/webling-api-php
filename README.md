# Webling API Wrapper for PHP

[![Build Status](https://travis-ci.org/usystems/webling-api-php.svg?branch=master)](https://travis-ci.org/usystems/webling-api-php)

A Lightweight Webling API Wrapper for PHP.

## IMPORTANT: This project is still under development and not yet ready for production use!

Install with Composer:

    composer require usystems/webling-api-php

Simple usage:

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
