<?php
class Moogento_Pickpack_Model_Source_Storepickupoptions
{

   public function toOptionArray() {

        /*
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
                {	*/
        
	        return array(
	            array('value' => 'pickup_location', 'label' => Mage::helper('pickpack')->__('Pickup Location')),
	            array('value' => 'pickup_date', 'label' => Mage::helper('pickpack')->__('Pickup Date')),
	            array('value' => 'pickup_time', 'label' => Mage::helper('pickpack')->__('Pickup Time')),
	            array('value' => 'pickup_store_id', 'label' => Mage::helper('pickpack')->__('Pickup Store ID')),
	            array('value' => 'pickup_store_address', 'label' => Mage::helper('pickpack')->__('Pickup Store Address')),
	            array('value' => 'pickup_store_phone', 'label' => Mage::helper('pickpack')->__('Pickup Store Phone')),
	            array('value' => 'pickup_store_email', 'label' => Mage::helper('pickpack')->__('Pickup Store Email')),
	        );
	    // }
    }
	
}