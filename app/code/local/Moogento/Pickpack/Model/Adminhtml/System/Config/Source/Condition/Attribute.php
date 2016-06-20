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
* File        Status.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_System_Config_Source_Condition_Attribute
{
    public function toOptionArray() {
        return array(
            array('value' => 'no', 'label' => Mage::helper('pickpack')->__('No (default)')),
            array('value' => 'status', 'label' => Mage::helper('pickpack')->__('Yes, by Order Status')),
            array('value' => 'shipping_method', 'label' => Mage::helper('pickpack')->__('Yes, by Shipping Method')),
            array('value' => 'attribute', 'label' => Mage::helper('pickpack')->__('Yes, by Order Attribute')),
        );
    }
}
