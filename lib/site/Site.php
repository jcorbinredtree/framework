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
require_once 'lib/util/CurrentPath.php';
require_once 'lib/util/Params.php';
require_once 'lib/site/SiteConfig.php';
require_once 'lib/site/SiteLayout.php';
require_once 'lib/site/SiteLog.php';
require_once 'lib/site/SiteModuleLoader.php';

/**
 * A site has:
 *   configuration
 *   modules
 *
 * Example usage in inedx.php:
 *   require_once('SITE/framework/Loader.php');
 *   class MySite extends Site { ... }
 *   Site::set('MySyte')->handle();
 *
 * @package Site
 */
abstract class Site extends CallbackManager
{
    /**
     * Static management
     */

    /**
     * The site, really, no foolies
     * @var Site
     * @see Site::Site
     */
    private static $TheSite;

    /**
     * Returns $TheSite
     *
     * Site::set must've been called firt to instantiate the site subclass
     *
     * @return Site
     * @see Site::set
     */
    final public static function Site()
    {
        if (! isset(self::$TheSite)) {
            throw new RuntimeException('Site not set');
        }
        return self::$TheSite;
    }

    /**
     * Sets the site class in play and instantiates $TheSite
     *
     * @param class string classname, subclass of Site
     * @return Site the site
     */
    final public static function set($class)
    {
        assert(is_subclass_of($class, 'Site'));
        return new $class();
    }

    /**
     * Returns thi site log, convenience for Site::Site()->log
     *
     * @return SiteLog
     */
    final public static function getLog()
    {
        $site = self::Site();
        if (! isset($site->log)) {
            throw new RuntimeException('no site log');
        }
        return $site->log;
    }

    /**
     * Returns the site config, convenience for Site::Site()->config
     * @return SiteConfig
     */
    final public static function getConfig()
    {
        $site = self::Site();
        if (! isset($site->config)) {
            throw new RuntimeException('no site config');
        }
        return $site->config;
    }

    /**
     * Get a site module
     *
     * @param module string
     * @return SiteModule
     */
    final public static function getModule($module)
    {
        assert(is_string($module));
        $site = self::Site();
        assert(isset($site->modules));
        return $site->modules->get($module);
    }

    /**
     * Convenience utility, if argument is a string, explodes it on ',', other
     * wise the argument should be an array of strings
     *
     * The array is then walked, and each path that is non-absolute, prepended
     * with Loader::$Base. Each path is resolved with realpath() and added
     * to the result list only if it is a directory
     */
    public static function pathArray($path)
    {
        if (is_string($path)) {
            $path = explode(',', $path);
        }
        assert(is_array($path));
        $r = array();
        foreach ($path as $p) {
            if (! preg_match('/^((\w:)?\/|~)/', $p)) {
                $p = Loader::$Base."/$p";
            }
            $p = realpath($p);
            if ($p !== false && is_dir($p)) {
                array_push($r, $p);
            }
        }
        return $r;
    }

    /**
     * Instance methods/properties
     */

    /**
     * Mode flags
     */
    const MODE_DEBUG = 0x01;
    const MODE_TEST  = 0x02;
    const MODE_MAINT = 0x04;

    /**
     * Site mode flags
     *
     * The default is production mode, i.e. not test, not debug, not maint
     *
     * This is an immutable property, it can only be set from the constructor
     *
     * @var int
     */
    protected $mode=0;

    /**
     * The order of config file loading is:
     *
     *   Loader::$FrameworkPath/config.ini
     *   {any config files added by SiteModules}
     *   Loader::$LocalPath/config.ini
     *   Loader::$Base/siteconfig.ini
     *
     * @var SiteConfig
     */
    public $config;

    /**
     * Absolute url for how the client got to the server, has no path
     * component, only protocol, host, and port
     *
     * @var string
     */
    public $serverUrl;

    /**
     * Base url path for where the site lives on $sereverUrl
     *
     * @var string
     */
    public $url;

    /**
     * The url currently being served
     *
     * @var string
     */
    public $requestUrl;

    /**
     * @var SiteModuleLoader
     */
    public $modules;

    /**
     * @var SiteLog
     */
    public $log;

    /**
     * Creates a new site:
     *   starts the timing clock (if enabled)
     *   creates the SiteLayout if not set
     *   sets up config
     */
    private function __construct()
    {
        if (isset(self::$TheSite)) {
            throw new RuntimeException(
                'Site already set to '.get_class(self::$TheSite)
            );
        }
        self::$TheSite = $this;

        $start = array(microtime(true), 'start');
        $this->timePoint('start');

        if (! isset($this->url)) {
            $this->url = dirname($_SERVER['PHP_SELF']);
            if ($this->url == '/') {
                $this->url = '';
            }
        }

        $proto = 'http';
        $port = '';
        if (isset($_SERVER['SERVER_PORT'])) {
            if ($_SERVER['SERVER_PORT'] == 443) {
                $proto = 'https';
            } elseif ($_SERVER['SERVER_PORT'] != 80) {
                $port = ':'.$_SERVER['SERVER_PORT'];
            }
        }
        $this->serverUrl = "$proto://".$_SERVER['SERVER_NAME'].$port;

        if (! isset($this->layout)) {
            $this->layout = new SiteLayout($this);
        }

        // The log starts off in an unconfigured state where it accumulates
        // messages until configuration is done and it's told what to do with
        // them; however if something goes wrong early on, it dumps all logged
        // messages
        $this->log = new SiteLog($this);

        $this->config = new SiteConfig($this);
        $this->config->addFile(Loader::$FrameworkPath.'/config.ini');

        new SiteModuleLoader($this);

        try {
            $this->dispatchCallback('onConfig');
            $this->config->addFile(Loader::$LocalPath.'/config.ini');
            $this->config->addFile(Loader::$Base.'/siteconfig.ini');
            $this->config->compile();
            $this->dispatchCallback('onPostConfig');
        } catch (SiteConfigParseException $ex) {
            die("Error loading configuration: ".$ex->getMessage()."\n");
        }

        $this->timing = $this->isDebugMode();
        if ($this->timing) {
            array_push($this->timePoints, $start);
        }
    }

    /**
     * Tests whether the MODE_TEST flag is set
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return (bool) $this->mode & self::MODE_TEST;
    }

    /**
     * Tests whether the MODE_DEBUG flag is set
     *
     * @return boolean
     */
    public function isDebugMode()
    {
        return (bool) $this->mode & self::MODE_DEBUG;
    }

    /**
     * Tests whether the MODE_MAINT flag is set
     *
     * @return boolean
     */
    public function isMaintMode()
    {
        return (bool) $this->mode & self::MODE_MAINT;
    }

    /**
     * Tests whether the given mode flag is set
     *
     * @param int $flag
     * @return boolean
     */
    public function isMode($flag)
    {
        assert(is_int($flag));
        return (bool) $this->mode & $flag;
    }

    /**
     * Sites can override this if they need to do wild things with path
     * translation
     *
     * @param string $requrl the requested url, like $_SERVER['REQUEST_URI']
     * @return string the url being served
     * @see handle
     */
    protected function parseUrl($requrl)
    {
        $base = $this->url;
        $baselen = strlen($base);
        $reqlen = strlen($requrl);
        $url = null;

        if ($reqlen >= $baselen && substr($requrl, 0, $baselen) == $base) {
            if ($reqlen == $baselen) {
                $url = '';
            } elseif ($requrl[$baselen] == '/') {
                $url = substr($requrl, $baselen+1);
            }
        }
        if (! isset($url)) {
            throw new RuntimeException(
                "invalid requset url '$requrl', expecting something under $base"
            );
        }

        $this->requestUrl = $url;
        $this->dispatchCallback('onParseUrl', $this);
    }

    /**
     * Handling process:
     *   dispatch: onParseUrl
     *   dispatch: onAccessCheck
     *   dispatch: onRequestStart
     *   dispatch: onSendResponse
     *   dispatch: onResponseSent
     *   dispatch: onCleanup
     *
     * At any point in the process, a thrown exception will cause 'onException'
     * to be dispatched, if that generates no output, then the exception is
     * simply printed in a minimal html document. After the onException, normal cleanup is done
     *
     * @return void
     * @see parseUrl
     */
    final public function handle()
    {
        $args = array_slice(func_get_args(), 1);
        try {
            @ob_start();

            CurrentPath::set(Loader::$Base);

            $this->parseUrl(Params::server('REQUEST_URI'));
            $this->log->info(sprintf(
                '==> Framework v%s: New Request from %s - %s <==',
                Loader::$FrameworkVersion,
                Params::server('REMOTE_ADDR'),
                $this->requestUrl
            ));

            $this->dispatchCallback('onAccessCheck',  $this);
            $this->dispatchCallback('onRequestStart', $this);
            $this->dispatchCallback('onSendResponse', $this);
            $this->dispatchCallback('onResponseSent', $this);
            $this->dispatchCallback('onCleanup');

            @ob_end_flush();
        } catch (Exception $ex) {
            @ob_end_clean();
            try {
                ob_start();
                $this->dispatchCallback('onException', $this, $ex);
                if (ob_get_length()) {
                    ob_end_flush();
                } else {
                    @ob_end_clean();
                    header('Content-Type: text/html');
                    print
                        "<html>\n".
                        "  <head><title>Unhandled Exception</title></head>\n".
                        "  <body>\n".
                        "    <h1>Unhandled exception:</h1>\n".
                        "    <pre>$ex</pre>\n".
                        "  </body>\n".
                        "</html>\n";
                }
            } catch (Exception $rex) {
                header('Content-Type: text/html');
                print
                    "<html>\n".
                    "  <head><title>Broken exception handler</title></head>\n".
                    "  <body>\n".
                    "    <h1>Broken exception handler:</h1>\n".
                    "    <pre>$rex</pre>\n\n".
                    "    <h1>Original exception:</h1>\n".
                    "    <pre>$ex</pre>\n".
                    "  </body>\n".
                    "</html>\n";
            }
            try {
                $this->dispatchCallback('onCleanup');
            } catch (Exception $ex) {
                print "Exception in cleanup: <pre>$ex</pre>\n";
            }
        }
        $this->timingReport();
    }

    private $timing = false;
    private $timePoints = array();

    /**
     * If timing is enabled, adds a named time point
     *
     * @param what string the label for this time point
     * @return void
     */
    public function timePoint($what)
    {
        if ($this->timing) {
            // TODO deal with integer microseconnds intsead of floating seconds
            array_push($this->timePoints, array(microtime(true), $what));
        }
    }

    /**
     * If timing is enabled, generates a final timing report
     */
    public function timingReport()
    {
        // TODO fully report on all time points
        if (! $this->timing) {
            return;
        }
        $this->timePoint('done');

        $start = $this->timePoints[0];
        $end = $this->timePoints[count($this->timePoints)-1];
        $this->log->info(sprintf(
            '==> Request Served in %.4f seconds <==',
            $end[0] - $start[0]
        ));
    }

    /**
     * Converts the given url to be absolute in this site
     * @param url string
     * @return string
     */
    public function rel2abs($url)
    {
        assert(is_string($url));
        if (strlen($url) > 0 && $url[0] == '/') {
            return $this->serverUrl.$url;
        } elseif (! preg_match('~^\w+://~', $url)) {
            return $this->serverUrl.$this->url.'/'.$url;
        } else {
            return $url;
        }
    }
}

?>
