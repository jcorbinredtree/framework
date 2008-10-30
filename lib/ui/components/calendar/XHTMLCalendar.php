<?php

/**
 * XHTMLCalendar class definition
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
 * XHTML Calendar
 *
 * This represents a calendar, as XHTML
 *
 * @category     UI
 * @package        Utils
 */
class XHTMLCalendar
{
    const MONTH_VIEW = 0x01;
    const WEEK_VIEW = 0x02;
    const DAY_VIEW = 0x03;
    
    public $date;
    public $view = XHTMLCalendar::MONTH_VIEW;
    
    public $dateCallback = null;
    public $timeCallback = null;
    
    /*
     * month
     */
    public $monthCellOut = null;
    public $monthCellOver = null;
    public $monthCellClicked = null;
    public $printMonthHeaders = true;
    public $monthHeaderFormat = 'l';
    
    /*
     * week
     */
    public $weekHeaderFormat = 'D, M j';
    public $weekHourFormat = 'g A';
    
    /*
     * day
     */
    public $dayHeaderFormat = 'D, M j';
    public $dayHourFormat = 'g A';
    
    public function __construct()
    {
        $this->date = time();
    }
    
    public function draw()
    {
        $template = new CalendarTemplate();
        $template->assign('calendar', $this);
        
        switch($this->view) {
            case XHTMLCalendar::MONTH_VIEW:
                $template->display('view/Month.tpl.php');
                break;
            case XHTMLCalendar::WEEK_VIEW:
                $template->display('view/Week.tpl.php');
                break;
            case XHTMLCalendar::DAY_VIEW:
                $template->display('view/Day.tpl.php');
                break;
            default:
                print 'unknown view';
                break;
        }
    }
}

?>
