<?php
/**
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
 */

class PaymentForm extends SessionObject
{
    /**
     * A provider class name
     *
     * @var string
     */
    public $provider;
    public $test = false;

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
    public $customerBirthMonth;
    public $customerBirthDay;
    public $customerBirthYear;

    public $apiUsername = "";
    public $apiPassword = "";
    public $apiSignature = "";
    public $live = false;


    /**
     * Gets a new PaymentForm from $where
     *
     * @param mixed $where
     * @return PaymentForm
     */
    public static function From(&$where)
    {
        $us = new PaymentForm();
        $us->merge($where);
        return $us;
    }

    public function draw()
    {
        $path = Application::setPath(dirname(__FILE__));
        $template = new Template('view/view.xml');
        print $template->render(array('payment' => $this));
        Application::setPath($path);
    }

    public function validate()
    {
        $gateway = new $this->provider();
        Params::arrayToObject($this, $gateway);

        return $gateway->validate();
    }

    public function purchase()
    {
        $gateway = new $this->provider();
        Params::ArrayToObject($this, $gateway);

        return $gateway->purchase();
    }
}

?>
