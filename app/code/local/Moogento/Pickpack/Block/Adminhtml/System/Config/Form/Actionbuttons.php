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
* File        Actions.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Actionbuttons
    extends Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Actions
{
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $html = $this->_getHeaderHtml($element);
 
        foreach ($element->getSortedElements() as $field) {
            if (
                ($field->getId() == 'pickpack_options_action_menu_show_pdf_label_cn22')
            ) {
                if(!(Mage::helper('pickpack')->isInstalled('Moogento_Cn22')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Cn22',' ',1);
            }
            
            if (
                ($field->getId() == 'pickpack_options_action_menu_show_pdf_trolleybox')
            ) {
                if(!(Mage::helper('pickpack')->isInstalled('Moogento_Trolleybox')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Trolleybox',' ',1);
            }
			//////////////////group for product separated//////////////////////////////
			
            $html .= $field->toHtml();
			
             if (
                ($field->getId() == 'pickpack_options_action_menu_show_pdf_label_cn22')
            ) {
                if(!(Mage::helper('pickpack')->isInstalled('Moogento_Cn22')))
                    $html .= $this->_getFieldsContainerFooter();
            }
            
            if (
                ($field->getId() == 'pickpack_options_action_menu_show_pdf_trolleybox')
            ) {
                if(!(Mage::helper('pickpack')->isInstalled('Moogento_Trolleybox')))
					 $html .= $this->_getFieldsContainerFooter();
            }
        }
		
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
