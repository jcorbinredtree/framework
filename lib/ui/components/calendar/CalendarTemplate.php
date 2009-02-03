<?php

/**
 * Calandar Template class definition
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
 * @category     UI
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * Calendar Template
 *
 * This class is a thin wrapper for Template, just to easily find our templates
 *
 * @category     UI
 * @package        Utils
 */

class CalendarTemplate extends Template
{
    public function __construct()
    {
        parent::__construct();
        $this->addPath('template', dirname(__FILE__));
    }

    function dateCallback($time)
    {
    if ($this->calendar->dateCallback) {
        return call_user_func($this->calendar->dateCallback, $time);
    }

    return '&nbsp;';
    }

    function timeCallback($time)
    {
    if ($this->calendar->timeCallback) {
        return call_user_func($this->calendar->timeCallback, $time);
    }

    return '&nbsp;';
    }

    function formatHour($format, $hour)
    {
        $buf = '';

        for ($i = 0; $i < strlen($format); $i++) {
        switch($format[$i]) {
             case 'a':
                 $buf .= ($hour < 12) ? 'am' : 'pm';
                 break;
             case 'A':
                 $buf .= ($hour < 12) ? 'AM' : 'PM';
                 break;
             case 'g':
                 $buf .= (1 + (($hour >= 12) ? ($hour - 12) : $hour));
                 break;
             case 'G':
                 $buf .= $hour;
                 break;
             case 'g':
                 $buf .= sprintf('%.2f', abs((1 + $hour) - 12));
                 break;
             case 'G':
                 $buf .= sprintf('%.2f', $hour);
                 break;
             default:
                 $buf .= $format[$i];
             }
        }

        return $buf;
    }
}

?>
