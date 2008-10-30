<?php

require_once dirname(__FILE__) . "/../Config.php";

global $config, $current, $database;

$config = new Config();
$config->setTestMode(true);

$_SESSION = array();

require_once "$config->absPath/lib/application/Application.php";
        
Application::requireMinimum();

$database = new Database($config->testDsn);
$database->log = $database->time = true;

$current = new Current();

if (!class_exists('UnitTestCase')) {
    require_once "$config->absPath/extensions/simpletest/autorun.php";
}

require_once "$config->absPath/lib/tests/FrameworkTestCase.php";

?>