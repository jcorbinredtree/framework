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

        // try to load our data from the session
        self::$data = Session::get(self::$SessionKey);
        if (self::$data && is_array(self::$data)) {
            return;
        }

        // load the data from our application file
        $file = self::getDataFile();
        if (file_exists($file)) {
            self::$data = unserialize(file_get_contents($file));
        } else {
            self::$data = array();
            self::$dirty = true;
        }
    }

    public static function unintialize()
    {
        if (!self::$dirty) {
            return;
        }

        $file = self::getDataFile();
        $data = self::$data;
        $data = serialize($data);

        file_put_contents($file, $data);
    }

    public static function getDataFile()
    {
        return Site::Site()->layout->getCacheArea().'/application.data';
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
        self::$data[$key] = $d;
        Session::set(self::$SessionKey, self::$data);

        self::$dirty = true;
    }

    /**
     * Retrieves the data associated with $key.
     *
     * @param string $key the key name for this data
     * @return mixed this value will be set to null if no data is found
     */
    public static function get($key)
    {
        if (!self::$data) {
            return null;
        }

        if (!array_key_exists($key, self::$data)) {
            return null;
        }

        return self::$data[$key];
    }

    public static function addClassEntry($className, $file)
    {
       $map =& self::get('applicationclassmap');
       if (!is_array($map)) {
           $map = array();
       }

       $map[$className] = $file;
       self::$data['applicationclassmap'] =& $map;
       self::$dirty = true;
    }

    public static function getClassLocation($className)
    {
       $map =& self::get('applicationclassmap');
       if (!is_array($map)) {
           $map = array();
       }

       return array_key_exists($className, $map) ? $map[$className] : null;
    }
}

?>
