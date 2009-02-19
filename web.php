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

        // stuff all output in case the broken catcher is broken
        ob_start();

        // The old page failed, create a new one
        SitePage::setCurrent(new ExceptionPage($ex));
        SitePage::renderCurrent();

        // it managed to do its thing, so let it through
        ob_end_flush();
    } catch (Exception $rex) {
        // Throw away any output that the failed exception handling created
        @ob_end_clean();

        // Hopelessly broken policy/theme/whatever, we'll just do it ourselves
        $l = array(
            'Broken exception handler' => $rex,
            'Original exception' => $ex
        );
        foreach ($l as $title => $e) {
            print "<p><h1>$title:</h1>\n";
            print $e->getMessage()." at ".htmlentities($e->getFile().':'.$e->getLine())."<br />\n";
            $i = 0;
            print "Trace:<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\">\n";
            foreach ($e->getTrace() as $frame) {
                $i++;
                $what = '';
                if (array_key_exists('class', $frame) && array_key_exists('type', $frame)) {
                    $what .= $frame['class'].$frame['type'];
                }
                print "<tr><td>$i</td><td>".htmlentities($what.$frame['function'])."</td><td>";
                if (array_key_exists('file', $frame) && array_key_exists('line', $frame)) {
                    print htmlentities($frame['file']).':'.$frame['line'];
                } else {
                    print "-- Unknown --";
                }
                print "</td></tr>\n";
            }
            print "</table></p>\n";
        }
    }
}

if ($config->isDebugMode()) {
    $pageTime = (microtime(true) - $__start);
    $message = '==> Request Served in ' . (sprintf('%.4f', $pageTime)) . ' seconds; ';
    if (isset($database)) {
        $databaseTime = $database->getTotalTime();
        $databaseQueries = $database->getTotalQueries();
        $message .= $database->getTotalQueries() . ' queries executed in ';
        $message .= sprintf('%.4f', $database->getTotalTime()) . ' seconds, ';
        $message .= sprintf('%.2f', (($databaseTime / $pageTime) * 100)) . '% of total time <==';
    }

    $config->info($message);
}

?>
