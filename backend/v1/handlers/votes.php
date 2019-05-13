<?php

require_once "BaseHandler.php";

class votes extends BaseHandler {

	public function http_get($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		$verb = end($request);

		switch($verb) {
		default:
			$retval->msg = "unhandled verb ${verb}";
			break;
		}

		return $retval;
	}

	private function publishMQTT($item) {
		$server = \AppConfig::config["mqtt"]["server"];
		$port = \AppConfig::config["mqtt"]["port"];
		$username = "";                   
		$password = "";                   
		$client_id = "phpMQTT-publisher-instagauge-" . bin2hex(openssl_random_pseudo_bytes(8));	
		$mqtt = new \Bluerhinos\phpMQTT($server, $port, $client_id, "/etc/ssl/certs/");
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$m = new \stdClass();
			$m->type = "survey";
			$m->survey = $item;
			$topic = "instagauge/" . $item->_key;
			$mqtt->publish($topic, json_encode($m), 0);
			$mqtt->close();
		}
	}

	private function saveVote($surveyId, $votes) {
		$aql = <<<AQL
		FOR s IN surveys
			FILTER (s._key == @surveyId) AND (s.votingDisabled != true) AND (s.status != 'draft')
				LET notificationTimeElapsed = FIRST(FOR v IN votes
												FILTER v.surveyId == @surveyId
													SORT v.createdAt DESC
														LIMIT 1
															RETURN FLOOR((DATE_NOW() - v.createdAt) / 1000) > 2)															
				INSERT { 
					surveyId : @surveyId,
					votes : @votes,
					createdAt : DATE_NOW()
				} INTO votes RETURN MERGE({ notificationTimeElapsed : notificationTimeElapsed }, NEW)
AQL;

		$stmt = new ArangoDBClient\Statement($this->connection, [
			"query" => $aql,
			"bindVars" => [
				"surveyId" => $surveyId,
				"votes" => $votes
			]
		]);
		$cursor = $stmt->execute();
		$item = $cursor->getAll()[0];
		if($item != null) {
			$item = json_decode($item->toJson());

			if($item->notificationTimeElapsed) {
				try {
					$this->publishMQTT((new \surveys())->setConnection($this->connection)->getSurveyWithResults($surveyId, null));
				} catch(\Exception $xcp) {}
			}
		}

		return $item;
	}

	public function http_post($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		$verb = end($request);

		switch($verb) {
		case "forceRefresh":
			if(!$this->csrfTokenValid()) {
				die("go away.");
			}
			if(!isset($input["surveyId"])) {
				die("no surveyId");
			}
			try {
				$this->publishMQTT((new \surveys())->setConnection($this->connection)->getSurveyWithResults($input["surveyId"], null));
			} catch(\Exception $e) {
				// don't care
			}
			break;

		case "cast":
			if(!$this->csrfTokenValid()) {
				die("go away.");
			}
			if(!isset($input["surveyId"])) {
				die("no surveyId");
			}
			if(!isset($input["votes"])) {
				die("no votes");
			}
			$retval->item = $this->saveVote($input["surveyId"], $input["votes"]);
			if($retval->item === null) {
				$retval->msg = "Your vote was NOT recorded; maybe voting has been disabled for this poll?";
			}
			$retval->ok = true;
			break;

		default:
			$retval->msg = "unhandled verb ${verb}";
			break;
		}

		return $retval;
	}
}
