<?php

/**
 * DatabaseObjectMeta class definition
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

require_once 'lib/database/DatabaseObjectAbstractMeta.php';

class DatabaseObjectMeta extends DatabaseObjectAbstractMeta
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
    static public function forClass($class)
    {
        if (! array_key_exists($class, self::$ClassMeta)) {
            assert(class_exists($class));
            assert(is_subclass_of($class, 'DatabaseObject'));
            self::$ClassMeta[$class] = new self($class);
        }
        return self::$ClassMeta[$class];
    }

    protected $key=null;

    protected $queries = array(
        'dbo_select' =>
            'SELECT {colspec} FROM {table} WHERE {key}=? LIMIT 1',
        'dbo_insert' =>
            'INSERT INTO {table} SET {fieldset}',
        'dbo_update' =>
            'UPDATE {table} SET {fieldset} WHERE {key}={keybind} LIMIT 1',
        'dbo_delete' =>
            'DELETE FROM {table} WHERE {key}=?'
    );

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
        $members = array();
        global $config;
        $refcls = new ReflectionClass($class);
        foreach ($refcls->getProperties() as $prop) {
            $name = $prop->getName();
            switch ($name) {
            case 'table':
            case 'key':
                if (! $prop->isStatic() && $config->isDebugMode()) {
                    trigger_error("$class->$name should be $class::\$$name");
                }
                $this->$name = $prop->getValue();
                break;
            default:
                if (! $prop->isStatic()) {
                    array_push($members, $name);
                }
                break;
            }
        }

        if (! isset($this->key)) {
            throw new RuntimeException(
                "Cannot determine database key for $class"
            );
        }

        parent::__construct($class, $members);
    }

    protected function columnName($member)
    {
        if ($member == 'id') {
            return $this->key;
        } else {
            return parent::columnName($member);
        }
    }

    /**
     * @return string the name of the primary key column in this table
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Expands a SQL string
     *
     * Clauses defined here:
     *   {key}      - the $key property, sql quoted
     *   {keybind}  - a string like ":table_id" for use as a named placeholder
     *
     * @param sql string
     * @return string
     * @see $key, DatabaseObjectAbstractMeta::expandSQL
     */
    protected function expandSQL($sql)
    {
        $sql = parent::expandSQL($sql);
        $sql = str_replace('{key}', "`$this->key`", $sql);
        $sql = str_replace('{keybind}', ":$this->key", $sql);
        return $sql;
    }

    public function isManualColumn($column)
    {
        if ($column == $this->key) {
            return true;
        }
        return parent::isManualColumn($column);
    }
}

?>
