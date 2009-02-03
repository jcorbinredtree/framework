<?php

/**
 * Template class definition
 *
 * PHP version 5
 *
 * LICENSE: This file is a part of the Red Tree Systems framework,
 * and is licensed royalty free to customers who have purchased
 * services from us. Please see http://www.redtreesystems.com for
 * details.
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
     * @access public
     * @return Template new instance
     */
    public function __construct()
    {
        $this->compiler = 'FrameworkCompiler';

        $policy = PolicyManager::getInstance();
        Compiler::setCompileDirectory($policy->getTemplatesDir() . '/');
        Compiler::setCompilerClass('FrameworkCompiler');
    }

    /**
     * Override the base fetch to do some processing
     *
     * @param string $template the path to the template
     * @return string the template output
     */
    public function fetch($template)
    {
        global $current;

        $fullPath = ($template[0] == '/') ? $template : "$current->path/$template";
        $fullPath = preg_replace('|[/](?:[^/]+)[/](?:[.]{2})[/]|', '/', $fullPath);

		// how shitty mr bates
		$fullPath = preg_replace('|[/]{2,}|', '/', $fullPath);
        $fullPath = preg_replace('|^(.*)C[:][/]|', 'C:/', $fullPath);

        $path = preg_replace("|[/](?:[^/]+?)$|", '', $fullPath);
        $path = Application::setPath($path);
        $results = parent::fetch($fullPath);
        Application::setPath($path);

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
