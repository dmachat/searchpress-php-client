<?php

class searchPress_Search extends searchPress_ApiResource {
	/**
	 * @param array|null $params
	 * @param string|null $apiKey
	 *
	 * @return array The Elastic Search hits.hits object
	 */
	public static function search($params = null, $apiKey = null) {
		$class = get_class();
		return self::_scopedSearch($class, $params, $apiKey);
	}
}
