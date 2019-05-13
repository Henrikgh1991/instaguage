<?php

if(!isset($user)) {
	$appSessionId = session_id();

	require_once('simplesamlphp/lib/_autoload.php');                                                                                                                                                                   
	$sp = "default-sp";                                                                                                                                                                                                
	$as = new SimpleSAML_Auth_Simple($sp);
	$as->requireAuth(array());

	$attributes = $as->getAttributes();

	$user = array(
		"gid" => strtolower($_SESSION["samlUserdata"]["gid"][0]),
		"lastName" => $_SESSION["samlUserdata"]["sn"][0],
		"firstName" => $_SESSION["samlUserdata"]["givenname"][0],
		"email" => $attributes["email"][0],
		"name" => $attributes["givenname"][0] . " " . $attributes["sn"][0]
	);

	session_id($appSessionId);
	session_reset();
}
