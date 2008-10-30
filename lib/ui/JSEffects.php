<?php

/**
 * JSEffects class definition
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
 * @category     UI
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
 * @category     UI
 * @package        Utils
 */
class JSEffects
{    
    /**
     * Determines whether we have loaded the editor js to the browser or not.
     *
     * @static 
     * @access private
     * @var boolean
     */    
    private static $loadedEditor = false;
    
    /**
     * Determines whether we have loaded the editor to the browser or not.
     *
     * @static 
     * @access private
     * @var boolean
     */    
    private static $loadedEditorHead = false;

    /**
     * Constructor; Private
     * 
     * @access private
     * @return JSEffects new instance
     */
    private function __construct()
    {

    }


    /**
     * Loads the editor src
     *
     * @static 
     * @access public
     * @return array a single-element array specifying the
     * editor source location
     */
    public static function LoadEditor()
    {
        global $config;

        if (JSEffects::$loadedEditor) {
            return array();
        }

        JSEffects::$loadedEditor = true;

        return array(
            $config->absUri . '/extensions/tiny_mce/tiny_mce.js'         
        );
    }

    /**
     * Initializes the editor. This is usually used by
     * putting the return value in the <head>.
     *
     * @static 
     * @access public
     * @param array $options an associate array of options to pass to the
     * editor as JSON
     * @return string the initialization code, including <script> tags
     */
    public static function LoadEditorHead($options=null)
    {
        global $config;

        if (JSEffects::$loadedEditorHead) {
            return '';
        }

        JSEffects::$loadedEditorHead = true;

        $output = '<script type = "text/javascript">';

        if (!$options) {
            $options = array(
                'mode' => '"textareas"',
                'theme' => '"advanced"',
                'theme_advanced_toolbar_location' => '"top"',
                'theme_advanced_toolbar_align' => '"left"'
         );
        }

        header('Content-Type: text/plain');
        print 'CHANGE THIS: JSEffects(123)';
        die(print_r(JSEffects::HashToJSON($options), true));
        exit(0);
        $output .= 'tinyMCE.init({ ' . JSEffects::HashToJSON($options) . '});</script>';

        return $output;
    }

    /**
     * Converts an associtive array to JSON
     *
     * @static 
     * @access private
     * @param array $hash an associative array
     * @return string JSON
     */
    private static function HashToJSON($hash)
    {
        $output = '';

        $once = false;
        foreach ($hash as $key => $value) {
            if ($once) {
                $output .= ', ';
            }
            else {
                $once = true;
            }

            $output .= $key .= ' : ';
            
            if (is_array($value)) {
            	$output .= '[' .  HashToJSON($value) . ']';
            }
            else {
            	$output .= $value;
            }
        }

        return $output;
    }
}

?>
