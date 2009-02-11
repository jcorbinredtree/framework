<?php
/**
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
 */

class LifeCycleManager
{
    private $cyclers;

    private static $singleton = null;
    public static function instance()
    {
        if (! isset(self::$singleton)) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }

    private function __construct()
    {
        $this->cyclers = array();
    }

    public function register(ILifeCycle &$cycler)
    {
        if (! isset($cycler)) {
            throw new InvalidArgumentException("null cycler");
        }
        array_push($this->cyclers, $cycler);
    }

    public function unregister(ILifeCycle &$cycler)
    {
        if (! isset($cycler)) {
            throw new InvalidArgumentException("null cycler");
        }

        $lcs = array();
        foreach ($this->cyclers as &$lco) {
            if ($cycler != $lco) {
                array_push($lcs, $lco);
            }
        }

        $this->cyclers = $lcs;
    }

    private function dispatch($event)
    {
        $args = array_slice(func_get_args(), 1);
        foreach ($this->cyclers as &$lco) {
            call_user_func_array(array($lco, $event), $args);
        }
    }

    private function delegate($event)
    {
        $args = array_slice(func_get_args(), 1);
        foreach ($this->cyclers as &$lco) {
            $r = call_user_func_array(array($lco, $event), $args);
            if ($r) {
                return true;
            }
        }

        return false;
    }

    private function delegateReturn($event)
    {
        $args = array_slice(func_get_args(), 1);
        foreach ($this->cyclers as &$lco) {
            $r = call_user_func_array(array($lco, $event), $args);
            if ($r !== null) {
                return $r;
            }
        }

        return null;
    }

    private function collect($event)
    {
        $ret = array();
        $args = array_slice(func_get_args(), 1);
        foreach ($this->cyclers as &$lco) {
            $r = call_user_func_array(array($lco, $event), $args);
            if (! is_array($r)) {
                throw new RuntimeException(
                    "LifeCycleManager->collect($event): ".
                    get_class($lco)." returned non-array"
                );
            }
            $ret = array_merge($ret, $r);
        }

        return $ret;
    }

    public static function add(ILifeCycle &$item)
    {
        self::instance()->register($item);
    }

    public static function remove(ILifeCycle &$item)
    {
        self::instance()->unregister($item);
    }

    public static function onInitialize()
    {
        self::instance()->dispatch('onInitialize');
    }

    public static function onException(Exception &$ex)
    {
        self::instance()->dispatch('onException', $ex);

        return false;
    }

    public static function onURLRewrite()
    {
        return self::instance()->delegate('onURLRewrite');
    }

    public static function onRequestStart()
    {
        self::instance()->dispatch('onRequestStart');
    }

    public static function onAction(ActionProvider &$provider, ActionDescription &$description)
    {
        return self::instance()->delegateReturn('onAction', $provider, $description);
    }

    /**
     * Called to get items for the top navigation
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetTopNavigation()
    {
        return self::instance()->collect('onGetTopNavigation');
    }

    /**
     * Called to get items for the right navigation
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetRightNavigation()
    {
        return self::instance()->collect('onGetRightNavigation');
    }

    /**
     * Called to get items for the bottom navigation
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetBottomNavigation()
    {
        return self::instance()->collect('onGetBottomNavigation');
    }

    /**
     * Called to get items for the left navigation
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetLeftNavigation()
    {
        return self::instance()->collect('onGetLeftNavigation');
    }

    /**
     * Called to get items for the top modules
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetTopModules()
    {
        return self::instance()->collect('onGetTopModules');
    }

    /**
     * Called to get items for the right modules
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetRightModules()
    {
        return self::instance()->collect('onGetRightModules');
    }

    /**
     * Called to get items for the bottom modules
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetBottomModules()
    {
        return self::instance()->collect('onGetBottomModules');
    }

    /**
     * Called to get items for the left modules
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetLeftModules()
    {
        return self::instance()->collect('onGetLeftModules');
    }

    public static function onPreRender(LayoutDescription &$layout)
    {
        return self::instance()->dispatch('onPreRender', $layout);
    }

    public static function onPostRender()
    {
        return self::instance()->dispatch('onPostRender');
    }
}

?>
