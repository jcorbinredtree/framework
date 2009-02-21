<?php

/**
 * Site definition
 *
 * PHP version 5
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
 * @category     Site
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/util/CallbackManager.php';
require_once 'lib/application/Application.php';
require_once 'Config.php';

/**
 * A site has:
 *   pages
 *
 * TODO Coming soon to a Site near you:
 *   database
 *   configuration
 *   components
 *   a life
 *   a policy
 *   a current request
 *
 * Example usage in inedx.php:
 *   require_once('SITE/framework/lib/site/Site.php');
 *   class MySite extends Site { ... }
 *   Site::set('MySite')
 *   Site::Site()->handleRequest()
 *
 * @package Site
 */
abstract class Site extends CallbackManager
{
    /**
     * Static management
     */

    private static $TheSite;

    final public static function Site()
    {
        if (! isset(self::$TheSite)) {
            throw new RuntimeException('site not started');
        }
        return self::$TheSite;
    }

    final public static function set($class)
    {
        assert(is_subclass_of($class, 'Site'));
        self::$TheSite = new $class();
    }

    /**
     * Instances methods/properties
     */

    /**
     * @var Config
     */
    public $config;

    private function __construct()
    {
        $start = array(microtime(true), 'start');
        $this->timePoint('start');

        // TODO allow for changable config class name?
        global $config; // compatability global
        $config = $this->config = new Config();
        $this->onConfig();

        $this->timing = $config->isDebugMode();
        if ($this->timing) {
            array_push($this->timePoints, $start);
        }

    }

    private $timing = false;
    private $timePoints = array();

    public function timePoint($what)
    {
        if ($this->timing) {
            // TODO deal with integer microseconnds intsead of floating seconds
            array_push($this->timePoints, array(microtime(true), $what));
        }
    }

    public function timingReport()
    {
        if (! $this->timing) {
            return;
        }
        $this->timePoint('done');

        $start = $this->timePoints[0];
        $end = $this->timePoints[count($this->timePoints)-1];
        $pageTime = $end[0] - $start[0];
        $message = sprintf('==> Request Served in %.4f seconds; ', $pageTime);

        global $database;
        if (isset($database)) {
            // TODO Let Database generate its own statistic info
            $databaseTime = $database->getTotalTime();
            $databaseQueries = $database->getTotalQueries();
            $message .= sprintf('%d queries executed in %.4f seconds, %.2f%% of total time',
                $database->getTotalQueries(),
                $database->getTotalTime(),
                $databaseTime / $pageTime * 100
            );
        }
        $message .= ' <==';

        $this->config->info($message);
    }

    final public function doWeb()
    {
        try {
            Application::startWeb();
            $this->timingReport();
        } catch (Exception $ex) {
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
                $this->exceptionHandlerOfLastResort($req, $ex);
            }
        }
    }

    final public function doWebLite()
    {
        // TODO convert
        require_once dirname(__FILE__) . '/web-lite.php';
    }

    final public function doTests()
    {
        // TODO convert
        require_once dirname(__FILE__) . '/tests.php';
    }

    final public function doCli()
    {
        // TODO convert
        require_once dirname(__FILE__) . '/cli.php';
    }

    abstract public function onConfig();

    /**
     * yes it's got a long name
     * yes it's supposed to be a pain in the ass
     * no you shouldn't touch it
     */
    final private function exceptionHandlerOfLastResort($rex, $ex)
    {
        // Hopelessly broken policy/theme/whatever, we'll just do it ourselves
        $l = array(
            'Broken exception handler' => $rex,
            'Original exception' => $ex
        );
        print
            "<html><head>\n".
            "  <title>Broken exception handler</title>\n".
            "</head><body>\n\n";
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
            print "</table></p>\n\n";
        }
        print "</body></html>\n";
    }
}

?>
