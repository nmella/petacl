<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Custommessage extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public $y;
    public $pageFooterHeight;
    public $message_yn = 'no';
    public $min_product_y;
    public $start_page_for_order;
    public $order_number_display;
    public $items_header_top_firstpage;
    public $bottom_message_pos;
    public $has_shown_product_image;
    public $img_height;

    public function __construct($arguments) {
        parent::__construct($arguments);
    }

    public function showCustomMessage() {
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();

        $this->message_yn = $this->_getConfig('custom_message_yn', '', false, $wonder, $storeId);
        if (($this->message_yn == 'yesimage')) {
            $this->showCustomMessageImage();
        }
        else {
            $this->showCustomMessageText();
        }
    }

    public function showCustomMessageImage() {
        $page = $this->getPage();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $custom_message_image_locked_yn = $this->_getConfig('custom_message_image_locked_yn', 0, false, $wonder, $storeId);
        $custom_message_image_nudge = explode(',', trim($this->_getConfig('custom_message_image_nudge', '0,0', false, $wonder, $storeId)));

        $this->y -= 40;

        // 2250 x 417  (540 x 100)
        // Dimensions 540pt(A4)|562pt(US Letter) x 100pt @ 300dpi : non-interlaced .png
        $packlogo_filename = $this->_getConfig('custom_message_image', null, false, $wonder, $storeId);

        if ($packlogo_filename) {
            $sub_folder = 'message_invoice';
            if ($packlogo_filename) {
                $packlogo_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $packlogo_filename;
                $dirImg = $packlogo_path;
                $imageObj = new Varien_Image($dirImg);
                $bottom_image_width = $imageObj->getOriginalWidth();
                $bottom_image_height = $imageObj->getOriginalHeight();
                $image_ext = substr($packlogo_path, strrpos($packlogo_path, '.') + 1);
            }

            if (isset($image_ext) && (($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($packlogo_path))) {
                if ($pageConfig['page_size'] == "letter")
                    $logo_maxdimensions2 = explode(',', '612,41');
                elseif ($pageConfig['page_size'] == "a4")
                    $logo_maxdimensions2 = explode(',', '595,41');
                else
                    $logo_maxdimensions2 = explode(',', '556,41');
                try {
                    if ($bottom_image_width > $logo_maxdimensions2[0]) {
                        $bottom_img_height = ceil(($logo_maxdimensions2[0] / $bottom_image_width) * $bottom_image_height);
                        $bottom_img_width = $logo_maxdimensions2[0];
                    } 
					elseif ($bottom_image_height > $logo_maxdimensions2[1]) {
                            $temp_var = $logo_maxdimensions2[1] / $bottom_image_height;
                            $bottom_img_height = $logo_maxdimensions2[1];
                            $bottom_img_width = $temp_var * $bottom_image_width;
                    }
                    
					if ($this->y < (20 + $bottom_img_height)) {
                        $page = $this->newPage();
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        if ($generalConfig['second_page_start'] == 'asfirst')
							$this->y = $this->items_header_top_firstpage;
                        else
							$this->y = $pageConfig['page_top'];

                        $paging_text = '-- ' . $this->order_number_display . ' | ' . Mage::helper('pickpack')->__('Page') . ' ' . $this->getPageCount() . ' --';
                        $paging_text_width = $this->widthForStringUsingFontSize($paging_text, $generalConfig['font_family_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_style_subtitles'], $generalConfig['non_standard_characters']);
                        $paging_text_x = (($pageConfig['padded_right'] / 2) - ($paging_text_width / 2));

                        $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                        $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    }
                    $bottom_image_x1 = 0;
                    $bottom_image_x2 = $bottom_img_width;
                    $bottom_image_y1 = 0;
                    $bottom_image_y2 = $bottom_img_height;
                    $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);
                    if ($custom_message_image_locked_yn == 1) {
                        $bottom_image_y2 = (10 + $bottom_img_height);
                        $bottom_image_y1 = 10;

                        $bottom_image_x1 += $custom_message_image_nudge[0];
                        $bottom_image_x2 += $custom_message_image_nudge[0];
                        $bottom_image_y1 += $custom_message_image_nudge[1];
                        $bottom_image_y2 += $custom_message_image_nudge[1];
                        $this->getPage($this->start_page_for_order)->drawImage($packlogo, $bottom_image_x1, $bottom_image_y1, $bottom_image_x2, $bottom_image_y2);
                    }
                    else
                        $page->drawImage($packlogo, $bottom_image_x1, $bottom_image_y1, $bottom_image_x2, $bottom_image_y2);
                } catch (Exception $e) {
                }
            }
        }
    }

    public function showCustomMessageText() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $customer_group = ucwords(strtolower(Mage::getModel('customer/group')->load((int)$order->getCustomerGroupId())->getCode()));

        $positional_message_box_fixed_position_demension_x = $this->_getConfig('positional_message_box_fixed_position_demension', 250, false, $wonder, $storeId);

        $bkg_color_comments = new Zend_Pdf_Color_Html($generalConfig['bkg_color_comments']);
        $bkg_color_gift_message_zend = new Zend_Pdf_Color_Html($generalConfig['bkg_color_gift_message']);

        $message_filter = trim($this->_getConfig('custom_message_filter', '', false, $wonder, $storeId));
        $custom_message_fixed = $this->_getConfig('custom_message_fixed', 0, false, $wonder, $storeId);

        $message = trim($this->_getConfig('custom_message', '', false, $wonder, $storeId));
        $messageA = trim($this->_getConfig('custom_messageA', '', false, $wonder, $storeId));
        $messageB = trim($this->_getConfig('custom_messageB', '', false, $wonder, $storeId));
        if ($this->message_yn == 'yes2') {
            if (strpos(strtolower($message_filter), strtolower($customer_group)) !== false)
                $message = $messageB;
            else
                $message = $messageA;
        }
        elseif ($this->message_yn == 'yesbox')
            $message = $this->_getConfig('custom_message_yesbox', '', false, $wonder, $storeId);
        elseif($this->message_yn == 'no') {
            $message = null;
            $messageA = null;
            $messageB = null;
        }

        $strip_message_line_break = $this->_getConfig('strip_message_line_break', 0, false, 'general', $storeId);
        if($strip_message_line_break == 1){
            $message = preg_replace('/\s+/', ' ', $message);
        }

        if ($message != null) {
            // Add variables to custom message box
           	$grand_total_value = $this->formatPriceTxt($order, number_format(floatval($order->getGrandTotal()), 2, '.', ','));			
			// $grand_total_value = Mage::helper('core')->currency($order->getGrandTotal(), true, false);
			// $orderSymbolCode = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol();

            $message = preg_replace('~\%order_id\%~', "#".$order->getIncrementId(), $message);
            $message = preg_replace('~\%grand_total\%~', $grand_total_value, $message);
			// End add variables to custom message box

            $next_page_box = 0;
            $custom_message_position = array();
            if ($this->message_yn == 'yesbox') {
                if($strip_message_line_break == 1){
                    $message = preg_replace('/\s+/', ' ', $message);
                }
                $custom_message_position = explode(',', trim($this->_getConfig('positional_message_box_fixed_position', '20,200', false, $wonder, $storeId)));
                if($this->y < $custom_message_position[1] - 10)
                    $next_page_box = 1;

                //$maxWidthPage = ($padded_right + 20 - $custom_message_position[0] - 20);
                $maxWidthPage = ($positional_message_box_fixed_position_demension_x);
                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $font_size_compare = ($generalConfig['font_size_comments']);
                $line_width = $this->parseString($message, $font_temp, $font_size_compare); // bigger = left
                $char_width = $line_width / strlen($message);
                $max_chars = round($maxWidthPage / $char_width);

                if (strlen($message) > $max_chars)
                    $chunks = explode("\n", wordwrap($message, $max_chars, "\n"));
                else $chunks = explode("\n", $message);
                $line_count = count($chunks);

                $custom_message_box_left = ($custom_message_position[0] - 7);
                $custom_message_box_right = $custom_message_position[0] + $positional_message_box_fixed_position_demension_x + 15;

            } else {
                $custom_message_box_left = $pageConfig['padded_left'];
                $custom_message_box_right = $pageConfig['padded_right'];

                // shift up message box
                if ($this->has_shown_product_image == 1) $this->y += ($this->img_height / 2);
                $message_array = explode("\n", $message);
                $line_count = count($message_array);
                $this->y -= (($generalConfig['font_size_subtitles'] - 4) / 2);
            }
            if ($this->y < (20 + ($line_count + 1) * ($generalConfig['font_size_comments'] + 2)) || $next_page_box == 1) {
                $page = $this->newPage();
                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                if ($generalConfig['second_page_start'] == 'asfirst')
					$this->y = $this->items_header_top_firstpage;
                else
					$this->y = $pageConfig['page_top'];

                $paging_text = '-- ' . $this->order_number_display . ' | ' . Mage::helper('pickpack')->__('Page') . ' ' . $this->getPageCount() . ' --';
                $paging_text_width = $this->widthForStringUsingFontSize($paging_text, $generalConfig['font_family_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_style_subtitles'], $generalConfig['non_standard_characters']);
                $paging_text_x = (($pageConfig['padded_right'] / 2) - ($paging_text_width / 2));

                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
            }
            if (strtoupper($generalConfig['bkg_color_message']) != '#FFFFFF' && $this->message_yn != 'yes' && $this->message_yn != 'yes2') {
                $page->setFillColor($bkg_color_comments);
                $page->setLineColor($bkg_color_comments);
                $page->setLineWidth(0.5);
				//$custom_message_position for custom postition of message
                if ($this->message_yn == 'yesbox')
                    $page->drawRectangle($custom_message_box_left, ($custom_message_position[1]- ($line_count * ($generalConfig['font_size_comments'] + 2)) - 7), $custom_message_box_right, ($custom_message_position[1] + 11 - 10));
                else
                    $page->drawRectangle($custom_message_box_left, ($this->y - ($line_count * ($generalConfig['font_size_comments'] + 2)) - 7), $custom_message_box_right, ($this->y + 11 - 10));
            }

            $this->_setFont($page, $generalConfig['font_style_comments'], $generalConfig['font_size_comments'], $generalConfig['font_family_comments'], $this->_general['non_standard_characters'], $generalConfig['font_color_gift_message']);
			
            if ($this->message_yn == 'yesbox') {
                if (isset($chunks) && is_array($chunks)) {
                    $temp_y_line = $custom_message_position[1];
                    foreach ($chunks as $chunk) {
                        if ($chunk != '') {
                            $temp_y_line -= ($generalConfig['font_size_comments'] + 2);
                            $page->drawText($chunk, ($custom_message_position[0]), $temp_y_line, 'UTF-8');
                        }
                    }
                    unset($temp_y_line);
                    unset($chunks);
                }
            }
            elseif (($this->message_yn != 'yes') && ($this->message_yn != 'yes2')) {
                if(isset($message_array)) {
                    foreach ($message_array as $value) {
                        if ($generalConfig['non_standard_characters'] == 0)
                            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        else
                            $font_temp = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
						
                        $line_width = ceil($this->parseString($value, $font_temp, ($generalConfig['font_size_message'] * 0.96))); //*0.77)); // bigger = left

                        $left_margin = ceil((($pageConfig['padded_right'] - $line_width) / 2));
                        if ($left_margin < 0) $left_margin = 0;

                        if ($line_width == 0) // some issue with non-standard fonts
                            $left_margin = 25;

                        if (isset($value) && isset($left_margin) && ($this->y > 9)) 
							$page->drawText($value, $left_margin, $this->y, 'UTF-8');
                        $this->y -= ($generalConfig['font_size_message'] + 2);
                        if ($this->y < 10) 
							$this->y = 10;
                    }
                }
            }
        }

        $line_count_message = 0;
        if ($this->message_yn == 'yes' || $this->message_yn == 'yes2') {
            if (!isset($custom_message_box_left)) 
				$custom_message_box_left = $pageConfig['padded_left'];
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $max_chars = $this->getMaxCharMessage2($message, $pageConfig['padded_right'], $generalConfig['font_size_message'], $font_temp, $custom_message_box_left);
            $message_array = explode("\n", wordwrap($message, $max_chars, "\n"));
            if (!isset($line_count_message) || ($line_count_message == 0))
                $line_count_message = count($message_array);

            $this->y -= ($generalConfig['font_size_message'] * 1.5);
            $temp_height = ($line_count_message * ($generalConfig['font_size_message'] + 2)) + 10;

            if (($this->y - $temp_height) < $this->pageFooterHeight) {
                $page = $this->newPage();
                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                if ($generalConfig['second_page_start'] == 'asfirst')
                    $this->y = $this->items_header_top_firstpage;
                else
                    $this->y = $pageConfig['page_top'];
                $paging_text = '-- ' . $this->order_number_display . ' | ' . Mage::helper('pickpack')->__('Page') . ' ' . $this->getPageCount() . ' --';
                $paging_text_width = $this->widthForStringUsingFontSize($paging_text, $generalConfig['font_family_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_style_subtitles'], $generalConfig['non_standard_characters']);
                $paging_text_x = (($pageConfig['padded_right'] / 2) - ($paging_text_width / 2));

                $page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
                $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 1.5));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            }

            if ($generalConfig['bkg_color_gift_message'] != '#FFFFFF') {
                $page->setFillColor($bkg_color_gift_message_zend);
                $page->setLineColor($bkg_color_gift_message_zend);
                $page->setLineWidth(0.5);
				
                if ($generalConfig['fill_bkg_gift_message_yn'] == 0) {
                    $page->drawLine($custom_message_box_left, ($this->y), $pageConfig['padded_right'], ($this->y));
                    $page->drawLine($custom_message_box_left,  ($this->y - ($line_count_message * ($generalConfig['font_size_message'] + 2)) - 10), $pageConfig['padded_right'],  ($this->y - ($line_count_message * ($generalConfig['font_size_message'] + 2)) - 10));
                    $page->drawLine($custom_message_box_left, ($this->y), $custom_message_box_left,  ($this->y - ($line_count_message * ($generalConfig['font_size_message'] + 2)) - 10));
                    $page->drawLine($pageConfig['padded_right'], ($this->y - ($line_count_message * ($generalConfig['font_size_message'] + 2)) - 10), $pageConfig['padded_right'],  ($this->y));
                }
                else
                    $page->drawRectangle($custom_message_box_left, ($this->y - ($line_count_message * ($generalConfig['font_size_message'] + 2)) - 10), $pageConfig['padded_right'], ($this->y));
            }

            $this->bottom_message_pos = ($this->y - ($line_count_message * ($generalConfig['font_size_message'] + 2)) - 10);
            $this->_setFont($page, $generalConfig['font_style_message'], $generalConfig['font_size_message'], $generalConfig['font_family_message'], $this->_general['non_standard_characters'], $generalConfig['font_color_gift_message']);
            $this->y -= ($generalConfig['font_size_message'] * 1.25);
            foreach ($message_array as $value) {
                if ($generalConfig['non_standard_characters'] == 0)
                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                else
                    $font_temp = Zend_Pdf_Font::fontWithPath($this->action_path . 'arial.ttf');
                $line_width = ceil($this->parseString($value, $font_temp, ($generalConfig['font_size_message'] * 0.96))); //*0.77)); // bigger = left
                $left_margin = 0;
                
				if($custom_message_fixed == 0)
                    $left_margin = ceil((($pageConfig['padded_right'] - $line_width) / 2));
                
				if ($left_margin < 0) 
					$left_margin = 0;
                
				if ($line_width == 0 || $custom_message_fixed== 1) // some issue with non-standard fonts
                    $left_margin = 25;

                if (isset($value) && isset($left_margin) && ($this->y > 9))
					$page->drawText($value, $left_margin, $this->y, 'UTF-8');
                $this->y -= ($generalConfig['font_size_message'] + 2);
                
				if ($this->y < 10) 
					$this->y = 10;
            }
        }
    }

    protected function getMaxCharMessage2($message, $padded_right, $font_size_options, $font_temp, $padded_left) {
        $maxWidthPage_message = $padded_right - $padded_left - 10;
        $font_size_compare_message = $font_size_options;
        $line_width_message = $this->parseString($message, $font_temp, $font_size_compare_message);
        $char_width_message = $line_width_message / strlen($message);
        $max_chars_message = round($maxWidthPage_message / $char_width_message);
        return $max_chars_message;
    }
}