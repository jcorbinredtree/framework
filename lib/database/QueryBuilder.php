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

class QueryBuilder
{
    /**
     * Our base database object
     *
     * @var IDatabaseObject
     */
    private $dbo;

    private $type;
    private $fields = '';
    private $joins = array();
    private $wheres = array();
    private $orders = array();
    private $groupBys = array();
    private $limit = '';

    /**
     * Holds the pager for this query
     *
     * @var Pager
     */
    private $pager = null;

    public function __construct(IDatabaseObject &$dbo, $type='SELECT')
    {
        $this->dbo = $dbo;
        $this->type = $type;
    }

    public function join(IDatabaseObject &$dboB, $keyA=null, $keyB=null)
    {
        return $this->joinObject($this->dbo, $dboB, $keyA, $keyB);
    }

    public function joinObject(IDatabaseObject &$dboA, IDatabaseObject &$dboB, $keyA=null, $keyB=null)
    {
        $metaA = $dboA->meta();
        $metaB = $dboB->meta();
        $tableA = $metaA->getTable();
        $tableB = $metaB->getTable();

        if (!$keyA) {
            $keyA = $metaA->getKey();
        }

        if (!$keyB) {
            $keyB = $keyA;
        }

        $sql = "INNER JOIN `$tableB` ON `$tableB`.`$keyB` = `$tableA`.`$keyA`";

        array_push($this->joins, $sql);
    }

    public function where($sql)
    {
        array_push($this->wheres, $sql);
    }

    public function order($field)
    {
        array_push($this->orders, $field);
    }

    public function group($field)
    {
        array_push($this->groupBys, $field);
    }

    public function setFields($f)
    {
        $this->fields = $f;
    }

    public function setLimit($l)
    {
        $this->limit = $l;
    }

    public function setPager(Pager &$pager)
    {
        $this->pager = $pager;
    }

    public function __toString()
    {
        $dbo =& $this->dbo;
        $meta = $dbo->meta();
        $table = $meta->getTable();
        $key = $meta->getKey();
        $sql = "$this->type ";
        $meat = '';

        if ($this->type == 'SELECT') {
            if ($this->fields) {
                $sql .= "$this->fields ";
            }
            else {
                $sql .= "`$table`.`$key`," . $dbo->getColumnsSQL() . ' ';
            }
        }
        elseif ($this->fields) {
            throw new IllegalArgumentException('field specification is incompatible with non-select queries');
        }
        elseif ($this->pager) {
            throw new IllegalArgumentException('pager is not compatible with non-select queries');
        }

        if ($this->pager && $this->limit) {
            throw new IllegalArgumentException('pager is mutually exclusive with limit');
        }

        $meat = "FROM `$table` ";
        foreach ($this->joins as $join) {
            $meat .= "$join ";
        }

        for ($i = 0; $i < count($this->wheres); $i++) {
            $where = $this->wheres[$i];
            if (!$i) {
                $where = "WHERE $where";
            }
            else {
                $where = "AND $where ";
            }

            $meat .= "$where ";
        }

        for ($i = 0; $i < count($this->groupBys); $i++) {
            $group = $this->groupBys[$i];
            if (!$i) {
                $group = "GROUP BY $group";
            }
            else {
                $group = ",$group";
            }

            $meat .= "$group ";
        }

        if ($this->pager && (null === $this->pager->results)) {
            global $database;

            $lsql = "SELECT COUNT(*) $meat";
            if ($database->query($lsql)) {
                $this->pager->setResults($database->getScalarValue());
            }
        }

        $sql .= $meat;

        for ($i = 0; $i < count($this->orders); $i++) {
            $order = $this->orders[$i];
            if (!$i) {
                $order = "ORDER BY $order";
            }
            else {
                $order = ",$order";
            }

            $sql .= "$order ";
        }

        if ($this->pager) {
            $sql .= $this->pager->getLimit();
        }
        elseif ($this->limit) {
            $sql .= "LIMIT $this->limit";
        }

        return $sql;
    }
}

?>
