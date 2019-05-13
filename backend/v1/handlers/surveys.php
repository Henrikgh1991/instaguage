<?php

require_once "BaseHandler.php";
Twig_Autoloader::register();

class surveys extends BaseHandler {
	protected $twig = null;

	function __construct() {
		$loader = new Twig_Loader_Filesystem('templates');
		$this->twig = new Twig_Environment($loader, array(
					'cache' => 'templates/cache',
					'auto_reload' => true
				));
	}
	
	private function isAccessOK() {
		if(!$this->csrfTokenValid()) {
			die("go away.");
		}			
		if($this->user == null) {                                                                                                                                                                          
			die("not logged in");
		}
	}

	public function getSurveyWithResults($id, $pw) {
		$aql = <<<AQL
		LET s = DOCUMENT("surveys", @id)
			RETURN MERGE({ timestamp : DATE_NOW() }, UNSET(s, [ ]), { votes : [] })
AQL;

		$bindVars = [
			"id" => $id,
		];

		$stmt = new ArangoDBClient\Statement($this->connection, [
			"query" => $aql,
			"bindVars" => $bindVars
		]);
		$cursor = $stmt->execute();
		$item = $cursor->getAll()[0];

		if($item !== null) {
			$item = json_decode($item->toJson());
			$storedPw = $item->password;
			unset($item->password);

			if(($pw === null) && property_exists($item, "noPublicResults") && ($item->noPublicResults === true)) {
				if(($this->user === null) || ($this->user["gid"] != $item->user->gid)) {
					throw new \Exception("no public results");
				}
			}

			if(($pw === null) || ($pw == $storedPw)) {
				// only retrieve votes when needed
				$aql = <<<AQL
				FOR v IN votes
					FILTER v.surveyId == @id
						SORT v.createdAt DESC
							FOR qid IN ATTRIBUTES(v.votes)
								RETURN { qid : qid, vote : v.votes[qid].value, vid : v._key }
AQL;

				$bindVars = [
					"id" => $id,
				];

				$stmt = new ArangoDBClient\Statement($this->connection, [
					"query" => $aql,
					"bindVars" => $bindVars,
					'_flat' => true,
					'batchSize' => 1000,
					'fullCount' => true
				]);
				$cursor = $stmt->execute();

				$voteMap = [];

				foreach($item->questions as $q) {
					$votes = new \stdClass();
					$votes->total = 0;
					$voteMap[$q->id] = [ "q" => $q, "votes" => $votes ];
				}

				// required for paging, but is EVIL: violates encapsulation; TODO: patch upstream
				$r = new ReflectionObject($cursor);
				$posP = $r->getProperty('_position');
				$posP->setAccessible(true);
				$lenP = $r->getProperty('_length');
				$lenP->setAccessible(true);
				$resP = $r->getProperty('_result');
				$resP->setAccessible(true);

				// get number of single votes to process
				$numItems = $cursor->getExtra()["stats"]["fullCount"];
				$maxVotes = 0;

				while($numItems--) {
					$objV = $cursor->current();
					$cursor->next();

					// manual and invasive paging of DB data
					if($posP->getValue($cursor) >= $lenP->getValue($cursor)) {
						$resP->setValue($cursor, []); // clear current batch of data
						$cursor->valid(); // get next batch of data from db server
						$cursor->rewind(); // move cursor to beginning of next batch
					}

					$v = json_decode(json_encode($objV)); // create object from single db data (recursively)

					$data = $voteMap[$v->qid];
					$votes = $data["votes"];
					$votes->total++;
					switch($data["q"]->type) {
					case "multipleChoice":
						foreach($v->vote as $subvote) {
							if(!property_exists($votes, $subvote->id)) {
								$votes->{$subvote->id} = 0;
							}
							$votes->{$subvote->id}++;							
						}
						break;

					case "scale":
						if(!property_exists($votes, $v->vote->id)) {
							$votes->{$v->vote->id} = 0;
						}
						$votes->{$v->vote->id}++;
						break;

					case "wordcloud":
						if(!property_exists($votes, "words")) {
							$votes->words = [];
						}
						foreach($v->vote as $c) {
							$w = $c->content;
							if(!isset($votes->words[$w])) {
								$votes->words[$w] = 0;
							}
							$votes->words[$w]++;
						}
						break;

					case "matrix":
						if(!property_exists($votes, "points")) {
							$votes->points = [];
						}
						if(isset($data["q"]->params->mode) && ($data["q"]->params->mode == 'items')) {
							foreach($v->vote as $vid => $p) {
								if(!isset($votes->points[$vid])) {
									$votes->points[$vid] = [
										"count" => 0,
										"point" => [ "x" => 0, "y" => 0 ]
									];
								}
								$votes->points[$vid]["count"]++;
								$votes->points[$vid]["point"]["x"] += $p->x;
								$votes->points[$vid]["point"]["y"] += $p->y;
							}
						} else {
							$vid = "|" . $v->vote->x . "|" . $v->vote->y . "|";
							if(!isset($votes->points[$vid])) {
								$v->vote->count = 0;
								$votes->points[$vid] = $v->vote;
							}
							$votes->points[$vid]->count++;
						}
						break;
					
					case "order":
						if(!property_exists($votes, $v->qid)) {
							$votes->{$v->qid} = [ "total" => 0 ];
						}
						foreach($v->vote as $i => $id) {
							$id = $id->id;
							if(!isset($votes->{$v->qid}[$id])) {
								$votes->{$v->qid}[$id] = 0;
							}
							$votes->{$v->qid}[$id] += $i;
						}
						$votes->{$v->qid}["total"]++;
						break;


					case "freeText":
						if(!property_exists($votes, $v->vote->id)) {
							$votes->{$v->vote->id} = [];
						}
						if(strlen(trim($v->vote->content)) == 0) {
							$votes->total--;
						} else {
							$votes->{$v->vote->id}[] = trim($v->vote->content);
						}
						break;
					}
				}

				foreach($item->questions as $q) {
					$maxVotes = max($maxVotes, $voteMap[$q->id]["votes"]->total);

					if($q->type == "order") {
						$votes = $voteMap[$q->id]["votes"];
						$tmp = $votes->{$q->id};
						unset($tmp["total"]);
						asort($tmp);
						$votes->{$q->id} = array_keys($tmp);
						$votes->rawData = [];
						foreach($tmp as $rawKey => $sum) {
							foreach($q->options as $optionItem) {
								if($optionItem->id == $rawKey) {
									$votes->rawData[] = [ "title" => $optionItem->title, "id" => $optionItem->id, "value" => $sum ];
								}
							}
						}
					}
					
					if($q->type == "matrix") {
						if(isset($q->params->mode) && ($q->params->mode == 'items')) {
							$qMap = [];
							foreach($q->items as $v) {
								$qMap[$v->id] = $v;
							}
							$newMap = [];
							$votes = $voteMap[$q->id]["votes"];
							foreach($votes->points as $vid => $v) {
								$v["point"]["x"] /= $v["count"];
								$v["point"]["y"] /= $v["count"];
								$v["title"] = $qMap[$vid]->title;
								$votes->points[$vid] = $v;
							}
						}
					}

					if(($voteMap[$q->id]["votes"]->total < 5) && ($maxVotes < 5)) {
						$q->votes = [
							"total" => $voteMap[$q->id]["votes"]->total
						];
					} else {
						$q->votes = $voteMap[$q->id]["votes"];
					}
				}
			}

			unset($item->votes);
		}

		// $item->memUsage = ceil(memory_get_peak_usage() / (1024*1024));

		return $item;
	}

	public function get_mine(&$retval) {
		$params = [
			"gid" => $this->user["gid"]
		];

		$retval->user = $this->user;

		$aql = <<<AQL
		FOR s IN surveys
			LET age = DATE_DIFF(s.createdAt, DATE_NOW(), "d")
			FILTER (s.user != null) AND (s.user.gid == @gid) AND (age <= 180)
				SORT s.createdAt DESC
					RETURN MERGE(s, {						
					})
AQL;

		$items = $this->getItems($aql, $params);

		$retval->items = [];
		foreach($items as $item) {
			$retval->items[] = json_decode($item->toJson());
		}

		$retval->ok = true;
	}

	private function transformAnswer($type, &$a) {
		switch($type) {
		case "scale":
			$a = $a->title;
			break;

		case "multipleChoice":
			$a = implode(";", array_map(function($i) {
				return $i->title;
			}, $a));
			break;

		case "wordcloud":
			$a = implode(";", array_map(function($i) {
				return "[" . $i->content . "]";
			}, $a));
			break;

		case "matrix":
			if(property_exists($a, "x")) {
				$a = $a->x . "/" . $a->y;
			} else {
				$b = [];
				foreach($a as $k => $v) {
					$b[] = $v->x . "/" . $v->y;
				}
				$a = implode(";", $b);
			}
			break;

		case "order":
			$a = implode(";", array_map(function($i) {
				return "[" . $i->title . "]";
			}, $a));
			break;

		case "freeText":
			$a = $a->content;
			break;
		}
	}

	private function get_sheetData($id, $pw, &$retval) {
		$time = time();

		$aql = <<<AQL
		LET s = DOCUMENT('surveys', @id),
			numVotes = LENGTH(FOR v IN votes FILTER v.surveyId == s._key RETURN 1)
			FILTER (s.password == @pw) AND (numVotes >= 5)
				FOR q IN s.questions
					FILTER q.type NOT IN [ "heading" ]
					LET a = (FOR v IN votes
								FILTER v.surveyId == s._key
									RETURN v.votes[q.id].value)
					RETURN {
						title : q.title,
						answers : a,
						type : q.type
					}
AQL;

		$bindVars = [
			"id" => $id,
			"pw" => $pw
		];

		$stmt = new ArangoDBClient\Statement($this->connection, [
			"query" => $aql,
			"bindVars" => $bindVars
		]);
		$cursor = $stmt->execute();
		$items = $cursor->getAll();

		$retval = [];
		foreach($items as $item) {
			$retval[] = json_decode($item->toJson());
		}

		foreach($retval as $item) {
			foreach($item->answers as &$a) {
				$this->transformAnswer($item->type, $a);
			}
		}

		$f = fopen('php://memory', 'wb+');
		fwrite($f, chr(239) . chr(187) . chr(191)); // utf8 bom
		foreach($retval as $item) {
			$items = [];
			$items[] = preg_replace('/[\n\r]/', '', $item->title);
			foreach($item->answers as $a) {
				$items[] = preg_replace('/[\n\r]/', '', $a);
			}
			fputcsv($f, $items);
		}
		fseek($f, 0);

		header("Content-Type: text/csv; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"survey_sheets_${id}_${time}.csv\"");
		fpassthru($f);

		die();
	}

	public function http_get($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		$verb = end($request);

		try {
			switch($verb) {
			case "sheetData":
				// only verified Siemens users may download the results
				if($this->user == null) {
					header("Content-Type: text/html");
					die("not logged in.");
				}

				$pw = null;

				if(isset($_REQUEST["accessToken"])) {
					$accessToken = $_REQUEST["accessToken"];
					if(isset($_SESSION["accessTokens"])) {
						$accessTokens = $_SESSION["accessTokens"];
						if(isset($accessTokens[$accessToken])) {
							$accessData = $accessTokens[$accessToken];
							if(time() - $accessData["createdAt"] < 30) {
								$pw = $accessData["pw"];
							}
							unset($accessTokens[$accessToken]);
							$_SESSION["accessTokens"] = $accessTokens;
						}
					}
				}

				if($pw == null) {
					die("invalid credentials.");
				}

				$id = prev($request);
				$this->get_sheetData($id, $pw, $retval);
				break;

			case "exportResults":
				// only verified Siemens users may download the results
				if($this->user == null) {
					header("Content-Type: text/html");
					die("not logged in.");
				}

				$pw = null;

				if(isset($_REQUEST["accessToken"])) {
					$accessToken = $_REQUEST["accessToken"];
					if(isset($_SESSION["accessTokens"])) {
						$accessTokens = $_SESSION["accessTokens"];
						if(isset($accessTokens[$accessToken])) {
							$accessData = $accessTokens[$accessToken];
							if(time() - $accessData["createdAt"] < 30) {
								$pw = $accessData["pw"];
							}
							unset($accessTokens[$accessToken]);
							$_SESSION["accessTokens"] = $accessTokens;
						}
					}
				}

				$id = prev($request);
				$time = time();
				$survey = $this->getSurveyWithResults($id, $pw);

				if(($pw === null) && ($this->user["gid"] != $survey->user->gid)) {
					die("only survey owner may export results");
				}
	
				if(true) {
					header("Content-Disposition: attachment; filename=\"survey_${id}_${time}.xml\"");
					header("Content-Type: application/vnd.ms-excel; charset=utf-8");
				} else {
					header("Content-Type: text/plain; charset=utf-8");
				}

				$this->twig->addFilter( new Twig_SimpleFilter('cast_to_array', function ($stdClassObject) {
					$response = array();
					foreach ($stdClassObject as $key => $value) {
						$response[$key] = $value;
					}
					return $response;
				}));
				
				$fileContent = $this->twig->render("excel.xml", array(
					"survey" => $survey,
					"user" => $this->user
				));

				die($fileContent);
				break;

			case "details":
				$pw = "";
				if(isset($_SERVER["HTTP_X_SURVEY_PW"])) {
					$pw = $_SERVER["HTTP_X_SURVEY_PW"];
				}
				$id = prev($request);
				$retval->survey = $this->getSurveyWithResults($id, $pw);
				$retval->ok = true;
				break;

			case "results":
				$id = prev($request);
				$retval->survey = $this->getSurveyWithResults($id, null);
				$retval->ok = true;
				break;

			case "mine":
				$this->get_mine($retval);
				break;

			default:
				$retval->msg = "unhandled verb ${verb}";
				break;
			}
		} catch(\Exception $e) {
			$retval->ok = false;
			$retval->msg = $e->getMessage();
		}

		return $retval;
	}

	private function createSurvey($s) {
		$key = openssl_random_pseudo_bytes(4);
		$pw = bin2hex(openssl_random_pseudo_bytes(8));

		$base62 = new Tuupola\Base62;
		$key = $base62->encode($key);

		$aql = <<<AQL
		INSERT MERGE(@s, {
			_key : @key,
			password : @pw,
			createdAt : DATE_NOW(),
			user : @user
		}) INTO surveys RETURN NEW
AQL;

		$stmt = new ArangoDBClient\Statement($this->connection, [
			"query" => $aql,
			"bindVars" => [
				"key" => $key,
				"pw" => $pw,
				"s" => $s,
				"user" => $this->user
			]
		]);
		$cursor = $stmt->execute();
		$item = json_decode($cursor->getAll()[0]->toJson());

		return $item;
	}

	private function cloneSurvey($sourceId, $sourcePw) {
		$key = openssl_random_pseudo_bytes(4);
		$pw = bin2hex(openssl_random_pseudo_bytes(8));

		$base62 = new Tuupola\Base62;
		$key = $base62->encode($key);

		$aql = <<<AQL
		FOR s IN surveys
			FILTER (s._key == @sourceId) AND (s.password == @sourcePw)
				INSERT MERGE(UNSET(s, [ "noPublicResults" ]), {
					_key : @key,
					password : @pw,
					sourceKey : @sourceId,
					createdAt : DATE_NOW(),
					user : @user,
					status : "draft",
					title : s.title ? CONCAT(s.title, " (clone)") : ""
				}) INTO surveys RETURN NEW
AQL;

		$stmt = new ArangoDBClient\Statement($this->connection, [
			"query" => $aql,
			"bindVars" => [
				"key" => $key,
				"pw" => $pw,
				"sourceId" => $sourceId,
				"sourcePw" => $sourcePw,
				"user" => $this->user
			]
		]);
		$cursor = $stmt->execute();
		$item = json_decode($cursor->getAll()[0]->toJson());

		return $item;
	}

	private function toggleVoting($id, $pw) {
		$aql = <<<AQL
		FOR s IN surveys
			FILTER (s._key == @key) AND (s.password == @pw)
				UPDATE s WITH {
					votingDisabled : (s.votingDisabled ? !s.votingDisabled : true)
				} IN surveys
AQL;

		$stmt = new ArangoDBClient\Statement($this->connection, [
			"query" => $aql,
			"bindVars" => [
				"key" => $id,
				"pw" => $pw
			]
		]);
		$cursor = $stmt->execute();
	}
	
	public function http_post($request, $input) {
		$retval = new stdClass();
		$retval->ok = false;
		$retval->msg = "Success.";

		$verb = end($request);

		switch($verb) {
		case "toggleVoting":
			$this->isAccessOK();
			$id = prev($request);
			$pw = $_SERVER["HTTP_X_SURVEY_PW"];
			$this->toggleVoting($id, $pw);
			$retval->survey = $this->getSurveyWithResults($id, $pw);
			$retval->ok = true;
			break;
		
		case "clone":
			$this->isAccessOK();
			$retval->survey = $this->cloneSurvey(prev($request), $_SERVER["HTTP_X_SURVEY_PW"]);
			$retval->ok = true;
			break;

		case "create":
			$this->isAccessOK();
			if(!isset($input["survey"])) {
				die("no survey");
			}

			$survey = $input["survey"];
			$retval->survey = $this->createSurvey($survey);
			$retval->ok = true;
			break;

		case "generateAccessToken":
			$this->isAccessOK();
			$id = prev($request);
			$pw = $_SERVER["HTTP_X_SURVEY_PW"];
			$survey = $this->getSurveyWithResults($id, $pw);
			if(!isset($_SESSION["accessTokens"])) {
				$_SESSION["accessTokens"] = [];
			}
			$token = bin2hex(openssl_random_pseudo_bytes(32));
			$_SESSION["accessTokens"][$token] = [ "pw" => $pw, "createdAt" => time() ];
			$retval->item = $token;
			$retval->ok = true;
			break;

		default:
			$retval->msg = "unhandled verb ${verb}";
			break;
		}

		return $retval;
	}
}
