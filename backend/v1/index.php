<?php

session_start();

require_once "../../app-config.php";
include_once "db.php";
include_once "phpMQTT.php";

include_once "handlers/user.php";
include_once "handlers/surveys.php";
include_once "handlers/votes.php";
include_once "handlers/admin.php";
include_once "handlers/whatsnew.php";

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_REQUEST['r'],'/'));
$input = file_get_contents('php://input');
$input = json_decode($input, true);

header('Access-Control-Allow-Origin: *');
header("Content-type: application/json");

if(count($request) == 0) {
	return;
}

$retval = null;

$namespace = "";
$classname = $request[0];

if(file_exists("handlers/" . $classname . "/")) {
	$namespace = $classname;
	$classname = "$namespace\\$request[1]";
}

if(class_exists($classname)) {
	$handler = new $classname();
	if(isset($_SESSION["user"])) {
		$handler->setUser($_SESSION["user"]);
	}
} else {
	die("class $classname doesnt exist");
}

$handler->setConnection($connection);
$method = "http_" . $method;

if(method_exists($handler, $method)) {
	$retval = $handler->$method($request, $input);
} else {
	die("method $method in $classname doesnt exist");
}

echo json_encode($retval);
