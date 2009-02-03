<?php
/**
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
 */

require_once dirname(__FILE__) . "/../../../bootstrap.php";

class CMSReplicationObjectTest extends FrameworkTestCase
{
    public function testGetClone()
    {
        $obj = new CROTestObjectClass();
        $obj->keyColumn = $obj->id = 2;
        $obj->columnA = 'repli';
        $obj->columnB = 'cate';

        $obj2 = $obj->getClone();

        $this->assertTrue(($obj2->keyColumn < 0), 'key column is empty');
        $this->assertTrue(($obj2->id < 0), 'id is not set');
        $this->assertTrue(($obj2->columnA == 'repli'), 'column a intact');
        $this->assertTrue(($obj2->columnB == 'cate'), 'column b intact');
    }
}

class CROTestObjectClass extends CMSReplicationObject
{
    public $keyColumn;
    public $columnA;
    public $columnB;

    public function __construct()
    {
        $this->table = 'fake_table';
        $this->key = 'key_column';
    }
}

?>
