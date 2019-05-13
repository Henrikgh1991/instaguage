<?php

require_once "../../app-config.php";
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use ArangoDBClient\Connection as ArangoConnection;
use ArangoDBClient\ConnectionOptions as ArangoConnectionOptions;
use ArangoDBClient\UpdatePolicy as ArangoUpdatePolicy;

$connectionOptions = array(
    ArangoConnectionOptions::OPTION_DATABASE      => AppConfig::config["db"]["database"],
    ArangoConnectionOptions::OPTION_ENDPOINT      => AppConfig::config["db"]["endpoint"],
    ArangoConnectionOptions::OPTION_AUTH_TYPE     => 'Basic',
    ArangoConnectionOptions::OPTION_AUTH_USER     => AppConfig::config["db"]["username"],
    ArangoConnectionOptions::OPTION_AUTH_PASSWD   => AppConfig::config["db"]["password"],
    ArangoConnectionOptions::OPTION_CONNECTION    => 'Keep-Alive',
    ArangoConnectionOptions::OPTION_TIMEOUT       => 3,
    ArangoConnectionOptions::OPTION_RECONNECT     => true,
    ArangoConnectionOptions::OPTION_CREATE        => true,
    ArangoConnectionOptions::OPTION_UPDATE_POLICY => ArangoUpdatePolicy::LAST,
);

$connection = new ArangoConnection($connectionOptions);
