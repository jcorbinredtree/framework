<?php

/**
 * HTMLPageMeta definition
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
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

class HTMLPageMeta
{
    private $data = array();

    public function has($name)
    {
        return array_key_exists($name, $this->data);
    }

    public function get($name)
    {
        if (! array_key_exists($name, $this->data)) {
            return null;
        } else {
            return $this->data[$name];
        }
    }

    public function set($name, $Value)
    {
        if (! isset($value)) {
            if (array_key_exists($name, $this->data)) {
                unset($this->data[$name]);
            }
        } else {
            $this->data[$name] = $value;
        }
    }

    public function add($name, $Value)
    {
        if (! array_key_exists($name, $this->data)) {
            $this->data[$name] = array();
        } elseif(! is_array($this->data[$name])) {
            $this->data[$name] = array($this->data[$name]);
        }
        array_push($this->data[$name], $value);
    }

    public function clear($name)
    {
        if (array_key_exists($name, $this->data)) {
            unset($this->data[$name]);
        }
    }
}

?>
