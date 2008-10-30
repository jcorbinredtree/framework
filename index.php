<?php

/**
 * Main page
 *
 * PHP version 5.3+
 *
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
 *
 * @category     Application
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

$__start = microtime(true);

require dirname(__FILE__) . '/Config.php';

/**
 * One of three global variables, the config
 * holds all of the configuration information
 * such as absolute path and uri. Additionally,
 * it implements a logger, so you can say things
 * like $config->warn("problem").
 * 
 * @global Config $config
 * @see Config
 */
$config = new Config();
require "$config->fwAbsPath/lib/application/Application.php";

if (function_exists('onConfig')) {
    onConfig($config);
}

Application::start();

if ($config->isDebugMode()) {
    $pageTime = (microtime(true) - $__start);
    $databaseTime = $database->getTotalTime();
    $databaseQueries = $database->getTotalQueries();
    $message = '==> Request Served in ' . (sprintf('%.4f', $pageTime)) . ' seconds; ';
    $message .= $database->getTotalQueries() . ' queries executed in ';
    $message .= sprintf('%.4f', $database->getTotalTime()) . ' seconds, ';
    $message .= sprintf('%.2f', (($databaseTime / $pageTime) * 100)) . '% of total time <==';
    
    $config->info($message);
}

?>
