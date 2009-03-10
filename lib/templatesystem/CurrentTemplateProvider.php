<?php

/**
 * TemplateSystem class definition
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

/**
 * Loads templates inside of CurrentPath::get
 *
 * Only handles template resources beginning in "view/"
 */
class CurrentTemplateProvider extends PHPSTLFileBackedProvider
{
    /**
     * Subclasses implement this to do basic file based resolution
     *
     * @param resource string
     * @return string the file path
     * @see PHPSTLFileBackedProvider::getResourceFile
     */
    protected function getResourceFile($resource)
    {
        if (substr($resource, 0, 5) != 'view/') {
            return null;
        }

        $path = realpath(CurrentPath::get().'/'.$resource);
        if ($path == false || ! is_file($path)) {
            return null;
        } else {
            return $path;
        }
    }

    protected function createTemplate($resource, $data, $identifier=null)
    {
        return parent::createTemplate($resource, $data,
            'file://'.CurrentPath::get().'/'.$resource
        );
    }

    /**
     * @return string
     * @see PHPSTLTemplateProvider::__tostring
     */
    public function __tostring()
    {
        return "[Current Path Provider]";
    }
}

# vim:set sw=4 ts=4 expandtab:
?>
