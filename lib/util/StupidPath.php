<?php

/**
 * StupidPath class definition
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
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.1
 * @link         http://framework.redtreesystems.com
 */

/**
 * Implements a stupid path
 *
 * This is essentially an array that is string equivalent at all times:
 *
 *   $p = new StupidPath('/a/b/c');
 *   echo $p; // /a/b/c
 *   echo json_encode($p->components); // ['a','b','c']
 *   echo $p->up(); // /a/b
 *   echo $p->up(2); // /a
 *   echo $p->up()->down('d', 'file.css'); // /a/b/d/file.css
 */
class StupidPath
{
    public $components;

    /**
     * Constructs a new stupid path
     *
     * @param a string or array
     */
    public function __construct($a)
    {
        if (is_string($a)) {
            $a = explode('/', $a);
        }
        $this->components = $a;
    }

    /**
     * Returns string path
     */
    public function __tostring()
    {
        return implode('/', $this->components);
    }

    /**
     * Creates a new StupidPath based on the first count($componets)-$n
     * elemnets and returns it.
     *
     * @param n int optinonal defaults to 1
     * @return StupidPath
     */
    public function up($n=1)
    {
        return new StupidPath(array_slice(
            $this->components, 0, count($this->components)-$n
        ));
    }

    /**
     * Creates a new StupidPath with all arguments appended to $components and
     * returns it.
     *
     * @param <args> strings
     *   If any string contains a '/' it will be exploded and merged as if
     *   each elemnt had been given individually.
     *
     * @return StupidPath
     */
    public function down()
    {
        $add = func_get_args();
        if (is_array($add[0])) {
            $add = $add[0];
        }

        $a = array();
        foreach ($add as $c) {
            $a = array_merge($a, explode('/', $c));
        }
        return new StupidPath(array_merge(
            $this->components, $a
        ));
    }
}

?>
