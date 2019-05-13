<?php

require_once "IHandler.php";

class BaseHandler implements IHandler {	
	protected $connection = null;
	protected $user = null;

	function __construct() {
	}

	protected function hasNamespace($ns) {
		global $_SESSION;
		if(!isset($_SESSION["profile"]->namespaces)) return false;
		if(!in_array($ns, $_SESSION["profile"]->namespaces)) return false;
		return true;
	}

	protected function csrfTokenValid() {
		$retval = false;
		$headerName = "HTTP_X_CSRF_TOKEN";

		// if there's a token to compare, it's not ok
		if(!isset($_SESSION["csrfToken"])) {
			return false; 
		}

		$headers = $_SERVER; //getallheaders();

		// no token in header -> not ok
		if(!isset($headers[$headerName])) {
			return false;
		}

		// header and session token match => ok
		if(trim($headers[$headerName]) == trim($_SESSION["csrfToken"])) {
			$retval = true;
		}

		return $retval;
	}

	protected function getItems($aql, $params) {
		$stmt = new \ArangoDBClient\Statement($this->connection, [
			"query" => $aql,
			"bindVars" => $params
		]);

		$cursor = $stmt->execute();
		return $cursor->getAll();
	}

	public function setConnection($connection) {
		$this->connection = $connection;
		return $this;
	}

	public function setUser($user) {
		$this->user = $user;
		return $this;
	}

	public function http_get($request, $input) {
		return null;
	}

	public function http_post($request, $input) {
		return null;
	}

	public function http_delete($request, $input) {
		return null;
	}

	public function http_put($request, $input) {
		return null;
	}
}
