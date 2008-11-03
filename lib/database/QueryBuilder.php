<?php

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

    public function join(IDatabaseObject &$dbo2, $keyA=null, $keyB=null)
    {
        $dbo = $this->dbo;

        if (!$keyA) {
            $keyA = $dbo->key;
        }

        if (!$keyB) {
            $keyB = $keyA;
        }

        $sql = "INNER JOIN `$dbo2->table` ON `$dbo2->table`.`$keyB` = ";
        $sql .= "`$dbo->table`.`$keyA`";

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
        $sql = "$this->type ";
        $meat = '';

        if ($this->type == 'SELECT') {
            if ($this->fields) {
                $sql .= "$this->fields ";
            }
            else {
                $sql .= "`$dbo->table`.`$dbo->key`," . $dbo->getColumnsSQL() . ' ';
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

        $meat = "FROM `$dbo->table` ";
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