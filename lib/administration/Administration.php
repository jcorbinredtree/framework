<?php

/**
 * Administration class definition
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
 * @category     Application
 * @package		 Administration 
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * This base class defines an Administration part in the admin section
 *
 * @category     Application
 * @package      Administration
 */
abstract class Administration extends ActionProvider
{        
    /**
     * Holds required scripts 
     * 
     * @var array
     */
    public $scripts = array();
    
   /**
    * Stores the breadcrumbs for the page
    * 
    * @var array
    */
    public $breadCrumbs = array();
    
    /**
     * Adds an item as a bread crumb
     * 
     * @param NavigatorItem $crumb
     * @return void
     */
    public function addBreadCrumb(NavigatorItem $crumb)
    {
        array_push($this->breadCrumbs, $crumb);
    }

    /**
     * Adds an item as a bread crumb to the head
     * 
     * @param NavigatorItem $crumb
     * @return void
     */
    public function addBreadCrumbHead(NavigatorItem $crumb)
    {
        array_unshift($this->breadCrumbs, $crumb);
    }    
        
    /**
     * Returns text in href form suitable for linking to other actions within the framework.
     * 
     * @static 
     * @access public
     * @param string a component class name
     * @param int $action the action you want to link to
     * @param int $stage the stage you want to link to, default Stage::VIEW
     * @param array $options an associative array of parameters to pass to the action. You may set
     * -textalize to true if you are using the text directly (ie not in an href). This
     * option will be removed from the final link, but does not do encoding transformations
     * such as & => &amp;.
     * @return string text to use in an href upon success; null upon failure
     */
    public static function getActionURI($component, $action, $options=array(), $stage=Stage::VIEW)
    {
        global $config, $database;

        $amp = array_key_exists('-textalize', $options) ? '&' : '&amp;';

        $component = urlencode($component);

        $link = "$config->absUri/admin";        
        $link .= "/?" . AppConstants::COMPONENT_KEY . "=$component";

        if ($action) {
            $link .= $amp . AppConstants::ACTION_KEY . "=$action";
        }
        
        if ($stage) {
            $link .= $amp . AppConstants::STAGE_KEY . "=$stage";
        }
        
        if (!array_key_exists(AppConstants::POPUP_KEY, $options) && Params::request(AppConstants::POPUP_KEY)) {
            $options[AppConstants::POPUP_KEY] = 1;
        }

        foreach ($options as $kw => $val) {
            $skip = false;
            
            if ($kw[0] == '-') {
                switch ($kw) {
                    case '-textalize':
                        $skip = true;
                        break;
                    case '-popup':
                        $kw = AppConstants::POPUP_KEY;
                        break;
                    case '-no-html':
                        $kw = AppConstants::NO_HTML_KEY;
                        break;
                    case '-secure':
                        $kw = AppConstants::SECURE_KEY;
                        break;
                }                
            }
            
            if ($skip) {
                continue;
            }

            if ($val === null) {
                continue;
            }
            
            if ($kw == AppConstants::SECURE_KEY) {
                if ($val) {
                    $link = preg_replace('/^http[:]/i', 'https:', $link);
                }
                else {
                    $link = preg_replace('/^https[:]/i', 'http:', $link);
                }
            }

            $kw = urlencode($kw);            
            $link .= "${amp}$kw=$val";
        }

        return $link;
    }

    /**
     * Gets the file where $className resides
     *
     * @param string $className the classname to find
     * @return string file; null on failure
     */
    public static function find($className)
    {
        global $config;
            
        if ($file = Application::classMapped($className)) {
            return $file;
        }
        
        $paths = array("$config->absPath/components", "$config->absPath/modules", "$config->absPath/themes");
            
        foreach ($paths as $path) {
            $fh = opendir($path);
            
            while (false !== ($file = readdir($fh))) {        
                if ($file[0] == '.') {
                    continue;
                }
            
                if ($file == 'shared') {
                    continue;
                }
                
                $file = "$path/$file";
                if (!is_dir($file)) {
                    continue;
                }
                       
                $file = "$file/${className}.php";
                if (!file_exists($file)) {
                    continue;
                }
            
                return $file;
            }
            
            closedir($fh);
        }   
        
        return null;
    }
    
    /**
     * Returns an instance of the specified item, and performs basic initializations on it.
     * 
     * @static
     * @access public
     * @param string $className the class name to load
     * @return Component an instance of the specified component on success; null otherwise
     */    
    static public function load($className)
    {
        global $config, $database, $current;

        $file = '';

        if (!($file = Application::classMapped($className))) {
            $file = Administration::find($className);
            if (!$file) {
                return null;
            }
            
            include_once $file;                     
        }        

        /*
         * set the current path
         */
        $current->path = basename($file);

        $obj = new $className();
        $obj->onRegisterActions();
        return $obj;
    }
    
    /**
     * Implements the perform
     *
     * @param ActionDescription $action
     * @param int $stage
     * @return boolean
     */
    public function perform(ActionDescription &$action, $stage) 
    {
        global $config;
        
        $class = preg_replace('/Administration$/', '', $this->getClass());
        $path = Application::setPath("$config->absPath/components/$class/");
       
        $handler = (is_string($action->handler) ? array($this, $action->handler) : $action->handler); 
        $returnValue = call_user_func($handler, $stage);

        Application::setPath($path);

        return $returnValue;        
    }   
    
    /**
     * A simple method to simply view a template, optionally setting aruments
     * 
     * @param string name the location of the template
     * @param array arguments [optional] the arguments to pass to the template, 
     * expressed as name/value pairs
     * @return void
     */
    public function viewTemplate($name, $arguments=array())
    {
        $template = new Template();
        $template->setArguments($arguments);
        
        $this->write($template->fetch($name));
    }
    
    /**
     * Adds a script to the page requirements
     * 
     * @param string $req the location of the script
     * @return void
     */
    public function addScript($req)
    {
        if (is_array($req)) {
            foreach ($req as $r) {
                array_push($this->scripts, $r);
            }
        
            return;
        }
    
        array_push($this->scripts, $req);
    } 
}
?>