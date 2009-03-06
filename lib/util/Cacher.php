<?php

/**
 * Cacher class definition
 *
 * PHP version 5
 *
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
 *
 * @category     util
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * Defines standard methods the system may use to ascertain
 * information about this objects subclass.
 *
 * @static
 * @category     Cache
 * @package        Utils
 */
class Cacher
{
    /**
     * Constructor; Private
     *
     * @access private
     * @return Cacher a new instance
     */
    private function __construct()
    {

    }

    /*
     * @TODO: implement request caching
     */

    /**
     * Writes the given component's buffer to the disk, for later retreival.
     *
     * @static
     * @access public
     * @param ICacheable $obj the cachable object
     * @param object $data the structure to serialize to the cache
     * @return boolean true if cached, false otherwise
     */
    public static function writeCache(ICacheable &$obj, &$data)
    {
        return false;
        if (!$obj->isCacheable()) {
            return false;
        }

        $path = Cacher::getCacheDirectory($obj);
        if (!$path) {
            return false;
        }

        $file = "$path/cache";

        if (!file_put_contents($file, serialize($data))) {
            Site::getLog()->error("couldn't create file $file");
            return false;
        }

        Site::getLog()->info("wrote cache for $file");

        return true;
    }

    /**
     * Returns the date in unix time our copy of this component/action cache.
     *
     * @static
     * @access public
     * @param ICacheable $obj the ICacheable object
     * @return int a time in unix format if we have a copy, false otherwise
     */
    public static function isCached(ICacheable &$obj)
    {
        return false;

        if (!$obj->isCacheable()) {
            return false;
        }

        $path = Cacher::getCacheDirectory($obj);
        if (!$path) {
            return false;
        }

        $file = "$path/cache";

        return (file_exists($file) ? filemtime($file) : false);
    }

    /**
     * Uses the cache, if available.
     *
     * @static
     * @access public
     * @param ICacheable $obj the object we're operating on
     * @return string the cached data if available, false otherwise
     */
    public static function useCache(ICacheable &$obj)
    {
        return false;

        if (!($cacheModifiedTime = Cacher::isCached($obj))) {
            return false;
        }

        if (!$obj->useCache($cacheModifiedTime)) {
            return false;
        }

        $path = Cacher::getCacheDirectory($obj) . '/cache';
        if (!($buffer = file_get_contents($path))) {
            return false;
        }

        Site::getLog()->info("using cache for $path");

        return $buffer;
    }

    /**
     * This method is usually called when the current action is a candidate for
     * complete caching, such as non or semi dynamic javascript files or images.
     * It will always set the Last-Modified and Expires headers, and if an
     * If-Modified-Since header was sent, it will return 304 as appropriate.
     * This does require apache 2.0 handler compliation to function.
     *
     * @param int $ourModificationTime the most recent modification date of the item you
     * wish to cache
     * @param int $expires the unix time from now you want the item to expire. the default
     * is 86400, or one day from now
     * @return void
     */
    public static function notModified($ourModificationTime, $expires=86400)
    {
        if (!function_exists('apache_request_headers')) {
            return;
        }

        /*
         * @WARNING: this is apache-specific
         */
        $headers = apache_request_headers();
        $ifModifiedSince = (isset($headers['If-Modified-Since'])
                           ? strtotime($headers['If-Modified-Since'])
                           : 0);

        header('Pragma: cache');
        header('Cache-Control: private');
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $ourModificationTime) . " GMT");
        header('Expires: '       . gmdate("D, d M Y H:i:s", (time() + $expires))  . " GMT");

        if (isset($headers['If-Modified-Since'])
            && ($ourModificationTime >= $ifModifiedSince))
        {
            header('HTTP/1.1 304 Not Modified');
            exit(0);
        }
    }

    /**
     * Returns the last update date of the specified table.
     * Note that this is MySQL specific, so if your target db changes,
     * you'll want to modify this accordingly, if a similar
     * facility exists at all.
     *
     * @static
     * @access public
     * @param string $table the name of a table
     * @return int the last update_date of $table
     */
    public static function tableUpdateDate($table)
    {
        global $database;

        // @WARNING: This is mysql-specific
        $sth = $database->query('SHOW TABLE STATUS');
        while ($row = $sth->fetchObject('stdClass')) {
            if ($row->name == $table) {
                return strtotime($row->updateTime);
            }
        }

        return 0;
    }

    /**
     * A convenience method that returns true if the specified
     * table(s) contents were last updated before $time. Note
     * that this is MySQL specific, so if your target db changes,
     * you'll want to modify this accordingly, if a similar
     * facility exists at all.
     *
     * @static
     * @access public
     * @param mixed $tables the name of a table, or an array of
     * table names
     * @param int $time the time argument in unix time
     * @return boolean true if the given table(s) have been
     * modified since $time
     */
    public static function tablesModifiedSince($tables, $time)
    {
        global $database;

        if (!is_array($tables)) {
            $tables = array($tables);
        }

        // @WARNING: This is mysql-specific
        $sth = $database->query('SHOW TABLE STATUS');
        while ($row = $sth->fetchObject('stdClass')) {
            if (in_array($row->name, $tables)) {
                $updateTime = strtotime($row->update_time);
                if ($updateTime < $time) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Builds a standardized directory structure given an object code and action.
     * This takes the values of $_GET and $_POST into account for a truly unique
     * cache per request.
     *
     * @static
     * @access private
     * @param ICacheable $obj the object that implements ICacheable
     * @return string a directory in which to cache for the current request or false if it couldn't
     */
    private static function getCacheDirectory(ICacheable &$obj)
    {
        global $current;

        $params = get_class($obj);
        $request = array_merge($_GET, $_POST);

        foreach ($request as $name => $value) {
            $params .= (is_array($name) ? implode(',', $name) : $name);
            $params .= (is_array($value) ? implode(',', $value) : $value);
        }

        // TODO $params .= ($current->user ? $current->user->id : -1);

        $site = Site::Site();
        $cacheDir = $site->layout->getCacheArea('objects');
        $path = "$cacheDir/objects/".md5($params);
        if (! file_exists($path)) {
            if (! @mkdir($path, 0775, true)) {
                Site::getLog()->error("unable to make dir $path");
                return false;
            }
        }

        return $path;
    }
}

?>
