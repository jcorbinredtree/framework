<?php

class ApplicationItem extends DatabaseObject
{
    public $path;
    public $class;
    
    public static function classExists($class)
    {
    	global $database;
    	
    	$sql = 'SELECT COUNT(*) FROM application_map WHERE `class` = ?';
    	$database->executef($sql, $class);
        return ($database->getScalarValue() > 0);
    }    
    
    public static function getMap()
    {
    	global $database;
    	
        $sql = 'SELECT * FROM application_map';

        $objs = $database->queryForResultObjects($sql, 'ApplicationItem');
        $map = array();
        foreach ($objs as $obj) {
            $map[$obj->class] = $obj;
        }   

        return $map;
    }
    
    public function __construct()
    {
        $this->key = 'map_id';
        $this->table = 'application_map';
    }
    
    public function getFile()
    {
        global $config;
        
        return "$config->absPath/$this->path"; 
    }
}

?>