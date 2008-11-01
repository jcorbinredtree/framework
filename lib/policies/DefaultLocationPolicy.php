<?php

class DefaultLocationPolicy implements ILocationPolicy 
{
    public function getTemplatesDir()
    {
        global $config;
        
        return "$config->absPath/SITE/writable/cache/templates";
    }
    
    public function getLogsDir()
    {
        global $config;
        
        return "$config->absPath/SITE/writable/logs";
    }
    
    /**
     * Gets the location of the cache directory.
     * This directory should be writable.
     *
     * @return string the location of the cache directory.
     */
    public function getCacheDir()
    {
        global $config;
        
        return "$config->absPath/SITE/writable/cache";            
    }
    
    public function logs()
    {
        global $config;
        
        $logDir = DefaultLocationPolicy::getLogsDir();
        if (file_exists($logDir)) {
            if (!is_writable($logDir)) {
                throw new Exception('The log directory exists, but is not writable');
            }

            $level = ($config->isDebugMode() ? PEAR_LOG_DEBUG : PEAR_LOG_WARNING);
            $test = ($config->isTestMode() ? '.test' : '');
            $config->setLog(Log::singleton('file', $logDir . '/' . date('Y-m-d') . "$test.log", '', null, $level));
        }
        else {
            $config->setLog(Log::singleton('null'));
        }                
    }
}

?>