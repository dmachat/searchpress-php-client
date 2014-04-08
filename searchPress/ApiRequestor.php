<?php

class searchPress_ApiRequestor {
	/**
	 * @var string @apiKey The API key that's to be used to make requests.
	 */
	public $apiKey;

	public function __construct($apiKey = null) {
		$this->_apiKey = $apiKey;
	}

	/**
	 * @param string $url The path to the API endpoint.
	 *
	 * @returns string The full path.
	 */
	public static function apiUrl($url = '') {
		$apiBase = searchPress::$apiBase;
		return "$apiBase$url";
	}

	/**
	 * @param string|mixed $value A string to UTF8-encode.
	 *
	 * @returns string|mixed The UTF8-encoded string, or the object passed in if
	 *   it wasn't a string.
	 */
	public static function utf8($value) {
		if (is_string($value) && mb_detect_encoding($value, "UTF-8", TRUE) != "UTF-8") {
			return utf8_encode($value);
		}
		else {
			return $value;
		}
	}

	private static function _encodeObjects($d) {
		if ($d instanceof searchPress_ApiResource) {
			return self::utf8($d->id);
		}
		else if ($d === true) {
			return 'true';
		}
		else if ($d === false) {
			return 'false';
		}
		else if (is_array($d)) {
			$res = array();
			foreach ($d as $k => $v)
				$res[$k] = self::_encodeObjects($v);
			return $res;
		}
		else {
			return self::utf8($d);
		}
	}

	/**
	 * @param array $arr The ES search array.
	 *
	 * @returns string A json encoded query for the ES endpoint.
	 */
	public static function encode($arr) {
		if (!is_array($arr))
			return $arr;

		return json_encode($arr);
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array|null $params
	 *
	 * @returns array An array whose first element is the response and second
	 *   element is the API key used to make the request.
	 */
	public function request($method, $url, $params = null) {
		if (!$params)
			$params = array();
		list($rbody, $rcode, $myApiKey) = $this->_requestRaw($method, $url, $params);
		$resp = $this->_interpretResponse($rbody, $rcode);
		return array($resp, $myApiKey);
	}

	/**
	 * @param string $rbody A JSON string.
	 * @param int $rcode
	 * @param array $resp
	 *
	 * @throws searchPress_InvalidRequestError if the error is caused by the user
	 *   @TODO Middleware return a 404 on no results, need to fail more gracefully
	 * @throws searchPress_AuthenticationError if the error is caused by invalid
	 *   permissions.
	 * @throws searchPress_ApiError otherwise.
	 */
	public function handleApiError($rbody, $rcode, $resp) {
		$msg = isset($error['message']) ? $error['message'] : null;
		$param = isset($error['param']) ? $error['param'] : null;

		switch ($rcode) {
			case 404:
				throw new searchPress_InvalidRequestError($msg, $param, $rcode, $rbody, $resp);
			case 401:
				throw new searchPress_AuthenticationError($msg, $rcode, $rbody, $resp);
			default:
				throw new searchPress_ApiError($msg, $rcode, $rbody, $resp);
		}
	}

	private function _requestRaw($method, $url, $params) {
		$myApiKey = $this->_apiKey;
		if (!$myApiKey)
			$myApiKey = searchPress::$apiKey;

		if (!$myApiKey) {
			// @TODO Add full instructions to the error messaging.
			$msg = 'No API key provided. (You can set your API key using '
				. '"searchPress::setApiKey([API-KEY])". You can generate API keys from '
				. 'the Get searchPress web interface.';
			throw new searchPress_AuthenticationError($msg);
		}

		$absUrl = $this->apiUrl($url);
		$params = self::_encodeObjects($params);
		$langVersion = phpversion();
		$uname = php_uname();
		$ua = array('bindings_version' => searchPress::VERSION,
								'lang' => 'php',
								'lang_version' => $langVersion,
								'publisher' => 'searchPress',
								'uname' => $uname);
		$headers = array('Content-Type: application/json',
										 'X-searchPress-Client-User-Agent: ' . json_encode($ua),
										 'Authorization: Bearer ' . $myApiKey);
		if (searchPress::$apiVersion)
			$headers[] = 'searchPress-Version: ' . searchPress::$apiVersion;
		list($rbody, $rcode) = $this->_curlRequest(
			$method,
			$absUrl,
			$headers,
			$params
		);
		return array($rbody, $rcode, $myApiKey);
	}

	private function _interpretResponse($rbody, $rcode) {
		try {
			$resp = json_decode($rbody, true);
		}
		catch (Exception $e) {
			$msg = "Invalid response body from API: $rbody "
					 . "(HTTP response code was $rcode)";
			throw new searchPress_ApiError($msg, $rcode, $rbody);
		}

		if ($rcode < 200 || $rcode >= 300) {
			$this->handleApiError($rbody, $rcode, $resp);
		}
		return $resp;
	}

	private function _curlRequest($method, $absUrl, $headers, $params) {
		$curl = curl_init();
		$method = strtolower($method);
		$opts = array();
		if ($method == 'get') {
			$opts[CURLOPT_HTTPGET] = 1;
		}
		else if ($method == 'post') {
			$opts[CURLOPT_POST] = 1;
		}
		else {
			throw new searchPress_ApiError("Unrecognized method $method");
		}

		$absUrl = self::utf8($absUrl);
		$opts[CURLOPT_URL] = $absUrl;
		$opts[CURLOPT_RETURNTRANSFER] = true;
		$opts[CURLOPT_CONNECTTIMEOUT] = 30;
		$opts[CURLOPT_TIMEOUT] = 80;
		$opts[CURLOPT_RETURNTRANSFER] = true;
		$opts[CURLOPT_HTTPHEADER] = $headers;
		$opts[CURLOPT_POSTFIELDS] = self::encode($params);

		curl_setopt_array($curl, $opts);
		$rbody = curl_exec($curl);

		$rcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return array($rbody, $rcode);
	}

	/**
	 * @param number $errno
	 * @param string $message
	 * @throws searchPress_ApiConnectionError
	 *
	 * @TODO Check support addresses
	 */
	public function handleCurlError($errno, $message) {
		$apiBase = searchPress::$apiBase;
		switch ($errno) {
			case CURLE_COULDNT_CONNECT:
			case CURLE_COULDNT_RESOLVE_HOST:
			case CURLE_OPERATION_TIMEOUT:
				$msg = "Could not connect to searchPress ($apiBase). Please check your "
					. "internet connection and try again. If this problem persists, "
					. "you should check searchPress's service status at "
					. "https://getsearchpress.com/status, or";
				break;
			default:
				$msg = "Unexpected error communicating with searchPress. "
					. "If this problem persists,";
		}
		$msg .= " let us know at support@getsearchpress.com.";
		$msg .= "\n\n(Network error [errno $errno]: $message)";
		throw new searchPress_ApiConnectionError($msg);
	}
}
