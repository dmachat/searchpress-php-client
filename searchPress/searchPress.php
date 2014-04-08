<?php

abstract class searchPress {
  /**
   * @var string The searchPress client API key to be used for requests.
   */
  public static $apiKey;

  /**
   * @var string The base URL for the searchPress API.
   */
  //public static $apiBase = 'http://mw.searchpress.com';
	public static $apiBase = 'http://localhost:8000';

	/**
	 * @var string|null The version of the searchPress API to use for requests.
	 */
	public static $apiVersion = null;
	const VERSION = '0.0.1';

  /**
   * @return string The API key used for requests.
   */
  public static function getApiKey() {
    return self::$apiKey;
  }

  /**
   * Sets the API key to be used for requests.
   *
   * @param string $apiKey
   */
  public static function setApiKey($apiKey) {
    self::$apiKey = $apiKey;
	}

	/**
	 * @param string $apiVersion The API version to use for requests.
	 */
	public static function getApiVersion() {
		return self::$apiVersion;
	}
}
