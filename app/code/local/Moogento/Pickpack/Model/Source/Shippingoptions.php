<?php
class Moogento_Pickpack_Model_Source_Shippingoptions
{

   public function toOptionArray() {

        if(Mage::helper('pickpack')->isInstalled('Magalter_Customshipping'))
        {
        	$shippingOptionsFields = Mage::getModel('magalter_customshipping/option')->getCollection();
        	$returnArr = array();
        	foreach($shippingOptionsFields as $option)
        	{
        		$returnArr[] = array('value' => $option->getData('name'), 'label' => $option->getData('name'));
        	}
            $returnArr[] = array('value' => 'None', 'label' => 'None');
        	return $returnArr;
        }
        else
        {	
	        return array(
	            array('value' => 0, 'label' => Mage::helper('pickpack')->__('No')),
	            array('value' => 1, 'label' => Mage::helper('pickpack')->__('Yes')),
	        );
	    }
    }
	
}