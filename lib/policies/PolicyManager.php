<?php

class PolicyManager
{
    public function get($policy)
    {
        global $config;
        
        switch ($policy) {
            case 'location':
                return $config->getLocationPolicy();
                break;
            default:
                return null;
        }
    }
    
    public static function getTemplatesPolicy()
    {
        $policy = PolicyManager::get('location');
        return call_user_func(array($policy, 'templates'));
    }
    
    public static function getLogsPolicy()
    {
        $policy = PolicyManager::get('location');
        return call_user_func(array($policy, 'logs'));
    }
    
    private function __construct() {}
}

?>