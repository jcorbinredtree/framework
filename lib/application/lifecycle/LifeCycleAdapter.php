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

abstract class LifeCycleAdapter implements ILifeCycle
{
    /**
     * Called when an action is invoked. Returning a boolean value here will prevent the action
     * from being called, and pass your value through to the framework.
     *
     * @param ActionProvider $provider
     * @param ActionDescription $description
     * @return boolean to handle the action, null to ignore
     */
    public function onAction(ActionProvider &$provider, ActionDescription &$description)
    {
        global $config;

        $config->info("onAction");
        return null;
    }

    /**
     * Called to get items for the top navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetTopNavigation()
    {
        global $config;

        $config->info("onGetXXXNavigation");
        return array();
    }

    /**
     * Called to get items for the right navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetRightNavigation()
    {
        global $config;

        $config->info("onGetXXXNavigation");
        return array();
    }

    /**
     * Called to get items for the bottom navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetBottomNavigation()
    {
        global $config;

        $config->info("onGetXXXNavigation");
        return array();
    }

    /**
     * Called to get items for the left navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetLeftNavigation()
    {
        global $config;

        $config->info("onGetXXXNavigation");
        return array();
    }

    /**
     * Called to get items for the top modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetTopModules()
    {
        global $config;

        $config->info("onGetXXXModules");
        return array();
    }

    /**
     * Called to get items for the right modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetRightModules()
    {
        global $config;

        $config->info("onGetXXXModules");
        return array();
    }

    /**
     * Called to get items for the bottom modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetBottomModules()
    {
        global $config;

        $config->info("onGetXXXModules");
        return array();
    }

    /**
     * Called to get items for the left modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetLeftModules()
    {
        global $config;

        $config->info("onGetXXXModules");
        return array();
    }

    /**
     * Invoked before anything is written to the browser, but after everything has
     * been collected in the WebPage object
     *
     * @param WebPage $page
     * @return void
     */
    public function onPreRender(WebPage &$page)
    {
        global $config;

        $config->info("onPreRender");
    }

    /**
     * Called after the contents have been written to the browser.
     *
     * @return void
     */
    public function onPostRender()
    {
        global $config;

        $config->info("onPostRender");
    }
}

?>
