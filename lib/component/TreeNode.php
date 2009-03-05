<?php

/**
 * TreeNode class definition
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
 * @category     Utils
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * An abstract class to handle tree-like structures
 *
 * @package      Utils
 */

abstract class TreeNode
{
    /**
     * Holds the id of the current node
     *
     * @access public
     * @var int
     */
    public $id;

    /**
     * Holds the id of the parent TreeNode
     *
     * @access public
     * @var int
     */
    public $parentId;

    /**
     * Holds a link to the parent TreeNode
     *
     * @access public
     * @var TreeNode
     */
    public $parent;

    /**
     * Holds links to the children TreeNode
     *
     * @access public
     * @var array
     */
    public $children = array();

    /**
     * Returns the top-level parent from this item
     *
     * @return TreeNode the parent who has no parent
     */
    public function getTopLevelParent()
    {
        $parent = $this;
        while ($parent->parent) {
            $parent = $parent->parent;
        }

        return $parent;
    }

    /**
     * Adds a child TreeNode to the current node.
     * Note that this sets the parent of the item
     * as well.
     *
     * @access public
     * @param TreeNode $item
     * @return TreeNode the treenode passed in
     */
    public function addChild(TreeNode &$item)
    {
        $item->setParent($this);
        array_push($this->children, $item);

        return $item;
    }

    /**
     * Sets the current node's parent to the item.
     * Note that we are *NOT* added as a child.
     *
     * @access public
     * @param TreeNode $item
     * @return TreeNode the treenode passed in
     */
    public function setParent(TreeNode &$item)
    {
        return $this->parent = $item;
    }

    /**
     * Removes the node from the tree
     *
     * @param mixed $mixed The $id of a node, or the node object.
     * The default value is null, which means remove yourself from
     * your parent.
     * @return void
     */
    public function prune($mixed=null)
    {
        $id = ($mixed
                            ? (is_numeric($mixed) ? $mixed : $mixed->id)
                            : null);

        if ((null == $mixed) || ($this->id == $id)) {
            if ($this->parent) {
                $children = array();
                foreach ($this->parent->children as $child) {
                    if ($child->id != $this->id) {
                        array_push($children, $child);
                    }
                }

                $this->parent->children = $children;
            }

            return;
        }

        $children = array();
        foreach ($this->children as $child) {
            if ($child->id != $id) {
                array_push($children, $child);
            }
        }

        $this->children = $children;
    }

    /**
     * Determines whether the current node
     * contains the specified $id as a descendant.
     *
     * @param int $id The id to check for
     * @return boolean true if we have a descendant
     */
    public function hasDescendant($id)
    {
        if ($this->id == $id) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child->hasDescendant($id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if the current node has children
     *
     * @return int the number of children this node
     * contains
     */
    public function hasChildren()
    {
        return count($this->children);
    }

    /**
     * Finds a particular TreeNode
     *
     * @param int $id the id to find
     * @param array $list the list of elements to search
     * @return Treenode upon success; null on failure
     */
    public static function find($id, array &$list)
    {
        foreach ($list as $item) {
            if ($item->id == $id) {
                return $item;
            }

            if ($item->hasChildren()) {
                if ($current = TreeNode::find($id, $item->children)) {
                    return $current;
                }
            }
        }

        return null;
    }

    /**
     * Adds several TreeNodes at once to the current node.
     * Note that this method sets the appropriate parent and
     * children of the array.
     *
     * @param array $members An array of TreeNodes to add
     * @return void
     */
    public function addMembers(&$members)
    {
        // mmmmmmmmm, inefficent!
        for ($i = 0; $i < count($members); $i++) {
            for ($x = $i; $x < count($members); $x++) {
                if (! $members[$x] instanceof TreeNode) {
                    throw new InvalidArgumentException(
                        "member $x is not a TreeNode!"
                    );
                }

                if (! $members[$i] instanceof TreeNode) {
                    throw new InvalidArgumentException(
                        "member $i is not a TreeNode!"
                    );
                }

                if ($members[$x]->parentId == $members[$i]->id) {
                    $members[$i]->addChild($members[$x]);
                }

                if ($members[$x]->id == $members[$i]->parentId) {
                    $members[$x]->addChild($members[$i]);
                }
            }

            if (!$members[$i]->parentId) {
                array_push($this->children, $members[$i]);
            }
        }
    }
}

?>
