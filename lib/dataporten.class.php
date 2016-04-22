<?php

   /**
	*
	* @author Simon Skrødal
	* @since  August 2015
	*/
	class Dataporten {

		protected $userName, $isAdmin, $userOrg, $isSuperAdmin, $config;

		function __construct($config) {
			// Exits on OPTION call
			$this->_checkCORS();
			//
			$this->config = $config;
			// Exits on incorrect credentials
			$this->_checkGateKeeperCredentials();
			// Ensure that the client has access to the (moderated) admin scope
			$this->isAdmin  = $this->_hasDataportenScope("admin");
		}
		

		private function _hasDataportenScope($scope) {
			if(!isset($_SERVER["HTTP_X_DATAPORTEN_SCOPES"])) {
				Response::error(401, $_SERVER["SERVER_PROTOCOL"] . ' Unauthorized (missing scope)');
			}
			// Get the scope(s)
			$scopes = $_SERVER["HTTP_X_DATAPORTEN_SCOPES"];
			// Make array
			$scopes = explode(',', $scopes);
			// True/false
			return in_array($scope, $scopes);
		}

		private function _checkCORS() {
			// Access-Control headers are received during OPTIONS requests
			if(strcasecmp ( $_SERVER['REQUEST_METHOD'], "OPTIONS") === 0) {
				Response::result('CORS OK :-)');
			}
		}

		private function _checkGateKeeperCredentials() {
			if(empty($_SERVER["PHP_AUTH_USER"]) || empty($_SERVER["PHP_AUTH_PW"])){
				Response::error(401, $_SERVER["SERVER_PROTOCOL"] . ' Unauthorized (Missing API Gatekeeper Credentials)');
			}

			// Gatekeeper. user/pwd is passed along by the Dataporten Gatekeeper and must matched that of the registered API:
			if( 	( strcmp ($_SERVER["PHP_AUTH_USER"], $this->config['user']) !== 0 ) || 
					( strcmp ($_SERVER["PHP_AUTH_PW"],  $this->config['passwd']) !== 0 ) ) {
				// The status code will be set in the header
				Response::error(401, $_SERVER["SERVER_PROTOCOL"] . ' Unauthorized (Incorrect API Gatekeeper Credentials)');
			}
		}

	}