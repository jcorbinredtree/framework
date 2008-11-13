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
     * Holds required stylesheets
     *
     * @var array
     */
    public $stylesheets = array();

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
     * do your initializations onInitialize()
     */
    final public function __construct() {
        global $config;

        $this->title = 'Untitled Page';
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

        $c = new $component();

        Application::setPath($c->getPath());

        $c->onRegisterActions();
        return $c;
    }

    /**
     * Returns text in href form suitable for linking to other actions within the framework.
     *
     * @see ILinkPolicy::getActionURI
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
        $policy = PolicyManager::getInstance();
        return $policy->getActionURI($component, $action, $options, $stage);
    }

    public function getPath()
    {
        $us = new ReflectionClass($this->getClass());
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
        global $config;

        $class = $this->getClass();
        $path = Application::setPath($this->getPath());

        $handler = (is_string($action->handler) ? array($this, $action->handler) : $action->handler);
        $returnValue = call_user_func($handler, $stage);

        Application::setPath($path);

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

        return new NavigatorItem(Component::getActionURI($this->getClass(), $crumb), $crumb);
    }

    /**
     * Adds a script to the page requirements
     *
     * @param string $req the location of the script
     * @return void
     */
    protected function addScript($req)
    {
        if (in_array($req, $this->stylesheets)) {
            return;
        }

        if (is_array($req)) {
            foreach ($req as $r) {
                array_push($this->scripts, $r);
            }

            return;
        }

        array_push($this->scripts, $req);
    }

    /**
     * Adds a stylesheet to the page requirements
     *
     * @param string $req the location of the stylesheet
     * @return void
     */
    protected function addStylesheet($req)
    {
        if (in_array($req, $this->stylesheets)) {
            return;
        }

        if (is_array($req)) {
            foreach ($req as $r) {
                array_push($this->stylesheets, $r);
            }

            return;
        }

        array_push($this->stylesheets, $req);
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
