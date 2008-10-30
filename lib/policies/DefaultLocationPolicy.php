<?php

class DefaultLocationPolicy implements ILocationPolicy 
{
    public static function templates()
    {
        global $config;
        
        return "$config->absPath/SITE/writable/cache/templates";
    }
    
    public static function logs()
    {
        global $config;
        
        return "$config->absPath/SITE/writable/cache/logs";
    }
        
    private function __construct() { }   
}

?>