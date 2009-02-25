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

        new SiteWebHandlerPageProvider($this->site);
        $this->site->addCallback('onRequestStart', array($this, 'startRequest'), true);
    }

    public function startRequest()
    {
        Main::sessionTimeout(); // Has session timed out? (only for timed-sessions)
        Main::restoreRequest(); // Restore any previously saved requests
        Main::setLanguage();
    }

    public function cleanup()
    {
        Application::end();
    }
}

class SiteWebHandlerPageProvider extends SitePageProvider
{
    public function loadPage($url)
    {
        // TODO implement this in SitePage
        global $current;
        $current->setSecureRequest(Params::request(AppConstants::SECURE_KEY));
        $this->site->addCallback('onAccessCheck', array('Main', 'secureRequest'));

        $componentClass = Params::request(AppConstants::COMPONENT_KEY);
        if (isset($componentClass)) {
            $page = Component::createComponentPage($componentClass);
            $current->component = $page->component;
            return $page;
        }
    }
}

?>
