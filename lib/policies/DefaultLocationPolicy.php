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
        
        return "$config->absPath/SITE/writable/cache/logs";
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
            $config->setLog(Log::singleton('file', $logDir . '/' . date('Y-m-d') . '.log', '', null, $level));
        }
        else {
            $config->setLog(Log::singleton('null'));
        }                
    }
}

?>