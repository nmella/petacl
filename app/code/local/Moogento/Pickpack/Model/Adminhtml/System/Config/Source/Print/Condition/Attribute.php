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
* File        Attribute.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_System_Config_Source_Print_Condition_Attribute
{
    protected $_optionArray = null;

    public function toOptionArray() {
        if (is_null($this->_optionArray)) {

            $_internalFlags = (array)Mage::getConfig()->getNode('global/moo_pp/internal_flags');

            if (Mage::getResourceSingleton('pickpack/sales_order') && is_object(Mage::getResourceSingleton('pickpack/sales_order')->getOrderFields())) {
                $orderTable = Mage::getResourceSingleton('pickpack/sales_order')->getOrderFields();

                $this->_optionArray = array();

                foreach ($orderTable as $fieldName => $fieldInfo) {
                    $label = $fieldName;

                    if (isset($_internalFlags[$fieldName])) {
                        $label = $_internalFlags[$fieldName];
                    } else {
                        $label = ucwords(str_replace('_', ' ', $label));
                    }

                    $this->_optionArray[] = array(
                        'value' => $fieldName,
                        'label' => $label
                    );
                }
            }

            if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14')) {
                $this->_optionArray[] = array(
                    'value' => 'szy_custom_attribute',
                    'label' => Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute_header')
                );
                $this->_optionArray[] = array(
                    'value' => 'szy_custom_attribute2',
                    'label' => Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute2_header')
                );
                $this->_optionArray[] = array(
                    'value' => 'szy_custom_attribute3',
                    'label' => Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute3_header')
                );
            }
        }

        return $this->_optionArray;
    }
}
