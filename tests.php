<?php
/**
 * LICENSE: The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 *
 * The Original Code is Red Tree Systems Code.
 *
 * The Initial Developer of the Original Code is Red Tree Systems, LLC. All Rights Reserved.
 */

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
