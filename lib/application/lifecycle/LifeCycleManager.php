<?php

class LifeCycleManager
{
    private static $lifeCyclers = array();

    private function __construct() { }

    public static function add(ILifeCycle &$item)
    {
        array_push(LifeCycleManager::$lifeCyclers, $item);
    }

    public static function remove(ILifeCycle &$item)
    {
        $lcs = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            if ($item != $lco) {
                array_push($lcs, $lco);
            }
        }

        LifeCycleManager::$lifeCyclers = $lcs;
    }

    public static function onInitialize()
    {
        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $lco->onInitialize();
        }
    }

    public static function onException(Exception &$ex)
    {
        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $lco->onException($ex);
        }

        return false;
    }

    public static function onURLRewrite()
    {
        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            if ($lco->onURLRewrite()) {
                return true;
            }
        }

        return false;
    }

    public static function onRequestStart()
    {
        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $lco->onRequestStart();
        }
    }

    public static function onAction(ActionProvider &$provider, ActionDescription &$description)
    {
        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            if (null !== ($res = $lco->onAction($provider, $description))) {
                return $res;
            }
        }

        return null;
    }

    /**
     * Called to get items for the top navigation
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetTopNavigation()
    {
        $out = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $out = array_merge($out, $lco->onGetTopNavigation());
        }

        return $out;
    }

    /**
     * Called to get items for the right navigation
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetRightNavigation()
    {
        $out = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $out = array_merge($out, $lco->onGetRightNavigation());
        }

        return $out;
    }

    /**
     * Called to get items for the bottom navigation
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetBottomNavigation()
    {
        $out = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $out = array_merge($out, $lco->onGetBottomNavigation());
        }

        return $out;
    }

    /**
     * Called to get items for the left navigation
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetLeftNavigation()
    {
        $out = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $out = array_merge($out, $lco->onGetLeftNavigation());
        }

        return $out;
    }

    /**
     * Called to get items for the top modules
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetTopModules()
    {
        $out = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $out = array_merge($out, $lco->onGetTopModules());
        }

        return $out;
    }

    /**
     * Called to get items for the right modules
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetRightModules()
    {
        $out = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $out = array_merge($out, $lco->onGetRightModules());
        }

        return $out;
    }

    /**
     * Called to get items for the bottom modules
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetBottomModules()
    {
        $out = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $out = array_merge($out, $lco->onGetBottomModules());
        }

        return $out;
    }

    /**
     * Called to get items for the left modules
     *
     * @return array of NavigatorItem objects
     */
    public static function onGetLeftModules()
    {
        $out = array();

        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $out = array_merge($out, $lco->onGetLeftModules());
        }

        return $out;
    }

    public static function onPreRender(LayoutDescription &$layout)
    {
        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $lco->onPreRender($layout);
        }
    }

    public static function onPostRender()
    {
        foreach (LifeCycleManager::$lifeCyclers as &$lco) {
            $lco->onPostRender();
        }
    }
}

?>
