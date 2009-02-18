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


Application::start();
Main::startSession();

$current = new Current();

// This function should be defined in the site's index.php
if (function_exists('onConfig')) {
    onConfig($config);
}

/**
 * Two of three global variables. The entire
 * application revolves around the database,
 * so a good database class is indispensible.
 * Note that the logging and timing of queries
 * is set to correspond with the value of
 * $config->debug.
 *
 * @global Database $database
 * @see Database
 */
$database = new Database();
$database->log = $database->time = $config->isDebugMode();

$config->initalize();

// Load a user if there is one to load
Main::loadUser();

Main::setLanguageAndTheme();

// set appropriate path
{
    $bt = debug_backtrace();
    $bt = $bt[count($bt) - 1];
    Application::setPath(dirname($bt['file']));
}

?>
