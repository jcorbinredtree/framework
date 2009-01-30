<?php

/**
 * Database Object Meta class definition
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
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * @see DatabaseObject::meta
 */

class DatabaseObject_Meta
{
    /**
     * Static storage for meta objects
     */
    static private $ClassMeta = array();

    /**
     * Static singlton manager
     *
     * Returns the meta object for a given DatabaseObjcet subclass
     *
     * @param class string the class
     *
     * @return DatabaseObject_meta
     */
    static public function &forClass($class)
    {
        if (! array_key_exists($class, self::$ClassMeta)) {
            if (! is_subclass_of($class, 'DatabaseObject')) {
                throw new InvalidArgumentException(
                    "$class is not a DatabaseObject subclass"
                );
            }

            self::$ClassMeta[$class] =& new self($class);
        }

        return self::$ClassMeta[$class];
    }

    private $class;
    private $table;
    private $key;

    /**
     * @return string the name of the class represented by this meta object.
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string the sql table name
     */
    public function getTable()
    {
        return $this->table;
    }
    /**
     * @return string the name of the primary key column in this table
     */
    public function getKey()
    {
        return $this->key;
    }

    private $columnMap; // Holds member <-> column map
    private $columnDef; // Holds database field definitions

    /**
     * Constructor
     *
     * This shouldn't be called directly
     *
     * @see DatabaseObject::meta
     *
     * @param class string
     */
    function __construct($class)
    {
        global $database;

        $this->class = $class;

        // Introspect class's members
        $vars = get_class_vars($class);

        $this->table = $vars['table'];
        $this->key = $vars['key'];

        // columnMap, just a basic under_scored to camelCased translation
        $this->columnMap = array();

        // columnDef, informatino on database field details
        $this->columnDef = array();

        $members = array_keys($vars);
        foreach ($members as $member) {
            if ($member == 'id') {
                $column = $this->key;
            } else {
                // TODO it would be nice to not rely on this externally
                // since it's such a simple translation
                $column = Params::propertyToField($member);
            }

            $def = $database->getTableFieldDefinition($this->table, $column);
            if (! $def) {
                continue;
            } else {
                $this->columnDef[$column] = $def[0];
            }

            $this->columnMap[$member] = $column;
        }

        ksort($this->columnDef);
        ksort($this->columnMap);
    }

    /**
     * Returns an associative array mapping member namse to database columns
     *
     * @return array
     */
    public function getColumnMap()
    {
        return $this->columnMap;
    }

    /**
     * Returns the database definition of the column
     *
     * @param column string
     *
     * @return mixed
     */
    public function getColumnDefinition($column)
    {
        if (! array_key_exists($column, $this->columnDef)) {
            throw new Exception("$this->class: No such column '$column' in '$this->table'");
        }
        return $this->columnDef[$column];
    }

    /**
     * Tests whether the given member corresponds to a database column
     *
     * @param member string
     *
     * @return boolean
     */
    public function isColumn($member)
    {
        if (array_key_exists($column, $this->columnDef)) {
            return true;
        } else {
            return false;
        }
    }
}

?>
