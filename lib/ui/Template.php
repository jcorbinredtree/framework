<?php

/**
 * Template class definition
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
 * @package      Utils
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2006 Red Tree Systems, LLC
 * @license      http://www.redtreesystems.com PROPRITERY
 * @version      2.0
 * @link         http://www.redtreesystems.com
 */

/**
 * Template
 *
 * This is a wrapper class around PHPSTLTemplate templates
 *
 * @category     UI
 * @package      Utils
 */
class Template extends PHPSTLTemplate
{
    /**
     * Constructor
     *
     * @param template string
     *
     * @access public
     * @return Template new instance
     */
    public function __construct($template=null)
    {
        global $current;

        $this->compiler = 'FrameworkCompiler';

        $policy = PolicyManager::getInstance();
        Compiler::setCompileDirectory($policy->getTemplatesDir() . '/');
        Compiler::setCompilerClass('FrameworkCompiler');

        $this->addPath($current->path);

        parent::__construct($template);
    }

    /**
     * Override the base fetchTemplate to set the current application path when the template is processed
     *
     * @param string $template full path to a file that exists
     * @return string the template output
     */
    public function fetchTemplate($template)
    {
        $oldAppPath = Application::setPath(dirname($template));

        $results = parent::fetchTemplate($template);

        Application::setPath($oldAppPath);

        return $results;
    }

    /**
     * A shortcut method to Component::getActionURI(...).
     *
     * @see Component::getActionURI
     * @access public
     * @param mixed $component default current->component
     * @param int $action default current->action
     * @param array $args
     * @param int $stage the stage to link to
     * @return boolean true if succeeded.
     */
    public function href($component=null, $action=null, $args=array(), $stage=null)
    {
        global $current;

        if (!$action) {
            $action = $current->action;
        }

        if (!$component) {
            $component = $current->component;
        }

        if (!$stage) {
            $stage = $current->stage;
        }

        return call_user_func_array(array($current->component, 'getActionURI'),
                                    array($component, $action, $args, $stage));
    }

    /**
     * Gets an image from the current theme
     *
     * @see Theme->getImage
     * @since 1.1
     * @access public
     * @param string $key the key of the image you wish to get
     * @return string the source of the image
     */
    public function getThemeImage($key)
    {
        global $current;

        return $current->theme->getImage($key);
    }

    /**
     * Gets an image from the current theme
     *
     * @see Theme->getIcon
     * @since 1.1
     * @access public
     * @param string $key the key of the image you wish to get
     * @return string the source of the image
     */
    public function getThemeIcon($key)
    {
        global $current;

        return $current->theme->getIcon($key);
    }

    /**
     * Sets arguments of name/value pairs to the template
     *
     * @param array arguments the arguments to set on the template
     * @return void
     */
    public function setArguments($arguments)
    {
        foreach ($arguments as $name => $value) {
            $this->assign($name, $value);
        }
    }
}

?>
