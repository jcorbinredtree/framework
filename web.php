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

try {    
    Application::startWeb();
}
catch (Exception $ex) {
    try {
        LifeCycleManager::onException($ex);

        @ob_end_clean();

        # stuff all output in case the broken catcher is broken
        ob_start();

        $policy = PolicyManager::getInstance();
        $theme = $policy->getExceptionTheme();
        $layout = new LayoutDescription();
        $layout->content = $ex;
        $theme->onDisplay($layout);

        print $theme->getBuffer();

        # it managed to do its thing, so let it through
        ob_end_flush();
    } catch (Exception $rex) {
        # Throw away any output that the failed exception handling created
        @ob_end_clean();

        # Hopelessly broken policy/theme/whatever, we'll just do it ourselves
        $l = array(
            'Broken exception handler' => $rex,
            'Original exception' => $ex
        );
        foreach ($l as $title => $e) {
            print "<p><h1>$title:</h1>\n";
            print $e->getMessage() . "<br />\n";
            print "Trace:<ol>\n";
            print "  <li>".htmlentities($e->getFile()).':'.$e->getLine()."</li>\n";
            foreach ($e->getTrace() as $frame) {
            print "  <li>".htmlentities($frame['file']).':'.$frame['line']."</li>\n";
            }
            print "</ol></p>\n";
        }
    }
}

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
