<?php
/**
 * 
 * Date: 19.12.15
 * Time: 16:21
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Customercomments extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public $customer_comments = null;
    public $subheader_start;
    public $y;

    public function showComments() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $helper = Mage::helper('pickpack');
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $notes_yn = $this->_getConfig('notes_yn', 0, false, $wonder, $storeId);
        $notes_position = $this->_getConfig('notes_position', 'yesshipping', false, $wonder, $storeId);

        if($notes_position == 'yesbox'){
            $font_family_comments = $this->_getConfig('font_family_comments', 'helvetica', false, 'general', $storeId);
            $font_style_comments = $this->_getConfig('font_style_comments', 'regular', false, 'general', $storeId);
            $font_size_comments = $this->_getConfig('font_size_comments', 9, false, 'general', $storeId);
            $font_color_comments = trim($this->_getConfig('font_color_comments', '#222222', false, 'general', $storeId));
            $fill_bkg_color_comments = $this->_getConfig('fill_bkg_comments_yn', 0, false, 'general', $storeId);
            $bkg_color_message = trim($this->_getConfig('bkg_color_comments', '#5BA638', false, 'general', $storeId));
        }
        else if ($notes_position == 'yesshipping') {
            $font_family_comments = $this->_getConfig('font_family_message', 'helvetica', false, 'general', $storeId);
            $font_style_comments = $this->_getConfig('font_style_message', 'regular', false, 'general', $storeId);
            $font_size_comments = $this->_getConfig('font_size_message', 9, false, 'general', $storeId);
            $font_color_comments = trim($this->_getConfig('font_color_message', '#222222', false, 'general', $storeId));
            $fill_bkg_color_comments = $this->_getConfig('fill_bkg_color_comments', 0, false, 'general', $storeId);
            $bkg_color_message = trim($this->_getConfig('bkg_color_message', '#5BA638', false, 'general', $storeId));
        }
        else {
            $font_family_comments = $this->_getConfig('font_family_gift_message', 'helvetica', false, 'general', $storeId);
            $font_style_comments = $this->_getConfig('font_style_gift_message', 'regular', false, 'general', $storeId);
            $font_size_comments = $this->_getConfig('font_size_gift_message', 9, false, 'general', $storeId);
            $font_color_comments = trim($this->_getConfig('font_color_gift_message', '#222222', false, 'general', $storeId));
            $fill_bkg_color_comments = $this->_getConfig('fill_bkg_gift_message_yn', 0, false, 'general', $storeId);
            $bkg_color_message = trim($this->_getConfig('bkg_color_gift_message', '#5BA638', false, 'general', $storeId));
        }

        $bkg_color_message_zend = new Zend_Pdf_Color_Html($bkg_color_message);

        if ($notes_yn == 0) $notes_position = 'no';

        $customer_comments_shown = false;
        $customer_comments_b = null;

        if ($order->getOnestepcheckoutCustomercomment() != '') {
            $this->customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getOnestepcheckoutCustomercomment())));

        } elseif ($order->getData('gomage_checkout_customer_comment')) {
            $this->customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getData('gomage_checkout_customer_comment'))));
        } elseif ($order->getHCheckoutcomment()) {
            $this->customer_comments .= $helper->__('This is a message from the customer : ') . $order->getHCheckoutcomment();
        } elseif ($order->getData('customer_comment')) {
            // custom on solo site but likely used elsewhere so left in code
            $this->customer_comments .= $order->getData('customer_comment');
        }

        if ($order->getFirecheckoutCustomerComment() != '') {
            $this->customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getFirecheckoutCustomerComment())));

        } elseif ($order->getData('firecheckoutCustomerComment')) {
            $this->customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getData('firecheckoutCustomerComment'))));
        }

        if(Mage::helper('pickpack')->isInstalled('MW_Onestepcheckout')){
            $MWorder=Mage::getModel('onestepcheckout/onestepcheckout')->getCollection()->addFieldToFilter('sales_order_id',$order->getId())->getFirstItem();
            $this->customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $MWorder->getMwCustomercommentInfo())));
        }

        if (Mage::helper('pickpack')->isInstalled('Aitoc_Aitcheckoutfields')) {
            $data_label = '';
            //$filter_by_code = 'delivery'; // <<< enter attribute code to use here
            $code = array();
            $data = array();
            $code = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getInvoiceCustomData($order->getId(), null, true);

            if (is_array($code)) {
                foreach ($code as $data) {
                    if (($data['value'] != '')) ; // && ($data['code'] == $filter_by_code))
                    {
                        if ($this->customer_comments != '') $this->customer_comments .= ' | ';
                        if ($data['label'] != '') $data_label = $data['label'] . ' : ';
                        $this->customer_comments .= $data_label . trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $data['value'])));
                    }
                }
            }
        }

        if (Mage::helper('pickpack')->isInstalled('Spletnisistemi_OrderComment')) {
            if ($order->getSpletnisistemiOrdercomment() != '') {
                $this->customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getSpletnisistemiOrdercomment())));
            }
        }
        if (Mage::helper('pickpack')->isInstalled('MageMods_OrderComment')) {

            if ($order->getComment() != '') {
                $this->customer_comments .= trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getComment())));
            }
        }
        if ($order->getBiebersdorfCustomerordercomment()) {
            $customer_comments_b = trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $order->getBiebersdorfCustomerordercomment())));
        }

        if ($notes_position != 'yesbox') {
            if (strlen($this->customer_comments) > 0) {
                $this->subheader_start -= ($generalConfig['font_size_body'] * 0.3);
                $this->y = $this->subheader_start;

                $max_comment_length = ($pageConfig['padded_right'] - $pageConfig['padded_left'] + 100);
                $max_comment_characters = stringBreak($this->customer_comments, $max_comment_length, $generalConfig['font_size_body']);
                $customer_comments_wrapped = wordwrap($this->customer_comments, $max_comment_characters, "\n", false);
                $comment_comments_array = explode("\n", $customer_comments_wrapped);
                $number_lines = count($comment_comments_array);

                $line_count = 0;
                $token = strtok($customer_comments_wrapped,"\n");
                if (($bkg_color_message_zend != '') && ($bkg_color_message_zend != '#FFFFFF')) {
                    $page->setFillColor($bkg_color_message_zend);
                    $page->setLineColor($bkg_color_message_zend);
                    $page->setLineWidth(0.5);
                    if ($fill_bkg_color_comments == 0) {
                        $page->drawLine($pageConfig['padded_left'], ($this->y + $font_size_comments + 2), $pageConfig['padded_right'], ($this->y + $font_size_comments + 2));
                        $page->drawLine($pageConfig['padded_left'], ($this->y - (($number_lines +1) * ($font_size_comments + 3)) - 6), $pageConfig['padded_right'], ($this->y - (($number_lines +1) * ($font_size_comments + 3)) - 6));
                        $page->drawLine($pageConfig['padded_left'], ($this->y + $font_size_comments + 2), $pageConfig['padded_left'], ($this->y - (($number_lines +1) * ($font_size_comments + 3)) - 6));
                        $page->drawLine($pageConfig['padded_right'], ($this->y + $font_size_comments + 2), $pageConfig['padded_right'], ($this->y - (($number_lines +1) * ($font_size_comments + 3)) - 6));
                    }
                    else
                        $page->drawRectangle($pageConfig['padded_left'], ($this->y + $font_size_comments + 2), $pageConfig['padded_right'], ($this->y - (($number_lines +1) * ($font_size_comments + 3)) - 6));
                }

                $this->_setFont($page, 'bold', $font_size_comments, $font_family_comments, $generalConfig['non_standard_characters'], $font_color_comments);
                $this->y -= 5;
                $page->drawText($helper->__('Customer Comments'), ($pageConfig['padded_left'] + 10), $this->y, 'UTF-8');
                $this->y -= ($font_size_comments + 5);
                $this->_setFont($page, $font_style_comments, ($font_size_comments - 1), $font_family_comments, $generalConfig['non_standard_characters'], $font_color_comments);

                while ($token != false) {
                    $page->drawText(trim($token), $pageConfig['padded_left'] + 10, $this->y, 'UTF-8');
                    $this->y -= $font_size_comments + 3;
                    $token = strtok("\n");
                    $line_count++;
                }
                $customer_comments_shown = true;
                $this->subheader_start = $this->y - $generalConfig['font_size_body'];
            }

            if (strlen($customer_comments_b) > 0) {
                $customer_comments_b_wrapped = wordwrap($customer_comments_b, 114, "\n", false);
                $this->y -= ($generalConfig['font_size_body'] / 2);
                if ($customer_comments_shown === false) {
	                $this->_setFont($page, 'bold', ($font_size_comments*1.2), $font_family_comments, $generalConfig['non_standard_characters'], $font_color_comments);					
                    $page->drawText($helper->__('Customer Comments'), ($pageConfig['padded_left']), $this->y, 'UTF-8');
                    $this->y -= ($font_size_comments);
                }
                $this->_setFont($page, $font_style_comments, ($font_size_comments - 1), $font_family_comments, $generalConfig['non_standard_characters'], $font_color_comments);

                $line_count = 0;
                $token = strtok($customer_comments_b_wrapped, "\n");
                while ($token != false) {
                    $page->drawText(trim($token), ($pageConfig['padded_left']), $this->y, 'UTF-8');
                    $this->y -= $font_size_comments;
                    $token = strtok("\n");
                    $line_count++;
                }
                $this->subheader_start = $this->y - $generalConfig['font_size_body'];
            }
        }
    }
}