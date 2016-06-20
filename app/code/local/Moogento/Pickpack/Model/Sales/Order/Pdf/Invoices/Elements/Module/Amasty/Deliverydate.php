<?php
/**
 * 
 * Date: 20.12.15
 * Time: 14:53
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Amasty_Deliverydate extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Abstract
{
    public $subheader_start;
    public $y;

    public function showDeliveryDate() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $currentStore = $order->getStoreId();
        $generalConfig = $this->getGeneralConfig();

        if (Mage::getStoreConfig('amdeliverydate/general/enabled', $currentStore)) {
            $currentStore = $order->getStoreId();
            $fields = Mage::helper('amdeliverydate')->whatShow('invoice_pdf', $currentStore, 'include');
            $shipment_fields = Mage::helper('amdeliverydate')->whatShow('shipment_pdf', $currentStore, 'include');
            if (is_array($fields) && (!empty($fields))) {
                $deliveryDate = Mage::getModel('amdeliverydate/deliverydate');
                $deliveryDate->load($order->getId(), 'order_id');
                $list = array();
                foreach ($fields as $field) {
                    $value = $deliveryDate->getData($field);
                    if ('date' == $field) {
                        $label = 'Approximate Shipping Date';
                        if ('0000-00-00' != $value) {
                            $format = Mage::app()->getLocale()->getDateTimeFormat(
                                Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
                            );
                            $format = trim(str_replace(array('m', 'a', 'H', ':', 'h', 's'), '', $format));
                            $value = Mage::app()->getLocale()->date($value, Varien_Date::DATE_INTERNAL_FORMAT, null, false)->toString($format);
                        } else {
                            $value = '';
                        }
                    } elseif ('time' == $field) {
                        $label = 'Delivery Time Interval';
                    } elseif ('comment' == $field) {
                        $label = 'Customer Comments';
                        $value = htmlentities(preg_replace('/\$/', '\\\$', $value), ENT_COMPAT, "UTF-8");
                        $text = str_replace(array("\r\n", "\n", "\r"), '~~~', $value);
                        $value = array();

                        foreach (explode('~~~', $text) as $str) {
                            foreach (Mage::helper('core/string')->str_split($str, 120, true, true) as $part) {
                                if (empty($part)) {
                                    continue;
                                }
                                $value[] = $part;
                            }
                        }
                    }

                    if (is_array($value)) {
                        $list[$label] = $value;
                    } elseif ($value) {
                        $list[$label] = $value;
                    }
                }

                $this->y -= ($generalConfig['font_size_body'] + 4);
                $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText('Delivery date', ($this->addressXY[0]), $this->y);
                $this->y -= ($generalConfig['font_size_body'] + 3);
                $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $line_count = 0;

                $this->subheader_start -= (($generalConfig['font_size_body']) * ($line_count + 2));
                if (!empty($list)) {
                    foreach ($list as $label => $value) {
                        if (is_array($value)) {
                            $page->drawText($label . ': ', $this->addressXY[0], $this->y, 'UTF-8');
                            foreach ($value as $str) {
                                $page->drawText($str, $this->addressXY[0] + 160, $this->y, 'UTF-8');
                                $this->y -= 10;
                                $line_count++;
                            }
                        } else {
                            $page->drawText($label . ': ', $this->addressXY[0], $this->y, 'UTF-8');
                            $page->drawText($value, $this->addressXY[0] + 160, $this->y, 'UTF-8');
                            $this->y -= 10;
                            $line_count++;
                        }
                    }
                }
                $this->subheader_start -= (($generalConfig['font_size_body']) * ($line_count + 2));
            }
        }
    }
}