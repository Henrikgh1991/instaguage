<?php

session_start();

require_once "../../login/saml/index.php";

if(isset($_REQUEST["returnTo"])) {
    $_SESSION["user"] = $user;
    header("Location: ${_REQUEST['returnTo']}", true, 307);
} else {
    die("nowhere to returnTo");
}