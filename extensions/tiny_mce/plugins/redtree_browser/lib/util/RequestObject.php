<?php

/**
 * RequestObject class definition
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
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2008 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * This is an abstract class to ease parameter handling. It is
 * somewhere between a data access object and a form object.
 * This class should be extended by most helper classes.
 * 
 * @see IRequestObject
 */
abstract class RequestObject implements IRequestObject
{
    public static function from(&$where)
    {
        throw new Exception("not implemented");
    }
    
    public function validate()
    {
        return false;
    }

    public function merge(&$with)
    {
        Params::ArrayToObject($with, $this);            
    }
}

?>
