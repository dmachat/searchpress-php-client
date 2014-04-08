<?php

class searchPress_Object implements ArrayAccess {
  /**
   * @var array Attributes that should not be sent to the API because they're
   *    not updatable (e.g. API key, ID).
   */
  public static $permanentAttributes;

  public static function init() {
    self::$permanentAttributes = new searchPress_Util_Set(array('_apiKey', 'id'));
  }

  protected $_apiKey;

  public function __construct($id = null, $apiKey = null) {
    $this->_apiKey = $apiKey;
  }

  // ArrayAccess methods
  public function offsetSet($k, $v) {
    $this->$k = $v;
  }

  public function offsetExists($k) {
    return array_key_exists($k, $this->_values);
  }

  public function offsetUnset($k) {
    unset($this->$k);
  }
  public function offsetGet($k) {
    return array_key_exists($k, $this->_values) ? $this->_values[$k] : null;
  }

  public function keys() {
    return array_keys($this->_values);
  }

  // Pretend to have late static bindings, even in PHP 5.2
  protected function _lsb($method) {
    $class = get_class($this);
    $args = array_slice(func_get_args(), 1);
    return call_user_func_array(array($class, $method), $args);
  }
  protected static function _scopedLsb($class, $method) {
    $args = array_slice(func_get_args(), 2);
    return call_user_func_array(array($class, $method), $args);
  }

  public function __toJSON() {
    if (defined('JSON_PRETTY_PRINT'))
      return json_encode($this->__toArray(true), JSON_PRETTY_PRINT);
    else
      return json_encode($this->__toArray(true));
  }

  public function __toString() {
    return $this->__toJSON();
  }

  public function __toArray($recursive=false) {
    if ($recursive)
      return searchPress_Util::convertsearchPressObjectToArray($this->_values);
    else
      return $this->_values;
  }
}

searchPress_Object::init();
