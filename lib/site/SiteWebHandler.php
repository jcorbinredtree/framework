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
require_once 'lib/site/Session.php';
require_once 'lib/site/SiteContentPageProvider.php';

/**
 * This is the primary site handler, it maps urls to components and pages
 */
class SiteWebHandler extends SiteHandler
{
    public function initialize()
    {
        parent::initialize();
        Application::start();

        Session::start($this->site);
        Session::check($this->site);

        $this->site->getDatabase();

        $this->site->log->info(
            "==> Framework v".Loader::$FrameworkVersion.
            ": New Request from ".Params::server('REMOTE_ADDR').
            ' - ' . Params::server('REQUEST_URI').
            ' <=='
        );

        // TODO do secure checking right
        global $current;
        $current->setSecureRequest(Params::request('_se'));
        $this->site->addCallback('onAccessCheck', array('Main', 'secureRequest'));

        $this->site->addCallback('onRequestStart', array($this, 'startRequest'), true);

        new SiteContentPageProvider($this->site);
    }

    public function startRequest()
    {
        Main::restoreRequest(); // Restore any previously saved requests
        Main::setLanguage();
    }

    public function cleanup()
    {
        Application::end();
    }
}

?>
