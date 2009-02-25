<?php

/**
 * SiteLoader
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

class SiteLoader
{
    /**
     * The absolute path to the site root, such as /var/www/full/path/to/app.
     */
    static public $Base;

    /**
     * The base of the site's url space, this is the url that serves $Base
     */
    static public $UrlBase;

    /**
     * The absolute path of the framework
     */
    static public $FrameworkPath;

    /**
     * The absolute path to the site's specific code area
     */
    static public $LocalPath;

    /**
     * Whether we're running from the cli or not
     */
    static public $IsCli;

    /**
     * Loads the site
     */
    static public function load()
    {
        self::$Base = dirname(dirname(dirname(__FILE__)));
        self::$UrlBase = dirname($_SERVER['PHP_SELF']);
        if (self::$UrlBase == '/') {
            self::$UrlBase = '';
        }
        self::$FrameworkPath = self::$Base.'/SITE/framework';
        self::$LocalPath = self::$Base.'/SITE/local';
        set_include_path(implode(':', array(
            get_include_path(),
            self::$FrameworkPath,
            self::$LocalPath
        )));
        self::$IsCli = 'cli' === php_sapi_name();
        if (self::$IsCli) {
            $_SERVER = array(
                'SERVER_NAME' => 'cli',
                'SERVER_PORT' => 80,
                'PHP_SELF'    => '/'
            );
        }

        require_once 'lib/site/Site.php';
    }
}
SiteLoader::load();

?>
