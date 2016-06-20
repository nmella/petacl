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


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Trolleybox
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
     protected function _getFieldsContainerHeader2($title) {
        
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        
        $html = '<tr class="auto-processing"><td colspan="' . $colspan . '">';
        
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';


        return $html;
    }
    
    protected function _getFieldsContainerHeaderWithClass($title,$class) {       
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;     
        $html = '<tr class="column_config '.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        
        

        return $html;
    }

    protected function _getFieldsContainerHeaderWithClassNoneborder($title,$class) {       
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;     
        $html = '<tr class="auto-processing '.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';


        return $html;
    }
    
    
    protected function _getFieldsContainerHeaderWithClassAndStatus($title,$class,$status) {       
       $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;     

        
        if($status == 1)
        {
            $html = '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
        {
            $html = '<tr class="column_config '.$class.'"><td colspan="' . $colspan . '">';
        }
        
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }
    
    protected function _getGroupContainerHeaderWithClass($class) {       
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;     
        
        $html = '<tr class="'.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }
    
    protected function _isShipEasyInstalled() {
        return Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14');
    }

    protected function _getInstallShipEasyMessage() {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

        $html = '<tr><td colspan="' . $colspan . '" ><b>Advanced Features</b><br/> <span style="color:#ff0000" >To enable automated features, please install </span> <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
        return $html;
    }

    protected function _getFieldsContainerHeaderManual($title) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        if(!$isShipEasyInstalled)
        {
            $html = '<tr><td colspan="' . $colspan . '" ><b>Advanced Features</b><br/> <span style="color:#ff0000" >&nbsp;&nbsp;&nbsp; To enable automated features, please install </span> <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
            $html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
        {
            $html = '<tr class="pack_invoice_group manual-printing"><td colspan="' . $colspan . '">';
        }
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';


        return $html;
    }
    
    
    protected function _getFieldsContainerHeaderManualWithID($title,$id) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        if(!$isShipEasyInstalled)
        {
            $html = '<tr><td colspan="' . $colspan . '" ><b>Advanced Features</b><br/> <span style="color:#ff0000" >&nbsp;&nbsp;&nbsp; To enable automated features, please install </span> <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
            $html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
        {
            $html = '<tr class="pack_invoice_group manual-printing"><td colspan="' . $colspan . '">';
        }
        $html .= '<fieldset id="'.$id.'" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';


        return $html;
    }
    
    
    protected function _getFieldsContainerHeaderAuto($title) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        if(!$isShipEasyInstalled)
        {
            $html = '<tr><td colspan="' . $colspan . '" ><b>Advanced Features</b><br/> <span style="color:#ff0000" >&nbsp;&nbsp;&nbsp; To enable automated features, please install </span> <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
            $html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
        {
            $html = '<tr class="pack_invoice_group manual-printing auto-processing-printing"><td colspan="' . $colspan . '">';
        }
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default) {
            $html .= '<colgroup class="use-default" />';
        }
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';


        return $html;
    }
    
    protected function _getFieldsContainerHeader($title) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        if(!$isShipEasyInstalled)
        {
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
    
    protected function _getFieldsContainerHeaderNoneborder($title) {
        $isShipEasyInstalled = 1;//$this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        if(!$isShipEasyInstalled)
        {
            //$html = '<tr><td colspan="' . $colspan . '" style="color:#ff0000"><b>Advanced Features</b><br/> To enable automated features, please install <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
            $html .= '<tr ><td colspan="' . $colspan . '">';
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

    protected function _getFieldsContainerFooter() {
        $html = '</tbody></table></fieldset></td></tr>';

        return $html;
    }

    protected function _getFieldsContainerFooter4() {
        $html = '<tr class="text_padding" style="height:15px"><td></td></tr></tbody></table></fieldset></td></tr>';

        return $html;
    }
    

    protected function _getFieldsContainerFooter3() {
        $html = '<tr class="text_padding" style="height:32px"><td></td></tr></tbody></table></fieldset></td></tr>';

        return $html;
    }
    
     protected function _getFieldsContainerFooter2() {
        $html = '</div>';

        return $html;
    }
    
    protected function _getTextFieldsContainerFooter() {
        $html = '</div>';

        return $html;
    }
    public function render(Varien_Data_Form_Element_Abstract $element) {

        $dependingFields = array(
            'pickpack_options_wonder_invoice_additional_action',
            'pickpack_options_wonder_invoice_auto_processing_additional_action',
            'pickpack_options_wonder_additional_action',
        );

        $html = $this->_getHeaderHtml($element);

        foreach ($element->getSortedElements() as $field) {

            /**
             * Additional Action Config
             */
             
            
            //////////////////group for product separated//////////////////////////////
            
            if (
                ($field->getId() == 'trolleybox_options_trolleybox_picklist_heading_manual_action')
            ) {
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group_manual_printing');
            }

            if (
                ($field->getId() == 'trolleybox_options_trolleybox_picklist_manual_description')
            ) {
                $html .= $this->_getFieldsContainerHeaderManual('Manual printing');
            }
            $html .= $field->toHtml();
            
            //close custom weight
            
            ////////////////Close group product separated//////////////////////////////

            // if (
            //     ($field->getId() == 'trolleybox_options_trolleybox_picklist_szy_own_value3')
            //     )
            // {
            //         $html .= $this->_getFieldsContainerFooter();
            //         $html .= $this->_getFieldsContainerFooter();
            // }

            
            if (
                ($field->getId() == 'trolleybox_options_trolleybox_picklist_szy_attribute_to_update')
                )
            {
                    $html .= $this->_getFieldsContainerFooter();
                    $html .= $this->_getFieldsContainerFooter();
            }

            
        }

        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
