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

        global $config;

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
            $config->fatal("Unable to parse the dsn: $this->dsn");
            die('Unable to parse dsn');
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
        $this->parsedDSN = Database::parseDSN($this->dsn);
    }

    /**
     * Connects to the database if not already connected.
     *
     * @return void
     */
    private function lazyLoad()
    {
        global $config;

        if ($this->pdo) {
            return;
        }

        try {
            $dsn =& $this->parsedDSN;

            if (! is_array($this->dbOptions)) {
                $this->dbOptions = array();
            }

            $this->dbOptions[PDO::ATTR_PERSISTENT] = true;
            $this->dbOptions[PDO::ATTR_CASE] = PDO::CASE_NATURAL;
            $this->dbOptions[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $this->dbOptions[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;

            $this->pdo = new PDO(
                "$dsn->driver:host=$dsn->host;dbname=$dsn->db",
                $dsn->user,
                $dsn->password,
                $this->dbOptions
            );
        } catch (PDOException $e) {
            $config->fatal($e->getMessage());
            die("Unable to connect to the database");
        }
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

        $this->lazyLoad();

        if ($this->statement) {
            $arr = $this->statement->errorInfo();
        } else {
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

        $this->lazyLoad();

        /*
         * @WARNING: this is mysql-specific
         */
        try {
            $sql = "SELECT `$field` FROM `$table` LIMIT 1";
            $sth = $this->pdo->query($sql);
            if (! $sth) {
                throw new Exception($this->error());
            }
            $desc = $sth->getColumnMeta(0);
            $out = array($desc);
            $out[0]['notnull'] = in_array('not_null', $desc);
            return $out;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Begins a transaction
     *
     * @see http://us.php.net/manual/en/pdo.begintransaction.php
     * @return boolean true on success
     */
    public function transaction()
    {
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

        $this->lazyLoad();

        try {
            $this->startTiming();

            $rows = $this->pdo->exec($sql);

            $this->endTiming();
        } catch (PDOException $e) {
            $this->endTiming();

            $this->errorLog("$type($sql)", $e->getMessage());
            return false;
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
                    if (null !== $val) {
                        $params[":$key"] = $val;
                    }
                }
            }
            else {
                $params =& $param;
            }
        }

        try {
            $this->startTiming();

            $result = false;

            if ($params) {
                $result = $this->statement->execute($params);
            } else {
                $result = $this->statement->execute();
            }

            if (!$result) {
                $this->endTiming();

                $what = $this->whatStatement('execute', null, $params);
                $this->errorLog($what, $this->error());
                return false;
            }

            $this->endTiming();
        } catch (PDOException $e) {
            $this->endTiming();

            $what = $this->whatStatement('execute', null, $params);
            $this->errorLog($what, $e->getMessage());
            return false;
        }

        if ($this->log) {
            $what = $this->whatStatement('execute', null, $params);
            $this->infoLog($what, $this->count());
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
        // Temporarily disable logging
        $logging = $this->log;
        $this->log = false;

        $this->startTiming();

        if (!$this->prepare($sqlf)) {
            $this->log = $logging;
            return false;
        }

        /*
         * bind function args. if an array was passed, then flatten it
         */
        {
            $args = func_get_args();
            $index = 1;
            for ($i = 1; $i < count($args); $i++) {
                $arg = $args[$i];
                if (is_array($arg)) {
                    foreach ($arg as $a) {
                        $this->bindValue($index++, $a);
                    }

                    break;
                }

                $this->bindValue($index++, $arg);
            }
        }

        $res = $this->execute();

        $this->endTiming();

        // Restore logging
        $this->log = $logging;

        if ($this->log) {
            $what = $this->whatStatement('executef', $sqlf, $args);
            $this->infoLog($what, $this->count());
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

        $this->lazyLoad();

        try {
            $this->startTiming();

            $this->statement = $this->pdo->prepare($sql);

            $this->endTiming();
        }
        catch (PDOException $e) {
            $this->endTiming();

            $what = $this->whatStatement('prepare', $sql);
            $this->errorLog($what, $e->getMessage());
            return false;
        }

        if ($this->log) {
            $this->infoLog($this->whatStatement('prepare', $sql));
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

        $this->lazyLoad();

        try {
            $this->startTiming();

            $this->statement = $this->pdo->query($sql);

            $this->endTiming();
        }
        catch (PDOException $e) {
            $this->endTiming();

            $what = $this->whatStatement('query', $sql);
            $this->errorLog($what, $e->getMessage());
            return false;
        }

        if ($this->log) {
            $what = $this->whatStatement('query', $sql);
            $this->infoLog($what, $this->count());
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
     * @return array an array of object rows, or null on error
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
        }
        else {
            $args = array();
        }

        //global $config; $config->warn("1.) $sql");
        if ($this->executef($sql, $args)) {
           return $this->getResultObjects($type);
        }

        return null;
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
            if (($obj instanceof IDatabaseObject) && (isset($row[$obj->key]))) {
                $row['id'] = $row[$obj->key];
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
        global $config;

        $sth = array_pop($this->statementStack);
        $this->statement =& end($this->statementStack);
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
        global $config;

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

        $config->info(
            # Usually looks something like:
            #   Database::action(details), time: n.mmmm seconds, rows: n
            'Database::'.implode(', ', $parts),
            4 # try to pin it on Database's consumer
        );
    }

    /**
     * Logs an error message through Config.
     *
     * @param what string what the caller did that went badly
     * @param why string why it didn't work out (optional)
     */
    private function errorLog($what, $why=null)
    {
        global $config;

        $parts = array($what);

        array_push($parts, "failed: $why");

        if ($this->time && isset($this->lastTimeDelta)) {
            array_push($parts,
                sprintf('time: %.4f seconds', $this->lastTimeDelta)
            );
        }

        $config->error(
            # Usually looks something like:
            #   Database::action(details), failed: it didn't work out, time: n.mmmm seconds
            'Database::'.implode(', ', $parts),
            4 # try to pin it on Database's consumer
        );
    }

    /**
     * Returns a what clause for use with other logging functions, such as:
     *
     * @param action string single word like 'execute' or 'prepare'
     * @param sql string the statement, if null will attempt to get from $statement
     * @param params string|array (optional) if an array is given, will be
     * passed through json_encode (or print_r if not available)
     *
     * @return string "action(sql|params)"
     */
    private function whatStatement($action, $sql, $params=null)
    {
        if (! isset($sql)) {
            if (isset($this->statement)) {
                $sql = $this->statement->queryString;
            } else {
                $sql = '{no statement}';
            }
        }

        $what = "$action($sql";
        if (isset($params)) {
            if (is_array($params) && count($params)) {
                if (function_exists('json_encode')) {
                    $params = json_encode($params);
                } else {
                    $params = print_r($params, true);
                }
            }
            if ($params) {
                $what .= "|$params";
            }
        }
        $what .= ')';

        return $what;
    }
}

?>
