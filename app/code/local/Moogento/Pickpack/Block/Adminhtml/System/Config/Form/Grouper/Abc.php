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
* File        Abc.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


abstract class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Grouper_Abc
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_openers = array();
    protected $_openers_none_border = array();
    protected $_closers = array();
	protected function _isShipEasyInstalled() {
        return Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14');
    }
    protected function _getFieldsContainerHeader($title) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
		if(!$isShipEasyInstalled)
		{
			$html = '<tr><td colspan="' . $colspan . '" style="color:#ff0000">To access "Automated Action group" please install <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
        	$html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
        {
        	$html = '<tr class="auto-processing"><td colspan="' . $colspan . '">';
        }
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';


        return $html;
    }

    protected function _getFieldsContainerFooter() {
        $html = '</tbody></table></fieldset></td></tr>';

        return $html;
    }
    
     protected function _getFieldsContainerHeaderNoneborder($title) {
    	$isShipEasyInstalled = 1;//$this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
		if(!$isShipEasyInstalled)
		{
			//$html = '<tr><td colspan="' . $colspan . '" style="color:#ff0000">To access "Automatic Action group" please install <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
        	$html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
        {
        	$html = '<tr  class="auto-processing-none-boder"><td colspan="' . $colspan . '">';
        }
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';


        return $html;
    }


    public function render_old(Varien_Data_Form_Element_Abstract $element) {
        $html = $this->_getHeaderHtml($element);

        foreach ($element->getSortedElements() as $field) {
            if (isset($this->_openers[$field->getId()])) {
                $html .= $this->_getFieldsContainerHeader($this->_openers[$field->getId()]);
            }
            
            if (isset($this->_openers_none_border[$field->getId()])) {
                $html .= $this->_getFieldsContainerHeaderNoneborder($this->_openers[$field->getId()]);
            }

            $html .= $field->toHtml();

            if (isset($this->_closers[$field->getId()])) {
                $html .= $this->_getFieldsContainerFooter();
            }
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }
    
    public function render(Varien_Data_Form_Element_Abstract $element) {

        $dependingFields = array(
            'pickpack_options_messages_additional_action',
            'pickpack_options_messages_auto_processing_additional_action',
            'pickpack_options_picks_additional_action',
        );

        $html = $this->_getHeaderHtml($element);

        foreach ($element->getSortedElements() as $field) {

            /**
             * Additional Action Config
             */
            /**
             * Auto Processing Additional Action Config
             */
            if (
                ($field->getId() == 'pickpack_options_messages_auto_processing_additional_action') ||
                ($field->getId() == 'pickpack_options_picks_auto_processing_additional_action')
            ) {
                $html .= $this->_getFieldsContainerHeaderNoneborder('Order action');
                if (!$this->_isShipEasyInstalled()) {
                    $html .= $this->_getInstallShipEasyMessage();
                }
            }

            /**
             * Auto Processing Print Condition Config
             */
            // if (
//                 ($field->getId() == 'pickpack_options_messages_auto_processing_check') ||
//                 ($field->getId() == 'pickpack_options_picks_auto_processing_check')
//             ) {
//                 $html .= $this->_getFieldsContainerHeaderNoneborder('Order filter');
//             }
	
			//New
			if (
                ($field->getId() == 'pickpack_options_messages_auto_processing_print_flag') ||
                ($field->getId() == 'pickpack_options_picks_auto_processing_print_flag')
            ) {
                $html .= $this->_getFieldsContainerHeaderNoneborder('Order filter');
            }

            if (
                ($field->getId() == 'pickpack_options_picks_auto_processing') ||
                ($field->getId() == 'pickpack_options_messages_auto_processing')
            ) {
                $html .= $this->_getFieldsContainerHeader('Automated printing');
            }
            
            if (
                ($field->getId() == 'pickpack_options_messages_auto_processing_condition_type') ||
                ($field->getId() == 'pickpack_options_picks_auto_processing_condition_type')
            ) {
                $html .= $this->_getFieldsContainerHeaderNoneborder('Automated order processing');
            }
            
            

            if (!$this->_isShipEasyInstalled() && in_array($field->getId(), $dependingFields)) {
                $field->setValue(0);
                $field->setReadonly(true, true);
            }
            $html .= $field->toHtml();


            if (
                ($field->getId() == 'pickpack_options_messages_szy_own_value2') ||
                ($field->getId() == 'pickpack_options_picks_szy_own_value2') ||
                ($field->getId() == 'pickpack_options_messages_auto_processing_szy_own_value2') ||
                ($field->getId() == 'pickpack_options_picks_auto_processing_szy_own_value2')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            if (
                ($field->getId() == 'pickpack_options_picks_auto_processing_groupping') ||
                ($field->getId() == 'pickpack_options_messages_auto_processing_groupping')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
            }

             if (
                ($field->getId() == 'pickpack_options_picks_szy_check_own_value2') ||
                ($field->getId() == 'pickpack_options_messages_szy_check_own_value2')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }
        }

        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
