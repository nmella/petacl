<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Ordernote extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public $y;
    public $pageFooterHeight;
    public $flag_message_after_shipping_address = 0;
    public $subheader_start;
    public $customer_comments;
    public $order_number_display;
    public $items_header_top_firstpage;
    public $comments_y;
    public $gift_message_array = array();

    public function showTopNote() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $font_family_message = $this->_getConfig('font_family_message', 'helvetica', false, 'general', $storeId);
        $font_style_message = $this->_getConfig('font_style_message', 'italic', false, 'general', $storeId);
        $font_size_message = $this->_getConfig('font_size_message', 10, false, 'general', $storeId);
        $font_color_message = trim($this->_getConfig('font_color_message', '#222222', false, 'general', $storeId));
        $font_family_comments = $this->_getConfig('font_family_comments', 'helvetica', false, 'general', $storeId);
        $notes_title = $this->_getConfig('notes_title', '', false, $wonder, $storeId);
        $strip_comment_line_break = $this->_getConfig('strip_comment_line_break', 0, false, 'general', $storeId);
        $notes_filter_options = $this->_getConfig('notes_filter_options', '', false, $wonder, $storeId);
        $notes_filter = trim(strtolower($this->_getConfig('notes_filter', '', false, $wonder, $storeId)));
        $notes_filter = preg_replace('/^([\'"])(.*)\\1$/', '\\2', $notes_filter);
        if ($notes_filter_options != 'yestext') $notes_filter = '';

        $bkg_color_message = trim($this->_getConfig('bkg_color_message', '#5BA638', false, 'general', $storeId));
        $bkg_color_message_zend = new Zend_Pdf_Color_Html($bkg_color_message);

        if ($order->getStatusHistoryCollection(true)) {
            $notes = $order->getStatusHistoryCollection(true);
            $note_line = array();
            $note_comment_count = 0;
            $test_name = 'abcdefghij'; //10
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $test_name_length = round($this->parseString($test_name, $font_temp, ($font_size_message)));
            $pt_per_char = ($test_name_length / 10);
            $max_name_length = $pageConfig['padded_right'] -  10;
            $character_breakpoint = round(($max_name_length / $pt_per_char));
            $i = 0;
            $line_count_note = 0;
            foreach ($notes as $_item) {
                if ($notes_filter_options == 'yestext' && ($this->checkFilterNotes($_item['comment'], $notes_filter))) {
                    $_item['comment'] = '';
                }
                if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
                    $check_comments_for_gift_message_filter = $this->_getConfig('check_comments_for_gift_message_filter', 'Checkout Message', false, $this->getWonder(), $this->getStoreId());
                    $pos = strpos($_item['comment'], 'M2E Pro Notes');
                    $pos2 = strpos($_item['comment'], $check_comments_for_gift_message_filter);
                    if (($pos !== false) && ($pos2 !== false)) {
                        $start_pos1 = strlen('M2E Pro Notes') + 1;
                        $start_pos2 = strlen('Checkout Message From Buyer:') + 1;
                        $str_1 = trim(substr($_item['comment'], $start_pos1));
                        $str_2 = trim(substr($str_1, $start_pos2));
                        $this->gift_message_array['notes'][] = $str_2;
                        $_item['comment'] = '';
                    } else
                        if ($pos !== false) {
                            $_item['comment'] = str_replace('M2E Pro Notes:', '', $_item['comment']);
                        }
                }
                if(Mage::helper('pickpack')->isInstalled('Brainvire_OrderComment')){
                    if($_item['is_customer_notified'] != 0)
                        $_item['is_visible_on_front'] = 1;
                }

                if ($_item['comment'] != '' && (($notes_filter_options == 'yesfrontend' && $_item['is_visible_on_front'] == 1)
                        || ($notes_filter_options == 'no'
                            || $notes_filter_options == 'yestext'))
                ) {
                    $_item['created_at'] = date('m/d/y', strtotime($_item['created_at']));
                    if($strip_comment_line_break == 1){
                        $_item['comment'] = preg_replace('/\s+/', ' ', $_item['comment']);
                    }
                    $str = Mage::helper('pickpack')->__('Because the Order currency is different from the Store currency, the conversion from');
                    $str_to = Mage::helper('pickpack')->__('Prices converted from');
                    $_item['comment'] = str_replace($str,$str_to,$_item['comment']);
                    $order_currency_code = $order->getOrderCurrencyCode();
                    $store_currency_code = $order->getStore()->getCurrentCurrencyCode();
                    $str = Mage::helper('pickpack')->__('"'.$order_currency_code.'" to "'.$store_currency_code.'"');
                    $str_to = Mage::helper('pickpack')->__('"'.$store_currency_code.'" to "'.$order_currency_code.'"');
                    $_item['comment'] = str_replace($str,$str_to,$_item['comment']);
                    preg_match_all('/\d+\.\d+/',  $_item['comment'], $matches);
                    $num = $matches[0];
                    if(isset($num[0]) && ($num[0] > 0)) {
	                    $str = Mage::helper('pickpack')->__('was performed using '. (float)$num[0] .' as a rate');
	                    $str_to = Mage::helper('pickpack')->__('@ '.(float)$num[0]);
	                    $_item['comment'] = str_replace($str,$str_to,$_item['comment']);
					}
					$note_line[$i]['date'] = $_item['created_at'];
					$note_line[$i]['comment'] = $_item['created_at'] . ' : ' . $_item['comment'];;
                    if ($note_line[$i]['comment'] != '') $note_comment_count = 1;
                    $note_line[$i]['is_visible_on_front'] = $_item['is_visible_on_front'];
                    $note_line_break = explode("\r\n", $note_line[$i]['comment']);
                    foreach ($note_line_break as $note_line_each) {
                        if ($note_line_each != "") {
                            $note_line_each = trim($note_line_each);
                            $note_line_wr = wordwrap($note_line_each, $character_breakpoint, "\n", false);
                            $comment_array = explode("\n", $note_line_wr);
                            $line_count_note += count($comment_array);
                            unset($comment_array);
                        }
                    }
                    $i++;
                }
            }

            if ($note_comment_count > 0) {
                $this->flag_message_after_shipping_address = 1;
                $this->subheader_start = $this->subheader_start - ($generalConfig['font_size_body'] * 1.25);
                $this->y = $this->subheader_start;
				$max_name_length = $pageConfig['padded_right'];

                if (($bkg_color_message_zend != '') && ($bkg_color_message_zend != '#FFFFFF')) {
                    $page->setFillColor($bkg_color_message_zend);
                    $page->setLineColor($bkg_color_message_zend);
                    $page->setLineWidth(0.5);
					$page->drawRectangle($pageConfig['padded_left'], ($this->y + $font_size_message + 2), $max_name_length, ($this->y - (($line_count_note + 1) * ($font_size_message + 3)) - 6));
                }
                $this->_setFont($page, 'bold', ($font_size_message), $font_family_message, $generalConfig['non_standard_characters'], $font_color_message);
                $this->y -= 5;
                $page->drawText(Mage::helper('sales')->__($notes_title), ($pageConfig['padded_left'] + 10), $this->y, 'UTF-8');
                $this->y -= ($font_size_message + 5);
                $this->_setFont($page, $font_style_message, ($font_size_message - 1), $font_family_comments, $this->_general['non_standard_characters'], $font_color_message);
                sksort($note_line, 'date', true);
                $i = 0;
                $line_count = 0;
                while (isset($note_line[$i]['date'])) {
                    $token = wordwrap($note_line[$i]['comment'], $character_breakpoint, "\n");
                    $token = strtok($token, "\n");
                    while ($token != false) {
                        $token = trim(Mage::helper('pickpack/functions')->clean_method($token, 'text'));
                        $page->drawText($token, ($pageConfig['padded_left'] + 10), $this->y, 'UTF-8');
                        $this->y -= ($font_size_message + 3);
                        $token = strtok("\n");
                        $line_count++;
                    }
                    $i++;
                }
                $this->y = $this->y + ($generalConfig['font_size_body'] * 1.25);
                $this->subheader_start = $this->y;
            }
            unset($note_line);
            unset($_item);
        }
    }

    public function showBottomNote() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $helper = Mage::helper('pickpack');
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $packingsheetConfig = $this->getPackingsheetConfig($wonder, $storeId);

        $font_family_comments = $this->_getConfig('font_family_comments', 'helvetica', false, 'general', $storeId);
        $font_style_comments = $this->_getConfig('font_style_comments', 'regular', false, 'general', $storeId);
        $font_size_comments = $this->_getConfig('font_size_comments', 9, false, 'general', $storeId);
        $font_color_comments = trim($this->_getConfig('font_color_comments', '#222222', false, 'general', $storeId));
        $fill_bkg_color_comments = $this->_getConfig('fill_bkg_comments_yn', 0, false, 'general', $storeId);

        $bkg_color_comments_pre = trim($this->_getConfig('bkg_color_comments', 'skyblue', false, 'general', $storeId));
        $bkg_color_comments = new Zend_Pdf_Color_Html($bkg_color_comments_pre);

        $bkg_color_gift_message_zend = new Zend_Pdf_Color_Html('' . $generalConfig['bkg_color_gift_message'] . '');

        $notes_title = $this->_getConfig('notes_title', '', false, $wonder, $storeId);
        $strip_comment_line_break = $this->_getConfig('strip_comment_line_break', 0, false, 'general', $storeId);
        $notes_filter_options = $this->_getConfig('notes_filter_options', '', false, $wonder, $storeId);
        $notes_filter = trim(strtolower($this->_getConfig('notes_filter', '', false, $wonder, $storeId)));
        $positional_message_box_fixed_position_demension_x = $this->_getConfig('positional_message_box_fixed_position_demension', 250, false, $wonder, $storeId);
        $positional_message_box_fixed_position = explode(",", $this->_getConfig('positional_message_box_fixed_position', '20,200', false, $wonder, $storeId));
        $notes_filter = preg_replace('/^([\'"])(.*)\\1$/', '\\2', $notes_filter);
        if ($notes_filter_options != 'yestext') $notes_filter = '';

        $test_name = 'abcdefghij';
        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $test_name_length = round($this->parseString($test_name, $font_temp, ($generalConfig['font_size_body'])));
        $pt_per_char = ($test_name_length / 10);

        $notes_yn = $this->_getConfig('notes_yn', 0, false, $wonder, $storeId);
        $notes_position = $this->_getConfig('notes_position', 'yesshipping', false, $wonder, $storeId);
        if ($notes_yn == 0) $notes_position = 'no';

        $message_character_breakpoint = round(($positional_message_box_fixed_position_demension_x / $pt_per_char));

        $notesX = 0;
        $orderNote = true;
        if (strlen($this->customer_comments) > 0){
            $orderComments[0] = array(
                'comment' =>  $this->customer_comments,
                'is_visible_on_front' => 1,
                'created_at' => $order->getCreatedAt()
            );
            $orderNote = false;
        }
        if($orderNote){
            if($order->getStatusHistoryCollection(true)){
                $orderComments = $order->getStatusHistoryCollection(true);

            }
        }
        if ($orderComments) {
            $note_line = array();
            $note_comment_count = 0;
            $line_count_note = 0;
            $i = 0;
            $comment_body = '';
           
            if ($notes_position == 'yesbox') {
                $max_name_length = $positional_message_box_fixed_position_demension_x - 10;
                $msgX = $pageConfig['padded_left'] + $positional_message_box_fixed_position[0];
                $right_bg_gift_msg = $msgX + $positional_message_box_fixed_position_demension_x;
                if($right_bg_gift_msg > $pageConfig['padded_right']){
                    $right_bg_gift_msg = $pageConfig['padded_right'];
                    $max_name_length = ($right_bg_gift_msg - $msgX) - 10;
                }
                $comments_y = $positional_message_box_fixed_position[1];
                $background_color_temp_pre = $bkg_color_comments_pre;
                $background_color_temp = $bkg_color_comments;
                $font_color_temp = $font_color_comments;
                $font_style_temp = $font_style_comments;
                $font_family_temp = $font_family_comments;
                $font_size_temp = $font_size_comments;
                $fill_bkg_color_temp = $fill_bkg_color_comments;
            }
            else if ($notes_position == 'yesshipping') {
                $max_name_length = $pageConfig['padded_right'] - 10;
                $msgX = $pageConfig['padded_left'];
                $right_bg_gift_msg = $pageConfig['padded_right'];
                $comments_y = $this->y;
                $background_color_temp_pre = trim($this->_getConfig('bkg_color_message', 'skyblue', false, 'general', $storeId));
                $background_color_temp = new Zend_Pdf_Color_Html($background_color_temp_pre);
                $font_style_temp = $this->_getConfig('font_style_message', 'regular', false, 'general', $storeId);;
                $font_family_temp = $this->_getConfig('font_family_message', 'helvetica', false, 'general', $storeId);
                $font_size_temp = $this->_getConfig('font_size_message', 9, false, 'general', $storeId);
                $font_color_temp = trim($this->_getConfig('font_color_message', '#222222', false, 'general', $storeId));
                $fill_bkg_color_temp = $this->_getConfig('fill_bkg_color_comments', 0, false, 'general', $storeId);
            }
            else{
                $max_name_length = $pageConfig['padded_right'] - 10;
                $msgX = $pageConfig['padded_left'];
                $right_bg_gift_msg = $pageConfig['padded_right'];
                $comments_y = $this->y;
                $background_color_temp_pre = trim($this->_getConfig('bkg_color_gift_message', 'skyblue', false, 'general', $storeId));
                $background_color_temp = new Zend_Pdf_Color_Html($background_color_temp_pre);
                $font_style_temp = $this->_getConfig('font_style_gift_message', 'regular', false, 'general', $storeId);;
                $font_family_temp = $this->_getConfig('font_family_gift_message', 'helvetica', false, 'general', $storeId);
                $font_size_temp = $this->_getConfig('font_size_gift_message', 9, false, 'general', $storeId);
                $font_color_temp = trim($this->_getConfig('font_color_gift_message', '#222222', false, 'general', $storeId));
                $fill_bkg_color_temp = $this->_getConfig('fill_bkg_gift_message_yn', 0, false, 'general', $storeId);
            }

            $test_name = 'abcdefghij'; //10
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $test_name_length = round($this->parseString($test_name, $font_temp, ($font_size_temp)));
            $pt_per_char = ($test_name_length / 10);
            $character_breakpoint = round($max_name_length / $pt_per_char);

            foreach ($orderComments as $comment) {
                if($orderNote)
                    $comment_body = trim(strip_tags(str_replace(array('<br/>', '<br />'), ' ', $comment->getData('comment'))));
                else
                    $comment_body = '';

                if($orderNote){
                    if ($notes_filter_options == 'yestext' && ($this->checkFilterNotes($comment_body, $notes_filter))) {
                        $comment_body = '';
                    } elseif (($notes_filter_options == 'yesfrontend') && ($comment['is_visible_on_front'] != 1)) {
                        $comment_body = '';
                    }
                }

                if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
                    $check_comments_for_gift_message_filter = $this->_getConfig('check_comments_for_gift_message_filter', 'Checkout Message', false, $wonder, $storeId);
                    $pos = strpos($comment['comment'], 'M2E Pro Notes');
                    $pos2 = strpos($comment['comment'], $check_comments_for_gift_message_filter);
                    if (($pos !== false) && ($pos2 !== false)) {

                        $start_pos1 = strlen('M2E Pro Notes') + 1;
                        $start_pos2 = strlen('Checkout Message From Buyer:') + 1;
                        $str_1 = trim(substr($comment_body, $start_pos1));
                        $str_2 = trim(substr($str_1, $start_pos2));
                        $this->gift_message_array['notes'][] = $str_2;
                    }
                }

                if ($comment_body == '') {
                    continue;
                }
				
                if ((($notes_filter_options == 'yesfrontend' && $comment['is_visible_on_front'] == 1) || $notes_filter_options == 'no' || (($notes_filter_options == 'yestext') && !preg_match('~' . $notes_filter . '~i', $comment_body))) && ($comment_body != '')) {
	                $comment['created_at'] = date('m/d/y', strtotime($comment['created_at']));
                    if (trim($comment_body) != ''){
                        if($strip_comment_line_break == 1){
                            $comment_body = preg_replace('/\s+/', ' ', $comment_body);
                        }
                        $comment_body = $comment['created_at'] . ' : ' . $comment_body;
                    }
	                $note_line[$i]['date'] = $comment['created_at'];
	                $note_line[$i]['comment'] = $comment_body;
                    if ($note_line[$i]['comment'] != '') $note_comment_count = 1;
                    $note_line_break = explode("\r\n", $note_line[$i]['comment']);
                    foreach ($note_line_break as $note_line_each) {
                        if ($note_line_each != "") {
                            $note_line_each = trim($note_line_each);
                            $note_line_wr = wordwrap($note_line_each, $character_breakpoint, "\n", false);
                            $comment_array = explode("\n", $note_line_wr);
			                $line_count_note += count($comment_array);
		                    unset($comment_array);
		                }
		            }
	                $i++;
	            }
			}

            // for the bottom of gift message

            if ($note_comment_count > 0) {
                $this->y = $comments_y;
                $temp_height = (($line_count_note + 1) * ($font_size_temp + 3)  + 6) + $pageConfig['page_bottom'];

                if (($this->y - $temp_height) < $this->pageFooterHeight) {
                    $page = $this->newPage();
                    $page_count = $this->getPdf()->getPageCount();
                    $this->y = $this->getPdf()->y;
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    if ($generalConfig['second_page_start'] == 'asfirst') $this->y = $this->items_header_top_firstpage;
                    else $this->y = $pageConfig['page_top'];

                    $paging_text = '-- ' . $this->order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
                    $paging_text_width = $this->widthForStringUsingFontSize($paging_text, $generalConfig['font_family_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_style_subtitles'], $generalConfig['non_standard_characters']);
                    $paging_text_x = (($pageConfig['padded_right'] / 2) - ($paging_text_width / 2));

                    $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                    $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                    $flag_print_newpage = 1;
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                } else
                    $this->y -= $font_size_temp;

                if (($background_color_temp_pre != '') && ($background_color_temp_pre != '#FFFFFF')) {
                    $page->setFillColor($background_color_temp);
                    $page->setLineColor($background_color_temp);
                    $page->setLineWidth(0.5);
                    if ($fill_bkg_color_temp == 0) {
                        $page->drawLine($msgX, ($this->y + $font_size_comments + 2), $right_bg_gift_msg, ($this->y + $font_size_comments + 2));
                        $page->drawLine($msgX, ($this->y - (($line_count_note +1) * ($font_size_comments + 3)) - 6), $right_bg_gift_msg, ($this->y - (($line_count_note +1) * ($font_size_comments + 3)) - 6));
                        $page->drawLine($msgX, ($this->y + $font_size_comments + 2), $msgX, ($this->y - (($line_count_note +1) * ($font_size_comments + 3)) - 6));
                        $page->drawLine($right_bg_gift_msg, ($this->y + $font_size_comments + 2), $right_bg_gift_msg, ($this->y - (($line_count_note +1) * ($font_size_comments + 3)) - 6));
                    }
                    else{
                        $page->drawRectangle($msgX, ($this->y + $font_size_temp + 2), $right_bg_gift_msg, ($this->y - (($line_count_note + 1) * ($font_size_temp + 3)) - 6));
                    }
                }
                $this->_setFont($page, 'bold', $font_size_temp, $font_family_temp, $generalConfig['non_standard_characters'], $font_color_temp);

                $this->y -= 5;
                $page->drawText(Mage::helper('sales')->__($notes_title), ($msgX + 10), $this->y, 'UTF-8');
                $this->y -= ($font_size_temp + 5);

                $this->_setFont($page, $font_style_temp, ($font_size_temp - 1), $font_family_temp, $generalConfig['non_standard_characters'], $font_color_temp);
                sksort($note_line, 'date', true);
                $i = 0;
                while (isset($note_line[$i]['date'])) {					
                    $token = wordwrap($note_line[$i]['comment'], $character_breakpoint, "\n");
                    $token = strtok($token, "\n");
                    while ($token != false) {
                        $token = trim(Mage::helper('pickpack/functions')->clean_method($token, 'text'));
                        $page->drawText($token, ($msgX + 10), $this->y, 'UTF-8');
						$this->y -= $font_size_temp + 3;
						$token = strtok("\n");
                    }
                    $i++;
                }
                // for the bottom of gift message
                $this->y = $this->y - ($generalConfig['font_size_body'] * 1.7);
                $this->comments_y = $this->y;
            }
            unset($note_line);
            unset($orderComments);
        }

        return $this->y;
    }

    private function checkFilterNotes($comment, $notes_filter) {
        $is_filter = false;
        $note_filter_array = explode('|', $notes_filter);
        foreach ($note_filter_array as $filter) {
            if (stripos($comment, $filter) !== false) {
                $is_filter = true;
                break;
            }
        }
        return $is_filter;
    }
}