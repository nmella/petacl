<?php

class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Field extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _decorateRowHtml($element, $html) {
//        if ($element->getHtmlId() == 'pickpack_options_wonder_invoice_product_sku_yn') {
//            var_dump($element);die();
//        }
        if(is_object($element->getFieldConfig()))
        	return '<tr class="' . $element->getFieldConfig()->row_css . '" id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
        else
        	return '<tr class="' . '' . '" id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }
}