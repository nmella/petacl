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
* File        Fields.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_System_Config_Source_Custom_Fields
{
    public function toOptionArray() {
    	$pickpack_version = (int)Mage::getConfig()->getNode()->modules->Moogento_Pickpack->version;
    	$shipeasy_version = (int)Mage::getConfig()->getNode()->modules->Moogento_ShipEasy->version;
		if($shipeasy_version < 3){
			return array(
				array(
					'value' => 'custom_attribute',
					'label' => Mage::getStoreConfig('moogento_shipeasy/grid/custom_attribute_header')
				),
				array(
					'value' => 'custom_attribute2',
					'label' => Mage::getStoreConfig('moogento_shipeasy/grid/custom_attribute2_header')
				),
				array(
					'value' => 'custom_attribute3',
					'label' => Mage::getStoreConfig('moogento_shipeasy/grid/custom_attribute3_header')
				),
			);
		}
		else
		{
			return array(
				array(
					'value' => 'custom_attribute',
					'label' => Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute_header')
				),
				array(
					'value' => 'custom_attribute2',
					'label' => Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute2_header')
				),
				array(
					'value' => 'custom_attribute3',
					'label' => Mage::getStoreConfig('moogento_shipeasy/grid/szy_custom_attribute3_header')
				),
			);
		}
    }
}
