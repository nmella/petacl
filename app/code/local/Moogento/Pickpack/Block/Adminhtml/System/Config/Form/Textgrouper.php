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
* File        Textgrouper.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Textgrouper
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _isShipEasyInstalled() {
        return Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14');
    }
	
	 protected function _getFieldsContainerHeaderWithClassAndStatus($title,$class,$status) {       
       $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;     

        if($status == 1)
            $html = '<tr style="display:none"><td colspan="' . $colspan . '">';
        else
            $html = '<tr class="column_config '.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }
    protected function _getInstallShipEasyMessage() {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        $html = '<tr><td colspan="' . $colspan . '" style="color:#ff0000"><b>Advanced Features</b><br/> To enable automated features, please install <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
        return $html;
    }

    protected function _getFieldsContainerHeaderManual($title) {
    	$isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
		if(!$isShipEasyInstalled) {
            $html = '<tr><td colspan="' . $colspan . '" ><ul class="addon_features">';
            $html .= '<li class="addon_features_title"><a href="https://moogento.com/magento-order-shipping-processing?utm_source=install&utm_medium=config&utm_campaign=pickpack_connector">Install shipEasy</a> to activate these advanced features:</li>';
            $html .= '<li class="addon_features_subtitle">&bull; &nbsp;Auto-set custom flag columns columns in Order Grid, when making PDFs</li>';
            $html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. a \'Printed\' column, to make sure you only ship orders once)</li>';
            $html .= '<li class="addon_features_subtitle">&bull; Auto-set Order Status when making PDFs</li>';
            $html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. set the status to \'Packing your order\' - keep your customers informed and reduce support requests)</li>';
            $html .= '</ul></td></tr>';
            $html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
        	$html = '<tr class="manual-printing"><td colspan="' . $colspan . '">';
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }
    
    protected function _getFieldsContainerHeader($title) {
        $html = '';
    	$isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
		if(!$isShipEasyInstalled)
        	$html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        else
        	$html = '<tr class="auto-processing"><td colspan="' . $colspan . '">';
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }
    
    protected function _getFieldsContainerHeaderWithClass($title,$class) {    	
		$isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;		
		$html = '<tr class="auto-processing '.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }
    
    protected function _getFieldsContainerHeaderNoneborder($title) {
        $html = '';
    	$isShipEasyInstalled = 1;//$this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
		if(!$isShipEasyInstalled)
        	$html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        else
        	$html = '<tr  class="auto-processing-none-boder"><td colspan="' . $colspan . '">';
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';

        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';

        return $html;
    }
    
    protected function _getFieldsContainerFooter() {
        $html = '</tbody></table></fieldset></td></tr>';
        return $html;
    }
     protected function _getFieldsContainerFooter2() {
        $html = '<tr class="text_padding" style="height:32px"><td></td></tr></tbody></table></fieldset></td></tr>';
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
            if ($field->getId() == 'pickpack_options_general_font_family_header') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Picklist Title','subheadings text-format customise_fonts_and_colours_items');
            elseif ($field->getId() == 'pickpack_options_general_font_family_subtitles') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Titlebars','subheadings text-format customise_fonts_and_colours_items');
            elseif ($field->getId() == 'pickpack_options_general_font_family_company')
                $html .= $this->_getFieldsContainerHeaderWithClass('Company address','subheadings text-format customise_fonts_and_colours_items');
            elseif ($field->getId() == 'pickpack_options_general_font_family_body') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Page body','subheadings text-format customise_fonts_and_colours_items');
            elseif ($field->getId() == 'pickpack_options_general_font_family_message') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Message-box (under shipping details)','subheadings text-format customise_fonts_and_colours_items');
            elseif ($field->getId() == 'pickpack_options_general_font_family_gift_message') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Message-box (under products)','subheadings text-format customise_fonts_and_colours_items');
            elseif ($field->getId() == 'pickpack_options_general_font_family_comments') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Message-box (movable)','subheadings text-format customise_fonts_and_colours_items');
            elseif ($field->getId() == 'pickpack_options_general_gift_override_yn')
                $html .= $this->_getFieldsContainerHeaderWithClass('Gift message overrides','subheadings text-format customise_fonts_and_colours_items message-box-movable-gift-override');
            // elseif ($field->getId() == 'pickpack_options_general_gift_override_yn')
            //     $html .= $this->_getFieldsContainerHeaderWithClass('Message-box (gift messages specifically)','subheadings text-format customise_fonts_and_colours_items');
            elseif ($field->getId() == 'pickpack_options_label_font_family_label') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Address label','subheadings text-format');
			elseif ($field->getId() == 'pickpack_options_label_zebra_manual_description')
                $html .= $this->_getFieldsContainerHeaderManual('Manual printing');
			elseif ( ($field->getId() == 'pickpack_options_label_zebra_show_customs_declaration')
				     && !(Mage::helper('pickpack')->isInstalled('Moogento_Cn22')) )
                $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Cn22',' ',1);
            elseif ($field->getId() == 'pickpack_options_label_zebra_font_family_label') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Shipping Address','subheadings text-format');
            elseif ($field->getId() == 'pickpack_options_label_zebra_show_product_list') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Product List','subheadings text-format');
            elseif ($field->getId() == 'pickpack_options_label_zebra_label_return_address_yn') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Return Address','subheadings text-format');
			elseif ($field->getId() == 'pickpack_options_label_zebra_shelving_real_yn') 
                $html .= $this->_getFieldsContainerHeaderWithClass('Custom attribute','custom_attribute_zebra_text_grouped');

            $html .= $field->toHtml();

            if (
				($field->getId() == 'pickpack_options_label_font_color_label') ||
				($field->getId() == 'pickpack_options_general_bkg_color_gift_message') ||
                ($field->getId() == 'pickpack_options_general_font_color_body') ||
				($field->getId() == 'pickpack_options_general_bkg_color_message') ||
				
				($field->getId() == 'pickpack_options_general_font_color_company') ||
                ($field->getId() == 'pickpack_options_general_bkg_color_comments') ||
                ($field->getId() == 'pickpack_options_general_font_size_gift_override') ||
				($field->getId() == 'pickpack_options_label_zebra_font_color_label') ||
				($field->getId() == 'pickpack_options_label_zebra_font_color_product') ||
				($field->getId() == 'pickpack_options_label_zebra_font_color_return_label_side') ||
				($field->getId() =='pickpack_options_label_zebra_pricesN_shelfX')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

		   //close for shipeasy connection 
           if ($field->getId() == 'pickpack_options_label_zebra_szy_own_value3')
                $html .= $this->_getFieldsContainerFooter();
		   elseif (
                ($field->getId() == 'pickpack_options_general_font_color_subtitles') ||
                ($field->getId() == 'pickpack_options_general_font_color_header')
            )
                $html .= $this->_getFieldsContainerFooter2();
			elseif ( ($field->getId() == 'pickpack_options_label_zebra_show_customs_declaration_nudge')
                && !(Mage::helper('pickpack')->isInstalled('Moogento_Cn22')) )
                    $html .= $this->_getFieldsContainerFooter2();
        }

        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
