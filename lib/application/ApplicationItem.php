<?php

class ApplicationItem extends DatabaseObject
{
    public $path;
    public $class;
    public $isLifecycle = false;
    public $isAdministration = false;
    
    public static function classExists($class)
    {
    	global $database;
    	
    	$sql = 'SELECT COUNT(*) FROM application_map WHERE `class` = ?';
    	$database->executef($sql, $class);
        return ($database->getScalarValue() > 0);
    }

    /**
     * Finds all the life cycle contributors
     *
     * @return array of ILifeCycle objects
     */
    public static function getLifeCycleObjects()
    {
        global $database;

        $sql = 'SELECT `class` FROM application_map WHERE is_lifecycle = 1';
        return $database->queryForResultValues($sql);
    }

    /**
     * Finds all the administration providers in the system
     *
     * @return array of providers
     */
    public static function getAdministrations()
    {
        global $database;

        $sql = 'SELECT `class` FROM application_map WHERE is_administration = 1';
        return $database->queryForResultValues($sql);
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