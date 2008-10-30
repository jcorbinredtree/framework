<?php

/**
 * IRequestObject class definition
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
 * @category     Utils
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * This is an interface to standardize parameter handling. It is
 * somewhere between a data access object and a form object.
 * This class should be implemented by most helper classes.
 * 
 * @see RequestObject
 * @package        Utils
 */
interface IRequestObject {
    /**
     * Creates a new object of this class type. Usually
     * you would pass in one of $_GET, $_REQUEST, or $_POST,
     * and have a helper object populated with the fields.
     * Unfortuantly, this has to be implemented in the subclass.
     *
     * @abstract 
     * @access public 
     * @param array $where The associtive array from whose keys that
     * match will be copied to the object
     * @return object an object of subclasses type
     */
    public static function From(&$where);
    
    /**
     * Validates the current object. Useful for form processing.
     * 
     * @abstract 
     * @access public 
     * @return boolean true if all is well
     */
    public function validate();
    
    /**
     * Merges the current object with the specified associtive array.
     * 
     * @access public 
     * @return void
     */
    public function merge(&$with);
}

?>
