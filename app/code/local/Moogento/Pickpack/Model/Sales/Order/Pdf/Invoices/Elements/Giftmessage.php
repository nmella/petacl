<?php
/**
 * 
 * Date: 19.12.15
 * Time: 16:21
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Giftmessage extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public $x;
    public $y;
    public $custom_message_yn = 0;
    public $bottom_message_pos;
    public $comments_y;
    public $subheader_start;
    public $giftWrap_info;
    public $headerBarXY;
    public $bottom_bg_gift_msg;
    public $line_count;
    public $email_X;
    public $order_number_display;
    public $items_header_top_firstpage;
    public $gift_message_array = array();
    public $msg_line_count = 0;
    public $flag_message_after_shipping_address = 0;

    protected $y_before_order_gift_message;
    protected $page_before;
	protected $general_path;
	
	const BOX_PADDING_UNFILLED = 5;
	const BOX_PADDING_FILLED = 3;
		
	private $_padding_yn = 0;

    public function __construct($arguments) {
        parent::__construct($arguments);
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $this->giftWrap_info = Mage::helper('pickpack/gift')->getGiftWrapInfo($order, $wonder);
		$this->giftwrap_path = Mage::helper('pickpack')->getPickpackImagesPath();
    }
	
    public function showBottomOrderGiftMessage() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $helper = Mage::helper('pickpack');
        $gift_message_id = $order->getGiftMessageId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $this->gift_message_array['printed'] = 0;
		
        $notes_yn = $this->_getConfig('notes_yn', 0, false, $wonder, $storeId);
        $notes_position = $this->_getConfig('notes_position', 'yesshipping', false, $wonder, $storeId);
        if ($notes_yn == 0) $notes_position = 'no';

        $bkg_color_message_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_message']);
        $bkg_color_gift_message_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_gift_message']);
        $bkg_color_comments_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_comments']);

        $gift_message_yn = $this->_getConfig('gift_message_yn', 'yesunder', false, $wonder, $storeId);
        $message_title_tofrom_yn = $this->_getConfig('message_title_tofrom_yn', 'yes', false, $wonder, $storeId);
        $positional_message_box_fixed_position = explode(",", $this->_getConfig('positional_message_box_fixed_position', '20,200', false, $wonder, $storeId));
        $positional_message_box_fixed_position_demension_x = $this->_getConfig('positional_message_box_fixed_position_demension', 250, false, $wonder, $storeId);

        $test_name = 'abcdefghij';
        $font_choice = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $test_name_length = round($this->parseString($test_name, $font_choice, ($generalConfig['font_size_body'])));
        $pt_per_char = ($test_name_length / 10);
        $message_character_breakpoint = round(($positional_message_box_fixed_position_demension_x / $pt_per_char));

        $gift_message_item = Mage::getModel('giftmessage/message');

        if((!is_null($gift_message_id) || $this->giftWrap_info['message'] != NULL || $this->giftWrap_info['wrapping_paper'] != NULL)) {
            $gift_msg_array = $this->getOrderGiftMessage($gift_message_id, $gift_message_yn, $gift_message_item, $this->giftWrap_info);
            $gift_sender = $gift_msg_array[1];
            $gift_recipient = $gift_msg_array[2];
            $gift_message = $gift_msg_array[0];
            $to_from = '';
            $to_from_from = '';
            if (isset($gift_recipient) && $gift_recipient != '') {
                if ($gift_message_yn != 'yesnewpage')
					$to_from .= $helper->__('Message to:'). ' ' . $gift_recipient;
                else
					$to_from .= $helper->__('To ') . $gift_recipient;
            }
            if (isset($gift_sender) && $gift_sender != '') 
				$to_from_from = $helper->__('From:') . ' ' . $gift_sender;
        }
        else
            $gift_message = '';
		
        if ($helper->isInstalled('Webtex_GiftRegistry')){
            $customerId = $order->getData("customer_id");
            $gift_registry = Mage::getModel("webtexgiftregistry/webtexgiftregistry")->load($customerId, "customer_id");
            if(isset($gift_registry['registry_id']) && $gift_registry['registry_id'] != '') {
                $gift_registry_message = $helper->__('This is a Gift Registry Order') . ' (' . $gift_registry["giftregistry_id"] . ')'  ;
                $gift_message = $gift_message . $gift_registry_message;
            }
        }
        if($gift_message != ''){
            if ($gift_message_yn == 'yesbox') {
                $this->y = $positional_message_box_fixed_position[1];
                $msgX = $positional_message_box_fixed_position[0];
                $gift_message = wordwrap($gift_message, $message_character_breakpoint, "\n", false);
                $bkg_color_zend_choice = $bkg_color_comments_zend;
				$bkg_fill_yn_choice = $generalConfig['fill_bkg_comments_yn'];
                $bkg_color_choice = $generalConfig['bkg_color_comments'];
                $font_style_choice = $generalConfig['font_style_comments'];
                $font_family_choice = $generalConfig['font_family_comments'];
                $font_size_choice = $generalConfig['font_size_comments'];
                $font_color_choice = $generalConfig['font_color_comments'];
                $x2_right = $positional_message_box_fixed_position[0] + $positional_message_box_fixed_position_demension_x;
                $gift_msg_array = $this->splitWordsToArrayBasedOnMaxWidth($gift_message, $positional_message_box_fixed_position_demension_x, $font_size_choice, $font_family_choice);
                $message_line_spacing = $font_size_choice * 1.5;
            } elseif ($gift_message_yn == 'yesunder') {
                $msgX = $pageConfig['padded_left'];
                $gift_message = wordwrap($gift_message, 96, "\n", false);
                $bkg_color_zend_choice = $bkg_color_gift_message_zend;
				$bkg_fill_yn_choice = $generalConfig['fill_bkg_gift_message_yn'];
				$bkg_color_choice = $generalConfig['bkg_color_gift_message'];
                $font_style_choice = $generalConfig['font_style_gift_message'];
                $font_family_choice = $generalConfig['font_family_gift_message'];
                $font_size_choice = $generalConfig['font_size_gift_message'];
                $font_color_choice = $generalConfig['font_color_gift_message'];
                $x2_right = $pageConfig['padded_right'];
                $gift_msg_array = $this->createMsgArray($gift_message);
                $message_line_spacing = $font_size_choice * 1.5;
            } elseif ($gift_message_yn == 'yesnewpage') {
                $msgX = $pageConfig['padded_left'];
                $gift_message = wordwrap($gift_message, 96, "\n", false);
                $bkg_color_zend_choice = $bkg_color_gift_message_zend;
				$bkg_fill_yn_choice = $generalConfig['fill_bkg_gift_message_yn'];
				$bkg_color_choice = $generalConfig['bkg_color_gift_message'];
                $font_style_choice = $generalConfig['font_style_gift_message'];
                $font_family_choice = $generalConfig['font_family_gift_message'];
                $font_size_choice = $generalConfig['font_size_gift_message'];
                $font_color_choice = $generalConfig['font_color_gift_message'];
                $x2_right = $pageConfig['padded_right'];
                $gift_msg_array = $this->createMsgArray($gift_message);
                $message_line_spacing = $font_size_choice * 1.5;
            }
            $this->y_before_order_gift_message = $this->y;
            if ($notes_position == 'yesbox' && $gift_message_yn == 'yesbox')
                $this->y = $this->comments_y;
            elseif ($this->custom_message_yn == "yes")
                $this->y = $this->bottom_message_pos - 15;
            else
                $this->y = $this->y - $pageConfig['vertical_spacing'];
            $line_tofrom = 0;
            if ($message_title_tofrom_yn == 1)
                $line_tofrom = 2.5;
            $this->msg_line_count = count($gift_msg_array) + $line_tofrom;
            // Caculate necessary height for print gift message.
            $temp_height = 0;
            foreach ($gift_msg_array as $gift_msg_line) {
                $temp_height += 2 * $font_size_choice;
            }
            /***********PRINTING ORDER GIFT MESSAGE NEWPAGE***********/
            if ($gift_message_yn == 'yesnewpage') {
                $this->page_before = $page;
                $page = $this->newPage();
                if ($generalConfig['second_page_start'] == 'asfirst') $this->y = $this->items_header_top_firstpage;
                if ($bkg_color_choice != '#FFFFFF') {
                    $page->setFillColor($bkg_color_choice);
                    $page->setLineColor($bkg_color_choice);
                    $page->setLineWidth(0.5);
                    $page->drawRectangle($pageConfig['padded_left'], ($this->y - ($font_size_choice / 2)), $pageConfig['padded_right'], ($this->y + $font_size_choice + 2));
                }
				
				if($generalConfig['gift_override_yn'] == 1)
	                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
				else
					$this->_setFont($page, 'bold', $font_size_choice, $font_family_choice, $generalConfig['non_standard_characters'], $font_color_choice);
                $page->drawText($helper->__('Order Gift Message for Order') . ' #' . $order->getRealOrderId(), ($msgX + $generalConfig['font_size_gift_message'] / 3), $this->y, 'UTF-8');
                $this->y = ($this->y - $font_size_choice * 0.8);
            }

            /***********PRINTING ORDER GIFT MESSAGE POSITIONAL BOX***********/
            if ($gift_message_yn == 'yesbox' && $notes_position != 'yesbox')
                $this->y = $positional_message_box_fixed_position[1];
			
            $flag_print_newpage = 0;
            if (($this->y - $temp_height) < 10) {
                $page = $this->newPage();
                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                if ($generalConfig['second_page_start'] == 'asfirst') 
					$this->y = $this->items_header_top_firstpage;
                else 
					$this->y = $pageConfig['page_top'];

                $paging_text = '-- ' . $this->order_number_display . ' | ' . $helper->__('Page') . ' ' . $this->getPageCount() . ' --';
                $paging_text_width = $this->widthForStringUsingFontSize($paging_text, $generalConfig['font_family_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_style_subtitles'], $generalConfig['non_standard_characters']);
                $paging_text_x = (($pageConfig['padded_right'] / 2) - ($paging_text_width / 2));

                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                $flag_print_newpage = 1;
				if($generalConfig['gift_override_yn'] == 1)
	                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
				else
                	$this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            } else
                $this->y -= $font_size_choice * 1.7;
            $x1_left = $msgX;

            if (($gift_message_yn == 'yesbox')) {
                if ($flag_print_newpage == 0) {
                    if ($notes_position != 'yesbox')
                        $y1_top = $positional_message_box_fixed_position[1];
                    else
                        $y1_top = $this->comments_y + 2;
                    $this->bottom_bg_gift_msg = $y1_top - ($this->msg_line_count * $message_line_spacing) - $font_size_choice * 0.5;
                    $this->y += $font_size_choice * 0.5;
                } else {
                    $y1_top = ($this->y + $font_size_choice);
                    $this->bottom_bg_gift_msg = $y1_top - $this->msg_line_count * ($message_line_spacing) - $font_size_choice * 0.5;
                }
            } else {
                if ($this->msg_line_count < 4)
                    $this->bottom_bg_gift_msg = $this->y - ($this->msg_line_count - 1) * ($font_size_choice + 3) - 5;
                else
                    $this->bottom_bg_gift_msg = $this->y - ($this->msg_line_count -1) * ($font_size_choice + 3) - 5;
                $y1_top = ($this->y + $font_size_choice);
            }
			
            $this->_padding_yn = $this->drawGiftMessageBox($bkg_fill_yn_choice, $bkg_color_choice, $bkg_color_zend_choice, $page, $x1_left, $y1_top, $x2_right, $this->bottom_bg_gift_msg, $message_title_tofrom_yn, $this->_padding_yn);
            $this->_setFont($page, 'bold', $font_size_choice, $font_family_choice, $generalConfig['non_standard_characters'], $font_color_choice);
            $this->y = $this->showToFrom($message_title_tofrom_yn, $to_from, $msgX + ($font_size_choice / 3), $this->y, $to_from_from, $font_size_choice, $page, $this->_padding_yn);
			
			if($generalConfig['gift_override_yn'] == 1)
                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
			else
            	$this->_setFont($page, $font_style_choice, $font_size_choice, $font_family_choice, $generalConfig['non_standard_characters'], $font_color_choice);
            $this->y = $this->drawGiftMessageText($gift_msg_array, $msgX + ($font_size_choice / 3), $font_size_choice, $this->y, $page, $this->_padding_yn);

			unset($gift_msg_array);
            if (isset($this->giftWrap_info['wrapping_paper'])) {
                $wrapping_paper_text = trim($this->giftWrap_info['wrapping_paper']);
                if ($wrapping_paper_text != '') {
                    if ($gift_message_yn == 'yesnewpage') {
                        $this->y -= ($generalConfig['font_size_gift_message'] + 3);
                        if (strtoupper($generalConfig['bkg_color_message']) != '#FFFFFF') {
                            $page->setFillColor($bkg_color_message_zend);
                            $page->setLineColor($bkg_color_message_zend);
                            $page->setLineWidth(0.5);
                            $page->drawRectangle($pageConfig['padded_left'], ($this->y - ($generalConfig['font_size_gift_message'] / 2)), $pageConfig['padded_right'], ($this->y + $generalConfig['font_size_gift_message'] + 2));
                        }

                        $this->_setFont($page, $generalConfig['font_style_gift_message'], ($generalConfig['font_size_gift_message']), $generalConfig['font_family_gift_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_gift_message']);

                        $this->y -= ($generalConfig['font_size_gift_message'] + 2);
                        $page->drawText($helper->__('Wrapping Paper Selected'), ($msgX + $generalConfig['font_size_gift_message']), $this->y, 'UTF-8');
                    } else {
                        $this->_setFont($page, 'bold', ($generalConfig['font_size_gift_message']), $generalConfig['font_family_gift_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_gift_message']);

                        $this->y -= ($generalConfig['font_size_gift_message'] + 2);
                        $page->drawText($helper->__('Wrapping Paper Selected'), ($msgX + $generalConfig['font_size_gift_message']), $this->y, 'UTF-8');
                    }
                    $this->y -= ($generalConfig['font_size_gift_message'] + 2);
                    $this->_setFont($page, 'regular', ($generalConfig['font_size_gift_message'] - 1), $generalConfig['font_family_gift_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_gift_message']);
                    $page->drawText($wrapping_paper_text, ($msgX + $generalConfig['font_size_gift_message']), $this->y, 'UTF-8');
                }
            }
        }
    }

    public function showBottomProductGiftMessage() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $helper = Mage::helper('pickpack');
        $gift_message_id = $order->getGiftMessageId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $gift_message_combined = $this->getProductGiftMessage($this->gift_message_array);
        if(isset($this->gift_message_array['items']) && ($gift_message_combined)) {
            $notes_yn = $this->_getConfig('notes_yn', 0, false, $wonder, $storeId);
            $notes_position = $this->_getConfig('notes_position', 'yesshipping', false, $wonder, $storeId);
            if ($notes_yn == 0) $notes_position = 'no';

            $bkg_color_gift_message_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_gift_message']);
            $bkg_color_message_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_message']);
            $bkg_color_comments_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_comments']);

            $gift_message_yn = $this->_getConfig('gift_message_yn', 'yesunder', false, $wonder, $storeId);
            $positional_message_box_fixed_position_demension_x = $this->_getConfig('positional_message_box_fixed_position_demension', 250, false, $wonder, $storeId);

            $product_gift_message_yn = $this->_getConfig('product_gift_message_yn', 'no', false, $wonder, $storeId);
            $positional_message_box_fixed_position = explode(",", $this->_getConfig('positional_message_box_fixed_position', '20,200', false, $wonder, $storeId));

            $test_name = 'abcdefghij';
            $font_choice = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $test_name_length = round($this->parseString($test_name, $font_choice, ($generalConfig['font_size_body'])));
            $pt_per_char = ($test_name_length / 10);

            // add product gift message and history ebay note to order message
            if ($product_gift_message_yn == 'yesbox') {
                $this->y = $positional_message_box_fixed_position[1];
                $msgX = $positional_message_box_fixed_position[0];
                $bkg_color_zend_choice = $bkg_color_comments_zend;
				$bkg_fill_yn_choice = $generalConfig['fill_bkg_comments_yn'];
                $bkg_color_choice = $generalConfig['bkg_color_comments'];
                $font_style_choice = $generalConfig['font_style_comments'];
                $font_family_choice = $generalConfig['font_family_comments'];
                $font_size_choice = $generalConfig['font_size_comments'];
                $font_color_choice = $generalConfig['font_color_comments'];
                $x2_right = $positional_message_box_fixed_position[0] + $positional_message_box_fixed_position_demension_x;
                $gift_msg_array = $this->splitWordsToArrayBasedOnMaxWidth($gift_message_combined, $positional_message_box_fixed_position_demension_x, $font_size_choice, $font_family_choice);
                $message_line_spacing = $font_size_choice * 1.5;
            } elseif ($product_gift_message_yn == 'yesunder') {
                $msgX = $pageConfig['$padded_left'];
                $bkg_color_zend_choice = $bkg_color_gift_message_zend;
				$bkg_fill_yn_choice = $generalConfig['fill_bkg_gift_message_yn'];
				$bkg_color_choice = $generalConfig['bkg_color_gift_message'];
                $font_style_choice = $generalConfig['font_style_gift_message'];
                $font_family_choice = $generalConfig['font_family_gift_message'];
                $font_size_choice = $generalConfig['font_size_gift_message'];
                $font_color_choice = $generalConfig['font_color_gift_message'];
                $x2_right = $pageConfig['$padded_right'];
                $gift_msg_array = $this->createMsgArray($gift_message_combined);
                $message_line_spacing = $font_size_choice * 1.5;
            } elseif ($product_gift_message_yn == 'yesnewpage') {
                $msgX = $pageConfig['$padded_left'];
                $bkg_color_zend_choice = $bkg_color_comments_zend;
				$bkg_fill_yn_choice = $generalConfig['fill_bkg_gift_message_yn'];
                $bkg_color_choice = $generalConfig['bkg_color_gift_message'];
                $font_style_choice = $generalConfig['font_style_gift_message'];
                $font_family_choice = $generalConfig['font_family_gift_message'];
                $font_size_choice = $generalConfig['font_size_gift_message'];
                $font_color_choice = $generalConfig['font_color_gift_message'];
                $x2_right = $pageConfig['$padded_right'];
                $gift_msg_array = $this->createMsgArray($gift_message_combined);
                $message_line_spacing = $font_size_choice * 1.5;
            }
			
            if (($gift_message_yn == $product_gift_message_yn) && (!is_null($gift_message_id) || $this->giftWrap_info['message'] != NULL || $this->giftWrap_info['wrapping_paper'] != NULL))
                $this->y = $this->bottom_bg_gift_msg;
            elseif ($product_gift_message_yn == $notes_position)
                $this->y = $this->comments_y;
            elseif ($product_gift_message_yn == "yesunder" && $gift_message_yn != "no" && $gift_message_yn != "yesundership" && (!is_null($gift_message_id) || $this->giftWrap_info['message'] != NULL || $this->giftWrap_info['wrapping_paper'] != NULL))
                $this->y = $this->y_before_order_gift_message;
            
			$flag_print_newpage = 0;
            if (($product_gift_message_yn == 'yesbox')) {
                if ($flag_print_newpage == 0) {
                    $this->bottom_bg_gift_msg = $this->y - $this->msg_line_count * ($font_size_choice + 1) - $font_size_choice * 0.5;
                    $this->y += $font_size_choice * 0.5;
                } else
                    $this->bottom_bg_gift_msg = $this->y - $this->msg_line_count * ($font_size_choice + 1) - $font_size_choice * 0.5;
            } else
                $this->bottom_bg_gift_msg = $this->y - $this->msg_line_count * ($font_size_choice + 1);

            $line_tofrom = 0;
            $this->msg_line_count = count($gift_msg_array) + $line_tofrom;
            if ($product_gift_message_yn != 'yesnewpage') {
                $temp_height = 0;
                foreach ($gift_msg_array as $gift_msg_line) {
                    $temp_height += 2 * $font_size_choice;
                }

                if (($this->y - $temp_height) < 10 && count($gift_msg_array) > 0) {
                    $page = $this->newPage();
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    if ($generalConfig['second_page_start'] == 'asfirst') $this->y = $this->items_header_top_firstpage;
                    else $this->y = $pageConfig['page_top'];

                    $paging_text = '-- ' . $this->order_number_display . ' | ' . $helper->__('Page') . ' ' . $this->getPageCount() . ' --';
                    $paging_text_width = $this->widthForStringUsingFontSize($paging_text, $generalConfig['font_family_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_style_subtitles'], $generalConfig['non_standard_characters']);
                    $paging_text_x = (($pageConfig['$padded_right'] / 2) - ($paging_text_width / 2));

                    $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                    $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                }
                //draw background gift message
                $x1_left = $msgX;
                //$y1_top = ($this->y + $font_size_choice);
                //$this->bottom_bg_gift_msg = ($this->y - ($this->msg_line_count * ($font_size_choice - 1)));
                $page_choice = $page;
                if ( ($gift_message_yn == "yesnewpage") && ($product_gift_message_yn != "yesnewpage") ) {
                    $page = $this->page_before;
                }
                $this->_padding_yn = $this->drawGiftMessageBox($bkg_fill_yn_choice, $bkg_color_choice, $bkg_color_zend_choice, $page, $x1_left, $y1_top, $x2_right, $this->bottom_bg_gift_msg, $message_title_tofrom_yn, $this->_padding_yn);
				if($generalConfig['gift_override_yn'] == 1)
	                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
				else
                	$this->_setFont($page, $font_style_choice, ($font_size_choice - 1), $font_family_choice, $generalConfig['non_standard_characters'], $font_color_choice);
                $this->y = $this->drawGiftMessageText($gift_msg_array, $msgX + $font_size_choice / 3, $font_size_choice, $this->y, $page, $this->_padding_yn);
                unset($gift_msg_array);
                $page = $page_choice;
            } else {
                if ($gift_message_yn != 'yesnewpage') {
                    $page = $this->newPage();
                    if ($generalConfig['second_page_start'] == 'asfirst') $this->y = $this->items_header_top_firstpage;
                } elseif (!is_null($gift_message_id))
                    $this->y = $this->bottom_bg_gift_msg - 25;
                $this->_setFont($page, 'bold', $font_size_choice, $font_family_choice, $generalConfig['non_standard_characters'], $font_color_choice);
                $page->drawText($helper->__('Product Gift Message for Order') . ' #' . $order->getRealOrderId(), ($msgX), $this->y, 'UTF-8');
                $this->y = ($this->y - 10 - $font_size_choice);
                $x1_left = $msgX;
                $y1_top = ($this->y + $font_size_choice);
                $this->bottom_bg_gift_msg = ($this->y - ($this->msg_line_count * ($font_size_choice - 1)));
                $this->_padding_yn = $this->drawGiftMessageBox($bkg_fill_yn_choice, $bkg_color_choice, $bkg_color_zend_choice, $page, $x1_left, $y1_top, $x2_right, $this->bottom_bg_gift_msg, $message_title_tofrom_yn, $this->_padding_yn);
				if($generalConfig['gift_override_yn'] == 1)
	                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
				else
                	$this->_setFont($page, $font_style_choice, ($font_size_choice - 1), $font_family_choice, $generalConfig['non_standard_characters'], $font_color_choice);
                foreach ($gift_msg_array as $gift_msg_line) {
                    $page->drawText(trim($gift_msg_line), ($msgX + $font_size_choice / 3), $this->y, 'UTF-8');
                    $this->y -= ($font_size_choice + 3);
                }
                unset($gift_msg_array);
            }
        }
    }

    public function showTopOrderGiftMessage() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $gift_message_id = $order->getGiftMessageId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $bkg_color_message_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_message']);

        $gift_message_yn = $this->_getConfig('gift_message_yn', 'yesunder', false, $wonder, $storeId);
        $message_title_tofrom_yn = $this->_getConfig('message_title_tofrom_yn', 'yes', false, $wonder, $storeId);

        $gift_message_item = Mage::getModel('giftmessage/message');

        $gift_message ='';
        $to_from = '';
        $to_from_from = '';
        if((!is_null($gift_message_id) || $this->giftWrap_info['message'] != NULL || $this->giftWrap_info['wrapping_paper'] != NULL)){
            $gift_msg_array = $this->getOrderGiftMessage($gift_message_id, $gift_message_yn, $gift_message_item, $this->giftWrap_info);
            $gift_sender = $gift_msg_array[1];
            $gift_recipient = $gift_msg_array[2];
            $gift_message = $gift_msg_array[0];
            //}
            if (isset($gift_recipient) && $gift_recipient != '') {
                if ($gift_message_yn != 'yesnewpage')
					$to_from .= $helper->__('Message to:') .' ' . $gift_recipient;
                else
					$to_from .= $helper->__('To') . ' ' . $gift_recipient;
            }
            if (isset($gift_sender) && $gift_sender != '') $to_from_from = $helper->__('From:') .' ' . $gift_sender;
        }
        if ($helper->isInstalled('Webtex_GiftRegistry')){
            $customerId = $order->getData("customer_id");

            $gift_registry = Mage::getModel("webtexgiftregistry/webtexgiftregistry")->load($customerId, "customer_id");
            if(isset($gift_registry['registry_id']) && $gift_registry['registry_id'] != '') {
                $gift_registry_message = $helper->__('This is a Gift Registry Order') . ' (' . $gift_registry["giftregistry_id"] . ')'  ;
                $gift_message = $gift_message . $gift_registry_message;
            }
        }
        if($gift_message != ''){
            $this->subheader_start = $this->subheader_start + $generalConfig['font_size_body'] ;
            $this->y = $this->subheader_start + 15;
            $this->y -= ($generalConfig['font_size_message'] *2 + 10);

            $msgX = $pageConfig['padded_left'];
            
            $gift_msg_array = $this->createMsgArray($gift_message);
            $line_tofrom = 0;
            if ($message_title_tofrom_yn == 1)
                $line_tofrom = 2.5;
            $this->msg_line_count = count($gift_msg_array) + $line_tofrom;
            // Caculate necessary height for print gift message.
            $temp_height = 0;
            foreach ($gift_msg_array as $gift_msg_line) {
                $temp_height += 2 * $generalConfig['font_size_message'];
            }

            $this->flag_message_after_shipping_address = 1;
            //draw background gift message
            $x1_left = $pageConfig['padded_left'];
            $x2_right = $pageConfig['padded_right'];
            $y1_top = ($this->y + $generalConfig['font_size_message'] * 1.2);
            $this->bottom_bg_gift_msg = $this->y - ($this->msg_line_count-0.5) * ($generalConfig['font_size_message'] + 1.4);
            $this->_padding_yn = $this->drawGiftMessageBox($bkg_fill_yn_choice, $bkg_color_message, $bkg_color_message_zend, $page, $x1_left, $y1_top, $x2_right, $this->bottom_bg_gift_msg, $message_title_tofrom_yn, $this->_padding_yn);
            $this->_setFont($page, 'bold', ($generalConfig['font_size_message']), $generalConfig['font_family_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_message']);

            // add option to show to from
            $this->_setFont($page, 'bold', ($generalConfig['font_size_message']), $generalConfig['font_family_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_message'], $page);
            $this->y = $this->showToFrom($message_title_tofrom_yn, $to_from, $this->x, $this->y, $to_from_from, $generalConfig['font_size_message'], $page, $this->_padding_yn);
            // print the gift message content
			if($generalConfig['gift_override_yn'] == 1)
                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
			else
            	$this->_setFont($page, $generalConfig['font_style_message'], ($generalConfig['font_size_gift_message'] - 1), $generalConfig['font_family_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_message']);

            $this->y = $this->drawGiftMessageText($gift_msg_array, $this->x, $generalConfig['font_size_message'], $this->y, $page, $this->_padding_yn);
            unset($gift_msg_array);
            if (isset($this->giftWrap_info['wrapping_paper'])) {
                $wrapping_paper_text = trim($this->giftWrap_info['wrapping_paper']);
                if ($wrapping_paper_text != '') {
                    if ($gift_message_yn == 'yesnewpage') {
                        $this->y -= ($generalConfig['font_size_message'] + 3);
                        if (strtoupper($generalConfig['bkg_color_message']) != '#FFFFFF') {
                            $page->setFillColor($bkg_color_message_zend);
                            $page->setLineColor($bkg_color_message_zend);
                            $page->setLineWidth(0.5);
                            $page->drawRectangle($pageConfig['padded_left'], ($this->y - ($generalConfig['font_size_gift_message'] / 2)), $pageConfig['padded_right'], ($this->y + $generalConfig['font_size_gift_message'] + 2));
                        }

						if($generalConfig['gift_override_yn'] == 1)
			                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
						else
                			$this->_setFont($page, $generalConfig['font_style_gift_message'], ($generalConfig['font_size_gift_message']), $generalConfig['font_family_gift_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_gift_message']);

                        $this->y -= ($generalConfig['font_size_gift_message'] + 2);
                        $page->drawText($helper->__('Wrapping Paper Selected'), ($msgX + $generalConfig['font_size_gift_message']), $this->y, 'UTF-8');
                    } else {
						if($generalConfig['gift_override_yn'] == 1)
			                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
						else
                			$this->_setFont($page, 'bold', ($generalConfig['font_size_gift_message']), $generalConfig['font_family_gift_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_gift_message']);

                        $this->y -= ($generalConfig['font_size_gift_message'] + 2);
                        $page->drawText($helper->__('Wrapping Paper Selected'), ($msgX + $generalConfig['font_size_gift_message']), $this->y, 'UTF-8');
                    }
                    $this->y -= ($generalConfig['font_size_gift_message'] + 2);
					if($generalConfig['gift_override_yn'] == 1)
		                $this->_setFont($page, $generalConfig['font_style_gift_override'], $generalConfig['font_size_gift_override'], $generalConfig['font_family_gift_override'], $generalConfig['non_standard_characters'], $font_color_choice);
					else
                		$this->_setFont($page, 'regular', ($generalConfig['font_size_gift_message'] - 1), $generalConfig['font_family_gift_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_gift_message']);
                    $page->drawText($wrapping_paper_text, ($msgX + $generalConfig['font_size_gift_message']), $this->y, 'UTF-8');
                }
            }
            $this->subheader_start = $this->y - $generalConfig['font_size_body'] - 5 ;//- 2.5 * $generalConfig['font_size_body'];
        }
    }

    public function showTopProductGiftMessage() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $gift_message_id = $order->getGiftMessageId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $notes_yn = $this->_getConfig('notes_yn', 0, false, $wonder, $storeId);
        $bkg_color_message_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_message']);
        $gift_message_yn = $this->_getConfig('gift_message_yn', 'yesunder', false, $wonder, $storeId);
        $notes_position = $this->_getConfig('notes_position', 'yesshipping', false, $wonder, $storeId);
        if ($notes_yn == 0)
			$notes_position = 'no';

        $test_name = 'abcdefghij';
        $font_choice = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $test_name_length = round($this->parseString($test_name, $font_choice, ($generalConfig['font_size_body'])));
        $pt_per_char = ($test_name_length / 10);
        $max_chars_message = $this->getMaxCharMessage($pageConfig['padded_right'], $generalConfig['font_size_options'], $font_choice);

        $gift_message_product = $this->getProductGiftMessageUnderShip($order, $max_chars_message);
        if ($gift_message_product) {
            $msgX = $pageConfig['padded_left'];
            $gift_msg_array = $this->createMsgArray($gift_message_product);
            // Caculate necessary height for print gift message.
            $temp_height = $this->getHeightLine($gift_msg_array, $generalConfig['font_size_message']);
            
			if ($gift_message_yn == "yesundership" && !is_null($gift_message_id))
                $this->y = $this->bottom_bg_gift_msg;
            elseif ($gift_message_yn != "yesundership" && $notes_position == 'yesshipping')
                $this->y = $this->subheader_start + ($generalConfig['font_size_body'] * ($this->line_count + 1));
            else
                $this->y = $this->subheader_start - 4 * $generalConfig['font_size_message'];
			
            $this->flag_message_after_shipping_address = 1;
            $x1_left = $pageConfig['padded_left'];
            $x2_right = $pageConfig['padded_right'];
            $y1_top = ($this->y + $generalConfig['font_size_message']);
            $this->bottom_bg_gift_msg = ($this->y - $temp_height);
            $this->_padding_yn = $this->drawGiftMessageBox($bkg_fill_yn_choice, $bkg_color_message, $bkg_color_message_zend, $page, $x1_left, $y1_top, $x2_right, $this->bottom_bg_gift_msg, $message_title_tofrom_yn, $this->_padding_yn);
            // print the gift message content
            $this->_setFont($page, $generalConfig['font_style_gift_message'], ($generalConfig['font_size_message'] - 1), $generalConfig['font_family_message'], $generalConfig['non_standard_characters'], $generalConfig['font_color_message']);
            $this->y = $this->drawGiftMessageText($gift_msg_array, $msgX + $generalConfig['font_size_message'], $generalConfig['font_size_message'], $this->y, $page, $this->_padding_yn);
            unset($gift_msg_array);
            $this->subheader_start = $this->y - 3.5 * $generalConfig['font_size_body'];
        }
    }

    /**
     * Function for show qtys
     *
     * @param $gift_message_id
     * @param $gift_message_yn
     * @param $gift_message_item
     * @param $giftWrap_info
     * @param $gift_message_array
     * @return array
     */
    protected function getOrderGiftMessage($gift_message_id,$gift_message_yn, $gift_message_item, $giftWrap_info, $gift_message_array = array()) {
        return Mage::helper('pickpack/gift')->getOrderGiftMessage($gift_message_id,$gift_message_yn, $gift_message_item, $giftWrap_info, $gift_message_array);
    }

    public function createMsgArray($gift_message) {
        return Mage::helper('pickpack/gift')->createMsgArray($gift_message);
    }
	
    private function getBoxPadding($padding_yn) {
        if($padding_yn == 1)
			return self::BOX_PADDING_UNFILLED;
        elseif($padding_yn == 2)
        	return self::BOX_PADDING_FILLED;
	    else
			return 0;
    }
	
    private function drawEmptyBox($bkg_color_choice, $bkg_color_zend_choice, $page, $x1_left, $y1_top, $x2_right, $y2_bottom) {
        $page->drawLine($x1_left, $y1_top, $x2_right, $y1_top);
        $page->drawLine($x1_left, $y2_bottom, $x2_right, $y2_bottom);
        $page->drawLine($x1_left, $y1_top, $x1_left, $y2_bottom);
        $page->drawLine($x2_right, $y1_top, $x2_right, $y2_bottom);
		return;
    }
	
    private function drawFullBox($bkg_color_choice, $bkg_color_zend_choice, $page, $x1, $y1, $x2, $y2) {
        $page->drawRectangle($x1_left, $y1_top, $x2_right, $y2_bottom);
		return;
    }
	
    private function drawGiftCard($y1, $x2, $y2) {
        $page = $this->getPage();
		$giftwrap_path = $this->giftwrap_path . 'gift_message_card.png';
		
		if(isset($giftwrap_path) && ($giftwrap_path != '')) {						
	    	try{ 
				if(file_exists($giftwrap_path)) {
                    $giftcard = Zend_Pdf_Image::imageWithPath($giftwrap_path);
									
					$sharp_multiplier = (72/300);
					// giftcard image = 348 x 845
					// normal image height : (845 x (72/300))
                    $img_width_orig = (348 * $sharp_multiplier);
                    $img_height_orig = (845 * $sharp_multiplier);
					$height_message_box = ($y1 - $y2 + 20);
					//$img_height = $height_message_box;
					$img_width = ( ($height_message_box / $img_height_orig) * $img_width_orig);
					$x1 = $x2 - $img_width;
					//$y2 = $y1 - $img_height;
											
                    $page->drawImage($giftcard, $x1+10, $y2-10, $x2+10, $y1+10);
					unset($giftcard);
					unset($img_width);
					unset($height_message_box);
					unset($img_height_orig);
					unset($img_width_orig);
					unset($sharp_multiplier);
				}
				else
	           	 	Mage::log('Giftcard image not found at '.$giftwrap_path, null, 'moogento_pickpack.log');
	        } catch(Exception $e) {
				Mage::log($e->getMessage(). ' - Error, giftcard image not found at '.$giftwrap_path, null, 'moogento_pickpack.log');
			}
		}		
		return;
    }

	/*
		returns 1 if there was an empty box drawn, 2 for a full box, or 0 if not (used to set padding on message text inside the box)
	*/
    private function drawGiftMessageBox($bkg_fill_yn_choice, $bkg_color_choice, $bkg_color_zend_choice, $page, $x1_left, $y1_top, $x2_right, $y2_bottom, $message_title_tofrom_yn, $padding_yn) {
		if ( !(isset($bkg_color_choice)) || ($bkg_color_choice == '') || ($bkg_color_choice == '#FFFFFF') )
			return 0;
		$generalConfig = $this->getGeneralConfig();
        $page->setFillColor($bkg_color_zend_choice);
        $page->setLineColor($bkg_color_zend_choice);
        $page->setLineWidth(0.5);
		$y2_bottom -= $this->getBoxPadding($padding_yn);
		
		if($message_title_tofrom_yn == 1) {
			if($generalConfig['gift_override_yn'] == 1)
	            $y2_bottom -= ($generalConfig['font_size_gift_override']);
			else
	        	$y2_bottom -= ($generalConfig['font_size_body']);
		} else {
			if($generalConfig['gift_override_yn'] == 1)
	            $y2_bottom -= ($generalConfig['font_size_gift_override'] * 1.3);
			else
	        	$y2_bottom -= ($generalConfig['font_size_body'] * 1.3);
		}
		
		$return_value = 1;
		if($bkg_fill_yn_choice == 1) {
			$this->drawFullBox($bkg_color_choice, $bkg_color_zend_choice, $page, $x1_left, $y1_top, $x2_right, $y2_bottom);
			$return_value = 2;
		} else
			$this->drawEmptyBox($bkg_color_choice, $bkg_color_zend_choice, $page, $x1_left, $y1_top, $x2_right, $y2_bottom);
	
		// show moo gift card
		if($generalConfig['gift_image_yn'] == 1)
			$this->drawGiftCard($y1_top, $x2_right, $y2_bottom);

		return $return_value;
    }

	/*
		$padding_yn : 
			0 No box drawn, no padding
			1 Empty box drawn, some padding
			2 Full box drawn, more padding
	*/
    protected function showToFrom($message_title_tofrom_yn, $to_from, $msgX, $y, $to_from_from, $font_size, $page, $padding_yn) {
		$msgX += $this->getBoxPadding($padding_yn);
		$y -= $this->getBoxPadding($padding_yn);
		
		if($message_title_tofrom_yn == 1) {
            $page->drawText($to_from, $msgX, $y, 'UTF-8');
            $y -= ($font_size + 3);
		    if (isset($to_from_from) && ($to_from_from != '')) {
                $page->drawText($to_from_from, $msgX, $y, 'UTF-8');
                $y -= ($font_size * 1.75);
            }
        }
        return $y;
    }

    protected function drawGiftMessageText($gift_msg_array, $msgX, $font_size, $y, $page, $padding_yn) {
		$msgX += $this->getBoxPadding($padding_yn);
		$y -= $this->getBoxPadding($padding_yn);
        
		foreach ($gift_msg_array as $gift_msg_line) {
            $page->drawText(trim($gift_msg_line), $msgX, $y, 'UTF-8');
            $y -= ($font_size + 3);
        }
        return $y;
    }

    private function getHeightLine($gift_msg_array, $font_size) {
        $temp_height = 0;
        foreach ($gift_msg_array as $gift_msg_line) {
            $temp_height += $font_size + 3;
        }
        return $temp_height;
    }

    private function getProductGiftMessageUnderShip($order, $max_chars_message) {
        $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
        // add product gift message and history ebay note to order message
        $gift_message_combined = '';
        foreach ($itemsCollection as $item) {
            $item_message = $this->getItemGiftMessage($item, $max_chars_message);
            if (count($item_message) > 2) {
                $item_message['message-content'] = $item_message[2];
                $item_message['from'] = $item_message[0];
                $item_message['to'] = $item_message[1];
                if (isset($item_message) && is_array($item_message)) {
                    foreach ($item_message['message-content'] as $k2 => $v2)
                        $gift_message_combined .= "\n" . $v2;
                }
            }
        }
        return $gift_message_combined;
    }

    /*private function createMsgArray2($gift_message, $max_width = 250, $font_size = 10, $font_choice = null) {
        if ($font_choice == null)
            $font_choice = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        else
            $font_choice = $this->getFontName2($font_choice, 'regular', 0);
        $line_width = $this->parseString('1234567890', $font_choice, $font_size * 0.8);
        $char_width = $line_width / 11;

        $max_chars = round($max_width / $char_width);

        $gift_message_wordwrap = wordwrap($gift_message, $max_chars, "\n", false);
        $gift_msg_array = array();
        $token = strtok($gift_message_wordwrap, "\n");
        while ($token != false) {
            $gift_msg_array[] = $token;
            $token = strtok("\n");
        }
        return $gift_msg_array;
    }*/

    private function getProductGiftMessage($gift_message_array) {
        $gift_message_combined = '';
        if (isset($gift_message_array['items']))
            foreach ($gift_message_array['items'] as $item_message) {
                if (isset($item_message['printed'])) {
                    if ($item_message['printed'] == 0) {
                        if (isset($item_message['message-content']) && is_array($item_message['message-content'])) {
                            foreach ($item_message['message-content'] as $v2)
                                $gift_message_combined .= "\n" . $v2;
                        }
                    }
                }
            }
        return $gift_message_combined;
    }


    public function showRepeatGiftMessage() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $gift_message_id = $order->getGiftMessageId();
        $gift_message_item = Mage::getModel('giftmessage/message');
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
		
        $positional_remessage_box_fixed_position = explode(",", $this->_getConfig('positional_remessage_box_fixed_position', '20,200', false, $wonder, $storeId));
        $positional_message_box_fixed_position_demension_x = $this->_getConfig('positional_message_box_fixed_position_demension', 250, false, $wonder, $storeId);

        $bkg_color_comments_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_comments']);
        $gift_message_yn = $this->_getConfig('gift_message_yn', 'yesunder', false, $wonder, $storeId);

        $gift_msg_pro_array = array();
        $gift_msg_array = array();
        if(isset($this->gift_message_array['items']) && ($gift_message_combined = $this->getProductGiftMessage($this->gift_message_array))){

            $gift_msg_pro_array = $this->splitWordsToArrayBasedOnMaxWidth($gift_message_combined, $positional_message_box_fixed_position_demension_x, $generalConfig['font_size_comments'], $generalConfig['font_family_comments']);
        }
        $gift_message = '';
        if((!is_null($gift_message_id) || $this->giftWrap_info['message'] != NULL || $this->giftWrap_info['wrapping_paper'] != NULL)){
            $gift_msg_array = $this->getOrderGiftMessage($gift_message_id, $gift_message_yn, $gift_message_item, $this->giftWrap_info, $gift_msg_array);
            $gift_message = $gift_msg_array[0];
        }
        //TODO gift registry
        if (Mage::helper('pickpack')->isInstalled('Webtex_GiftRegistry')){
            $customerId = $order->getData("customer_id");

            $gift_registry = Mage::getModel("webtexgiftregistry/webtexgiftregistry")->load($customerId, "customer_id");
            if(isset($gift_registry['registry_id']) && $gift_registry['registry_id'] != '') {
                $gift_registry_message = 'This is a Gift Registry Order ' . '(' . $gift_registry["giftregistry_id"] . ')'  ;
                $gift_message = $gift_message . $gift_registry_message;
            }
        }

        if($gift_message != ''){
            $message_character_breakpoint = $positional_message_box_fixed_position_demension_x;
            $gift_message = wordwrap($gift_message, $message_character_breakpoint, "\n", false);
            $gift_msg_array = $this->splitWordsToArrayBasedOnMaxWidth($gift_message, $positional_message_box_fixed_position_demension_x, $generalConfig['font_size_comments'], $generalConfig['font_family_comments']);
        }
        $gift_msg_combined = array_merge($gift_msg_pro_array, $gift_msg_array);

        if($gift_msg_combined != null){
            $x1_left = $msgX_repeat = $positional_remessage_box_fixed_position[0];
            $x2_right = $positional_remessage_box_fixed_position[0] + $positional_message_box_fixed_position_demension_x;
            $y1_top = $positional_remessage_box_fixed_position[1] + $generalConfig['font_size_comments'];
            $msg_line_count = count($gift_msg_combined);
            $bottom_bg_gift_msg = $y1_top - $msg_line_count * ($generalConfig['font_size_comments'] + 1) - $generalConfig['font_size_comments'] * 0.5;

            $this->_padding_yn = $this->drawGiftMessageBox($bkg_fill_yn_choice, $bkg_color_choice, $bkg_color_zend_choice, $page, $x1_left, $y1_top, $x2_right, $bottom_bg_gift_msg, $message_title_tofrom_yn, $this->_padding_yn);
            $this->_setFont($page, $generalConfig['font_style_comments'], ($generalConfig['font_size_comments'] - 1), $generalConfig['font_family_comments'], $this->_general['non_standard_characters'], $generalConfig['font_color_comments']);
            $this->drawGiftMessageText($gift_msg_combined, $msgX_repeat + $generalConfig['font_size_comments'] / 3, $generalConfig['font_size_comments'], $positional_remessage_box_fixed_position[1], $page, 0);
        }
    }

    public function showTopGiftWrapIcon($currentHeaderPage) {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $gift_message_id = $order->getGiftMessageId();
        $gift_message_item = Mage::getModel('giftmessage/message');

        $show_gift_wrap_top_right_xpos = $this->_getConfig('show_gift_wrap_top_right_xpos', 0, false, $wonder, $storeId);
        $show_gift_wrap_top_right_ypos = $this->_getConfig('show_gift_wrap_top_right_ypos', 0, false, $wonder, $storeId);

        $media_path = Mage::getBaseDir('media');
        $image = Zend_Pdf_Image::imageWithPath($media_path.'/moogento/pickpack/big-gift_wrap.png');
        $x2 = $pageConfig['padded_right'] - $show_gift_wrap_top_right_xpos;
        $x1 = $x2 - 50;
        $y2 = $pageConfig['page_top'] + 5  - $show_gift_wrap_top_right_ypos;
        $y1 = $y2 - 50;
        $currentHeaderPage->drawImage($image, $x1, $y1 , $x2, $y2);
    }


    public function showGiftWrapUnderShippingAddress() {
        /***************************PRINTING GIFT WRAP UNDER SHIPPING ADDRESS *******************************/
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $giftWrap_info = Mage::helper('pickpack/gift')->getGiftWrapInfo($order, $wonder);
        if (isset($giftWrap_info['style'])) {
            $page->drawText('Giftwrap style: ' . $giftWrap_info['style'], $pageConfig['padded_left'], $this->subheader_start, 'UTF-8');
            $this->y -= $generalConfig['font_size_body'];
            $this->subheader_start -= $generalConfig['font_size_body'];
        }
        /***************************END PRINTING GIFT WRAP UNDER SHIPPING ADDRESS *******************************/
    }
}