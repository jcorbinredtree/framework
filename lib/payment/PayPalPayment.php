<?php

/**
 * PayPalPayment class definition
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
 * @category   Payment
 * @author     Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright  2007 Red Tree Systems, LLC
 * @license    MPL 1.1
 * @version    1.0
 * @link       http://framework.redtreesystems.com
 */

/**
 * Sets up the concrete implemenation for the PayPal backend
 *
 * @category   Payment
 */

class PayPalPayment extends Payment {
  public $apiUsername = "";
  public $apiPassword = "";
  public $apiSignature = "";
  public $live = false;
  public $paymentType = 'Sale';

  /**
   * Returns a PayPalPayment merged with $where
   *
   * @param mixed $where
   * @return PayPalPayment
   */
  public static function From( &$where ) {
    $us = new PayPalPayment();
    Params::ArrayToObject( $where, $us );
    return $us;
  }

  /*
   * @NOTICE: this is hardcoded to use US Currency
   */
  public function purchase() {
    global $current, $config;

    set_include_path("$config->absPath/extensions/:" . get_include_path());
    require_once "$config->absPath/extensions/PayPal.php";
    require_once "$config->absPath/extensions/PayPal/Profile/Handler/Array.php";
    require_once "$config->absPath/extensions/PayPal/Profile/API.php";
    require_once "$config->absPath/extensions/PayPal/Type/DoDirectPaymentRequestType.php";
    require_once "$config->absPath/extensions/PayPal/Type/DoDirectPaymentRequestDetailsType.php";
    require_once "$config->absPath/extensions/PayPal/Type/DoDirectPaymentResponseType.php";

    //  Add all of the types
    require_once "$config->absPath/extensions/PayPal/Type/BasicAmountType.php";
    require_once "$config->absPath/extensions/PayPal/Type/PaymentDetailsType.php";
    require_once "$config->absPath/extensions/PayPal/Type/AddressType.php";
    require_once "$config->absPath/extensions/PayPal/Type/CreditCardDetailsType.php";
    require_once "$config->absPath/extensions/PayPal/Type/PayerInfoType.php";
    require_once "$config->absPath/extensions/PayPal/Type/PersonNameType.php";
    require_once "$config->absPath/extensions/PayPal/CallerServices.php";

    $environment = ( $this->live ? 'live' : 'sandbox' );

    $dp_request = new DoDirectPaymentRequestType();
    $OrderTotal = new BasicAmountType();
    $OrderTotal->setattr( 'currencyID', 'USD' );
    $OrderTotal->setval( $this->amount, 'iso-8859-1' );

    $PaymentDetails = new PaymentDetailsType();
    $PaymentDetails->setOrderTotal( $OrderTotal );

    $shipTo = new AddressType();
    $shipTo->setName( $this->firstName . ' ' . $this->lastName );
    $shipTo->setStreet1( $this->address1 );
    $shipTo->setStreet2( $this->address2 );
    $shipTo->setCityName( $this->city );
    $shipTo->setStateOrProvince( $this->state );
    $shipTo->setCountry( 'US' );
    $shipTo->setPostalCode( $this->zip );
    $PaymentDetails->setShipToAddress( $shipTo );

    $dp_details = new DoDirectPaymentRequestDetailsType();
    $dp_details->setPaymentDetails( $PaymentDetails );

    // Credit Card info
    $card_details = new CreditCardDetailsType();
    $card_details->setCreditCardType( $this->creditCardType );
    $card_details->setCreditCardNumber( $this->creditCardNumber );
    $card_details->setExpMonth( $this->expirationMonth );
    $card_details->setExpYear( $this->expirationYear );
    $card_details->setCVV2( $this->cvv2Number );

    $payer = new PayerInfoType();
    $person_name = new PersonNameType();
    $person_name->setFirstName( $this->firstName );
    $person_name->setLastName( $this->lastName );
    $payer->setPayerName( $person_name );
    $payer->setPayerCountry( 'US' );
    $payer->setAddress( $shipTo );

    $card_details->setCardOwner( $payer );
    $dp_details->setCreditCard( $card_details );
    $dp_details->setIPAddress( $_SERVER [ 'SERVER_ADDR' ] );
    $dp_details->setPaymentAction( 'Sale' );

    $dp_request->setDoDirectPaymentRequestDetails( $dp_details );

    $handler = ProfileHandler_Array::getInstance( array(
      'username' => $this->apiUsername,
      'certificateFile' => null,
      'subject' => null,
      'environment' => $environment
    ) );

    $pid = ProfileHandler::generateID();
    $profile = new APIProfile( $pid, $handler );
    $profile->setAPIUsername( $this->apiUsername );
    $profile->setAPIPassword( $this->apiPassword );
    $profile->setSignature( $this->apiSignature );
    $profile->setEnvironment( $environment );

    $caller = new CallerServices( $profile );
    $response = $caller->DoDirectPayment( $dp_request );

    if ( PayPal::isError( $response ) ) {
      $current->addWarning( $response->message );
      return false;
    }

    if ( $response->Ack == 'Success' ) {
      return true;
    }

    if ( is_array( $response->Errors ) ) {
      foreach ( $response->Errors as $error ) {
        $current->addWarning( $error->LongMessage );
	 	  }
    }
    else {
      $current->addWarning( $response->Errors->LongMessage );
    }

    return false;
  }

}

?>
