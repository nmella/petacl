<?php /**
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
 * File        Combined.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Giftmessage extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    protected $_printing_format = array();
    protected $_product_config = array();
    protected $_order_config = array();
    protected $_helper = '';

    protected $_orderCollection = array();
    protected $_itemsCollection = array();
    protected $_productsCollection = array();

    protected $_totalItemsPerOrder = array();   

    protected $store_list = array();
    protected $product_list = '';   
    protected $product_list_arr = array();

    //Total ordered of each product
    protected $product_ordered = array();
    //Number of ordered of each product per order
    protected $product_ordered_per_order = array();

    protected $order_list_of_product = array();
    protected $order_list_of_product_per_option = array(); 

    protected $order_model_list = array();

    protected $product_model_list_arr = array();
    protected $product_order_list_arr = array();    
    protected $item_model_list_arr = array();

    protected $pre_print_time = 0;
    protected $next_print_time = 0;
    protected $max_print_time = 0;
    protected $runtime = 0;
    protected $pagecount =0;
	protected $_logo_maxdimensions = array();
	protected $_columns_xpos_array = array();
	protected $_columns_xpos_array_order = array();
    
    public function getGiftmessage($orders = array(),$from_shipment = 'order',$from ='') {

		$this->_logo_maxdimensions = explode(',', '269,41');
        //TODO 1: Get and set general configuration values
        $store_id = Mage::app()->getStore()->getId();
        
        $this->_beforeGetPdf();
        
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style     = new Zend_Pdf_Style();
        $page_size = $this->_getConfig('page_size', 'a4', false, 'general');        
        $this->_printing_format['padded_left'] = 20;
        
        if ($page_size == 'letter') {
            $page                                   = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $this->_printing_format['page_top']     = 770;
            $this->_printing_format['padded_right'] = 587;
        } elseif ($page_size == 'a4') {
            $page                                   = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $this->_printing_format['page_top']     = 820;
            $this->_printing_format['padded_right'] = 570;
        } elseif ($page_size == 'a5-landscape') {
            $page                                   = $pdf->newPage('596:421');
            $this->_printing_format['page_top']     = 395;
            $this->_printing_format['padded_right'] = 573;
        } elseif ($page_size == 'a5-portrait') {
            $page                                   = $pdf->newPage('421:596');
            $this->_printing_format['page_top']     = 573;
            $this->_printing_format['padded_right'] = 395;
        }
        $this->pagecount++;
//         Mage::log(memory_get_usage(),'null','moogento_pickpack.log');
        
        $pdf->pages[] = $page;
 
        $this->_printing_format['red_bkg_color']           = new Zend_Pdf_Color_Html('lightCoral');
        $this->_printing_format['grey_bkg_color']           = new Zend_Pdf_Color_GrayScale(0.85);
        //$this->_printing_format['alternate_row_color']      = new Zend_Pdf_Color_Html($this->_printing_format['alternate_row_color_temp']);
        $this->_printing_format['dk_grey_bkg_color']        = new Zend_Pdf_Color_GrayScale(0.3);
        $this->_printing_format['dk_cyan_bkg_color']        = new Zend_Pdf_Color_Html('darkCyan');
        $this->_printing_format['dk_og_bkg_color']          = new Zend_Pdf_Color_Html('darkOliveGreen');
        $this->_printing_format['white_bkg_color']          = new Zend_Pdf_Color_Html('white');
        $this->_printing_format['orange_bkg_color']         = new Zend_Pdf_Color_Html('Orange');
        $this->_printing_format['black_color']              = new Zend_Pdf_Color_Rgb(0, 0, 0);
        $this->_printing_format['grey_color']               = new Zend_Pdf_Color_GrayScale(0.3);
        $this->_printing_format['greyout_color']            = new Zend_Pdf_Color_GrayScale(0.6);
        $this->_printing_format['white_color']              = new Zend_Pdf_Color_GrayScale(1);
        
        $this->_printing_format['font_family_header_default']         = 'helvetica';
        $this->_printing_format['font_size_header_default']           = 16;
        $this->_printing_format['font_style_header_default']          = 'bolditalic';
        $this->_printing_format['font_color_header_default']          = 'darkOliveGreen';
        $this->_printing_format['font_family_subtitles_default']      = 'helvetica';
        $this->_printing_format['font_style_subtitles_default']       = 'bold';
        $this->_printing_format['font_size_subtitles_default']        = 15;
        $this->_printing_format['font_color_subtitles_default']       = '#222222';
        $this->_printing_format['background_color_subtitles_default'] = '#999999';
        $this->_printing_format['font_family_body_default']           = 'helvetica';
        $this->_printing_format['font_size_body_default']             = 10;
        $this->_printing_format['font_style_body_default']            = 'regular';
        $this->_printing_format['font_color_body_default']            = 'Black';
        
        $this->_printing_format['font_family_header'] = $this->_getConfig('font_family_header', $this->_printing_format['font_family_header_default'], false, 'general');
        $this->_printing_format['font_style_header']  = $this->_getConfig('font_style_header', $this->_printing_format['font_style_header_default'], false, 'general');
        $this->_printing_format['font_size_header']   = $this->_getConfig('font_size_header', $this->_printing_format['font_size_header_default'], false, 'general');
        $this->_printing_format['font_color_header']  = trim($this->_getConfig('font_color_header', $this->_printing_format['font_color_header_default'], false, 'general'));
        
        $this->_printing_format['font_family_body'] = $this->_getConfig('font_family_body', $this->_printing_format['font_family_body_default'], false, 'general');
        $this->_printing_format['font_style_body']  = $this->_getConfig('font_style_body', $this->_printing_format['font_style_body_default'], false, 'general');
        $this->_printing_format['font_size_body']   = $this->_getConfig('font_size_body', $this->_printing_format['font_size_body_default'], false, 'general');
        $this->_printing_format['font_color_body']  = trim($this->_getConfig('font_color_body', $this->_printing_format['font_color_body_default'], false, 'general'));
        
		$this->_printing_format['font_family_gift_message'] = $this->_getConfig('font_family_gift_message', 'helvetica', false, 'general', $store_id);
		$this->_printing_format['font_style_gift_message'] = $this->_getConfig('font_style_gift_message', 'italic', false, 'general', $store_id);
		$this->_printing_format['font_size_gift_message'] = $this->_getConfig('font_size_gift_message', 12, false, 'general', $store_id);
		$this->_printing_format['font_color_gift_message'] = trim($this->_getConfig('font_color_gift_message', '#222222', false, 'general', $store_id));
		
        $this->_printing_format['font_family_subtitles']           = $this->_getConfig('font_family_subtitles', $this->_printing_format['font_family_subtitles_default'], false, 'general');
        $this->_printing_format['font_style_subtitles']            = $this->_getConfig('font_style_subtitles', $this->_printing_format['font_style_subtitles_default'], false, 'general');
        $this->_printing_format['font_size_subtitles']             = $this->_getConfig('font_size_subtitles', $this->_printing_format['font_size_subtitles_default'], false, 'general');
        $this->_printing_format['font_color_subtitles']            = trim($this->_getConfig('font_color_subtitles', $this->_printing_format['font_color_subtitles_default'], false, 'general'));
        $this->_printing_format['background_color_subtitles']      = trim($this->_getConfig('background_color_subtitles', $this->_printing_format['background_color_subtitles_default'], false, 'general'));
        $this->_printing_format['background_color_subtitles_zend'] = new Zend_Pdf_Color_Html($this->_printing_format['background_color_subtitles']);
        $this->_printing_format['bkg_color_gift_message'] = trim($this->_getConfig('bkg_color_gift_message', '#5BA638', false, 'general', $store_id));
		
        $this->_printing_format['font_color_header_zend']    = new Zend_Pdf_Color_Html($this->_printing_format['font_color_header']);
        $this->_printing_format['font_color_subtitles_zend'] = new Zend_Pdf_Color_Html($this->_printing_format['font_color_subtitles']);
        $this->_printing_format['font_color_body_zend']      = new Zend_Pdf_Color_Html($this->_printing_format['font_color_body']);
        
        $this->_printing_format['bkg_color_gift_message_zend'] = new Zend_Pdf_Color_Html('' .  $this->_printing_format['bkg_color_gift_message'] . '');
		
        $this->_printing_format['non_standard_characters'] = $this->_getConfig('non_standard_characters', 0, false, 'general');
        
        $this->_printing_format['barcode_type'] = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $store_id);
       
        switch ($this->_printing_format['barcode_type']) {
            case 'code128':
                $font_family_barcode = 'Code128bWin.ttf';
                break;
            
            case 'code39':
                $font_family_barcode = 'CODE39.ttf';
                
                break;
            
            case 'code39x':
                $font_family_barcode = 'CODE39X.ttf';
                
                break;
            
            default:
                $font_family_barcode = 'Code128bWin.ttf';
                break;
        }
        
        $this->_printing_format['date_format'] = $this->_getConfig('date_format', 'M. j, Y', false, 'general');
        $group = 'gift_message';
       $gift_message_yn = $this->_getConfig('gift_message_yn', 1, false, $group, $store_id);
	   $product_gift_message_yn = $this->_getConfig('product_gift_message_yn', 1, false, $group, $store_id);
	   $message_title_tofrom_yn = $this->_getConfig('message_title_tofrom_yn', 'yes', false, $group, $store_id);
	   $gift_message_nudge = explode(',' , $this->_getConfig('gift_message_nudge', '0,0', false, $group, $store_id));
	   $max_width_gift_message = $this->_getConfig('max_width_gift_message', 0, false, $group, $store_id);
	   $show_orderid_yn = $this->_getConfig('show_orderid_yn', 0, false, $group, $store_id);
       $order_id_nudge = explode(',' , $this->_getConfig('order_id_nudge', '0,0', false, $group, $store_id));
       $image_background_nudge = explode(',' , $this->_getConfig('image_background_nudge', '0,0', false, $group, $store_id));
       $image_background_yn = $this->_getConfig('image_background_yn', 0, false, $group, $store_id);
       $angle_text_yn = $this->_getConfig('angle_text', 0, false, $group, $store_id);
        $this->_helper = Mage::helper('pickpack');
		
        if($gift_message_nudge[0] > 0)
			$msgX = $gift_message_nudge[0];
		else
			$msgX = $this->_printing_format['padded_left'];
		if($max_width_gift_message == "" || $max_width_gift_message ==0)
			$max_width_gift_message = $this->_printing_format['padded_right'] - $this->_printing_format['padded_left'];
        $helper = Mage::helper('pickpack');
		$first_page = '';
		   //1. Print header
        //$this->printHeader($page,$store_id);
		$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA); 
              
        $max_chars_message = $this->getMaxCharMessage($max_width_gift_message, $this->_printing_format['font_size_gift_message'], $font_temp);
        $option_group = "gift_message";
        $sub_folder = "gift_image_background";
		foreach ($orders as $orderSingle) {
            $has_page = true;
            $image = '';
			// if ($shipments[0] == 'shipment') {
                // $order = $helper->getOrderByShipment($orderSingle);
            // } else {

            $order = $helper->getOrder($orderSingle);
            
            //}
			if($first_page == 'n'){
                if(!is_null($order->getGiftMessageId())){
				    $page = $this->newPage();
                }
                else
                    $has_page = false;
            }
			else
				$first_page = 'n';
            if($image_background_yn == 1){
                $image = "top_left_ribbon.png";
            }
            elseif ($image_background_yn == 2) {
                $image = "ribbon.png";
            }
            elseif($image_background_yn == 3){
                $image = Mage::getStoreConfig('pickpack_options/' . $option_group . '/image_background_file', $order->getStore()->getId());
            }
            if ($image) {
                $filename = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $image;
                $image_ext = '';
                $temp_array_image = explode('.', $image);
                $imageObj        = Mage::helper('pickpack')->getImageObj($filename);
                    
                $orig_img_width  = $imageObj->getOriginalWidth();
                $orig_img_height = $imageObj->getOriginalHeight();

                $image_ext = array_pop($temp_array_image);
                if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png') || ($image_ext == 'PNG')) && (is_file($filename))) {
                    $image = Zend_Pdf_Image::imageWithPath($filename);
                    $page->drawImage($image, $image_background_nudge[0] + $this->_printing_format['padded_left'], $this->_printing_format['page_top'] - $orig_img_height + $image_background_nudge[1], $orig_img_width + $image_background_nudge[0], $this->_printing_format['page_top'] + $image_background_nudge[1]);
                }
            }

            if($gift_message_nudge[1] > 0)
                $this->y = $gift_message_nudge[1];
            else
                $this->y = $this->_printing_format['page_top'];
            
            $order_id = $order->getRealOrderId();
			$itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
            if($angle_text_yn == 1)
                $page->rotate(0, 0, 3.14/12);
			if ($gift_message_yn != 0) {
                $this->printOrderGiftMessage($page,$order,$gift_message_yn, $message_title_tofrom_yn,$max_chars_message,$max_width_gift_message, $msgX);    
            }
			
			if($product_gift_message_yn != 0){
				foreach($itemsCollection as $item){
                    if($has_page == false){
                        if($item->getGiftMessageId()){
                            $page = $this->newPage();
                            $has_page = true;
                        }
                    }
					$this->printProductGiftMessage($page, $order, $item, $gift_message_yn, $message_title_tofrom_yn, $product_gift_message_yn, $max_chars_message,$max_width_gift_message, $msgX);
				}
			}
            if($angle_text_yn == 1)
                $page->rotate(0, 0, 0 - 3.14/12);
            if($has_page == true && $show_orderid_yn == 1){
                $this->_setFont($page, $this->_printing_format['font_style_body'], ($this->_printing_format['font_size_body']*0.8), $this->_printing_format['font_family_body'], $this->_printing_format['non_standard_characters'], '#666666');
                $page->drawText($order_id, $order_id_nudge[0], $this->_printing_format['page_top'] - $order_id_nudge[1], 'UTF-8');
            }
		}
        $this->_afterGetPdf();
        return $pdf;
    }
	protected function getGiftwrap(){
		$giftWrap_info = array();
		$giftWrap_info['wrapping_paper'] = NULL;
		$giftWrap_info['message'] = NULL;

		if (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') || Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
			if (Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
				$quoteId = $order->getQuoteId();
				$selections = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
				$giftwrapCollection = array();
				if ($quoteId) {
					$giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
					foreach ($giftwrapCollection as $info_collection) {
						$giftWrap_info['message'] .= "\n" . $info_collection['giftwrap_message'];
						$style_gift = Mage::getModel('giftwrap/giftwrap')->load($info_collection['styleId']);
						if ($giftwrap_style_yn == 'yesbox') {
							$giftWrap_info['wrapping_paper'] .= $style_gift->getData('title');
						} else
							if ($giftwrap_style_yn == 'yesshipping') {
								$giftWrap_info['style'] .= $style_gift->getData('title');
							}
					}
				}


				$giftWrapInfos = Mage::getModel('giftwrap/giftwrap')
					->getCollection()
					->addFieldToFilter('store_id', '0');

				foreach ($giftWrapInfos as $info) {
					$giftWrap_info['message'] .= $info->getData('message');
					$giftWrap_info['wrapping_paper'] .= str_ireplace(array('.jpg', '.jpeg', '.gif', '.png'), '', $info->getData('image'));
				}


				/*
				 [giftcard_id] => 1
				 [status] => 1
				 [name] => Test Gift Card
				 [image] => Test gift image.png
				 [price] => 1.5000
				 [store_id] => 0
				 [message] =>
				 [character] => 200
				 [option_id] => 1
				 [default_name] => 1
				 [default_price] => 1
				 [default_image] => 1
				 [default_sort_order] => 1
				 [default_message] => 1
				 [default_status] => 1
				 [default_character] => 1
				 */
			} elseif (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') && (Mage::getModel('giftwrap/order'))) {
				/*
				 ["entity_id"]=>"2"
				 ["order_id"]=>"181"
				 ["message"]=>"happy birthday"
				 ["items"]=>"961"
				 ["fee"]=>"0"
				 ["giftbox_image"]=>"xmage_giftwrap/Screen shot 2011-09-06 at 2.33.42 PM.png"
				 ["giftcard_image"]=>"xmage_giftwrap/gift_card/giftwrap2.jpg"
				 ["giftcard_html"]=>"<div style="position: relative;"><p>Test content</p><div id="gift-textbox" class="drsElement drsMoveHandle" style="visibility: visible;position: absolute;width:100px; height:100px; top:10px; left:10px;"><div id="gift-content">happy birthday</div></div></div>"
				 */
				$orderId = $order->getId();
				$giftWrapInfos = Mage::getModel('giftwrap/order')->getCollection()->addFieldToFilter('order_id', $orderId);
				foreach ($giftWrapInfos as $info) {
					$giftWrap_info['message'] .= $info->getData('message');
					if (isset($giftWrap_info['wrapping_paper'])) $giftWrap_info['wrapping_paper'] .= ' | ';
					$giftWrap_info['wrapping_paper'] .= trim(str_ireplace(array('xmage_giftwrap/', '.jpg', '.jpeg', '.gif', '.png'), '', $info->getData('giftbox_image')));
				}
			}

			unset($giftWrapInfos);
			if (trim($giftWrap_info['wrapping_paper']) != '' && $prices_hideforgift_yn == 1) $prices_yn = 0;
		}
	}
	protected function printProductGiftMessage($page, $order, $item, $gift_message_yn, $message_title_tofrom_yn, $product_gift_message_yn, $max_chars_message,$max_width_gift_message, $msgX){
		//$gift_message_combined = $this->getProductGiftMessageUnderShip($order, $this->_printing_format['padded_right']);			
		$item_gift_message = array();
		$item_gift_message['has_message'] = 0;
		//$msgX = $this->_printing_format['padded_left'];
		$character_message_breakpoint = 96;
		//$gift_message = wordwrap($gift_message, 96, "\n", false);
		$background_color_temp = $this->_printing_format['bkg_color_gift_message_zend'];
		$bkg_color_gift_message = $this->_printing_format['bkg_color_gift_message'];
		$font_style_gift_message = $font_style_temp = $this->_printing_format['font_style_gift_message'];
		$font_family_gift_message = $font_family_temp = $this->_printing_format['font_family_gift_message'];
		$font_size_gift_message = $font_size_temp = $this->_printing_format['font_size_gift_message'];
		$font_color_gift_message = $font_color_temp = $this->_printing_format['font_color_gift_message'];
		$right_bg_gift_msg = $max_width_gift_message;
		$non_standard_characters = $this->_printing_format['non_standard_characters'];
		if (Mage::helper('giftmessage/message')->getIsMessagesAvailable('order_item', $item) && $item->getGiftMessageId()) {
			$item_gift_message['has_message'] = 1;
			$item_msg_array = $this->getItemGiftMessage($item, $max_chars_message);
			$item_gift_message['message-from'] = $item_msg_array[0];
			$item_gift_message['message-to'] = $item_msg_array[1];
			$item_gift_message['message-content'] = $item_msg_array[2];
			$gift_msg_array = $item_gift_message['message-content'];
			if ($item_gift_message['has_message'] == 1) {
				//TODO devsite
				$this->y -= $font_size_gift_message/2;
				
				$this->_setFont($page, $font_style_gift_message, ($font_size_gift_message - 1), $font_family_gift_message, $non_standard_characters, $font_color_gift_message);
				//$temp_height = 0;
				$line_tofrom = 0;
				if ($message_title_tofrom_yn == 1)
					$line_tofrom = 2.5;
				$msg_line_count = count($gift_msg_array) + $line_tofrom;
				if ($product_gift_message_yn != 0) {
					$temp_height = 0;
					foreach ($gift_msg_array as $gift_msg_line) {
						$temp_height += 2 * $font_size_temp;
					}
					if (($this->y - $temp_height) < 10 && count($gift_msg_array) > 0) {
						$page = $this->nooPage($page_size);
						$page_count++;
						$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
						if ($second_page_start == 'asfirst') $this->y = $items_header_top_firstpage;
						else $this->y = $page_top;

						$paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
						$paging_text_width = $this->widthForStringUsingFontSize($paging_text, $font_family_subtitles, ($font_size_subtitles - 2));
						$paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

						$page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
						$this->y = ($this->y - ($font_size_subtitles * 2));
						$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
					}
					//draw background gift message
					$left_bg_gift_msg = $msgX;
					$top_bg_gift_msg = ($this->y + $font_size_temp);
					$bottom_bg_gift_msg = ($this->y - ($msg_line_count * ($font_size_temp - 1)));
					// $page_temp = $page;
					// if ($gift_message_yn == "yesnewpage" && $product_gift_message_yn != "yesnewpage") {
						// $page = $page_before;
					// }
					$this->drawBackgroundGiftMessage($bkg_color_gift_message, $background_color_temp, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
					if ($message_title_tofrom_yn == 1) {
						$font_size_temp = $font_size_gift_message;
						$this->_setFont($page, 'bold', ($font_size_gift_message), $font_family_gift_message, $non_standard_characters, $font_color_gift_message);
						$this->y = $this->showToFrom($message_title_tofrom_yn, $item_gift_message['message-to'], $msgX + $font_size_temp / 3, $this->y, $item_gift_message['message-from'], $font_size_temp, $page);
					}
					$this->_setFont($page, $font_style_temp, ($font_size_temp - 1), $font_family_temp, $non_standard_characters, $font_color_temp);
					$this->y = $this->drawOrderGiftMessage($gift_msg_array, $msgX + $font_size_temp / 3, $font_size_temp, $this->y, $page);
					unset($gift_msg_array);
				}
			}
		}
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
    protected function printOrderGiftMessage($page, $order,$gift_message_yn, $message_title_tofrom_yn,$max_chars_message,$max_width_gift_message, $msgX) {
		/***********PRINTING ORDER GIFT MESSAGE***********/
		$msg_line_count = 0;
		$gift_sender = '';
		$gift_recipient = '';
		$gift_message = '';
		$gift_msg_array = array();
		$gift_message_item = Mage::getModel('giftmessage/message');
        $gift_message_id = $order->getGiftMessageId();
		$giftWrap_info = array();
		//if((!is_null($gift_message_id) || $giftWrap_info['message'] != NULL || $giftWrap_info['wrapping_paper'] != NULL)){	
		if((!is_null($gift_message_id))){	
			$gift_msg_array = $this->getOrderGiftMessage($gift_message_id, $gift_message_yn, $gift_message_item, $giftWrap_info, $gift_msg_array);
			$gift_sender = $gift_msg_array[1];
			$gift_recipient = $gift_msg_array[2];
			$gift_message = $gift_msg_array[0];
			
			$to_from = '';
			$to_from_from = '';
			if (isset($gift_recipient) && $gift_recipient != '') {
				$to_from .= 'To : ' . $gift_recipient;
			}
			if (isset($gift_sender) && $gift_sender != '') $to_from_from = 'From : ' . $gift_sender;
		}
		//TODO gift registry
		// if (isInstalled('Webtex_GiftRegistry')){
			// $customerId = $order->getData("customer_id");

			// $gift_registry = Mage::getModel("webtexgiftregistry/webtexgiftregistry")->load($customerId, "customer_id");
			// $gift_registry_message = '';
			// if(isset($gift_registry['registry_id']) && $gift_registry['registry_id'] != '') {
				// $gift_registry_message = 'This is a Gift Registry Order ' . '(' . $gift_registry["giftregistry_id"] . ')'  ;
				// $gift_message = $gift_message . $gift_registry_message;
			// }
		// }
		if($gift_message != ''){
			
			//$msgX = $this->_printing_format['padded_left'];
			$character_message_breakpoint = 96;
			$gift_message = wordwrap($gift_message, $max_chars_message, "\n", false);
			$background_color_temp = $this->_printing_format['bkg_color_gift_message_zend'];
			$bkg_color_gift_message = $this->_printing_format['bkg_color_gift_message'];
			$font_style_temp = $this->_printing_format['font_style_gift_message'];
			$font_family_temp = $this->_printing_format['font_family_gift_message'];
			$font_size_gift_message = $font_size_temp = $this->_printing_format['font_size_gift_message'];
			$font_color_temp = $this->_printing_format['font_color_gift_message'];
			$right_bg_gift_msg = $max_width_gift_message;
			$non_standard_characters = $this->_printing_format['non_standard_characters'];
			$gift_msg_array = $this->createMsgArray($gift_message);
			
			$line_tofrom = 0;
			if ($message_title_tofrom_yn == 1)
				$line_tofrom = 2.5;
			$msg_line_count = count($gift_msg_array) + $line_tofrom;
			// Caculate necessary height for print gift message.
			$temp_height = 0;
			foreach ($gift_msg_array as $gift_msg_line) {
				$temp_height += 2 * $font_size_temp;
			}
			
			$flag_print_newpage = 0;
			if (($this->y - $temp_height) < 10) {
				$page = $this->newPage();
				$page_count++;
				$this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
				if ($second_page_start == 'asfirst') $this->y = $items_header_top_firstpage;
				else $this->y = $page_top;

				$paging_text = '-- ' . $order_number_display . ' | ' . $helper->__('Page') . ' ' . $page_count . ' --';
				$paging_text_width = $this->widthForStringUsingFontSize($paging_text, $font_family_subtitles, ($font_size_subtitles - 2));
				$paging_text_x = (($padded_right / 2) - ($paging_text_width / 2));

				$page->drawText($paging_text, $paging_text_x, ($this->y), 'UTF-8');
				$this->y = ($this->y - ($font_size_subtitles * 2));
				$flag_print_newpage = 1;
				$this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
			} else
				$this->y -= $font_size_temp * 1.7;
			$left_bg_gift_msg = $msgX;

			
			$bottom_bg_gift_msg = $this->y - ($msg_line_count - 1) * ($font_size_temp + 3) - 5;
			
			$top_bg_gift_msg = ($this->y + $font_size_temp);

			$this->drawBackgroundGiftMessage($bkg_color_gift_message, $background_color_temp, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
			$this->_setFont($page, 'bold', ($font_size_temp), $font_family_temp, $non_standard_characters, $font_color_temp);
			$this->y = $this->showToFrom($message_title_tofrom_yn, $to_from, $msgX + 4, $this->y, $to_from_from, $font_size_temp, $page);
			$this->_setFont($page, $font_style_temp, ($font_size_gift_message - 1), $font_family_temp, $non_standard_characters, $font_color_temp);
            $this->y = $this->drawOrderGiftMessage($gift_msg_array, $msgX + $font_size_temp / 3, $font_size_temp, $this->y, $page);
			//TODO need to test this case again.
			unset($gift_msg_array);
			if (isset($giftWrap_info['wrapping_paper'])) {
				$wrapping_paper_text = trim($giftWrap_info['wrapping_paper']);
				if ($wrapping_paper_text != '') {
					if ($gift_message_yn == 'yesnewpage') {
						$this->y -= ($font_size_gift_message + 3);
						if (strtoupper($bkg_color_message) != '#FFFFFF') {
							$page->setFillColor($bkg_color_message_zend);
							$page->setLineColor($bkg_color_message_zend);
							$page->setLineWidth(0.5);
							$page->drawRectangle($padded_left, ($this->y - ($font_size_gift_message / 2)), $padded_right, ($this->y + $font_size_gift_message + 2));
						}

						$this->_setFont($page, $font_style_gift_message, ($font_size_gift_message), $font_family_gift_message, $non_standard_characters, $font_color_gift_message);

						$this->y -= ($font_size_gift_message + 2);
						$page->drawText($helper->__('Wrapping Paper Selected'), ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
					} else {
						$this->_setFont($page, 'bold', ($font_size_gift_message), $font_family_gift_message, $non_standard_characters, $font_color_gift_message);

						$this->y -= ($font_size_gift_message + 2);
						$page->drawText($helper->__('Wrapping Paper Selected'), ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
					}
					$this->y -= ($font_size_gift_message + 2);
					$this->_setFont($page, 'regular', ($font_size_gift_message - 1), $font_family_gift_message, $non_standard_characters, $font_color_gift_message);
					$page->drawText($wrapping_paper_text, ($msgX + $font_size_gift_message), $this->y, 'UTF-8');
				}
			}
		}
	}
	private function drawBackgroundGiftMessage($bkg_color_gift_message, $background_color_temp, $page, $left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg) {
        if (($bkg_color_gift_message != '') && ($bkg_color_gift_message != '#FFFFFF')) {
            $page->setFillColor($background_color_temp);
            $page->setLineColor($background_color_temp);
            $page->setLineWidth(0.5);
            $page->drawRectangle($left_bg_gift_msg, $top_bg_gift_msg, $right_bg_gift_msg, $bottom_bg_gift_msg);
        }
    }
	
	protected function getMaxCharsPrinting($name, $column, $columnX){
		$next_col_to_product_x = getPrevNext2($this->_columns_xpos_array, $columnX, 'next');
		$max_name_length = $next_col_to_product_x - $this->_product_config[$column]['Xpos'];
		$font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$line_width_name = $this->parseString($name, $font_temp_shelf2, ($this->_printing_format['font_size_body']));
		$char_width_name = $line_width_name / strlen($name);
		$max_chars_name = round($max_name_length / $char_width_name);
		return $max_chars_name;
	}
	
	protected function getMaxCharsPrinting2($name, $column, $columnX){
		$next_col_to_product_x = getPrevNext2($this->_columns_xpos_array_order, $columnX, 'next');
		$max_name_length = $next_col_to_product_x - $this->_order_config[$column]['Xpos'];
		$font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$line_width_name = $this->parseString($name, $font_temp_shelf2, ($this->_printing_format['font_size_body']));
		$char_width_name = $line_width_name / strlen($name);
		$max_chars_name = round($max_name_length / $char_width_name);
		return $max_chars_name;
	}
	
    protected function checkMultiLineName($name){
    	//$name = htmlentities($name);
		$multiline_name = array();
		//$name = Mage::helper('pickpack/functions')->clean_method($name, 'pdf_more');
		$max_chars_print = $this->getMaxCharsPrinting($name, 'name', 'nameX');
		if(strlen($name) > $max_chars_print){
			$multiline_name = explode("\n", wordwrap($name, $max_chars_print, "\n"));
		}
		return count($multiline_name);
	}
	
}