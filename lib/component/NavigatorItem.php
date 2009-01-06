<?php

/**
 * Navigator component definition
 *
 * PHP version 5
 *
 * LICENSE: This file is a part of the Red Tree Systems framework,
 * and is licensed royalty free to customers who have purchased
 * services from us. Please see http://www.redtreesystems.com for
 * details.
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
