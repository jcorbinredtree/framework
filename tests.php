<?php

require_once dirname(__FILE__) . '/cli.php'; // include the cli setup

$config->setTestMode(true);

$database = new Database($config->getDatabaseInfo());
$database->log = $database->time = true;

$current = new Current();

if (!class_exists('UnitTestCase')) {
    require_once "$config->fwAbsPath/extensions/simpletest/autorun.php";
}

require_once "$config->fwAbsPath/lib/tests/FrameworkTestCase.php";

?>