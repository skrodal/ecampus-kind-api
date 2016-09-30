<?php

	/**
	 *
	 * @author Simon Skrødal
	 * @since  August 2015
	 */

	// User/pass
	$DATAPORTEN_CONFIG_PATH = '/var/www/etc/ecampus-kind/dataporten_config.js';
	// Endpoint URI
	$KIND_CONFIG_PATH = '/var/www/etc/ecampus-kind/kind_config.js';
	// API Root
	$BASE = dirname(__FILE__);
	// Result or error responses
	require_once($BASE . '/lib/response.class.php');
	// Checks CORS and pulls Dataporten info from headers
	require_once($BASE . '/lib/dataporten.class.php');
	$dataporten_config = file_get_contents($DATAPORTEN_CONFIG_PATH);
	if($dataporten_config === false) {
		Response::error(404, $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found: Dataporten config.');
	}
	$dataporten = new Dataporten(json_decode($dataporten_config, true));
	// AltoRouter: http://altorouter.com
	require_once($BASE . '/lib/router.class.php');
	// Remember to update .htacces as well!
	$API_BASE_PATH = '/api/ecampus-kind';
	$router        = new Router();
	$router->setBasePath($API_BASE_PATH);
	// Proxy API to KIND
	require_once($BASE . '/lib/kind.class.php');
	$kind_config = file_get_contents($KIND_CONFIG_PATH);
	if($kind_config === false) {
		Response::error(404, $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found: KIND config.');
	}
	$kind = new Kind(json_decode($kind_config, true));

	####################################
	# DEFINE ROUTES
	####################################

	/** GET all REST routes */
	$router->map('GET', '/', function () {
		global $router;
		Response::result(array('status' => true, 'data' => $router->getRoutes()));
	}, 'List all available routes.');

	$router->map('GET', '/subscription/codes/', function () {
		global $kind;
		Response::result($kind->getSubscriptionStatusCodeMap());
	}, 'Subscription codes mapped to textual representation.');

	/** Dump of subscriber-info for service [i:id] */
	$router->map('GET', '/service/[i:serviceId]/subscribers/', function ($serviceId) {
		global $kind;
		Response::result($kind->getServiceSubscribers($serviceId));
	}, 'Get subscription data for all subscribers.');

	/** List of emails (teknisk kontakt) for service [i:id] */
	$router->map('GET', '/service/[i:serviceId]/mailinglist/', function ($serviceId) {
		global $kind;
		Response::result($kind->getServiceMailingList($serviceId));
	}, 'Get list of emails (teknisk kontakt) for service.');

	/** Subscriber-info for org [a:org] for service [i:id] */
	$router->map('GET', '/service/[i:serviceId]/org/[*:orgId]/', function ($serviceId, $orgId) {
		global $kind;
		Response::result($kind->getServiceOrgSubscriber($serviceId, $orgId));
	}, 'Get subscription data for selected org subscribers.');

	####################################
	# MATCH AND EXECUTE REQUESTED ROUTE
	####################################

	$match = $router->match();

	if($match && is_callable($match['target'])) {
		sanitizeInput();
		call_user_func_array($match['target'], $match['params']);
	} else {
		Response::error(404, $_SERVER["SERVER_PROTOCOL"] . " The requested resource route could not be found.");
	}

	####################################
	# UTILS
	####################################

	// http://stackoverflow.com/questions/4861053/php-sanitize-values-of-a-array/4861211#4861211
	function sanitizeInput() {
		$_GET  = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
		$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	}