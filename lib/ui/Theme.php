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
abstract class Theme extends BufferedObject
{
    private $path = null;

    /**
     * The page that this theme styles
     *
     * @var WebPage
     */
    protected $page;

    /**
     * The template used to implement the final page rendering, such as
     * xhtmlpage.xml
     *
     * @var PageTemplate
     */
    protected $pageTemplate;

    /**
     * Creats a theme object for a page
     *
     * @param page WebPage optional, defaults to WebPage::getCurrent
     */
    public function __construct($page=null)
    {
        if (! isset($page)) {
            $page = WebPage::getCurrent();
        } elseif (! is_a($page, 'WebPage')) {
            throw new InvalidArgumentException('Not a WebPage');
        }
        $this->page = $page;

        $this->pageTemplate = new PageTemplate($this->page);
        $this->pageTemplate->assign('theme', $this);
    }

    /**
     * Gets the name of the current class
     *
     * @return string the name of the theme class
     */
    public function getClass()
    {
        return get_class($this);
    }

    /**
     * Renders the theme
     *
     * This calls onRender, processes the page template, and then flushes
     * output
     *
     * @return void
     */
    public function render()
    {
        // set the current path to the theme location & display
        $oldPath = Application::setPath($this->getPath());

        LifeCycleManager::onPreRender($this->page);

        if (method_exists($this, 'onDisplay')) {
            global $config;
            // Deprecated pre 3.0.76 interface
            if ($config->isDebugMode()) {
                $cls = get_class($this);
                $config->deprecatedComplain(
                    $cls.'->onDisplay', $cls.'->onRender'
                );
            }
            $this->onDisplay($this->page);
        }

        $this->onRender();

        $this->write($this->pageTemplate->render());
        $this->flush();

        LifeCycleManager::onPostRender();

        Application::setPath($oldPath);
    }

    /**
     * Called by render to do final page layout processing
     *
     * Does nothing in the abstract, you likely want to override this
     *
     * @param WebPage $page the page being displayed
     * @return void
     */
    protected function onRender()
    {
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
        if ($this->path) {
            return $this->path;
        }

        $us = new ReflectionClass($this->getClass());
        return $this->path = dirname($us->getFileName());
    }

    /**
     * Returns an instance of the specified theme
     *
     * @static
     * @access public
     * @param string $theme a theme class name
     * @return Theme an instance of the specified theme
     */
    static public function load($theme)
    {
        $us = new $theme();
        Application::setPath($us->getPath());

        return $us;
    }
}

?>
