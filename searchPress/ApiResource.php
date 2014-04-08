<?php

abstract class searchPress_ApiResource extends searchPress_Object {
  /**
   * @param string $class
   *
   * @returns string The name of the class, with namespacing and underscores
   *    stripped.
   */
  public static function className($class) {
    // Useful for namespaces: Foo\searchPress_Charge
    if ($postfix = strrchr($class, '\\'))
      $class = substr($postfix, 1);
    if (substr($class, 0, strlen('searchPress')) == 'searchPress')
      $class = substr($class, strlen('searchPress'));
    $class = str_replace('_', '', $class);
    $name = urlencode($class);
    $name = strtolower($name);
    return $name;
  }

  /**
   * @param string $class
   *
   * @returns string The endpoint URL for the given class.
   */
  public static function classUrl($class) {
    $base = self::_scopedLsb($class, 'className', $class);
    return "/${base}";
  }

  /**
   * @returns string The full API URL for this API resource.
   */
  public function instanceUrl() {
    $id = $this['id'];
    $class = get_class($this);
    if (!$id) {
      $message = "Could not determine which URL to request: "
               . "$class instance has invalid ID: $id";
      throw new searchPress_InvalidRequestError($message, null);
    }
    $id = searchPress_ApiRequestor::utf8($id);
    $base = $this->_lsb('classUrl', $class);
    $extn = urlencode($id);
    return "$base/$extn";
  }

  private static function _validateCall($method, $params = null, $apiKey = null){
    if ($params && !is_array($params)) {
      $message = "You must pass an array as the first argument to searchPress API "
               . "method calls.  (HINT: an example search query would be: "
               . "\"searchPressSearch::search(array('query' => array('index' => "
               . "'MY_INDEX', 'q' => 'SEARCH_STRING')))\")";
      throw new searchPress_Error($message);
    }

    if ($apiKey && !is_string($apiKey)) {
      $message = 'The second argument to searchPress API method calls is an '
               . 'optional per-request apiKey, which must be a string.  '
               . '(HINT: you can set a global apiKey by '
               . '"searchPress::setApiKey(<apiKey>)")';
      throw new searchPress_Error($message);
    }
  }

  protected static function _scopedSearch($class, $params = null, $apiKey = null) {
    self::_validateCall('create', $params, $apiKey);
    $requestor = new searchPress_ApiRequestor($apiKey);
    $url = self::_scopedLsb($class, 'classUrl', $class);
    list($response, $apiKey) = $requestor->request('get', $url, $params);
		return $response;
  }
}
