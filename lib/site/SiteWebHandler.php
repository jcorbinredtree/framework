<?php

/**
 * SiteWebHandler definition
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

require_once 'lib/application/Application.php';

/**
 * This is the primary site handler, it maps urls to components and pages
 */
class SiteWebHandler extends SiteHandler
{
    public function initialize()
    {
        parent::initialize();
        Application::start();
        Main::startSession();
        $this->site->getDatabase();

        $this->site->config->info(
            "==> Framework v".$this->site->config->getVersion().
            ": New Request from ".Params::server('REMOTE_ADDR').
            ' - ' . Params::server('REQUEST_URI').
            ' <=='
        );

        $this->site->addCallback('onRequestStart', array($this, 'startRequest'), true);
    }

    /**
     * Handles the request
     *
     * @see Site::handle
     */
    public function resolvePage()
    {
        /**
         * Parses the request and populates the $_GET and $_REQUEST arrays.
         * The order of precedence is as follows:
         *
         * 1.) A matching key in $this->site->config->getUrlMappings()
         * 2.) The ILinkPolicy::parse() method
         */
        $pathBase = $this->site->config->absUriPath;
        $url = Params::server('REQUEST_URI');
        if (substr($url, 0, strlen($pathBase)) == $pathBase) {
            $url = substr($url, strlen($pathBase));
        }
        if (strlen($url) > 0 && $url[0] == '/') {
            $url = strlen($url) > 1 ?  substr($url, 0) : '';
        }

        $mappings = $this->site->config->getUrlMappings();
        if (array_key_exists($url, $mappings)) {
            $map = $mappings[$url];
            $_REQUEST[AppConstants::COMPONENT_KEY] = $_GET[AppConstants::COMPONENT_KEY] = $map[0];
            $_REQUEST[AppConstants::ACTION_KEY] = $_GET[AppConstants::ACTION_KEY] = $map[1];
            if (count($map) > 2) {
                $sets = explode(',', 'null,' . $map[2]);
                foreach ($sets as $set) {
                    if ($set == 'null') {
                        continue;
                    }
                    $args = explode('=', $set);
                    $_REQUEST[$args[0]] = $_GET[$args[0]] = $args[1];
                }
            }

            if (count($map) > 3) {
                $_REQUEST[AppConstants::STAGE_KEY] = $_GET[AppConstants::STAGE_KEY] = $map[3];
            }
        } else {
            $policy = PolicyManager::getInstance();
            $policy->parse();
        }

        // TODO implement this in SitePage
        global $current;
        $current->setSecureRequest(Params::request(AppConstants::SECURE_KEY));
        $this->site->addCallback('onAccessCheck', array('Main', 'secureRequest'));

        $componentClass = Params::request(AppConstants::COMPONENT_KEY);
        if (isset($componentClass)) {
            $page = Component::createComponentPage($componentClass);
            $current->component = $page->component;
            return $page;
        } else {
            return null;
        }
    }

    public function startRequest()
    {
        Main::sessionTimeout(); // Has session timed out? (only for timed-sessions)
        Main::restoreRequest(); // Restore any previously saved requests
        Main::setLanguageAndTheme();
    }

    public function cleanup()
    {
        Application::end();
    }
}

?>
