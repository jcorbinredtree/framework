<?php

global $config, $current, $database;

$config = new Config();

$_SESSION = array();

require_once "$config->fwAbsPath/lib/application/Application.php";
        
Application::requireMinimum();

if (function_exists('onConfig')) {
    onConfig($config);
}

$config->setTestMode(true);

$database = new Database($config->getDatabaseInfo());
$database->log = $database->time = true;

$current = new Current();

if (!class_exists('UnitTestCase')) {
    require_once "$config->fwAbsPath/extensions/simpletest/autorun.php";
}

require_once "$config->fwAbsPath/lib/tests/FrameworkTestCase.php";

?>