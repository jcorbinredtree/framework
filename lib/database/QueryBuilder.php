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
    
    public function limit($l)
    {
        $this->limit = $l;
    }
    
    public function __toString()
    {        
        $dbo =& $this->dbo;
        $sql = "$this->type ";
        
        if ($this->type == 'SELECT') {
            $sql .= "`$dbo->table`.`$dbo->key`," . $dbo->getColumnsSQL() . ' ';
        }
        
        $sql .= "FROM `$dbo->table` ";
        foreach ($this->joins as $join) {
            $sql .= "$join ";
        }
        
        for ($i = 0; $i < count($this->wheres); $i++) {
            $where = $this->wheres[$i];
            if (!$i) {
                $where = "WHERE $where";
            }
            else {
                $where = "AND $where ";                
            }
            
            $sql .= "$where ";
        }
        
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
        
        if ($this->limit) {
            $sql .= "LIMIT $this->limit";
        }
        
        return $sql;
    }
}

?>