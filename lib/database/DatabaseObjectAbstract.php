<?php

/**
 * Database Object Abstract
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

require_once 'lib/database/DatabaseObjectAbstractMeta.php';

/**
 * Common base class for DatabaseObject and DatabaseObjectLink
 */
abstract class DatabaseObjectAbstract
{
    /**
     * The subject table
     *
     * @var string
     */
    static public $table = null;

    /**
     * The database that was selected upon instantiation
     */
    protected $_db;

    public function __construct()
    {
        $database = Site::getModule('Database');
        $this->_db = $database->getSelected();
    }

    public function getDatabase()
    {
        $database = Site::getModule('Database');
        $database->select($this->_db);
        return $database;
    }

    abstract public function meta();
}

# vim:set sw=4 ts=4 expandtab:
?>
