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
* File        Pagepad.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Block_Pagepad extends Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $element->setStyle('width:40px;')
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = array();
        }

        $from = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $to = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        $bottom = $element->setValue(isset($values[2]) ? $values[2] : null)->getElementHtml();
        return Mage::helper('adminhtml')->__('Left/right : ') . $from . ' ' . Mage::helper('adminhtml')->__('pt') . ' &nbsp; ' . Mage::helper('adminhtml')->__('Top : ') . $to . '  ' . Mage::helper('adminhtml')->__('pt').' &nbsp; ' . Mage::helper('adminhtml')->__('Bottom : ') . $bottom .' ' .Mage::helper('adminhtml')->__('pt');
    }
}
