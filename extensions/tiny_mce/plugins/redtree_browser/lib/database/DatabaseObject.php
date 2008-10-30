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
 * @category   Database
 * @author     Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright  2007 Red Tree Systems, LLC
 * @license    MPL 1.1
 * @version    1.1
 * @link       http://framework.redtreesystems.com
 */

abstract class DatabaseObject extends RequestObject implements IDatabaseObject {
    public $id = -1;
    public $table = '';
    public $key = '';
    public $silent = false;
    
    private $time;
    private $log;
    
    public function enterQuery()
    {
        global $database;
        
        if (!$this->silent) {
            return;
        }
        
        $this->time = $database->time;
        $this->log = $database->log;
        $database->time = $database->log = false;
    }
    
    public function leaveQuery()
    {
        global $database;
                
        if (!$this->silent) {
            return;
        }        
        
        $database->time = $this->time; 
        $database->log = $this->log; 
    }

    public function exists($id) {
        global $database;
        
        $res = true;
        
        $this->enterQuery();
        {
            $sql = "SELECT COUNT(*) FROM `$this->table` WHERE `$this->key` = '" . $database->escape($id) . "'";
            if (!$database->query($sql)) {
                $this->leaveQuery();
                return true;
            }
            
            $res = ($database->getScalarValue() > 0);
        }
        $this->leaveQuery();

        return $res;
    }

    public function create() {
        global $database;

        $this->enterQuery();
        $database->lock( $this->table, Database::LOCK_WRITE );
        {
            $sql = sprintf( "INSERT INTO `%s` SET ", $this->table );
            $sql .= $this->getFieldsSQL();
            if ( ! ( $database->insert( $sql ) >= 0 ) ) {
                $database->unlock();
                $this->leaveQuery();
                return false;
            }

            $this->id = $database->lastInsertID( $this->table );
        }
        $database->unlock();
        $this->leaveQuery();

        return true;
    }

    public function fetch( $id ) {
        global $database;
         
        $res = false;
        
        $this->enterQuery();
        {
            $sql = "SELECT " . $this->getColumnsSQL() . " FROM `$this->table`";
            $sql .= " WHERE `$this->key` = '" . $database->escape( $id ) . "' LIMIT 1";
            if ( $database->query( $sql ) && $database->count() ) {
                $row = $database->getRow();
                Params::ArrayToObject( $row, $this );
                $this->id = $id;
                $res = true;
            }
        }
        $this->leaveQuery();
         
        return $res;
    }

    public function update() {
        global $database;

        $res = false;
        $this->enterQuery();
        {
            $sql = sprintf( "UPDATE `%s` SET ", $this->table );
            $sql .= $this->getFieldsSQL();
            $sql .= " WHERE `" . $this->key . "` = " . $this->id . ' LIMIT 1';
            
            $res = ( $database->update( $sql ) >= 0 );
        }
        $this->leaveQuery();

        return $res;
    }

    public function delete() {
        global $database;

        $res = false;
        $this->enterQuery();
        {
            $sql = sprintf( "DELETE FROM `%s` WHERE `%s` = '%s'", $this->table, $this->key, $database->escape( $this->id ) );
            $res = ( $database->delete( $sql ) >= 0 );
        }
        $this->leaveQuery();
        
        return $res;
    }

    protected function getFieldsSQL() {
        global $database, $config;
        
        $this->enterQuery();

        $fields = $this->getFields();
        $sql = '';
        foreach ( $fields as $property => $field ) {
            $def = $database->getTableFieldDefinition( $this->table, $field );
            if ( ! $def ) {
                continue;
            }

            $def = $def[ 0 ];
            $type = Params::Generic( $def, 'type', '' );            

            $sql .= "`$field` = ";
            if ( ( $type == 'date' ) || ( $type == 'timestamp' ) ) {
                $mydate = date('Y-m-d H:i:s', (int) $this->$property );
                $sql .= "'" . $mydate . "'";
            }
            elseif ($type == 'blob') {
                if ( Params::Generic( $def, 'notnull', false ) ) {
                    $sql .= "0x" . bin2hex($this->$property);
                }
                else {
                    $sql .= $this->$property ? "0x" . bin2hex($this->$property) : 'null';
                }                
            }
            elseif ($type == 'integer') {
                $val = ($this->$property ? (int) $this->$property : 0);

                if (!is_numeric($val)) {
                    $config->error(get_class($this) . "->$property (=$val) is not numeric!");
                    $val = 0;
                }

                if (Params::Generic( $def, 'notnull', false )) {
                    $sql .= (int) sprintf('%d', $val);
                }
                else {
                    $sql .= (null === $this->$property) ? 'NULL' : (int) sprintf('%d', $val);
                }
            }
            else {
                if ( Params::Generic( $def, 'notnull', false ) ) {
                    $sql .= "'" . $database->escape( $this->$property ) . "'";
                }
                else {
                    $sql .= $database->quote( $this->$property );
                }
            }

            $sql .= ', ';
        }
        
        $this->leaveQuery();

        return substr( $sql, 0, ( strlen( $sql ) - 2 ) );
    }

    protected function getColumnsSQL($prefix='') {
        global $database;

        $fields = $this->getFields();
        $sql = '';
        foreach ( $fields as $property => $field ) {
            $def = $database->getTableFieldDefinition( $this->table, $field );
            if ( ! $def ) {
                continue;
            }

            $def = $def[ 0 ];
            $type = Params::Generic( $def, 'type', '' );
            if ( ( $type == 'date' ) || ( $type == 'timestamp' ) ) {
                $sql .= "UNIX_TIMESTAMP( $prefix`$field` ) AS `$field`";
            }
            else {
                $sql .= "$prefix`$field`";
            }

            $sql .= ', ';
        }

        return substr( $sql, 0, ( strlen( $sql ) - 2 ) );
    }

    private function getFields() {
        $fields = get_class_vars( get_class( $this ) );
        $description = array();

        foreach ( $fields as $field => $value ) {
            if ( $field == 'id' ) {
                $description[ 'id' ] = $this->key;
            }

            $description[ $field ] = preg_replace_callback( '/[A-Z]/',
            create_function( '$matches', 'return "_" . strtolower( $matches[ 0 ] );' ), $field );
        }

        return $description;
    }
}

?>