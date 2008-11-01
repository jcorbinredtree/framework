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
 * This interface defines what it means to be a user
 *
 * @package      Security
 * @category     Users
 */
interface IUserFactory
{
    /**
     * Called when a user attemtps to log in. Return true and set up a value in the session
     * if this user is authenticated by the credentials.
     *
     * @param string $un The user name
     * @param string $pass The password
     * @return boolean true if the user successfully logged in
     */
    public function login($un, $pass);
    
    /**
     * This is called on every request to load a user presumably based
     * on a saved session key set with IUser::login;
     *
     * @return IUser if you restored a user from the session, otherwise null
     */
    public function restore();

    /**
     * Called to log a user out of the system.
     *
     * @return void
     */
    public function logout();
}

?>