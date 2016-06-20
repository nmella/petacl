<?php

class Mage_Payment_Model_Puntopagos_Santander extends Mage_Payment_Model_Puntopagos
{   
    protected $_code  = 'santander';
    protected $medio_pago;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = false;
 
    public function __construct()
    {
       parent::_construct();
       $this->medio_pago = $this->getConfigData('medio_pago');
    }

}
