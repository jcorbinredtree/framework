<?php

/**
 * IUser interface
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
 * @package      Security
 * @category     Users
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2008 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * This interface defines the expected methods of users
 *
 * @package      Security
 * @category     Users
 */
interface IUser
{
    /**
     * Determines if the user should be treated as an administrator
     *
     * @return boolean true if this user is an administrator
     */
    public function isAdministrator();

    /**
     * Determines if the user belongs to the specified group
     *
     * @param string $name the name of the group
     * @return boolean true if this user belongs to the group
     */
    public function inGroupName($name);
    
    /**
     * Returns the groups this user belongs to
     *
     * @return array an array of group names this user belongs to
     */
    public function getGroups();
}

?>