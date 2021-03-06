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

require_once dirname(__FILE__).'/DatabaseObjectAbstract.php';
require_once dirname(__FILE__).'/DatabaseObjectMeta.php';

/**
 * Simple ORM object base class
 *
 * This class should be extended by any class wishing to be an ORM object.
 * Note that this class is a RequestObject also, so you can perform any of
 * those methods as well.
 *
 * Usage:
 * 1) Extend this class
 * 2) Add properties to your class definition that map to your column names;
 *    property namse should be camelCased and column names should be
 *    under_scored. Properties beginning with a '_' will be ignored. Properties
 *    that do not correspnod to a column at runtime will be ignored.
 * 3) Define various static fields to control things
 *
 * Example:
 *   class MyDBO extends DatabaseObject
 *   {
 *     // Convenience call to DatabaseObject::load until php supports late static
 *     // binding in 5.3.0
 *     public static function load($id)
 *     {
 *         return DatabaseObject::load(__CLASS__, $id);
 *     }
 *
 *     public static $table = 'my_table';  // required
 *     public static $key = 'my_table_id'; // required, "table_id" is customary
 *
 *     // All are optional
 *     public static $CustomSQL = array(
 *       'name' => 'SELECT bla FROM {table} WHERE bla'
 *       // see DatabaseObjectMeta::expandSQL for details on expansion strings
 *       // like {table}
 *     );
 *     public static $ManualColumns = array(
 *       // declares that this subclass will handle thees columns, they
 *       // shouldn't be touched by the default create/update/fetch logic
 *       'some_col'
 *     );
 *
 *     public $myProp;         // will be column my_prop
 *     protected $anotherProp; // doesn't have to be public
 *   }
 *
 * Relationships are generally up to you, but it's often enough to
 * just override the methods of DatabaseObject as appropriate.
 *
 * @category     Database
 * @package      Core
 */
abstract class DatabaseObject extends DatabaseObjectAbstract
{
    static protected $ObjectCache = array();

    static protected function cacheKey(DatabaseObjectMeta $meta, $id)
    {
        $database = $meta->getDatabase();
        return $database->getDSN().'/'.$meta->getTable()."/$id";
    }

    /**
     * Loads an existing DatabaseObject, caches the result so that there is ever
     * only one instance
     *
     * @param class string the class to load
     * @param id int the object id
     * @return DatabaseObject
     */
    static public function load($class, $id, $db=null)
    {
        assert(class_exists($class));
        assert(is_subclass_of($class, __CLASS__));

        if (! is_int($id)) {
            if (is_numeric($id)) {
                $id = (int) $id;
            }
            if (! is_int($id) || $id == 0) {
                throw new InvalidArgumentException('invalid id');
            }
        }

        $cacheKey = self::cacheKey(DatabaseObjectMeta::forClass($class, $db), $id);
        if (! array_key_exists($cacheKey, self::$ObjectCache)) {
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
                $o = new $class();
            }
            $o->fetch($id);
            self::$ObjectCache[$cacheKey] = $o;
        }
        return self::$ObjectCache[$cacheKey];
    }

    /**
     * Key into $ObjectCache
     */
    private $_cacheKey;

    /**
     * This field will serve as the primary key's id.
     *
     * @var int
     */
    public $id = null;

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
        if (isset($this->id)) {
            throw new RuntimeException('already created');
        }
        assert(! isset($this->_cacheKey));

        $meta = $this->meta();
        $table = $meta->getTable();
        $key = $meta->getKey();

        $database = $this->getDatabase();
        $database->transaction();
        $database->lock($table, Database::LOCK_WRITE);
        try {
            $sql = $meta->getSQL('dbo_insert');
            $sth = $database->prepare($sql)->execute($this->getInsertValues());
            $this->id = (int) $database->lastInsertId();

            $cacheKey = self::cacheKey($meta, $this->id);
            assert(
                ! array_key_exists($cacheKey, self::$ObjectCache) ||
                self::$ObjectCache[$cacheKey] === $this
            );
            self::$ObjectCache[$cacheKey] = $this;
            $this->_cacheKey = $cacheKey;
        } catch (Exception $e) {
            $this->id = null;
            $database->rollback();
            $database->unlock();
            throw $e;
        }
        $database->commit();
        $database->unlock();
    }

    /**
     * Fetches the given $id into the current object.
     * This isn't what you want to call, you should be calling DatabaseObject::load
     *
     * @param mixed $id
     * @return boolean
     */
    protected function fetch($id)
    {
        assert(is_int($id));
        if (isset($this->id)) {
            throw new RuntimeException(
                "id already set in fetch, misbehaved subclass?"
            );
        }

        $meta = $this->meta();
        $cacheKey = self::cacheKey($meta, $id);
        assert(
            ! array_key_exists($cacheKey, self::$ObjectCache) ||
            self::$ObjectCache[$cacheKey] === $this
        );

        $database = $this->getDatabase();
        $sql = $meta->getSQL('dbo_select');
        $sth = $database->execute($sql, $id);
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return false;
        }
        $this->unserialize($row, false);
        $this->id = $id;
        $this->_cacheKey = $cacheKey;
        return true;
    }

    /**
     * Updates the current object in the database, based on the properties set
     *
     * @return void
     */
    public function update()
    {
        if (! isset($this->id)) {
            throw new RuntimeException('not created');
        }
        assert(isset($this->_cacheKey));
        assert(array_key_exists($this->_cacheKey, self::$ObjectCache));
        assert(self::$ObjectCache[$this->_cacheKey] === $this);

        $meta = $this->meta();
        $table = $meta->getTable();
        $key = $meta->getKey();
        $sql = $meta->getSQL('dbo_update');
        $database = $this->getDatabase();
        $database->prepare($sql)->execute($this->getUpdateValues());
    }

    /**
     * Removes the current $this->id from the database
     *
     * @return void
     */
    public function delete()
    {
        $meta = $this->meta();
        assert(isset($this->_cacheKey));
        assert(array_key_exists($this->_cacheKey, self::$ObjectCache));
        assert(self::$ObjectCache[$this->_cacheKey] === $this);

        $database = $this->getDatabase();
        $sql = $meta->getSQL('dbo_delete');
        $database->execute($sql, $this->id);
        $this->id = null;
        $this->_cacheKey = null;
        unset(self::$ObjectCache[$this->_cacheKey]);
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
        return DatabaseObjectMeta::forClass(get_class($this), $this->_db);
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
     * Builds the named value array for create
     *
     * @return array
     */
    protected function getInsertValues()
    {
        return $this->getFieldSetValues();
    }

    /**
     * Builds the named value array for update
     *
     * @return array
     */
    protected function getUpdateValues()
    {
        $values = $this->getFieldSetValues();
        $values[":$key"] = $this->id;
        return $values;
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
}

?>
