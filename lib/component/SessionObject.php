<?php

/**
 * SessionObject class definition
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
 * @category     Utils
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * SessionObject
 *
 * This defines standard methods used for objects that are placed into the session.
 * Sadly, this can not be an abstract, but that is the intention.
 *
 * @package        Utils
 */
abstract class SessionObject extends RequestObject
{
    /**
     * Returns the current class as an object from the session
     *
     * @access public
     * @static
     * @param string $key the key to retrieve
     * @return object the class instaniation or null if there is none
     */
    public static function fromSession($key)
    {
        return unserialize(Params::SESSION($key));
    }

    /**
     * Saves the object into the session
     *
     * @access public
     * @param string $key the key to save to
     * @return void
     */
    public function save($key)
    {
        $_SESSION[$key] = serialize($this);
    }
}

?>
