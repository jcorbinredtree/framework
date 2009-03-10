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

require_once 'lib/application/CurrentPath.php';
require_once 'lib/application/Main.php';
require_once 'lib/component/IRequestObject.php';
require_once 'lib/component/RequestObject.php';
require_once 'lib/util/Params.php';


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
     * DEPRECATED
     * @see CurrentPath::set
     */
    public static function setPath($path)
    {
        Site::getLog()->deprecatedComplain(
            'Application::setPath', 'CurrentPath::set'
        );
        return CurrentPath::set($path);
    }
}

?>
