<?php
/**
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
 */

class DefaultSecurityPolicy implements ISecurityPolicy
{

    /**
     * @see ISecurityPolicy::getLoginUrl()
     */
    public function getLoginUrl()
    {
        global $config;

        return "$config->absUri/login";
    }

    /**
     * @see ISecurityPolicy::login()
     *
     * @param string $un
     * @param string $pass
     * @return boolean
     */
    public function login($un, $pass)
    {
        if (($un == 'admin') && ($pass == 'admin')) {
            Session::set('__uid', 1);
            return true;
        }

        return false;
    }

    /**
     * @see ISecurityPolicy::logout()
     *
     */
    public function logout()
    {

    }

    /**
     * @see ISecurityPolicy::restore()
     *
     * @return IUser
     */
    public function restore()
    {
        global $config, $current;

        $uid = Session::get('__uid');
        if (!$uid) {
            return;
        }

        return new DefaultUser();
    }
}

?>
