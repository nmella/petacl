<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Fullpayment extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public function showFullPayment() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $full_payment = $order->getPayment()->getData('additional_data');
        if (($full_payment == '') || strpos($full_payment,'{{pdf_row_separator}}')==false){
            $payment_order = $this->getPaymentOrder($order);
            Mage::unregister('current_order');
            Mage::register('current_order', $order);
            $full_payment = Mage::helper('payment')
                ->getInfoBlock($payment_order)
                ->setIsSecureMode(true)
                ->toPdf();
        }
        if(isset($full_payment) && $full_payment != ''){
            $full_payment_arr = explode('{{pdf_row_separator}}', $full_payment);
            $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'] - 2, $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);

            foreach ($full_payment_arr as $key => $payment_message) {
                $payment_message = trim($payment_message);
                $payment_message = trim(strip_tags(str_replace(array('<br/>', '<br />', '<span>', '</span>'), ' ', $payment_message)));
                if($payment_message != ''){
                    $maxWidthPage = $pageConfig['padded_right'] - $this->packingsheetConfig['pickpack_show_full_payment_nudge'][0];
                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $line_width = $this->parseString($payment_message, $font_temp, $this->generalConfig['font_size_body'] - 2); // bigger = left
                    $char_width = $line_width / strlen($payment_message);
                    $max_chars = round($maxWidthPage / $char_width);
                    if(strlen($payment_message) > $max_chars){
                        $message_arr = explode("\n", wordwrap($payment_message, $max_chars, "\n"));
                        foreach ($message_arr as $value) {
                            $page->drawText($value, $this->packingsheetConfig['pickpack_show_full_payment_nudge'][0], $this->packingsheetConfig['pickpack_show_full_payment_nudge'][1], 'UTF-8');
                            $this->packingsheetConfig['pickpack_show_full_payment_nudge'][1] -= ($this->generalConfig['font_size_body'] + 1);
                        }
                    }
                    else{
                        $page->drawText($payment_message, $this->packingsheetConfig['pickpack_show_full_payment_nudge'][0], $this->packingsheetConfig['pickpack_show_full_payment_nudge'][1], 'UTF-8');
                        $this->packingsheetConfig['pickpack_show_full_payment_nudge'][1] -= ($this->generalConfig['font_size_body'] + 1);
                    }
                }
            }
        }
    }
}