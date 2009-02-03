<?php

/**
 * Navigator component definition
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
 * @license      http://www.redtreesystems.com PROPRITERY
 * @version      1.0
 * @link         http://www.redtreesystems.com
 */

/**
 * NavigatorItem - a small TreeNode class to manage variable-depth items
 *
 * @category     Components
 */

class NavigatorItem extends TreeNode
{
    /**
     * Specifies the href for this NavigatorItem
     *
     * @access public
     * @var string
     */
    public $href;

    /**
     * Specifies the label for this NavigatorItem
     *
     * @access public
     * @var string
     */
    public $label;

    /**
     * An icon representing this NavigatorItem
     *
     * @var string
     */
    public $icon;

    /**
     * Represents the state of this item
     *
     * @var boolean
     */
    public $isPublished = true;

    /**
     * Determines if this item should show in menus
     *
     * @var boolean
     */
    public $isInMenu = true;

    /**
     * Indicates the order this item should fall in (positional; less is higher)
     *
     * @var int
     */
    public $order;

    /**
     * Specifices if we are the current item or not
     */
    public $isCurrent = false;

    /**
     * Constructor
     *
     * @param string $href
     * @param string $label
     * @return NavigatorItem
     */
    public function __construct($href='', $label='')
    {
        $this->id = uniqid();
        $this->href = $href;
        $this->label = $label;
    }
}

?>
