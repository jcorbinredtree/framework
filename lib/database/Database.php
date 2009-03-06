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
 * @version      3.0
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
    /**
     * Utility to format a time string from a number of seconds
     */
    static public function formatTime($time)
    {
        $sec = $time % 60;
        $time = ($time-$sec)/60;
        $min = $time % 60;
        $time = ($time-$min)/60;
        return sprintf('%d:%02d:%02d', $time, $min, $sec);
    }

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
     * @var array
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
     * The parsed DSN
     *
     * @var string
     */
    private $parsedDSN = '';

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
     * The current transaction level (allows nestable transactions)
     */
    private $transactionLevel = 0;

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
        $this->init();
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

        if (isset($dsn)) {
            $this->dsn = $dsn;
        } else {
            $this->dsn = $config->getDatabaseInfo();
        }

        if (isset($options)) {
            $this->dbOptions = $options;
        } else {
            $this->dbOptions = $config->getDatabaseOptions();
        }

        $this->init();
    }

    /**
     * Parses a DSN string
     *
     * Expects a string formated like:
     *   driver://user:password@host/db
     * and returns an object with the following fields defined:
     *   driver
     *   user
     *   password
     *   host
     *   db
     * with the obvious correspondence with the string
     *
     * @param sDsn string, the dsn string
     *
     * @return Object the parsed dsn
     */
    public static function parseDSN($sDsn) {
        if (! isset($sDsn) || ! $sDsn) {
            return null;
        }

        $dsn = new stdClass();
        $matches = array();

        /*
         * parse the dsn
         */
        if (preg_match('|^(.+?)://(.+?):(.*?)@(.+)/(.+)|', $sDsn, $matches)) {
            $dsn->driver = $matches[1];
            $dsn->user = $matches[2];
            $dsn->password = $matches[3];
            $dsn->host = $matches[4];
            $dsn->db = $matches[5];
        } else {
            throw new InvalidArgumentException(
                "Unable to parse the dsn: $this->dsn"
            );
        }

        return $dsn;
    }

    /**
     * A method for initializing the class
     *
     * @access private
     * @return void
     */
    private function init()
    {
        $this->parsedDSN = self::parseDSN($this->dsn);
    }

    /**
     * Connects to the database if not already connected.
     *
     * @return void
     */
    private function lazyLoad()
    {
        if ($this->pdo) {
            return;
        }

        if (! is_array($this->dbOptions)) {
            $this->dbOptions = array();
        }

        $this->dbOptions[PDO::ATTR_PERSISTENT] = true;
        $this->dbOptions[PDO::ATTR_CASE] = PDO::CASE_NATURAL;
        $this->dbOptions[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $this->dbOptions[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;

        $this->pdo = new PDO(
            sprintf('%s:host=%s;dbname=%s',
                $this->parsedDSN->driver,
                $this->parsedDSN->host,
                $this->parsedDSN->db
            ),
            $this->parsedDSN->user,
            $this->parsedDSN->password,
            $this->dbOptions
        );

        register_shutdown_function(array($this, 'unlock'));
    }

    private $dsnId;
    /**
     * Returns a string that tryes to be a unique dsn identifier
     *
     * @return string
     */
    public function dsnId()
    {
        if (! isset($this->dsnId)) {
            $this->dsnId = sprintf('%s://%s/%s',
                $this->parsedDSN->driver,
                $this->parsedDSN->host,
                $this->parsedDSN->db
            );
        }
        return $this->dsnId;
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
     *
     * @return int
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
    public function setStatement(PDOStatement $statement)
    {
        return $this->statement = $statement;
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
        $this->lazyLoad();

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
        $rows = 0;

        $this->lazyLoad();

        try {
            $this->startTiming();
            $rows = $this->pdo->exec($sql);
            $this->endTiming();
        } catch (PDOException $e) {
            $this->endTiming();
            throw new DatabaseException($this, "$type($sql)", $e->getMessage());
        } catch (Exception $e) {
            $this->endTiming();
            throw $e;
        }

        if ($this->log) {
            $what = $this->whatStatement($type, $sql);
            $this->infoLog($what, $rows);
        }

        return $rows;
    }

    /**
     * Executes a prepared statement
     *
     * @param mixed $param an object, assoc array mapping to the names of
     * the bound columns, or a normal array containing the values in order
     *
     * @return PDOStatement
     */
    public function execute($param=null)
    {
        $params = null;
        if ($param) {
            if (is_object($param)) {
                $params = array();
                $vars = get_object_vars($param);
                foreach ($vars as $key => &$val) {
                    if (null !== $val) {
                        $params[":$key"] =& $val;
                    }
                }
                unset($val);
            } elseif (isset($param)) {
                if (! is_array($param)) {
                    throw new InvalidArgumentException('not an array');
                }
                $params =& $param;
            }
        }

        $what = $this->whatStatement('execute', $params);
        try {
            $this->startTiming();
            if ($params) {
                $this->statement->execute($params);
            } else {
                $this->statement->execute();
            }
            $this->endTiming();
        } catch (PDOException $e) {
            $this->endTiming();
            $this->free();
            throw new DatabaseException($this, $what, $e->getMessage());
        } catch (Exception $e) {
            $this->endTiming();
            $this->free();
            throw $e;
        }

        if ($this->log) {
            $this->infoLog($what, $this->count());
        }

        return $this->statement;
    }

    /**
     * Prepares and executes a statement
     *
     * @param string $sqlf a SQL string, with positional(?) based preparation
     * @param arglist ... variable arguments representing the prepared args
     * @return PDOStatement
     */
    public function executef($sqlf)
    {
        $this->startTiming();

        try {
            $this->prepare($sqlf);

            // Temporarily disable logging
            $logging = $this->log;
            $this->log = false;

            // bind function args. if an array was passed, then flatten it
            $args = array_slice(func_get_args(), 1);
            $index = 1;
            foreach ($args as &$arg) {
                if (is_array($arg)) {
                    foreach ($arg as &$a) {
                        $this->bindParam($index++, $a);
                    }
                    unset($a);

                    break;
                } # else, not array

                $this->bindParam($index++, $arg);
            }
            unset($arg);

            $this->execute();
            $this->endTiming();
            $this->log = $logging;
        } catch (Exception $e) {
            $this->endTiming();
            $this->log = $logging;
            throw $e;
        }

        if ($this->log) {
            $what = $this->whatStatement('executef', $args);
            $this->infoLog($what, $this->count());
        }

        return $this->statement;
    }

    /**
     * Prepares the given SQL for db'ing
     *
     * @param string $sql
     * @return PDOStatement
     */
    public function prepare($sql)
    {
        $this->lazyLoad();

        $what = $this->whatStatement('prepare', $sql);
        try {
            $this->startTiming();
            $sth = $this->pdo->prepare($sql);
            $this->endTiming();
            $this->statement = $sth;
        } catch (PDOException $e) {
            $this->endTiming();
            throw new DatabaseException($this, $what, $e->getMessage());
        } catch (Exception $e) {
            $this->endTiming();
            throw $e;
        }

        if ($this->log) {
            $this->infoLog($this->whatStatement('prepare', $sql));
        }

        array_push($this->statementStack, $this->statement);

        return $this->statement;
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
        $this->lazyLoad();

        $what = $this->whatStatement('query', $sql);
        try {
            $this->startTiming();
            $this->statement = $this->pdo->query($sql);
            $this->endTiming();
        } catch (PDOException $e) {
            $this->endTiming();
            throw new DatabaseException($this, $what, $e->getMessage());
        } catch (Exception $e) {
            $this->endTiming();
            throw $e;
        }

        if ($this->log) {
            $what = $this->whatStatement('query', $sql);
            $this->infoLog($what, $this->count());
        }

        array_push($this->statementStack, $this->statement);
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
        $args = func_get_args();
        array_shift($args);

        if ($this->executef($sql, $args)) {
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

        for ($i = 0; $i < $this->statement->rowCount(); $i++) {
            $row = $this->getScalarValue(false);
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
     * @param arglist ... variable arguments representing the prepared args
     * @return array an array of object rows
     */
    public function queryForResultObjects($sql, $type='stdClass')
    {
        $args = func_get_args();
        if (count($args) > 2) {
            array_shift($args);
            array_shift($args);

            /* flatten array args */
            $tmp = array();
            foreach ($args as $arg) {
                if (is_array($arg)) {
                    foreach ($arg as $i) {
                        array_push($tmp, $i);
                    }

                    continue;
                }

                array_push($tmp, $arg);
            }

            $args = $tmp;
        } else {
            $args = array();
        }

        $this->executef($sql, $args);
        return $this->getResultObjects($type);
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

        for ($i = 0; $i < $this->statement->rowCount(); $i++) {
            array_push($output, $this->getObject($type, false));
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
        $this->lazyLoad();

        $id = $this->pdo->lastInsertId();

        if ($this->log) {
            $this->infoLog("lastInsertId($id)");
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
     * Like bindValue, except binds by reference instead of copying the data in
     * value; then contents of value must be available when the statement
     * executes.
     *
     * @see bindValue
     * @param int|string $param parameter identifier. for a prepared statement using named placeholders,
     * this will be a parameter name of the form :name. for a prepared statement using
     * question mark placeholders, this will be the 1-indexed position of the parameter.
     * @param $value the value to bind to the parameter.
     * @return void
     */
    public function bindParam($param, &$value)
    {
        if ($this->log) {
            $this->infoLog("bindValue($param=$value)");
        }

        $this->statement->bindParam($param, $value);
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
    public function bindValue($param, &$value)
    {
        $this->statement->bindValue($param, $value);

        if ($this->log) {
            $this->infoLog("bindValue($param=$value)");
        }
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
            $meta = new DatabaseObjectMeta($type);
            $key = $meta->getKey();
            if ($obj instanceof DatabaseObject && isset($row[$key])) {
                $row['id'] = $row[$key];
            }

            Params::arrayToObject($row, $obj, true);

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
        $sth = array_pop($this->statementStack);
        $this->statement =& end($this->statementStack);
    }

    /**
     * Locks the specified tables.
     *
     * @access public
     * @param mixed $tables either an array of table names or a single table name to lock
     * @param int $type the type of lock to aquire. You may use the bit masks Database::LOCK_READ,
     * and Database::LOCK_WRITE
     * @return void
     */
    public function lock($tables, $type=Database::LOCK_READ)
    {
        foreach (array(
            'READ' => Database::LOCK_READ,
            'WRITE' => Database::LOCK_WRITE
        ) as $op => $mask) {
            if ($type & $mask) {
                $sql = 'LOCK TABLES '.implode(" $op, ", (array) $tables)." $op";
                if ($this->perform($sql, 'lock') < 0) {
                    throw new DatabaseException($this,
                        'lock', 'no rows affected'
                    );
                }
            }
        }
    }

    /**
     * Unlocks any tables previousy locked. It's assumed to be safe to call this
     * even if you haven't locked any tables.
     *
     * @access public
     * @return void
     */
    public function unlock()
    {
        $this->perform("UNLOCK TABLES", 'unlock');
    }

    /**
     * Begins a transaction
     *
     * @see http://us.php.net/manual/en/pdo.begintransaction.php
     * @return boolean true on success
     */
    public function transaction()
    {
        if ($this->transactionLevel++) {
            $this->infoLog('transaction(start ignored)');
            return;
        }

        $this->lazyLoad();

        $this->startTiming();

        $ret = $this->pdo->beginTransaction();

        $this->endTiming();

        if ($this->log) {
            $this->infoLog('transaction(start)');
        }

        return $ret;
    }

    /**
     * Rolls a transaction back
     *
     * @see http://us.php.net/manual/en/pdo.rollback.php
     * @return boolean true on success
     */
    public function rollback()
    {
        if (--$this->transactionLevel) {
            $this->infoLog('transaction(rollback ignored)');
            return;
        }

        $this->lazyLoad();

        $this->startTiming();

        $ret = $this->pdo->rollBack();

        $this->endTiming();

        if ($this->log) {
            $this->infoLog('transaction(rollback)');
        }

        return $ret;
    }

    /**
     * Commits a transaction
     *
     * @see http://us.php.net/manual/en/pdo.commit.php
     * @return boolean true on success
     */
    public function commit()
    {
        if (--$this->transactionLevel) {
            $this->infoLog('transaction(commit ignored)');
            return;
        }

        $this->lazyLoad();

        $this->startTiming();

        $ret = $this->pdo->commit();

        $this->endTiming();

        if ($this->log) {
            $this->infoLog('transaction(commit)');
        }

        return $ret;
    }

    /**
     * Timing utility
     */

    private $startTime = null;
    private $lastTimeDelta = null;

    /**
     * Starts timing if enabled and not already timing.
     *
     * @return void
     */
    private function startTiming()
    {
        if (! $this->time || isset($this->startTime)) {
            return;
        }

        $this->lastTimeDelta = null;
        $this->startTime = microtime(true);
    }

    /**
     * Ends timing if enabled and started.
     *
     * Sets lastTimeDelta to the number of microseconds since startTime, and
     * returns it for convenience.
     *
     * Increments totalTime by lastTimeDelta.
     *
     * @return float
     */
    private function endTiming()
    {
        if (! $this->time || ! isset($this->startTime)) {
            return;
        }

        $this->lastTimeDelta = microtime(true) - $this->startTime;
        $this->startTime = null;

        $this->totalTime += $this->lastTimeDelta;

        return $this->lastTimeDelta;
    }

    /**
     * Logging utility
     */

    private $observing = false;
    private $observers = array();

    const INFO  = 0;
    const ERROR = 1;

    /**
     * Registers an observer of database activity
     *
     * callable is passed 3 paramaters:
     *   $type - one of Database::INFO or Database::ERROR
     *   $what - string description
     *   $data - associative array containing extra information
     *           If type == Database::ERROR, this will contain at least one
     *           item named 'why' containing detail on why $what failed.
     * callable's return doesn't matter
     *
     * Example:
     *   class Mumble
     *   {
     *     public function foo()
     *     {
     *       global $database;
     *       $obsh = $database->observe(array($this, 'onDatabaseEvent'))
     *       $database->spindleAndMutilate();
     *       $database->stopObserving($obsh);
     *     }
     *
     *     public function onDatabaseEvent($type, $what, $data)
     *     {
     *       switch ($type) {
     *         case Database::INFO:
     *           print "database->$what\n";
     *           break;
     *         case Database::ERROR:
     *           print "database->$what failed $data['why']\n";
     *           break;
     *       }
     *     }
     *   }
     *   $bla = new Mumble();
     *   $bla->foo();
     *   // prints:
     *   //   database->spindleAndMutilate...
     *   // or
     *   //   database->spindleAndMutilate failed: the paper tore
     *
     *
     * @param callable mixed a callable observer
     * @see stopObeserving
     * @return mixed returns reference to callable so that the caller can easily
     *         stash it for later removal
     */
    public function &observe($callable)
    {
        if (! is_callable($callable)) {
            throw new InvalidArgumentException("Argument isn't callable");
        }

        if (! in_array($callable, $this->observers)) {
            array_push($this->observers, $callable);
        }

        $this->observing = (bool) count($this->observers);

        return $callable;
    }

    /**
     * Unregisters an observer registered with observe
     *
     * @param observer mixed as in observeu
     * @see observe
     * @return void
     */
    public function stopObserving(&$callable)
    {
        if (! is_callable($callable)) {
            throw new InvalidArgumentException("Argument isn't callable");
        }

        $new = array();
        foreach ($this->observers as &$observer) {
            if ($observer !== $callable) {
                array_push($new, $observer);
            }
        }
        unset($observer);

        $this->observers = $new;
        $this->observing = (bool) count($this->observers);
    }

    /**
     * Notifies all observers of an event
     *
     * @param type int INFO or ERROR
     * @param what string what happened
     * @param data array named extra data
     *
     * @return void
     */
    private function notifyObservers($type, $what, $data)
    {
        foreach ($this->observers as $observer) {
            call_user_func($observer, $type, $what, $data);
        }
    }

    /**
     * Logs an info message through Config.
     *
     * This method does NOT check the $log flag first, the caller should do that
     * such that caller doesn't spend time building $what just to have it thrown
     * away.
     *
     * @param what string what the caller did, e.g. "query(DELETE FROM table)"
     * @param rows int number of rows affected by what happened (optional)
     *
     * @return void
     */
    private function infoLog($what, $rows=null)
    {
        $this->totalQueries++;

        $parts = array($what);

        if ($this->time && isset($this->lastTimeDelta)) {
            array_push($parts,
                sprintf('time: %.4f seconds', $this->lastTimeDelta)
            );
        }

        if (isset($rows)) {
            array_push($parts, sprintf('rows: %d', $rows));
        }

        if ($this->observing) {
            $data = array(
                'extra' => array_slice($parts, 1)
            );
            $this->notifyObservers(Database::INFO, $what, $data);
        }

        Site::getLog()->info(
            # Usually looks something like:
            #   Database::action(details), time: n.mmmm seconds, rows: n
            'Database::'.implode(', ', $parts),
            4 # try to pin it on Database's consumer
        );
    }

    /**
     * Returns a what clause for use with other logging functions, such as:
     *
     * @param action string single word like 'execute' or 'prepare'
     * @param detail mixed detail of what action did, if not a string will be
     * passed throuh json_encode
     *
     * @return string "action(detail)"
     */
    private function whatStatement($action, $detail)
    {
        if (! is_string($detail)) {
            if (function_exists('json_encode')) {
                $detail = json_encode($detail);
            } else {
                // TODO fake json since this should be shallow
                $detail = print_r($detail, true);
            }
        }

        return "$action($detail)";
    }
}

?>
