<?php

/**
 * Layout Description definition
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
 * @category     UI
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Holds all the information a theme could possibly want
 *
 * @package      UI
 */
class LayoutDescription
{
    /**
     * Holds the left modules
     *
     * @var array
     */
    private $leftModules = array();

    /**
     * Holds the top modules
     *
     * @var array
     */
    private $topModules = array();

    /**
     * Holds the right modules
     *
     * @var array
     */
    private $rightModules = array();

    /**
     * Holds the bottom modules
     *
     * @var array
     */
    private $bottomModules = array();

    /**
     * Holds the left navigation
     *
     * @var array
     */
    private $leftNavigation = array();

    /**
     * Holds the top navigation
     *
     * @var array
     */
    private $topNavigation = array();

    /**
     * Holds the right navigation
     *
     * @var array
     */
    private $rightNavigation = array();

    /**
     * Holds the bottom navigation
     *
     * @var array
     */
    private $bottomNavigation = array();

    /**
     * Meta keywords
     *
     * @var string
     */
    public $keywords;

    /**
     * The meta description
     *
     * @var string
     */
    public $description;

    /**
     * Additional meta head
     *
     * @var string
     */
    public $head;

    /**
     * The title for the page
     *
     * @var string
     */
    public $title;

    /**
     * The scripts used for this page
     *
     * @var array
     */
    public $scripts = array();

    /**
     * The stylesheets to include
     *
     * @var array
     */
    public $stylesheets = array();

    /**
     * Breadcrumbs used for this page
     *
     * @var array
     */
    public $breadCrumbs;

    /**
     * The current component
     *
     * @var Component
     */
    public $component;

    /**
     * The current component's buffer
     *
     * @var string
     */
    public $content;

    /**
     * The current top level item
     *
     * @var NavigatorItem
     */
    public $topLevelItem;

    /**
     * The current item
     *
     * @var NavigatorItem
     */
    public $currentItem;

    /**
     * Determines if this is the home page
     *
     * @var boolean
     */
    public $isHomePage;

    /**
     * Determines if this is a popup window
     *
     * @var boolean
     */
    public $isPopup;

    /**
     * The search phrase
     *
     * @var string
     */
    public $searchWord;

    /**
     * Gets the current set of warnings
     *
     * @return array
     */
    public function getWarnings()
    {
        global $current;

        return $current->getWarnings();
    }

    /**
     * Gets the current set of notices
     *
     * @return array
     */
    public function getNotices()
    {
        global $current;

        return $current->getNotices();
    }

    /**
     * Adds a top module
     *
     * @param Module $module
     * @return void
     */
    public function addTopModule(Module &$module)
    {
        array_push($this->topModules, $module);
    }

    /**
     * Gets the modules meant for the top of the page
     *
     * @return array
     */
    public function getTopModules()
    {
        return array_merge($this->topModules, LifeCycleManager::onGetTopModules());
    }

    /**
     * Adds a left module
     *
     * @param Module $module
     * @return void
     */
    public function addLeftModule(Module &$module)
    {
        array_push($this->leftModules, $module);
    }

    /**
     * Gets the modules meant for the left of the page
     *
     * @return array
     */
    public function getLeftModules()
    {
       return array_merge($this->leftModules, LifeCycleManager::onGetLeftModules());
    }

    /**
     * Adds a right module
     *
     * @param Module $module
     * @return void
     */
    public function addRightModule(Module &$module)
    {
        array_push($this->rightModules, $module);
    }

    /**
     * Gets the modules meant for the right of the page
     *
     * @return array
     */
    public function getRightModules()
    {
        return array_merge($this->rightModules, LifeCycleManager::onGetRightModules());
    }

    /**
     * Adds a bottom module
     *
     * @param Module $module
     * @return void
     */
    public function addBottomModule(Module &$module)
    {
        array_push($this->bottomModules, $module);
    }

    /**
     * Gets the modules meant for the bottom of the page
     *
     * @return array
     */
    public function getBottomModules()
    {
       return array_merge($this->bottomModules, LifeCycleManager::onGetBottomModules());
    }

    /**
     * Adds a top module
     *
     * @param Navigation $module
     * @return void
     */
    public function addTopNavigation(NavigatorItem &$navigation)
    {
        array_push($this->topNavigation, $navigation);
    }

    /**
     * Gets the navigation meant for the top of the page
     *
     * @return array
     */
    public function getTopNavigation()
    {
        return array_merge($this->topNavigation, LifeCycleManager::onGetTopNavigation());
    }

    /**
     * Adds a left module
     *
     * @param Navigation $module
     * @return void
     */
    public function addLeftNavigation(NavigatorItem &$navigation)
    {
        array_push($this->leftNavigation, $navigation);
    }

    /**
     * Gets the navigation meant for the left of the page
     *
     * @return array
     */
    public function getLeftNavigation()
    {
       return array_merge($this->leftNavigation, LifeCycleManager::onGetLeftNavigation());
    }

    /**
     * Adds a right module
     *
     * @param Navigation $module
     * @return void
     */
    public function addRightNavigation(NavigatorItem &$navigation)
    {
        array_push($this->rightNavigation, $navigation);
    }

    /**
     * Gets the navigation meant for the right of the page
     *
     * @return array
     */
    public function getRightNavigation()
    {
        return array_merge($this->rightNavigation, LifeCycleManager::onGetRightNavigation());
    }

    /**
     * Adds a bottom module
     *
     * @param Navigation $module
     * @return void
     */
    public function addBottomNavigation(NavigatorItem &$navigation)
    {
        array_push($this->bottomNavigation, $navigation);
    }

    /**
     * Gets the navigation meant for the bottom of the page
     *
     * @return array
     */
    public function getBottomNavigation()
    {
       return array_merge($this->bottomNavigation, LifeCycleManager::onGetBottomNavigation());
    }
}
?>
