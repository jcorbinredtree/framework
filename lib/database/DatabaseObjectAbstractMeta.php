<?php

/**
 * DatabaseObjectAbstractMeta class definition
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

abstract class DatabaseObjectAbstractMeta
{
    protected $class;
    protected $table;

    protected $columnMap; // Holds member <-> column map
    protected $columnDef; // Holds database field definitions
    protected $sqlCache;  // Holds sql strings
    protected $manualColumns;
    protected $customSQL;

    /**
     * meta implementations should define their bulitin queries here, entries in
     * this array cannot be overridden by customSQL
     */
    protected $queries = array();

    public function __construct($class, $members)
    {
        $this->class = $class;
        $this->sqlCache = array();

        $refcls = new ReflectionClass($class);
        $refcls = new ReflectionClass($class);
        $this->customSQL = self::collectStaticArray($refcls, 'CustomSQL');
        $this->manualColumns = self::collectStaticArray($refcls, 'ManualColumns');

        if (! isset($this->table)) {
            throw new RuntimeException(
                "Cannot determine database table for $class"
            );
        }

        // columnMap, just a basic under_scored to camelCased translation
        $this->columnMap = array();

        // columnDef, information on database field details
        $this->columnDef = array();

        foreach ($members as $member) {
            $column = $this->columnName($member);
            if ($this->inspectColumn($column)) {
                $this->columnMap[$member] = $column;
            }
        }
        ksort($this->columnMap);

        if (isset($this->manualColumns)) {
            foreach ($this->manualColumns as $col) {
                $this->inspectColumn($col);
            }
        }
    }

    protected function inspectColumn($column)
    {
        if (array_key_exists($column, $this->columnDef)) {
            return true;
        }
        global $database;
        $def = $database->getTableFieldDefinition($this->table, $column);
        if (! $def) {
            return false;
        } else {
            $this->columnDef[$column] = $def[0];
            ksort($this->columnDef);
            return true;
        }
    }

    public function isManualColumn($column)
    {
        return
            isset($this->manualColumns) &&
            in_array($column, $this->manualColumns);
    }

    protected function columnName($member)
    {
        // TODO it would be nice to not rely on this externally
        // since it's such a simple translation
        return Params::propertyToField($member);
    }

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

    /**
     * @param op string
     * @return string
     */
    public function getSQL($op)
    {
        if (! array_key_exists($op, $this->sqlCache)) {
            $this->sqlCache[$op] = $this->buildSQL($op);
        }
        return $this->sqlCache[$op];
    }

    /**
     * Called by buildSQL to expand SQL statements
     *
     * @param sql string
     * @return string
     */
    protected function expandSQL($sql)
    {
        $sql = str_replace('{table}', $this->table, $sql);
        $sql = str_replace('{colspec}', $this->getColumnsSQL(null, null), $sql);
        $sql = str_replace('{fieldset}', $this->getFieldSetSQL(), $sql);
        return $sql;
    }

    /**
     * @param op string
     * @return string
     */
    public function buildSQL($op)
    {
        if (array_key_exists($op, $this->queries)) {
            return $this->expandSQL($this->queries[$op]);
        }
        if (array_key_exists($op, $this->customSQL)) {
            return $this->expandSQL($this->customSQL[$op]);
        }
        throw new RuntimeException("Invalid sql operation $op for $this->class");
    }

    /**
     * Returns a list of all non-manual columns
     *
     * @return array
     */
    public function getAutomaticColumns() {
        $cols = array_values($this->columnMap);
        $man = array_filter($cols, array($this, 'isManualColumn'));
        return array_diff($cols, $man);
    }

    /**
     * Builds a list of colums for this class's fields, usable in a select statement.
     *
     * @param cols which columns to process, if not set all automatic members
     *   will be used
     * @param prefix string prefix to prepend to the column names, or null to return
     *   bare column names. Optional, defaults to the table name as returned by
     *   getTable())
     * @param glue mixed if set to null, than an array of columns is returned,
     *   otherwise glue is used to implode the array and a string is returned.
     *   The default is to implode on ', '.
     *
     * @return mixed see glue parameter
     */
    public function getColumnsSQL($cols=null, $prefix='', $glue=', ')
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

        if (! isset($cols)) {
            $cols = $this->getAutomaticColumns();
        }

        $sql = array();
        foreach ($cols as $column) {
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
    public function getFieldSetSQL($cols=null, $bindByName=true, $glue=', ')
    {
        if (! isset($cols)) {
            $cols = $this->getAutomaticColumns();
        }

        $set = array();
        foreach ($cols as $column) {
            if ($this->isManualColumn($column)) {
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
     * utility for constructor
     */
    static protected function collectStaticArray(ReflectionClass $class, $name) {
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
