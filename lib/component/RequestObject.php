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
	public static function from(&$where)
	{
		throw new NotImplementedException();
	}

	public function validate()
	{
		return false;
	}

	/**
	 * Merges the current object with the specified associtive array.
	 *
	 * @access public
	 * @return void
	 */
	public function merge(&$with)
	{
		Params::ArrayToObject($with, $this);
	}
}

?>
