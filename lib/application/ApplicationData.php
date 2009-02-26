<?php
/**
 * LICENSE: The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 *
 * The Original Code is Red Tree Systems Code.
 *
 * The Initial Developer of the Original Code is Red Tree Systems, LLC. All Rights Reserved.
 */

class ApplicationData
{
    /**
     * The key used to store the application data
     */
    public static $SessionKey = '__application_data';

    private static $dirty = false;
    private static $data;

    private function __construct() { }

    public static function initialize()
    {
        global $config;

        /*
         * try to load our data from the session
         */
        ApplicationData::$data = Session::get(self::$SessionKey);
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
            if (is_dir($dir)) {
                throw new Exception("$dir is not writable");
            }

            ob_start();
            if (! mkdir($dir, 0777, true)) {
                $mess = ob_get_clean();
                throw new Exception("Could not create $dir: $mess");
            } else {
                ob_end_clean();
            }
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
        Session::set(self::$SessionKey, ApplicationData::$data);

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
       $map =& ApplicationData::get('applicationclassmap');
       if (!is_array($map)) {
           $map = array();
       }

       $map[$className] = $file;
       ApplicationData::$data['applicationclassmap'] =& $map;
       ApplicationData::$dirty = true;
    }

    public static function getClassLocation($className)
    {
       $map =& ApplicationData::get('applicationclassmap');
       if (!is_array($map)) {
           $map = array();
       }

       return array_key_exists($className, $map) ? $map[$className] : null;
    }
}

?>
