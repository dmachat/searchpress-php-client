# searchPress Client PHP library

To use, copy the searchPressAPI folder to your application, and add the following where you'd like to send a search query using searchPress:

	require_once('<RELATIVE-PATH>/searchPress.api.php');
	$params = array('query' => array('index' => '<YOUR-INDEX-NAME>', 'q' => '<YOUR-QUERY-STRING>'));
	searchPress::setApiKey('<YOUR-API-KEY>');
	return searchPress_Search::search($params);

This will return an instance of the elasticsearch hits array with the query results.
