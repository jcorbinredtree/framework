<?php

/**
 * Email Class definition
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
 * @category     Util
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * Defines standard methods the system may use to ascertain
 * information about this objects subclass.
 *
 * @static
 * @category     Email
 * @package        Utils
 */
class Email
{
    /**
     * Constructor; Private
     *
     * @access private
     * @return Email a new instance
     */
    private function __construct()
    {

    }


    /**
     * A rudimentery validation on the email address, and a check
     * if the specified domain exists. This was found on a devshed
     * article, and modified slightly.
     *
     * @static
     * @param string the email address
     * @return boolean true if the email address is properly formatted,
     * and the domain exists.
     */
    public static function IsValid($email)
    {
        if(!preg_match("/.+@.+/" , $email)) {
            return false;
        }

        list($username, $domain) = split('@', $email);

        if(!checkdnsrr("$domain.", 'MX')) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if the specified email address is found on
     * public blacklists. This method was found in the php.net
     * user notes with the following statement:
     * "written by satmd, do what you want with it, but keep the author please"
     *
     * @static
     * @access public
     * @param string $email the email address in question
     * @return true if the email domain is blacklisted
     */
    public static function IsBlackListed($email)
    {
        list($username, $domain) = split('@', $email);

        $dnsbl_check = array('bl.spamcop.net',
                'list.dsbl.org',
                'sbl.spamhaus.org');

        $ip = gethostbyname($domain);

        $quads = explode('.', $ip);
        if (count($quads) != 4) {
            return false;
        }

        $rip = $quads[3] . '.' . $quads[2] . '.' . $quads[1] . '.' . $quads[0];

        for ($i = 0; $i < count($dnsbl_check); $i++) {
            if (checkdnsrr("$rip." . $dnsbl_check[$i] . '.', 'A')) {
                return true;
            }
        }

        return false;
    }
}

?>
