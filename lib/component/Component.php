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
     * Component instances, hashed by name
     *
     * @var Component
     */
    private static $instances = array();

    /*
     * Returns the singleton instance of the given component class
     *
     * If the class does not exist, trys to load lib/component/CLASS/CLASS.php
     *
     * @param class string class name
     * @return Object a sub class of Component
     */
    final public static function getInstance($class)
    {
        if (! array_key_exists($class, Component::$instances)) {
            if (! class_exists($class)) {
                @ob_start();
                include_once "lib/component/$class/$class.php";
                if (! class_exists($class)) {
                    $mess = ob_get_clean();
                    throw new RuntimeException("no such component $class: $mess");
                }
                @ob_end_clean();
            }
            Component::$instances[$class] = new $class();
        }

        return Component::$instances[$class];
    }

    /**
     * Constructs a SitePage that will render a given component class
     */
    static public function createComponentPage($class)
    {
        $config = Site::getConfig();
        $component = self::getInstance($class);

        $page = new HTMLPage();
        $page->component = $component;
        return $page;
    }

    public $stage;
    public $action;

    /**
     * contructor; generic initializations
     * do your initializations onInitialize()
     */
    final public function __construct($actionId=null)
    {
        global $config;

        $this->title = get_class($this);
        $this->onInitialize();
        $this->onRegisterActions();

        if (! isset($actionId)) {
            $actionId = Params::request(AppConstants::ACTION_KEY, null);
        }
        if (! isset($actionId)) {
            if (get_class($this) == $config->getDefaultComponent()) {
                $actionId = $config->getDefaultAction();
            } else {
                throw new RuntimeException("need an action");
            }
        }

        $this->action = $this->getAction($actionId);
        if (! $this->action) {
            throw new RuntimeException("Unknown action $actionId");
        }

        $this->stage = Params::request(AppConstants::STAGE_KEY, Stage::VIEW);

        if ((Params::server('HTTPS') != 'on') && $this->action->requiresSSL) {
            Application::forward(
                $current->getCurrentRequest(array('-secure'=>true))
            );
        }
    }

    public function onInitialize()
    {
    }

    public function __toString()
    {
        return get_class($this);
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
     * Returns text in href form suitable for linking to other actions within the framework.
     *
     * @access public
     * @param string a component class name
     * @param int $action the action id you want to link to, defaults to the current action
     * @param array $options an associative array of parameters to pass to the action. You may set
     * -textalize to true if you are using the text directly (ie not in an href). This
     * option will be removed from the final link, but does not do encoding transformations
     * such as & => &amp;.
     *
     * -popup indicates a popup window
     * -secure indicates a secure link
     *
     * @param int $stage the stage you want to link to, default Stage::VIEW
     * @return string text to use in an href upon success; null upon failure
     */
    public function getActionURI($action=null, $options=array(), $stage=Stage::VIEW)
    {
        if (! isset($action)) {
            $action = $this->action->id;
        }
        if (! isset($stage)) {
            $stage = $this->stage;
        }
        $policy = PolicyManager::getInstance();
        return $policy->getActionURI(get_class($this), $action, $options, $stage);
    }

    /**
     * Returns the path to this component
     *
     * The base class presumes that the file that defines a component class is
     * in the toplevel component directory, example:
     *   File: /somewhere/SITE/local/components/SomeCog/SomeCog.php
     *     class SomeCog extends Component { ... }
     *   Then:
     *     $c = new SomeCog();
     *     echo $c->getPath(); // prints /somewhere/SITE/local/components/SomeCog
     *
     * @return string
     */
    public function getPath()
    {
        $us = new ReflectionClass(get_class($this));
        return dirname($us->getFileName());
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
        $oldPath = CurrentPath::set($this->getPath());

        $handler = is_string($action->handler)
            ? array($this, $action->handler)
            : $action->handler;
        $returnValue = call_user_func($handler, $stage);

        CurrentPath::set($oldPath);
        return $returnValue;
    }

    /**
     * Adds an item as a bread crumb. $crumb may be an instance of NavigatorItem,
     * which is passed directly as is, or a string representing an action on this object.
     * In the case of the latter, a NavigatorItem is constructed to store as a breadcrumb item.
     *
     * @param mixed $crumb
     * @param string $label The label to set for this navigator item
     * @return void
     */
    public function addBreadCrumb($crumb, $label='')
    {
        $ni = $this->getNavigatorItem($crumb);
        if ($label) {
            $ni->label = $label;
        }

        array_push($this->breadCrumbs, $ni);
    }

    /**
     * Adds an item as a bread crumb to the head. $crumb may be an instance of NavigatorItem,
     * which is passed directly as is, or a string representing an action on this object.
     * In the case of the latter, a NavigatorItem is constructed to store as a breadcrumb item.
     *
     * @param mixed $crumb
     * @param string $label The label to set for this navigator item
     * @return void
     */
    public function addBreadCrumbHead($crumb, $label='')
    {
        $ni = $this->getNavigatorItem($crumb);
        if ($label) {
            $ni->label = $label;
        }

        array_unshift($this->breadCrumbs, $ni);
    }

    /**
     * A simple helper method for adding crumbs
     *
     * @param mixed $crumb
     * @return NavigatorItem
     */
    private function getNavigatorItem($crumb)
    {
        if ($crumb instanceof NavigatorItem) {
            return $crumb;
        }

        return new NavigatorItem(
            $this->getActionURI($crumb),
            $crumb
        );
    }

    /*
     * +====================+
     * |  VIEW (cacheable)  <-------------^------------<
     * +====================+             |            |
     *                                 NO |            |
     * +====================+     +==============+     |
     * |      VALIDATE      |-----> Return True? |     |
     * +====================+     +==============+     |
     *                                    |            |
     * +====================+             |            |
     * |       PERFORM      <-------------v YES        |
     * +=========|==========+                          |
     *           |                                     |
     * +=========v==========+                          |
     * |    Return False?   |--------------------------^ YES
     * +====================+
     */
    public function render()
    {
        switch ($this->stage) {
            default:
            case Stage::VIEW:
                Application::performAction($this, $this->action, $this->stage);
                break;
            case Stage::VALIDATE:
                if (!Application::call($this, $this->action, Stage::VALIDATE)) {
                    Application::performAction($this, $this->action, Stage::VIEW);
                    break;
                }
            case Stage::PERFORM:
                if (!Application::call($this, $this->action, Stage::PERFORM)) {
                    Application::performAction($this, $this->action, Stage::VIEW);
                }
                break;
        }

        $page = Site::getPage();
        $page->addToBuffer('content', $this);
    }

    /**
     * DEPRECATED, use HTMLPage->addAsset(...)
     */
    public function addScript($req)
    {
        global $config;
        $config->deprecatedComplain(
            'Component->addScript(...)',
            'HTMLPage->addAsset(new HTMLPageScript(...))'
        );
        SitePage::getCurrent()->addAsset(new HTMLPageScript($req));
    }

    /**
     * DEPRECATED, use HTMLPage->addAsset(...)
     */
    public function addStylesheet($req)
    {
        global $config;
        $config->deprecatedComplain(
            'Component->addScript(...)',
            'HTMLPage->addAsset(new HTMLPageStylesheet(...))'
        );
        SitePage::getCurrent()->addAsset(new HTMLPageStylesheet($req));
    }
}

?>
