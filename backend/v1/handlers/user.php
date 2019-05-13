<?php

require_once "BaseHandler.php";

class user extends BaseHandler {

	public function http_get($request, $input) {
		global $_SESSION;
		$retval = null;

		if(isset($_SESSION["user"])) {
			$retval = $_SESSION["user"];
			$user = $retval;
		} else {
			return $retval;
		}

		return $retval;
	}

	public function http_post($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		if(!isset($_SESSION["user"]) || ($_SESSION["user"] == null)) {
			$retval->ok = false;
			$retval->msg = "Authorization failed.";
			return $retval;
		}

		if(in_array("logout", $request)) {
			unset($_SESSION["user"]);
			session_destroy();
			$retval->ok = true;
			$retval->msg = "You have successfully been logged out.";
		}

		return $retval;
	}
}
