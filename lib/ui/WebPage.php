<?php

/**
 * WebPage definition
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
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

require_once(dirname(__FILE__).'/WebPageAsset.php');

/**
 * WebPages are themed SitePages
 *
 * @package Ui
 */
class WebPage extends SitePage
{
    /**
     * The title for the page
     *
     * @var string
     */
    public $title;

    /**
     * Page meta data
     *
     * @var WebPageMeta
     */
    public $meta;

    /**
     * List of WebPageAssets
     *
     * @see addAsset, getAssets
     */
    private $assets;

    /**
     * Holds the current theme object
     *
     * @var Theme
     */
    private $theme;

    /**
     * Constructor
     *
     * Creates a new WebPage.
     *
     * While this is publically accessible for flexibility, this should be
     * sparingly used; you likely meant to call the static method Current.
     *
     * @see Current
     */
    public function __construct()
    {
        parent::__construct('text/html');
        $this->assets = array();
        $this->meta = new WebPageMeta();
    }

    /**
     * Returns the theme that should render the current page
     *
     * @return Theme
     */
    public function getTheme()
    {
        if (! isset($this->theme)) {
            $policy = PolicyManager::getInstance();
            if ($this->hasData('exception')) {
                $theme = $policy->getExceptionTheme();
            } else {
                $theme = $policy->getTheme();
            }
            if (! $theme) {
                throw new RuntimeException(
                    'A theme could not be resonably resolved'
                );
            }
            $this->theme = $theme;
        }
        return $this->theme;
    }

    /**
     * Returns a list of assets in this page filtered by class
     *
     * You likely wanted to call getAssets
     *
     * @param class string
     * @return array
     */
    public function getAssetsByClass($class)
    {
        $r = array();
        foreach ($this->assets as $asset) {
            if (is_a($asset, $class)) {
                array_push($r, $asset);
            }
        }
        return $r;
    }

    /**
     * Returns a list of WebPageAsset objects, optionally filtered by asset type
     *
     * @param type string optional, one of:
     *   script     - returns script assets
     *   stylesheet - returns stylesheet assets
     *   link       - returns link assets
     *   linkonly   - returns link assets that aren't stylesheets
     *   alternate  - returns alternate link assets
     * @return array
     */
    public function getAssets($type=null)
    {
        switch ($type) {
        case 'script':
            return $this->getAssetsByClass('WebPageScript');
        case 'stylesheet':
            return $this->getAssetsByClass('WebPageStylesheet');
        case 'link':
            return $this->getAssetsByClass('WebPageLinkedResource');
        case 'alternate':
            return $this->getAssetsByClass('WebPageAlternateLink');
        case 'linkonly':
            return array_diff(
                $this->getAssetsByClass('WebPageLinkedResource'),
                $this->getAssetsByClass('WebPageStylesheet')
            );
        default:
            return $this->assets;
        }
    }

    /**
     * Adds an asset to the page if not already added
     *
     * @param asset WebPageAsset
     * @return void
     */
    public function addAsset(WebPageAsset $asset)
    {
        if (! $this->hasAsset($asset)) {
            array_push($this->assets, $asset);
        }
    }

    /**
     * Tests whether an asset is in this page
     *
     * @param asset WebPageAsset
     * @return boolean
     */
    public function hasAsset(WebPageAsset $asset)
    {
        foreach ($this->assets as $a) {
            if ($a->compare($asset)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the page title
     *
     * If the current theme defines a 'formatPageTitle' method, then it is
     * called with the page title as argument, and its return value passed on;
     * otherwise the $title property is simply returned.
     *
     * @return string
     */
    public function formatTitle()
    {
        $theme = $this->getTheme();
        if (method_exists($theme, 'formatPageTitle')) {
            return $theme->formatPageTitle($this->title);
        } else {
            return $this->title;
        }
    }
}

?>
