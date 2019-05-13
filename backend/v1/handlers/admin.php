<?php

require_once "BaseHandler.php";

class admin extends BaseHandler {

	private function isAccessOK() {
		if(!$this->csrfTokenValid()) {
			throw new \Exception("Invalid CSRF token.");
		}			
		if($this->user == null) {                                                                                                                                                                          
			throw new \Exception("No user information.");
		}
	}

	private function get_details($id, &$retval) {
		$params = [
			"gid" => $this->user["gid"],
			"id" => $id
		];

		$aql = <<<AQL
		FOR s IN surveys
			FILTER (s._key == @id) AND (s.user.gid == @gid) AND (s.status == "draft")
				RETURN s
AQL;

		$items = $this->getItems($aql, $params);

		$retval->item = null;
		foreach($items as $item) {
			$retval->item = json_decode($item->toJson());
		}

		$retval->ok = true;
	}

	public function http_get($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		try {
			$verb = end($request);
			switch($verb) {
			case "details":
				$surveyId = prev($request);
				$this->get_details($surveyId, $retval);
				break;

			default:
				throw new \Exception("unhandled verb ${verb}");
				break;
			}
		} catch(\Exception $e) {
			$retval->ok = false;
			$retval->msg = $e->getMessage();
		}

		return $retval;
	}

	private function put_details($id, $s, &$retval) {
		$params = [
			"user" => $this->user,
			"id" => $id,
			"s" => $s,
			"unsetFields" => [ "user", "createdAt", "_rev", "_id", "password" ],
		];

		$aql = <<<AQL
		FOR s IN surveys
			FILTER (s._key == @id) AND (s.user.gid == @user.gid) AND (s.status == "draft")
				UPDATE s WITH MERGE(UNSET(@s, @unsetFields), {
					lastUpdatedAt : DATE_NOW(),
					user : @user
				}) IN surveys
					RETURN NEW				
AQL;

		$items = $this->getItems($aql, $params);

		$retval->item = null;
		foreach($items as $item) {
			$retval->item = json_decode($item->toJson());
		}

		$retval->ok = true;
	}

	public function http_put($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		try {
			$verb = end($request);
			switch($verb) {
			case "details":
				$this->isAccessOK();
				$surveyId = prev($request);
				$s = $input["survey"];
				$s["status"] = "draft";
				$this->put_details($surveyId, $s, $retval);
				break;

			default:
				throw new \Exception("unhandled verb ${verb}");
				break;
			}
		} catch(\Exception $e) {
			$retval->ok = false;
			$retval->msg = $e->getMessage();
		}

		return $retval;
	}

	public function http_post($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		try {
			$verb = end($request);
			switch($verb) {
			case "details":
				$this->isAccessOK();
				$surveyId = prev($request);
				$s = $input["survey"];
				$s["status"] = "final";
				$this->put_details($surveyId, $s, $retval);
				break;

			default:
				throw new \Exception("unhandled verb ${verb}");
				break;
			}
		} catch(\Exception $e) {
			$retval->ok = false;
			$retval->msg = $e->getMessage();
		}

		return $retval;
	}

	private function delete_details($surveyId, $pw, &$retval) {
		$params = [
			"gid" => $this->user["gid"],
			"id" => $surveyId,
			"pw" => $pw,
		];

		$aql = <<<AQL
		FOR s IN surveys
			FILTER (s._key == @id) AND (s.user.gid == @gid) AND (s.status == "draft") AND (s.password == @pw)
				LIMIT 1
				LET oldVotes = (FOR v IN votes FILTER v._to == s._id REMOVE v IN votes RETURN v)
				REMOVE s IN surveys
					RETURN MERGE(OLD, { votes : oldVotes })
AQL;

		$items = $this->getItems($aql, $params);

		$retval->deletedItem = null;
		foreach($items as $item) {
			$retval->deletedItem = json_decode($item->toJson());
		}

		$retval->ok = true;	
	}

	public function http_delete($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		try {
			$verb = end($request);
			switch($verb) {
			case "details":
				$this->isAccessOK();
				$surveyId = prev($request);
				$pw = "";
				if(isset($_SERVER["HTTP_X_SURVEY_PW"])) {
					$pw = $_SERVER["HTTP_X_SURVEY_PW"];
				}
				$this->delete_details($surveyId, $pw, $retval);
				(new \surveys())->setConnection($this->connection)->setUser($this->user)->get_mine($retval);
				break;

			default:
				throw new \Exception("unhandled verb ${verb}");
				break;
			}
		} catch(\Exception $e) {
			$retval->ok = false;
			$retval->msg = $e->getMessage();
		}

		return $retval;
	}
}
