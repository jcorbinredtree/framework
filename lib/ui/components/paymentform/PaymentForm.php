<?php

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
        $template = new Template();
        
        $path = Application::setPath(dirname(__FILE__));
        
        $template->assign('payment', $this);
        $template->display('view/view.xml');
        
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
