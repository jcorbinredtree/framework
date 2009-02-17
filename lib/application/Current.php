<?php

/**
 * Current class definition
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
 * @category     Core
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Contains information about the current state of the framework.
 *
 * There should be only one instance of this class throughout the platform,
 * but is not made into a singleton class.
 *
 * @package        Core
 */
class Current
{
    /**
     * Holds the current id
     *
     * @var int
     */
    public $id = 0;

    /**
     * Holds the current user, if available
     *
     * @access public
     * @var IUser
     */
    public $user = null;

    /**
     * Holds the current stage
     *
     * @var int
     */
    public $stage = Stage::VIEW;

    /**
     * Holds the current theme
     *
     * @access public
     * @var Theme
     */
    public $theme = null;

    /**
     * Holds the current component. This is not
     * entirely accurate, as it actually holds the
     * component REQUESTED, which may not actually
     * be the current component (say, if one were
     * required by the main component).
     *
     * @access public
     * @var Component
     */
    public $component = null;

    /**
     * Holds a reference to the current LayoutDescription
     *
     * DEPRECATED
     * TODO: migrate anything using this to using WebPage::getCurrent
     *
     * @var LayoutDescription
     */
    public $layout = null;

    /**
     * Holds the current action
     *
     * @access public
     * @var ActionDescription
     */
    public $action = null;

    /**
     * Holds the current path. The current path is set to
     * the last operating component, module, or theme's directory.
     * It is safe to set this value via Application::setPath.
     *
     * @access public
     * @var string
     */
    public $path = "";

    /**
     * Determine whether or not the request is secure
     *
     * @since 1.4
     * @access private
     * @var boolean
     */
    private $secure = false;

    /**
     * Holds the current warning messages to print to
     * the user
     *
     * @access private
     * @var array
     */
    private $warnings = array();

    /**
     * Holds the current warning messages to print to
     * the user, in an associtive format
     *
     * @access private
     * @var array
     */
    private $keyedWarnings = array();

    /**
     * Holds the current notice messages to print to
     * the user
     *
     * @access private
     * @var array
     */
    private $notices = array();

    /**
     * Constructor
     *
     * @access public
     * @return Current a new instance
     */
    public function __construct()
    {
    }

    /**
     * Sets whether this is a secure (https) request.
     *
     * @since 1.4
     * @access public
     * @param boolean the value to set
     * @return void
     */
    public function setSecureRequest($val=true)
    {
        $this->secure = $val;
    }

    /**
     * Returns true if this is a secure request. Note
     * that this is only a value, and does not actually
     * determine if this is actually a secure request,
     * but it is generally believed to coincide with
     * reality.
     *
     * @since 1.4
     * @access public
     * @return boolean true if this is a secure request
     */
    public function isSecureRequest()
    {
        return $this->secure;
    }

    /**
     * Gets the current request again, with the specified options
     *
     * @see Component::GetActionURI
     * @access public
     * @param array addtional options to pass to Component::GetActionURI
     * @return string an url suitable for browsing to
     */
    public function getCurrentRequest($addOptions=null)
    {
        global $config;

        $options = array_merge($_GET, $_POST);

        unset($options[AppConstants::COMPONENT_KEY]);
        unset($options[AppConstants::ACTION_KEY]);

        if ($addOptions) {
            $options = array_merge($options, $addOptions);
        }

        $policy = PolicyManager::getInstance();
        return $policy->getActionURI($this->component->getClass(), $this->action->id, $options, $this->stage);
    }

    /**
     * Adds a warning to the system. This will be printed to the
     * browser.
     *
     * @param string $warning the warning
     * @param string $key adds this warning to the keyed warnings
     * @return void
     */
    public function addWarning($warning, $key='')
    {
        if ($key) {
            if (!array_key_exists($key, $this->keyedWarnings)) {
                $this->keyedWarnings[$key] = array();
            }

            array_push($this->keyedWarnings[$key], $warning);
        }
        else {
            array_push($this->warnings, $warning);
        }
    }

    /**
     * Get the current warnings as an array
     *
     * @access public
     * @return array the warnings
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Get the keyed warnings
     *
     * @access public
     * @return array keyed warnings
     */
    public function getKeyedWarnings()
    {
        return $this->keyedWarnings;
    }

    /**
     * Adds a notice to the system. This will be printed to the
     * browser.
     *
     * @param string the notice
     * @return void
     */
    public function addNotice($notice)
    {
        array_push($this->notices, $notice);
    }

    /**
     * Clears the current warnings
     *
     * @since v1.5
     * @return void
     */
    public function clearWarnings()
    {
        $this->warnings = array();
    }

    /**
     * Clears the current notices
     *
     * @since v1.5
     * @return void
     */
    public function clearNotices()
    {
        $this->notices = array();
    }

    /**
     * Get the current notices as an array
     *
     * @access public
     * @return array the notices
     */
    public function getNotices()
    {
        return $this->notices;
    }
}

?>