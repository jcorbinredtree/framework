<?php

/**
 * SiteCliHandler definition
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

class SiteCliHandler extends SiteHandler
{
    /**
     * Handles the request
     *
     * @see Site::handle
     */
    public function initialize()
    {
        $_SERVER['REQUEST_URI'] = SiteLoader::$UrlBase;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        parent::initialize();

        global $_SESSION;
        $_SESSION = array();

        Application::start();
        $this->site->getDatabase();
    }
}

?>
