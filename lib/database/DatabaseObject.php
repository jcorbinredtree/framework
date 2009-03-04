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

// TODO part ways with this
require_once 'lib/component/RequestObject.php';

require_once 'lib/database/DatabaseObjectMeta.php';

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
 * just override the methods of DatabaseObject as appropriate.
 *
 * @category     Database
 * @package      Core
 */
abstract class DatabaseObject extends RequestObject
{
    static function load($class, $id)
    {
        if (! is_int($id)) {
            if (is_numeric($id)) {
                $id = (int) $id;
            }
            if (! is_int($id) || $id == 0) {
                throw new InvalidArgumentException('invalid id');
            }
        }
        if (
            ! class_exists($class) ||
            ! is_subclass_of($class, 'DatabaseObject')
        ) {
            throw new InvalidArgumentException("invalid class $class");
        }

        $refcls = new ReflectionClass($class);
        try {
            $factory = $refcls->getMethod('factory');
            if ($factory->isStatic()) {
                $o = call_user_func(array($class, 'factory'), $id);
            } else {
                $factory = null;
            }
        } catch (ReflectionException $e) {
        }
        if (! isset($factory)) {
            // TODO relpace fetch with a sane loading system such that there is ever
            // only one instance per id
            $o = new $class();
            $o->fetch($id);
        }
        return $o;
    }

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
     * A simple property to track memoization
     *
     * @var array
     */
    protected $memo = array();

    public function __construct()
    {
    }

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
     * @return void
     */
    public function create()
    {
        global $database, $config;

        $meta = $this->meta();
        $table = $meta->getTable();
        $key = $meta->getKey();

        $database->lock($table, Database::LOCK_WRITE);
        try {
            $sql = $meta->getSQL('dbo_insert');
            $values = $this->getFieldSetValues();
            $database->prepare($sql);
            $database->execute($values);
            $p = Params::fieldToProperty($key);
            if (property_exists($this, $p) && $this->$p) {
                $this->id = $this->$p;
            } else {
                $this->id = $database->lastInsertId();
            }
            $database->free();
        } catch (Exception $e) {
            $database->unlock();
            throw $e;
        }
        $database->unlock();
    }

    /**
     * Fetches the given $id into the current object. IT'S NOT A STATIC METHOD.
     *
     * @param mixed $id
     * @return boolean
     */
    public function fetch($id)
    {
        $meta = $this->meta();
        global $database;
        $sql = $meta->getSQL('dbo_select');
        $database->executef($sql, $id);
        if (! $database->count()) {
            $database->free();
            return false;
        }
        $row = $database->getRow();
        $this->unserialize($row, false);
        $this->id = $id;
        return true;
    }

    /**
     * Updates the current object in the database, based on the properties set
     *
     * @return void
     */
    public function update()
    {
        global $database;
        $meta = $this->meta();
        $table = $meta->getTable();
        $key = $meta->getKey();
        $sql = $meta->getSQL('dbo_update');
        $values = $this->getFieldSetValues();
        $values[":$key"] = $this->id;
        $database->prepare($sql);
        $database->execute($values);
        $database->free();
    }

    /**
     * Removes the current $this->id from the database
     *
     * @return void
     */
    public function delete()
    {
        $meta = $this->meta();
        global $database;
        $sql = $meta->getSQL('dbo_delete');
        $database->executef($sql, $this->id);
        $this->id = -1;
        $database->free();
    }

    /**
     * Returns the meta object for this DatabaseObject's class
     *
     * @see DatabaseObjectMeta
     *
     * @return object DatabaseObjectMeta
     */
    public function meta()
    {
        return DatabaseObjectMeta::forClass(get_class($this));
    }

    /*
     * 5.3.0 someday, the magic of late static binding:
     *
     * public static function staticMeta()
     * {
     *     return DatabaseObjectMeta::forClass(get_called_class());
     * }
     */

    /**
     * Set to true while serialize calls selfToData.
     *
     * @var boolean;
     */
    protected $serializing = false;

    /**
     * Set to true while unserialize calls dataToSelf.
     *
     * @var boolean;
     */
    protected $unserializing = false;

    /**
     * Serializes this DatabaseObject to a named array.
     *
     * @return array
     */
    final public function serialize()
    {
        $this->serializing = true;
        $data = $this->selfToData();
        $this->serializing = false;
        assert(is_array($data));
        return $data;
    }

    /**
     * Does the actual work for serialize, subclasses should override this to
     * save their data
     *
     * @return array
     */
    protected function selfToData()
    {
        $meta = $this->meta();
        $data = array();
        foreach ($meta->getColumnMap() as $property => $column) {
            if ($meta->isManualColumn($column)) {
                continue;
            }
            $data[$column] = $this->$property;
        }
        return $data;
    }

    /**
     * Unserializes this DatabaseObject from a named array
     *
     * @param data array
     * @param save boolean whether the newly restored self should be saved to
     * the database
     * @return void
     * @see dataToSelf
     */
    final public function unserialize($data, $save=true)
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('not an array');
        }
        $this->unserializing = true;
        $this->dataToSelf($data, $save);
        $this->unserializing = false;
    }

    /**
     * Does the actual work for unserialize, subclasses should override this to
     * restore their data
     *
     * @param data array
     * @param save boolean
     * @return void
     * @see unserialize
     */
    protected function dataToSelf($data, $save)
    {
        assert(is_array($data));
        $meta = $this->meta();
        foreach ($meta->getColumnMap() as $property => $column) {
            if ($meta->isManualColumn($column)) {
                continue;
            }
            if (array_key_exists($column, $data)) {
                $this->$property = $data[$column];
            } else {
                $this->$property = null;
            }
        }

        if ($save) {
            if (isset($this->id)) {
                $this->update();
            } else {
                $this->create();
            }
        }
    }

    /**
     * Returns a value list for executing a prepared sql statement containing
     * the fragment returned by meta()->getFieldSetSQL.
     *
     * @param byName boolean whether to return an associative array suitable for use
     * with a sql fragment with named parameters, true by default.
     *
     * @return array value list
     */
    public function getFieldSetValues($byName=true)
    {
        $meta = $this->meta();
        $key = $meta->getKey();

        $values = array();
        foreach ($meta->getColumnMap() as $property => $column) {
            if ($meta->isManualColumn($column)) {
                continue;
            }
            if ($byName) {
                $values[":$column"] =& $this->$property;
            } else {
                array_push($values, &$this->$property);
            }
        }

        return $values;
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
