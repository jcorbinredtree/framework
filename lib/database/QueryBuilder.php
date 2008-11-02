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
    private $joins = array();
    private $wheres = array();
    private $orders = array();
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

    public function join(IDatabaseObject &$dbo2)
    {
        $dbo = $this->dbo;
        $sql = "INNER JOIN `$dbo2->table` ON `$dbo2->table`.`$dbo->key` = ";
        $sql .= "`$dbo->table`.`$dbo->key`";

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
            $sql .= "`$dbo->table`.`$dbo->key`," . $dbo->getColumnsSQL() . ' ';
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