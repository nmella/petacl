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
* File        Aabstract.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

/**
 * Sales Order PDF abstract model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Moogento_Pickpack_Model_Sales_Order_Pdf_Config_Config extends Varien_Object
{
    public function getWonderConfig($field, $wonder, $storeId = null) {
        $storeId = !is_null($storeId) ? $storeId : 0;
        $key = $storeId . '-' . $wonder . '-' . $field;
        if(!$this->hasData($key)) {
            $value = $this->_getConfig($field, $default = '', false, $wonder, $storeId, $trim = true, 'pickpack_options');
            $this->setData($key, $value);
            return $value;
        }

        return $this->getData($key);
    }

    protected function _getConfig($field, $default = '', $add_default = true, $group = 'wonder', $store = null, $trim = true, $section = 'pickpack_options') {
        return Mage::helper('pickpack/config')->getConfig($field, $default, $add_default, $group, $store, $trim, $section);
    }

    public function getNudgeConfig($field, $default, $wonder, $store = null) {
        $this->_getConfig($field, $default, true, $this->getWonder(), $store = null, $trim = true, $section = 'pickpack_options');
    }

    public function getGeneralConfig($field, $store = null) {
        $this->_getConfig($field, '', false, 'general', $store = null, true, 'pickpack_options');
    }
}
