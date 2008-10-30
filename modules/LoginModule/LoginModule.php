<?php

class LoginModule extends Module
{
    public function onDisplay($position)
    {
        $this->viewTemplate('view/loginform.xml');
    }
    
    public function isCacheable() 
    { 
        return true; 
    }
    
    public function useCache($time) 
    { 
        global $config;
        
        return (filemtime("$config->absPath/modules/LoginModule/view/loginform.xml") <= $time); 
    }
}

?>