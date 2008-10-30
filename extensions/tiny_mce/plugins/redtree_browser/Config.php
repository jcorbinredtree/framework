<?php

/**
 * Config class definition
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
 * Contains configuration information 
 */
class Config
{    
    /**
     * This is your database connection information, in a MDB2 DSN format
     *
     * @access public
     * @var string
     */
    public $dsn = 'mysql://redtreedev:redtreesystems@localhost/intermedia';
    public $dbOptions = array();
    
    public $compileDir;    

    /**
     * The absolute uri, such as http://place.com/full/path/to/app.
     * This value is calculated in the constructor.
     *
     * @access public
     * @var string
     */
    public $absUri = null;

    /**
     * The absolute path, such as /var/www/full/path/to/app. This value is
     * calculated as dirname( __FILE__ ), and is what you want 99% of
     * the time.
     *
     * @access public
     * @var string
     */
    public $absPath = null;

    /**
     * Constructor; Sets up auto vars
     *
     * @access public
     * @return Config a new instance
     */
    public function __construct() 
    {
        $this->absPath = dirname(__FILE__);
        $this->absUri = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
        $this->compileDir = "$this->absPath/tmp/";
    }
    
    /*
     * implement the below methods if you wish to log 
     */

    /**
     * Writes a message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function log($message, $frame=2) 
    {

    }

    /**
     * Writes an info message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function info($message, $frame=2) 
    {

    }

    /**
     * Writes a notice message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function notice($message, $frame=2) 
    {

    }

    /**
     * Writes a warning message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function warn($message, $frame=2) 
    {
        throw new Exception($message);
    }

    /**
     * Writes an error message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function error($message, $frame=2) 
    {
        throw new Exception($message);
    }

    /**
     * Writes an alert message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function alert($message, $frame=2) 
    {
        throw new Exception($message);
    }

    /**
     * Writes a fatal message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function fatal($message, $frame=2) 
    {
        throw new Exception($message);
    }
}

?>