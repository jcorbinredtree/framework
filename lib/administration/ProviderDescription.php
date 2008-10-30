<?php
/*
 * ProviderDescription class definition
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
 * This structure allows administratable items to communicate with the framework
 *
 * @category     Application
 * @package      Administration
 */
class ProviderDescription extends TreeNode
{
    private static $providers = array();    
    
    private $items = array();
    
    public $name;
    public $icon;
    public $default = null;
    public $weight = 0;
    public $active = false;
    
    public static function getInstance($section)
    {
        $provider = null;        
        if (!array_key_exists($section, ProviderDescription::$providers)) {
            $provider = new ProviderDescription($section);
            
            ProviderDescription::$providers[$section] =& $provider;
        }
        else {
            $provider =& ProviderDescription::$providers[$section];
        }
               
        return $provider;
    }
    
    public static function getAllDescriptions()
    {
        return ProviderDescription::$providers;
    }
    
    public static function getParentDescriptions()
    {
        $parents = array();
        
        foreach(ProviderDescription::$providers as $provider) {
            if (!$provider->parent) {
                array_push($parents, $provider);
            }
        }
        
        return $parents;
    }
    
    public function getSubMenu($section)
    {
        if ($this->parent) {
            throw new Exception("only one level of depth is supported for providers");    
        }        
        
        $sub =& ProviderDescription::getInstance($section);
        $this->addChild($sub);
        return $sub;
    }
    
    public function handles($className, $actionId)
    {
        foreach ($this->items as $item) {
            if (($item[0]->getClass() == $className) && ($item[1]->id == $actionId)) {
                return true;
            }
        }

        foreach ($this->children as $child) {
            if ($child->handles($className, $actionId)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function addAction(ActionProvider &$provider, ActionDescription &$item)
    {
        array_push($this->items, array($provider, $item));
    }
    
    public function getActions()
    {
        return $this->items;
    }   

    private function __construct($section) {
        $this->name = $section;
    }    
}

?>