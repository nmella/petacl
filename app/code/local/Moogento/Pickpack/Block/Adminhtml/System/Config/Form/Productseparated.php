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


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Productseparated
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    
    /*
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
        return Mage::helper('pickpack')->isShipEasyInstalled();
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
        $html = '/div>';

        return $html;
    }
    
    */
    
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
        return Mage::helper('pickpack')->isInstalled('Moogento_Automation');
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
            $html = '<tr><td colspan="' . $colspan . '" ><ul class="addon_features">';
            $html .= '<li class="addon_features_title"><a href="https://moogento.com/automation?utm_source=install&utm_medium=config&utm_campaign=pickpack_connector">Install Automation</a> to activate these advanced features:</li>';
            $html .= '<li class="addon_features_subtitle">&bull; &nbsp;Auto-create PDFs based on custom rules</li>';
            $html .= "<li>- - &nbsp;Send the PDFs to email, local filesystem, or remote FTP</li>";
            $html .= "<li>- - &nbsp;Set a 'print once' filter</li>";
            $html .= "<li>- - &nbsp;Set a filter to not auto-print orders with out-of-stock items</li>";
            $html .= "<li>- - &nbsp;Set a filter to only process orders that match a custom shipEasy attribute</li>";
            $html .= "<li>- - &nbsp;Set option to only auto-print on certain days</li>";
            $html .= "<li>- - &nbsp;Add additional filters based on Order Status, Shipping Method, or Product Attribute</li>";
            $html .= "<li>- - &nbsp;Set up to 3 actions - eg. print express shipments immediatey to a specific printer</li>";
            $html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. reduce training : new orders just print out)</li>';

            $html .= '<li class="addon_features_subtitle">&bull; &nbsp;Auto-change order statuses</li>';
            $html .= "<li>- - &nbsp;Apply at a specific time of day</li>";
            $html .= "<li>- - &nbsp;Apply to specific order statuses</li>";
            $html .= "<li>- - &nbsp;Apply to specific shipEasy flags</li>";
            $html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. auto-mark orders as Shipped when they\'ve been flagged as QA\'d and Packed)</li>';
            $html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. auto-mark Shipped orders as Archived at the end of the day)</li>';
            $html .= '</ul></td></tr>';
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
            // For Sku
            if (
                ($field->getId() == 'pickpack_options_product_separated_pickpack_sku_yn_separated')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Sku','sku_text_grouped');
            }
            //for product name
            if (
                ($field->getId() == 'pickpack_options_product_separated_pickpack_name_yn_separated')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Name','name_text_grouped');
            }
            
            //for product type
            if (
                ($field->getId() == 'pickpack_options_product_separated_pickpack_type_yn_separated')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Type','type_text_grouped');
            }
            //for product total qty
            if (
                ($field->getId() == 'pickpack_options_product_separated_pickpack_title_total_qty')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Qty Total','total_qty_text_grouped');
            }
            //for order ID
            if (
                ($field->getId() == 'pickpack_options_product_separated_show_order_id')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Order ID','orderId_text_grouped');
            }
            //for customer name
            if (
                ($field->getId() == 'pickpack_options_product_separated_show_customer_name')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Customer Name','namecus_text_grouped');
            }
            //for order date
            if (
                ($field->getId() == 'pickpack_options_product_separated_show_order_date')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Order Date','orderdate_text_grouped');
            }
            
            //for order email
            if (
                ($field->getId() == 'pickpack_options_product_separated_show_customer_email')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Order Email','orderemail_text_grouped');
            }
            
            //for order phone
            if (
                ($field->getId() == 'pickpack_options_product_separated_show_customer_phone')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Order Phone','orderphone_text_grouped');
            }
            
            //for order qty
            if (
                ($field->getId() == 'pickpack_options_product_separated_show_product_qty_in_order')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Order Qty','orderqty_text_grouped');
            }
            
            //for order tickbox
            if (
                ($field->getId() == 'pickpack_options_product_separated_tickbox_yn')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Tickbox','tickbox_text_grouped');
            }
            
             if (
                ($field->getId() == 'pickpack_options_product_separated_heading_manual_action') 
            ) {
//                 $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_manual_printing');
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group_manual_printing');
            }
            
             //Automated processing 1
            if (
                ($field->getId() == 'pickpack_options_product_separated_autoprocess_description')
            ) {
                //Turn this line on when need
//                 $html .= $this->_getFieldsContainerHeader('Automated processing');
                 $html .=$this->_getFieldsContainerHeaderManualWithID('Automated processing','product_separated_description');
            }
            
             if (
                ($field->getId() == 'pickpack_options_product_separated_enable_auto_processing') 
            ) {
                $html .=$this->_getFieldsContainerHeaderManual('Main control');
            }
            
            $html .= $field->toHtml();
            
            //close custom weight
            
            ////////////////Close group product separated//////////////////////////////
            // close for sku
            if (
                ($field->getId() == 'pickpack_options_product_separated_pickpack_sku_Xpos_separated')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            //close for name
            if (
                ($field->getId() == 'pickpack_options_product_separated_pickpack_name_Xpos_separated')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            //close for type
            if (
                ($field->getId() == 'pickpack_options_product_separated_pickpack_type_Xpos_separated')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            //close for total qty
            if (
                ($field->getId() == 'pickpack_options_product_separated_pickpack_position_total_qty')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            //close for order ID
            if (
                ($field->getId() == 'pickpack_options_product_separated_order_id_Xpos')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            //close for customer name
            if (
                ($field->getId() == 'pickpack_options_product_separated_customer_name_Xpos')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            //close for order date
            if (
                ($field->getId() == 'pickpack_options_product_separated_order_date_Xpos')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            
            //close for order email
            if (
                ($field->getId() == 'pickpack_options_product_separated_customer_email_Xpos')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            
            //close for order phone
            if (
                ($field->getId() == 'pickpack_options_product_separated_customer_phone_Xpos')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            
            //close for order qty
            if (
                ($field->getId() == 'pickpack_options_product_separated_product_qty_in_order_Xpos')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            
            //close for tickbox
            if (
                ($field->getId() == 'pickpack_options_product_separated_tickbox_width')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }
            
            // Close Additional Action Config 1      
            if (
                ($field->getId() == 'pickpack_options_product_separated_auto_processing_groupping')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
            }
        }

        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
