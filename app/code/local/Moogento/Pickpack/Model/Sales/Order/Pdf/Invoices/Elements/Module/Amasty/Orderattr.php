<?php
/**
 * 
 * Date: 20.12.15
 * Time: 14:53
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Amasty_Orderattr extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Abstract
{
    public $subheader_start;
    public $flag_message_after_shipping_address = 0;

    public $order_attribute_value;

    public function showAttribute() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $helper = Mage::helper('pickpack');
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $background_color_subtitles = $generalConfig['background_color_subtitles'];
        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($background_color_subtitles);

        $fill_product_header_yn = 1;

        $this->subheader_start +=$generalConfig['font_size_body'];

        $order_custom_attribute_yn = $this->_getConfig('order_custom_attribute_yn', 0, false, $wonder, $storeId);
        $order_custom_attribute_filter = $this->_getConfig('order_custom_attribute_filter', '', false, $wonder, $storeId);

        $filter_custom_attributes_array = explode("\n", $order_custom_attribute_filter);
        foreach ($filter_custom_attributes_array as $key => $value) {
            $filter_custom_attributes_array[$key] = trim($value);
        }

        if ($order_custom_attribute_yn == 1) {
            if (
                (($wonder == 'wonder_invoice') && (Mage::getStoreConfig('amorderattr/pdf/invoice') == 1))
                ||
                (($wonder == 'wonder') && (Mage::getStoreConfig('amorderattr/pdf/shipment') == 1))
            ) {
                $amas_attributes = $this->getAmasAttribute();
                if ($amas_attributes->getSize() > 0) {
                    $list =  $this->getValueOrderAttribute($amas_attributes, $filter_custom_attributes_array, $order);

                    $flat_print_separator_line = 0;
                    foreach ($list as $label => $value) {
                        if (is_array($value) && !(empty($value))) {
                            if ($flat_print_separator_line == 0) {
                                if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                    $page->setFillColor($background_color_subtitles_zend);
                                    $page->setLineColor($background_color_subtitles_zend);
                                    $page->setLineWidth(0.5);
                                    if ($fill_product_header_yn == 1) {
                                        $page->drawLine($pageConfig['padded_left'], $this->subheader_start - $generalConfig['font_size_body'], $pageConfig['padded_right'], $this->subheader_start - $generalConfig['font_size_body']);
                                        $this->subheader_start -= 30;
                                    }
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    $flat_print_separator_line = 1;
                                }
                            }
                            if($generalConfig['shipment_details_bold_label_yn'] == 1){
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                                $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            }else{
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            }
                            $page->drawText($helper->__($label) . ': ', $pageConfig['padded_left'], $this->subheader_start, 'UTF-8');
                            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            $label_length = round($this->parseString($helper->__($label), $font_temp, ($generalConfig['font_size_body'])));
                            $count_line = 0;
                            foreach ($value as $str) {
                                $page->drawText(trim($str), $pageConfig['padded_left'] + $label_length + 6, $this->subheader_start, 'UTF-8');
                                $count_line++;
                                if (count($value) > $count_line){
                                    $this->subheader_start -= 1.5 * $generalConfig['font_size_body'];
                                }
                            }
                            if(count($list) > 1 || $this->flag_message_after_shipping_address != 1)
                                $this->subheader_start -= 1.5 * $generalConfig['font_size_body'];
                        } else {
                            if (is_string($value) && strlen(trim($value)) > 0) {
                                if ($flat_print_separator_line == 0) {
                                    if (strtoupper($background_color_subtitles) != '#FFFFFF') {
                                        $page->setFillColor($background_color_subtitles_zend);
                                        $page->setLineColor($background_color_subtitles_zend);
                                        $page->setLineWidth(0.5);
                                        if ($fill_product_header_yn == 1) {
                                            $page->drawLine($pageConfig['padded_left'], $this->subheader_start - $generalConfig['font_size_body'], $pageConfig['padded_right'], $this->subheader_start - $generalConfig['font_size_body']);
                                            $this->subheader_start -= 30;
                                        }
                                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                        $flat_print_separator_line = 1;
                                    }
                                }
                                if($generalConfig['shipment_details_bold_label_yn'] == 1){
                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                }else{
                                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                }

                                $page->drawText($helper->__($label) . ': ', $pageConfig['padded_left'], $this->subheader_start, 'UTF-8');
                                $label_length = round($this->parseString($helper->__($label), $font_temp, ($generalConfig['font_size_body'])));
                                $amorderattrX = $pageConfig['padded_left'] + $label_length + 10;

                                if($label_length > $pageConfig['padded_right'] - 100){
                                    $amorderattrX = $pageConfig['padded_left'];
                                    $this->subheader_start -= 1.5 * $generalConfig['font_size_body'];
                                }

                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $page->drawText(trim($value), $amorderattrX, $this->subheader_start, 'UTF-8');
                                $this->subheader_start -= 1.5 * $generalConfig['font_size_body'];
                            }
                        }
                    }
                }
            }
        }
    }

    private function getAmasAttribute() {
        $amas_attributes = Mage::getModel('eav/entity_attribute')->getCollection();
        $amas_attributes->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
        $amas_attributes->addFieldToFilter('include_pdf', 1);
        $amas_attributes->getSelect()->order('checkout_step');
        $amas_attributes->getSelect()->order('sorting_order');
        return $amas_attributes;
    }

    private function getValueOrderAttribute($amas_attributes, $filter_custom_attributes_array, $order) {
        $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
        $list = array();
        foreach ($amas_attributes as $attribute) {
            $check_label = $attribute->getData('attribute_code');
            if ((in_array($check_label, $filter_custom_attributes_array, true))) {
                continue;
            } else {
                $currentStore = $order->getStoreId();
                $storeIds = explode(',', $attribute->getData('store_ids'));
                if (!in_array($currentStore, $storeIds) && !in_array(0, $storeIds)) {
                    continue;
                }

                $value = '';

                switch ($attribute->getFrontendInput()) {
                    case 'select':
                        $options = $attribute->getSource()->getAllOptions(true, true);
                        foreach ($options as $option) {
                            if ($option['value'] == $orderAttributes->getData($attribute->getAttributeCode())) {
                                $value = $option['label'];
                                break;
                            }
                        }

                        break;
                    case 'date':
                        $value = $orderAttributes->getData($attribute->getAttributeCode());
                        $format = Mage::app()->getLocale()->getDateTimeFormat(
                            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                        );
                        if ('time' == $attribute->getNote()) {
                            $value = Mage::app()->getLocale()->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT, null, false)->toString($format);
                        } else {
                            $format = trim(str_replace(array('m', 'a', 'H', ':', 'h', 's'), '', $format));
                            $value = Mage::app()->getLocale()->date($value, Varien_Date::DATE_INTERNAL_FORMAT, null, false)->toString($format);
                        }
                        break;
                    case 'checkboxes':
                        $options = $attribute->getSource()->getAllOptions(true, true);
                        $checkboxValues = explode(',', $orderAttributes->getData($attribute->getAttributeCode()));
                        foreach ($options as $option) {
                            if (in_array($option['value'], $checkboxValues)) {
                                $value[] = $option['label'];
                            }
                        }
                        $value = implode(', ', $value);
                        break;
                    case 'boolean':
                        $value = $orderAttributes->getData($attribute->getAttributeCode()) ? 'Yes' : 'No';
                        $value = Mage::helper('catalog')->__($value);
                        break;
                    case 'textarea':
                        $text = $orderAttributes->getData($attribute->getAttributeCode());
                        $text = str_replace(array("\r\n", "\n", "\r"), '~~~', $text);
                        $value = array();
                        foreach (explode('~~~', $text) as $str) {
                            foreach (Mage::helper('core/string')->str_split($str, 99, true, true) as $part) {
                                if (empty($part)) {
                                    continue;
                                }
                                $value[] = $part;
                            }
                        }
                        break;
                    default:
                        $value = $orderAttributes->getData($attribute->getAttributeCode());
                        break;
                }

                if (is_array($value)) {
                    $list[$attribute->getFrontendLabel()] = $value;
                } else {
                    $list[$attribute->getFrontendLabel()] = str_replace('$', '\$', $value);
                }
            }
        }
        return $list;
    }
}