<?php

/**
 * Theme class definition
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
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Sets up the abstract definition of a Theme
 *
 * @package        Themes
 */
abstract class Theme extends BufferedObject
{    
    /**
     * Gets the name of the current class
     *
     * @return string the name of the theme class
     */
    public function getClass() {
        return get_class($this);
    }    
    
    /**
     * Displays the application
     * 
     * @param LayoutDescription $layout a populated LayoutDescription
     * @return void
     */
    abstract public function onDisplay(LayoutDescription &$layout);
    
    /**
     * Gets image based on the theme
     * 
     * @param string $key
     * @return string
     */
    public function getImage($key)
    {
        global $config;
        
        $class = $this->getClass();
        return $config->absUri . "/themes/$class/view/images/$key"; 
    }
    
    /**
     * Gets icon based on the theme
     * 
     * @param string $key
     * @return string
     */
    public function getIcon($key)
    {
        global $config;
        
        $class = $this->getClass();
        return $config->absUri . "/themes/$class/view/icons/$key.png"; 
    }    
        
    /**
     * Load css from /theme/<code>/view/css/components/<current>/<current>.css.
     * Specific actions may also be picked up here via name.
     * 
     * @return array an array of stylesheets to use
     */
    public function getStyleSheets()
    {
        global $config, $current;
        
        $ss = array();
        $code = $this->getClass();
        $component = $current->component->getClass();
        $lowerComponent = strtolower($component);
        
        $css = $config->absPath . "/themes/$code/view/css/components/$component/$lowerComponent.css";        
        if (file_exists($css)) {
            array_push($ss, $css);
        }        
        
        if (ereg('MSIE ([0-9])', Params::server('HTTP_USER_AGENT'), $version)) {   
            $version = $version[1];                    
            $css = $config->absPath . "/themes/$code/view/css/components/$component/$lowerComponent-ie$version.css";        
            if (file_exists($css)) {
                array_push($ss, $css);
            }            
            else {
                $css = $config->absPath . "/themes/$code/view/css/components/$component/$lowerComponent-ie.css";        
                if (file_exists($css)) {
                    array_push($ss, $css);
                }
            }
        }

        return $ss;
    }

    /**
     * Returns an instance of the specified theme
     * 
     * @static
     * @access public
     * @param string $theme a theme class name
     * @return Theme an instance of the specified theme
     */        
    static public function load($theme)
    {
        global $config, $current;

        /*
         * set the current path
         */
        Application::setPath("$config->absPath/themes/$theme");
        return new $theme();
    }
}

?>
