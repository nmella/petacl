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
* File        Addresspad.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2015 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Block_Addresspad extends Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $element->setStyle('width:2em;')
            ->setName($element->getName() . '[]');

        if ($element->getValue()) {
            $values = explode(',', $element->getValue());
        } else {
            $values = array();
        }

        /*
        [surcharge] 		=> 0
        [surcharge_label] 	=>
        1[sub_total] 		=> 619.9600
        2[taxamount] 		=> 13.2000
        3[shipping_base] 	=> 20.0000
        --------------------------------
        [grand_total] 		=> 653.1600
        */

        $order_1 = $element->setValue(isset($values[0]) ? $values[0] : null)->getElementHtml();
        $order_2 = $element->setValue(isset($values[1]) ? $values[1] : null)->getElementHtml();
        $order_3 = $element->setValue(isset($values[2]) ? $values[2] : null)->getElementHtml();

        $output = '&nbsp;&nbsp;' . $order_1 . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $order_2 . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $order_3; //.'  &nbsp; '.$order_4;
        $output .= '<br /><p style = "font-size:90%;">&nbsp;' . Mage::helper('adminhtml')->__('Top, pt') . '&nbsp;&nbsp;&nbsp;' . Mage::helper('adminhtml')->__('Bottom, pt') . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . Mage::helper('adminhtml')->__('Left, pt'); //.'&nbsp;&nbsp; '.Mage::helper('adminhtml')->__('1st').' : ';
        $output .= '</p>';

        return $output;
    }
}
