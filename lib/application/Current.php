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
     * Holds the current user, if available
     *
     * @access public
     * @var IUser
     */
    public $user = null;

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
     * Holds the current path. The current path is set to
     * the last operating component, or module's directory.
     *
     * @access public
     * @var CurrentPath
     * @see CurrentPath::set
     */
    public $path = null;

    /**
     * Constructor
     *
     * @access public
     * @return Current a new instance
     */
    public function __construct()
    {
        $this->path = new CurrentPath(Loader::$Base);
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
        $options = array_merge($_GET, $_POST);

        if ($addOptions) {
            $options = array_merge($options, $addOptions);
        }

        return $this->component->getActionURI(
            $this->action->id, $options, $this->stage
        );
    }
}

?>
