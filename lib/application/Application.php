<?php

/**
 * Application class definition
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
 * @category     Application
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.1
 * @link         http://framework.redtreesystems.com
 */

/**
 * Implements PHP's autoload function in order to
 * load required classed.
 *
 * @see the php docs
 * @param string $class a class name
 * @return void
 */
function __autoload($class)
{
    Application::autoLoad($class);
}

/**
 * Application specific actions
 *
 * This is a static class to ease general flow and standarization
 *
 * @static
 * @category     Application
 * @package        Core
 */
class Application
{
    private static $class;

    /**
     * Contstructor; Private
     *
     * @access private
     * @return Application a new instance
     */
    private function __construct()
    {

    }

    /**
     * Forwards to the login page
     *
     * @return void
     */
    public static function login()
    {
        global $config;

        $policy = PolicyManager::getInstance();

        Application::saveRequest();
        Application::forward($policy->getLoginUrl());
    }

    /**
     * Call back for File::find. Finds file
     * names consisting of <class>.php, and
     * includes them into global space.
     *
     * @param string $file the current file name
     * @return void
     */
    static public function findClass($file)
    {
        global $config;

        $class = Application::$class;
        if (preg_match("|/$class.php$|", $file)) {
            include_once $file;

            ApplicationData::addClassEntry($class, $file);
        }
    }

    /**
     * Determines if a class has been mapped, and tried to load it.
     * If so, this method will return the path to the file where the class is defined.
     *
     * @param string $class a class name to test
     * @return string a file path if found, false otherwise
     */
    static public function includeClass($class)
    {
        $file = ApplicationData::getClassLocation($class);
        if (!$file) {
            return false;
        }

        if (!file_exists($file)) {
            throw new Exception("The location for $class is missing");
        }

        require_once $file;
        return $file;
    }

    /**
     * Loads classes based on class names
     *
     * @see the php docs
     * @param string $class a class name
     * @return boolean true if $class was included
     */
    static public function autoLoad($class)
    {
        global $config, $current;

        if (!$config) { return false; }

        if ($file = Application::includeClass($class)) {
            return $file;
        }

        $config->warn("$class unknown, looking through the filesystem");

        if (!class_exists('File', false)) {
            include_once "$config->fwAbsPath/lib/util/File.php";
        }

        /*
         * autoload classes at the current->path
         */
        if ($current) {
            Application::$class = $class;
            File::find(array('Application', 'findClass'), $current->path);
            if ($file = Application::includeClass($class)) {
                $config->info("$class found in $file");
                return $file;
            }
        }

        $targets = array("$config->absPath/SITE/local/components", "$config->fwAbsPath/components",  // components
        				 "$config->absPath/SITE/local/modules", "$config->fwAbsPath/modules",        // modules
        				 "$config->absPath/SITE/local/lib", "$config->fwAbsPath/lib",                // lib
                         "$config->absPath/SITE/local/themes", "$config->fwAbsPath/themes",          // themes
                         "$config->absPath/SITE/local/extensions", "$config->fwAbsPath/extensions"); // extensions
        foreach ($targets as $target) {
            $config->debug("examining $target");

            if (!file_exists($target)) {
                continue;
            }

            Application::$class = $class;
            File::find(array('Application', 'findClass'), $target);
            if ($file = Application::includeClass($class)) {
                $config->info("$class found in $file");
                return $file;
            }
        }

        return false;
    }

    /**
     * Saves the current request into the session. A new class
     * is created with the following properties:
     *     stage (int)
     *     action (string)
     *     component (string)
     *     get (array)
     *     post (array)
     *     request (array)
     *
     * @static
     * @access public
     * @return void
     */
    static public function saveRequest()
    {
        global $current, $config;

        $obj = new stdClass();
        $obj->stage = $current->stage;
        $obj->action = ($current->action ? $current->action->id : null);
        $obj->component = ($current->component ? $current->component->getClass() : null);
        $obj->get = $_GET;
        $obj->post = $_POST;
        $obj->request = $_REQUEST;

        $config->info("saving request");

        $_SESSION[AppConstants::SAVED_REQUEST_KEY] = serialize($obj);
    }

    /**
     * Gets the saved request out of the session, and removes it. Upon
     * success, a structure with the following properties is returned:
     *     action (string)
     *     stage (int)
     *     component (string)
     *     get (array)
     *     post (array)
     *     request (array)
     *
     * @static
     * @access public
     * @return object structure upon success; null otherwise
     */
    static public function popSavedRequest()
    {
        if (Params::session(AppConstants::SAVED_REQUEST_KEY)) {
            $request = Params::session(AppConstants::SAVED_REQUEST_KEY);
            unset($_SESSION[AppConstants::SAVED_REQUEST_KEY]);

            return unserialize($request);
        }

        return null;
    }

    /**
     * Saves the value of the global $current variable into
     * the session.
     *
     * @static
     * @access public
     * @return void
     */
    static public function saveCurrent()
    {
        global $current;

        $_SESSION[AppConstants::LAST_CURRENT_KEY] = serialize($current);
    }

    /**
     * Gets the $current variable from the session, and returns it.
     *
     * @static
     * @access public
     * @return Current $current upon success; null otherwise
     */
    static public function popSavedCurrent()
    {
        global $current, $config;

        if (Params::session(AppConstants::LAST_CURRENT_KEY)) {
            $request = Params::session(AppConstants::LAST_CURRENT_KEY);
            unset($_SESSION[AppConstants::LAST_CURRENT_KEY]);

            return unserialize($request);
        }

        return null;
    }

    /**
     * Forward the user to another location. Note that this must happen
     * before headers are sent. This method terminates the script.
     *
     * @static
     * @access public
     * @return void
     */
    static public function forward($uri=null)
    {
        global $config;

        Application::saveCurrent();

        if ($config->isTestMode()) {
            return;
        }

        if (!$uri) {
            $policy = PolicyManager::getInstance();
            $uri = $policy->getActionURI($config->getDefaultComponent(), $config->getDefaultAction());
        }

        session_write_close();
        header("Location: $uri");
        exit(0);
    }

    /**
     * Calls the specified action
     *
     * @static
     * @access public
     * @param Component $component the component of whose method you wish to call
     * @param ActionDescription $action the method you want to call
     * @param int $stage the action stage
     * @return mixed the return value of the method
     */
    static public function call(ActionProvider &$provider, ActionDescription &$action, $stage=Stage::VIEW)
    {
        global $current;

        if (!$provider->allows($action)) {
            return null;
        }

        $current->action =& $action;

        if (null !== ($res = LifeCycleManager::onAction($provider, $action))) {
            return $res;
        }

        return $provider->perform($action, $stage);
    }

    /**
     * Calls the specified action, using the cache if appropriate and available.
     *
     * @static
     * @access public
     * @param Component $component the component of whose method you wish to call
     * @param ActionDescription $action the method you want to call
     * @param int $stage the action stage
     * @return boolean true if the method was successfully called; false otherwise
     */
    static public function performAction(ActionProvider &$component, ActionDescription &$action, $stage=Stage::VIEW)
    {
        if (!$component->allows($action)) {
            return false;
        }

        $data = '';

        /*
         * if we can't use a cache of the action
         */
        if (!($data = Cacher::useCache($action))) {
            /*
             * then call it & try to cache it
             */
            Application::call($component, $action, $stage);

            Cacher::writeCache($action, $component);
        } else {
            $component = unserialize($data);
        }

        return true;
    }

    /**
     * Calls a module's onDisplay method, trying to cache the object
     *
     * @param Module $module the module to display
     * @param int $position the position the module is going to be in
     * @return boolean true upon success
     */
    static public function performModule(Module &$module, $position=Module::POSITION_LEFT)
    {
        global $config;

        Application::setPath("$config->fwAbsPath/modules/" . $module->getClass());

        if (!($data = Cacher::useCache($module))) {
            $module->onDisplay($position);

            Cacher::writeCache($module, $module);
        } else {
            $module = unserialize($data);
        }

        return true;
    }

    /**
     * Sets the current->path. A relative or absolute path
     * may be used.
     *
     * @static
     * @access public
     * @param string $path the value current->path should be set to
     * @return string the old value of current->path
     */
    public static function setPath($path)
    {
        global $current;

        $oldPath = $current->path;

        if (preg_match('|^(?:[A-Z][:])?/|', $path)) {
            $current->path = $path;
        }
        else {
            $current->path .= ((preg_match('|/$|', $current->path)) ? '' : '/') . $path;
        }

        return $oldPath;
    }

    private static function requireMinimum()
    {
        global $config;

        require_once "$config->fwAbsPath/lib/application/AppConstants.php";
        require_once "$config->fwAbsPath/lib/application/ApplicationData.php";
        require_once "$config->fwAbsPath/lib/application/Main.php";
        require_once "$config->fwAbsPath/lib/database/Database.php";
        require_once "$config->fwAbsPath/lib/util/Params.php";
        require_once "$config->fwAbsPath/lib/component/IRequestObject.php";
        require_once "$config->fwAbsPath/lib/component/RequestObject.php";
        require_once "$config->fwAbsPath/lib/database/IDatabaseObject.php";
        require_once "$config->fwAbsPath/lib/database/DatabaseObject.php";

        require_once "$config->fwAbsPath/lib/policies/ILocationPolicy.php";
        require_once "$config->fwAbsPath/lib/policies/DefaultLocationPolicy.php";
        require_once "$config->fwAbsPath/lib/policies/ILinkPolicy.php";
        require_once "$config->fwAbsPath/lib/policies/DefaultLinkPolicy.php";
        require_once "$config->fwAbsPath/lib/policies/PolicyManager.php";
    }

    public static function start()
    {
        Application::requireMinimum();

        /*
         * load app data
         */
        ApplicationData::initialize();
    }

    public static function end()
    {
        ApplicationData::unintialize();
    }

    /**
     * Starts the web application
     *
     */
    public static function startWeb()
    {
        global $config, $database;

        Application::start();

        Main::startSession();

        /*
         * This function should be defined in the site's index.php
         */
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

        LifeCycleManager::onInitialize();

        $config->info("==> Framework v" . $config->getVersion() . ": New Request from " . Params::server('REMOTE_ADDR') .' - ' . Params::server('REQUEST_URI') . ' <==');
        Main::parseRequest();

        $config->initalize();

        /*
         * fill out the current ticket
         */
        Main::populateCurrent();

        LifeCycleManager::onRequestStart();

        /*
         * if this should be a secure request, make sure we're
         * using https. if we're not we need to come back to this
         * request securely.
         */
        Main::secureRequest();

        /*
         * Has session timed out? (only for timed-sessions)
         */
        Main::sessionTimeout();

        /*
         * Load a user if there is one to load
         */
        Main::loadUser();

        /*
         * Restore any previously saved requests
         */
        Main::restoreRequest();

        Main::setLanguageAndTheme();

        /*
         * debug values
         */
        if ($config->isDebugMode()) {
            $config->debug('GET: ' . print_r($_GET, true));
            $config->debug('POST: ' . print_r($_POST, true));

            $safeSession = $_SESSION;
            foreach ($safeSession as $key => $value) {
                if (preg_match('/^__application/', $key)) {
                    unset($safeSession[$key]);
                }
            }

            $config->debug('SESSION: ' . print_r($safeSession, true));
        }

        Main::render();

        Application::end();
    }
}

?>
