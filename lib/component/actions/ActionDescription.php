<?php

/**
 * ActionDescription class definition
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
 * @package      Components
 * @category     Actions
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Defines a structure describing actions
 *
 * @package      Components
 * @category     Actions
 */
class ActionDescription extends NavigatorItem implements ICacheable
{
	/**
	 * One of either a method name of the current component, or
	 * any array capable of passing through call_user_func.
	 *
	 * @var string|array
	 */
    public $handler;

    /**
     * True if this action requires a user
     *
     * @var boolean
     */
    public $requiresUser = false;

    /**
     * The groups allowed to run this action
     *
     * @var array
     */
    public $requireGroups = array();

    /**
     * When set to a boolean value, overrides the settings of
     * $requiresUser or requireGroups, allowing for custom
     * or more complex permissions. Set to null to use the
     * standard access system.
     *
     * @var boolean
     */
    public $isAccessible = null;

    /**
     * Determines whether or not this action is cacheable
     *
     * @var boolean
     */
    public $cacheable = false;

    /**
     * The handler to call when useCache is called in call_user_func
     * format. If you set this === true it will always try to use the cache
     *
     * @var array
     */
    public $useCacheHandler = null;

    /**
     * Constructor with variable args
     *
     * @param array $args an associtive array of properties and values
     * @throws Exception if array key doesn't match property
     */
    public function __construct($args=array())
    {
        foreach ($args as $key => $val) {
            if (!property_exists($this, $key)) {
                throw new Exception("unknown property $key");
            }

            $this->$key = $val;
        }
    }

    /**
     * Determines if the component is cacheable for this action.
     *
     * @return boolean
     */
    public function isCacheable()
    {
        return $this->cacheable;
    }

    /**
     * Sets the cacheability of this action
     *
     * @param boolean $val
     * @return void
     */
    public function setCacheable($val)
    {
        $this->cacheable = $val;
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
        if ($this->useCacheHandler) {
            if ($this->useCacheHandler === true) {
                return true;
            }

            return call_user_func($this->useCacheHandler, $cacheModifiedTime);
        }

        return false;
    }
}

?>