<?php

/**
 * Database Object class definition
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
 * Simple ORM object base class
 *
 * This class should be extended by any class wishing to be an ORM object.
 * Note that this class is a RequestObject also, so you can perform any of
 * those methods as well.
 *
 * Usage:
 * 1.) Extend this class
 * 2.) Add public properties to your class definition that map to your
 * field names, replacing _[a-z] with [A-Z]. Thus, user_name in your db
 * becomes public $userName, this_other_field becomes public $thisOtherField,
 * and so on. It's likely that you have public properties that don't map to
 * field names, and that's not a problem.
 * 3.) Define public static $table to the appropriate table
 * 4.) Define public static $key to the name of your primary key field
 *
 * Relationships are generally up to you, but it's often enough to
 * just override the methods of IDatabaseObject as appropriate.
 *
 * @category     Database
 * @package      Core
 */
abstract class DatabaseObject extends RequestObject implements IDatabaseObject
{
    /**
     * This field will serve as the primary key's id.
     *
     * @var int
     */
    public $id = -1;

    /**
     * The subject table
     *
     * @var string
     */
    static public $table = null;

    /**
     * The name of our primary key field
     *
     * @var string
     */
    static public $key = null;

    /**
     * Statement type constantes used by DatabaseObject_Meta::getSQL
     */
    const SQL_SELECT=0;
    const SQL_INSERT=1;
    const SQL_UPDATE=2;
    const SQL_DELETE=3;

    /**
     * A simple property to track memoization
     *
     * @var array
     */
    protected $memo = array();

    /**
     * Determines if this key exists in the memoization
     *
     * @param string $key
     * @return boolean
     */
    protected function hasMemo($key)
    {
        return array_key_exists($key, $this->memo);
    }

    /**
     * Gets a memoization
     *
     * @param string $key
     * @return mixed
     */
    protected function getMemo($key)
    {
        if (array_key_exists($key, $this->memo)) {
            return $this->memo[$key];
        }

        return null;
    }

    /**
     * Sets a simple memoization
     *
     * @param string $key
     * @param mixed $data
     */
    protected function setMemo($key, &$data)
    {
        $this->memo[$key] = $data;
    }

    /**
     * Called to create the object
     *
     * @return boolean
     */
    public function create()
    {
        global $database, $config;

        $meta = $this->meta();
        $table = $meta->getTable();
        $key = $meta->getKey();

        $database->lock($table, Database::LOCK_WRITE);
        {
            $meta = $this->meta();
            $sql = $meta->getSQL(DatabaseObject::SQL_INSERT);

            if (!$database->prepare($sql)) {
                $config->error("could not prepare db object");
                $database->unlock();
                return false;
            }

            $values = $meta->getFieldSetValues($this);

            if (!$database->execute($values)) {
                $config->error("could not execute insert on db object");
                $database->unlock();
                return false;
            }

            $p = Params::fieldToProperty($key);
            if (property_exists($this, $p) && $this->$p) {
                $this->id = $this->$p;
            }
            else {
                $this->id = $database->lastInsertId();
            }
        }
        $database->unlock();

        return true;
    }

    /**
     * Fetches the given $id into the current object. IT'S NOT A STATIC METHOD.
     *
     * @param mixed $id
     * @return boolean
     */
    public function fetch($id)
    {
        global $database;

        $sql = $this->meta()->getSQL(DatabaseObject::SQL_SELECT);
        if ($database->executef($sql, $id) && $database->count()) {
            $row = $database->getRow();
            Params::ArrayToObject($row, $this);
            $this->id = $id;

            return true;
        }

        return false;
    }

    /**
     * Updates the current object in the database, based on the properties set
     *
     * @return boolean
     */
    public function update()
    {
        global $database;

        $meta = $this->meta();
        $table = $meta->getTable();
        $key = $meta->getKey();

        $sql = $meta->getSQL(DatabaseObject::SQL_UPDATE);

        if (!$database->prepare($sql)) {
            return false;
        }

        $values = $meta->getFieldSetValues($this);
        $values[":$key"] = $this->id;

        if (! $database->execute($values)) {
            return false;
        }

        if ($database->count() != 1) {
            $this->errorLog(
                "update($table.$key = $this->id)",
                'no rows updated, likely no such key'
            );
            return false;
        }

        return true;
    }

    /**
     * Removes the current $this->id from the database
     *
     * @return boolean
     */
    public function delete()
    {
        global $database;

        $sql = $this->meta()->getSQL(DatabaseObject::SQL_DELETE);

        if ($database->executef($sql, $this->id)) {
            $this->id = -1;
            return true;
        }

        return false;
    }

    /**
     * DEPRECATED
     */
    public function getColumnsSQL($prefix='')
    {
        global $config;
        $config->deprecatedComplain(
            'DatabaseObject->getColumnsSQL()',
            'DatabaseObject->meta()->getColumnsSQL()'
        );
        return $this->meta()->getColumnsSQL($prefix='');
    }

    /**
     * DEPRECATED
     */
    public function getFields()
    {
        global $config;
        $config->deprecatedComplain(
            'DatabaseObject->getFields',
            'DatabaseObject->meta()->getColumnMap()'
        );
        return $this->meta()->getColumnMap();
    }

    /**
     * Returns the meta object for this DatabaseObject's class
     *
     * @see DatabaseObject_Meta
     *
     * @return object DatabaseObject_Meta
     */
    public function &meta()
    {
        $meta = DatabaseObject_Meta::forClass(get_class($this));
        return $meta;
    }

    /**
     * Logs an error message through Config.
     *
     * @param what string what the caller did that went badly
     * @param why string why it didn't work out (optional)
     */
    protected function errorLog($what, $why=null)
    {
        global $config;

        $class = get_class($this);

        $config->error("$class::$what failed: $why");
    }
}

?>
