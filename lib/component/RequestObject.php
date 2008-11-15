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
 * @category     Utils
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.1
 * @link             http://framework.redtreesystems.com
 */

/**
 * This is an abstract class to ease parameter handling. It is
 * somewhere between a data access object and a form object.
 * This class should be extended by most helper classes.
 *
 * @package        Utils
 */
abstract class RequestObject implements IRequestObject
{
    /**
     * Implement this method to allow a pattern such as:
     * $robj = MyClass::from($_POST);
     * if ($robj->myProp) { ... }
     *
     * @param array $where An associtive array to load data from
     */
	public static function from(&$where)
	{
		throw new NotImplementedException();
	}

	public function validate()
	{
		return false;
	}

	/**
	 * Merges the current object with the specified associtive array. Colon properties will
	 * be marked up to their respective objects. For instance, if your object is
	 *
	 * class X extends RequestObject
	 * {
	 *     public $objProp;
	 * }
	 *
	 * and the $with is
	 *
	 * objProp:value1
	 *
	 * then $this->objProp->value1 will be set accordingly.
	 *
	 * @param array $with an associtive array of items to merge
	 * @return void
	 */
	public function merge(&$with)
	{
		Params::arrayToObject($with, $this);

		$objs = array();
		foreach ($with as $key => $value) {
		    $m = array();
            if (preg_match('/^(\w+)[:](\w+)/', $key, $m)) {
                $oKey = $m[1];
                $pKey = $m[2];

                if (!array_key_exists($oKey, $objs)) {
                    $objs[$oKey] = array();
                }

                $objs[$oKey][$pKey] = $value;
            }
		}

		if (count($objs)) {
		    foreach ($objs as $prop => $desc) {
		        Params::arrayToObject($desc, $this->$prop);
		    }
		}
	}
}

?>
