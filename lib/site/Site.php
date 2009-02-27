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
require_once 'lib/util/Params.php';
require_once 'lib/site/SiteLayout.php';
require_once 'lib/site/SiteHandler.php';
require_once 'lib/site/SitePageProvider.php';
require_once 'lib/site/SiteModuleLoader.php';
require_once 'Config.php';

// TODO this is just here for the autoloading, which needs to be split into its
// own independant class
require_once 'lib/application/Application.php';

/**
 * A site has:
 *   pages
 *   a database
 *   a configuration
 *   and more
 *
 * TODO
 *   someday a site will only have a configuration, modules after:
 *   Database and TemplateSystem are transformed into SiteModules
 *   page logic is pulled out into a SitePageSystem
 *
 * Example usage in inedx.php:
 *   require_once('SITE/framework/SiteLoader.php');
 *   class MySite extends Site { ... }
 *   Site::doRole('MySite', 'web');
 *
 * @package Site
 */
abstract class Site extends CallbackManager
{
    public static $DefaultPageLayout = 'default';

    public static $Modules = array(
        // TODO move this out into a requirement of forth-coming SitePageSystem
        'TemplateSystem'
    );

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
            throw new RuntimeException('site not started');
        }
        return self::$TheSite;
    }

    /**
     * Sets the site class in play and instantiates $TheSite
     *
     * @param class string classname, subclass of Site
     * @return void
     */
    final public static function set($class)
    {
        assert(is_subclass_of($class, 'Site'));
        self::$TheSite = new $class();
    }

    /**
     * Primary entry point, convenience function really
     *
     * Exapmle:
     *   Site::doRole('MySite', 'some-handler', 'some', 'handler', 'args');
     *
     * Is equivalent to:
     *   Site::set('MySite');
     *   Site::Site()->handle('some-handler', 'some', 'handler', 'args');
     *
     * @param SiteClass string as in Site::set
     * @param role string as in Site::handle
     * @return void
     * @see Site::set, Site::handle
     */
    final public static function doRole($SiteClass, $role)
    {
        self::set($SiteClass);
        self::Site();
        $args = array_slice(func_get_args(), 1);
        call_user_func_array(array(self::$TheSite, 'handle'), $args);
    }

    /**
     * Returns the site config, convenience for Site::Site()->config
     * @return Config
     */
    final public static function getConfig()
    {
        $site = self::Site();
        if (! isset($site->config)) {
            throw new RuntimeException('no site config');
        }
        return $site->page;
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
     * Returns the current SitePage, convenience for Site::Site()->page
     * @return SitePage
     */
    final public static function getPage()
    {
        $site = self::Site();
        if (! isset($site->page)) {
            throw new RuntimeException('no current site page');
        }
        return $site->page;
    }

    /**
     * Convenience utility, if argument is a string, explodes it on ',', other
     * wise the argument should be an array of strings
     *
     * The array is then walked, and each path that is non-absolute, prepended
     * with SiteLoader::$Base. Each path is resolved with realpath() and added
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
                $p = SiteLoader::$Base."/$p";
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
     * @var Config
     */
    public $config;

    /**
     * This member is meant to supercede the old $current global, it should
     * embody all aspects of the current request/response context
     *
     * @var SitePage
     */
    public $page;

    /**
     * Absolute url for how the client got to the server
     */
    public $serverUrl;

    /**
     * @var SiteModuleLoader
     */
    public $modules;

    /**
     * Creates a new site:
     *   starts the timing clock (if enabled)
     *   creates the SiteLayout if not set
     *   sets up config
     */
    private function __construct()
    {
        $start = array(microtime(true), 'start');
        $this->timePoint('start');

        if (! isset($this->layout)) {
            $this->layout = new SiteLayout($this);
        }

        new SiteModuleLoader($this);
        $this->modules->call('initialize');

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

        // TODO allow for changable config class name?
        global $config; // compatability global
        $config = $this->config = new Config($this);
        $this->onConfig();

        $this->modules->call('onConfig');

        $this->timing = $config->isDebugMode();
        if ($this->timing) {
            array_push($this->timePoints, $start);
        }
    }

    /**
     * Loads a site handler by role name
     *   e.g., "role" resolves to "SiteRoleHandler"
     *
     * SiteRoleHandler must be a subclass of SiteHandler
     *
     * If the SiteRoleHandler class isn't already loaded, then trys to
     * load "lib/site/SiteRoleHandler.php", failing that, an
     * InvalidArgumentException is thrown
     *
     * @param role string required usually something like 'web', 'web-lite',
     * etc; will be camelcased with respect to dashes or underscores
     * @return SiteHandler the handler
     * @see SiteHandler
     */
    public function loadHandler($role)
    {
        assert(is_string($role));

        $class = 'Site'.implode('',
            array_map('ucfirst', preg_split('/[-_]/', $role))
        ).'Handler';
        if (! class_exists($class)) {
            ob_start();
            include_once "lib/site/$class.php";
            @ob_end_clean();
        }
        if (! class_exists($class)) {
            throw new InvalidArgumentException("no such handler $role");
        }

        assert(is_subclass_of($class, 'SiteHandler'));

        return new $class($this);
    }

    /**
     * Handles a site request for a given role by delegating to SiteRoleHandler
     *
     * Calls the handle method on the handler instance with any additional
     * arguments passed after $role
     *
     * Handling process:
     *   Load Handler                            role => handler
     *   handler->setArguments                   args -> handler
     *   handler->initialize                     handler stage
     *   dispatch: onHandlerInitialize           callback
     *   Got a page?
     *     yes: continue
     *     no: page = new NotFoundPage
     *   dispatch: onPageResolved                callback
     *   dispatch: onAccessCheck                 callback
     *   dispatch: onRequestStart                callback
     *   handler->sendResponse                   handler stage
     *   dispatch: onRequestSent                 callback
     *   dispatch: onHandlerCleanup              callback
     *   $andler->cleanup                        handler stage
     *
     * @param role string
     * @return void
     * @see loadHandler
     */
    final public function handle($role)
    {
        $args = array_slice(func_get_args(), 1);
        try {
            $urlPat = quoteMeta(SiteLoader::$UrlBase);
            $urlPat = "~^$urlPat(?:/(.*))?\$~";
            $url = Params::server('REQUEST_URI');
            $matches = array();
            if (preg_match($urlPat, $url, $matches)) {
                $url = $matches[1];
            }

            @ob_start();
            $handler = $this->loadHandler($role);
            $handler->setArguments($args);
            $handler->initialize();
            $this->dispatchCallback('onHandlerInitialize', $handler);
            $r = $this->marshallSingleCallback('onResolvePage', $url);
            if (! isset($r) || $r === SitePageProvider::FAIL) {
                $this->page = new NotFoundPage($url);
            } elseif ($r === SitePageProvider::REDIRECT) {
                $this->dispatchCallback('onRedirect', $this);
            } else {
                $this->page = $r;
            }
            if (isset($this->page)) {
                $this->dispatchCallback('onPageResolved', $this);
                $this->dispatchCallback('onAccessCheck', $this);
                $this->dispatchCallback('onRequestStart', $this);
                $handler->sendResponse();
                $this->dispatchCallback('onRequestSent', $this);
            }
            $this->dispatchCallback('onHandlerCleanup', $handler);
            $handler->cleanup();
            @ob_end_flush();
        } catch (Exception $ex) {
            @ob_end_clean();
            try {
                @ob_start();
                $this->page = new ExceptionPage($ex, $this->page);
                $this->page->render();
                @ob_end_flush();
            } catch (Exception $rex) {
                // Looks like if we want something done right, we'll have to
                // do it ourselves
                $this->exceptionHandlerOfLastResort($rex, $ex);
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

    abstract public function onConfig();

    /**
     * Returns the database interface for the site
     */
    public function getDatabase()
    {
        // TODO move away from using a global, this should be consumed
        // through the Site singleton
        global $database;
        if (!isset($database)) {
            $database = new Database();
            $database->log = $database->time = $this->config->isDebugMode();
        }
        return $database;
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
            return $this->config->absUri.'/'.$url;
        } else {
            return $url;
        }
    }

    /**
     * Determines the layout for a page
     *
     * This is called from the HTMLPage constructor, a layout may or may not
     * have been set by the constructor depending on if one was provided by the
     * instantiating scope.
     *
     * The onLayoutHTMLPage callback is dispatched, callbacks should modify the
     * page directly and throw a StopException if they wan to halt the callback.
     * If the page has no layout set after dispatching, then it will be set to
     * Site::$DefaultPageLayout.
     *
     * @param page HTMLPage
     * @return string
     * @see HTMLPage, CallbackManager::dispatchCallback
     */
    public function layoutHTMLPage(HTMLPage &$page)
    {
        $this->dispatchCallback('onLayoutHTMLPage', $page);
        if (! $page->hasLayout()) {
            $page->setLayout(self::$DefaultPageLayout);
        }
    }

    /**
     * yes it's got a long name
     * yes it's supposed to be a pain in the ass
     * no you shouldn't touch it
     *
     * @param rex Exception recursive exception (what went really wrong)
     * @param ex Exception oriiginal exception (what went wrong)
     */
    final private function exceptionHandlerOfLastResort($rex, $ex)
    {
        // The rat fell off the wheel so hard that even ExceptionPage failed
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
