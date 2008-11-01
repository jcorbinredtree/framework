<?php

class QueryBuilder
{
    /**
     * Our base database object
     *
     * @var IDatabaseObject
     */
    private $dbo;
    
    private $joins = array();
    private $wheres = array();    
    private $orders = array();    
    
    public function __construct(IDatabaseObject &$dbo)
    {
        $this->dbo = $dbo;   
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
    
    public function __toString()
    {        
        throw new NotImplementedException();
    }
}

?>