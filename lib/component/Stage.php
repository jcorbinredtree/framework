<?php

/**
 * Stage definition
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
 * @category     Components
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Basically an enum for Stage
 *
 * @category     Components
 */
class Stage
{
    /**
     * private constructor
     *
     */
    private function __construct()
    {

    }

    /**
     * The view stage
     *
     * @var int
     */
    const VIEW = 1;

    /**
     * The validate stage
     *
     * @var int
     */
    const VALIDATE = 2;

    /**
     * The perform stage
     *
     * @var int
     */
    const PERFORM = 3;
}

?>
