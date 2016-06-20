<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Observer.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_Action_Observer
{
    public function addCustomLayoutHandle(Varien_Event_Observer $observer) {
        $controllerAction = $observer->getEvent()->getAction();
        $layout = $observer->getEvent()->getLayout();
        if ($controllerAction && $layout && $controllerAction instanceof Mage_Adminhtml_System_ConfigController) { // Can be checked in other ways of course
            if ($controllerAction->getRequest()->getParam('section') == 'pickpack_options') {
                $layout->getUpdate()->addHandle('handle_add_css_js');
            }
        }
        return $this;
    }
    protected function _updateAttribute($configPrefix, $orderIds, $fieldPrefix = '') {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }

        if (!empty($fieldPrefix)) {
            $fieldPrefix .= '_';
        }

        $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');
        foreach ($orderIds as $orderId) {

            $storeId = Mage::getResourceSingleton('pickpack/sales_order')->getOrderStoreId($orderId);
            if (!$storeId) {
                $storeId = 0;
            }

            if (Mage::getStoreConfigFlag("pickpack_options/{$configPrefix}/{$fieldPrefix}additional_action", $storeId)) {

                $attributeToUpdate = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}szy_attribute_to_update", $storeId);
            	if($attributeToUpdate == 'custom_attribute')
            		$postfix =  'szy_custom_value1';
            	else
            		if($attributeToUpdate == 'custom_attribute2')
            			$postfix =  'szy_custom_value2';
            		else
            			$postfix =  'szy_custom_value3';
            		
               

                $value = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}{$postfix}", $storeId);
                
                if($value =='{{date}}')
                {            	
					$value = date("d-m-Y", Mage::getModel('core/date')->timestamp(time()));
                }
                else
					if ($value == '_custom_') {
						if($attributeToUpdate == 'custom_attribute')
							$postfix =  'szy_own_value1';
						else
							if($attributeToUpdate == 'custom_attribute2')
								$postfix =  'szy_own_value2';
							else
								$postfix =  'szy_own_value3';
						$value = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}{$postfix}", $storeId);
					}

                try {
                	$pickpack_version = (int)Mage::getConfig()->getNode()->modules->Moogento_Pickpack->version;
					$shipeasy_version = (int)Mage::getConfig()->getNode()->modules->Moogento_ShipEasy->version;
					if($shipeasy_version < 3){
	                    $resource->updateGridRow($orderId, $attributeToUpdate, $value);
	            	}
	            	else
	            	{
	            		$resource->updateGridRow($orderId, 'szy_'.$attributeToUpdate, $value);
	            	}
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        return $this;
    }
    
    public function beforeResponse($observer) {

    }
    public function afterResponse($observer) {

    }

    public function invoiceAfterToPdf($observer) {
        $orderIds = $observer->getOrderIds();
        if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14')) {
            $this->_updateAttribute('wonder_invoice', $orderIds);
        }
    }
	
    public function zebraAfterToPdf($observer) {
        $orderIds = $observer->getOrderIds();
        if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14')) {
            $this->_updateAttribute('label_zebra', $orderIds);
        }
    }

    public function invoiceAfterAutoToPdf($observer) {
        $orderIds = $observer->getOrderIds();
        if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14')) {
            $this->_updateAttribute('wonder_invoice', $orderIds, 'auto_processing');
        }
    }

    public function packAfterToPdf($observer) {
        $orderIds = $observer->getOrderIds();
        if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14')) {
            $this->_updateAttribute('wonder', $orderIds);
        }
    }

    public function packAfterAutoToPdf($observer) {
        $orderIds = $observer->getOrderIds();
        if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14')) {
            $this->_updateAttribute('wonder', $orderIds, 'auto_processing');
        }
    }
    
    public function packAfterManualPrint($observer) {
    	$orderIds = $observer->getOrderIds();
    	$this->updateOrderStatus($orderIds,'wonder');
    }
    
    public function invoiceAfterManualPrint($observer) {
    	$orderIds = $observer->getOrderIds();
    	$this->updateOrderStatus($orderIds,'wonder_invoice');
    }
    
    private function updateOrderStatus($orderIds,$group='wonder') {
    	if (Mage::helper('pickpack')->isInstalled('Moogento_Core')) {
			$status = Mage::getStoreConfig("pickpack_options/{$group}/additional_action_change_order_status");

			foreach($orderIds as $id) {
                try{
                    Mage::helper('moogento_core')->changeOrderStatus($id, $status, '');
                } catch(Exception $e) {
                	echo $e->getMessage(); exit;
                }
            }
		}
    }
}
