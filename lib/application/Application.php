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
     * TODO modularize request saving if it seems to have any use going forward
     */
    public static $RequestSaveKey = '_sr';

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
        global $current;

        $obj = new stdClass();
        $obj->get = $_GET;
        $obj->post = $_POST;
        $obj->request = $_REQUEST;
        $obj->component = $current->component;

        Site::getLog()->info("saving request");

        $_SESSION[self::$RequestSaveKey] = serialize($obj);
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
        $request = Session::get(self::$RequestSaveKey);
        if (isset($request)) {
            Session::set(self::$RequestSaveKey, null);
            $request =  unserialize($request);
        }
        return $request;
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

        $current->action =& $action;

        /**
         * TODO re-implement this when we revamp Component
         * if (null !== ($res = LifeCycleManager::onAction($provider, $action))) {
         *     return $res;
         * }
         */

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
        $oldPath = CurrentPath::set($module->getPath());

        if (!($data = Cacher::useCache($module))) {
            $module->onDisplay($position);

            Cacher::writeCache($module, $module);
        } else {
            $module = unserialize($data);
        }

        CurrentPath::set($oldPath);
        return true;
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
