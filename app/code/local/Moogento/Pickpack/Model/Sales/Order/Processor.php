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
* File        Processor.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Model_Sales_Order_Processor extends Varien_Object
{
    protected $_processors = array(
        'wonder_invoice' => 'pickpack/sales_order_processor_invoice',
        'wonder_invoice2' => 'pickpack/sales_order_processor_invoice2',
        'wonder_invoice3' => 'pickpack/sales_order_processor_invoice3',

        'wonder' => 'pickpack/sales_order_processor_packingSheet',
        'wonder2' => 'pickpack/sales_order_processor_packingSheet2',
        'wonder3' => 'pickpack/sales_order_processor_packingSheet3',

        'picks' => 'pickpack/sales_order_processor_orderPick',
        'picks2' => 'pickpack/sales_order_processor_orderPick2',
        'picks3' => 'pickpack/sales_order_processor_orderPick3',

        'messages' => 'pickpack/sales_order_processor_itemsPick',
        'messages2' => 'pickpack/sales_order_processor_itemsPick2',
        'messages3' => 'pickpack/sales_order_processor_itemsPick3',
    );
    
    public function processStatusChange($order) {
        $increment_id = $order->getData('increment_id');
        // if($increment_id == '500034696')
//      {
//      
            //Turn off auto processing.
        foreach ($this->_processors as $processor) {
            $processor = Mage::getSingleton($processor);
            $processor->processOrderStatusUpdate($order);
        }
//      }
        return $this;
    } 
    
    public function processStatusChange_ForCron($order) {   
        foreach ($this->_processors as $key => $processor) {
            $this->processBulk($key);
        }
        return $this;
        
    } 
    
    public function processStatusChange_trigger_cron($order) {
        $this->processBulk('wonder_invoice');
    } 

    public function processBulk($type = null) {
        
        foreach ($this->_processors as $key => $processor) {
            if ($type && ($key != $type)) {
                continue;
            }
            $processor = Mage::getSingleton($processor);
            $processor->processBulk(Mage::app()->getLocale()->date()->toString('e'));
        }
        
    }
    
    public function processProductSeparated() {
        $mage_time = Mage::getModel('core/date')->timestamp(time());  
        $current_time =  date('h:i:s', $mage_time);         
        $cron_config_time = Mage::getStoreConfig('pickpack_options/product_separated/auto_processing_condition_specific_time');
        $cron_period = Mage::getStoreConfig('pickpack_options/product_separated/cron_period');
        if(is_numeric($cron_period))
            $cron_period = 60*$cron_period;
        else
            $cron_period = 300;
        $cron_config_time = str_replace(',',':',$cron_config_time);
        $time_cron =  strtotime($current_time);
        $time_config = strtotime($cron_config_time);    
        $processor = Mage::getSingleton('pickpack/sales_order_processor_productseparated');
        $processor->processProductSeparated(Mage::app()->getLocale()->date()->toString('e'));
    }
    
}