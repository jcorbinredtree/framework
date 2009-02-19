<?php

/**
 * Theme class definition
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
 * Sets up the abstract definition of a Theme
 *
 * @package        Themes
 */
// Old style themes used to do silly things like write content themselves, so
// until we berak compatability with old themes entirely, we still need to
// extend BufferedObject
abstract class Theme extends BufferedObject
{
    private $path = null;

    /**
     * The page that this theme styles
     *
     * @var HTMLPage
     */
    protected $page;

    /**
     * Creats a theme object for a page
     *
     * @param page HTMLPage optional, defaults to SitePage::getCurrent
     */
    public function __construct($page=null)
    {
        if (! isset($page)) {
            $page = SitePage::getCurrent();
        } elseif (! is_a($page, 'HTMLPage')) {
            throw new InvalidArgumentException('Not a HTMLPage');
        }
        $this->page = $page;

        $page->addCallback('prerender', array($this, 'pushPath'));
        if (method_exists($this, 'onDisplay')) {
            // Deprecated pre 3.0.76 interface
            global $config;
            if ($config->isDebugMode()) {
                $cls = get_class($this);
                $config->deprecatedComplain(
                    $cls.'->onDisplay', 'prerender page callback'
                );
            }
            $page->addCallback('prerender', array($this, 'onDisplayShim'));
        }
        if (method_exists($this, 'onRender')) {
            $page->addCallback('prerender', array($this, 'onRender'));
        }
        $page->addCallback('prerender', array($this, 'popPath'));
    }

    private $oldPath = null;
    private $pushed = false;
    /**
     * Sets CurrentPath to our path, saves the old one for later restoration.
     */
    public function pushPath()
    {
        if ($this->pushed) {
            return;
        }
        $this->oldPath = CurrentPath::set($this->getPath());
        $this->pushed = true;
    }

    /**
     * Converse of pushPath
     */
    public function popPath()
    {
        if ($this->pushed) {
            CurrentPath::set($this->oldPath);
            $this->pushed = false;
        }
    }

    /**
     * Compatability shim that sets curret path for old onDisplay hooks
     *
     * Deprecated, keeps pre 3.0.76 themes working
     */
    final private function onDisplayShim(SitePage $page)
    {
        $this->onDisplay();
        $page->addToBuffer('content', $this->getBuffer());
        $this->clear();
    }

    /**
     * Gets image based on the theme
     *
     * @param string $key
     * @return string
     */
    public function getImage($key)
    {
        return $this->getPath() . "/view/images/$key";
    }

    /**
     * Gets icon based on the theme
     *
     * @param string $key
     * @return string
     */
    public function getIcon($key)
    {
        return $this->getPath() . "/view/icons/$key.png";
    }

    public function getPath()
    {
        if (! $this->path) {
            $us = new ReflectionClass(get_class($this));
            $this->path = dirname($us->getFileName());
        }
        return $this->path;
    }

    /**
     * Returns an instance of the specified theme
     *
     * @static
     * @access public
     * @param string $theme a theme class name
     * @param SitePage $page
     * @return Theme an instance of the specified theme
     */
    static public function load($theme, SitePage $page=null)
    {
        return new $theme($page);
    }
}

?>
