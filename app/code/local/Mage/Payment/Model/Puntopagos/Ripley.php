<?php

class Mage_Payment_Model_Puntopagos_Ripley extends Mage_Payment_Model_Puntopagos
{   
    protected $_code  = 'ripley';    
    protected $medio_pago;
    
    public function __construct()
    {
       parent::_construct();
       $this->medio_pago = $this->getConfigData('medio_pago');
    }

}
