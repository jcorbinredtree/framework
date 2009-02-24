<?php

/**
 * ActionProvider class definition
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
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Objects wishing to provide actions should extend this class
 *
 * @package      Components
 * @category     Actions
 */
abstract class ActionProvider extends BufferedObject
{
    /**
     * Holds the list of registered actions
     *
     * @var array
     */
    protected $actions = array();

    /**
     * Called when it's time to register actions
     *
     * @return void
     */
    public abstract function onRegisterActions();

    /**
     * The implementor should override this class to properly execute the
     * given ActionDescription
     *
     * @param ActionDescription $action
     * @param int $stage
     * @return boolean the value of the user_func
     */
    public abstract function perform(ActionDescription &$action, $stage);

    /**
     * Gets the action specified by $id
     *
     * @param string $id the id of the action
     * @return ActionDescription
     */
    public function getAction($id)
    {
        if (!array_key_exists($id, $this->actions)) {
            return null;
        }

        return $this->actions[$id];
    }

    /**
     * Registers ActionDescription with this provider
     *
     * @param mixed $description either an ActionDescription, or an associtive array
     * @return void
     */
    public function registerAction($description)
    {
        $obj = null;

        if (is_array($description)) {
            $obj = new ActionDescription($description);
        }
        elseif ($description instanceof ActionDescription) {
            $obj =& $description;
        }
        else {
            throw new Exception("unknown parameter");
        }

        $this->actions[$obj->id] = $obj;
    }
}

?>
