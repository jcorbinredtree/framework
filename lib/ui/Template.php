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
    private $currentPath=null;

    public function __construct(PHPSTLTemplateProvider $provider, $resource)
    {
        parent::__construct($provider, $resource);

        // Stash away the current path for when we render
        global $current;
        if (isset($current)) {
            $this->currentPath = $current->path;
        }
    }

    /**
     * Sets the application path when this template is rendered.
     */
    private $oldAppPath=null;
    protected function renderSetup($args)
    {
        parent::renderSetup($args);
        if (isset($this->currentPath)) {
            $this->oldAppPath = CurrentPath::set($this->currentPath);
        }
    }

    /**
     * Restores the application path after template is rendered.
     */
    protected function renderCleanup()
    {
        parent::renderCleanup();
        if (isset($this->currentPath)) {
            CurrentPath::set($this->oldAppPath);
        }
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

        if (!$component) {
            $component = $current->component;
        }

        return $component->getActionURI($action, $args, $stage);
    }

    /**
     * DEPRECATED
     */
    public function getThemeImage($key)
    {
        $config->deprecatedComplain(
            'Template->getThemeImage',
            '$page->theme->getImage'
        );
        $page = SitePage::getCurrent();
        if (! is_a($page, 'HTMLPage')) {
            throw new RuntimeException('Only html pages are themed');
        }
        return $page->getTheme()->getImage($key);
    }

    /**
     * DEPRECATED
     */
    public function getThemeIcon($key)
    {
        $config->deprecatedComplain(
            'Template->getThemeIcon',
            '$page->theme->getIcon'
        );
        $page = SitePage::getCurrent();
        if (! is_a($page, 'HTMLPage')) {
            throw new RuntimeException('Only html pages are themed');
        }
        return $page->getTheme()->getIcon($key);
    }
}

?>
