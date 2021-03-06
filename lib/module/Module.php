<?php

/**
 * Module base class definition
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
 * @category     Modules
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Sets up the abstract definition of a Module. This is the base class
 * from which all user-defined module are derived.
 *
 * @package      Modules
 */
abstract class Module extends BufferedObject implements ICacheable
{
    /**
     * Describes the left position
     *
     * @var int
     */
    const POSITION_LEFT = 1;

    /**
     * Describes the top position
     *
     * @var int
     */
    const POSITION_TOP = 2;

    /**
     * Describes the right position
     *
     * @var int
     */
    const POSITION_RIGHT = 3;

    /**
     * Describes the bottom position
     *
     * @var int
     */
    const POSITION_BOTTOM = 4;

    /**
     * Constructor
     */
    public function __construct()
    {
        $oldPath = CurrentPath::set($this->getPath());
        $this->onInitialize();
        CurrentPath::set($oldPath);
    }

    /**
     * Returns the path to this module
     *
     * The base class presumes that the file that defines a module class is
     * in the toplevel module directory, example:
     *   File: /somewhere/SITE/local/modules/SomeCog/SomeCog.php
     *     class SomeCog extends Module { ... }
     *   Then:
     *     $c = new SomeCog();
     *     echo $c->getPath(); // prints /somewhere/SITE/local/modules/SomeCog
     *
     * @return string
     */
    public function getPath()
    {
        $us = new ReflectionClass(get_class($this));
        return dirname($us->getFileName());
    }

    /**
     * Called when the module is first loaded
     *
     * @return void
     */
    public function onInitialize()
    {
    }

    /**
     * Displays the current module
     *
     * @param Page $page the page being displayed
     * @return void
     */
    abstract public function onDisplay($page);

    /**
     * Returns an instance of the specified module, and performs basic initializations on it.
     *
     * @static
     * @access public
     * @param string $module module class name
     * @return Module an instance of the specified module on success; null otherwise
     */
    static public function load($module)
    {
        if (!class_exists($module)) {
            $path = "modules/$module/$module.php";

            if (!file_exists($path)) {
                return null;
            }

            include_once $path;
        }

        return new $module();
    }

    /**
     * Determines if the component is cacheable for this action. Always returns false.
     *
     * @return boolean false
     */
    public function isCacheable()
    {
        return false;
    }

    /**
     * Determines if this component should be retrieved from the cache
     *
     * @access public
     * @param int $cacheModifiedTime the last time (as unix time)
     * the cache was modified for this item
     * @return boolean false
     */
    public function useCache($cacheModifiedTime)
    {
        return false;
    }
}

?>
