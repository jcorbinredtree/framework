<?php

global $config, $current, $database;

$config = new Config();

$_SESSION = array();

require_once "$config->fwAbsPath/lib/application/Application.php";

Application::start();

if (function_exists('onConfig')) {
    onConfig($config);
}

$database = new Database($config->getDatabaseInfo());
$database->log = $database->time = true;

$current = new Current();

?>