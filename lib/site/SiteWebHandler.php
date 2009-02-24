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
    }

    /**
     * Handles the request
     *
     * @see Site::handle
     */
    public function resolvePage()
    {
        Main::parseRequest();
        Main::loadCurrent(); // Restores the Current object from the session if needed

        global $current;
        $current->setSecureRequest(Params::request(AppConstants::SECURE_KEY));

        // TODO better component determination
        $componentClass = Params::request(AppConstants::COMPONENT_KEY);
        if (! isset($componentClass)) {
            $componentClass = $this->site->config->getDefaultComponent();
        }
        $current->component = Component::getInstance($componentClass);
        $page = $current->component->createPage();

        $this->site->addCallback('onAccessCheck', array('Main', 'secureRequest'));

        Main::sessionTimeout(); // Has session timed out? (only for timed-sessions)
        Main::restoreRequest(); // Restore any previously saved requests
        Main::setLanguageAndTheme();

        return $page;
    }

    public function sendResponse()
    {
        $page = Site::getPage();
        $page->render();

        Application::end();
    }
}

?>
