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

require_once 'lib/database/IDatabaseObject_Meta.php';

class DatabaseObject_Meta implements IDatabaseObject_Meta
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

    private $class=null;
    private $table=null;
    private $key=null;

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

    // Holds sql strings
    private $sqlCache;
    private $customSqlCache;

    // Static definiton collections
    private $manualColumns;
    private $customSQL;

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
        global $config, $database;

        $this->class = $class;
        $this->sqlCache = array();
        $this->customSqlCache = array();

        $refcls = new ReflectionClass($class);

        $members = array();
        // Reflect on the state of things
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
            case 'ManualColumns':
            case 'CustomSQL':
                $m = strtolower($name[0]).substr($name, 1);
                $this->$m = self::collectStaticArray($refcls, $name);
                break;
            default:
                if (! $prop->isStatic()) {
                    array_push($members, $name);
                }
                break;
            }
        }

        if (! isset($this->table)) {
            throw new RuntimeException(
                "Cannot determine database table for $class"
            );
        }
        if (! isset($this->key)) {
            throw new RuntimeException(
                "Cannot determine database key for $class"
            );
        }

        // columnMap, just a basic under_scored to camelCased translation
        $this->columnMap = array();

        // columnDef, informatino on database field details
        $this->columnDef = array();

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
     * Returns an associative array mapping member names to database columns
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

    public function isManualColumn($column)
    {
        return
            isset($this->manualColumns) &&
            in_array($column, $this->manualColumns);
    }

    /**
     * Builds SQL statements
     *
     * @param op int the operation constant indicating what type of statement to build, one of:
     *   DatabaseObject::SQL_SELECT
     *   DatabaseObject::SQL_INSERT
     *   DatabaseObject::SQL_UPDATE
     *   DatabaseObject::SQL_DELETE
     *
     * @return string the SQL string
     */
    public function getSQL($op)
    {
        switch ($op) {
        case DatabaseObject::SQL_SELECT:
        case DatabaseObject::SQL_INSERT:
        case DatabaseObject::SQL_UPDATE:
        case DatabaseObject::SQL_DELETE:
            if (! array_key_exists($op, $this->sqlCache)) {
                $this->sqlCache[$op] = $this->buildSQL($op);
            }
            return $this->sqlCache[$op];
        default:
            throw new InvalidArgumentException("Invalid SQL op $op");
        }
    }

    /**
     * Private utility method called by getSQL to build a statment the first time around.
     *
     * @see getSQL
     *
     * @param op int as in getSQL
     * @return string
     */
    private function buildSQL($op)
    {
        $table = $this->getTable();
        $key = $this->getKey();

        switch ($op) {
        case DatabaseObject::SQL_SELECT:
            return
                "SELECT ".$this->getColumnsSQL(null) .
                " FROM `$table` WHERE `$key` = ? LIMIT 1";
        case DatabaseObject::SQL_INSERT:
            return "INSERT INTO `$table` SET ".$this->getFieldSetSQL();
        case DatabaseObject::SQL_UPDATE:
            return
                "UPDATE `$table` SET ".$this->getFieldSetSQL().
                " WHERE `$key` = :$key LIMIT 1";
        case DatabaseObject::SQL_DELETE:
            return "DELETE FROM `$table` WHERE `$key` = ?";
        }
    }

    /**
     * Builds a list of colums for this class's fields, usable in a select statement.
     *
     * @param prefix string prefix to prepend to the column names, or null to return
     *   bare column names. Optional, defaults to the table name as returned by
     *   getTable())
     * @param glue mixed if set to null, than an array of columns is returned,
     *   otherwise glue is used to implode the array and a string is returned.
     *   The default is to implode on ', '.
     *
     * @return mixed see glue parameter
     */
    public function getColumnsSQL($prefix='', $glue=', ')
    {
        if ( $prefix === null) {
            $prefix = '';
        } else {
            if ($prefix == '') {
                $prefix = $this->table;
            } else {
                # Why is this here... why shouldn't a consumer be able
                # to specify "Database.Table" as a prefix?
                $prefix = str_replace('.', '', $prefix);
            }
            $prefix = "`$prefix`.";
        }

        $sql = array();
        $key = $this->getKey();
        $fields = $this->getColumnMap();

        foreach ($fields as $property => $column) {
            if ($column == $key) {
                continue;
            }
            if (
                isset($this->manualColumns) &&
                in_array($column, $this->manualColumns)
            ) {
                continue;
            }

            $def = $this->getColumnDefinition($column);
            switch (strtolower(Params::generic($def, 'native_type'))) {
                // FIXME how about a timezone?
                case 'time':
                    array_push($sql, "TIME_TO_SEC($prefix`$column`) AS `$column`");
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    array_push($sql, "UNIX_TIMESTAMP($prefix`$column`) AS `$column`");
                    break;
                default:
                    array_push($sql, "$prefix`$column` AS `$column`");
            }
        }

        if ($glue) {
            return implode($glue, $sql);
        } else {
            return $sql;
        }
    }

    /**
     * Builds the needed SQL fragment to update or insert fields.
     *
     * @param bindByName boolean whether to generate named bind parameters, true by
     * default.
     * @param glue mixed if set to null, than an array of columns is returned,
     * otherwise glue is used to implode the array and a string is returned.
     * The default is to implode on ', '.
     *
     * @return string like "col1=:?, col2=:?" or "col1=:col1, col2=:col2"
     */
    public function getFieldSetSQL($bindByName=true, $glue=', ')
    {
        $key = $this->getKey();
        $fields = $this->getColumnMap();
        $set = array();

        foreach ($fields as $property => $column) {
            if ($property == 'id' || $column == $key) {
                continue;
            }
            if (
                isset($this->manualColumns) &&
                in_array($column, $this->manualColumns)
            ) {
                continue;
            }

            if ($bindByName) {
                $value = ":$column";
            } else {
                $value = '?';
            }

            $def = $this->getColumnDefinition($column);
            switch (strtolower(Params::generic($def, 'native_type'))) {
                case 'time':
                    $value = "SEC_TO_TIME($value)";
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $value = "FROM_UNIXTIME($value)";
                    break;
            }

            array_push($set, "`$column`=$value");
        }

        if ($glue) {
            return implode($glue, $set);
        } else {
            return $set;
        }
    }

    /**
     * Stores a custom sql statement
     *
     * Subclasses should use this when they build a custom database query, example:
     *   class SomeClass extends DatabaseObject {
     *     ...
     *     const MY_CUSTOM_CODE=<unique integer>;
     *     public function doSomething() {
     *       $meta = $this->meta();
     *       $sql = $meta->getCustomSQL(MY_CUSTOM_CODE);
     *       if ($sql === false) {
     *         $sql = buildSQLSomehowTheFirstTime();
     *         $meta->setCustomSQL(MY_CUSTOM_CODE, $sql);
     *       }
     *       $database->doSomethingWith($sql);
     *       ...
     *     }
     *     ...
     *   };
     *
     * @see DatabaseObject_Meta::getCustomSQL
     * @param code mixed a key for customSqlCache
     * @param sql string the statement
     *
     * @return string $sql for convenience
     */
    public function setCustomSQL($code, $sql)
    {
        $sql = str_replace('{table}', $this->table, $sql);
        $sql = str_replace('{key}', "`$this->key`", $sql);
        return $this->sqlCache[$code] = $sql;
    }

    /**
     * Retrieves a cusotm sql statement stored by setCustomSQL
     *
     * @see setCustomSQL
     * @param code mixed a key for customSqlCache
     * @return mixed if the code exists, the stored string is returned, otherwise
     *   the false value.
     */
    public function getCustomSQL($code)
    {
        if (! array_key_exists($code, $this->customSqlCache)) {
            // Subclass statically defines sql strings
            if (
                isset($this->customSQL) &&
                array_key_exists($code, $this->customSQL)
            ) {
                return $this->setCustomSQL($code, $this->customSQL[$code]);
            }
            return false;
        } else {
            return $this->customSqlCache[$code];
        }
    }

    /**
     * Priavte utility, go away
     */
    static private function collectStaticArray(ReflectionClass $class, $name) {
        $a=array();
        while ($class) {
            if ($class->hasProperty($name)) {
                $prop = $class->getProperty($name);
                if (! $prop->isStatic()) {
                    throw new RuntimeException(
                        "$class->name::\$$name isn't static"
                    );
                }
                $v = $prop->getValue();
                if (! is_array($v)) {
                    throw new RuntimeException(
                        "$class->name::\$$name isn't an array"
                    );
                }
                $a = array_unique(array_merge($a, $v));
            }
            $class = $class->getParentClass();
        }
        return $a;
    }
}

?>
