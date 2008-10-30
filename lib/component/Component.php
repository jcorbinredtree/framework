<?php

/**
 * Component class definition
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
 * @category     Components
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Sets up the abstract definition of a Component. This is the base class
 * from which all user-defined components are derived.
 *
 * @package        Components
 */
abstract class Component extends ActionProvider
{       
   /**
    * Stores the breadcrumbs for the page
    * 
    * @var array
    */
    public $breadCrumbs = array();
    
    /**
     * Holds required scripts 
     * 
     * @var array
     */
    public $scripts = array();
    
    /**
     * Holds required head 
     * 
     * @var string
     */    
    public $head = '';
    
    /**
     * Holds the title of the current page;
     * 
     * @var string
     */
    public $title = '';
    
    /**
     * Meta keywords
     * 
     * @var string 
     */
    public $keywords = '';
    
    /**
     * The meta description
     *
     * @var string
     */    
    public $description = '';

    /**
     * contructor; generic initializations
     * do you initializations onInitialize()
     */
    final public function __construct() {
        global $config;
        
        $this->title = $config->company;
    } 
    
    public function __toString() {
        return $this->getClass();
    }
    
    /**
     * Called when a search is performed
     *
     * @param string $keyword
     * @return array an array of ComponentSearchResult 
     */
    public static function onSearch($keyword)
    {
        return array();
    }

    /**
     * Returns an instance of the specified component
     * 
     * @static
     * @access public
     * @param string $component a component class name
     * @return Component an instance of the specified theme
     */        
    static public function load($component)
    {
        global $config, $current;
        
        /*
         * set the current path
         */
        Application::setPath("$config->absPath/component/$component");
        
        $c = new $component();
        $c->onRegisterActions();
        return $c;
    }    

    /**
     * Returns text in href form suitable for linking to other actions within the framework.
     * 
     * @static 
     * @access public
     * @param string a component class name
     * @param int $action the action id you want to link to
     * @param array $options an associative array of parameters to pass to the action. You may set
     * -textalize to true if you are using the text directly (ie not in an href). This
     * option will be removed from the final link, but does not do encoding transformations
     * such as & => &amp;.
     * 
     * -popup indicates a popup window
     * 
     * -secure indicates a secure link
     * 
     * -no-html indicates to disregard the theme, and go directly to the action (for binary and such)
     * @param int $stage the stage you want to link to, default Stage::VIEW
     * @return string text to use in an href upon success; null upon failure
     */
    public static function getActionURI($component, $action, $options=array(), $stage=Stage::VIEW)
    {
        global $config, $database;
        
        $amp = array_key_exists('-textalize', $options) ? '&' : '&amp;';

        $component = urlencode($component);

        $link = $config->absUri;

        if ($config->sefLinks) {
            $link .= "/" . AppConstants::COMPONENT_KEY . "/$component";
        }
        else {
            $link .= "/?" . AppConstants::COMPONENT_KEY . "=$component";
        }

        if ($action) {
            if ($config->sefLinks) {
                $link .= '/' . AppConstants::ACTION_KEY . "/$action";
            }
            else {
                $link .= $amp . AppConstants::ACTION_KEY . "=$action";
            }
        }
        
        if ($stage) {
            if ($config->sefLinks) {
                $link .= '/' . AppConstants::STAGE_KEY . "/$stage";
            }
            else {
                $link .= $amp . AppConstants::STAGE_KEY . "=$stage";
            }
        }        
        
        /*
         * if we're a popup, other links should be popups
         */
        if (!array_key_exists('-popup', $options) && Params::request(AppConstants::POPUP_KEY)) {
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

            if ($config->sefLinks) {
                $link .= "/$kw/$val";
            }
            else {
                $link .= "${amp}$kw=$val";
            }
        }

        return $link;
    }

    /**
     * Called before rendering takes place
     *
     * @param LayoutDescription $description
     * @return void
     */
    public function onPreRender(LayoutDescription &$description)
    {
        
    }
    
    /**
     * Called after rendering has occurred
     *
     * @param string $content
     * @return void
     */
    public function onPostRender(&$content)
    {
        
    }
    
    /**
     * Implements the perform
     *
     * @param ActionDescription $action
     * @param int $stage
     * @return boolean, null on permission denied
     */
    public function perform(ActionDescription &$action, $stage) 
    {
        global $config;
        
        $class = $this->getClass();
        $path = Application::setPath("$config->absPath/components/$class/");
       
        $handler = (is_string($action->handler) ? array($this, $action->handler) : $action->handler); 
        $returnValue = call_user_func($handler, $stage);

        Application::setPath($path);

        return $returnValue;        
    }
    
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
     * Adds a script to the page requirements
     * 
     * @param string $req the location of the script
     * @return void
     */
    protected function addScript($req)
    {
        if (is_array($req)) {
            foreach ($req as $r) {
                array_push($this->scripts, $r);
            }
        
            return;
        }
    
        array_push($this->scripts, $req);
    }
    
    /**
     * A simple method to simply view a template, optionally setting aruments
     * 
     * @param string name the location of the template
     * @param array arguments [optional] the arguments to pass to the template, 
     * expressed as name/value pairs
     * @return void
     */
    protected function viewTemplate($name, $arguments=array())
    {
        $template = new Template();
        $template->setArguments($arguments);
        
        $this->write($template->fetch($name));
    }
}

?>
