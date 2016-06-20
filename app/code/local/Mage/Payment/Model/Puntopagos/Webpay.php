<?php

class Mage_Payment_Model_Puntopagos_Webpay extends Mage_Payment_Model_Puntopagos
{   
    protected $_code  = 'webpay';    
    protected $medio_pago;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;

 
    public function __construct()
    {
       parent::_construct();
       $this->medio_pago = $this->getConfigData('medio_pago');
    }

}
