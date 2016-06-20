<?php

class Mage_Payment_Model_Puntopagos_Tbanc extends Mage_Payment_Model_Puntopagos
{   
    protected $_code  = 'tbanc';    
    protected $medio_pago;
    
    public function __construct()
    {
       parent::_construct();
       $this->medio_pago = $this->getConfigData('medio_pago');
    }

}
