<?php

/**
 * ILocationPolicy interface definition
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
 * @category     Application
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Defines a location policy. An implementation of this policy may be set to change the
 * framework's standard behavior.
 *
 * @category     Policies
 * @package      Core
 */
interface ILocationPolicy
{
    /**
     * Gets the location of the compiled templates directory.
     * This directory should be writable.
     *
     * @return string the location of the templates directory.
     */
    public function getTemplatesDir();

    /**
     * Gets the location of the cache directory.
     * This directory should be writable.
     *
     * @return string the location of the cache directory.
     */
    public function getCacheDir();

    /**
     * Gets the location of the logs directory.
     * This directory should be writable.
     *
     * @return string the location of the logs directory.
     */
    public function getLogsDir();

    /**
     * Implements the log policy based on the current conditions.
     * This method should configure the global logger at Config#log
     *
     * @return void
     */
    public function logs();
}

?>
