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
* File        Values.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


abstract class Moogento_Pickpack_Model_Adminhtml_System_Config_Source_Custom_Values
{
    protected $_attributeId = 0;

    public function toOptionArray() {
        $values = array();
        $pickpack_version = (int)Mage::getConfig()->getNode()->modules->Moogento_Pickpack->version;
    	$shipeasy_version = (int)Mage::getConfig()->getNode()->modules->Moogento_ShipEasy->version;
		if($shipeasy_version < 3){		
			if($this->_attributeId == 1)
				$fieldPostfix ='custom_attribute_preset';
			else
				if($this->_attributeId == 2)
					$fieldPostfix = 'custom_attribute2_preset';
				else
					$fieldPostfix = 'custom_attribute3_preset';
		}
		else
		{
			if($this->_attributeId == 1)
				$fieldPostfix ='szy_custom_attribute_preset';
			else
				if($this->_attributeId == 2)
					$fieldPostfix = 'szy_custom_attribute2_preset';
				else
					$fieldPostfix = 'szy_custom_attribute3_preset';
		}
        
        $configPresets = Mage::getStoreConfig('moogento_shipeasy/grid/' . $fieldPostfix);
        $configPresets = explode("\n", $configPresets);
        foreach ($configPresets as $preset) {
            $preset = trim($preset);
            if (empty($preset)) {
                continue;
            }

            if (strpos($preset, '|') !== false) {
                list($label, $color) = explode('|', $preset);
                $values[] = array(
                    'value' => $label,
                    'label' => $label
                );
            } else {
                $values[] = array(
                    'value' => $preset,
                    'label' => $preset
                );
            }
        }

        $values[] = array(
            'value' => '_custom_',
            'label' => 'Custom Value'
        );

        return $values;
    }
}
