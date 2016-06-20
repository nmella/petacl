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


class Moogento_Pickpack_Model_Sales_Order_Observer
{
    protected $_orderStatus = null;
    protected $_isNewOrder = false;
 
    public function saveBefore($observer) {
        $order = $observer->getOrder();

        if ($order->getId()) {
            $this->_isNewOrder = false;
        } else {
            $this->_isNewOrder = true;
        }

        if (!$this->_isNewOrder && $order->getOrigData('status')) {
            $this->_orderStatus = $order->getOrigData('status');
        } else {
            $this->_orderStatus = $order->getStatus();
        }

        return $this;
    }
	
	
	public function orderSuccess($observer) {
        try {
            $currentOrder = $observer->getEvent()->getOrder();
            if (is_null($currentOrder)) {
                $orders = $observer->getEvent()->getOrders();
            } else {
                $orders = array($currentOrder);
            }
            foreach ($orders as $currentOrder) {
                $order = new Mage_Sales_Model_Order();
                $incrementId = $currentOrder->getData('increment_id');
                $order->loadByIncrementId($incrementId);
                if ($order->getStatus()) {
                    Mage::getSingleton('pickpack/sales_order_processor')->processStatusChange($order);
                }
            }
        } catch (Exception $e) {
            Mage::log("app/code/local/Moogento/Pickpack/Model/Sales/Order/Observer.php orderSuccess()", null, 'moogento_pickpack.log');
            Mage::log($e->getMessage(), null, 'moogento_pickpack.log');
        }
        return $this;
    }



    public function saveAfter($observer) {
    
 		//Step 2
        $order = $observer->getOrder();

        //Case 1: Change order status
        try {
            if (!$this->_isNewOrder) {
                if ($order->getStatus() != $this->_orderStatus) {
                    Mage::getSingleton('pickpack/sales_order_processor')->processStatusChange($observer->getOrder());

                }
            }
        } catch (Exception $e) {
            Mage::log("app/code/local/Moogento/Pickpack/Model/Sales/Order/Observer.php saveAfter() //Case 1: Change order status", null, 'moogento_pickpack.log');
            Mage::log($e->getMessage(), null, 'moogento_pickpack.log');
        }

        //Case 2: Amazon importer
        /**
         * For AMAZON imported orders
         */
        try {
            if (($order->getPaymentType() == 'amagento') || ($order->getShippingMethod() == "amagentoshippingrate_amagentoshippingrate")) {
                if ($order->getStatus()) {
                    Mage::getSingleton('pickpack/sales_order_processor')->processStatusChange($observer->getOrder());
                }
            }
        } catch (Exception $e) {
            Mage::log("app/code/local/Moogento/Pickpack/Model/Sales/Order/Observer.php saveAfter() //Case 2: Amazon importer", null, 'moogento_pickpack.log');
            Mage::log($e->getMessage(), null, 'moogento_pickpack.log');
        }

        //Case 3: New order


        return $this;
    }

    public function placeAfter($observer) {
    	//Step 1
        $order = $observer->getOrder();

        if ($this->_isNewOrder && $order->getStatus()) {
            Mage::getSingleton('pickpack/sales_order_processor')->processStatusChange($order);
        }

        return $this;
    }

    public function bulkProcessing($type = null) {
        try {
            Mage::getSingleton('pickpack/sales_order_processor')->processBulk($type);
        } catch (Exception $e) {
            Mage::throwException('Error at bulkProcessing');
        }
    }

    public function bulkProcessingWonderInvoice() {
        try {
            $this->bulkProcessing('wonder_invoice');
        } catch (Exception $e) {
            Mage::throwException('Error at bulkProcessingWonderInvoice');
        }
    }

    public function bulkProcessingWonder() {
        try {
            $this->bulkProcessing('wonder');
        } catch (Exception $e) {
            Mage::throwException('Error at bulkProcessingWonder');
        }
    }

    public function bulkProcessingPicks() {
        try {
            $this->bulkProcessing('picks');
        } catch (Exception $e) {
            Mage::throwException('Error at bulkProcessingPicks');
        }
    }

    public function bulkProcessingMessages() {
        try {
            $this->bulkProcessing('messages');
        } catch (Exception $e) {
            Mage::throwException('Error at bulkProcessingMessages');
        }
    }
    
    
    public function processAllProductSeparated() {
    	try {
            Mage::getSingleton('pickpack/sales_order_processor')->processProductSeparated();
        } catch (Exception $e) { 
            Mage::log($e->getMessage(), null, 'moogento_pickpack.log');
        }
    }
}
