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
class LayoutDescription extends HTMLPage
{
    /**
     * Meta keywords
     * DEPRECATED, there's no way to sanely make this work properly with HTMLPage
     *
     * @var string
     */
    public $keywords;

    /**
     * The meta description
     * DEPRECATED, there's no way to sanely make this work properly with HTMLPage
     *
     * @var string
     */
    public $description;

    /**
     * Additional meta head
     * DEPRECATED, there's no way to sanely make this work properly with HTMLPage
     *
     * @var string
     */
    public $head;

    /**
     * The scripts used for this page
     * DEPRECATED, there's no way to sanely make this work properly with HTMLPage
     *
     * @var array
     */
    public $scripts = array();

    /**
     * The stylesheets to include
     * DEPRECATED, there's no way to sanely make this work properly with HTMLPage
     *
     * @var array
     */
    public $stylesheets = array();

    /**
     * Breadcrumbs used for this page
     * DEPRECATED, there's no way to sanely make this work properly with HTMLPage
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
     * Adds a top module
     *
     * @param Module $module
     * @return void
     */
    public function addTopModule(Module &$module)
    {
        $this->addToBuffer('top', $module);
    }

    /**
     * Gets the modules meant for the top of the page
     *
     * @return array
     */
    public function getTopModules()
    {
        $r = $this->getBuffer('top', true);
        if (! isset($r)) {
            $r = array();
        }
        return array_merge($r, LifeCycleManager::onGetTopModules());
    }

    /**
     * Adds a left module
     *
     * @param Module $module
     * @return void
     */
    public function addLeftModule(Module &$module)
    {
        $this->addToBuffer('left', $module);
    }

    /**
     * Gets the modules meant for the left of the page
     *
     * @return array
     */
    public function getLeftModules()
    {
        $r = $this->getBuffer('left', true);
        if (! isset($r)) {
            $r = array();
        }
        return array_merge($r, LifeCycleManager::onGetLeftModules());
    }

    /**
     * Adds a right module
     *
     * @param Module $module
     * @return void
     */
    public function addRightModule(Module &$module)
    {
        $this->addToBuffer('right', $module);
    }

    /**
     * Gets the modules meant for the right of the page
     *
     * @return array
     */
    public function getRightModules()
    {
        $r = $this->getBuffer('right', true);
        if (! isset($r)) {
            $r = array();
        }
        return array_merge($r, LifeCycleManager::onGetRightModules());
    }

    /**
     * Adds a bottom module
     *
     * @param Module $module
     * @return void
     */
    public function addBottomModule(Module &$module)
    {
        $this->addToBuffer('bottom', $module);
    }

    /**
     * Gets the modules meant for the bottom of the page
     *
     * @return array
     */
    public function getBottomModules()
    {
        $r = $this->getBuffer('bottom', true);
        if (! isset($r)) {
            $r = array();
        }
        return array_merge($r, LifeCycleManager::onGetBottomModules());
    }

    /**
     * Adds a top module
     *
     * @param Navigation $module
     * @return void
     */
    public function addTopNavigation(NavigatorItem &$navigation)
    {
        $this->addData('topNavigation', $navigation);
    }

    /**
     * Gets the navigation meant for the top of the page
     *
     * @return array
     */
    public function getTopNavigation()
    {
        $r = $this->getBuffer('topNavigation', true);
        if (! isset($r)) {
            $r = array();
        }
        return array_merge($r, LifeCycleManager::onGetTopNavigation());
    }

    /**
     * Adds a left module
     *
     * @param Navigation $module
     * @return void
     */
    public function addLeftNavigation(NavigatorItem &$navigation)
    {
        $this->addData('leftNavigation', $navigation);
    }

    /**
     * Gets the navigation meant for the left of the page
     *
     * @return array
     */
    public function getLeftNavigation()
    {
        $r = $this->getBuffer('leftNavigation', true);
        if (! isset($r)) {
            $r = array();
        }
       return array_merge($r, LifeCycleManager::onGetLeftNavigation());
    }

    /**
     * Adds a right module
     *
     * @param Navigation $module
     * @return void
     */
    public function addRightNavigation(NavigatorItem &$navigation)
    {
        $this->addData('rightNavigation', $navigation);
    }

    /**
     * Gets the navigation meant for the right of the page
     *
     * @return array
     */
    public function getRightNavigation()
    {
        $r = $this->getBuffer('rightNavigation', true);
        if (! isset($r)) {
            $r = array();
        }
        return array_merge($r, LifeCycleManager::onGetRightNavigation());
    }

    /**
     * Adds a bottom module
     *
     * @param Navigation $module
     * @return void
     */
    public function addBottomNavigation(NavigatorItem &$navigation)
    {
        $this->addData('bottomNavigation', $navigation);
    }

    /**
     * Gets the navigation meant for the bottom of the page
     *
     * @return array
     */
    public function getBottomNavigation()
    {
        $r = $this->getBuffer('bottomNavigation', true);
        if (! isset($r)) {
            $r = array();
        }
        return array_merge($r, LifeCycleManager::onGetBottomNavigation());
    }
}
?>
