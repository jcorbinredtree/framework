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
 * 3.) Set $this->table to the appropriate table
 * 4.) Set $this->key to the name of your primary key field
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
    public $table = '';
    
    /**
     * The name of our primary key field
     *
     * @var string
     */
    public $key = '';
    
    /**
     * Called to create the object
     *
     * @return boolean
     */
    public function create()
    {
        global $database, $config;
        
        $database->lock($this->table, Database::LOCK_WRITE);
        {
            $sql = sprintf("INSERT INTO `%s` SET ", $this->table);             
                    
            $fields = $this->getFields();
            $values = array();
            foreach ($fields as $property => $field) {
                $def = $database->getTableFieldDefinition($this->table, $field);
                if (!$def) {
                    continue;
                }
                
                $def = $def[0];                
                if ($this->isDate($def)) {
                    array_push($values, date('Y-m-d H:i:s', (int) $this->$property));
                }
                else {                
                    array_push($values, $this->$property);
                }
                
                $sql .= "`$field`=?,";
            }
            
            $sql = substr($sql, 0, (strlen($sql) - 1));
            if (!$database->prepare($sql)) {
                $config->error("could not prepare db object");
                $database->unlock();
                return false;
            }
            
            if (!$database->execute($values)) {
                $config->error("could not execute insert on db object");
                $database->unlock();
                return false;                
            }
            
            $this->id = $database->lastInsertId();
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
        
        $sql = "SELECT " . $this->getColumnsSQL() . " FROM `$this->table`";
        $sql .= " WHERE `$this->key` = ? LIMIT 1";
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
        global $database, $config;
        
        $fields = $this->getFields();
        $sql = "UPDATE `$this->table` SET ";
        $values = array();
        foreach ($fields as $property => $field) {
            $def = $database->getTableFieldDefinition($this->table, $field);
            if (!$def) {
                continue;
            }
            
            $def = $def[0];
            
            if ($this->isDate($def)) {
                array_push($values, date('Y-m-d H:i:s', (int) $this->$property));
            }
            else {                
                array_push($values, $this->$property);
            }
            
            $sql .= "`$field`=?,";
        }
        
        $sql = substr($sql, 0, (strlen($sql) - 1));
        $sql .= " WHERE `$this->key` = ? LIMIT 1";
        if (!$database->prepare($sql)) {
            $config->error("could not prepare db object");
            return false;
        }                
        
        array_push($values, $this->id);        
        return $database->execute($values);
    }
    
    /**
     * Removes the current $this->id from the database
     *
     * @return boolean
     */
    public function delete()
    {
        global $database;
        
        if ($database->executef("DELETE FROM `$this->table` WHERE `$this->key` = ?", $this->id)) {
            return true;
        }
        
        return false;
    }
    
    private function isDate($def)
    {
        switch (strtolower(Params::generic($def, 'native_type'))) {
            case 'date':
            case 'datetime':
            case 'timestamp':
                return true;        
        }
        
        return false;
    }
    
    public function getColumnsSQL($prefix='')
    {
        global $database;
        
        if (!$prefix) {
            $prefix = $this->table;
        }
        
        $prefix = preg_replace('/[.]$/', '', $prefix);
        
        $fields = $this->getFields();
        $sql = '';
        foreach ($fields as $property => $field) {
            $def = $database->getTableFieldDefinition($this->table, $field);
            if (!$def) {
                continue;
            }
            
            $def = $def[0];
            if ($this->isDate($def)) {
                $sql .= "UNIX_TIMESTAMP(`$prefix`.`$field`) AS `$field`";
            }
            else {
                $sql .= "`$prefix`.`$field`";
            }
            
            $sql .= ', ';
        }
        
        return substr($sql, 0, (strlen($sql) - 2));        
    }    
    
    protected function getFields()
    {
        $fields = get_class_vars(get_class($this));
        $description = array();
        
        foreach ($fields as $field => $value) {
            if ($field == 'id') {
                $description['id'] = $this->key;
            }
            
            $description[$field] = preg_replace_callback('/[A-Z]/', 
                    create_function('$matches', 'return "_" . strtolower($matches[0]);'), $field);
        }
        
        return $description;
    }
}

?>
