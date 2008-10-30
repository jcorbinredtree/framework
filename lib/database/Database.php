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
 * @category     Database
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Simplification class for PDO
 *
 * There should be only one instance of this class throughout the platform,
 * but is not made into a singleton class for flexibility reasons.
 *
 * @category     Database
 * @package      Core
 */

class Database
{
    const LOCK_READ = 0x01;
    const LOCK_WRITE = 0x02;
    
    /**
     * Holds the original PDO instance.
     * 
     * @access public
     * @var PDO
     */
    public $pdo;
    
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
     * Holds the current statement
     * 
     * @var PDOStatement
     */
    private $statement;
    
    /**
     * A stack of statements
     * 
     * @access private
     * @var array
     */
    private $statementStack = array();

    /**
     * Serialize
     * 
     * @access public
     * @return array of variables to serialize
     */
    public function __sleep()
    {
        $arr = get_class_vars(get_class($this));
        unset($arr['pdo']);
        return array_keys($arr);
    }

    /**
     * Reconnect to the database when we are unserialized.
     * 
     * @access public
     * @return void
     */
    public function __wakeup()
    {        
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
    public function __construct($dsn=null, $options=null)
    {
        global $config;
        
        $this->dsn = ($dsn ? $dsn : $config->getDatabaseInfo());
        $this->dbOptions = ($options ? $options : $config->getDatabaseOptions());

        $this->__init();
    }
    
    /**
     * A method for initializing the class
     * 
     * @access private
     * @return void
     */
    private function __init()
    {
        global $config;
        
        $dsn = new stdClass();
        $matches = array();
        
        /*
         * parse the dsn
         */ 
        if (preg_match('|^(.+?)[:][/]{2}(.+?)[:](.+?)[@](.+)[/](.+)|', $this->dsn, $matches)) {
            $dsn->driver = $matches[1];
            $dsn->user = $matches[2];
            $dsn->password = $matches[3];
            $dsn->host = $matches[4];
            $dsn->db = $matches[5];
        }
        else {
            $config->fatal("Unable to parse the dsn: $this->dsn");
            die('Unable to parse dsn');
        }
        
        try {
            if (is_array($this->dbOptions)) {
                $this->dbOptions[PDO::ATTR_PERSISTENT] = true;                
            }
            else {
                $this->dbOptions = array(PDO::ATTR_PERSISTENT => true);                
            }
            
            $this->pdo = new PDO("$dsn->driver:host=$dsn->host;dbname=$dsn->db", 
                                 $dsn->user, $dsn->password, $this->dbOptions);
        } catch (PDOException $e) {
            $config->fatal($e->getMessage());
            die("Unable to connect to the database");
        }
        
        $this->pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);        
    }
    
    /**
     * Returns the total number of queries executed.
     * Only available if logging was enabled.
     * 
     * @return int
     */
    public function getTotalQueries()
    {
        return $this->totalQueries;
    }
    
    /**
     * Returns the total time queries have taken to execute.
     * Only available if timing was enabled.
     */
    public function getTotalTime()
    {
        return $this->totalTime;
    }
    
    /**
     * Gets the last statement
     *
     * @return PDOStatement
     */
    public function getStatement()
    {
        return $this->statement;
    }
    
    /**
     * Sets the current statement
     *
     * @param PDOStatement $statement
     * @return PDOStatement
     */
    public function setStatement(PDOStatement &$statement)
    {
        return $this->statement = $statement;
    }

    /**
     * Return error information
     * 
     * @access public
     * @return string error information
     */
    public function error()
    {
        $arr = null;
        
        if ($this->statement) {
           $arr = $this->statement->errorInfo();
        }
        else {
            $arr = $this->pdo->errorInfo();
        }
        
        return $arr[2];
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
    public function getTableFieldDefinition($table, $field)
    {
        global $config;
        
        /*
         * @WARNING: this is mysql-specific
         */
        try {
            $sql = "SELECT `$field` FROM `$table` LIMIT 1";
            $sth = $this->pdo->query($sql);
            $desc = $sth->getColumnMeta(0);
            $out = array($desc);
            $out[0]['notnull'] = in_array('not_null', $desc);
            return $out;            
        } catch (Exception $e) {
            return null;
        }
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
    public function lock($tables, $type=Database::LOCK_READ)
    {
        global $config;

        if (!is_array($tables)) {
            $tables = array($tables);
        }

        foreach (array('READ' => Database::LOCK_READ, 'WRITE' => Database::LOCK_WRITE) as $op => $mask) {
            if ($type & $mask) {
                $sql = 'LOCK TABLES ' . implode(" $op, ", $tables) . " $op";

                if ($this->perform($sql, 'lock') < 0) {
                    return false;
                }
            }
        }

        return true;
    }
    
	/**
     * Performs an arbitrary SQL statement.
     * 
     * @access public
     * @param string $sql the SQL you want to perform
     * @param string $type the type of statement you're executing.
     * This is only relevant with logging on, as it's what shows up.
     * The default is 'perform'.
     * @return int the number of rows affected
     */
    public function perform($sql, $type='perform')
    {
        global $config;        

        $rows = 0;
        $time = $start = 0;

        if ($this->time) {
            $start = microtime(true);
        }

        try {
            $rows = $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $config->error(sprintf('%s { %s } failed: %s', $type, $sql, $e->getMessage()), 3);
            return false;
        }
        
        if ($this->time) {
            $time = microtime(true) - $start;
            $this->totalTime += $time;
        }
        
        if ($this->log && $this->time) {
            $this->totalQueries++;
            $config->info(sprintf('%s(%s) performed in %.4f seconds, %d rows affected', 
                         $type, $sql, $time, $rows), 3);
        }
        elseif ($this->time) {
            $config->info(sprintf('%s performed in %.4f seconds, %d rows affected', 
                         $type, $time, $rows), 3);
        }
        elseif ($this->log) {
            $this->totalQueries++;
            $config->info(sprintf('%s(%s) %d rows affected', 
                         $type, $sql, $rows), 3);
        }

        return $rows;
    }    

    /**
     * Unlocks any tables previousy locked. It's assumed to be safe to call this
     * even if you haven't locked any tables.
     * 
     * @access public
     * @return boolean true upon success; false otherwise 
     */
    public function unlock()
    {
        return ($this->perform("UNLOCK TABLES", 'unlock') < 0);
    }
    
    /**
     * Executes a prepared statement
     *
     * @param mixed $param an object, assoc array mapping to the names of
     * the bound columns
     * @return true upon success
     */
    public function execute($param=null)
    {
        global $config;
        
        $params = null;
        if ($param) {
            if (is_object($param)) {
                $params = array();
                $vars = get_object_vars($param);
                foreach ($vars as $key => $val) {
                    $params[":$key"] = $val;
                } 
            }
            else {
                $params =& $param;
            }
        }

        $time = $start = 0;

        if ($this->time) {
            $start = microtime(true);
        }

        try {
            $result = false;
        
            if ($params) {
                $result = $this->statement->execute($params);
            }
            else {
                $result = $this->statement->execute();
            }            
            
            if (!$result) {
                $config->error("execute failed", 3);
                return false;
            }
        } catch (PDOException $e) {
            $config->error('execute failed: ' . $e->getMessage(), 3);
            return false;
        }    

        $params = ($params ? join(',', $params) : '');
        
        if ($this->time) {
            $time = microtime(true) - $start;
            $this->totalTime += $time;
        }
        
        $rows = $this->count();
        
        if ($this->log && $this->time) {
            $this->totalQueries++;
            $config->info(sprintf('execute(%s|%s) performed in %.4f seconds, %d rows returned', $this->statement->queryString, $params, $time, $rows), 3);
        }
        elseif ($this->time) {
            $config->info(sprintf('execute(%s|%s) performed in %.4f seconds, %d rows returned', $this->statement->queryString, $params, $time, $rows), 3);
        }
        elseif ($this->log) {
            $this->totalQueries++;
            $config->info(sprintf('execute(%s|%s) succeeded, %d rows returned', $this->statement->queryString, $params, $rows), 3);
        }

        return true;
    }
    
    /**
     * Prepares and executes a statement
     *
     * @param string $sqlf a SQL string, with positional(?) based preparation
     * @param arglist ... variable arguments representing the prepared args
     * @return unknown
     */
    public function executef($sqlf) 
    {
        $start = microtime(true);
        $logging = $this->log;
        $timing = $this->time;
        
        $this->log = $this->time = false;
        if (!$this->prepare($sqlf)) {
            $this->log = $logging;
            $this->time = $timing;
            return false;
        }
        
        $args = func_get_args();        
        for ($i = 1; $i < count($args); $i++) {
            $this->bindValue($i, $args[$i]);
        }
        
        $res = $this->execute();
        
        $this->log = $logging;
        $this->time = $timing;
        if ($this->time) {
            global $config;
            
            $time = (microtime(true) - $start);
            
            ++$this->totalQueries;
            $this->totalTime += $time;
            
            if ($this->log) {
                array_shift($args);
                $args = implode('","', $args);
                if ($args) {
                    $args = "|\"$args\"";  
                }
                
                $config->info(sprintf("executef(%s%s) executed in %.4f seconds, %d rows returned", $sqlf, $args, $time, $this->count()), 3);
            }
        }
        
        return $res;
    }
    
    /**
     * Prepares the given SQL for db'ing
     *
     * @param string $sql
     * @return true upon success
     */
    public function prepare($sql)
    {
        global $config;
        
        $start = microtime(true);        

        try {
            $this->statement = $this->pdo->prepare($sql);
        }
        catch (PDOException $e) {
            $config->error("prepare '$sql' failed: " . $e->getMessage(), 3);
            return false;
        }        
        
        $time = (microtime(true) - $start);
        if ($this->time) {
            $this->totalTime += $time;
            ++$this->totalQueries;
        }        
        
        if ($this->log) {
            $config->info(sprintf("prepare(%s) analyzed in %.4f seconds", $sql, $time), 3);
        }

        array_push($this->statementStack, $this->statement);

        return true;        
    }

    /**
     * Uses the given SQL to query the database.
     * 
     * @access public
     * @param string $sql your SQL statement
     * @return boolean
     */
    public function query($sql)
    {
        global $config;

        $time = $start = 0;

        if ($this->time) {
            $start = microtime(true);
        }

        try {
            $this->statement = $this->pdo->query($sql);
        }
        catch (PDOException $e) {
            $config->error("query '$sql' failed: " . $e->getMessage(), 3);
            return false;
        }
        
        if ($this->time) {
            $time = (microtime(true) - $start);
            $this->totalTime += $time;
        }    
        
        if ($this->log && $this->time) {
            $this->totalQueries++;
            $config->info(sprintf('query(%s) executed in %.4f seconds, %d rows returned', 
                         $sql, $time, $this->count()), 3);
        }
        elseif ($this->log) {
            $this->totalQueries++;
            $config->info(sprintf('query(%s) %d rows returned', 
                         $sql, $this->count()), 3);
        }
        elseif ($this->time) {            
            $config->info(sprintf('query executed in %.4f seconds, %d rows returned', 
                         $time, $this->count()), 3);
        }

        array_push($this->statementStack, $this->statement);

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
    public function queryForResultValues($sql)
    {        
        if ($this->query($sql)) {
            return $this->getResultValues();
        }

        return array();
    }
 
    /**
     * Returns an array of single values based on the last prepare/execute
     * 
     * @access public
     * @since v2.0
     * @return array an array of single values
     */
    public function getResultValues()
    {
        $output = array();
        
        while($row = $this->getScalarValue(false)) {
            array_push($output, $row);
        }

        $this->free();

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
    public function queryForResultObjects($sql, $type='stdClass')
    {
        if ($this->query($sql)) {
           return $this->getResultObjects($type);
        }
        
        return array();
    }    
    
    /**
     * Returns an array of object rows based on the last prepare/execute
     * The objects returned have been run through Params::ArrayToObject.
     * 
     * @access public
     * @since v2.0
     * @see Params::ArrayToObject
     * @param string $type the type of objects to be returned
     * @return array an array of object rows 
     */
    public function getResultObjects($type='stdClass')
    {
        $output = array();
        
        while($row = $this->getObject($type, false)) {
            array_push($output, $row);
        }

        $this->free();

        return $output;        
    }        

    /**
     * Returns the last auto_increment number used to insert a row.
     * 
     * @access public
     * @return int the last auto_increment number
     */
    public function lastInsertId()
    {
        global $config;
        
        $id = $this->pdo->lastInsertId();

        if ($this->log) {
            $config->info("lastInsertID($id)");
        }

        return $id;
    }

    /**
     * Returns the number of items in the current result set.
     * 
     * @access public
     * @return int the number of rows in the result set
     */
    public function count()
    {
        return (int) ($this->statement ? $this->statement->rowCount() : -1);
    }
    
    /**
     * Binds a value to a corresponding named or question mark placeholder in the SQL statement 
     * that was used to prepare the statement.
     *
     * @see http://www.php.net/manual/en/function.PDOStatement-bindValue.php
     * @param int|string $param Parameter identifier. For a prepared statement using named placeholders, 
     * this will be a parameter name of the form :name. For a prepared statement using 
     * question mark placeholders, this will be the 1-indexed position of the parameter.
     * @param $value The value to bind to the parameter.
     * @return void
     */
    public function bindValue($param, $value)
    {
        global $config;
        
        if ($this->log) {
            $config->info("bindValue($param=$value)", 3);
        }
        
        $this->statement->bindValue($param, $value);
    }

    /**
     * Fetch one row from the last result set.
     * 
     * @access public
     * @param boolean $kill the default, true, will free() the connection
     * @return mixed a result
     */
    public function getScalarValue($kill=true)
    {
        $val = $this->statement->fetchColumn();
        
        if ($kill) {
            $this->free();
        }
        
        return $val;
    }

    /**
     * Fetches a row from the current result set. Note that the default
     * is now PDO::FETCH_ASSOC.
     * 
     * @access public
     * @see getObject
     * @param int $as can fetch in object, array, or keyed mode
     * @param boolean $kill the default, true, will free() the statement
     * @return mixed a row
     */
    public function getRow($as=PDO::FETCH_ASSOC, $kill=true)
    {           
        $row = $this->statement->fetch($as);
            
        if ($kill) {
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
    public function getObject($type='stdClass', $kill=true)
    {       
        $obj = new $type();
        if ($row = $this->getRow(PDO::FETCH_ASSOC, false)) {
            if (($obj instanceof IDatabaseObject) && (isset($row[$obj->key]))) {
                $row['id'] = $row[$obj->key];
            }
                        
            Params::ArrayToObject($row, $obj, true);
        
            if ($kill) {
                $this->free();
            }
            
            return $obj;            
        }     
        
        return null;
    }  

    /**
     * Frees the last result set
     * 
     * @access public
     * @return void
     */
    public function free()
    {
        global $config;
        
        $sth = array_pop($this->statementStack);
        $this->statement =& end($this->statementStack);
    }
}

?>
