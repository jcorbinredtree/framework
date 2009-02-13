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
     * Gets the name of the current class
     *
     * @return string the name of the theme class
     */
    public function getClass() {
        return get_class($this);
    }

    /**
     * Displays the application
     *
     * @param WebPage $page the page being displayed
     * @return void
     */
    abstract public function onDisplay(WebPage &$page);

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

    public function createPageTemplate(WebPage &$page)
    {
        return new PageTemplate($page);
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
