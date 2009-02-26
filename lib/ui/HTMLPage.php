<?php

/**
 * HTMLPage definition
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

require_once 'lib/ui/HTMLPageAsset.php';
require_once 'lib/ui/HTMLPageMeta.php';

/**
 * HTMLPages are SitePages with codified detail needed to coherently build an
 * HTML page such as:
 *   whether to include an <?xml ... ?> header
 *   doctype
 *   page title
 *   <meta /> data
 *   assets such as scripts, stylesheets, and links
 *
 * @package Ui
 */
class HTMLPage extends SitePage
{
    /**
     * Whether to output a <?xml ... ?> header
     */

    public $xmlHeader = true;
    /**
     * The <!DOCTYPE ... > of the current page
     *
     * This should be a string known by CoreTag::doctype
     *
     * @var string
     * @see CoreTag::doctype
     */
    public $doctype = 'xhtml 1.1';

    /**
     * The title for the page
     *
     * @var string
     */
    public $title;

    /**
     * Page meta data
     *
     * @var HTMLPageMeta
     */
    public $meta;

    /**
     * List of HTMLPageAssets
     *
     * @see addAsset, getAssets
     */
    private $assets;

    /**
     * Constructor
     *
     * Creates a new HTMLPage.
     *
     * While this is publically accessible for flexibility, this should be
     * sparingly used; you likely meant to call the static method Current.
     *
     * @param layout string the layout name, if non-null, setLayout is called
     * with this argument. Whether a layout was specified or not,
     * Site->setHTMLPageLayout will be called after possibly calling setLayout.
     *
     * @param content mixed convenience argument, will be added to the
     * 'content' buffer if a string, the string is treated as a template
     * resource and passed through TemplateSystem::load
     *
     * @param data mixed null or an array of data items to initially set
     *
     * The layout template should start like:
     *   <template
     *     xmlns:core="class://CoreTag"
     *     core:extends="${this.pageTemplate}">
     *
     * Details:
     *   The value of $layout, whatever it ends up being, is set as the
     *   $template property and so is processed by SitePage::render. If the page
     *   layout cares to be a well behaved html page in the normal sense, it
     *   needs to extend the normal html template.
     *
     *   When the HTMLPage is rendered, it sets the template argument
     *   'pageTemplate' to the value of the $template property usually provided
     *   for a text/html page, see SitePage for whatever that means,
     *
     * @see Current, SitePage::$template, SitePage::render, setLayout,
     * Site::setHTMLPageLayout, CoreTag::_extends
     */
    public function __construct($layout=null, $content=null, $data=null)
    {
        parent::__construct('text/html');
        $this->headers->setContentTypeCharset('utf-8');
        $this->assets = array();
        $this->meta = new HTMLPageMeta();
        $this->setData('pageTemplate', $this->template);
        if (isset($layout)) {
            if (preg_match('/(^\/|^\w:\/|\/(~\w*|\.\.)|(~\w*|\.\.)\/)/', $layout)) {
                throw new InvalidArgumentException(
                    "Invalid path components in '$layout'"
                );
            }
            $this->setLayout($layout);
        }
        Site::Site()->layoutHTMLPage($this);
        if (isset($content)) {
            if (is_string($content)) {
                $content = TemplateSystem::load($content);
            }
            $this->addToBuffer('content', $content);
        }
        if (isset($data)) {
            assert(is_array($data));
            foreach ($data as $n => &$v) {
                $this->setData($n, $v);
            }
        }
    }

    /**
     * Sets the page layout
     *
     * The value will be set to the 'pageLayout' data item, the $template
     * property will be set to "layouts/$layout.xml".
     *
     * @param layout string
     * @return void
     */
    public function setLayout($layout)
    {
        $this->setData('pageLayout', $layout);
        $this->template = "layouts/$layout.xml";
    }

    /**
     * Tests whether the page has a layout set
     *
     * @return boolean
     */
    public function hasLayout()
    {
        return $this->hasData('pageLayout');
    }

    /**
     * Returns the layout string
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->getData('pageLayout');
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
     * Returns a list of HTMLPageAsset objects, optionally filtered by asset type
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
            return $this->getAssetsByClass('HTMLPageScript');
        case 'stylesheet':
            return $this->getAssetsByClass('HTMLPageStylesheet');
        case 'link':
            return $this->getAssetsByClass('HTMLPageLinkedResource');
        case 'alternate':
            return $this->getAssetsByClass('HTMLPageAlternateLink');
        case 'linkonly':
            return array_diff(
                $this->getAssetsByClass('HTMLPageLinkedResource'),
                $this->getAssetsByClass('HTMLPageStylesheet')
            );
        default:
            return $this->assets;
        }
    }

    /**
     * Adds an asset to the page if not already added
     *
     * @param asset HTMLPageAsset
     * @return void
     */
    public function addAsset(HTMLPageAsset $asset)
    {
        if (! $this->hasAsset($asset)) {
            array_push($this->assets, $asset);
        }
    }

    /**
     * Tests whether an asset is in this page
     *
     * @param asset HTMLPageAsset
     * @return boolean
     */
    public function hasAsset(HTMLPageAsset $asset)
    {
        foreach ($this->assets as $a) {
            if ($a->compare($asset)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Formats the page title
     *
     * @return string
     */
    public function formatTitle()
    {
        $site = Site::Site();
        if (
            property_exists($site, 'title') &&
            isset($site->title)
        ) {
            $siteTitle = $site->title;
        } elseif (
            property_exists($site->config, 'title') &&
            isset($site->config->title)
        ) {
            $siteTitle = $site->config->title;
        } else {
            return $this->title;
        }
        if ($this->title) {
            return "$siteTitle - $this->title";
        } else {
            return "$siteTitle";
        }
    }
}

?>
