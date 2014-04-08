<?php

class searchPress_InvalidRequestError extends searchPress_Error {
	public function __construct($message, $param, $httpStatus = null, $httpBody = null, $jsonBody = null) {
		parent::__construct($message, $httpStatus, $httpBody, $jsonBody);
		$this->param = $param;
	}
}
