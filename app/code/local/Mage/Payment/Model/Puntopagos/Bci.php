<?php

class Mage_Payment_Model_Puntopagos_Bci extends Mage_Payment_Model_Puntopagos
{   
    protected $_code  = 'bci';    
    protected $medio_pago;
    
    public function __construct()
    {
       parent::_construct();
       $this->medio_pago = $this->getConfigData('medio_pago');
    }

}
