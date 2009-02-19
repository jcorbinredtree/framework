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

/* NOTE: these are documented in the order in which they are called */
interface ILifeCycle
{
    /**
     * Called when an exception is generated from the system
     *
     * @param Exception $ex the exception
     * @return void
     */
    public function onException(Exception &$ex);

    /**
     * Called when the application first starts
     *
     * @return void
     */
    public function onInitialize();

    /**
     * Invoked to parse GET and POST info out of the URL.
     * Returning a true value stops the processor and leaves
     * the parsing up to you. Tread lightly!
     *
     * @return boolean true if you handled the operation
     */
    public function onURLRewrite();

    /**
     * The request has started, and basic initializations have been performed
     *
     * @return void
     */
    public function onRequestStart();

    /**
     * Called when an action is invoked. Returning a boolean value here will prevent the action
     * from being called, and pass your value through to the framework. There are no guarantees
     * about the chain of events. Your implementation may or may not get called depending on
     * what other actions might be handling this action.
     *
     * @param ActionProvider $provider
     * @param ActionDescription $description
     * @return boolean to handle the action, null to ignore
     */
    public function onAction(ActionProvider &$provider, ActionDescription &$description);

    /**
     * Called to get items for the top navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetTopNavigation();

    /**
     * Called to get items for the right navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetRightNavigation();

    /**
     * Called to get items for the bottom navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetBottomNavigation();

    /**
     * Called to get items for the left navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetLeftNavigation();

    /**
     * Called to get items for the top modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetTopModules();

    /**
     * Called to get items for the right modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetRightModules();

    /**
     * Called to get items for the bottom modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetBottomModules();

    /**
     * Called to get items for the left modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetLeftModules();
}

?>
