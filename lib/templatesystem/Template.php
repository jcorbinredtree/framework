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
 * @category     TemplateSystem
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

class Template extends PHPSTLTemplate
{
    private $currentPath=null;

    public function __construct(PHPSTLTemplateProvider $provider, $resource, $identifier)
    {
        parent::__construct($provider, $resource, $identifier);

        // Stash away the current path for when we render
        $this->currentPath = CurrentPath::get();
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
}

?>
