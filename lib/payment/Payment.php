<?php

/**
 * Payment base class definition
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
 * @category     payment
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * Sets up the abstract definition of a Payment
 *
 * @category     Payment
 */

abstract class Payment extends RequestObject
{
    public $firstName;
    public $lastName;
    public $phoneNumber;
    public $creditCardType;
    public $creditCardNumber;
    public $expirationMonth;
    public $expirationYear;
    public $cvv2Number;
    public $address1;
    public $address2;
    public $city;
    public $state;
    public $zip;
    public $amount;

    /**
     * Purchases the goods or services
     *
     * @access public
     * @return boolean true upon success
     */
    abstract public function purchase();

    public function validate()
    {
        return Params::Validate($this, array(
            'firstName' => I18N::String('Please enter a first name'),
            'lastName' => I18N::String('Please enter a last name'),
            'creditCardType' => I18N::String('Please select your credit card type'),
            'creditCardNumber' => I18N::String('Please enter your credit card number'),
            'expirationMonth' => I18N::String('Please select the month this credit card expires'),
            'expirationYear' => I18N::String('Please select the year this credit card expires'),
            'cvv2Number' => I18N::String('Please enter your security code'),
            'address1' => I18N::String('Please enter your billing address'),
            'city' => I18N::String('Please enter your billing city'),
            'state' => I18N::String('Please enter your billing state'),
            'zip' => I18N::String('Please enter your billing zipcode'),
            'amount' => array(
                array(Params::VALIDATE_EMPTY, I18N::String('The amount of your purchase could not be found')),
                array(Params::VALIDATE_NUMERIC, I18N::String('The amount of the service should be numeric')),
            )
     ));
    }
}



?>
