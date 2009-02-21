<?php

/**
 * SiteWebLiteHandler definition
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

class SiteWebLiteHandler extends SiteHandler
{
    /**
     * Handles the request
     *
     * @see Site::handle
     */
    public function handle()
    {
        Application::start();

        Main::startSession();

        global $current, $database;

        $current = new Current();

        $database = new Database();
        $database->log = $database->time = $this->site->config->isDebugMode();

        $this->site->config->initalize();

        // Load a user if there is one to load
        Main::loadUser();

        Main::setLanguageAndTheme();

        // set appropriate path
        CurrentPath::set(SiteLoader::$Base);
    }
}

?>