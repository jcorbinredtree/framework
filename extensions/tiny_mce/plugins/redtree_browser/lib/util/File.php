<?php

/**
 * File class definition
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
 * @category     Util
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Defines standard methods the system may use to ascertain 
 * information about this objects subclass.
 *
 * @static
 * @category     File
 * @package      Utils
 */
class File
{
    /**
     * Descends depth-first into directories, calling $closure
     * for each *FILE*
     *
     * @param mixed $closure a user function, as defined by call_user_func
     * @param string $path the starting directory
     * @return void
     */
    public static function find($closure, $path)
    {
        if (!is_dir($path)) {
            call_user_func($closure, $path);
            return;
        }
        
        $fh = opendir($path);
            
        while (false !== ($file = readdir($fh))) {        
            if ($file[0] == '.') {
                continue;
            }

            File::find($closure, "$path/$file");
        }
            
        closedir($fh);        
    }
    
    /**
     * Gets from the $_FILE superglobal, based on $key, the binary
     * content that was uploaded
     *
     * @param string $key the key to use from $_FILE
     * @return string the binary from the file
     */
    public static function getUploadedContent($key)
    {
        if (!isset($_FILES[$key])) {
            return null;
        }
        
        $tmp_name = $_FILES[$key]['tmp_name'];
        $fname = $_FILES[$key]['name'];
        $size = $_FILES[$key]['size'];
        $data = null;

        if (!file_exists($tmp_name)) {
            return null;
        }
        
        if (!is_uploaded_file($tmp_name)) {
            return null;
        }
                
        return file_get_contents($tmp_name);
    }
}

?>
