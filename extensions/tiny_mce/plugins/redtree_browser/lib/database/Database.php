<?php

/**
 * Database class definition
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
 * @version    1.0
 * @link       http://framework.redtreesystems.com
 */

require_once( 'MDB2.php' );

/**
 * Simplification class for PEAR::MDB2
 *
 * There should be only one instance of this class throughout the platform,
 * but is not made into a singleton class for flexibility reasons.
 *
 * @category   Database
 * @package    Core
 */

class Database {
  const LOCK_READ = 0x01;
  const LOCK_WRITE = 0x02;
  
  /**
   * Holds the original MDB2 instance.
   * 
   * @access public
   * @var MDB2
   */
  public $mdb2;
  
  /**
   * If set to true, then all SQL statements will be logged as [info] messages.
   * 
   * @access public
   * @var boolean
   */
  public $log = false;
  
  /**
   * The dsn used for connecting
   * 
   * @access public
   * @var boolean
   */
  public $dsn = false;
  
  /**
   * DB options used to connect
   * 
   * @access public
   * @var boolean
   */
  public $dbOptions = null;
  
  /**
   * If set to true, then all SQL statements will be timed as [info] messages.
   * 
   * @access public
   * @var boolean
   */
  public $time = false;
  
  /**
   * If timing is enabled, this tracks the total time queries
   * have taken to execute
   * 
   * @var double
   * @access private
   */
  private $totalTime = 0;
  
  /**
   * If logging is enabled, this tracks the total queries executed
   * 
   * @var int
   * @access private
   */
  private $totalQueries = 0;

  /**
   * Holds the last MDB2 Result.
   * 
   * @access private
   * @var mixed
   */
  private $lastres;
  
  /**
   * A stack of result objects.
   * 
   * @access private
   * @var mixed
   */
  private $resStack = array();

  /**
   * Serialize
   * 
   * @access public
   * @return array of variables to serialize
   */
  public function __sleep() {
    return array_keys( get_class_vars( get_class( $this ) ) );
  }

  /**
   * Reconnect to the database when we are unserialized.
   * 
   * @access public
   * @return void
   */
  public function __wakeup() {    
    $this->__init();
  }
  
  /**
   * Constructor; Allows for DSN and configuration options.
   * 
   * @access public
   * @param string $dsn a dsn connection, default null
   * @param string $options connection options, default null
   * @return void
   */
  public function __construct( $dsn=null, $options=null ) {
    global $config;
    
    $this->dsn = ( $dsn ? $dsn : $config->dsn );
    $this->dbOptions = ( $options ? $options : $config->dbOptions );

    $this->__init();
  }
  
  /**
   * A method for initializing the class
   * 
   * @access private
   * @return void
   */
  private function __init() {
    global $config;
    
    $this->mdb2 =& MDB2::connect( $this->dsn, $this->dbOptions );

    if ( PEAR::isError( $this->mdb2 ) ) {
      $config->error( $this->error() );
      die( $this->error() );
    }
    
    $this->mdb2->loadModule( 'Reverse', null, true );
    $this->mdb2->loadModule( 'Manager', null, true );
  }

  /**
   * Escapes the given string - made query-ready.
   * 
   * @see the quote() method
   * @access public
   * @param string $string
   * @return string a string, query-ready
   */
  public function escape( $string ) {
    global $config;

    if ( get_magic_quotes_gpc() ) {
      $string = stripslashes( $string );
    }
    
    if ( $this->log ) {
      $config->info( "escape( $string ) = " . $this->mdb2->escape($string) );
    }

    return $this->mdb2->escape($string);
  }
  
  /**
   * Returns the total number of queries executed.
   * Only available if logging was enabled.
   * 
   * @return int
   */
  public function getTotalQueries() {
    return $this->totalQueries;
  }
  
  /**
   * Returns the total time queries have taken to execute.
   * Only available if timing was enabled.
   */
  public function getTotalTime() {
    return $this->totalTime;
  }
  
  /**
   * Quotes a string for inclusion in a query, usually an insert or update.
   * The difference between this and the escape() method is that if the
   * parameter is empty, the method returns null, otherwise it returns the
   * value, quoted in single quotes.
   * 
   * @see the escape() method
   * @access public
   * @param string $string
   * @return string the string, quoted
   */
  public function quote( $string ) {
    global $config;
 
    if ( get_magic_quotes_gpc() ) {
      $string = stripslashes( $string );
    }
     
    if ( $this->log ) {
      $config->info( "quote( $string ) = " . $this->mdb2->quote($string) );
    }
 
    return $this->mdb2->quote($string);
  }  

  /**
   * Returns true if the last operation erred.
   * 
   * @access public
   * @return boolean true if an error occurred
   */
  public function errored() {
    return ( PEAR::isError( $this->mdb2 ) || PEAR::isError( $this->lastres ) );
  }

  /**
   * An alias for the errored() method
   * 
   * @access public
   * @see errored
   * @return boolean true if an error occurred
   */
  public function isError() {
    return $this->errored();
  }

  /**
   * Return error information
   * 
   * @access public
   * @return string error information
   */
  public function error() {
    if ( PEAR::isError( $this->mdb2 ) ) {
      return $this->mdb2->getUserInfo();
    }
    
    if ( PEAR::isError( $this->lastres ) ) {
      return $this->lastres->getUserInfo();
    }
    
    return '';
  }
  
  /**
   * Get the structure of a field into an array
   * 
   * @see http://pear.php.net/package/MDB2/docs/latest/MDB2/MDB2_Driver_Reverse_Common.html#methodgetTableFieldDefinition
	 * @access public
   * @return data array on success, null on failure. The returned array contains an array 
   * for each field definition, with all or some of these indices, depending on the field data 
   * type: [notnull] [nativetype] [length] [fixed] [default] [type] [mdb2type]
   */
  public function getTableFieldDefinition( $table, $field ) {
    $def = $this->mdb2->getTableFieldDefinition( $table, $field );
    if ( Pear::isError( $def ) ) {
      return null;
    }
    
    return $def;
  }
  
  /**
   * Creates a new table
   * 
   * @access public
   * @see http://pear.php.net/package/MDB2/docs/latest/MDB2/MDB2_Driver_Manager_Common.html#methodcreateTable
   * @param table string $table Name of the table that should be created
	 * @param fields array $fields Associative array that contains the definition of each field of the new table 
	 * The indexes of the array entries are the names of the fields of the table an the array entry values 
	 * are associative arrays like those that are meant to be passed with the field definitions to 
	 * get[Type]Declaration() functions. array( 'id' => 
	 * array( 'type' => 'integer', 'unsigned' => 1 'notnull' => 1 'default' => 0 ), 'name' => array( 'type' => 'text', 'length' => 12 ), 
	 * 'password' => array( 'type' => 'text', 'length' => 12 ) );
	 * @param options array $options An associative array of table options: array( 'comment' => 'Foo', 'temporary' => true|false, ); 
   * @return true on success
   */
  public function createTable( $table, $fields ) {
    return ( ! Pear::isError( $this->mdb2->createTable( $table, $fields ) ) );
  }
  
  /**
   * Drops a table
   * 
   * @access public
   * @see http://pear.php.net/package/MDB2/docs/latest/MDB2/MDB2_Driver_Manager_Common.html#methoddropDatabase
   * @param table string $table the table name
   * @return true on success
   */
  public function dropTable( $table ) {
    return ( ! Pear::isError( $this->mdb2->dropTable( $table ) ) );
  }

  /**
   * Executes an arbitrary SQL statement.
   * 
   * @access public
   * @param string $sql the SQL you want to execute
   * @param string $type the type of statement you're executing.
   * This is only relevant with logging on, as it's what shows up.
   * The default is 'execute'.
   * @return int the number of rows affected
   */
  public function execute( $sql, $type='execute' ) {
    global $config;    

    $rows = 0;
    $time = $start = 0;

    if ( $this->time ) {
      $start = microtime( true );
    }

    $rows = $this->mdb2->exec( $sql );

    if ( $this->time ) {
      $time = microtime( true ) - $start;
      $this->totalTime += $time;
    }

    if ( PEAR::isError( $rows ) || ( $rows < 0 ) ) {
      $config->error( sprintf( '%s { %s } failed: %s', $type, $sql,
        PEAR::isError($rows)
            ? $rows->getUserInfo() 
            : $this->error() 
      ) );
      return null;
    }
    
    if ( $this->log && $this->time ) {
      $this->totalQueries++;
      $config->info( sprintf( '%s( %s ) executed in %.4f seconds, %d rows affected', 
			       $type, $sql, $time, $rows ) );
    }
    elseif ( $this->time ) {
      $config->info( sprintf( '%s executed in %.4f seconds, %d rows affected', 
			       $type, $time, $rows ) );
    }
    elseif ( $this->log ) {
      $this->totalQueries++;
      $config->info( sprintf( '%s( %s ) %d rows affected', 
			       $type, $sql, $this->count() ) );
    }

    return $rows;
  }

  /**
   * Locks the specified tables.
   * 
   * @access public
   * @param mixed $tables either an array of table names or a single table name to lock
   * @param int $type the type of lock to aquire. You may use the bit masks Database::LOCK_READ,
   * and Database::LOCK_WRITE
   * @return boolean true if the lock was obtained
   */
  public function lock( $tables, $type=Database::LOCK_READ ) {
    global $config;

    if ( ! is_array( $tables ) ) {
      $tables = array( $tables );
    }

    foreach ( array( 'READ' => Database::LOCK_READ, 'WRITE' => Database::LOCK_WRITE ) as $op => $mask ) {
      if ( $type & $mask ) {
        $sql = 'LOCK TABLES ' . implode( " $op, ", $tables ) . " $op";

        if ( $this->execute( $sql, 'lock' ) < 0 ) {
          return false;
        }
      }
    }

    return true;
  }

  /**
   * Unlocks any tables previousy locked. It's assumed to be safe to call this
   * even if you haven't locked any tables.
   * 
   * @access public
   * @return boolean true upon success; false otherwise 
   */
  public function unlock() {
    return ( $this->execute( "UNLOCK TABLES", 'unlock' ) < 0 );
  }

  /**
   * Uses the given SQL to insert into the database.
   * 
   * @access public
   * @param string $sql the SQL to insert
   * @return int the number of rows inserted, or -1 on error
   */
  public function insert( $sql ) {
    return ( null === ( $rows = $this->execute( $sql, 'insert' ) ) ? -1 : $rows );
  }

  /**
   * Uses the given SQL to update into the database.
   * 
   * @access public
   * @param string $sql the SQL to update
   * @return int the number of rows updated, or -1 on error.
   * This has been known to return 0 rows if the fields are
   * updated with the same values.
   */
  public function update( $sql ) {
    return ( null === ( $rows = $this->execute( $sql, 'update' ) ) ? -1 : $rows );
  }

  /**
   * Uses the given SQL to delete from the database.
   * 
   * @access public
   * @param string $sql the SQL to delete
   * @return int the number of rows removed
   */
  public function delete( $sql ) {
    return ( null === ( $rows = $this->execute( $sql, 'delete' ) ) ? -1 : $rows );
  }

  /**
   * Uses the given SQL to query the database.
   * 
   * @access public
   * @param string $sql the SQL to delete
   * @return boolean
   */
  public function query( $sql ) {
    global $config;

    $time = $start = 0;

    if ( $this->time ) {
      $start = microtime( true );
    }

    $this->lastres =& $this->mdb2->query( $sql );
    
    if ( $this->time ) {
      $time = ( microtime( true ) - $start );
      $this->totalTime += $time;
    }    

    if ( $this->errored() ) {
      $config->error( "Query failed: " . $this->error() );
      return false;
    }
    
    if ( $this->log && $this->time ) {
      $this->totalQueries++;
      $config->info( sprintf( 'query( %s ) executed in %.4f seconds, %d rows returned', 
			       $sql, $time, $this->count() ) );
    }
    elseif ( $this->log ) {
      $this->totalQueries++;
      $config->info( sprintf( 'query( %s ) %d rows returned', 
			       $sql, $this->count() ) );
    }
    elseif ( $this->time ) {      
      $config->info( sprintf( 'query executed in %.4f seconds, %d rows returned', 
			       $time, $this->count() ) );
    }

    array_push( $this->resStack, $this->lastres );

    return true;
  }
 
  /**
   * Queries the database and returns an array of single values.
   * 
   * @access public
   * @since v1.7
   * @param string $sql the SQL for your query
   * @return array an array of single values
   */
  public function queryForResultValues( $sql ) {
    $output = array();
    
    if ( $this->query( $sql ) ) {
      while( $row = $this->getScalarValue( false ) ) {
        array_push( $output, $row );
      }

      $this->free();
    }

    return $output;
  }
 
  /**
   * Queries the database and returns an array of object rows.
   * The objects returned have been run through Params::ArrayToObject.
   * 
   * @access public
   * @since v1.7
   * @see Params::ArrayToObject
   * @param string $sql the SQL for your query
   * @param string $type the type of objects to be returned
   * @return array an array of object rows 
   */
  public function queryForResultObjects( $sql, $type='stdClass' ) {
    $output = array();

    if ( $this->query( $sql ) ) {
      while( $row = $this->getObject( $type, false ) ) {
        array_push( $output, $row );
      }

      $this->free();
    }

    return $output;    
  }  

  /**
   * Returns the last auto_increment number used to insert a row.
   * 
   * @access public
   * @param table string $table the table name [optional]
   * @return int the last auto_increment number
   */
  public function lastInsertID( $table=null ) {
    global $config;

    if ( $this->log ) {
      $config->info( "lastInsertID( " . $this->mdb2->lastInsertID() . " )" );
    }

    return $this->mdb2->lastInsertID( $table );
  }

  /**
   * Returns the number of items in the current result set.
   * 
   * @access public
   * @return int the number of rows in the result set
   */
  public function count() {
    return (int) ( ( ! MDB2::isResult( $this->lastres ) ) ? 0 : $this->lastres->numRows() );
  }

  /**
   * Fetch one row from the last result set.
   * 
   * @access public
   * @param boolean $kill the default, true, will free() the connection
   * @return mixed a result
   */
  public function getScalarValue( $kill=true ) {
    if ( $this->errored() ) {
      return null;
    }
    
    $val = $this->lastres->fetchOne();
    
    if ( $kill ) {
      $this->free();
    }
    
    return $val;
  }

  /**
   * Fetches a row from the current result set. Note that the default
   * is now MDB2_FETCHMODE_ASSOC.
   * 
   * @access public
   * @see getObject
   * @param int $as can fetch in object, array, or keyed mode
   * @param boolean $kill the default, true, will free() the connection
   * @return mixed a row
   */
  public function getRow( $as=MDB2_FETCHMODE_ASSOC, $kill=true ) {
    if ( $this->errored() ) {
      return null;
    }
    
    $row = $this->lastres->fetchRow( $as );
      
    if ( $kill ) {
      $this->free();
    } 

    return $row;
  }
  
  /**
   * Fetches an object from the current result set. This maps field
   * names, replacing _ with the next letter capitalized. Note that
   * fields will be created on your object if they do not exist
   * 
   * @access public
   * @param string $type the type of objects to be returned
   * @param boolean $kill the default, true, will free() the connection
   * @see Params::ArrayToObject
   * @return $type an object
   */
  public function getObject( $type='stdClass', $kill=true ) {    
    if ( $this->errored() ) {
      return null;
    }
    
    $obj = new $type();
    if ( $row = $this->lastres->fetchRow( MDB2_FETCHMODE_ASSOC ) ) {
      if ( ( $obj instanceof IDatabaseObject ) && ( isset( $row[ $obj->key ] ) ) ) {
        $row[ 'id' ] = $row[ $obj->key ];
      }
            
      Params::ArrayToObject( $row, $obj, true );
    
      if ( $kill ) {
        $this->free();
      }
      
      return $obj;      
    }   
    
    return null;
  }  

  /**
   * Fetches a specific row number from the current result set.
   * 
   * @access public
   * @param int $x the row number to fetch
   * @return object a row as an object, unmapped
   */
  public function getRowNumber( $x ) {
    return ( $this->errored() ? null : $this->lastres->fetchRow( MDB2_FETCHMODE_OBJECT, $x ) );
  }

  /**
   * Frees the last result set
   * 
   * @access public
   * @return void
   */
  public function free() {
    global $config;
    
    $res = array_pop( $this->resStack );
    $this->lastres = end( $this->resStack );
    
    if ( ! $res ) {
      $config->error( "ERRONEOUS CALL" );
      return;
    }

    $res->free();
  }
}

?>