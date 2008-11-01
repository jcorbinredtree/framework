<?php

/**
 * DefaultUser class
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
 * This class implements the IUser class, but defines only one level of user, with crazy simple creds.
 *
 * @package      Security
 * @category     Users
 */
class DefaultUser implements IUser 
{
    public $userName = 'admin';
    public $password = 'admin';

    /**
     * @see IUser::getGroups()
     *
     * @return array
     */
    public function getGroups()
    {
        return array('Administrators');
    }

    /**
     * @see IUser::inGroupName()
     *
     * @param string $name
     * @return boolean
     */
    public function inGroupName($name)
    {
        return ($name == 'Administrators');
    }

    /**
     * @see IUser::isAdministrator()
     *
     * @return boolean
     */
    public function isAdministrator()
    {
        return true;
    }    
}

?>