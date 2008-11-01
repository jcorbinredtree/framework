<?php

class ApplicationData
{   
    private static $dirty = false;
    private static $data;    
    
    private function __construct() { }
    
    public static function initialize()
    {
        global $config;
                
        /*
         * try to load our data from the session
         */
        ApplicationData::$data = Session::get(AppConstants::APPLICATION_DATA_KEY);
        if (ApplicationData::$data && is_array(ApplicationData::$data)) {
            return;
        }

        /*
         * load the data from our application file
         */
        $file = ApplicationData::getDataFile();
        if (!file_exists($file)) {
            $config->warn('application file not found, rebuilding');
            
            file_put_contents($file, serialize(array()));
        }   

        ApplicationData::$data = unserialize(file_get_contents($file));
    }
    
    public static function unintialize()
    {
        if (!ApplicationData::$dirty) {
            return;
        }
        
        $file = ApplicationData::getDataFile();        
        $data = ApplicationData::$data;
        $data = serialize($data);
        
        file_put_contents($file, $data);        
    }
    
    public static function getDataFile()
    {
        $policy = PolicyManager::getInstance();
        $dir = $policy->getCacheDir();
        if (!is_writable($dir)) {
            throw new Exception("$dir is not writable");
        }
        
        return "$dir/application.data";        
    }

    /**
     * Adds data to an application-level store
     *
     * @param string $key the key name for this data
     * @param mixed $d the data you wish to store. this will be serialized, so you can pass anything
     * @return void
     */
    public static function set($key, &$d)
    {
        ApplicationData::$data[$key] = $d;
        Session::set(AppConstants::APPLICATION_DATA_KEY, ApplicationData::$data);
                
        ApplicationData::$dirty = true;        
    }

    /**
     * Retrieves the data associated with $key. 
     *
     * @param string $key the key name for this data
     * @return mixed this value will be set to null if no data is found
     */
    public static function get($key)
    {
        if (!ApplicationData::$data) {
            return null;
        }
        
        if (!array_key_exists($key, ApplicationData::$data)) {
            return null;
        }
        
        return ApplicationData::$data[$key];
    }
    
    public static function addClassEntry($className, $file)
    {
       $map =& ApplicationData::get(AppConstants::CLASSMAP_KEY);
       if (!is_array($map)) {
           $map = array();
       }
       
       $map[$className] = $file;
       ApplicationData::$data[AppConstants::CLASSMAP_KEY] =& $map;
       ApplicationData::$dirty = true;
    }

    public static function getClassLocation($className)
    {
       $map =& ApplicationData::get(AppConstants::CLASSMAP_KEY);
       if (!is_array($map)) {
           $map = array();
       }

       return array_key_exists($className, $map) ? $map[$className] : null;
    }
}

?>