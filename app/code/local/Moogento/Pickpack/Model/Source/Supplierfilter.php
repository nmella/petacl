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
* File        Supplierfilter.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Source_Supplierfilter
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        if(Mage::helper('pickpack')->isInstalled('Moogento_Trolleybox'))
        {
            return array(
            array('value' => 'no', 'label' => ''),
            array('value' => 'pack', 'label' => Mage::helper('pickpack')->__('PDF (Packing Sheet)')),
            array('value' => 'invoice', 'label' => Mage::helper('pickpack')->__('PDF (Invoice)')),
            array('value' => 'zebra', 'label' => Mage::helper('pickpack')->__('PDF (Zebra Labels)')),
            array('value' => 'order_combined', 'label' => Mage::helper('pickpack')->__('PDF (Order-combined Picklist)')),
            array('value' => 'trolleybox', 'label' => Mage::helper('pickpack')->__('PDF (Trolleybox Picklist)')),                                          
            array('value' => 'order_separated', 'label' => Mage::helper('pickpack')->__('PDF (Order-separated Picklist)')),
            array('value' => 'order_summary', 'label' => Mage::helper('pickpack')->__('PDF (Orders Summary)')),
            array('value' => 'stock', 'label' => Mage::helper('pickpack')->__('PDF (Out-of-stock List)')),                                          

        );
        }
        return array(
            array('value' => 'no', 'label' => ''),
            array('value' => 'pack', 'label' => Mage::helper('pickpack')->__('PDF (Packing Sheet)')),
            array('value' => 'invoice', 'label' => Mage::helper('pickpack')->__('PDF (Invoice)')),
            array('value' => 'zebra', 'label' => Mage::helper('pickpack')->__('PDF (Zebra Labels)')),
            array('value' => 'order_combined', 'label' => Mage::helper('pickpack')->__('PDF (Order-combined Picklist)')),
            array('value' => 'order_separated', 'label' => Mage::helper('pickpack')->__('PDF (Order-separated Picklist)')),
            array('value' => 'order_summary', 'label' => Mage::helper('pickpack')->__('PDF (Orders Summary)')),
            array('value' => 'stock', 'label' => Mage::helper('pickpack')->__('PDF (Out-of-stock List)')),                                          
        );
    }

}
