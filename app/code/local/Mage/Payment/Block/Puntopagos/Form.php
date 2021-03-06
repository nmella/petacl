<?php

class Mage_Payment_Block_Puntopagos_Form extends Mage_Payment_Block_Form
{      
    protected function _construct()
    {        
        $locale = Mage::app()->getLocale();
        $this->setTemplate('payment/puntopagos/form.phtml');                        
        return parent::_construct();        
    }
    
    public function getLogo()
    {
        $logo = Mage::getStoreConfig('payment/' . $this->getMethodCode() . '/logo');
        if ($logo)          
          return Mage::getUrl('media',array('_secure'=>true)) . 'paymentlogo/' . $logo;
              
    }
}
