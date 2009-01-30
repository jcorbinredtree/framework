<?php

/**
 * DatabaseObject_Test class definition
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

// If called directly from command line
if (realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
    $APP = 'test';
    require_once dirname(__FILE__).'/../../../../index.php';
}

/**
 * This class is meant for testing DatabaseObject, every feature of
 * DatabaseObject should be demonstrated here in some way.
 */

class DatabaseObject_Test extends FrameworkTestCase
{
    private $dbObserverHandle;
    private $db = null;
    public $verbose = false;

    public function setUp()
    {
        // stash a ref to the global for flexibility and convenience
        global $database;
        $this->db =& $database;

        // hook database events
        $this->dbObserverHandle =& $this->db->observe(
            array($this, 'onDatabaseEvent')
        );
    }

    public function tearDown()
    {
        // reverse converse of setUp
        $this->db->stopObserving($this->dbObserverHandle);
        $this->db = null;
    }

    // handler for database events, hooked by setUp
    public function onDatabaseEvent($type, $what, $data)
    {
        switch ($type) {
            case Database::INFO:
                if ($this->verbose) {
                    print "Database activity $what ";
                    if (array_key_exists('extra', $data)) {
                        print implode(' ', $data['extra']);
                    }
                    print "\n";
                }
                if ($this->expecting()) {
                    $this->verify($what);
                } else {
                    $this->fail(
                        "Unexpected database activity: $what"
                    );
                }
                break;
            case Database::ERROR:
                $why = $data['why'];
                $this->fail("Database::$what failed: $why");
                break;
            default:
                $this->fail(
                    "Database notified us of a bogus event type($type)"
                );
                break;
        }
    }

    // Expects to see a string == action(detail)
    public function expectExact($action, $detail, $multiplicity=1, $message='%s')
    {
        $this->expect(
            new EqualExpectation("$action($detail)"),
            $multiplicity, $message
        );
    }

    // Expects to match a string action(pattern)
    public function expectMatch($action, $pattern, $multiplicity=1, $message='%s')
    {
        $this->expect(
            new PatternExpectation("$action\($pattern\)"),
            $multiplicity, $message
        );
    }

    public function testDummy()
    {
        { // Test meta object
            $meta = DatabaseObject_Meta::forClass('DBODummy');
            $table = $meta->getTable();
            $key = $meta->getKey();

            $this->assertEqual('DBODummy', $meta->getClass());
            $this->assertEqual('dbodummy', $table);
            $this->assertEqual('dbodummy_id', $key);

            $colMap = $meta->getColumnMap();
            $this->assertEqual($colMap["aDate"],     "a_date");
            $this->assertEqual($colMap["aDateTime"], "a_date_time");
            $this->assertEqual($colMap["aTime"],     "a_time");
            $this->assertEqual($colMap["mess"],      "mess");
            $this->assertEqual($colMap["id"],        "dbodummy_id");
        }

        $drop = "DROP TABLE IF EXISTS $table";

        $create =
            "CREATE TABLE $table (\n  ".
            implode(",\n  ", DBODummy::$CreateSpec).
            "\n)";

        $this->expectExact('perform', $drop);
        $this->expectExact('perform', $create);

        $this->db->perform($drop);
        $this->db->perform($create);

        $dummy = new DBODummy();

        $fieldSet = $dummy->getFieldSetSQL();
        $this->assertEqual($fieldSet, implode(', ', array(
            '`a_date`=:a_date',
            '`a_date_time`=:a_date_time',
            '`a_time`=:a_time',
            '`mess`=:mess'
        )));

        { // Create a dummy
            $this->populate($dummy);
            // TODO populate really should be a method on the object or its meta

            $this->expectExact('lock', "LOCK TABLES $table WRITE");
            $this->expectExact('prepare',
                "INSERT INTO `$table` SET $fieldSet"
            );
            $this->expectExact('execute', json_encode(array(
                ":a_date"      => date('Y-m-d', (int) $dummy->aDate),
                ":a_date_time" => date('Y-m-d H:i:s', (int) $dummy->aDateTime),
                ":a_time"      => Database::formatTime($dummy->aTime),
                ":mess"        => $dummy->mess
            )));
            $this->expectExact('lastInsertId', 1); // The table is virgin
            $this->expectExact('unlock', 'UNLOCK TABLES');

            if (! $dummy->create()) {
                $this->fail("Failed to create dummy");
                return;
            } elseif ($this->verbose) {
                print "Dummy $dummy->id updated\n";
            }
        }

        { // Change the dummy and save
            $this->populate($dummy);
            $this->expectExact('prepare',
                "UPDATE `$table` SET $fieldSet WHERE `$key` = :$key LIMIT 1"
            );
            $this->expectExact('execute', json_encode(array(
                ":a_date"      => date('Y-m-d', (int) $dummy->aDate),
                ":a_date_time" => date('Y-m-d H:i:s', (int) $dummy->aDateTime),
                ":a_time"      => Database::formatTime($dummy->aTime),
                ":mess"        => $dummy->mess,
                ":dbodummy_id" => $dummy->id
            )));

            if (! $dummy->update()) {
                $this->fail("Failed to update dummy");
                return;
            } elseif ($this->verbose) {
                print "Dummy $dummy->id updated\n";
            }
        }

        { // Fetch a dummy
            $dummyId = $dummy->id;
            $dummyData = array(
                $dummy->aDate,
                $dummy->aDateTime,
                $dummy->aTime,
                $dummy->mess
            );
            $dummy = null;

            $pfx = "`$table`.";
            $this->expectExact('prepare',
                'SELECT '.implode(', ', array(
                    "UNIX_TIMESTAMP($pfx`a_date`) AS `a_date`",
                    "UNIX_TIMESTAMP($pfx`a_date_time`) AS `a_date_time`",
                    "TIME_TO_SEC($pfx`a_time`) AS `a_time`",
                    "$pfx`mess` AS `mess`"
                )).
                " FROM `$table` WHERE `$key` = ? LIMIT 1"
            );
            $this->expectExact('executef', json_encode(array($dummyId)));

            $dummy = new $table();
            if (! $dummy->fetch($dummyId)) {
                $this->fail("Failed to load dummy");
            } elseif ($this->verbose) {
                print "Loaded dummy $dummyId";
            }

            $this->assertEqual($dummyId, $dummy->id);
            $this->assertEqual($dummyData[0], $dummy->aDate);
            $this->assertEqual($dummyData[1], $dummy->aDateTime);
            $this->assertEqual($dummyData[2], $dummy->aTime);
            $this->assertEqual($dummyData[3], $dummy->mess);
        }

        { // Kill a dummy
            $this->expectExact('prepare',
                "DELETE FROM `$table` WHERE `$key` = ?"
            );
            $this->expectExact('executef', json_encode(array($dummyId)));

            if (! $dummy->delete()) {
                $this->fail("Failed to delete dummy");
            } elseif ($this->verbose) {
                print "Deleted dummy $dummyId\n";
            }

            $this->assertEqual(-1, $dummy->id);

            // Delete only clears id
            $this->assertEqual($dummyData[0], $dummy->aDate);
            $this->assertEqual($dummyData[1], $dummy->aDateTime);
            $this->assertEqual($dummyData[2], $dummy->aTime);
            $this->assertEqual($dummyData[3], $dummy->mess);

            // TODO once wipe added, values should be nullifed
        }

        // TODO update a non-existent id should fail
        // TODO fetch non-existent id should fail
    }
}

class DBODummy extends DatabaseObject
{
    /**
     * Definitions
     */
    static public $table = 'dbodummy';
    static public $key = 'dbodummy_id';
    static public $CreateSpec = array(
        'dbodummy_id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
        'mess VARCHAR(255)',
        'a_date DATE',
        'a_time TIME',
        'a_date_time DATETIME'
    );


    /**
     * Fields
     */
    public $mess;
    public $aDate;
    public $aTime;
    public $aDateTime;
}

?>
