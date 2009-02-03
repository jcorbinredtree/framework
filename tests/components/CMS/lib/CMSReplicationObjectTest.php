<?php

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
