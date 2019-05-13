<?php

require_once "BaseHandler.php";

class whatsnew extends BaseHandler {

	private function isAccessOK() {
		if(!$this->csrfTokenValid()) {
			throw new \Exception("Invalid CSRF token.");
		}			
		if($this->user == null) {                                                                                                                                                                          
			throw new \Exception("No user information.");
		}
	}

	private function get_latest(&$retval) {
		$params = [ ];

		$aql = <<<AQL
		FOR w IN whatsNew
			FILTER w.active
			SORT w.createdAt DESC
				LIMIT 5
					RETURN w
AQL;

		$items = $this->getItems($aql, $params);

		$retval->items = [];
		foreach($items as $item) {
			$retval->items[] = json_decode($item->toJson());
		}

		$retval->ok = true;
	}

	private function get_status(&$retval) {
		$params = [ 
			"gid" => ($this->user ? $this->user["gid"] : '')
		];

		$aql = <<<AQL
		LET latestUserNews = DOCUMENT("userSettings", @gid).latestWhatsNewCreatedAt
		FOR w IN whatsNew
			FILTER w.active
			SORT w.createdAt DESC
				LIMIT 1
					RETURN @gid ? (w.createdAt > latestUserNews) : false
AQL;

		$items = $this->getItems($aql, $params);

		$retval->item = null;
		foreach($items as $item) {
			$retval->item = $item; //json_decode($item->toJson());
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
			case "status":
				$this->get_status($retval);
				break;

			case "latest":
				$this->get_latest($retval);
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

	private function post_statusUpdate(&$retval) {
		if($this->user == null) return;

		$params = [ 
			"gid" => $this->user["gid"]
		];

		$aql = <<<AQL
		LET latestNewsCreatedAt = FIRST(FOR w IN whatsNew FILTER w.active SORT w.createdAt DESC LIMIT 1 RETURN w.createdAt)
		UPSERT { _key : @gid }
			INSERT {
				_key : @gid,
				createdAt : DATE_NOW(),
				lastUpdatedAt : DATE_NOW(),
				latestWhatsNewCreatedAt : latestNewsCreatedAt
			}
			UPDATE {
				lastUpdatedAt : DATE_NOW(),
				latestWhatsNewCreatedAt : latestNewsCreatedAt
			} IN userSettings
AQL;

		$items = $this->getItems($aql, $params);
	}

	public function http_post($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		try {
			$verb = end($request);
			switch($verb) {
			case "statusUpdate":
				$this->isAccessOK();
				$this->post_statusUpdate($surveyId, $pw, $retval);
				$this->get_status($retval);
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
