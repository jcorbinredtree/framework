<?php

class FrameworkTestCase extends UnitTestCase
{
    protected function populate(DatabaseObject &$obj)
    {
        global $database;
        
        $fields = $obj->getFields();
        foreach ($fields as $property => $field) {
            $def = $database->getTableFieldDefinition($obj->table, $field);  
            if (!$def) {
                continue;
            }

            $type = strtolower(Params::generic($def[0], 'native_type'));            
            switch ($type) {
                case 'int':
                case 'integer':
                case 'float':
                case 'currency':
                case 'decimal':
                case 'double':
                case 'real':
                case 'tinyint':
                case 'short':
                case 'long':                    
                    $obj->$property = mt_rand(0, 100);
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $obj->$property = time() + (mt_rand(0, (86400 * 7)) * (mt_rand(0, 1) ? 1 : -1)); 
                    break;
                default:
                    $obj->$property = uniqid();
                    break;
            }
        }
    }
}

?>