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
* File        Abstract.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


abstract class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Grouper_Abstract
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_openers = array();
    protected $_openers_none_border = array();
    protected $_closers = array();
    /*
    protected function _isShipEasyInstalled() {
        return Mage::helper('pickpack')->isShipEasyInstalled();
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
    
    protected function _getGroupContainerHeaderWithClass($class) {       
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;     
        
        $html = '<tr style="display:none class="'.$class.'"><td colspan="' . $colspan . '">';
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

    protected function _getInstallShipEasyMessage() {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

        $html = '<tr><td colspan="' . $colspan . '" style="color:#ff0000"><b>Advanced Features</b><br/> To enable automated features, please install <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
        return $html;
    }
    
    protected function _getFieldsContainerHeader($title) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        if(!$isShipEasyInstalled)
        {
            $html = '<tr><td colspan="' . $colspan . '" style="color:#ff0000"><b>Advanced Features</b><br/> To enable automated features, please install <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
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
            'pickpack_options_messages_additional_action',
            'pickpack_options_messages_auto_processing_additional_action',
            'pickpack_options_picks_additional_action',
        );

        $html = $this->_getHeaderHtml($element);

        foreach ($element->getSortedElements() as $field) {

           
           
           if (
            ($field->getId() == 'pickpack_options_picks_pickpack_warehouse') ||
            ($field->getId() == 'pickpack_options_messages_combined_warehouse_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Warehouse','warehouse_column',1);
                // else
                //     $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Warehouse','warehouse_column',0);
            } 
            
            if (
            ($field->getId() == 'pickpack_options_picks_pickpack_giftwrap')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap')) &&  !(Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Giftwrap','giftwrap message',1);
            } 
            
            if (
                ($field->getId() == 'pickpack_options_messages_pickpack_pickproductbarcode_combined_yn') 
                //  ||($field->getId() == 'pickpack_options_trolleybox_picklist_combined_sku_yn')
            ) {
                $html .= $this->_getFieldsContainerHeader2('Show');
            }
            
           
            if (!$this->_isShipEasyInstalled() && in_array($field->getId(), $dependingFields)) {
                $field->setValue(0);
                $field->setReadonly(true, true);
            }
            
            //New TODO

            if (
                ($field->getId() == 'pickpack_options_messages_autoprocess_description')
            ) {
                //Turn this line on when need
                    $html .=$this->_getFieldsContainerHeaderManualWithID('Automated processing','messages_description');
            }
            
            if (
                ($field->getId() == 'pickpack_options_picks_autoprocess_description') 
            ) {
                    $html .=$this->_getFieldsContainerHeaderManualWithID('Automated processing','picks_description');
            }
            
             if (
                ($field->getId() == 'pickpack_options_picks_heading_manual_action') ||
                ($field->getId() == 'pickpack_options_messages_heading_manual_action')
            ) {
//                 $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_manual_printing');
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group_manual_printing');
            }
            
            if (
                ($field->getId() == 'pickpack_options_picks_enable_auto_processing') ||
                ($field->getId() == 'pickpack_options_messages_enable_auto_processing')
            ) {
//              $html .= $this->_getFieldsContainerFooter();
//New TODO
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_manual_printing');
//              $html .=$this->_getFieldsContainerHeaderManual('Automated processing');
                $html .=$this->_getFieldsContainerHeaderManual('Main control');
            }
            
             //Automated processing 1
            if (
                ($field->getId() == 'pickpack_options_picks_auto_processing') ||
                ($field->getId() == 'pickpack_options_messages_auto_processing')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                //Turn this line on when need
//                 $html .= $this->_getFieldsContainerHeader('Automated processing');
//                 $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Automated processing',' ',0);
                $html .=$this->_getFieldsContainerHeaderAuto('#1');
            }
           
            //Automated processing 2
            if (
                ($field->getId() == 'pickpack_options_picks_auto_processing_2nd') ||
                ($field->getId() == 'pickpack_options_messages_auto_processing_2nd')
            ) {
                //Turn this line on when need
                    $html .= $this->_getFieldsContainerFooter();
//                  $html .= $this->_getFieldsContainerHeader('Automated processing 2');
//                 $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Automated processing',' ',0);
                    $html .=$this->_getFieldsContainerHeaderAuto('#2');
            }
//             
//             
            //Automated processing 3
            if (
                ($field->getId() == 'pickpack_options_picks_auto_processing_3rd') ||
                ($field->getId() == 'pickpack_options_messages_auto_processing_3rd')
            ) {
                //Turn this line on when need
                    $html .= $this->_getFieldsContainerFooter();
//                  $html .= $this->_getFieldsContainerHeader('Automated processing 3');
//                  $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Automated processing Test',' ',0);
                    $html .=$this->_getFieldsContainerHeaderAuto('#3');
            }
            
            
          //   
//             //Autoprocessing order filter
//             if (
//                 ($field->getId() == 'pickpack_options_messages_auto_processing_print_flag') ||
//                 ($field->getId() == 'pickpack_options_picks_auto_processing_print_flag')
//             ) {
//                 $html .= $this->_getFieldsContainerHeaderNoneborder('Order filter');
//             }
//             
//             //Autoprocessing order filter
//             if (
//                 ($field->getId() == 'pickpack_options_messages_auto_processing_print_flag_2nd') ||
//                 ($field->getId() == 'pickpack_options_picks_auto_processing_print_flag_2nd')
//             ) {
//                 $html .= $this->_getFieldsContainerHeaderNoneborder('Order filter');
//             } 
//             
//             //Autoprocessing order filter
//             if (
//                 ($field->getId() == 'pickpack_options_messages_auto_processing_print_flag_3rd') ||
//                 ($field->getId() == 'pickpack_options_picks_auto_processing_print_flag_3rd')
//             ) {
//                 $html .= $this->_getFieldsContainerHeaderNoneborder('Order filter');
//             }
//             
//             
//             //Automated order processing 1
//             if (
//                 ($field->getId() == 'pickpack_options_messages_auto_processing_condition_type') ||
//                 ($field->getId() == 'pickpack_options_picks_auto_processing_condition_type')
//             ) {
//                  $html .= $this->_getFieldsContainerFooter();
//                 $html .= $this->_getFieldsContainerHeaderNoneborder('Automated order processing');
//             }
// 
// 
//          //Automated order processing 2
//             if (
//                 ($field->getId() == 'pickpack_options_messages_auto_processing_condition_type_2nd') ||
//                 ($field->getId() == 'pickpack_options_picks_auto_processing_condition_type_2nd')
//             ) {
//              $html .= $this->_getFieldsContainerFooter();
//                 $html .= $this->_getFieldsContainerHeaderNoneborder('Automated order processing');
//             }
//             
//             //Automated order processing 3
//             if (
//                 ($field->getId() == 'pickpack_options_messages_auto_processing_condition_type_3rd') ||
//                 ($field->getId() == 'pickpack_options_picks_auto_processing_condition_type_3rd')
//             ) {
//              $html .= $this->_getFieldsContainerFooter();
//                 $html .= $this->_getFieldsContainerHeaderNoneborder('Automated order processing');
//             }
//             
//             
//             if (!$this->_isShipEasyInstalled() && in_array($field->getId(), $dependingFields)) {
//                 $field->setValue(0);
//                 $field->setReadonly(true, true);
//             }
            
// 
//          // Additional Action Config 1 
//          if (
//              ($field->getId() == 'pickpack_options_messages_auto_processing_additional_action') ||
//              ($field->getId() == 'pickpack_options_picks_auto_processing_additional_action')
//          ) {
//              $html .= $this->_getFieldsContainerFooter();
//              $html .= $this->_getFieldsContainerHeaderNoneborder('Order additional action');
//              // if (!$this->_isShipEasyInstalled()) {
//          //                     $html .= $this->_getInstallShipEasyMessage();
//          //                 }
//          }
//          
//          // Additional Action Config 2 
//          if (
//              ($field->getId() == 'pickpack_options_messages_auto_processing_additional_action_2nd') ||
//              ($field->getId() == 'pickpack_options_picks_auto_processing_additional_action_2nd')
//          ) {
//              $html .= $this->_getFieldsContainerFooter();
//              $html .= $this->_getFieldsContainerHeaderNoneborder('Order additional action');
//              // if (!$this->_isShipEasyInstalled()) {
//          //                     $html .= $this->_getInstallShipEasyMessage();
//          //                 }
//          }
//          
//          // Additional Action Config 3 
//          if (
//              ($field->getId() == 'pickpack_options_messages_auto_processing_additional_action_3rd') ||
//              ($field->getId() == 'pickpack_options_picks_auto_processing_additional_action_3rd')
//          ) {
// //               $html .= $this->_getFieldsContainerFooter();
//              $html .= $this->_getFieldsContainerHeaderNoneborder('Order additional action');
//              // if (!$this->_isShipEasyInstalled()) {
//          //                     $html .= $this->_getInstallShipEasyMessage();
//          //                 }
//          }
            
            
            
            $html .= $field->toHtml();

            if (
            ($field->getId() == 'pickpack_options_picks_pickpack_warehouse') ||
            ($field->getId() == 'pickpack_options_messages_combined_warehouse_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
                    $html .= $this->_getFieldsContainerFooter();
                // else
                //     $html .= $this->_getFieldsContainerFooter();
            } 
            
            if (
            ($field->getId() == 'pickpack_options_picks_pickpack_giftwrap')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap')) &&  !(Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')))
                    $html .= $this->_getFieldsContainerFooter();
            } 

            if (
                ($field->getId() == 'pickpack_options_messages_pickpack_packprint') 
                    //||($field->getId() == 'pickpack_options_trolleybox_picklist_pickpack_packprint')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

        }
        
        
        // Close Additional Action Config 1      
            if (
                ($field->getId() == 'pickpack_options_messages_auto_processing_szy_custom_value3') ||
                ($field->getId() == 'pickpack_options_picks_auto_processing_szy_custom_value3')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }
            // 
            // Close Additional Action Config 2      
            if (
                ($field->getId() == 'pickpack_options_messages_auto_processing_szy_custom_value3_2nd') ||
                ($field->getId() == 'pickpack_options_picks_auto_processing_szy_custom_value3_2nd')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }
            
            // Close Additional Action Config 3      
            if (
                ($field->getId() == 'pickpack_options_messages_auto_processing_szy_custom_value3_3rd') ||
                ($field->getId() == 'pickpack_options_picks_auto_processing_szy_custom_value3_3rd')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            
            
            if (
                ($field->getId() == 'pickpack_options_picks_auto_processing_groupping_3rd')||
                ($field->getId() == 'pickpack_options_messages_auto_processing_groupping_3rd')
                
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
                //New TODO
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
            }
            
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
