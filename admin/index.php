<?php

/* @WARNING: this is apache-specific */
ob_start("ob_gzhandler");

/**
 * Admin page
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
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

$start = microtime(true);

function login()
{
    Application::saveRequest();
    header('Location: login.php');
    exit(0);
}

if (!function_exists('money_format')) {
	function money_format($i, $x) {
		return number_format($x, 2);
	}
}

require '../Config.php';

setlocale(LC_MONETARY, Config::MONETARY_LOCALE);

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

require_once "$config->absPath/lib/application/Application.php";

Application::requireMinimum();

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

Main::startSession();
LifeCycleManager::onInitialize();

$config->info("==> Framework v$config->version Admin: New Request from " . Params::SERVER('REMOTE_ADDR') .' - ' . Params::SERVER('REQUEST_URI') . ' <==');

$config->initalize();
$config->absUri = dirname($config->absUri . '../');
$config->absUriPath = dirname($config->absUriPath . '../');

/*
 * fill out the current ticket
 */

$current = null;
if (Params::session(AppConstants::LAST_CURRENT_KEY)) {
    $current = Application::popSavedCurrent();
}
else {
    /**
     * The current variable is the third of three global variables
     * in the application. This variable holds the current state
     * of the application such as the physical path, and messages
     * between the application and user.
     *
     * @global Current $current
     * @see Current;
     */
    $current = new Current();
}

$current->id = Params::request('id', 0);
$current->setSecureRequest(Params::request(AppConstants::SECURE_KEY));

if ($component = Params::request(AppConstants::COMPONENT_KEY)) {
    /*
     * try to load a standard component
     */
    $current->component = Administration::load($component);

    /*
     * no component? is it an admin driver?
     */
    if (!$current->component) {
        $file = "$config->absPath/admin/drivers/$component/$component.php";
        if (file_exists($file)) {
            if (!class_exists($component)) {
                include_once $file;
            }

            Application::setPath("$config->absPath/admin/drivers/$component/");
            $current->component = new $component();
            $current->component->onRegisterActions();
        }
    }

    /*
     * no? I don't know wtf you're talking about then
     */
    if (!$current->component) {
        $msg = "unknown component $component";
        $current->error($msg);
        die($msg);
    }

    $current->action = $current->component->getAction(Params::request(AppConstants::ACTION_KEY, 1));
}

$current->stage = Params::request(AppConstants::STAGE_KEY, Stage::VIEW);

/*
 * Has session timed out? (only for timed-sessions)
 */
Main::sessionTimeout();

/*
 * Do we need a user?
 */
if (!Params::session('user_id')) {
    Application::saveRequest();

    $config->info('User not found, doing login');
    login();
}

/*
 * Load a user from the session user_id
 */
$current->user = new User();
$current->user->fetch(Params::session('user_id'));
if (!$current->user->id) {
    Application::saveRequest();

    $config->warn('User #' . Params::session('user_id') . ' not found');

    $_SESSION = array();

    login();
}
else {
    $_SESSION['time'] = time();
}

/*
 * Restore any previously saved requests
 */
Main::restoreRequest();

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

/*
 * lock + load
 */
if ($current->component && Params::request(AppConstants::NO_HTML_KEY)) {
    switch ($current->stage) {
        default:
        case Stage::VIEW:
            Application::performAction($current->component, $current->action, $current->stage);
            break;
        case Stage::VALIDATE:
            if (!Application::call($current->component, $current->action, Stage::VALIDATE)) {
                Application::performAction($current->component, $current->action, Stage::VIEW);
                break;
            }
        case Stage::PERFORM:
            if (!Application::call($current->component, $current->action, Stage::PERFORM)) {
                Application::performAction($current->component, $current->action, Stage::VIEW);
            }

            break;
    }

    $config->debug('output: ' . $current->component->getBuffer());

    $current->component->flush();
} else {
    $current->layout = new AdministrationLayoutDescription();
    $current->layout->isPopup = Params::request(AppConstants::POPUP_KEY);

    /*
     * populate descriptions
     *  This simply finds *Administration.php files and returns an instance of each.
     *  Each provider is then initialized, presumably inserting items into ProviderDescriptions
     */
    $current->layout->providers = Application::getAdministrations();
    foreach ($current->layout->providers as $provider) {
        if (!$current->component || ($provider->getClass() != $current->component->getClass())) {
            $provider->onRegisterActions();
        }
    }

    /*
     * The provider descriptions are collected, sorted, and tested for an active state
     */
    $current->layout->providerDescriptions = ProviderDescription::getAllDescriptions();
    {
        function sortProviderDescriptions($a, $b) {
            if ($a->weight == $b->weight) {
                return 0;
            }

            return ($a->weight < $b->weight) ? -1 : 1;
        }

        /*
         * sort the providers
         */
        usort($current->layout->providerDescriptions, 'sortProviderDescriptions');

        /*
         * determine & set active
         */
        for ($i = 0; $i < count($current->layout->providerDescriptions); $i++) {
            $desc =& $current->layout->providerDescriptions[$i];

            if ($current->component instanceof IDriver) {
                $desc->active = ($desc->name == Params::request('provider'));
            }
            elseif ($current->component) {
                $desc->active = $desc->handles($current->component->getClass(), $current->action->id);
            }

            /*
             * only if this is really the intended item is it current (not an active parent)
             */
            if ($desc->active) {
                $current->layout->activeDescription =& $desc;
                if (!$desc->parent) {
                    $current->layout->activeTopLevelSection =& $desc;
                }
            }

            if ($desc->hasChildren()) {
                foreach ($desc->children as $child) {
                    if ($child->active) {
                        $desc->active = true;
                        break;
                    }
                }
            }
        }

        if (!$current->layout->activeTopLevelSection && $current->layout->activeDescription) {
            $parent = $current->layout->activeDescription;
            while (null != $parent->parent) {
                $parent = $parent->parent;
            }

            $current->layout->activeTopLevelSection =& $parent;
        }

        if ($current->layout->activeTopLevelSection && $current->layout->activeTopLevelSection->hasChildren()) {
            usort($current->layout->activeTopLevelSection->children, 'sortProviderDescriptions');
        }
    }

    /*
     * +====================+
     * |  VIEW (cacheable)  <-------------^------------<
     * +====================+             |            |
     *                                 NO |            |
     * +====================+     +==============+     |
     * |      VALIDATE      |-----> Return True? |     |
     * +====================+     +==============+     |
     *                                    |            |
     * +====================+             |            |
     * |       PERFORM      <-------------v YES        |
     * +=========|==========+                          |
     *           |                                     |
     * +=========v==========+                          |
     * |    Return False?   |--------------------------^ YES
     * +====================+
     */
    if ($current->component) {
        $vars = get_object_vars($current->component);
        foreach ($vars as $prop => $val) {
            if (property_exists($current->layout, $prop)) {
                $current->layout->$prop = $val;
            }
        }

        switch ($current->stage) {
            default:
            case Stage::VIEW:
                Application::performAction($current->component, $current->action, $current->stage);
                break;
            case Stage::VALIDATE:
                if (!Application::call($current->component, $current->action, Stage::VALIDATE)) {
                    Application::performAction($current->component, $current->action, Stage::VIEW);
                    break;
                }
            case Stage::PERFORM:
                if (!Application::call($current->component, $current->action, Stage::PERFORM)) {
                    Application::performAction($current->component, $current->action, Stage::VIEW);
                }

                break;
        }
    }

    /*
     * populate more layout info
     */
    if ($current->component) {
        $vars = get_object_vars($current->component);
        foreach ($vars as $prop => $val) {
            if (property_exists($current->layout, $prop)) {
                $current->layout->$prop = $val;
            }
        }
    }

    /*
     * set the current path to layout
     */
    Application::setPath("$config->absPath/admin/view");
    $layout = new AdminLayoutManager();

    if (!$config->isDebugMode()) {
        $layout->addFilter(new WhitespaceFilter());
    }

    $layout->display($current->layout);
    $layout->flush();
}

if ($config->isDebugMode()) {
    $pageTime = (microtime(true) - $start);
    $databaseTime = $database->getTotalTime();
    $databaseQueries = $database->getTotalQueries();
    $message = '==> Admin Request Served in ' . (sprintf('%.4f', $pageTime)) . ' seconds; ';
    $message .= $database->getTotalQueries() . ' queries executed in ';
    $message .= sprintf('%.4f', $database->getTotalTime()) . ' seconds, ';
    $message .= sprintf('%.2f', (($databaseTime / $pageTime) * 100)) . '% of total time <==';

    $config->info($message);
}

?>
