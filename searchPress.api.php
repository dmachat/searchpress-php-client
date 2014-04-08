<?php

// This snippet (and some of the curl code) due to the Facebook SDK.
if (!function_exists('curl_init')) {
  throw new Exception('searchPress needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('searchPress needs the JSON PHP extension.');
}
if (!function_exists('mb_detect_encoding')) {
  throw new Exception('searchPress needs the Multibyte String PHP extension.');
}

// searchPress singleton
require(dirname(__FILE__) . '/searchPress/searchPress.php');

// Errors
require(dirname(__FILE__) . '/searchPress/Error.php');
require(dirname(__FILE__) . '/searchPress/ApiConnectionError.php');
require(dirname(__FILE__) . '/searchPress/ApiError.php');
require(dirname(__FILE__) . '/searchPress/AuthenticationError.php');
require(dirname(__FILE__) . '/searchPress/InvalidRequestError.php');

// Requester
require(dirname(__FILE__) . '/searchPress/ApiRequestor.php');

// Util
require(dirname(__FILE__) . '/searchPress/Set.php');

// Resources
require(dirname(__FILE__) . '/searchPress/Object.php');
require(dirname(__FILE__) . '/searchPress/ApiResource.php');
require(dirname(__FILE__) . '/searchPress/Search.php');
