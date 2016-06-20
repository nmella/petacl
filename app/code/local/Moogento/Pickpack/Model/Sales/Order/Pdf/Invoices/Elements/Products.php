<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

/**
 * @method int getData()
 */
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Products extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public $subtotal_data = array();
    public $tax_percents_total = array();
    public $tax_percents = array();
    public $tax_rate_code = array();
    public $print_item_count = 0;
    public $bundle_options_x = 0;
    public $items_header_top_firstpage;
    public $order_number_display;
    public $hide_bundle_parent_f = false;
    public $minDistanceSku;
    public $yItemPos;
    public $yItemPosCombine;
    public $flag_image_newpage = 0;
    public $has_shown_product_image = 0;
    public $img_width = 0;
    public $img_height = 0;
    public $chunk_display;
    public $gift_message_array = array();
    public $itemQtyArray = array();

    public $before_print_image_y;
    public $befor_print_image_y_newpage;
    public $after_print_image_y;
    public $after_print_image_y_newpage;

    public $sku_supplier_item_action = array();
    public $sku_supplier_item_action_master = array();

    public $product_count = 0;

    public $count_item = 0;

    public $first_item_title_shift_sku   = 0;
    public $first_item_title_shift_items = 0;

    public $max_chars_message;
    public $discount_line_or_subtotal;

    public $min_product_y = 0;
    public $options_y_counter = 0;
    public $next_product_line_ypos = null;
    public $temp_count = 0;
    public $temp_bundle_count = 0;
    public $maxOffset = 0;

    public $order_subtotal_value = 0;
    public $vat_rateable_value = 0;
    public $order_item_count = 0;
    public $show_top_right_gift_icon = false;

    public $custom_attribute_combined_array = array();
    public $subtotal_addon = array();
    public $childArray = array();
    public $product_build = array();

    public $page_shelving_1;
    public $page_count_shelving_1 = 0;
    public $flag_print_shelving_1 = false;
    public $arr_page_y_shelving_1 = array();
    public $shelving_y_pos;
    public $max_y_1;

    protected $_sku_array;
    protected $sku_ProductId = array();
    protected $columns_xpos_array = array();

    public $fontColorBodyItem;

    public $subheader_start;
    public $isShipment;

    protected $_itemCollection;

    public function __construct($arguments) {
        parent::__construct($arguments);
    }

    public function caculateDefaultValue(){
        $this->columns_xpos_array = $this->preparePositionArrayX();
    }

	/*
		Returns number of pages in PDF
	*/
	private function countPdfPages(){
		$page = $this->getPage();
		
		$page_count = ($this->getPdf()->_PdfPageCount + 1);
		if( isset($page_count) && ($page_count > 1) )
			return $page_count;
		else return false;
	}
	
	/*
		Return minimum y-pos for product line for current page
	*/
	private function getMinProductY() {
		$pageConfig = $this->getPageConfig();
		$generalConfig = $this->getGeneralConfig();
		
        /***************************FIRST PAGE SETTING**********************/
        if ($this->countPdfPages() == 0) {
			$addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
			
			// If printing bottom shipping address
			if (
				($this->packingsheetConfig['pickpack_bottom_shipping_address_yn'] == 1) || 
				($this->packingsheetConfig['pickpack_return_address_yn'] == 1)
				)
			    $this->min_product_y = ($addressFooterXY[1] + ($this->packingsheetConfig['pickpack_shipfont'] * 2));

				// @TODO check for bottom shipping label print, and increase minimum to top of label if we're going to print
				// @TODO check for CN22 print, and increase minimum to top of label if we're going to print
			
			// Check for manual y-pos override	
			if ($this->packingsheetConfig['page_1_products_y_cutoff'] > $this->min_product_y)
			    $this->min_product_y = $this->packingsheetConfig['page_1_products_y_cutoff'];
			
			// Check for page padding
			if ($pageConfig['page_bottom'] > $this->min_product_y)
			    $this->min_product_y = $pageConfig['page_bottom'];
			
			$this->min_product_y += $generalConfig['font_size_body'];
        } else
			$this->min_product_y = 10 + $pageConfig['page_bottom'];
		return $this->min_product_y;
	}

	/*
		Check if we need to make a new page
	*/
	private function checkNewPageNeeded($specific_section = '', $specific_option = ''){
		$helper = Mage::helper('pickpack');
		$pageConfig = $this->getPageConfig();
		$generalConfig = $this->getGeneralConfig();
		
		if ( 
			($this->y < $pageConfig['page_bottom']) || 
			($this->y < $this->getMinProductY())
			) {
			$page = $this->newPage();
            $black_color = Mage::helper('pickpack/config_color')->getPdfColor('black_color');
            $red_color = Mage::helper('pickpack/config_color')->getPdfColor('red_color');
            $white_color = Mage::helper('pickpack/config_color')->getPdfColor('white_color');
            $greyout_color = Mage::helper('pickpack/config_color')->getPdfColor('greyout_color');

			if ($generalConfig['second_page_start'] == 'asfirst')
				$this->y = $this->items_header_top_firstpage;
			else
				$this->y = $pageConfig['page_top'];

			$this->drawPageNumber();

			$this->y = ($this->y - ($generalConfig['font_size_body'] * 2));
			
			$this->drawProductTitlebars();
			$this->drawProductTitles();

            $bundle_options_x = ($this->packingsheetConfig['qty_x_pos'] + $this->packingsheetConfig['shift_bundle_children_xpos']);
            $bundle_line_x2 = (($this->packingsheetConfig['tickboxX'] + 3) + (strlen('Bundle Options : ') * ($generalConfig['font_size_body'] - 2)) + $this->packingsheetConfig['shift_bundle_children_xpos'] + 20);
			
			if($specific_section == 'bundle') {
				$bundle_before = $specific_option;
                if ($bundle_before == 1)
                    $this->_drawText($helper->__('Bundle Options Cont\'d...') . ' : ', $bundle_options_x, $this->y);
                else
                    $this->_drawText($helper->__('Bundle Options') . ' : ', $bundle_options_x, $this->y);
				
                $this->getPage()->setLineWidth(0.5);
                $this->getPage()->setFillColor($white_color);
                $this->getPage()->setLineColor($greyout_color);
                $this->getPage()->drawLine(($bundle_options_x), ($this->y - 2), $bundle_line_x2, ($this->y - 2));
                $this->getPage()->setFillColor($black_color);
                $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
			} else
				$this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
			
			if($specific_option == 'shelving')
				$this->arr_page_y_shelving_1[$this->page_count_shelving_1] = $this->y;
			
			return true;
		} else
			return false;
	}
	
	/*
		Draws the page number on the page base or top
		Usually this will show page top of 2+ page orders
	*/
	private function drawPageNumber(){
		$helper = Mage::helper('pickpack');
        $page = $this->getPage();
		$pageConfig = $this->getPageConfig();
		$generalConfig = $this->getGeneralConfig();
		
		$this->_setFont($page, 'semibold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
		if($this->countPdfPages()) {
			$paging_text = '-- ' . $helper->__('Page') . ' ' . $this->countPdfPages() . ' --';
	        $paging_text_width = $this->widthForStringUsingFontSize($paging_text, $generalConfig['font_family_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_style_body'], $generalConfig['non_standard_characters']);
	        $paging_text_x = (($pageConfig['padded_right'] / 2) - ($paging_text_width / 2));
	        $this->_drawText($paging_text, $paging_text_x, ($this->y));		
			$this->y -= ($generalConfig['font_size_body'] * 1.7);
			
			return true;
		}
		
		return false;
	}
	
	/*
		Draws the product titlebars
	*/
	private function drawProductTitlebars(){
		$pageConfig = $this->getPageConfig();
		$generalConfig = $this->getGeneralConfig();

        $fill_product_header_yn = 1;

        $invoice_title = $this->packingsheetConfig['pickpack_title_pattern'];
        $invoice_title_temp = $invoice_title;
        $invoice_title_temp = explode("\n", $invoice_title_temp);
        $invoice_title_linebreak = count($invoice_title_temp);

        $fillbar_padding = explode(",", $this->generalConfig['fillbar_padding']);
		
		if (strtoupper($generalConfig['background_color_subtitles']) != '#FFFFFF') {
		    $line_widths = explode(",", $generalConfig['bottom_line_width']);
			$background_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['background_color_subtitles']);
		    $this->getPage()->setFillColor($background_color_subtitles_zend);
		    $this->getPage()->setLineColor($background_color_subtitles_zend);
		    $this->getPage()->setLineWidth(0.5);

		    if ($fill_product_header_yn == 0) {
		        switch( $generalConfig['fill_bars_subtitles'] ) {
		            case 0 :
		                $this->getPage()->drawLine($pageConfig['padded_left'], ($this->y - ($generalConfig['font_size_subtitles'] / 2) - 2), ($pageConfig['padded_right']), ($this->y - ($generalConfig['font_size_subtitles'] / 2) - 2));
		                $this->getPage()->drawLine($pageConfig['padded_left'], ($this->y + $generalConfig['font_size_subtitles'] + 2 + 2), ($pageConfig['padded_right']), ($this->y + $generalConfig['font_size_subtitles'] + 2 + 2));
		                break;
		            case 1 :
		                if ($invoice_title_linebreak <= 1) {							
	                        $bottom_fillbar = ceil($this->y - ($this->generalConfig['font_size_subtitles'] / 2)) - $fillbar_padding[1] + 1;
	                        $top_fillbar = ceil($this->y + $this->generalConfig['font_size_subtitles'] + 2) + $fillbar_padding[0] - 3;
		                    // set product line so:
							//  - top/bottom line are same height
							//  - if titlebar set to top=0 match that here
							//  - if titlebar set to bottom=0 match that here
							//  - both lines to match the bottom titlebar width	
							//  - match the padding for titlebar
																											
							if(isset($line_widths[0]) && $line_widths[0] > 0){
		                       if(isset($line_widths[1]) && $line_widths[1] > 0)
								   $this->getPage()->setLineWidth($line_widths[1]-0.5); // set to the bottom bar, if set
							   elseif(isset($line_widths[0]) && $line_widths[0] > 0)
								   $this->getPage()->setLineWidth($line_widths[0]-0.5); // set to the top bar, if bottom not set
							   
		                        $this->getPage()->drawLine($pageConfig['padded_left'], $top_fillbar, ($pageConfig['padded_right']), $top_fillbar);
								$this->getPage()->drawLine($pageConfig['padded_left'], $bottom_fillbar, ($pageConfig['padded_right']), $bottom_fillbar);
		                    }
		                }
		                break;
		            case 2 :
		                break;
		        }
		    } else {
		        switch( $generalConfig['fill_bars_subtitles'] ){
		            case 0 :
		                $this->getPage()->drawRectangle($pageConfig['padded_left'], ($this->y - ($generalConfig['font_size_subtitles'] / 2)), $pageConfig['padded_right'], ($this->y + $generalConfig['font_size_subtitles'] + 2));
		                break;
		            case 1 :
		                if ($invoice_title_linebreak <= 1) {
	                        $bottom_fillbar = ceil($this->y - ($this->generalConfig['font_size_subtitles'] / 2)) - $fillbar_padding[1] + 1;
	                        $top_fillbar = ceil($this->y + $this->generalConfig['font_size_subtitles'] + 2) + $fillbar_padding[0] - 3;
							
							if(isset($line_widths[0]) && $line_widths[0] > 0){
		                       if(isset($line_widths[1]) && $line_widths[1] > 0)
								   $this->getPage()->setLineWidth($line_widths[1]-0.5); // set to the bottom bar, if set
							   elseif(isset($line_widths[0]) && $line_widths[0] > 0)
								   $this->getPage()->setLineWidth($line_widths[0]-0.5); // set to the top bar, if bottom not set
							   
		                        $this->getPage()->drawLine($pageConfig['padded_left'], $top_fillbar, ($pageConfig['padded_right']), $top_fillbar);
								$this->getPage()->drawLine($pageConfig['padded_left'], $bottom_fillbar, ($pageConfig['padded_right']), $bottom_fillbar);
		                    }
		                }
		                break;
		            case 2 :
		                break;
		        }
		    }
		}	
	}

	/*
		Draws the product list column titles
	*/
	private function drawProductTitles(){
        $order = $this->getOrder();
		$helper = Mage::helper('pickpack');
        $storeId = $order->getStoreId();
        $wonder = $this->getWonder();
        $page = $this->getPage();
		$pageConfig = $this->getPageConfig();
		$generalConfig = $this->getGeneralConfig();

		$serial_code_yn = $this->_getConfig('serial_code_yn', 0, false, $wonder, $storeId);
		$serial_code_title = $this->_getConfig('serial_code_title', 'serial_code', false, $wonder, $storeId);
		$serial_codeX = $this->_getConfig('serial_code_pos', 350, false, $wonder, $storeId);
		
        if ($this->packingsheetConfig['product_sku_barcode_yn'] != 0) {
            $columns_xpos_array['sku_barcodeX'] = $this->packingsheetConfig['product_sku_barcode_x_pos'];
            if ($this->packingsheetConfig['product_sku_barcode_2_yn'] != 0) 
				$columns_xpos_array['sku_barcodeX_2'] = $this->packingsheetConfig['product_sku_barcode_2_x_pos'];
        }

        if ($this->packingsheetConfig['product_stock_qty_yn'] == 1) 
			$columns_xpos_array['stockqtyX'] = $this->packingsheetConfig['product_stock_qty_x_pos'];

		$product_options_title = trim($this->_getConfig('product_options_title', '', false, $wonder, $storeId));
		$optionsX = $this->_getConfig('pricesN_optionsX', 0, false, $wonder, $storeId);
        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);

        if ($shelving_real_yn == 1) {
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
            $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
        } else 
			$combine_custom_attribute_yn = 0;

        $shelving_real_title = trim($this->_getConfig('shelving_real_title', '', false, $wonder, $storeId));
        $shelving_real_title = str_ireplace(array('blank', "'"), '', $shelving_real_title);
		
        $shelfX = $this->_getConfig('pricesN_shelfX', 10, false, $wonder, $storeId);
        $shelf2X = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
        $shelf3X = $this->_getConfig('pricesN_shelf3X', 10, false, $wonder, $storeId);
        $shelf4X = $this->_getConfig('shelving_3_Xpos', 10, false, $wonder, $storeId);
        $optionsX = $this->_getConfig('pricesN_optionsX', 0, false, $wonder, $storeId);

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '') 
			$shelving_real_yn = 0;
        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '') 
				$shelving_yn = 0;
            if ($shelving_yn == 0)
				$shelving_attribute = null;
        } else 
			$shelving_yn = 0;
        if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
            $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
            $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $storeId));
            if ($shelving_2_attribute == '')
				$shelving_2_yn = 0;
        } else
			$shelving_2_yn = 0;
        if (($shelving_real_yn == 1) && ($shelving_yn == 1) && ($shelving_2_yn == 1)) {
            $shelving_3_yn = $this->_getConfig('shelving_3_yn', 0, false, $wonder, $storeId);
            $shelving_3_attribute = trim($this->_getConfig('shelving_3', '', false, $wonder, $storeId));
            if ($shelving_3_attribute == '')
				$shelving_3_yn = 0;
        } else
			$shelving_3_yn = 0;
        if ($shelving_real_yn == 1) 
			$columns_xpos_array['shelfX'] = $this->_getConfig('pricesN_shelfX', 10, false, $wonder, $storeId);
        if ($shelving_yn == 1) 
			$columns_xpos_array['shelf2X'] = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
        if ($shelving_2_yn == 1) 
			$columns_xpos_array['shelf3X'] = $this->_getConfig('pricesN_shelf3X', 10, false, $wonder, $storeId);
        if ($shelving_3_yn == 1) 
			$columns_xpos_array['shelf4X'] = $this->_getConfig('shelving_3_Xpos', 10, false, $wonder, $storeId);

        //combine custom attribute
        if ($shelving_real_yn == 1) {
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
            $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
        } else 
			$combine_custom_attribute_yn = 0;

        if ($this->packingsheetConfig['tickbox_yn'] == 1)
			$columns_xpos_array['tickboxX'] = $this->packingsheetConfig['tickboxX'];
        if ($this->packingsheetConfig['tickbox_2_yn'] == 1)
			$columns_xpos_array['tickbox2X'] = $this->packingsheetConfig['tickbox2X'];
		
        $shelving_title = $this->_getConfig('shelving_title', '', false, $wonder, $storeId);
        $shelving_title = trim(str_ireplace(array('blank', "'"), '', $shelving_title));
		
        if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
            $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
            $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $storeId));
            if ($shelving_2_attribute == '') 
				$shelving_2_yn = 0;
            $shelving_2_title = $this->_getConfig('shelving_2_title', '', false, $wonder, $storeId);
            $shelving_2_title = trim(str_ireplace(array('blank', "'"), '', $shelving_2_title));
        } else 
			$shelving_2_yn = 0;

        if (($shelving_real_yn == 1) && ($shelving_yn == 1) && ($shelving_2_yn == 1)) {
            $shelving_3_yn = $this->_getConfig('shelving_3_yn', 0, false, $wonder, $storeId);
            $shelving_3_attribute = trim($this->_getConfig('shelving_3', '', false, $wonder, $storeId));
            if ($shelving_3_attribute == '') 
				$shelving_3_yn = 0;
            $shelving_3_title = $this->_getConfig('shelving_3_title', '', false, $wonder, $storeId);
            $shelving_3_title = trim(str_ireplace(array('blank', "'"), '', $shelving_3_title));
        } else 
			$shelving_3_yn = 0;

        if ($shelving_real_yn == 1) {
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
            $combine_custom_attribute_title = $this->_getConfig('combine_custom_attribute_title', '', false, $wonder, $storeId);
            $combine_custom_attribute_title = trim(str_ireplace(array('blank', "'"), '', $combine_custom_attribute_title));
            $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
        } else 
			$combine_custom_attribute_yn = 0;

        if($this->packingsheetConfig['show_allowance_yn'] == 1)
			$columns_xpos_array['allowance'] = $this->_getConfig('show_allowance_xpos', '500', false, $wonder, $storeId);
		
        $first_item_title_shift_sku = 0;
        $first_item_title_shift_items = 0;
        if (($this->packingsheetConfig['qty_x_pos'] > 50) && ($this->packingsheetConfig['tickbox_yn'] == 1)) {
            if ($this->packingsheetConfig['product_name_x_pos'] < $this->packingsheetConfig['product_sku_x_pos'])
				$first_item_title_shift_items = $this->getFirstItemTitleShift();
            elseif ($this->packingsheetConfig['product_sku_x_pos'] < $this->packingsheetConfig['product_name_x_pos']) $first_item_title_shift_sku = $this->getFirstItemTitleShift();
        }
		
        $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
		
        if ($this->packingsheetConfig['product_images_yn'] == 1)
            $this->_drawText($helper->__($this->packingsheetConfig['product_images_title']), $this->packingsheetConfig['product_images_x_pos'], $this->y);

        if ($serial_code_yn == 1)
            $this->_drawText($helper->__($serial_code_title), ($serial_codeX + $first_item_title_shift_items), $this->y);

        $this->_drawText($helper->__($this->packingsheetConfig['qty_title']), $this->packingsheetConfig['qty_x_pos'], $this->y);

        if ($this->packingsheetConfig['show_product_name'] == 1)
            $this->_drawText($helper->__($this->packingsheetConfig['product_name_title']), ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $first_item_title_shift_items), $this->y);

        if($this->isShowGiftWrap())
            $this->_drawText($helper->__($this->packingsheetConfig['show_gift_wrap_title']), ($this->packingsheetConfig['show_gift_wrap_xpos'] + $first_item_title_shift_items), $this->y);

        if ($this->packingsheetConfig['product_sku_yn'] == 1) 
			$this->_drawText($helper->__($this->packingsheetConfig['product_sku_title']), ($this->packingsheetConfig['product_sku_x_pos'] + $first_item_title_shift_sku), $this->y);

        if ($this->packingsheetConfig['product_sku_barcode_yn'] != 0) 
			$this->_drawText($helper->__($this->packingsheetConfig['product_sku_barcode_title']), ($this->packingsheetConfig['product_sku_barcode_x_pos'] - 1), $this->y);

        if ($this->packingsheetConfig['product_sku_barcode_2_yn'] != 0) 
			$this->_drawText($helper->__($this->packingsheetConfig['product_sku_barcode_2_title']), ($this->packingsheetConfig['product_sku_barcode_2_x_pos'] - 1), $this->y);

        if ($this->packingsheetConfig['product_stock_qty_yn'] == 1)
            $this->_drawText($helper->__($this->packingsheetConfig['product_stock_qty_title']), ($this->packingsheetConfig['product_stock_qty_x_pos']), $this->y);

        if ($this->packingsheetConfig['product_options_yn'] == 'yescol')
            $this->_drawText($helper->__($product_options_title), ($optionsX), $this->y);

        if ($shelving_real_yn == 1 && $combine_custom_attribute_yn == 0)
            $this->_drawText($helper->__($shelving_real_title), ($shelfX), $this->y);

        if ($shelving_yn == 1 && $combine_custom_attribute_yn == 0)
            $this->_drawText($helper->__($shelving_title), ($shelf2X), $this->y);

        if ($shelving_2_yn == 1 && $combine_custom_attribute_yn == 0)
            $this->_drawText($helper->__($shelving_2_title), ($shelf3X), $this->y);

        if ($shelving_3_yn == 1 && $combine_custom_attribute_yn == 0)
            $this->_drawText($helper->__($shelving_3_title), ($shelf4X), $this->y);

        if ($combine_custom_attribute_yn == 1)
            $this->_drawText($helper->__($combine_custom_attribute_title), ($combine_custom_attribute_Xpos), $this->y);

        /************ START TO PRINT PRICE TITLE ************/
        $prices_yn = $this->isShowPrices();
        if ($prices_yn != '0') {
            if ($this->packingsheetConfig['product_line_prices_yn'])
                $this->_drawText(Mage::helper('pickpack')->__($this->packingsheetConfig['product_line_prices_title']), $this->packingsheetConfig['product_line_prices_title_xpos'], $this->y);
            if ($this->packingsheetConfig['product_line_discount_yn'])
                $this->_drawText(Mage::helper('pickpack')->__($this->packingsheetConfig['product_line_discount_title']), $this->packingsheetConfig['product_line_discount_title_xpos'], $this->y);
            if ($this->packingsheetConfig['product_line_tax_yn'])
                $this->_drawText(Mage::helper('pickpack')->__($this->packingsheetConfig['product_line_tax_title']), $this->packingsheetConfig['product_line_tax_title_xpos'], $this->y);
            if ($this->packingsheetConfig['product_line_total_yn'])
                $this->_drawText(Mage::helper('pickpack')->__($this->packingsheetConfig['product_line_total_title']), $this->packingsheetConfig['product_line_total_title_xpos'], $this->y);
        }
        /************ END TO PRINT PRICE TITLE ************/

        if($this->packingsheetConfig['show_allowance_yn'] == 1)
            $this->_drawText($helper->__($this->packingsheetConfig['show_allowance_title']), $this->packingsheetConfig['show_allowance_xpos'], $this->y);

        $this->y -= 34;
	}
		
    protected function preparePositionArrayX() {
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $wonder = $this->getWonder();

        $optionsX = $this->_getConfig('pricesN_optionsX', 0, false, $wonder, $storeId);

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '') 
			$shelving_real_yn = 0;
        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '') 
				$shelving_yn = 0;
            if ($shelving_yn == 0)
				$shelving_attribute = null;
        } else 
			$shelving_yn = 0;
        if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
            $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
            $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $storeId));
            if ($shelving_2_attribute == '')
				$shelving_2_yn = 0;
        } else
			$shelving_2_yn = 0;
        if (($shelving_real_yn == 1) && ($shelving_yn == 1) && ($shelving_2_yn == 1)) {
            $shelving_3_yn = $this->_getConfig('shelving_3_yn', 0, false, $wonder, $storeId);
            $shelving_3_attribute = trim($this->_getConfig('shelving_3', '', false, $wonder, $storeId));
            if ($shelving_3_attribute == '')
				$shelving_3_yn = 0;
        } else
			$shelving_3_yn = 0;
        if ($shelving_real_yn == 1) 
			$columns_xpos_array['shelfX'] = $this->_getConfig('pricesN_shelfX', 10, false, $wonder, $storeId);
        if ($shelving_yn == 1) 
			$columns_xpos_array['shelf2X'] = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
        if ($shelving_2_yn == 1) 
			$columns_xpos_array['shelf3X'] = $this->_getConfig('pricesN_shelf3X', 10, false, $wonder, $storeId);
        if ($shelving_3_yn == 1) 
			$columns_xpos_array['shelf4X'] = $this->_getConfig('shelving_3_Xpos', 10, false, $wonder, $storeId);

        //combine custom attribute
        if ($shelving_real_yn == 1) {
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
            $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
        } else 
			$combine_custom_attribute_yn = 0;

        if ($this->packingsheetConfig['tickbox_yn'] == 1)
			$columns_xpos_array['tickboxX'] = $this->packingsheetConfig['tickboxX'];
        if ($this->packingsheetConfig['tickbox_2_yn'] == 1)
			$columns_xpos_array['tickbox2X'] = $this->packingsheetConfig['tickbox2X'];

        if (($this->packingsheetConfig['product_sku_yn'] == 'configurable') || ($this->packingsheetConfig['product_sku_yn'] == 'fullsku') || ($this->packingsheetConfig['product_sku_yn'] == '1'))
			$columns_xpos_array['skuX'] = $this->packingsheetConfig['product_sku_x_pos'];

        if ($this->packingsheetConfig['product_sku_barcode_yn'] != 0) {
            $columns_xpos_array['sku_barcodeX'] = $this->packingsheetConfig['product_sku_barcode_x_pos'];
            if ($this->packingsheetConfig['product_sku_barcode_2_yn'] != 0) 
				$columns_xpos_array['sku_barcodeX_2'] = $this->packingsheetConfig['product_sku_barcode_2_x_pos'];
        }

        if ($this->packingsheetConfig['product_stock_qty_yn'] == 1) 
			$columns_xpos_array['stockqtyX'] = $this->packingsheetConfig['product_stock_qty_x_pos'];

        if ($this->packingsheetConfig['show_product_name'] == 1) 
			$columns_xpos_array['productX'] = $this->_getConfig('pricesN_productX', 10, false, $wonder, $storeId);

        $serial_code_yn = $this->_getConfig('serial_code_yn', 0, false, $wonder, $storeId);
        if($serial_code_yn == 1) 
			$columns_xpos_array['serial_codeX'] = $this->_getConfig('serial_code_pos', 350, false, $wonder, $storeId);

        if ($combine_custom_attribute_yn == 1) 
			$columns_xpos_array['combine_custom_attribute_Xpos'] = $combine_custom_attribute_Xpos;

        $columns_xpos_array['optionsX'] = $optionsX;
        $columns_xpos_array['qtyX'] = $this->packingsheetConfig['qty_x_pos'];

        if ($this->packingsheetConfig['product_images_yn'] == 1)
            $columns_xpos_array['imagesX'] = $this->packingsheetConfig['product_images_x_pos'];

        $prices_yn = $this->isShowPrices();
        if ($prices_yn == 1) {
            if ($this->packingsheetConfig['product_line_prices_yn'] != 0)
                $columns_xpos_array['priceX'] = $this->packingsheetConfig['product_line_prices_title_xpos'];
            if ($this->packingsheetConfig['product_line_discount_yn'] != 0)
                $columns_xpos_array['discountX'] = $this->packingsheetConfig['product_line_discount_title_xpos'];
            if ($this->packingsheetConfig['product_line_tax_yn'] != 0)
                $columns_xpos_array['taxX'] = $this->packingsheetConfig['product_line_tax_title_xpos'];
            if ($this->packingsheetConfig['product_line_total_yn'] != 0)
                $columns_xpos_array['totalX'] = $this->packingsheetConfig['product_line_total_title_xpos'];
        }

        if ($this->packingsheetConfig['product_qty_backordered_yn'] == 1)
			$columns_xpos_array['backorderedX'] = $this->packingsheetConfig['product_qty_backordered_x_pos'];

        if($this->packingsheetConfig['show_allowance_yn'] == 1)
			$columns_xpos_array['allowance'] = $this->_getConfig('show_allowance_xpos', '500', false, $wonder, $storeId);

        $supplier_hide_attribute_column = $this->_getConfig('supplier_hide_attribute_column',0, false, $wonder, $storeId);
        if($supplier_hide_attribute_column ==0) {
            if ($this->packingsheetConfig['product_warehouse_yn'] == 1)
				$columns_xpos_array['warehouseX'] = $this->_getConfig('prices_warehouseX', 400, false, $wonder, $storeId);
        }
        asort($columns_xpos_array);

        return $columns_xpos_array;
    }

    public function getCountItems() {
        return count($this->_itemCollection);
    }

    public function getItemCollection() {
        if(is_null($this->_itemCollection))
            $this->_itemCollection = Mage::helper('pickpack/order')->getItemsToProcess($this->getOrder());

        return $this->_itemCollection;
    }

    public function getProductBuildArray() {
        return $this->product_build;
    }

    public function showGridHeader() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $prices_yn = $this->isShowPrices();
        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '') $shelving_real_yn = 0;
        $shelving_real_title = trim($this->_getConfig('shelving_real_title', '', false, $wonder, $storeId));
        $shelving_real_title = str_ireplace(array('blank', "'"), '', $shelving_real_title);
        $supplier_hide_attribute_column = $this->_getConfig('supplier_hide_attribute_column',0, false, $wonder, $storeId);
        $product_options_title = trim($this->_getConfig('product_options_title', '', false, $wonder, $storeId));

        $shelfX = $this->_getConfig('pricesN_shelfX', 10, false, $wonder, $storeId);
        $shelf2X = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
        $shelf3X = $this->_getConfig('pricesN_shelf3X', 10, false, $wonder, $storeId);
        $shelf4X = $this->_getConfig('shelving_3_Xpos', 10, false, $wonder, $storeId);
        $optionsX = $this->_getConfig('pricesN_optionsX', 0, false, $wonder, $storeId);

        $serial_code_yn = $this->_getConfig('serial_code_yn', 0, false, $wonder, $storeId);
        if($serial_code_yn == 1){
            $serial_code_title = $this->_getConfig('serial_code_title', 'serial_code', false, $wonder, $storeId);
            $serial_codeX = $this->_getConfig('serial_code_pos', 350, false, $wonder, $storeId);
        }

        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '') 
				$shelving_yn = 0;
            if ($shelving_yn == 0) 
				$shelving_attribute = null;
            $shelving_title = $this->_getConfig('shelving_title', '', false, $wonder, $storeId);
            $shelving_title = trim(str_ireplace(array('blank', "'"), '', $shelving_title));
        } else 
			$shelving_yn = 0;
        // attr #3
        if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
            $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
            $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $storeId));
            if ($shelving_2_attribute == '') 
				$shelving_2_yn = 0;
            $shelving_2_title = $this->_getConfig('shelving_2_title', '', false, $wonder, $storeId);
            $shelving_2_title = trim(str_ireplace(array('blank', "'"), '', $shelving_2_title));
        } else 
			$shelving_2_yn = 0;

        if (($shelving_real_yn == 1) && ($shelving_yn == 1) && ($shelving_2_yn == 1)) {
            $shelving_3_yn = $this->_getConfig('shelving_3_yn', 0, false, $wonder, $storeId);
            $shelving_3_attribute = trim($this->_getConfig('shelving_3', '', false, $wonder, $storeId));
            if ($shelving_3_attribute == '') 
				$shelving_3_yn = 0;
            $shelving_3_title = $this->_getConfig('shelving_3_title', '', false, $wonder, $storeId);
            $shelving_3_title = trim(str_ireplace(array('blank', "'"), '', $shelving_3_title));
        } else 
			$shelving_3_yn = 0;

        if ($shelving_real_yn == 1) {
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
            $combine_custom_attribute_title = $this->_getConfig('combine_custom_attribute_title', '', false, $wonder, $storeId);
            $combine_custom_attribute_title = trim(str_ireplace(array('blank', "'"), '', $combine_custom_attribute_title));
            $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
        } else 
			$combine_custom_attribute_yn = 0;

        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['background_color_subtitles']);

		// work out where to put the product titlebar
		$this->subheader_start -= ($pageConfig['vertical_spacing'] );
		$this->subheader_start += ($generalConfig['font_size_body']*0.75);
        
		$address_pad = explode(",", $this->_getConfig('address_pad', '0,0,0', false, $wonder, $storeId));		
		if(isset($address_pad[1]))
			$this->subheader_start -= ($address_pad[1]);
		if(isset($address_pad[0]))
			$this->subheader_start -= ($address_pad[0]);
		
		if($generalConfig['fill_bars_subtitles'] == 1)
			$this->subheader_start -= ($pageConfig['vertical_spacing']);
		
		// shift down product titles, if page title is in our spot
        $pickpack_headerbar_yn = trim($this->_getConfig('pickpack_headerbar_yn', '1', false, $wonder, $storeId));
		if($pickpack_headerbar_yn == 2)
			$this->subheader_start -= ( ($generalConfig['font_size_subtitles']*2) + 2 + $generalConfig['font_size_subtitles'] + 2 + $generalConfig['titlebar_padding_top'] + $generalConfig['titlebar_padding_bot']);
		
		$this->y = $this->subheader_start;
		
		if (strtoupper($generalConfig['background_color_subtitles']) != '#FFFFFF') {
		    $this->getPage()->setFillColor($background_color_subtitles_zend);
		    $this->getPage()->setLineColor($background_color_subtitles_zend);
		    $this->getPage()->setLineWidth(0.5);
		    switch ($generalConfig['fill_bars_subtitles']) {
		        case 0: // Yes(default)
		            $this->getPage()->drawRectangle($pageConfig['padded_left'], ($this->y - ($generalConfig['font_size_subtitles'] / 2)), $pageConfig['padded_right'], ($this->y + $generalConfig['font_size_subtitles'] + 2));
		            break;
		        case 1: // Partially: lines top & bottom
		            $this->getPage()->drawLine($pageConfig['padded_left'], ($this->y - ($generalConfig['font_size_subtitles'] / 2) - $generalConfig['titlebar_padding_top'] - $generalConfig['titlebar_padding_bot']), ($pageConfig['padded_right']), ($this->y - ($generalConfig['font_size_subtitles'] / 2) - $generalConfig['titlebar_padding_top'] - $generalConfig['titlebar_padding_bot']));
		            $this->getPage()->drawLine($pageConfig['padded_left'], ($this->y + $generalConfig['font_size_subtitles'] + 2) , ($pageConfig['padded_right']), ($this->y + $generalConfig['font_size_subtitles'] + 2));
		            $this->y = $this->y - $generalConfig['titlebar_padding_top'];
		            break;
		        case 2:
				default:
					break;
		    }
    	}

        $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);


		
        if ($this->isShipment)
			$this->productXInc = 25;
        else
            $this->productXInc = 0;

        if (($this->packingsheetConfig['qty_x_pos'] > 50) && ($this->packingsheetConfig['tickbox_yn'] == 1)) {
            if ($this->packingsheetConfig['product_name_x_pos'] < $this->packingsheetConfig['product_sku_x_pos'])
				$this->first_item_title_shift_items = $this->getFirstItemTitleShift();
            elseif ($this->packingsheetConfig['product_sku_x_pos'] < $this->packingsheetConfig['product_name_x_pos'])
				$this->first_item_title_shift_sku = $this->getFirstItemTitleShift();
        }

        if ($this->packingsheetConfig['product_images_yn'] == 1)
			$this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['product_images_title']), $this->packingsheetConfig['product_images_x_pos'], $this->y);

        $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['qty_title']), $this->packingsheetConfig['qty_x_pos'], $this->y);
        $this->minDistanceSku = $pageConfig['padded_right'] - $this->packingsheetConfig['product_sku_x_pos'];
        if ($this->packingsheetConfig['show_product_name'] == 1) {
            $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['product_name_title']), ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->first_item_title_shift_items), $this->y);
            $distance_name = $this->packingsheetConfig['product_name_x_pos'] + $this->productXInc - $this->packingsheetConfig['product_sku_x_pos'];
            if ($distance_name > 0 && $distance_name < $this->minDistanceSku)
                $this->minDistanceSku = $distance_name;
        }

        if($this->isShowGiftWrap())
			$this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['show_gift_wrap_title']), ($this->packingsheetConfig['show_gift_wrap_xpos'] + $this->first_item_title_shift_items), $this->y);

        if ($serial_code_yn == 1)
            $this->_drawText(Mage::helper('sales')->__($serial_code_title), ($serial_codeX + $this->first_item_title_shift_items), $this->y);

         if (($this->packingsheetConfig['product_sku_yn'] == 'configurable') || ($this->packingsheetConfig['product_sku_yn'] == 'fullsku') || ($this->packingsheetConfig['product_sku_yn'] == '1'))
            $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['product_sku_title']), ($this->packingsheetConfig['product_sku_x_pos']), $this->y);

        if ($this->packingsheetConfig['product_sku_barcode_yn'] != 0) {
            $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['product_sku_barcode_title']), ($this->packingsheetConfig['product_sku_barcode_x_pos'] - 1), $this->y);
            $distance_barcode = $this->packingsheetConfig['product_sku_barcode_x_pos'] - $this->packingsheetConfig['product_sku_x_pos'];
            if ($distance_barcode > 0 && $distance_barcode < $this->minDistanceSku)
                $this->minDistanceSku = $distance_barcode;
        }

        if ($this->packingsheetConfig['product_sku_barcode_yn'] != 0 && $this->packingsheetConfig['product_sku_barcode_2_yn'] != 0) {
            $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['product_sku_barcode_2_title']), ($this->packingsheetConfig['product_sku_barcode_2_x_pos'] - 1), $this->y);
            $distance_barcode = $this->packingsheetConfig['product_sku_barcode_2_x_pos'] - $this->packingsheetConfig['product_sku_x_pos'];
            if ($distance_barcode > 0 && $distance_barcode < $this->minDistanceSku)
                $this->minDistanceSku = $distance_barcode;
        }

        if ($this->packingsheetConfig['product_stock_qty_yn'] == 1) {
            $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['product_stock_qty_title']), ($this->packingsheetConfig['product_stock_qty_x_pos']), $this->y);
            $distance_stock = $this->packingsheetConfig['product_stock_qty_x_pos'] - $this->packingsheetConfig['product_sku_x_pos'];
            if ($distance_stock > 0 && $distance_stock < $this->minDistanceSku)
                $this->minDistanceSku = $distance_stock;
        }

        if ($this->packingsheetConfig['product_qty_backordered_yn'] == 1) {
            $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['product_qty_backordered_title']), ($this->packingsheetConfig['product_qty_backordered_x_pos']), $this->y);
            $distance_qtybarcode = $this->packingsheetConfig['product_qty_backordered_x_pos'] - $this->packingsheetConfig['product_sku_x_pos'];
            if ($distance_qtybarcode > 0 && $distance_qtybarcode < $this->minDistanceSku)
                $this->minDistanceSku = $distance_qtybarcode;
        }
        if($supplier_hide_attribute_column ==0)
            if ($this->packingsheetConfig['product_warehouse_yn'] == 1) {
                $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['product_warehouse_title']), ($this->packingsheetConfig['prices_warehouseX']), $this->y);
                $distance_warehouse = $this->packingsheetConfig['prices_warehouseX'] - $this->packingsheetConfig['product_sku_x_pos'];
                if ($distance_warehouse > 0 && $distance_warehouse < $this->minDistanceSku)
                    $this->minDistanceSku = $distance_warehouse;
            }

        if ($this->packingsheetConfig['product_options_yn'] == 'yescol') {
            $this->_drawText(Mage::helper('sales')->__($product_options_title), ($optionsX), $this->y);
            $distance_option = $optionsX - $this->packingsheetConfig['product_sku_x_pos'];
            if ($distance_option > 0 && $distance_option < $this->minDistanceSku)
                $this->minDistanceSku = $distance_option;
        }

        if ($shelving_real_yn == 1 && $combine_custom_attribute_yn == 0) {
            $this->_drawText(Mage::helper('sales')->__($shelving_real_title), ($shelfX), $this->y);
            $distance_shel1 = $shelfX - $this->packingsheetConfig['product_sku_x_pos'];
            if ($distance_shel1 > 0 && $distance_shel1 < $this->minDistanceSku)
                $this->minDistanceSku = $distance_shel1;
        }

        if ($shelving_yn == 1 && $combine_custom_attribute_yn == 0)
            $this->_drawText(Mage::helper('sales')->__($shelving_title), ($shelf2X), $this->y);

        if ($shelving_2_yn == 1 && $combine_custom_attribute_yn == 0)
            $this->_drawText(Mage::helper('sales')->__($shelving_2_title), ($shelf3X), $this->y);

        if ($shelving_3_yn == 1 && $combine_custom_attribute_yn == 0)
            $this->_drawText(Mage::helper('sales')->__($shelving_3_title), ($shelf4X), $this->y);

        if ($combine_custom_attribute_yn == 1)
            $this->_drawText(Mage::helper('sales')->__($combine_custom_attribute_title), ($combine_custom_attribute_Xpos), $this->y);

        /************ START TO PRINT PRICE TITLE ************/
        if ($prices_yn != '0') {
            if ($this->packingsheetConfig['product_line_prices_yn'])
                $this->_drawText(Mage::helper('pickpack')->__($this->packingsheetConfig['product_line_prices_title']), $this->packingsheetConfig['product_line_prices_title_xpos'], $this->y);
            if ($this->packingsheetConfig['product_line_discount_yn'])
                $this->_drawText(Mage::helper('pickpack')->__($this->packingsheetConfig['product_line_discount_title']), $this->packingsheetConfig['product_line_discount_title_xpos'], $this->y);
            if ($this->packingsheetConfig['product_line_tax_yn'])
                $this->_drawText(Mage::helper('pickpack')->__($this->packingsheetConfig['product_line_tax_title']), $this->packingsheetConfig['product_line_tax_title_xpos'], $this->y);
            if ($this->packingsheetConfig['product_line_total_yn'])
                $this->_drawText(Mage::helper('pickpack')->__($this->packingsheetConfig['product_line_total_title']), $this->packingsheetConfig['product_line_total_title_xpos'], $this->y);
        }
        /************ END TO PRINT PRICE TITLE ************/

        if($this->packingsheetConfig['show_allowance_yn'] == 1)
            $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['show_allowance_title']), $this->packingsheetConfig['show_allowance_xpos'], $this->y);

        $this->y = $this->y - $generalConfig['titlebar_padding_bot'];

        $this->y -= ($generalConfig['font_size_subtitles'] / 2 + $pageConfig['vertical_spacing'] + $generalConfig['font_size_body'] - 1);
        //if (strtoupper($generalConfig['background_color_subtitles']) == '#FFFFFF') $this->y += 10;
    }

    public function sortProducts() {
        if ($this->generalConfig['sort_packing_yn']){
            //first sort
            $sort_by = $this->generalConfig['sort_packing'];
            if ($this->generalConfig['sort_packing_order'] == "ascending")
                $sort_by_order = true;
            else
                $sort_by_order = false;

            $sort_packing_attribute = null;
            if ($sort_by == 'attribute') {
                $sort_by_attribute = trim($this->generalConfig['sort_packing_attribute']);
                if ($sort_by_attribute != '')
                    $sort_by = $sort_by_attribute;
                else
                    $sort_by = 'sku';
            }
            //second sort
            $sort_second_by = $this->generalConfig['sort_packing_secondary'];
            if ($this->generalConfig['sort_packing_secondary_order'] == "ascending")
                $sort_second_by_order = true;
            else
                $sort_second_by_order = false;

            $sort_second_packing_attribute = null;
            if ($sort_second_by == 'attribute') {
                $sort_second_by_attribute = trim($this->generalConfig['sort_packing_secondary_attribute']);
                if ($sort_second_by_attribute != '')
                    $sort_second_by = $sort_second_by_attribute;
                else
                    $sort_second_by = 'sku';
            }

            //apply sort
            if ($sort_second_by != $sort_by)
                sksort($this->product_build, $sort_by, $sort_by_order);
            else
                Mage::helper('pickpack')->sortMultiDimensional($this->product_build, $sort_by, $sort_second_by, $sort_by_order, $sort_second_by_order);
        }
    }

    public function sortChildBundleProductsByPriority(&$array_bundle_children,$parent_product) {

        $sort_child_bundle_order = $this->generalConfig['sort_child_bundle_order'];
        if($sort_child_bundle_order == "ascending")
            $sortorder = SORT_ASC;
        else
            $sortorder = SORT_DESC;

        $array_child = array();
        $option_position_array = array();
        $item_position_array = array();

        $optionCollection = $parent_product->getTypeInstance(true)->getOptionsCollection($parent_product);
        foreach ($optionCollection as $prdOptions){
            $position_option = $prdOptions->getPosition();
            $option_id = $prdOptions->getData('option_id');

            $resource = Mage::getSingleton('core/resource');
            $tableName = $resource->getTableName('catalog_product_bundle_selection');
            $readConnection = $resource->getConnection('core_read');
            $query = 'SELECT tb.option_id,tb.product_id,tb.position FROM ' . $tableName .' AS tb '
                . 'WHERE tb.option_id = '.$option_id.' ORDER BY tb.position ASC';
            $results = $readConnection->fetchAll($query);

            foreach ($results as $row){
                $array_child[] = array(
                    "product_id" => $row['product_id'],
                    "option_position" => $position_option,
                    "item_position" => $row['position']
                );
                $option_position_array[] = $position_option;
                $item_position_array[] = $row['position'];
            }
        }

        array_multisort($option_position_array, $sortorder, $item_position_array, $sortorder, $array_child);

        $new_bundle_childen_array = array();
        foreach($array_bundle_children as $item){
            foreach ($array_child as $key => $value){
                if ($value['product_id'] == $item->getData('product_id')){
                    $new_bundle_childen_array[$key] = $item;
                }
            }
        }

        ksort($new_bundle_childen_array);
        $array_bundle_children = $new_bundle_childen_array;
    }

    public function sortChildBundleProducts(&$array_bundle_children,$bundle_product) {
        $sort_child_bundle_yn = $this->generalConfig['sort_child_bundle_yn'];
        $sort_packing_yn = $this->generalConfig['sort_packing_yn'];
        if (($sort_child_bundle_yn == 1 && $sort_packing_yn == 1)|| ($sort_child_bundle_yn == 2 && $sort_packing_yn == 1)){
            if($sort_child_bundle_yn == 1){
                $this->sortChildBundleProductsByPriority($array_bundle_children,$bundle_product);
                return;
            }
            else{
                $sort_by = $this->generalConfig['sort_packing'];
                $sort_child_bundle_order = $this->generalConfig['sort_packing_order'];
                $sort_by_attribute = trim($this->generalConfig['sort_packing_attribute']);

                $sort_second_by = $this->generalConfig['sort_packing_secondary'];
                $sort_child_bundle_secondary_order = $this->generalConfig['sort_packing_secondary_order'];
                $sort_second_by_attribute = trim($this->generalConfig['sort_packing_secondary_attribute']);
            }
            //first sort
            if ($sort_child_bundle_order == "ascending")
                $sort_by_order = true;
            else
                $sort_by_order = false;

            $sort_packing_attribute = null;
            if ($sort_by == 'attribute') {
                if ($sort_by_attribute != '')
                    $sort_by = $sort_by_attribute;
                else
                    $sort_by = 'sku';
            }
            //second sort
            if ($sort_child_bundle_secondary_order == "ascending")
                $sort_second_by_order = true;
            else
                $sort_second_by_order = false;

            $sort_second_packing_attribute = null;
            if ($sort_second_by == 'attribute') {
                if ($sort_second_by_attribute != '')
                    $sort_second_by = $sort_second_by_attribute;
                else
                    $sort_second_by = 'sku';
            }

            //apply sort
            if ($sort_second_by != $sort_by)
                sksort($array_bundle_children, $sort_by, $sort_by_order);
            else
                Mage::helper('pickpack')->sortMultiDimensional($array_bundle_children, $sort_by, $sort_second_by, $sort_by_order, $sort_second_by_order);
        }
    }

    public function prepareProducts($from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $order_items_arr) {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $supplier = $this->getSupplier();
        $wonder = $this->getWonder();
        $generalConfig = $this->getGeneralConfig();

        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

        $supplier_attribute_default = 'supplier';
        $supplier_options_default = 'filter';

        if($wonder =='wonder')
            $supplier_key = 'pack';
        else
            $supplier_key = 'invoice';

        $split_supplier_yn_temp = $this->_getConfig('pickpack_split_supplier_yn', 0, false, 'general', $storeId);
        $split_supplier_options_temp = $this->_getConfig('pickpack_split_supplier_options', 'no', false, 'general', $storeId);
        $split_supplier_options = explode(',',$split_supplier_options_temp);

        $split_supplier_yn = 'no';
        if ($split_supplier_yn_temp == 1) {
            if(in_array($supplier_key, $split_supplier_options))
                $split_supplier_yn = 'pickpack';
        }

        if ($split_supplier_yn != 'no')
            $supplier_options = $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false, 'general', $storeId);
        else
            $supplier_options = '';

        if(Mage::helper('pickpack')->isMageEnterprise())
            $show_gift_wrap_top_right = $this->packingsheetConfig['show_gift_wrap_top_right'];

        $count = 1;
        foreach ($this->getItemCollection() as $item) {
            //this code will filter items PDF by status invoiced or shiped
            $item_invoiced = $item->getData('qty_invoiced') - 0;
            $item_shiped = $item->getData('qty_shipped') - 0;
            if (($this->packingsheetConfig['filter_items_by_status'] == 1) && ($item_invoiced < 1))
                continue;
            elseif (($this->packingsheetConfig['filter_items_by_status'] == 2) && ($item_shiped < 1))
                continue;

            $product_sku = $this->getProductSku($item);
            $product = Mage::helper('pickpack/product')->getProductFromItem($item);
            $this->sku_ProductId[$product->getSku()] = $product->getId();

            if (!isset($supplier)) 
				$supplier = '~Not Set~';
            if (!isset($this->sku_supplier_item_action[$supplier][$product_sku])) {
                if ($supplier_options == 'filter') 
					$this->sku_supplier_item_action[$supplier][$product_sku] = 'hide';
                elseif ($supplier_options == 'grey') 
					$this->sku_supplier_item_action[$supplier][$product_sku] = 'keepGrey';
                if ($split_supplier_yn == 'no') 
					$this->sku_supplier_item_action[$supplier][$product_sku] = 'keep';
            }

            if (isset($this->sku_supplier_item_action[$supplier]) && isset($this->sku_supplier_item_action[$supplier][$product_sku]) && $this->sku_supplier_item_action[$supplier][$product_sku] != 'hide') {
                $sku = $product_sku . '-' . $count;

                $this->product_build[$sku] = $this->prepareItem($item, $from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $order_items_arr);

                $this->itemQtyArray[$item->getData('product_id')] = $this->product_build[$sku]['qty_string'];
                $childrenItems = $item->getChildrenItems();
                foreach($childrenItems as $child) {
                    $this->itemQtyArray[$child->getData('product_id')] = $child->getData('qty_ordered');
                }

                $this->gift_message_array['items'][$sku] = $this->product_build[$sku]['gift-message-array'];

                if($this->isShowGiftWrap()){
                    $show_item_gift = false;
                    if($item->getData('gw_id')){
                        $show_item_gift = $item->getData('gw_id');
                        if(isset($show_gift_wrap_top_right))
                            $this->show_top_right_gift_icon = true;
                    }
                    $this->product_build[$sku]['show_item_gift'] = $show_item_gift;
                }

                if (Mage::helper('core')->isModuleEnabled('Magik_Magikfees') === TRUE) {
                    $magikFeesElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_module_magik_magikfees', array($this, $page, $order));
                    $this->subtotal_addon['magikfee'] += $magikFeesElement->getSubtotalAddon($item);
                }
            }

            $this->y -= 15;
            $count++;
        } // end items
    }

    public function showItemsGrid($from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $order_items_arr) {
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $order->getStoreId();
        $supplier = $this->getSupplier();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $this->showGridHeader();
        $items_y_start = $this->y;
        $this->prepareProducts($from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $order_items_arr);
        $this->y = $items_y_start;
        $this->sortProducts();
        $this->showProducts($from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $order_items_arr);

        /* split bundle options */
        if(count($this->childArray))
            $this->splitBundleOptions($from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $storeId);
		
        /* split bundle option */
        $this->y -= 10;

        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

        if ($this->print_item_count == 0) {
            if (($this->packingsheetConfig['show_qty_options'] == 4))
                $this->_drawText('There are no invoiced items for this order', 50, $this->y);
            else if ($this->packingsheetConfig['show_qty_options'] == 3)
                $this->_drawText('There are no unshipped items for this order', 50, $this->y);
        }
    }

    public function showProducts($from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $order_items_arr) {
        $storeId = $this->getStoreId();
        $generalConfig = $this->getGeneralConfig();

        if ($generalConfig['background_color_subtitles'] == '#FFFFFF') 
			$this->y += $generalConfig['font_size_body'];
        foreach ($this->product_build as $item) {
            $this->showItem($item, $from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $order_items_arr);
            $this->count_item++;
        }
    }

    public function getSkuArr() {
        if(is_null($this->_sku_array)) {
            $this->_sku_array = array();
            foreach($this->_itemCollection as $item){
                $this->_sku_array[] = $item->getSku();
            }
        }
        return $this->_sku_array;
    }

    public function prepareItem($item, $from_shipment = 'order', $invoice_or_pack = 'pack', $order_invoice_id = '', $shipment_ids = '', $order_items_arr = array()) {
        $helper = Mage::helper('pickpack');
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $isSplitSupplier = $this->isSplitSupplier();
        $supplier = $this->_supplier;
        $generalConfig = $this->getGeneralConfig();
        $wonder = $this->getWonder();

        $shelving_yn = $this->_getConfig('shelving_yn', 0, false,  $wonder, $storeId);
        $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
        $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
        $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $this->getWonder(), $storeId));
        $shelving_3_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
        $shelving_3_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $storeId));
        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        $serial_code_yn = $this->_getConfig('serial_code_yn', 0, false, $wonder, $storeId);

        $sort_packing = $this->_getConfig('sort_packing', 'sku', false, 'general', $storeId);
        $sort_packing_attribute = null;
        if ($sort_packing == 'attribute') {
            $sort_packing_attribute = trim($this->_getConfig('sort_packing_attribute', '', false, 'general', $storeId));
            if ($sort_packing_attribute != '')
				$sort_packing = $sort_packing_attribute;
            else 
				$sort_packing = 'sku';
        }

        $sort_packing_secondary = $this->_getConfig('sort_packing_secondary', 'sku', false, 'general', $storeId);
        $sort_packing_secondary_attribute = null;
        if ($sort_packing_secondary == 'attribute') {
            $sort_packing_secondary_attribute = trim($this->_getConfig('sort_packing_secondary_attribute', '', false, 'general', $storeId));
            if ($sort_packing_secondary_attribute != '')
				$sort_packing_secondary = $sort_packing_secondary_attribute;
            else 
				$sort_packing_secondary = 'sku';
        }

        if (Mage::helper('pickpack')->isInstalled('Webtex_GiftRegistry')){
            $customOptions = $item->getProductOptions();

            if ($customOptions['info_buyRequest'])
                $info_buyRequest = $customOptions['info_buyRequest'];

            $answer = '';
            if(isset($info_buyRequest['webtex_giftregistry_id']) && $info_buyRequest['webtex_giftregistry_id']) {
                $registry = Mage::helper('webtexgiftregistry')->getRegistryById($info_buyRequest['webtex_giftregistry_id']);
                $_array['firstname'] = $registry->getData('firstname');
                $_array['lastname'] = $registry->getData('lastname');
                $_array['cofirstname'] = $registry->getData('co_firstname');
                $_array['colastname'] = $registry->getData('co_lastname');

                $answer = 'For'. ' ' . $_array['firstname'] . ' ' . $_array['lastname'];

                if($_array['cofirstname'] || $_array['colastname']) {
                    $answer .= ' ' . 'And' . ' ';
                    $answer .= $_array['cofirstname'] . ' ' . $_array['colastname'];
                }

                $answer .= ' ' . 'Gift Registry (#'.$registry->getData('giftregistry_id').')';
            }
        }

        $item_invoiced = $item->getData('qty_invoiced') - 0;
        $item_shiped = $item->getData('qty_shipped') - 0;
        if (($this->packingsheetConfig['filter_items_by_status'] == 1) && ($item_invoiced < 1))
            return;
        elseif (($this->packingsheetConfig['filter_items_by_status'] == 2) && ($item_shiped < 1))
            return;

        $custom_options_output = '';
        $Magikfee = 0;

        $product = Mage::helper('pickpack/product')->getProductFromItem($item);
        $product_id = $product->getId();
		
		if($this->packingsheetConfig['product_sku_yn'] == 'fullsku')
            $sku = $item->getSku();
        else
            $sku = $product->getSku();

        $product_sku = $sku;
		
        $supplierFilterOptions = Mage::helper("pickpack/config_supplier")->getFilterSupplierOptions($storeId);

        if (!isset($supplier)) 
			$supplier = '~Not Set~';

        if (!isset($this->sku_supplier_item_action[$supplier][$product_sku])) {
            if ($supplierFilterOptions == 'filter')
				$this->sku_supplier_item_action[$supplier][$product_sku] = 'hide';
            elseif ($supplierFilterOptions == 'grey')
				$this->sku_supplier_item_action[$supplier][$product_sku] = 'keepGrey';
            if (!$isSplitSupplier)
				$this->sku_supplier_item_action[$supplier][$product_sku] = 'keep';
        }

        $productArray = array();

        //TODO what's for
        $product_sku = $sku;
        $productArray['sku'] = $product_sku;
        $productArray['product'] = $product;
        $productArray['has_message'] = 0;
        $productArray['gift-message-array'] = array();
        $giftWrapInfo = Mage::helper('pickpack/gift')->getGiftWrapInfo($order, $wonder);
        if ((Mage::helper('giftmessage/message')->getIsMessagesAvailable('order_item', $item) && $item->getGiftMessageId()) || isset($answer) && (strlen($answer)>0)) {
            $productArray['has_message'] = 1;
            if(isset($answer)) {
                $productArray['message-from'] = '';
                $productArray['message-to'] = '';
                $productArray['message-content'] = $answer;
                $productArray['gift-message-array']['message-from'] = '';
                $productArray['gift-message-array']['message-to'] = '';
                $productArray['gift-message-array']['message-content'] = $answer;
            }
            else {
                $item_msg_array = $this->getItemGiftMessage($item, $this->_maxCharsMessage);
                $productArray['message-from'] = $item_msg_array[0];
                $productArray['message-to'] = $item_msg_array[1];
                $productArray['message-content'] = $item_msg_array[2];
                $productArray['gift-message-array']['message-from'] = $item_msg_array[0];
                $productArray['gift-message-array']['message-to'] = $item_msg_array[1];
                $productArray['gift-message-array']['message-content'] = $item_msg_array[2];
            }
            unset($gift_msg_array);
            unset($token);
            unset($msg_line_count);
            unset($_giftMessage);
            unset($item_message_from);
            unset($item_message_to);
            unset($item_message);
        }
        if (isset($giftWrapInfo['per_item'][$item->getId()])) {
            $productArray['has_message'] = 1;
            $productArray['message-from'] = '';
            $productArray['message-to'] = '';
            $productArray['message-content'] = $giftWrapInfo['per_item'][$item->getId()]['message'];
            $productArray['gift-message-array']['message-from'] = '';
            $productArray['gift-message-array']['message-to'] = '';
            $productArray['gift-message-array']['message-content'] = $giftWrapInfo['per_item'][$item->getId()]['message'];
        }
        unset($giftWrapInfo);

        if ($this->packingsheetConfig['product_sku_yn'] == 'configurable') {
            // get parent sku
            $_product_temp = Mage::getModel('catalog/product');
            $_product_temp->load($item->getProductId());
            $productArray['sku'] = $_product_temp->getSku();
        }
        $productArray['product_id'] = $product_id;
        $this->sku_ProductId[$product_sku] = $product_id;

        $options = array();
        $options_pre = $item->getProductOptions();
		
        if (Mage::helper('pickpack')->isInstalled('AW_Sarp')) {
            $periodTypeId = @$options_pre['info_buyRequest']['aw_sarp_subscription_type'];
            $periodStartDate = @$options_pre['info_buyRequest']['aw_sarp_subscription_start'];
        }
        
		if (isset($options_pre['info_buyRequest']) && is_array($options_pre['info_buyRequest'])) {
            unset($options_pre['info_buyRequest']['uenc']);
            unset($options_pre['info_buyRequest']['form_key']);
            unset($options_pre['info_buyRequest']['related_product']);
            unset($options_pre['info_buyRequest']['return_url']);
            unset($options_pre['info_buyRequest']['qty']);
            unset($options_pre['info_buyRequest']['_antispam']);
            unset($options_pre['info_buyRequest']['super_attribute']);
            unset($options_pre['info_buyRequest']['cpid']);
            unset($options_pre['info_buyRequest']['callback']);
            unset($options_pre['info_buyRequest']['isAjax']);
            unset($options_pre['info_buyRequest']['item']);
            unset($options_pre['info_buyRequest']['original_qty']);
            unset($options_pre['info_buyRequest']['bundle_option']);
            $options['options'] = array();
          
		    if(isset($options_pre['additional_options']) && is_array($options_pre['additional_options']))
                $options['options'] = $options_pre['additional_options'];
            else {
                if (isset($options_pre['options']) && is_array($options_pre['options'])){
                    foreach ($options_pre['options'] as $value) {
                        $options['options'][count($options['options'])] = $value;
                    }
                }

                if (isset($options_pre['attributes_info']) && is_array($options_pre['attributes_info'])){
                    foreach ($options_pre['attributes_info'] as $value) {
                        $options['options'][count($options['options'])] = $value;
                    }
                }
            }
        } else 
			$options = $options_pre;

        if (isset($options_pre['bundle_options']) && is_array($options_pre['bundle_options']))
            $options['bundle_options'] = $options_pre['bundle_options'];

        if (!(isset($options['options'])) || count($options['options']) == 0) {
            if (isset($options_pre['attributes_info']) && is_array($options_pre['attributes_info']))
                $options['options'] = $options_pre['attributes_info'];
		}
		
		$options_pre['options'] = '';
		$options_pre['attributes_info'] = '';
        unset($options_pre);
		
        if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')){
            $product_real = Mage::getModel("catalog/product")->load($product_id);
            $option_ebay = $this->getEbayOption($order, $product_sku, $product_id);
            if(!isset($options['options']))
                $options['options'] = array();
            $options['options'] = array_merge($options['options'], $option_ebay);
            $options['options'] = array_map('unserialize', array_unique(array_map('serialize', $options['options'])));
			unset($option_ebay);
        }

        if ($this->packingsheetConfig['filter_items_by_status'] == 1)
            $qty = $item_invoiced;
        elseif ($this->packingsheetConfig['filter_items_by_status'] == 2)
            $qty = $item_shiped;
        else
            $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int)$item->getQtyOrdered();

        $sku_print = $productArray['sku'];

        # Get product's category collection object
        //@TODO 2: OPTIMIZE HERE
        $catCollection = $product->getCategoryCollection();

        $categsToLinks = array();
        # Get categories names
        foreach ($catCollection as $cat) {
            if ($cat->getName() != '')
                $categsToLinks[] = $cat->getName();
        }
        $category_label = implode(', ', $categsToLinks);
        $productArray['%category%'] = $category_label;
        unset($category_label);

        $productArray['shelving'] = '';
        if ($shelving_yn == 1 && $shelving_attribute != '' && $product->offsetExists($shelving_attribute)) {
            $attributeName = $shelving_attribute;
            if($item->getData($shelving_attribute) != ''){
                $productArray['shelving'] = $item->getData($shelving_attribute);
                if($productArray['shelving'] == 0)
                    $productArray['shelving'] = 'No';
                if($productArray['shelving'] == 1)
                    $productArray['shelving'] = 'Yes';
            } else {
                if ($attributeName == '%category%')
                    $productArray['shelving'] = $productArray['%category%'];
				else {
                    if ($generalConfig['non_standard_characters']!=0)
                        $productArray['shelving'] = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute,false);
                    else
						$productArray['shelving'] = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute);
                }
            }
        }

        $productArray['shelving2'] = '';
     
	    if ($shelving_2_yn == 1 && $shelving_2_attribute != '' && $product->offsetExists($shelving_2_attribute)) {
            $attributeName = $shelving_2_attribute;
            if($item->getData($shelving_2_attribute) != ''){
                $productArray['shelving2'] = $item->getData($shelving_2_attribute);
                if($productArray['shelving2'] == 0)
                    $productArray['shelving2'] = 'No';
                if($productArray['shelving2'] == 1)
                    $productArray['shelving2'] = 'Yes';
            } else {
                if ($attributeName == '%category%')
                    $productArray['shelving2'] = $productArray['%category%'];
                else{
                    if ($generalConfig['non_standard_characters']!=0)
                        $productArray['shelving2'] = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_2_attribute,false);
                    else 
						$productArray['shelving2'] = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_2_attribute);
                }
            }
        }
        $productArray['shelving3'] = '';
       
	    if ($shelving_3_yn == 1 && $shelving_3_attribute != '' && $product->offsetExists($shelving_3_attribute)) {
            $attributeName = $shelving_3_attribute;
            if($item->getData($shelving_3_attribute) != ''){
                $productArray['shelving3'] = $item->getData($shelving_3_attribute);
                if($productArray['shelving3'] == 0)
                    $productArray['shelving3'] = 'No';
                if($productArray['shelving3'] == 1)
                    $productArray['shelving3'] = 'Yes';
            } else {
                if ($attributeName == '%category%')
                    $productArray['shelving3'] = $productArray['%category%'];
                else {
                    if ($generalConfig['non_standard_characters'] != 0)
                        $productArray['shelving3'] = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_3_attribute,false);
                    else 
						$productArray['shelving3'] = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_3_attribute);
                }
            }
        }
        //TODO for sort first
        if ($sort_packing != 'none' && $sort_packing != '')
            $productArray[$sort_packing] = $this->createArraySort($sort_packing, $productArray, $sku,$product_id);

        //TODO for sort secondary
        if ($sort_packing_secondary != 'none' && $sort_packing_secondary != '')
            $productArray[$sort_packing_secondary] = $this->createArraySort($sort_packing_secondary, $productArray, $sku,$product_id);

        $productArray['shelving_real'] = '';
        if ($shelving_real_yn == 1 && $shelving_real_attribute != '' && $product->offsetExists($shelving_real_attribute)) {
            $attributeName = $shelving_real_attribute;

            if ($attributeName == '%category%')
                $productArray['shelving_real'] = $productArray['%category%'];
            else {
                if ($generalConfig['non_standard_characters']!=0)
                    $productArray['shelving_real'] = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_real_attribute,false);
                else 
					$productArray['shelving_real'] = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_real_attribute);
            }
        }
        if ($this->packingsheetConfig['product_options_yn'] != 'no') {
            if (isset($options['options']) && is_array($options['options'])) {
                $i = 0;
                if (isset($options['options'][$i]))
					$continue = 1;
                else 
					$continue = 0;

                while ($continue == 1) {
                    if (trim($options['options'][$i]['label'] . $options['options'][$i]['value']) != '') {
                        if ($i > 0) 
							$custom_options_output .= ' ';
                        if(isset($options['options'][$i]['option_id'])){
                            $options_store = Mage::helper('pickpack/product')->getOptionProductByStore($this->packingsheetConfig['product_name_store_view'], $helper, $product_id, $storeId, $this->packingsheetConfig['product_name_specific_store_id'], $options, $i);
                            $options['options'][$i]['label'] = $options_store["label"];
                            $options['options'][$i]['value'] = $options_store["value"];
                        }
                        if ($this->packingsheetConfig['product_options_yn'] == 'yescol')
                            $custom_options_output .= htmlspecialchars_decode($options['options'][$i]['value']);
                        else
                            $custom_options_output .= htmlspecialchars_decode('[ ' . $options['options'][$i]['label'] . ' : ' . $options['options'][$i]['value'] . ' ]');
						
						$options['options'][$i] = '';
                    }
					$options['options'][$i] = '';
                    $i++;
                    if (isset($options['options'][$i]) && ($options['options'][$i] != $options['options'][$i-1]))
						$continue = 1;
                    else {
						$continue = 0;
						$options['options'][$i] = '';
					}
                }
				$options['options'][$i] = '';
				unset($options['options']);
            } elseif (is_array($options)) {
                unset($options['product']);
                foreach ($options as $attribute_code => $value) {
                    if ($attribute_code != "bundle_options") {
                        while (is_array($value))
                            $value = reset($value);
                        if (is_string($value) && trim($value) != '') {
                            if (Mage::helper('pickpack')->isInstalled('AW_Sarp'))
                                if ($attribute_code == 'aw_sarp_subscription_type')
                                    if (isset($periodTypeId) && isset($periodStartDate) && ($periodTypeId > 0) && $periodStartDate)
                                        $value = Mage::getModel('sarp/period')->load($periodTypeId)->getName();

                            if ($this->packingsheetConfig['product_options_yn'] == 'yescol')
                                $custom_options_output .= htmlspecialchars_decode($value);
                            else {
                                // TODO should show label here
                                $custom_options_output .= htmlspecialchars_decode('[ ' . str_replace(array('aw_sarp_subscription_type', 'aw_sarp_subscription_start'), array('Subscription type', 'First delivery'), $attribute_code) . ' : ' . $value . ' ]');
                            }
							$value = null;
                        }
                    }
                }

            }
        }

        $sku_bundle_real = '';
        $bundle_options_sku = '';
        if (isset($options['bundle_options'])) {
            if (is_array($options['bundle_options'])) {
                $sku_bundle_real = $sku_print;
                $bundle_options_sku = 'SKU : ' . $sku_print;
                $sku_print = $helper->__('(Bundle)');
            }
        }

        if($this->isShowGiftWrap()){
            $show_item_gift = false;
            if($item->getData('gw_id'))
				$show_item_gift = $item->getData('gw_id');
            $productArray['show_item_gift'] = $show_item_gift;
        }

        $name = '';
        $product_stock_qty = 0;

        //TUDO OPTIMIZE HERE
        if ($product && $this->packingsheetConfig['product_configurable_name'] == 'simple') {
            switch ($this->packingsheetConfig['product_name_store_view']) {
                case 'itemname':
                    $_newProduct =$helper->getProduct($product_id);
                    $name = trim($item->getName());
                    break;
                case 'default':
                    $_newProduct = $helper->getProduct($product_id);
                    if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                    if ($name == '') $name = trim($item->getName());
                    break;
                case 'storeview':
                    $_newProduct = $helper->getProductForStore($product_id, $storeId);
                    if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                    if ($name == '') $name = trim($item->getName());
                    break;
                case 'specificstore':
                    $_newProduct = $helper->getProductForStore($product_id,$this->packingsheetConfig['product_name_specific_store_id']);
                    if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                    if ($name == '') $name = trim($item->getName());
                    break;
                default:
                    $_newProduct =$helper->getProduct($product_id);
                    if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                    if ($name == '') $name = trim($item->getName());
                    break;
            }
        }
        else {
            if ($this->packingsheetConfig['product_name_store_view'] == "storeview")
                $name = trim($item->getName());
            else
                $name = $this->getNameDefaultStore($item);
            $_newProduct = $helper->getProductForStore($product_id, $storeId);
            if($this->packingsheetConfig['product_name_store_view'] == "specificstore" && $this->packingsheetConfig['product_name_specific_store_id'] != ""){
                $_Product = $helper->getProductForStore($product_id, $this->packingsheetConfig['product_name_specific_store_id']);
                if ($_Product->getData('name')) 
					$name = trim($_Product->getData('name'));
                if ($name == '') 
					$name = trim($item->getName());
            }
        }

        if ($this->packingsheetConfig['product_stock_qty_yn'] == 1){
            $product_stock_qty = (int)($_newProduct->getStockItem()->getQty());

            if($this->packingsheetConfig['location_specific_stock_yn']){
                if (strpos($order->getData('shipping_method'),'storepickup') !== false){
                    try {
                        $resource = Mage::getSingleton('core/resource');
                        $readConnection = $resource->getConnection('core_read');
                        $_newProduct_id = $_newProduct->getId();
                        $stock_id = $item->getData('stock_id');
                        if (isset($_newProduct_id) && isset($stock_id)){
                            $query = 'SELECT * FROM '.Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item').' WHERE stock_id = '.$stock_id.' AND product_id = '.$_newProduct_id;
                            $results = $readConnection->fetchAll($query);
                            $product_stock_qty = (int)$results[0]['qty'];
                        }
                        unset($_newProduct_id);
                        unset($stock_id);
                    } catch (Exception $e) {}
                }
            }
        }

        $productArray['product_stock_qty'] = $product_stock_qty;
        $productArray['product_qty_backordered'] = 0;
        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $children_items = $item->getChildrenItems();
            foreach($children_items as $children_item) {
                if (version_compare(Mage::getVersion(), '1.7', '>=')) {
                    //version is 1.6 or greater
                    $current_product_qty = $children_item->getProduct()->getStockItem()->getQty();
                } else {
                    //version is below 1.7
                    $_newProduct2 = $helper->getProductForStore($children_item->getData('product_id'), $storeId);
                    $current_product_qty = $_newProduct2->getStockItem()->getQty();
                }

                $ordered_product_qty = $children_item->getData('qty_ordered');
                if($current_product_qty < 0)
                    if(($current_product_qty+$ordered_product_qty)<=0)
                        $productArray['product_qty_backordered'] += round($ordered_product_qty,0);
                    else
                        $productArray['product_qty_backordered'] += round($current_product_qty+$ordered_product_qty,0);
            }
        } else {

            if (version_compare(Mage::getVersion(), '1.7', '>='))
                $current_product_qty = $item->getProduct()->getStockItem()->getQty();
            else {
                $_newProduct2 = $helper->getProductForStore($item->getData('product_id'), $storeId);
                $current_product_qty = $_newProduct2->getStockItem()->getQty();
            }

            $ordered_product_qty = $item->getData('qty_ordered');
            if($current_product_qty < 0)
                if(($current_product_qty+$ordered_product_qty)<=0)
					$productArray['product_qty_backordered'] = round($ordered_product_qty,0);
                else
                    $productArray['product_qty_backordered'] = round($current_product_qty+$ordered_product_qty,0);
        }

        $name = clean_for_pdf($name);

        $qty_string = $this->getQtyString($from_shipment, $this->_shippedItemsQty, $item, $qty, $invoice_or_pack, $order_invoice_id, $shipment_ids);

        if ($this->packingsheetConfig['show_qty_options'] == 2 && !$order_invoice_id && !$shipment_ids)
            $price_qty = $qty;
        else
            $price_qty = $qty_string;

        $productArray['display_name'] = $name;
        $productArray['qty_string'] = $qty_string;
        $productArray['sku_print'] = $sku_print;
        if (isset($options['bundle_options']))
            $productArray['sku_bundle_real'] = $sku_bundle_real;

        if($serial_code_yn == 1)
            $productArray["serial_code"] = $this->getSerialCode($order, $item);

        $price = 0;
        $tax_percent_temp = 0;
        $price_unit_tax = 0;
        $price_unit_taxed = 0;

        $tax = Mage::getModel('tax/calculation');
        $taxClassId = $product->getTaxClassId();
        $rates = $tax->load($taxClassId, 'product_tax_class_id');
        $taxCalculationRate = Mage::getModel('tax/calculation_rate')->load($rates['tax_calculation_rate_id']);


        if ($custom_options_output != '') {
            $custom_options_title = '';
            $productArray['custom_options_title_output'] = strip_tags($custom_options_title . $custom_options_output);
        }

        if (($this->packingsheetConfig['bundle_children_yn'] == 1) && isset($options['bundle_options']) && is_array($options['bundle_options'])) {
            $productArray['bundle_options_sku'] = $bundle_options_sku;

            $productArray['show_bundle_parent_yn'] = (($this->packingsheetConfig['show_bundle_parent'] != 1) && isset($product_build_value['bundle_options_sku'])) ? true : false;

            $productArray['bundle_children'] = $item->getChildrenItems();

            $productArray['bundle_qty_shipped'] = (int)$item->getQtyShipped();
            $productArray['bundle_qty_invoiced'] = (int)$item->getQtyInvoiced();
        }
        unset($options);
        $productArray['itemId'] = $item->getId();
        $productArray['item'] = $item;
        $productArray['product_sku_md5'] = md5($product->getData('sku'));

        return $productArray;
    }

    public function splitBundleOptions($from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $storeId) {
        $line_height = 0;

        $page = $this->getPage();
        $order = $this->getOrder();
        $helper = Mage::helper('pickpack');
        $storeId = $order->getStoreId();
        $supplier = $this->getSupplier();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $isShipment = ($from_shipment == 'order') ? false : true;

        $black_color = Mage::helper('pickpack/config_color')->getPdfColor('black_color');
        $red_color = Mage::helper('pickpack/config_color')->getPdfColor('red_color');
        $white_color = Mage::helper('pickpack/config_color')->getPdfColor('white_color');
        $greyout_color = Mage::helper('pickpack/config_color')->getPdfColor('greyout_color');

        $prices_yn = $this->isShowPrices();
        $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $product_options_title = trim($this->_getConfig('product_options_title', '', false, $wonder, $storeId));
        $font_size_options = $generalConfig['font_size_options'];

        $product_images_line_nudge = $this->packingsheetConfig['product_images_line_nudge'];
        if ($product_images_line_nudge > 0)
			$product_images_line_nudge = -abs($product_images_line_nudge);
        if ($this->packingsheetConfig['product_images_yn'] == 0) 
			$product_images_line_nudge = 0;

        $background_color_product_temp = trim($this->_getConfig('background_color_product', '#FFFFFF', false, 'general', $storeId));
        $background_color_product = new Zend_Pdf_Color_Html($background_color_product_temp);

        $shelving_2_attribute ='';

        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '')
                $shelving_yn = 0;
            if ($shelving_yn == 0)
                $shelving_attribute = null;
            $shelving_title = $this->_getConfig('shelving_title', '', false, $wonder, $storeId);
            $shelving_title = trim(str_ireplace(array('blank', "'"), '', $shelving_title));
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
            $combine_custom_attribute_title = trim(str_ireplace(array('blank', "'"), '', $this->_getConfig('combine_custom_attribute_title', '', false, $wonder, $storeId)));
            $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
        }
        else {
            $shelving_yn = 0;
            $combine_custom_attribute_yn = 0;
        }


        if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
            $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
            $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $storeId));
            if ($shelving_2_attribute == '')
                $shelving_2_yn = 0;
            $shelving_2_title = $this->_getConfig('shelving_2_title', '', false, $wonder, $storeId);
            $shelving_2_title = trim(str_ireplace(array('blank', "'"), '', $shelving_2_title));
        }
        else
            $shelving_2_yn = 0;

        if (($shelving_real_yn == 1) && ($shelving_yn == 1) && ($shelving_2_yn == 1)) {
            $shelving_3_yn = $this->_getConfig('shelving_3_yn', 0, false, $wonder, $storeId);
            $shelving_3_attribute = trim($this->_getConfig('shelving_3', '', false, $wonder, $storeId));
            if ($shelving_3_attribute == '')
                $shelving_3_yn = 0;
            $shelving_3_title = $this->_getConfig('shelving_3_title', '', false, $wonder, $storeId);
            $shelving_3_title = trim(str_ireplace(array('blank', "'"), '', $shelving_3_title));
        }
        else
            $shelving_3_yn = 0;

        $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $storeId);
        $font_family_barcode = Mage::helper('pickpack/barcode')->getFontForType($barcode_type);

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '')
			$shelving_real_yn = 0;
        $shelving_real_title = trim($this->_getConfig('shelving_real_title', '', false, $wonder, $storeId));
        $shelving_real_title = str_ireplace(array('blank', "'"), '', $shelving_real_title);

        $shelfX = $this->_getConfig('pricesN_shelfX', 10, false, $wonder, $storeId);
        $shelf2X = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
        $shelf3X = $this->_getConfig('pricesN_shelf3X', 10, false, $wonder, $storeId);
        $shelf4X = $this->_getConfig('shelving_3_Xpos', 10, false, $wonder, $storeId);

        $optionsX = $this->_getConfig('pricesN_optionsX', 0, false, $wonder, $storeId);

        $first_item_title_shift_sku = 0;
        $first_item_title_shift_items = 0;
        if (($this->packingsheetConfig['qty_x_pos'] > 50) && ($this->packingsheetConfig['tickbox_yn'] == 1)) {
            if ($this->packingsheetConfig['product_name_x_pos'] < $this->packingsheetConfig['product_sku_x_pos'])
				$first_item_title_shift_items = $this->getFirstItemTitleShift();
            elseif ($this->packingsheetConfig['product_sku_x_pos'] < $this->packingsheetConfig['product_name_x_pos']) $first_item_title_shift_sku = $this->getFirstItemTitleShift();
        }

        $font_helvetica = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $fillbar_padding = explode(",", $generalConfig['fillbar_padding']);

        $numbered_list_suffix = '.';

        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['background_color_subtitles']);

        $serial_code_yn = $this->_getConfig('serial_code_yn', 0, false, $wonder, $storeId);
        if($serial_code_yn == 1){
            $serial_code_title = $this->_getConfig('serial_code_title', 'serial_code', false, $wonder, $storeId);
            $serial_codeX = $this->_getConfig('serial_code_pos', 350, false, $wonder, $storeId);
        }

        $bundle_array = array();
        $bundle_quantity = array();
        $array_remove_keys = array();

        $invoice_title = $this->_getConfig('pickpack_title_pattern', 0, false, $wonder, $storeId, false);
        $invoice_title_temp = $invoice_title;
        $invoice_title_temp = explode("\n", $invoice_title_temp);
        $invoice_title_linebreak = count($invoice_title_temp);

        foreach( $this->childArray as $key => $child ) {
            if( in_array($child->getProductId(), $bundle_array)) {
                $qty = $child->getQtyOrdered() + $bundle_quantity[$child->getProductId()]['qty'];
                $child->setQtyOrdered($qty);
                $array_remove_keys[] = $bundle_quantity[$child->getProductId()]['key'];
            }
            $bundle_array[] = $child->getProductId();
            $bundle_quantity[$child->getProductId()]= array( 'qty' => $child->getQtyOrdered(), 'key' => $key );
        }
        unset($bundle_quantity);
        unset($bundle_array);

        foreach($array_remove_keys as $val)
            unset($this->childArray[$val]);

        foreach( $this->childArray as $child ) {
            $product_build_value = $child->getData('product_build_value');

			$this->checkNewPageNeeded();
            $page = $this->getPage();

            if ($background_color_product_temp != '#FFFFFF') {
				 if ( ($this->has_shown_product_image == 1) && ($product_images_line_nudge != 0) )
					 $this->y = $this->y + ($product_images_line_nudge * -1);
				
                $this->y += $generalConfig['font_size_body'] - 5;
                $this->getPage()->setLineWidth(0.5);
                //$this->y -= 5;
                $this->getPage()->setFillColor($background_color_product);
                $this->getPage()->setLineColor($background_color_product);
                $this->getPage()->drawLine($pageConfig['padded_left'], ($this->y), $pageConfig['padded_right'], ($this->y ));
                $this->getPage()->setFillColor($black_color);
                $this->y -= (($generalConfig['font_size_body']));
            }

            $product = $_newProduct = $helper->getProductForStore($child->getProductId(), $storeId);
            $sku = $child->getSku();
            $price = $child->getPriceInclTax();
            $qty = (int)$child->getQtyOrdered();
            if ($this->packingsheetConfig['product_name_store_view'] == "storeview")
                $name = $child->getName();
            elseif($this->packingsheetConfig['product_name_store_view'] == "specificstore" && $this->packingsheetConfig['product_name_specific_store_id'] != ""){
                $_product = $helper->getProductForStore($child->getProductId(), $this->packingsheetConfig['product_name_specific_store_id']);
                if ($_product->getData('name')) 
					$name = trim($_product->getData('name'));
                if ($name == '') 
					$name = trim($child->getName());
            }
            else
                $name = $this->getNameDefaultStore($child);

            $this->y -= $line_height*1.3;
            $this->options_y_counter += $line_height;
            if ($isShipment) {
                $this->productXInc = 25;
                switch ($this->packingsheetConfig['show_qty_options']) {
                    case 1:
                        $this->productXInc = 0;
                        break;
                    case 2:
                        $this->productXInc = 25;
                        break;
                    case 3:
                        $this->productXInc = 25;
                        break;
                }
            } else {
                switch ($this->packingsheetConfig['show_qty_options']) {
                    case 1:
                        $this->productXInc = 0;
                        break;
                    case 2:
                        $this->productXInc = 25;
                        break;
                    case 3:
                        $this->productXInc = 25;
                        break;
                }
            }
            /***get qty string**/
            $qty_string = $this->getQtyStringBundle($isShipment, $product_build_value, $qty, $invoice_or_pack, $order_invoice_id, $shipment_ids, $storeId);
            $price_qty = $qty_string;
            $addon_shift_x = $this->packingsheetConfig['shift_bundle_children_xpos'];

            /************************PRINTING CHECKBOX**************************/
            if (isset($this->sku_supplier_item_action[$supplier][$sku]) && $this->sku_supplier_item_action[$supplier][$sku] != 'hide' && !$this->hide_bundle_parent_f) {
                if ($this->sku_supplier_item_action[$supplier][$sku] == 'keepGrey')
                    $this->getPage()->setFillColor($greyout_color);
                elseif (($this->packingsheetConfig['tickbox_yn'] == 1) || ($this->packingsheetConfig['tickbox_2_yn'] == 1)) {
                    $this->getPage()->setLineWidth(0.5);
                    $this->getPage()->setFillColor($white_color);
                    $this->getPage()->setLineColor($black_color);
                    if ($this->packingsheetConfig['tickbox_yn'] == 1) {
                        if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5));
                        elseif ($this->packingsheetConfig['doubleline_yn'] == 2)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2));
                        elseif ($this->packingsheetConfig['doubleline_yn'] == 3)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3));
                        else
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2));
                        /* tickbox 1 signature line */
                        if ($this->packingsheetConfig['tickbox_signature_line']){
                            $this->getPage()->drawLine(($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] - 2)), ($this->y + 2), ($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y + 2));
                        }
                    }
                    if ($this->packingsheetConfig['tickbox_2_yn'] == 1) {
                        if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5));
                        elseif ($this->packingsheetConfig['doubleline_yn'] == 2)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2));
                        elseif ($this->packingsheetConfig['doubleline_yn'] == 3)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3));
                        else
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2));
                        /* tickbox 2 signature line */
                        if ($this->packingsheetConfig['tickbox_2_signature_line']){
                            $this->getPage()->drawLine(($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] - 2)), ($this->y + 2), ($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y + 2));
                        }
                    }
                    $this->getPage()->setFillColor($black_color);
                }
            }
            elseif ((($this->packingsheetConfig['tickbox_yn'] == 1) || ($this->packingsheetConfig['tickbox_2_yn'] == 1)) && !$this->hide_bundle_parent_f) {
                $this->getPage()->setLineWidth(0.5);
                $this->getPage()->setFillColor($white_color);
                $this->getPage()->setLineColor($black_color);
                if ($this->packingsheetConfig['tickbox_yn'] == 1) {
                    if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5));
                    elseif ($this->packingsheetConfig['doubleline_yn'] == 2)
                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2));
                    elseif ($this->packingsheetConfig['doubleline_yn'] == 3)
                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3));
                    else
                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2));

                    /* tickbox 1 signature line */
                    if ($this->packingsheetConfig['tickbox_signature_line']){
                        $this->getPage()->drawLine(($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] - 2)), ($this->y + 2), ($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y + 2));
                    }
                }
                if ($this->packingsheetConfig['tickbox_2_yn'] == 1) {
                    if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5));
                    elseif ($this->packingsheetConfig['doubleline_yn'] == 2)
                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2));
                    elseif ($this->packingsheetConfig['doubleline_yn'] == 3)
                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3));
                    else
                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2));
                    /* tickbox 2 signature line */
                    if ($this->packingsheetConfig['tickbox_2_signature_line']){
                        $this->getPage()->drawLine(($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] - 2)), ($this->y +2), ($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y + 2));
                    }
                }
                $this->getPage()->setFillColor($black_color);
            }

            if ($this->packingsheetConfig['numbered_product_list_yn'] == 1 && !$this->hide_bundle_parent_f)
                $this->_drawText($this->temp_count . $numbered_list_suffix, $this->packingsheetConfig['numbered_product_list_X'], ($this->y));

            $line_height = (1.15 * $generalConfig['font_size_body']);

            if($generalConfig['font_family_body'] == 'traditional_chinese' || $generalConfig['font_family_body'] == 'simplified_chinese'){
                $font_family_body_temp = $generalConfig['font_family_body'];
                $generalConfig['font_family_body'] = 'helvetica';
            }
            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            $draw_qty_value = round($product_build_value['qty_string'], 2);
			
            /* printing checkbox */
            if ($this->packingsheetConfig['numbered_product_list_bundle_children_yn'] == 1)
                $this->_drawText($this->temp_bundle_count . $numbered_list_suffix, $this->packingsheetConfig['numbered_product_list_bundle_children_X'] +$addon_shift_x, ($this->y));
			
            /***************************PRINTING BUNDLE SKU**********************/
            if ($this->packingsheetConfig['product_sku_yn'] == 1)
                $this->_drawText($sku, $this->packingsheetConfig['product_sku_x_pos'] + $addon_shift_x, $this->y);

            /***************************PRINTING BUNDLE BARCODE**********************/
            if (($this->packingsheetConfig['product_sku_barcode_yn'] != 0) && !$this->hide_bundle_parent_f) {
                $sku_barcodeY = $this->y - 4;
                $barcode = $sku;

                if ($this->packingsheetConfig['product_sku_barcode_yn'] == 2)
                    $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $storeId,1,true,$child->getProductId());
                $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$this->packingsheetConfig['product_sku_barcode_yn'],$this->packingsheetConfig['product_sku_barcode_x_pos'],$sku_barcodeY,$pageConfig['padded_right'],$font_family_barcode,$generalConfig['font_size_barcode_product'],$white_color);
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            }

            if (($this->packingsheetConfig['product_sku_barcode_2_yn'] != 0) && !$this->hide_bundle_parent_f) {
                $sku_barcodeY = $this->y - 4;
                $barcode = $sku;
                if ($this->packingsheetConfig['product_sku_barcode_2_yn'] == 2)
                    $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $storeId, 2, true, $child->getProductId());
                $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$this->packingsheetConfig['product_sku_barcode_yn'],$this->packingsheetConfig['product_sku_barcode_2_x_pos'],$sku_barcodeY,$pageConfig['padded_right'],$font_family_barcode,$generalConfig['font_size_barcode_product'],$white_color);
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            }

            if($generalConfig['font_family_body'] == 'traditional_chinese' || $generalConfig['font_family_body'] == 'simplified_chinese'){
                $font_family_body_temp = $generalConfig['font_family_body'];
                $generalConfig['font_family_body'] = 'helvetica';
            }
            $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

            /*************************** PRINTING BUNDLE CHILD PRODUCT QTY **********************/
            if ($draw_qty_value <= 1 || $this->packingsheetConfig['product_qty_upsize_yn'] == '0') {
                //set font to normal in case product_qty_upsize_yn wasn't set or product qty is 1
                $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            }else{
                //set font to bold and up one size
                $this->_setFont($page, 'bold', ($font_size_options + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            }
            $qty_draw_width = $this->widthForStringUsingFontSize( $draw_qty_value , $this->getPage()->getFont() , $this->getPage()->getFontSize(), $this->getPage()->getStyle(), $generalConfig['non_standard_characters']);

            $bundle_x = ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + 3);
            if ($this->packingsheetConfig['qty_x_pos'] > $bundle_x)
                $this->bundle_options_x = ($this->packingsheetConfig['tickboxX'] + 3);
            else {
                $this->bundle_options_x = ($this->packingsheetConfig['qty_x_pos'] + $this->packingsheetConfig['shift_bundle_children_xpos'] + 7);
                // tickbox   image    [bundle x] qty    name     code
                if ($this->packingsheetConfig['product_sku_x_pos'] > $this->packingsheetConfig['product_name_x_pos'])
                    $this->bundle_options_x = ($this->packingsheetConfig['qty_x_pos'] - 5); //($tickboxX+3);
            }

            if ($draw_qty_value > 1) {
                if ($this->packingsheetConfig['product_qty_upsize_yn'] == '1') { //boxed option
                    $this->getPage()->setFillColor($black_color);
                    //draw box
                    $this->getPage()->drawRectangle(($this->bundle_options_x - 2), ($this->y - 2), ($this->bundle_options_x + 2 + $qty_draw_width), ($this->y - 2 + $font_size_options));
                    //set font to bold and up one size with white color
                    $this->_setFont($page, 'bold', ($font_size_options + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                }
            }
            //draw qty
            $this->_drawText($qty_string, $this->bundle_options_x  + $addon_shift_x, ($this->y - 1));
            //return font to normal
            $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

            if(isset($font_family_body_temp)){
                $generalConfig['font_family_body'] = $font_family_body_temp;
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            }
            /***************************PRINTING BUNDLE PRICE**********************/
//old code
//            if ($prices_yn != '0') {
//                $bundle_options_part_price_total = ($price_qty * $price);
//                $bundle_price_display = $this->formatPriceTxt($order, $price);
//                $bundle_price_total_display = $this->formatPriceTxt($order, $bundle_options_part_price_total);
//
//                if ($price > 0)
//					$this->_drawText($bundle_price_display , $priceEachX, $this->y);
//                if ($bundle_options_part_price_total > 0)
//					$this->_drawText($bundle_price_total_display, $priceX, $this->y);
//            }
            /***************************PRINTING BUNDLE NAME**********************/
            $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $next_col_to_product_x = getPrevNext2($this->columns_xpos_array, 'productX', 'next');
            $max_name_length = $next_col_to_product_x - $this->packingsheetConfig['product_name_x_pos'];
            $line_width_name = $this->parseString($name, $font_temp_shelf2, ($generalConfig['font_size_body']));
            $char_width_name = ceil($line_width_name / strlen($name));
            $max_chars_name = round($max_name_length / $char_width_name);
            $multiline_name = wordwrap($name, $max_chars_name, "\n");
            $name_trim = str_trim($name, 'CHARS', $max_chars_name - 3, '...');
            $character_breakpoint_name = stringBreak($name, $max_name_length, $generalConfig['font_size_body'], $font_helvetica);

            if (strlen($name) > ($character_breakpoint_name + 2))
                $display_name = $name_trim;
            else
				$display_name = htmlspecialchars_decode($name);

            $token = strtok($multiline_name, "\n");
            $multiline_name_array = array();
            $temp_y = $this->y;
            $after_print_name_y = 1;
            if ($this->packingsheetConfig['show_product_name'] == 1 && $after_print_name_y) {
                if ($this->packingsheetConfig['product_name_bold_yn'])
                    $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                if ($this->packingsheetConfig['product_name_trim_yn'])
                    $this->_drawText($display_name, ($this->packingsheetConfig['product_name_x_pos'] +$addon_shift_x+ $this->productXInc + 2), $temp_y);
                elseif ($token != false) {
                    while ($token != false) {
                        $multiline_name_array[] = $token;
                        $token = strtok("\n");
                    }

                    foreach ($multiline_name_array as $name_in_line) {
                        $this->_drawText($name_in_line, ($this->packingsheetConfig['product_name_x_pos'] + $addon_shift_x + $this->productXInc + 2), $temp_y);
                        $temp_y -= $line_height;
                    }
                    $temp_y += $line_height;
                }
            }

            $this->y = $temp_y;
            $after_print_name_bundle_y = $this->y; // - $line_height;
            $this->_setFont($page, 'regular', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            unset($multiline_name_array);

            //draw backordered children bundle
            if ($this->packingsheetConfig['product_qty_backordered_yn'] == 1) {
                $backordered_children_bundle = (int)($child->getData("qty_backordered"));
                $this->_drawText($backordered_children_bundle, ($this->packingsheetConfig['product_qty_backordered_x_pos']), $this->y);
            }
            
			if ($this->packingsheetConfig['product_warehouse_yn'] == 1) {
                $item_warehouse = $child->getWarehouseTitle();
                $this->_drawText($item_warehouse, ($this->packingsheetConfig['prices_warehouseX']), $this->y);
            }
			
            /***************************PRINTING BUNDLE SHELVING**********************/
            $shelving_real_attribute = $this->_getConfig('shelving_real', 'shelf', false, $wonder, $storeId);
            $shelving_real_yn = $this->_getConfig('shelving_real_yn', 'shelf', false, $wonder, $storeId);
            $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$pageConfig['padded_right'],'shelfX',$addon_shift_x,$storeId,$this->getPageCount(),$this->items_header_top_firstpage,$pageConfig['page_top'],$this->items_header_top_firstpage);

            if($after_print_name_bundle_y < $this->y)
                $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] / 2;
            if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                $this->y = $after_print_barcode_y;

            /***************************PRINTING BUNDLE SHELVING 2**********************/
            $shelving_real_attribute = $this->_getConfig('shelving', 'shelf', false, $wonder, $storeId);
            $shelving_real_yn = $this->_getConfig('shelving_yn', 'shelf', false, $wonder, $storeId);
            $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$pageConfig['padded_right'],'shelf2X',$addon_shift_x,$storeId,$this->getPageCount(),$this->items_header_top_firstpage,$pageConfig['page_top'],$this->items_header_top_firstpage);

            if($after_print_name_bundle_y < $this->y)
                $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] / 2;
            if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                $this->y = $after_print_barcode_y;

            /***************************PRINTING BUNDLE SHELVING 3**********************/
            $shelving_real_attribute = $this->_getConfig('shelving_2', 'shelf', false, $wonder, $storeId);
            $shelving_real_yn = $this->_getConfig('shelving_2_yn', 'shelf', false, $wonder, $storeId);
            $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$pageConfig['padded_right'],'shelf3X',$addon_shift_x,$storeId,$this->getPageCount(),$this->items_header_top_firstpage,$pageConfig['page_top'],$this->items_header_top_firstpage);

            if($after_print_name_bundle_y < $this->y)
                $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] / 2;
            if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                $this->y = $after_print_barcode_y;

            /***************************PRINTING BUNDLE SHELVING 4**********************/
            $shelving_real_attribute = $this->_getConfig('shelving_3', 'shelf', false, $wonder, $storeId);
            $shelving_real_yn = $this->_getConfig('shelving_3_yn', 'shelf', false, $wonder, $storeId);
            $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$pageConfig['padded_right'],'shelf4X',$addon_shift_x,$storeId,$this->getPageCount(),$this->items_header_top_firstpage,$pageConfig['page_top'],$this->items_header_top_firstpage);

            if($after_print_name_bundle_y < $this->y)
                $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] / 2;
            if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                $this->y = $after_print_barcode_y;

            if ($this->packingsheetConfig['doubleline_yn'] == 2) $this->y -= 15;
            else
                if ($this->packingsheetConfig['doubleline_yn'] == 1.5) $this->y -= 7.5;
                else
                    if ($this->packingsheetConfig['doubleline_yn'] == 3) $this->y -= 20;
                    else
                        $this->y += 3.5;

            unset($after_print_name_bundle_y);
        }
    }

    public function showItem($product_build_value, $from_shipment = 'order', $invoice_or_pack = 'pack', $order_invoice_id = '', $shipment_ids = '', $order_items_arr = array()) {
        $page = $this->getPage();
        $helper = Mage::helper('pickpack');
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $supplier = $this->_supplier;
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $wonder = $this->getWonder();
        $isShipment = ($from_shipment == 'order') ? false : true;
        $this->productXInc = ($from_shipment == 'shipment') ? 25 : 0;
        $sku = $product_build_value['sku'];
        $item = $product_build_value['item'];
        $itemId = $product_build_value['itemId'];

        $font_helvetica = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

        $invoice_title = $this->_getConfig('pickpack_title_pattern', 0, false, $wonder, $storeId, false);
        $invoice_title_temp = $invoice_title;
        $invoice_title_temp = explode("\n", $invoice_title_temp);
        $invoice_title_linebreak = count($invoice_title_temp);

        $numbered_list_suffix = '.';
        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];

        $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $storeId);
        $font_family_barcode = Mage::helper('pickpack/barcode')->getFontForType($barcode_type);

        $prices_yn = $this->isShowPrices();
        $supplier_hide_attribute_column = $this->_getConfig('supplier_hide_attribute_column',0, false, $wonder, $storeId);
        $font_size_options = $generalConfig['font_size_options'];
        $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
        $product_options_title = trim($this->_getConfig('product_options_title', '', false, $wonder, $storeId));
        $optionsX = $this->_getConfig('pricesN_optionsX', 0, false, $wonder, $storeId);
        $shelfX = $this->_getConfig('pricesN_shelfX', 10, false, $wonder, $storeId);
        $shelf2X = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
        $shelf3X = $this->_getConfig('pricesN_shelf3X', 10, false, $wonder, $storeId);
        $shelf4X = $this->_getConfig('shelving_3_Xpos', 10, false, $wonder, $storeId);

        $product_gift_message_yn = $this->_getConfig('product_gift_message_yn', 'no', false, $wonder, $storeId);
        $gift_wrap_style_yn = $this->_getConfig('gift_wrap_style_yn', 'no', false, $wonder, $storeId);
        $message_title_tofrom_yn = $this->_getConfig('message_title_tofrom_yn', 'yes', false, $wonder, $storeId);

        $this->flag_image_newpage = 0;

        $tickboxX_bundle = 10;

        $first_item_title_shift_sku = 0;
        $first_item_title_shift_items = 0;
        if (($this->packingsheetConfig['qty_x_pos'] > 50) && ($this->packingsheetConfig['tickbox_yn'] == 1)) {
            if ($this->packingsheetConfig['product_name_x_pos'] < $this->packingsheetConfig['product_sku_x_pos'])
				$first_item_title_shift_items = $this->getFirstItemTitleShift();
            elseif ($this->packingsheetConfig['product_sku_x_pos'] < $this->packingsheetConfig['product_name_x_pos'])
				$first_item_title_shift_sku = $this->getFirstItemTitleShift();
        }

        $sort_packing = $this->_getConfig('sort_packing', 'sku', false, 'general', $storeId);
        $sortorder_packing = $this->_getConfig('sort_packing_order', 'ascending', false, 'general', $storeId);
        $sort_packing_attribute = null;
        if ($sort_packing == 'attribute') {
            $sort_packing_attribute = trim($this->_getConfig('sort_packing_attribute', '', false, 'general', $storeId));
            if ($sort_packing_attribute != '') 
				$sort_packing = $sort_packing_attribute;
            else 
				$sort_packing = 'sku';
        }

        $sort_packing_secondary = $this->_getConfig('sort_packing_secondary', 'sku', false, 'general', $storeId);
        $sortorder_packing_secondary = $this->_getConfig('sort_packing_secondary_order', 'ascending', false, 'general', $storeId);
        $sort_packing_secondary_attribute = null;
        if ($sort_packing_secondary == 'attribute') {
            $sort_packing_secondary_attribute = trim($this->_getConfig('sort_packing_secondary_attribute', '', false, 'general', $storeId));
            if ($sort_packing_secondary_attribute != '') 
				$sort_packing_secondary = $sort_packing_secondary_attribute;
            else 
				$sort_packing_secondary = 'sku';
        }

        if ($this->generalConfig['sort_packing_yn'] == 0){
            $sortorder_packing = 'none';
            $sortorder_packing_secondary = 'none';
        }

        $product_images_line_nudge = $this->packingsheetConfig['product_images_line_nudge'];
        if ($product_images_line_nudge > 0) 
			$product_images_line_nudge = -abs($product_images_line_nudge);
        if ($this->packingsheetConfig['product_images_yn'] == 0) 
			$product_images_line_nudge = 0;

        $serial_code_yn = $this->_getConfig('serial_code_yn', 0, false, $wonder, $storeId);
        if($serial_code_yn == 1){
            $serial_code_title = $this->_getConfig('serial_code_title', 'serial_code', false, $wonder, $storeId);
            $serial_codeX = $this->_getConfig('serial_code_pos', 350, false, $wonder, $storeId);
        }

        $black_color = Mage::helper('pickpack/config_color')->getPdfColor('black_color');
        $red_color = Mage::helper('pickpack/config_color')->getPdfColor('red_color');
        $white_color = Mage::helper('pickpack/config_color')->getPdfColor('white_color');
        $greyout_color = Mage::helper('pickpack/config_color')->getPdfColor('greyout_color');

        $font_family_gift_message = $this->_getConfig('font_family_gift_message', 'helvetica', false, 'general', $storeId);
        $font_style_gift_message = $this->_getConfig('font_style_gift_message', 'italic', false, 'general', $storeId);
        $font_size_gift_message = $this->_getConfig('font_size_gift_message', 12, false, 'general', $storeId);
        $font_color_gift_message = trim($this->_getConfig('font_color_gift_message', '#222222', false, 'general', $storeId));

        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['background_color_subtitles']);
        $background_color_vert_product_temp = '#FFFFFF';

        $fillbar_padding = explode(",", $generalConfig['fillbar_padding']);

        $shelving_2_attribute ='';
        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '') 
			$shelving_real_yn = 0;
        $shelving_real_title = trim($this->_getConfig('shelving_real_title', '', false, $wonder, $storeId));
        $shelving_real_title = str_ireplace(array('blank', "'"), '', $shelving_real_title);

        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '') 
				$shelving_yn = 0;
            if ($shelving_yn == 0) 
				$shelving_attribute = null;
            $shelving_title = $this->_getConfig('shelving_title', '', false, $wonder, $storeId);
            $shelving_title = trim(str_ireplace(array('blank', "'"), '', $shelving_title));
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
            $combine_custom_attribute_title = $this->_getConfig('combine_custom_attribute_title', '', false, $wonder, $storeId);
            $combine_custom_attribute_title = trim(str_ireplace(array('blank', "'"), '', $combine_custom_attribute_title));
            $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
            $combine_custom_attribute_title_each = $this->_getConfig('combine_custom_attribute_title_each', 10, false, $wonder, $storeId);
        } else {
            $shelving_yn = 0;
            $combine_custom_attribute_yn = 0;
        }
        if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
            $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
            $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $storeId));
            if ($shelving_2_attribute == '') 
				$shelving_2_yn = 0;
            $shelving_2_title = $this->_getConfig('shelving_2_title', '', false, $wonder, $storeId);
            $shelving_2_title = trim(str_ireplace(array('blank', "'"), '', $shelving_2_title));
        } else
            $shelving_2_yn = 0;

        if (($shelving_real_yn == 1) && ($shelving_yn == 1) && ($shelving_2_yn == 1)) {
            $shelving_3_yn = $this->_getConfig('shelving_3_yn', 0, false, $wonder, $storeId);
            $shelving_3_attribute = trim($this->_getConfig('shelving_3', '', false, $wonder, $storeId));
            if ($shelving_3_attribute == '') 
				$shelving_3_yn = 0;
            $shelving_3_title = $this->_getConfig('shelving_3_title', '', false, $wonder, $storeId);
            $shelving_3_title = trim(str_ireplace(array('blank', "'"), '', $shelving_3_title));
        } else
            $shelving_3_yn = 0;

        $shipaddress_packbarcode_yn = $this->_getConfig('shipaddress_packbarcode_yn', 0, false, $wonder, $storeId);
        if ($this->packingsheetConfig['pickpack_bottom_shipping_address_yn'] == 0)
            $shipaddress_packbarcode_yn = 0;

        if($this->packingsheetConfig['show_bundle_parent'] != 1 && isset($product_build_value['bundle_options_sku']))
            $this->hide_bundle_parent_f = true;

        if ($this->getReturnAddressMode() == 'yesgroup')
            $font_size_shipaddress = isset($this->packingsheetConfig['pickpack_shipfont_group']) ? $this->packingsheetConfig['pickpack_shipfont_group'] : 14;
        else
            $font_size_shipaddress = isset($this->packingsheetConfig['pickpack_shipfont']) ? $this->packingsheetConfig['pickpack_shipfont'] : 14;

        $sku_array = $this->getSkuArr();
        if( ($this->packingsheetConfig['new_pdf_per_name_yn'] == 0) 
			|| ( ($this->packingsheetConfig['new_pdf_per_name_yn'] == 1) 
				&& ($product_build_value["sku_print"] == $sku_array[count($sku_array) - $this->count_item]) ) ) {
            if (isset($product_build_value['bundle_options_sku']) && isset($product_build_value['sku_bundle_real'])) // after
                $sku_real = $product_build_value['sku_bundle_real'];
            else
                $sku_real = $product_build_value['sku_print'];
			
            $is_show_zero_qty = false;
            if ((!is_numeric($product_build_value['qty_string']) || ($product_build_value['qty_string'] > 0) || ($this->packingsheetConfig['show_zero_qty_options'] == 1) || $this->packingsheetConfig['show_zero_qty_options'] == 2))
                $is_show_zero_qty = true;

            if ((!$order_invoice_id || $this->checkItemBelongInvoiceDetail($sku_real, $order_invoice_id)) && (!$shipment_ids || $this->checkItemBelongShipment($sku_real, $shipment_ids)) && $is_show_zero_qty) {
                /****draw gray line for qty=0***/
                if ($this->packingsheetConfig['show_zero_qty_options'] == 1 && (int)$product_build_value['qty_string'] == 0)
                    $this->fontColorBodyItem = Mage::helper('pickpack/config_color')->getPdfColor('grayout_color');
                else
                    $this->fontColorBodyItem = $generalConfig['font_color_body'];
                $this->temp_count++;
                $this->min_product_y = 10;
                $this->print_item_count++;
                $this->order_item_count = $this->order_item_count + $product_build_value['qty_string'];
                $this->product_count++;

                /*************************CHECKING NEED TO CREATE NEW PAGE OR NOT**************************/
				$this->checkNewPageNeeded();
                $page = $this->getPage();

                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                /****draw gray line for qty=0***/
                if ($this->packingsheetConfig['show_zero_qty_options'] == 1 && (int)$product_build_value['qty_string'] == 0) {
                    $this->getPage()->setFillColor($greyout_color);
                    $this->getPage()->drawRectangle($pageConfig['padded_left'], ($this->y), $pageConfig['padded_right'], ($this->y + $generalConfig['font_size_body'] + 3));
                    $this->y = $this->y + 3;
                }

                /************************PRINTING CHECKBOX**************************/
                if (isset($this->sku_supplier_item_action[$supplier][$sku]) && $this->sku_supplier_item_action[$supplier][$sku] != 'hide' && !$this->hide_bundle_parent_f) {
                    if ($this->sku_supplier_item_action[$supplier][$sku] == 'keepGrey')
                        $this->getPage()->setFillColor($greyout_color);
                    elseif (($this->packingsheetConfig['tickbox_yn'] == 1) || ($this->packingsheetConfig['tickbox_2_yn'] == 1)) {
                        $this->getPage()->setLineWidth(0.5);
                        $this->getPage()->setFillColor($white_color);
                        $this->getPage()->setLineColor($black_color);
                        if ($this->packingsheetConfig['tickbox_yn'] == 1) {
                            if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
                                $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5));
                            elseif ($this->packingsheetConfig['doubleline_yn'] == 2)
                                $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2));
                            elseif ($this->packingsheetConfig['doubleline_yn'] == 3)
                                $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3));
                            else
                                $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2));
                            /* tickbox 1 signature line */
                            if ($this->packingsheetConfig['tickbox_signature_line']){
                                $this->getPage()->drawLine(($this->packingsheetConfig['tickboxX'] - $this->packingsheetConfig['tickbox_width']), ($this->y + 2), ($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y + 2));
                            }
                        }
                        if ($this->packingsheetConfig['tickbox_2_yn'] == 1) {
                            if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
                                $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5));
                            elseif ($this->packingsheetConfig['doubleline_yn'] == 2)
                                $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2));
                            elseif ($this->packingsheetConfig['doubleline_yn'] == 3)
                                $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3));
                            else
                                $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2));
                            /* tickbox 2 signature line */
                            if ($this->packingsheetConfig['tickbox_2_signature_line']){
                                $this->getPage()->drawLine(($this->packingsheetConfig['tickbox2X'] - $this->packingsheetConfig['tickbox2_width']), ($this->y + 2), ($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y + 2));
                            }
                        }
                        $this->getPage()->setFillColor($black_color);
                    }
                } elseif ((($this->packingsheetConfig['tickbox_yn'] == 1) || ($this->packingsheetConfig['tickbox_2_yn'] == 1)) && !$this->hide_bundle_parent_f) {
                    $this->getPage()->setLineWidth(0.5);
                    $this->getPage()->setFillColor($white_color);
                    $this->getPage()->setLineColor($black_color);
                    if ($this->packingsheetConfig['tickbox_yn'] == 1) {
                        if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5));
                        elseif ($this->packingsheetConfig['doubleline_yn'] == 2)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2));
                        elseif ($this->packingsheetConfig['doubleline_yn'] == 3)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3));
                        else
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2));
                        $after_print_ticbox1 = $this->y - $this->packingsheetConfig['tickbox_width'] + $generalConfig['font_size_body'];
                        /* tickbox 1 signature line */
                        if ($this->packingsheetConfig['tickbox_signature_line']){
                            $this->getPage()->drawLine(($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] - 2)), ($this->y + 2), ($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y + 2));
                        }
                    }
                    if ($this->packingsheetConfig['tickbox_2_yn'] == 1) {
                        if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 1.5));
                        elseif ($this->packingsheetConfig['doubleline_yn'] == 2)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 2));
                        elseif ($this->packingsheetConfig['doubleline_yn'] == 3)
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] /3), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] - 1 - $generalConfig['font_size_body'] / 3));
                        else
                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['tickbox_width']), ($this->y + $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] / 2 - 2));
                        $after_print_ticbox2 = $this->y - $this->packingsheetConfig['tickbox2_width'] + $generalConfig['font_size_body'];
                        /* tickbox 2 signature line */
                        if ($this->packingsheetConfig['tickbox_2_signature_line']){
                            $this->getPage()->drawLine(($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] - 2)), ($this->y + 2), ($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y + 2));
                        }
                    }
                    $this->getPage()->setFillColor($black_color);
                }

                if ($this->packingsheetConfig['numbered_product_list_yn'] == 1 && !$this->hide_bundle_parent_f)
                    $this->_drawText($this->temp_count . $numbered_list_suffix, $this->packingsheetConfig['numbered_product_list_X'], ($this->y));

                if (!isset($max_chars)) {
                    $maxWidthPage = ($pageConfig['padded_right'] + 20) - ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset);
                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $font_size_compare = $font_size_options;
                    $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                    $char_width = $line_width / 10;
                    $max_chars = round($maxWidthPage / $char_width);
                }

                $line_height = (1.15 * $generalConfig['font_size_body']);
                if($generalConfig['font_family_body'] == 'traditional_chinese' || $generalConfig['font_family_body'] == 'simplified_chinese'){
                    $font_family_body_temp = $generalConfig['font_family_body'];
                    $generalConfig['font_family_body'] = 'helvetica';
                }
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                $draw_qty_value = round($product_build_value['qty_string'], 2);
                /************************PRINTING QTY**************************/
                if (!$this->hide_bundle_parent_f){
                    if ($draw_qty_value <= 1 || $this->packingsheetConfig['product_qty_upsize_yn'] == '0') {
                        //set font to normal in case product_qty_upsize_yn wasn't set or product qty is 1
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                    }else{
                        //set font to bold and up one size
                        $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                    }
                    $qty_draw_width = $this->widthForStringUsingFontSize( $draw_qty_value , $this->getPage()->getFont() , $this->getPage()->getFontSize(), $this->getPage()->getStyle(), $generalConfig['non_standard_characters']);
                    $qty_value_center_width = 0;
                    if ($this->packingsheetConfig['center_value_qty'])
                        $qty_value_center_width = $this->widthForStringUsingFontSize($this->packingsheetConfig['qty_title'], $generalConfig['font_family_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_style_subtitles'], $generalConfig['non_standard_characters']) / 2 - $qty_draw_width / 2;

                    if ($draw_qty_value > 1) {
                        if ($this->packingsheetConfig['product_qty_upsize_yn'] == '1') { //boxed option
                            $this->getPage()->setFillColor($black_color);
                            //draw box
                            $this->getPage()->drawRectangle(($this->packingsheetConfig['qty_x_pos'] - 2 + $qty_value_center_width), ($this->y - 3), ($this->packingsheetConfig['qty_x_pos'] + 2 + $qty_draw_width + $qty_value_center_width), ($this->y - 3 + $generalConfig['font_size_body'] * 1.2));
                            //set font to bold and up one size with white color
                            $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                        }
                    }
                    //draw qty
                    $this->_drawText($draw_qty_value, $this->packingsheetConfig['qty_x_pos'] + $qty_value_center_width, $this->y);
                    //return font to normal
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                }

                if(isset($font_family_body_temp)){
                    $generalConfig['font_family_body'] = $font_family_body_temp;
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                }

                /***************************PRINTING SKU**********************/
                if ( 
					( ($this->packingsheetConfig['product_sku_yn'] == 1) || ($this->packingsheetConfig['product_sku_yn'] == 'fullsku') || ($this->packingsheetConfig['product_sku_yn'] == 'configurable') )
					&& !$this->hide_bundle_parent_f
					) {

                    $line_height = (1.15 * $generalConfig['font_size_body']);
                    $temp_y = $this->y;
                    $after_print_sku_y = $this->y;
                    $line_count_sku = 0;
                    $multiline_sku = $this->skuWordwrap($this->minDistanceSku, $generalConfig['font_size_body'], $product_build_value['sku_print']);
                    foreach ($multiline_sku as $sku_in_line) {
                        $line_count_sku++;
                        $this->_drawText($sku_in_line, $this->packingsheetConfig['product_sku_x_pos'], $this->y);
                        $this->y -= $line_height;
                    }
                    $after_print_sku_y = $temp_y - ($line_count_sku) * $line_height;
                    $this->y = $temp_y;
                }

                /***************************PRINTING STOCK**********************/
                if ($this->packingsheetConfig['product_stock_qty_yn'] == 1 && !$this->hide_bundle_parent_f)
                    $this->_drawText($product_build_value['product_stock_qty'], ($this->packingsheetConfig['product_stock_qty_x_pos']), $this->y);

                /***************************PRINTING QTY BACKORDERED *************/
                if ($this->packingsheetConfig['product_qty_backordered_yn'] == 1 && !$this->hide_bundle_parent_f)
                    $this->_drawText($product_build_value['product_qty_backordered'], ($this->packingsheetConfig['product_qty_backordered_x_pos']), $this->y);

                if($supplier_hide_attribute_column ==0 && !$this->hide_bundle_parent_f)
                    if ($this->packingsheetConfig['product_warehouse_yn'] == 1)
                        $this->_drawText($product_build_value['item_warehouse'], ($this->packingsheetConfig['prices_warehouseX']), $this->y);

                /***************************PRINTING BARCODE**********************/
                if (($this->packingsheetConfig['product_sku_barcode_yn'] != 0) && !$this->hide_bundle_parent_f) {
                    $after_print_barcode_y = $this->y;
                    $sku_barcodeY = $this->y - 4;
                    $barcode = $product_build_value['sku_print'];
                    if ($this->packingsheetConfig['product_sku_barcode_yn'] == 2)
                        $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $storeId);
                    $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$this->packingsheetConfig['product_sku_barcode_yn'],$this->packingsheetConfig['product_sku_barcode_x_pos'],$sku_barcodeY,$pageConfig['padded_right'],$font_family_barcode,$generalConfig['font_size_barcode_product'],$white_color);
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                }

                if (($this->packingsheetConfig['product_sku_barcode_2_yn'] != 0) && !$this->hide_bundle_parent_f) {
                    $after_print_barcode_y = $this->y;
                    $barcode = $product_build_value['sku_print'];
                    if ($this->packingsheetConfig['product_sku_barcode_2_yn'] == 2)
                        $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $storeId,2);
                    $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$this->packingsheetConfig['product_sku_barcode_yn'],$this->packingsheetConfig['product_sku_barcode_2_x_pos'],$sku_barcodeY,$pageConfig['padded_right'],$font_family_barcode,$generalConfig['font_size_barcode_product'],$white_color);
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                }

                /***************************PRINTING PRICE************************/
                if ($prices_yn != 0){
                    $priceBlock = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_products_price', array($this, $order, $page));
                    $priceBlock->generalConfig = $this->generalConfig;
                    $priceBlock->packingsheetConfig = $this->packingsheetConfig;
                    $priceBlock->y = $this->y;
                    $priceBlock->showProductPrice($product_build_value['product'],$product_build_value['item']);
                    $priceBlock->showProductDiscount($product_build_value['product'],$product_build_value['item']);
                    $priceBlock->showProductTax($product_build_value['product'],$product_build_value['item']);
                    $priceBlock->showProductTotal($product_build_value['product'],$product_build_value['item']);
                    $priceBlock->calucateSubtotalData($this->subtotal_data);
                }
                /***************************PRINTING ALLOWANCE**********************/
                if($this->packingsheetConfig['show_allowance_yn'] == 1)
                    $this->_drawText($product_build_value['allowance'], $this->packingsheetConfig['show_allowance_xpos'], $this->y);

                /***************************PRINTING NAME**********************/
                $this->yItemPos = $this->y;
                $line_height = (1.15 * $generalConfig['font_size_body']);
                $after_print_name_y = $this->y;
                $next_col_to_product_x = getPrevNext2($this->columns_xpos_array, 'productX', 'next');
                $max_name_length = $next_col_to_product_x - $this->packingsheetConfig['product_name_x_pos'];
                $name = Mage::helper('pickpack/functions')->clean_method($product_build_value['display_name'], 'pdf');

                if($name != "" && !$this->hide_bundle_parent_f){
                    $line_width_name = $this->parseString($name, $font_temp_shelf2, ($generalConfig['font_size_body']));
                    $char_width_name = ceil($line_width_name / strlen($name));
                    $max_chars_name = round($max_name_length / $char_width_name);
                    $multiline_name = wordwrap($name, $max_chars_name, "\n");
                    $name_trim = str_trim($name, 'CHARS', $max_chars_name - 3, '...');
                    $token = strtok($multiline_name, "\n");
                    if ($this->packingsheetConfig['product_name_bold_yn'])
                        $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);

                    $line_count = 0;
                    if ($this->packingsheetConfig['product_name_trim_yn']) {
                        if ($this->packingsheetConfig['show_product_name'] == 1){
                            // custom for print deposit label
                            $temp_y = $this->y;
                            $this->_drawText($name_trim, ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc), $this->y);
                            $line_count++;

                            $deposit_label = $product_build_value['product']['creare_deposit_label'];
                            if (isset($deposit_label) && ($deposit_label != '')){
                                $this->y -= $line_height;
                                $this->_drawText($deposit_label.' '  , ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc), $this->y);
                                $line_count++;
                            }

                            $after_print_name_y = $temp_y - $line_count * $line_height;
                            $this->y = $temp_y;
                        }
                    } else {
                        if ($this->packingsheetConfig['show_product_name'] == 1) {
                            $token = strtok($multiline_name, "\n");
                            $multiline_name_array = array();
                            $temp_y = $this->y;
                            if ($token != false) {
                                while ($token != false) {
                                    $multiline_name_array[] = $token;
                                    $token = strtok("\n");
                                }
                                foreach ($multiline_name_array as $name_in_line) {
                                    $line_count++;
                                    $this->_drawText($name_in_line. ' '  , ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc), $this->y);
                                    $this->y -= $line_height;
                                }
                            }

                            // custom for print deposit label
                            $deposit_label = $product_build_value['product']['creare_deposit_label'];
                            if (isset($deposit_label) && ($deposit_label != '')){
                                $this->_drawText($deposit_label.' '  , ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc), $this->y);
                                $line_count++;
                                $this->y -= $line_height;
                            }

                            $after_print_name_y = $temp_y - ($line_count) * $line_height;
                            $this->y = $temp_y;
                        } else
                            $after_print_name_y = $this->y - $generalConfig['font_size_body'];
                    }
                    $this->_setFont($page, 'regular', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                }

                /*PRINT GIFT WRAP*/
                if($this->isShowGiftWrap()){
                    if($product_build_value['show_item_gift']){
                        $gift_wrap_data = Mage::getModel('enterprise_giftwrapping/wrapping')->load($product_build_value['show_item_gift']);
                        if($this->packingsheetConfig['show_gift_wrap_icon'] == 0)
                            $this->_drawText('Yes - '.$gift_wrap_data->getData('design'), ($this->packingsheetConfig['show_gift_wrap_xpos'] + $first_item_title_shift_items), $this->y);
                        else{

                            $media_path = Mage::getBaseDir('media');
                            $image = Zend_Pdf_Image::imageWithPath($media_path.'/moogento/pickpack/gift_wrap.png');
                            $x1 = $this->packingsheetConfig['show_gift_wrap_xpos'] + $first_item_title_shift_items;
                            $x2 = $this->packingsheetConfig['show_gift_wrap_xpos'] + $first_item_title_shift_items + 13;
                            $y1 = $this->y - 5;
                            $y2 = $y1 +13 ;
                            $this->getPage()->drawImage($image, $x1, $y1 , $x2, $y2);
                            if($this->packingsheetConfig['show_gift_wrap_label'])
                                $this->_drawText($gift_wrap_data->getData('design'), $x2 + 2, $y1 + 2);
                        }
                    }

                }

                /***************************PRINT SERIAL CODE**********************/
                if($serial_code_yn == 1 && $product_build_value['serial_code'] != '' && !$this->hide_bundle_parent_f){
                    $serial_code_item = $product_build_value['serial_code'];
                    $this->_drawText($serial_code_item, $serial_codeX, $this->y);
                }

                /***************************PRINTING OPTIONS**********************/
                $after_print_option_y = $this->y;
                if ($this->packingsheetConfig['product_options_yn'] == 'yescol' && isset($product_build_value['custom_options_title_output']) && !$this->hide_bundle_parent_f) {
                    $this->_drawText($product_build_value['custom_options_title_output'], $optionsX, $this->y);
                } elseif ((($this->packingsheetConfig['product_options_yn'] == 'yes') || ($this->packingsheetConfig['product_options_yn'] == 'yesstacked') || ($this->packingsheetConfig['product_options_yn'] == 'yesboxed')) && isset($product_build_value['custom_options_title_output']) && ($product_build_value['custom_options_title_output'] != '') && !$this->hide_bundle_parent_f) {
                    $temp_y = $this->y;

                    $this->y = $after_print_name_y + $generalConfig['font_size_body'] * 1.4 - 2;
                    $this->offset = 10;
                    $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                    $maxWidthPage = ($pageConfig['padded_right'] + 20) - ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset);
                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $font_size_compare = $font_size_options;
                    $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                    $char_width = $line_width / 10;
                    $max_chars = round($maxWidthPage / $char_width);
                    if (strlen($product_build_value['custom_options_title_output']) > $max_chars) {
                        if ($this->packingsheetConfig['product_options_yn'] == 'yes') 
							$chunks = split_words($product_build_value['custom_options_title_output'], '/ /', $max_chars);
                        elseif ($this->packingsheetConfig['product_options_yn'] == 'yesstacked' || $this->packingsheetConfig['product_options_yn'] == 'yesboxed')
                            $chunks = explode(']', $product_build_value['custom_options_title_output']);
                        if($this->packingsheetConfig['product_options_yn'] == 'yesboxed'){
                            $this->getPage()->setLineWidth(1);
                            $this->getPage()->setFillColor($white_color);
                            $this->getPage()->setLineColor($black_color);
                            foreach ($chunks as $key => $element) {
                                if(trim($element) == '')
                                    unset($chunks[$key]);
                            }
                            $bottom_box_y = $this->y - count($chunks) * ($font_size_options + 2) - 4;
                            $this->getPage()->drawRectangle($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset - 2, ($this->y - 1),$this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $maxWidthPage/2, $bottom_box_y);
                        }
                        $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                        $lines = 0;
                        foreach ($chunks as $key => $chunk) {
                            $this->chunk_display = '';
                            if (trim($chunk != '')) {
                                $chunk = trim($chunk);

                                if ($this->packingsheetConfig['product_options_yn'] == 'yesstacked' || $this->packingsheetConfig['product_options_yn'] == 'yesboxed')
                                    $this->chunk_display = str_replace('[', '', $chunk);
                                else $this->chunk_display = $chunk;
                                if($this->packingsheetConfig['product_name_trim_yn'] == 1){
                                    $this->y -= ($font_size_options + 2);
                                    $this->options_y_counter += $font_size_options;
                                    $this->chunk_display = str_trim($this->chunk_display, 'WORDS', $max_chars + 4, '...');
                                    $this->_drawText($this->chunk_display, ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset), $this->y);
                                    $lines++;
                                }else{
                                    $multiline_name = wordwrap($this->chunk_display, $max_chars + 4, "\n");
                                    $token = strtok($multiline_name, "\n");
                                    if ($token != false) {
                                        while ($token != false) {
                                            $this->y -= ($font_size_options + 2);
                                            $this->options_y_counter += $font_size_options;
                                            $this->_drawText($token, ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset), $this->y);
                                            $lines++;
                                            $token = strtok("\n");
                                        }
                                    } else {
                                        $this->y -= ($font_size_options + 2);
                                        $this->options_y_counter += $font_size_options;
                                        $this->_drawText($token, ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset), $this->y);
                                        $lines++;
                                    }
                                }
                            }
                        }

                        unset($chunks);
                    } else {
                        if ($this->packingsheetConfig['product_options_yn'] == 'yesstacked' || $this->packingsheetConfig['product_options_yn'] == 'yesboxed') {
                            $chunks = explode(']', $product_build_value['custom_options_title_output']);
                            if($this->packingsheetConfig['product_options_yn'] == 'yesboxed'){
                                $this->getPage()->setLineWidth(1);
                                $this->getPage()->setFillColor($white_color);
                                $this->getPage()->setLineColor($black_color);
                                foreach ($chunks as $key => $element) {
                                    if(trim($element) == '')
                                        unset($chunks[$key]);
                                }
                                $bottom_box_y = $this->y - count($chunks) * ($font_size_options + 2) - 4;
                                $this->getPage()->drawRectangle($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset - 2, ($this->y - 1),$this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $maxWidthPage/2, $bottom_box_y);
                            }
                            $lines = 0;
                            $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                            foreach ($chunks as $key => $chunk) {
                                $this->chunk_display = '';
                                if (trim($chunk != '')) {
                                    $this->y -= ($font_size_options + 2);
                                    $this->options_y_counter += $font_size_options;
                                    //$this->chunk_display = str_replace('[[', '[', '[' . $chunk);
                                    $chunk = trim($chunk);
                                    if ($this->packingsheetConfig['product_options_yn'] == 'yesstacked' || $this->packingsheetConfig['product_options_yn'] == 'yesboxed')
                                        $this->chunk_display = str_replace('[', '', $chunk);
                                    else $this->chunk_display = $chunk;
                                    $this->_drawText($this->chunk_display, ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset), $this->y);
                                    $lines++;
                                }
                            }
                            unset($chunks);
                        } else {
                            $this->y -= ($font_size_options + 2);
                            $this->options_y_counter += $font_size_options;
                            $this->_drawText($product_build_value['custom_options_title_output'], ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset), $this->y);

                        }
                    }
                    $this->y -= $generalConfig['font_size_body'];
                    $after_print_option_y = $this->y;
                    $this->y = $temp_y;
                }
                /***************************PRINTING COMBINE ATTRIBUTE UNDER PRODUCT LINE**************************/
                if($this->packingsheetConfig['combine_custom_attribute_under_product'] == 1){
                    $this->offset = 10;
                    $attribute_string = $this->getCombineAttribute($product_build_value, '', "", "", $wonder, $storeId);
                    if((($this->packingsheetConfig['product_options_yn'] == 'yes') || ($this->packingsheetConfig['product_options_yn'] == 'yesstacked')) && isset($product_build_value['custom_options_title_output']) && ($product_build_value['custom_options_title_output'] != '')){
                        $this->y = $after_print_option_y;
                        $after_print_option_y = $this->y - $generalConfig['font_size_body'];
                    }
                    else
                        $this->y = $after_print_name_y + $generalConfig['font_size_body'] * 1.4 - $font_size_options - 3;

                    $this->_drawText(trim($attribute_string, ","), ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset), $this->y);
                }

                $this->printingImage($product_build_value);

                $this->printingShelving($product_build_value);

                $this->printingShelving2($product_build_value);

                $this->printingShelving3($product_build_value);

                $this->printingShelving4($product_build_value);

                /****************************PRINTING COMBINE CUSTOM ATTRIBUTE**********************/
                if($combine_custom_attribute_yn == 1 && $this->custom_attribute_combined_array != ''){
                    foreach ($this->custom_attribute_combined_array as $key => $custom_attribute) {
                        if($combine_custom_attribute_title_each == 1)
                            $this->_drawText($key . ': ' . $custom_attribute, $combine_custom_attribute_Xpos, $this->y);
                        else
                            $this->_drawText($custom_attribute, $combine_custom_attribute_Xpos, $this->y);
                        //$line_height=20;
                        $this->y -= $line_height;

                        $this->checkNewPageNeeded();
                        $page = $this->getPage();
                    }
                    unset($this->custom_attribute_combined_array);
                }
                /***************************PRINTING INDIVIDUAL MESSAGE**********************/
				$imageDimensions = $this->getProductImagesMaxDimensions();
                $x2 = $this->packingsheetConfig['product_images_x_pos'];
                $x2 = ($this->packingsheetConfig['product_images_x_pos'] + $imageDimensions[0] + 20);
				
				
                if (($product_gift_message_yn == 'yesunderinvi') && !$this->hide_bundle_parent_f) {
                    $product_giftmessage_xpos = 40;
                    $this->gift_message_array['items'][$sku]['printed'] = 1;
                    //Product gift message set front size

                    if ($product_build_value['has_message'] == 1) {

                        if ($this->has_shown_product_image == 0)
                            $this->y -= 5;

                        if ($message_title_tofrom_yn == 1) {
                            $font_size_temp = $font_size_gift_message;
                            $this->_setFont($page, 'bold', ($font_size_gift_message), $font_family_gift_message, $generalConfig['non_standard_characters'], $font_color_gift_message);
                            $this->y = $this->showToFrom($message_title_tofrom_yn, $product_build_value['message-to'], $product_giftmessage_xpos, $this->y, $product_build_value['message-from'], $font_size_temp, $page);
                        }
                        $this->_setFont($page, $font_style_gift_message, ($font_size_gift_message - 1), $font_family_gift_message, $generalConfig['non_standard_characters'], $font_color_gift_message);
                        $temp_height = 0;

                        if(is_array($product_build_value['message-content'])) {
                           
						    foreach ($product_build_value['message-content'] as $gift_msg_line) {
                                
								$this->checkNewPageNeeded();
                                $page = $this->getPage();
																
								$this->_setFont($page, 'bolditalic', $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        
								$this->_drawText($helper->__('Gift message: '), $x2 + 10, ( $this->y - $generalConfig['font_size_options'] )  );
						
								$this->_setFont($page, 'italic', $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
		                        $this->_drawText(ucwords(trim($gift_msg_line)), $x2 + 10 + (strlen($helper->__('Gift message: ')) * $generalConfig['font_size_options'] * (72/300) * 2), ( $this->y - $generalConfig['font_size_options'] )  );
								
                                $this->y -= ($generalConfig['font_size_options'] + 3);
                            }
							
                        } else {
                            $gift_msg_line = $product_build_value['message-content'];
                           
						    $this->checkNewPageNeeded();
                            $page = $this->getPage();
							
							$this->_setFont($page, 'bolditalic', $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    
							$this->_drawText($helper->__('Gift message: '), $x2 + 10, ( $this->y - $generalConfig['font_size_options'] )  );
					
							$this->_setFont($page, 'italic', $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	                        $this->_drawText(ucwords(trim($gift_msg_line)), $x2 + 10 + (strlen($helper->__('Gift message: ')) * $generalConfig['font_size_options'] * (72/300) * 2), ( $this->y - $generalConfig['font_size_options'] )  );
							
                            $this->y -= ($generalConfig['font_size_options'] * 0.6);
                        }
                    }

                }

                if ($gift_wrap_style_yn == 'yesunderinvi' && !$this->hide_bundle_parent_f) {
                    $giftWrapInfo = Mage::helper('pickpack/gift')->getGiftWrapInfo($order, $wonder);
                   
				    if (isset($giftWrapInfo['per_item'][$item->getId()])) {
                        
						$this->checkNewPageNeeded();
                        $page = $this->getPage();
                        
						$data = $giftWrapInfo['per_item'][$item->getId()];
                        $wrappingImageShown = false;
						
                        if ($data['wrapping_image']) {
                            $x1 = ($this->packingsheetConfig['product_images_x_pos'] + 20);
                            $y1 = ($this->y - $imageDimensions[1] - 5);
                            $x2 = ($this->packingsheetConfig['product_images_x_pos'] + $imageDimensions[0] + 20);
                            $y2 = ($this->y - 5);
                            $image = Zend_Pdf_Image::imageWithPath($data['wrapping_image']);
                            $this->getPage()->drawImage($image, $x1, $y1, $x2, $y2);
                            $wrappingImageShown = true;
                        }
                        $this->y -= $generalConfig['font_size_body'];
						$this->_setFont($page, 'bolditalic', $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        
						$this->_drawText($helper->__('Gift wrap: '), $x2 + 10, ( $this->y - (($y2-$y1)/2) )  );
						
						$this->_setFont($page, 'italic', $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $this->_drawText($data['wrapping_paper'], $x2 + 10 + (strlen($helper->__('Gift wrap: ')) * $generalConfig['font_size_options'] * (72/300) * 2), ( $this->y - (($y2-$y1)/2) )  );

                        if ($wrappingImageShown)
                            $this->y -= ($imageDimensions[1]-6);

                        if ($data['giftcard_image']) {
                            $x1 = ($this->packingsheetConfig['product_images_x_pos'] + 20);
                            $y1 = ($this->y - $imageDimensions[1]);
                            $x2 = ($this->packingsheetConfig['product_images_x_pos'] + $imageDimensions[0] + 20);
                            $y2 = ($this->y);
                            $image = Zend_Pdf_Image::imageWithPath($data['giftcard_image']);
                            $this->getPage()->drawImage($image, $x1, $y1, $x2, $y2);
                            $this->y -= $generalConfig['font_size_body'];
							$this->_setFont($page, 'bolditalic', $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            
							$this->_drawText($helper->__('Gift card: '), $x2 + 10, ( $this->y - (($y2-$y1)/2) + $generalConfig['font_size_options'])  );
							
							$this->_setFont($page, 'italic', $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	                        $this->_drawText($data['giftcard_name'], $x2 + 10 + (strlen($helper->__('Gift card: ')) * $generalConfig['font_size_options'] * (72/300) * 2), ( $this->y - (($y2-$y1)/2)  + $generalConfig['font_size_options'])  );
							
                            $this->y -= $imageDimensions[1];
                        }
                        $this->y -= $generalConfig['font_size_body'];
                    }
                }

                /***************************PRINTING EXTRA FEE**********************/
                if (isset($magik_product_str[$itemId]) && ($magik_product_str[$itemId] != '') && !$this->hide_bundle_parent_f) {
                    $this->offset = 10;
                    $line_height = ($generalConfig['font_size_body']);
                    $this->y -= $line_height;

                    $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                    $this->_drawText($magik_product_str[$itemId], ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $this->offset), $this->y);
                    $this->y -= $line_height;
                }
                /***************************PRINTING BUNDLE OPTIONS**********************/
                $flag_new_page_bundle = 0;

                if( $this->packingsheetConfig['show_bundle_parent'] == 1 && $this->packingsheetConfig['bundle_children_split'] == 1 ) {
                    if (isset($product_build_value['bundle_options_sku'])) {
                        if (isset($product_build_value['bundle_children']) && count($product_build_value['bundle_children'])) {
                            $this->sortChildBundleProducts($product_build_value['bundle_children'],$product_build_value['product']);
                            foreach ($product_build_value['bundle_children'] as $child) {
                                $qty = (int)$child->getQtyOrdered();
                                $qty_string = $this->getQtyStringBundle($isShipment, $product_build_value, $qty, $invoice_or_pack, $order_invoice_id, $shipment_ids, $storeId);
                                $child->setData('qty_string', $qty_string);
                                $this->childArray[] = $child;
                            }
                        }
                    }
                } else {
                    if (isset($product_build_value['bundle_options_sku'])) {
                        if (isset($product_build_value['bundle_children']) && count($product_build_value['bundle_children'])) {
                            $this->sortChildBundleProducts($product_build_value['bundle_children'],$product_build_value['product']);
                        }

                        $this->offset = 10;
                        $line_height = ($generalConfig['font_size_body']);
                        if(isset($after_print_option_y) && $after_print_option_y < $this->y)
                            $this->y = $after_print_option_y;
                        else
                            $this->y -= $line_height;
                        $this->options_y_counter += $line_height;

                        $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);

                        // $bundle_x = ($productX + $this->productXInc + 3);
                        if($after_print_name_y < $this->y)
                            $this->y = $after_print_name_y;
                        if (($this->packingsheetConfig['product_sku_x_pos'] < 800) && ($this->packingsheetConfig['product_sku_yn'] == 1)) {
                            $display_bundle_sku = $product_build_value['bundle_options_sku'];
                            $display_bundle_sku = str_trim($display_bundle_sku, 'WORDS', $pageConfig['padded_right'] - 3, '...');

                            $this->_drawText($display_bundle_sku, ($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + 3), $this->y);
                            $this->y -= $line_height;
                            $this->options_y_counter += $line_height;
                        } else 
							$this->offset = 0;
							
						$bundle_x = ($this->packingsheetConfig['qty_x_pos'] + $this->packingsheetConfig['shift_bundle_children_xpos']);
						$bundle_options_x = ($this->packingsheetConfig['qty_x_pos'] + $this->packingsheetConfig['shift_bundle_children_xpos']);// + 7);
						$bundle_line_x2 = (($this->packingsheetConfig['tickboxX'] + 3) + (strlen('Bundle Options : ') * ($generalConfig['font_size_body'] - 2)) + $this->packingsheetConfig['shift_bundle_children_xpos'] + 20);
                        // tickbox   image    [bundle x] qty    name     code
                        $bundle_before = 0;

                        if ( $this->y >= ($this->getMinProductY() + (2 * $generalConfig['font_size_body'])) ) {
                            $bundle_before = 1;
                            $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], '#333333');
                            $this->_drawText($helper->__('Bundle Options') . ' : ', $bundle_options_x, $this->y);
                            $this->getPage()->setLineWidth(0.5);
                            $this->getPage()->setFillColor($white_color);
                            $this->getPage()->setLineColor($greyout_color);
                            $this->getPage()->drawLine(($bundle_options_x), ($this->y - 2), $bundle_line_x2, ($this->y - 2));
                        }
                        $this->getPage()->setFillColor($black_color);
                        $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);

                        if (isset($product_build_value['bundle_children']) && count($product_build_value['bundle_children'])) {
                            foreach ($product_build_value['bundle_children'] as $child) {
                                $this->temp_bundle_count++;
                                //Check need to create new page or not
								$this->checkNewPageNeeded('bundle', $bundle_before);
                                $page = $this->getPage();
								
                                $product = $helper->getProductForStore($child->getProductId(), $storeId);
                                $sku = $child->getSku();
                                $price = $child->getPriceInclTax();
                                if(!$price){
                                    $infoBuyRequest = unserialize($child->getData('product_options'));
                                    $infoBuyRequest = unserialize($infoBuyRequest['bundle_selection_attributes']);
                                    $price = $infoBuyRequest['price'];
                                }
                                $qty = (int)$child->getQtyOrdered();
                                if ($this->packingsheetConfig['product_name_store_view'] == "storeview")
                                    $name = $child->getName();
                                elseif($this->packingsheetConfig['product_name_store_view'] == "specificstore" && $this->packingsheetConfig['product_name_specific_store_id'] != ""){
                                    $_product = $helper->getProductForStore($child->getProductId(), $this->packingsheetConfig['product_name_specific_store_id']);
                                    if ($_product->getData('name')) 
										$name = trim($_product->getData('name'));
                                    if ($name == '') 
										$name = trim($child->getName());
                                }
                                else
                                    $name = $this->getNameDefaultStore($child);

                                $this->y -= $line_height*1.3;
                                $this->options_y_counter += $line_height;
                                if ($isShipment) {
                                    $this->productXInc = 25;
                                    switch ($this->packingsheetConfig['show_qty_options']) {
                                        case 1:
                                            $price_qty = $qty;
                                            $this->productXInc = 0;
                                            break;
                                        case 2:
                                            $price_qty = (int)$this->_shippedItemsQty[$item->getData('product_id')];
                                            $this->productXInc = 25;
                                            break;
                                        case 3:
                                            $price_qty = (int)$this->_shippedItemsQty[$item->getData('product_id')];
                                            $this->productXInc = 25;
                                            break;
                                    }
                                } else {
                                    switch ($this->packingsheetConfig['show_qty_options']) {
                                        case 1:
                                            $price_qty = $qty;
                                            $this->productXInc = 0;
                                            break;
                                        case 2:
                                            $price_qty = (int)$item->getQtyShipped();
                                            $this->productXInc = 25;
                                            break;
                                        case 3:
                                            $price_qty = (int)$item->getQtyShipped();
                                            $this->productXInc = 25;
                                            break;
                                    }
                                }
                                /***get qty string**/
                                $qty_string = $this->getQtyStringBundle($isShipment, $product_build_value, $qty, $invoice_or_pack, $order_invoice_id, $shipment_ids, $storeId);
                                $draw_qty_value = $qty_string;
                                $price_qty = $qty_string;
                                $addon_shift_x = $this->packingsheetConfig['shift_bundle_children_xpos'];
                                //TODO Moo
                                if (($this->packingsheetConfig['tickbox_yn'] == 1) || ($this->packingsheetConfig['tickbox_2_yn'] == 1)) {
                                    $this->getPage()->setLineWidth(0.5);
                                    $this->getPage()->setFillColor($white_color);
                                    $this->getPage()->setLineColor($black_color);
                                    if ($this->packingsheetConfig['tickbox_yn'] == 1) {
                                        if ($this->packingsheetConfig['tickbox_signature_line']){
											$this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['shift_bundle_children_xpos'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 3 + $generalConfig['font_size_body'] / 2 - 3), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['shift_bundle_children_xpos'] + $this->packingsheetConfig['tickbox_width'] * 2 / 3), ($this->y + $this->packingsheetConfig['tickbox_width'] / 3 + $generalConfig['font_size_body'] / 2 - 3));
                                            $this->getPage()->drawLine(($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] - 2)), ($this->y), ($this->packingsheetConfig['tickboxX'] - ($this->packingsheetConfig['tickbox_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y));
                                        }else{
	                                        $this->getPage()->drawRectangle($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['shift_bundle_children_xpos'], ($this->y - $this->packingsheetConfig['tickbox_width'] / 3 + $generalConfig['font_size_body'] / 2 - 3), ($this->packingsheetConfig['tickboxX'] + $this->packingsheetConfig['shift_bundle_children_xpos'] + $this->packingsheetConfig['tickbox_width'] * 2 / 3), ($this->y + $this->packingsheetConfig['tickbox_width'] / 3 + $generalConfig['font_size_body'] / 2 - 3));
                                        }
                                    }
                                    if ($this->packingsheetConfig['tickbox_2_yn'] == 1) {
                                        if ($this->packingsheetConfig['tickbox_2_signature_line']){
                                            $this->getPage()->drawRectangle($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['shift_bundle_children_xpos'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 3 + $generalConfig['font_size_body'] / 2 - 3), ($this->packingsheetConfig['tickbox2X'] + $this->packingsheetConfig['shift_bundle_children_xpos'] + $this->packingsheetConfig['tickbox2_width'] * 2 / 3), ($this->y + $this->packingsheetConfig['tickbox_width'] / 3 + $generalConfig['font_size_body'] / 2 - 3));
                                            $this->getPage()->drawLine(($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] - 2)), ($this->y), ($this->packingsheetConfig['tickbox2X'] - ($this->packingsheetConfig['tickbox2_width'] * ($generalConfig['font_size_body'] / 2))), ($this->y));
                                        }else{
                                            $this->getPage()->drawRectangle($tickboxX_bundle + $this->packingsheetConfig['shift_bundle_children_xpos'], ($this->y - $this->packingsheetConfig['tickbox2_width'] / 2 + $generalConfig['font_size_body'] / 2 - 3), ($tickboxX_bundle + $this->packingsheetConfig['shift_bundle_children_xpos'] + $this->packingsheetConfig['tickbox2_width']), ($this->y + $this->packingsheetConfig['tickbox_width'] / 2 + $generalConfig['font_size_body'] / 2 - 3));
                                        }
                                    }
                                    $this->getPage()->setFillColor($black_color);
                                }
                                if ($this->packingsheetConfig['numbered_product_list_bundle_children_yn'] == 1)
                                    $this->_drawText($this->temp_bundle_count . $numbered_list_suffix, $this->packingsheetConfig['numbered_product_list_bundle_children_X'] +$addon_shift_x, ($this->y));

                                /***************************PRINTING BUNDLE SKU**********************/
                                if ($this->packingsheetConfig['product_sku_yn'] == 1) {
                                    if($generalConfig['font_family_body'] == 'traditional_chinese' || $generalConfig['font_family_body'] == 'simplified_chinese'){
                                        $font_family_body_temp = $generalConfig['font_family_body'];
                                        $generalConfig['font_family_body'] = 'helvetica';
                                    }
                                    $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                    $this->_drawText($sku, $this->packingsheetConfig['product_sku_x_pos'] + $addon_shift_x, $this->y);
                                }

                                /***************************PRINTING BUNDLE BARCODE**********************/
                                if (($this->packingsheetConfig['product_sku_barcode_yn'] != 0) && !$this->hide_bundle_parent_f) {
                                    $after_print_barcode_y = $this->y;
                                    $sku_barcodeY = $this->y - 4;
                                    $barcode = $sku;

                                    if ($this->packingsheetConfig['product_sku_barcode_yn'] == 2)
                                        $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $storeId, 1, true, $child->getProductId());
                                    $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$this->packingsheetConfig['product_sku_barcode_yn'],$this->packingsheetConfig['product_sku_barcode_x_pos'],$sku_barcodeY,$pageConfig['padded_right'],$font_family_barcode,$generalConfig['font_size_barcode_product'],$white_color);
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                }

                                if (($this->packingsheetConfig['product_sku_barcode_2_yn'] != 0) && !$this->hide_bundle_parent_f) {
                                    $after_print_barcode_y = $this->y;
                                    $sku_barcodeY = $this->y - 4;
                                    $barcode = $sku;
                                    if ($this->packingsheetConfig['product_sku_barcode_2_yn'] == 2)
                                        $barcode = $this->getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $storeId, 2, true, $child->getProductId());
                                    $after_print_barcode_y = $this->printProductBarcode($page,$barcode,$barcode_type,$this->packingsheetConfig['product_sku_barcode_yn'],$this->packingsheetConfig['product_sku_barcode_2_x_pos'],$sku_barcodeY,$pageConfig['padded_right'],$font_family_barcode,$generalConfig['font_size_barcode_product'],$white_color);
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                }

                                if($generalConfig['font_family_body'] == 'traditional_chinese' || $generalConfig['font_family_body'] == 'simplified_chinese'){
                                    $font_family_body_temp = $generalConfig['font_family_body'];
                                    $generalConfig['font_family_body'] = 'helvetica';
                                }
                                $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);

                                /*************************** PRINTING BUNDLE CHILD PRODUCT QTY **********************/
                                if ($draw_qty_value <= 1 || $this->packingsheetConfig['product_qty_upsize_yn'] == '0') {
                                    //set font to normal in case product_qty_upsize_yn wasn't set or product qty is 1
                                    $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                }else{
                                    //set font to bold and up one size
                                    $this->_setFont($page, 'bold', ($font_size_options + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                }
                                $qty_draw_width = $this->widthForStringUsingFontSize( $draw_qty_value , $this->getPage()->getFont() , $this->getPage()->getFontSize(), $this->getPage()->getStyle(), $generalConfig['non_standard_characters']);
                                if ($draw_qty_value > 1) {
                                    if ($this->packingsheetConfig['product_qty_upsize_yn'] == '1') { //boxed option
                                        $this->getPage()->setFillColor($black_color);
                                        //draw box
                                        $this->getPage()->drawRectangle(($this->packingsheetConfig['qty_x_pos'] + $addon_shift_x - 2), ($this->y - 2), ($this->packingsheetConfig['qty_x_pos'] + $addon_shift_x + 2 + $qty_draw_width), ($this->y - 2 + $font_size_options));
                                        //set font to bold and up one size with white color
                                        $this->_setFont($page, 'bold', ($font_size_options + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                                    }
                                }
                                //draw qty
                                $this->_drawText($qty_string, $this->packingsheetConfig['qty_x_pos'] + $addon_shift_x, ($this->y - 1));
                                //return font to normal
                                $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);

                                if(isset($font_family_body_temp)){
                                    $generalConfig['font_family_body'] = $font_family_body_temp;
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                }
                                /***************************PRINTING BUNDLE PRICE**********************/
//old code
//                                if ($prices_yn != '0') {
//                                    $bundle_options_part_price_total = ($price_qty * $price);
//                                    $bundle_price_display = $this->formatPriceTxt($order, $price);
//                                    $bundle_price_total_display = $this->formatPriceTxt($order, $bundle_options_part_price_total);
//
//                                    if ($price > 0) 
//										$this->_drawText('(' . $bundle_price_display . ')', $priceEachX, $this->y);
//                                    if ($bundle_options_part_price_total > 0) 
//										$this->_drawText('(' . $bundle_price_total_display . ')', $priceX, $this->y);
//                                }
                                /***************************PRINTING BUNDLE NAME**********************/
                                $after_print_name_bundle_y = $this->y;
                                $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $max_name_length = $next_col_to_product_x - $this->packingsheetConfig['product_name_x_pos'];
                                $line_width_name = $this->parseString($name, $font_temp_shelf2, $font_size_options);
                                $char_width_name = $line_width_name / strlen($name);
                                $max_chars_name = round($max_name_length / $char_width_name);
                                $multiline_name = wordwrap($name, $max_chars_name, "\n");
                                $name_trim = str_trim($name, 'CHARS', $max_chars_name - 3, '...');
                                $token = strtok($multiline_name, "\n");
                                $character_breakpoint_name = stringBreak($name, $max_name_length, $font_size_options, $font_helvetica);
                                $display_name = '';
                                if (strlen($name) > ($character_breakpoint_name + 2))
                                    $display_name = $name_trim;
                                else 
									$display_name = htmlspecialchars_decode($name);

                                $token = strtok($multiline_name, "\n");
                                $multiline_name_array = array();
                                $temp_y = $this->y;
                                if ($this->packingsheetConfig['show_product_name'] == 1 && $after_print_name_y) {
                                    if ($this->packingsheetConfig['product_name_bold_yn'])
                                        $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                    
									if ($this->packingsheetConfig['product_name_trim_yn'])
                                        $this->_drawText($display_name, ($this->packingsheetConfig['product_name_x_pos'] +$addon_shift_x+ $this->productXInc + 2), $temp_y);
                                    else {
                                        if ($token != false) {
                                            while ($token != false) {
                                                $multiline_name_array[] = $token;
                                                $token = strtok("\n");
                                            }

                                            foreach ($multiline_name_array as $name_in_line) {
                                                $this->_drawText($name_in_line, ($this->packingsheetConfig['product_name_x_pos']+$addon_shift_x + $this->productXInc + 2), $temp_y);
                                                $temp_y -= $line_height;
                                            }
                                            $temp_y += $line_height;
                                        }
                                    }
                                    $this->_setFont($page, $generalConfig['font_style_body'], $font_size_options, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                }

                                $this->y = $temp_y;
                                $after_print_name_bundle_y = $this->y; // - $line_height;
                                $this->_setFont($page, 'regular', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                                unset($multiline_name_array);

                                /***************************PRINTING BUNDLE TICKBOX**********************/
                                //draw backordered children bundle
                                if ($this->packingsheetConfig['product_qty_backordered_yn'] == 1) {
                                    $backordered_children_bundle = (int)($child->getData("qty_backordered"));
                                    $this->_drawText($backordered_children_bundle, ($this->packingsheetConfig['product_qty_backordered_x_pos']), $this->y);
                                }
                                if ($this->packingsheetConfig['product_warehouse_yn'] == 1) {
                                    $item_warehouse = $child->getWarehouseTitle();
                                    $this->_drawText($item_warehouse, ($this->packingsheetConfig['prices_warehouseX']), $this->y);
                                }
								
                                /***************************PRINTING BUNDLE SHELVING**********************/
                                $shelving_real = '';
                                $flag_newpage_shelving_real = 0;
                                $shelving_real_attribute = $this->_getConfig('shelving_real', 'shelf', false, $wonder, $storeId);
                                $shelving_real_yn = $this->_getConfig('shelving_real_yn', 'shelf', false, $wonder, $storeId);
                                $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$pageConfig['padded_right'],'shelfX',$addon_shift_x,$storeId,$this->getPageCount(),$this->items_header_top_firstpage,$pageConfig['page_top'],$this->order_number_display);
                                if($after_print_name_bundle_y < $this->y)
                                    $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] / 2;
                                if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                    $this->y = $after_print_barcode_y;

                                /***************************PRINTING BUNDLE SHELVING 2**********************/
                                $shelving_real = '';
                                $flag_newpage_shelving_real = 0;
                                $shelving_real_attribute = $this->_getConfig('shelving', 'shelf', false, $wonder, $storeId);
                                $shelving_real_yn = $this->_getConfig('shelving_yn', 'shelf', false, $wonder, $storeId);
                                $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$pageConfig['padded_right'],'shelf2X',$addon_shift_x,$storeId,$this->getPageCount(),$this->items_header_top_firstpage,$pageConfig['page_top'],$this->order_number_display);
                                if($after_print_name_bundle_y < $this->y)
                                    $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] / 2;
                                if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                    $this->y = $after_print_barcode_y;

                                /***************************PRINTING BUNDLE SHELVING 3**********************/
                                $shelving_real = '';
                                $flag_newpage_shelving_real = 0;
                                $shelving_real_attribute = $this->_getConfig('shelving_2', 'shelf', false, $wonder, $storeId);
                                $shelving_real_yn = $this->_getConfig('shelving_2_yn', 'shelf', false, $wonder, $storeId);
                                $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$pageConfig['padded_right'],'shelf3X',$addon_shift_x,$storeId,$this->getPageCount(),$this->items_header_top_firstpage,$pageConfig['page_top'],$this->order_number_display);
                                if($after_print_name_bundle_y < $this->y)
                                    $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] / 2;
                                if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                    $this->y = $after_print_barcode_y;

                                /***************************PRINTING BUNDLE SHELVING 4**********************/
                                $shelving_real = '';
                                $flag_newpage_shelving_real = 0;
                                $shelving_real_attribute = $this->_getConfig('shelving_3', 'shelf', false, $wonder, $storeId);
                                $shelving_real_yn = $this->_getConfig('shelving_3_yn', 'shelf', false, $wonder, $storeId);
                                $this->printBundleShelving($page, $shelving_real_yn, $shelving_real_attribute ,$product, $child,$this->columns_xpos_array,$pageConfig['padded_right'],'shelf4X',$addon_shift_x,$storeId,$this->getPageCount(),$this->items_header_top_firstpage,$pageConfig['page_top'],$this->order_number_display);
                                if($after_print_name_bundle_y < $this->y)
                                    $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] / 2;
                                if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                                    $this->y = $after_print_barcode_y;

                                /* end of printing bundle shelving */
                                if ($this->packingsheetConfig['doubleline_yn'] == 2)
									$this->y -= 7.5;
                                else
                                    if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
										$this->y -= 3.5;
                                    else
                                        $this->y += 3.5;
                            }
                            $after_print_name_y = $this->y - $generalConfig['font_size_body'];
                            $this->y -= $line_height*1.2;
                        }
                    }
                }

                /************************SET NEXT LINE Y POS TO PRINT THE NEXT ITEM**************/
                if (isset($next_product_line_ypos) && ($next_product_line_ypos > 0))
                    $this->y = ($next_product_line_ypos);
				
                /***************************PRINTING LINE UNDER EACH PRODUCT**********************/
                $this->y += ($generalConfig['font_size_body']);

                if ($this->has_shown_product_image == 1)
                    $this->y -= 15;

                if(isset($after_print_name_bundle_y) && $after_print_name_bundle_y < $this->y)
                    $this->y = $after_print_name_bundle_y - $generalConfig['font_size_body'] - 2;

                if(($flag_new_page_bundle == 0) && ($this->flag_image_newpage == 0))
                {
                    if (isset($after_print_name_y) && ($after_print_name_y < $this->y))
                        $this->y = $after_print_name_y;
                    if (isset($after_print_option_y) && ($after_print_option_y < $this->y))
                        $this->y = $after_print_option_y;
                    if (isset($after_print_sku_y) && ($after_print_sku_y < $this->y))
                        $this->y = $after_print_sku_y;
                    if (isset($after_print_barcode_y) && ($after_print_barcode_y < $this->y))
                        $this->y = $after_print_barcode_y;

                    if (isset($this->yItemPosCombine) && ($this->yItemPosCombine < $this->y))
                        $this->y = $this->yItemPosCombine;
                }

                $background_color_product_temp = trim($this->_getConfig('background_color_product', '#FFFFFF', false, 'general', $storeId));
                $background_color_product = new Zend_Pdf_Color_Html($background_color_product_temp);
                if ($background_color_product_temp != '#FFFFFF') {
                    if ($this->has_shown_product_image == 1)
                        if ($product_images_line_nudge != 0)
							$this->y = $this->y + ($product_images_line_nudge * -1);

                    if ($this->temp_count < count($this->product_build)) {
                        //$this->y += $generalConfig['font_size_body'] - 5;
                        $this->getPage()->setLineWidth(0.5);
                        $this->getPage()->setFillColor($background_color_product);
                        $this->getPage()->setLineColor($background_color_product);
                        $this->getPage()->drawLine($pageConfig['padded_left'], ($this->y + 1), $pageConfig['padded_right'], ($this->y + 1));
                        $this->getPage()->setFillColor($black_color);
                        $this->y -= (($generalConfig['font_size_body']));
                    }

                }

                if ($background_color_vert_product_temp != '#FFFFFF') {
                    if ($this->has_shown_product_image == 1) {
                        if ($product_images_line_nudge != 0) 
							$vert = ($product_images_line_nudge * -1);
                        $this->y -= ($generalConfig['font_size_body'] + 5);
                    } else
                        $vert = ($generalConfig['font_size_body'] * 1.5);

                    $top_y = $this->y;
                    if ($this->product_count == 1) {
                        $top_y += ($generalConfig['font_size_body'] * 1.5);
                        $vert = ($vert * 2);
                    }
                    $this->getPage()->setLineWidth(0.5);
                    $this->getPage()->setFillColor($background_color_product);
                    $this->getPage()->setLineColor($background_color_product);
                    $this->getPage()->drawLine($pageConfig['padded_left'], ($top_y), $pageConfig['padded_left'], ($top_y - $vert));
                    $this->getPage()->drawLine($pageConfig['padded_right'], ($top_y), $pageConfig['padded_right'], ($top_y - $vert));

                    $vert_x_nudge = 5;

                    if ($this->packingsheetConfig['product_images_yn'] == 1)
                        $this->getPage()->drawLine(($this->packingsheetConfig['product_images_x_pos'] - $vert_x_nudge), ($top_y), ($this->packingsheetConfig['product_images_x_pos'] - $vert_x_nudge), ($top_y - $vert));

                    $this->getPage()->drawLine(($this->packingsheetConfig['qty_x_pos'] - $vert_x_nudge), ($top_y), ($this->packingsheetConfig['qty_x_pos'] - $vert_x_nudge), ($top_y - $vert));

                    $this->getPage()->drawLine((($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $first_item_title_shift_items) - $vert_x_nudge), ($top_y), (($this->packingsheetConfig['product_name_x_pos'] + $this->productXInc + $first_item_title_shift_items) - $vert_x_nudge), ($top_y - $vert));

                    if ($this->packingsheetConfig['product_sku_yn'] == 1) 
						$this->getPage()->drawLine(($this->packingsheetConfig['product_sku_x_pos'] - $vert_x_nudge), ($top_y), ($this->packingsheetConfig['product_sku_x_pos'] - $vert_x_nudge), ($top_y - $vert));

                    if ($this->packingsheetConfig['product_options_yn'] == 'yescol')
                        $this->getPage()->drawLine(($optionsX - $vert_x_nudge), ($top_y), ($optionsX - $vert_x_nudge), ($top_y - $vert));

                    if ($shelving_real_yn == 1)
                        $this->getPage()->drawLine(($shelfX - $vert_x_nudge), ($top_y), ($shelfX - $vert_x_nudge), ($top_y - $vert));

                    if ($shelving_yn == 1)
                        $this->getPage()->drawLine(($shelf2X - $vert_x_nudge), ($top_y), ($shelf2X - $vert_x_nudge), ($top_y - $vert));

                    if ($shelving_2_yn == 1)
                        $this->getPage()->drawLine(($shelf3X - $vert_x_nudge), ($top_y), ($shelf3X - $vert_x_nudge), ($top_y - $vert));

                    if ($shelving_3_yn == 1)
                        $this->_drawText(Mage::helper('sales')->__($shelving_3_title), ($shelf4X), $this->y);

                    if($this->packingsheetConfig['show_allowance_yn'] == 1)
                        $this->_drawText(Mage::helper('sales')->__($this->packingsheetConfig['show_allowance_title']), $this->packingsheetConfig['show_allowance_xpos'], $this->y);

                    $this->getPage()->setFillColor($black_color);
                }

                /***************************DOUBLE LINE SPACING**********************/
                if (isset($after_print_ticbox1) && $after_print_ticbox1 < $this->y && $this->flag_image_newpage < 1)
                    $this->y = $after_print_ticbox1 - $generalConfig['font_size_body'] - 10;

                if (isset($after_print_ticbox2) && ($after_print_ticbox2 < $this->y) && ($this->flag_image_newpage < 1))
                    $this->y = $after_print_ticbox2 - $generalConfig['font_size_body'] - 10;

                if ($this->packingsheetConfig['doubleline_yn'] == 2)
					$this->y -= 15;
                else
                    if ($this->packingsheetConfig['doubleline_yn'] == 1.5)
						$this->y -= 7.5;
                    else
                        if ($this->packingsheetConfig['doubleline_yn'] == 3)
							$this->y -= 20 ;
                        else 
							$this->y -= 3.5;
            }

            unset($after_print_option_y);
            unset($after_print_ticbox1);
            unset($after_print_ticbox2);
            unset($after_print_name_bundle_y);
            unset($after_print_barcode_y);
            unset($after_print_name_y);
            unset($after_print_sku_y);
            unset($next_product_line_ypos);
            $this->hide_bundle_parent_f = false;
        }
    }

    public function printingShelving($product_build_value) {
        /***************************PRINTING SHELVING**********************/
        $page = $this->getPage();
        $helper = Mage::helper('pickpack');
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $wonder = $this->getWonder();

        $line_height = (1.15 * $generalConfig['font_size_body']);

        $shelfX = $this->_getConfig('pricesN_shelfX', 10, false, $wonder, $storeId);

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '') $shelving_real_yn = 0;
        $shelving_real_title = trim($this->_getConfig('shelving_real_title', '', false, $wonder, $storeId));
        $shelving_real_title = str_ireplace(array('blank', "'"), '', $shelving_real_title);

        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '')
				$shelving_yn = 0;
            if ($shelving_yn == 0)
				$shelving_attribute = null;
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
        } else
            $combine_custom_attribute_yn = 0;

        $this->y = $this->yItemPos;
        $this->yItemPosCombine = $this->y;
        $this->page_shelving_1 = $this->getPdf()->getPageCount();
        $line_height = (1.15 * $generalConfig['font_size_body']);
        $this->shelving_y_pos = $this->y;

        if (isset($product_build_value) && ($product_build_value['shelving_real'] != '') && !$this->hide_bundle_parent_f) {
            $print_star_shelving = 0;
            $shelving_real = $product_build_value['shelving_real'];

            $this->flag_print_shelving_1 = true;
            $shelving_real_star_specific_value_yn = $this->_getConfig('shelving_real_star_specific_value_yn', 0, false, $wonder, $storeId);
            $shelving_real_star_specific_value_filter = explode(',',trim($this->_getConfig('shelving_real_star_specific_value_filter', '', false, $wonder, $storeId)));

            if($shelving_real_star_specific_value_yn !== 0)
            {
                if(is_array($shelving_real_star_specific_value_filter))
                {
                    foreach($shelving_real_star_specific_value_filter as $text_filter)
                    {
                        if(!empty($text_filter) && strpos(strtolower($shelving_real),strtolower($text_filter)) !== FALSE)
                        {
                            $print_star_shelving = 1;
                            break;
                        }
                    }
                }
            }
            if($shelving_real_star_specific_value_yn && ($print_star_shelving == 1))
            {
                if($shelving_real_star_specific_value_yn == 1)
                    $shelving_real_image_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/shelving_real_image', $storeId);
                elseif($shelving_real_star_specific_value_yn == 'alert_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-alert.png';
                elseif($shelving_real_star_specific_value_yn == 'drink_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-drink.png';
                elseif($shelving_real_star_specific_value_yn == '18_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-18.png';
                elseif($shelving_real_star_specific_value_yn == '21_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-21.png';

                if (isset($shelving_real_image_filename) && $shelving_real_image_filename) {
                    $shelving_real_image_path = Mage::getBaseDir('media') . '/moogento/pickpack/customimage/' . $shelving_real_image_filename;
                    $dirImg = $shelving_real_image_path;
                    $imageObj = new Varien_Image($dirImg);
                    $shelving_image_width = $imageObj->getOriginalWidth()/300*72;
                    $shelving_image_height = $imageObj->getOriginalHeight()/300*72;

                    $image_ext = '';
                    $image_ext = substr($shelving_real_image_path, strrpos($shelving_real_image_path, '.') + 1);
                    if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($shelving_real_image_path))) {
                        $shelving_real_image = Zend_Pdf_Image::imageWithPath($shelving_real_image_path);
                        $this->getPage()->drawImage($shelving_real_image, $shelfX, $this->shelving_y_pos - $shelving_image_height/4 , $shelfX+$shelving_image_width, $this->shelving_y_pos + $shelving_image_height*.75);
                    }
                    unset($shelving_real_star_specific_value_yn);
                    unset($shelving_real_star_specific_value_filter);
                    unset($shelving_real_image_filename);
                }
            }
            else
            {
                if (is_array($shelving_real)) $shelving_real = implode(',', $shelving_real);
                $shelving_real = trim($shelving_real);
                if($this->packingsheetConfig['custom_round_yn'] != '0') {
                    $shelving_real = Mage::helper('pickpack/number')->roundNumber($shelving_real, $this->packingsheetConfig['custom_round_yn']);
                }
                $next_col_to_shelving_real = getPrevNext2($this->columns_xpos_array, 'shelfX', 'next', $pageConfig['padded_right'] - $pageConfig['page_pad_leftright']);
                $max_shelving_real_length = ($next_col_to_shelving_real - $shelfX);
                $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $line_width_shelving_real = $this->parseString('1234567890', $font_temp_shelf2, ($generalConfig['font_size_body']));
                $char_width_shelving_real = $line_width_shelving_real / 11;
                $max_chars_shelving_real = round($max_shelving_real_length / $char_width_shelving_real);
                $shelving_real = wordwrap($shelving_real, $max_chars_shelving_real, "\n");
                $shelving_real_trim = str_trim($shelving_real, 'WORDS', $max_chars_shelving_real - 3, '...');
                $token = strtok($shelving_real, "\n");
                $msg_line_count = 2;
                if ($token != false) {
                    while ($token != false) {
                        $shelving_real_array[] = strip_tags($token);
                        $msg_line_count++;
                        $token = strtok("\n");
                    }
                } else
                    $shelving_real_array[] = $shelving_real;
                if ($this->_getConfig('shelving_real_trim_content_yn', 0, false, $wonder, $storeId)) {
                    if($combine_custom_attribute_yn == 1)
                        $this->custom_attribute_combined_array[$shelving_real_title] = $shelving_real_trim;
                    else{
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                        $this->_drawText($shelving_real_trim, $shelfX, $this->y);
                        $this->y -= $line_height;
                    }
                } else {
                    if($combine_custom_attribute_yn == 1)
                        $this->custom_attribute_combined_array[$shelving_real_title] = $shelving_real;
                    else{
                        $count_shelving_row = count($shelving_real_array);
                        foreach ($shelving_real_array as $shelving_real_line) {
							$this->checkNewPageNeeded('shelving');
                            $page = $this->getPage();
                            
                            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                            $this->_drawText($shelving_real_line, $shelfX, $this->y);
                            if($count_shelving_row >0)
                                $this->y -= $line_height;
                        }
                    }
                }
                unset($shelving_real_array);
                unset($shelving_real);
            }
        }

        //Goto before print shelving.
        if ($this->flag_image_newpage) {
            if ($this->packingsheetConfig['product_images_yn'] == 1)
                if (($this->page_count_shelving_1 < 1) || (($this->page_count_shelving_1 == 1) && ($this->y > $this->after_print_image_y_newpage)))
                    $this->y = $this->after_print_image_y_newpage;
        } elseif (($this->packingsheetConfig['product_images_yn'] == 1) && ($this->y > $this->after_print_image_y) && ($this->page_count_shelving_1 < 1))
                $this->y = $this->after_print_image_y - $this->packingsheetConfig['product_images_y_nudge']; //- 15;
		
        $this->max_y_1 = $this->y;
    }

    public function printingShelving2($product_build_value) {
        /***************************PRINTING SHELVING 2**********************/
        $page = $this->getPage();
        $helper = Mage::helper('pickpack');
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $wonder = $this->getWonder();

        $line_height = (1.15 * $generalConfig['font_size_body']);

        $shelf2X = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '') 
			$shelving_real_yn = 0;

        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '') 
				$shelving_yn = 0;
            if ($shelving_yn == 0) 
				$shelving_attribute = null;
            $shelving_title = $this->_getConfig('shelving_title', '', false, $wonder, $storeId);
            $shelving_title = trim(str_ireplace(array('blank', "'"), '', $shelving_title));
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
        } else {
            $shelving_yn = 0;
            $combine_custom_attribute_yn = 0;
        }

        $page_count_shelving_2 = 0;
        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);

        if (isset($product_build_value['shelving']) && ($product_build_value['shelving'] != '') && !$this->hide_bundle_parent_f) {
            $print_star_shelving = 0;
            $shelving_real = $product_build_value['shelving'];
            if($this->packingsheetConfig['custom_round_yn'] != '0')
                $shelving_real = Mage::helper('pickpack/number')->roundNumber($shelving_real, $this->packingsheetConfig['custom_round_yn']);

            $shelving_real_star_specific_value_yn = $this->_getConfig('shelving_2_star_specific_value_yn', 0, false, $wonder, $storeId);
            $shelving_real_star_specific_value_filter = explode(',',trim($this->_getConfig('shelving_2_star_specific_value_filter', '', false, $wonder, $storeId)));

            if($shelving_real_star_specific_value_yn !== 0)
            {
                if(is_array($shelving_real_star_specific_value_filter))
                {
                    foreach($shelving_real_star_specific_value_filter as $text_filter)
                    {
                        if( !empty($text_filter) && strpos(strtolower($shelving_real),strtolower($text_filter)) !== FALSE)
                        {
                            $print_star_shelving = 1;
                            break;
                        }
                    }
                }
            }

            if($print_star_shelving == 1)
            {
                if($shelving_real_star_specific_value_yn == 1)
                    $shelving_real_image_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/shelving_2_image', $storeId);
                elseif($shelving_real_star_specific_value_yn == 'alert_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-alert.png';
                elseif($shelving_real_star_specific_value_yn == 'drink_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-drink.png';
                elseif($shelving_real_star_specific_value_yn == '18_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-18.png';
                elseif($shelving_real_star_specific_value_yn == '21_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-21.png';

                if ($shelving_real_image_filename) {
                    $shelving_real_image_path = Mage::getBaseDir('media') . '/moogento/pickpack/customimage/' . $shelving_real_image_filename;
                    $dirImg = $shelving_real_image_path;
                    $imageObj = new Varien_Image($dirImg);
                    $shelving_image_width = $imageObj->getOriginalWidth()/300*72;
                    $shelving_image_height = $imageObj->getOriginalHeight()/300*72;

                    $image_ext = '';
                    $image_ext = substr($shelving_real_image_path, strrpos($shelving_real_image_path, '.') + 1);
                    if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($shelving_real_image_path))) {
                        $shelving_real_image = Zend_Pdf_Image::imageWithPath($shelving_real_image_path);
                        $this->getPage()->drawImage($shelving_real_image, $shelf2X, $this->shelving_y_pos - $shelving_image_height/4 , $shelf2X+$shelving_image_width, $this->shelving_y_pos + $shelving_image_height*.75);
                    }
                }
                unset($shelving_real_star_specific_value_yn);
                unset($shelving_real_star_specific_value_filter);
                unset($shelving_real_image_filename);

            }
            else
            {
                if ($this->flag_print_shelving_1) {
                    $this->y = $this->yItemPos;
                    $page = $this->getPdf()->getPage($this->page_shelving_1 - 1);
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 4), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                }
                $shelving = $product_build_value['shelving'];
                if (is_array($shelving)) $shelving = implode(',', $shelving);
                $shelving = trim($shelving);
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);

                $next_col_to_shelving = getPrevNext2($this->columns_xpos_array, 'shelf2X', 'next', $pageConfig['padded_right'] - $pageConfig['page_pad_leftright']);
                $max_shelving_length = ($next_col_to_shelving - $shelf2X);
                $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $line_width_shelving = $this->parseString('1234567890', $font_temp_shelf2, ($generalConfig['font_size_body']));
                $char_width_shelving = $line_width_shelving / 8.5;
                $max_chars_shelving = round($max_shelving_length / $char_width_shelving);
                $shelving = wordwrap($shelving, $max_chars_shelving, "\n");
                $shelving_trim = strip_tags(str_trim($shelving, 'WORDS', $max_chars_shelving - 3, '...'));
                $token = strtok($shelving, "\n");
                $msg_line_count = 2;
                if ($token != false) {
                    while ($token != false) {
                        $shelving_array[] = strip_tags($token);
                        $msg_line_count++;
                        $token = strtok("\n");
                    }
                } else
                    $shelving_array[] = $shelving;
                if ($this->_getConfig('shelving_trim_content_yn', 0, false, $wonder, $storeId)) {
                    if($combine_custom_attribute_yn == 1)
                        $this->custom_attribute_combined_array[$shelving_title] = $shelving_trim;
                    else{
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                        $this->_drawText($shelving_trim, $shelf2X, $this->y);
                        $this->y -= $line_height;
                    }
                } else {
                    if($combine_custom_attribute_yn == 1)
                        $this->custom_attribute_combined_array[$shelving_title] = $shelving;
                    else
                        foreach ($shelving_array as $shelving_line) {
                            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                            $this->_drawText($shelving_line, $shelf2X, $this->y);
                            //$this->y -= $line_height;
                            $this->checkNewPageNeeded();
                            $page = $this->getPage();
                        }
                }
                unset($shelving_array);
                unset($shelving);
            }

        }
        $max_y_2 = $this->y;

        $this->getPdf()->getPage($this->getPdf()->getPageCount() - 1);
        if ($this->flag_image_newpage && ($this->page_count_shelving_1 < 1) && ($page_count_shelving_2 < 1))
            $this->y = $this->after_print_image_y_newpage; //- 15;
        elseif ($page_count_shelving_2 > $this->page_count_shelving_1)
                $this->y = $max_y_2;
        else
			$this->y = $this->max_y_1;
    }

    public function printingShelving3($product_build_value) {
        /***************************PRINTING SHELVING 3**********************/
        $page = $this->getPage();
        $helper = Mage::helper('pickpack');
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $wonder = $this->getWonder();

        $line_height = (1.15 * $generalConfig['font_size_body']);

        $shelf2X = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
        $shelf3X = $this->_getConfig('pricesN_shelf3X', 10, false, $wonder, $storeId);

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '') $shelving_real_yn = 0;

        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '') 
				$shelving_yn = 0;
            if ($shelving_yn == 0) 
				$shelving_attribute = null;
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
        } else {
            $shelving_yn = 0;
            $combine_custom_attribute_yn = 0;
        }
        if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
            $shelving_2_title = $this->_getConfig('shelving_2_title', '', false, $wonder, $storeId);
            $shelving_2_title = trim(str_ireplace(array('blank', "'"), '', $shelving_2_title));
        }

        if (isset($product_build_value['shelving2']) && ($product_build_value['shelving2'] != '') && !$this->hide_bundle_parent_f) {
            $print_star_shelving = 0;
            $shelving_real = $product_build_value['shelving3'];
            if($this->packingsheetConfig['custom_round_yn'] != '0')
                $shelving_real = Mage::helper('pickpack/number')->roundNumber($shelving_real, $this->packingsheetConfig['custom_round_yn']);

            $shelving_real_star_specific_value_yn = $this->_getConfig('shelving_3_star_specific_value_yn', 0, false, $wonder, $storeId);
            $shelving_real_star_specific_value_filter = explode(',',trim($this->_getConfig('shelving_3_star_specific_value_filter', '', false, $wonder, $storeId)));

            if($shelving_real_star_specific_value_yn !== 0)
            {
                if(is_array($shelving_real_star_specific_value_filter))
                {
                    foreach($shelving_real_star_specific_value_filter as $text_filter)
                    {
                        if( !empty($text_filter) && strpos(strtolower($shelving_real),strtolower($text_filter)) !== FALSE)
                        {
                            $print_star_shelving = 1;
                            break;
                        }
                    }
                }
            }

            if($print_star_shelving == 1)
            {
                if($shelving_real_star_specific_value_yn == 1)
                    $shelving_real_image_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/shelving_3_image', $storeId);
                elseif($shelving_real_star_specific_value_yn == 'alert_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-alert.png';
                elseif($shelving_real_star_specific_value_yn == 'drink_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-drink.png';
                elseif($shelving_real_star_specific_value_yn == '18_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-18.png';
                elseif($shelving_real_star_specific_value_yn == '21_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-21.png';

                if ($shelving_real_image_filename) {
                    $shelving_real_image_path = Mage::getBaseDir('media') . '/moogento/pickpack/customimage/' . $shelving_real_image_filename;
                    $dirImg = $shelving_real_image_path;
                    $imageObj = new Varien_Image($dirImg);
                    $shelving_image_width = $imageObj->getOriginalWidth()/300*72;
                    $shelving_image_height = $imageObj->getOriginalHeight()/300*72;

                    $image_ext = '';
                    $image_ext = substr($shelving_real_image_path, strrpos($shelving_real_image_path, '.') + 1);
                    if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($shelving_real_image_path))) {
                        $shelving_real_image = Zend_Pdf_Image::imageWithPath($shelving_real_image_path);
                        $this->getPage()->drawImage($shelving_real_image, $shelf3X, $this->shelving_y_pos - $shelving_image_height/4 , $shelf3X+$shelving_image_width, $this->shelving_y_pos + $shelving_image_height*.75);
                    }
                }
                unset($shelving_real_star_specific_value_yn);
                unset($shelving_real_star_specific_value_filter);
                unset($shelving_real_image_filename);
            }
            else
            {
                $this->y = $this->yItemPos;

                $shelving_2 = $product_build_value['shelving2'];

                if (is_array($shelving_2)) $shelving_2 = implode(',', $shelving_2);
                $shelving_2 = trim($shelving_2);
                $next_col_to_shelving_2 = getPrevNext2($this->columns_xpos_array, 'shelf3X', 'next', $pageConfig['padded_right'] - $pageConfig['page_pad_leftright']);
                $max_shelving_2_length = ($next_col_to_shelving_2 - $shelf2X);
                $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $line_width_shelving_2 = $this->parseString('1234567890', $font_temp_shelf2, ($generalConfig['font_size_body']));
                $char_width_shelving_2 = $line_width_shelving_2 / 11;
                $max_chars_shelving_2 = round($max_shelving_2_length / $char_width_shelving_2);
                $shelving_2 = wordwrap($shelving_2, $max_chars_shelving_2, "\n");
                $shelving_2_trim = str_trim($shelving_2, 'WORDS', $max_chars_shelving_2 - 3, '...');
                $token = strtok($shelving_2, "\n");
                $msg_line_count = 2;
                if ($token != false) {
                    while ($token != false) {
                        $shelving_2_array[] = $token;
                        $msg_line_count++;
                        $token = strtok("\n");
                    }
                } else
                    $shelving_2_array[] = $shelving_2;
                if ($this->_getConfig('shelving_2_trim_content_yn', 0, false, $wonder, $storeId)) {
                    if($combine_custom_attribute_yn == 1)
                        $this->custom_attribute_combined_array[$shelving_2_title] = $shelving_2_trim;
                    else{
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                        $this->_drawText($shelving_2_trim, $shelf3X, $this->y);
                        $this->y -= $line_height;
                    }
                } else {
                    if($combine_custom_attribute_yn == 1)
                        $this->custom_attribute_combined_array[$shelving_2_title] = $shelving_2;
                    else
                        foreach ($shelving_2_array as $shelving_2_line) {
                            $this->_drawText($shelving_2_line, $shelf3X, $this->y);
                            $this->checkNewPageNeeded();
                            $page = $this->getPage();
                        }
                }
                unset($shelving_2_array);
                unset($shelving_2);
            }
        }
    }

    public function printingShelving4($product_build_value) {
        /***************************PRINTING SHELVING 4**********************/
        $page = $this->getPage();
        $helper = Mage::helper('pickpack');
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $wonder = $this->getWonder();

        $line_height = (1.15 * $generalConfig['font_size_body']);

        $shelf2X = $this->_getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
        $shelf4X = $this->_getConfig('shelving_3_Xpos', 10, false, $wonder, $storeId);

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
        $shelving_real_attribute = trim($this->_getConfig('shelving_real', '', false, $wonder, $storeId));
        if ($shelving_real_attribute == '') $shelving_real_yn = 0;

        if ($shelving_real_yn == 1) {
            $shelving_yn = $this->_getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $shelving_attribute = trim($this->_getConfig('shelving', '', false, $wonder, $storeId));
            if ($shelving_attribute == '') $shelving_yn = 0;
            if ($shelving_yn == 0) $shelving_attribute = null;
            $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
        } else {
            $shelving_yn = 0;
            $combine_custom_attribute_yn = 0;
        }
        if (($shelving_real_yn == 1) && ($shelving_yn == 1)) {
            $shelving_2_yn = $this->_getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
            $shelving_2_attribute = trim($this->_getConfig('shelving_2', '', false, $wonder, $storeId));
            if ($shelving_2_attribute == '') 
				$shelving_2_yn = 0;
        } else {
            $shelving_2_yn = 0;
        }
        if (($shelving_real_yn == 1) && ($shelving_yn == 1) && ($shelving_2_yn == 1)) {
            $shelving_3_attribute = trim($this->_getConfig('shelving_3', '', false, $wonder, $storeId));
            if ($shelving_3_attribute == '') 
				$shelving_3_yn = 0;
            $shelving_3_title = $this->_getConfig('shelving_3_title', '', false, $wonder, $storeId);
            $shelving_3_title = trim(str_ireplace(array('blank', "'"), '', $shelving_3_title));
        }

        if (isset($product_build_value['shelving3']) && ($product_build_value['shelving3'] != '') && !$this->hide_bundle_parent_f) {
            $print_star_shelving = 0;
            $shelving_real = trim($product_build_value['shelving3']);
            if($this->packingsheetConfig['custom_round_yn'] != '0')
                $shelving_real = Mage::helper('pickpack/number')->roundNumber($shelving_real, $this->packingsheetConfig['custom_round_yn']);

            $shelving_real_star_specific_value_yn = $this->_getConfig('shelving_4_star_specific_value_yn', 0, false, $wonder, $storeId);
            $shelving_real_star_specific_value_filter = explode(',',trim($this->_getConfig('shelving_4_star_specific_value_filter', '', false, $wonder, $storeId)));
            if($shelving_real_star_specific_value_yn !== 0)
            {

                if(is_array($shelving_real_star_specific_value_filter))
                {
                    foreach($shelving_real_star_specific_value_filter as $text_filter)
                    {
                        if( !empty($text_filter) && strpos(strtolower($shelving_real),trim(strtolower($text_filter))) !== FALSE)
                        {
                            $print_star_shelving = 1;
                            break;
                        }
                    }
                }
            }

            if($print_star_shelving == 1)
            {
                if($shelving_real_star_specific_value_yn == 1)
                    $shelving_real_image_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/shelving_4_image', $storeId);
                elseif($shelving_real_star_specific_value_yn == 'alert_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-alert.png';
                elseif($shelving_real_star_specific_value_yn == 'drink_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-drink.png';
                elseif($shelving_real_star_specific_value_yn == '18_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-18.png';
                elseif($shelving_real_star_specific_value_yn == '21_flag')
                    $shelving_real_image_filename = 'default/attribute-flag-21.png';

                if ($shelving_real_image_filename) {
                    $shelving_real_image_path = Mage::getBaseDir('media') . '/moogento/pickpack/customimage/' . $shelving_real_image_filename;
                    $dirImg = $shelving_real_image_path;
                    $imageObj = new Varien_Image($dirImg);
                    $shelving_image_width = $imageObj->getOriginalWidth()/300*72;
                    $shelving_image_height = $imageObj->getOriginalHeight()/300*72;

                    $image_ext = '';
                    $image_ext = substr($shelving_real_image_path, strrpos($shelving_real_image_path, '.') + 1);
                    if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($shelving_real_image_path))) {
                        $shelving_real_image = Zend_Pdf_Image::imageWithPath($shelving_real_image_path);
                        $this->getPage()->drawImage($shelving_real_image, $shelf4X, $this->shelving_y_pos, $shelf4X+$shelving_image_width, $this->shelving_y_pos + $shelving_image_height);
                    }
                }
                unset($shelving_real_star_specific_value_yn);
                unset($shelving_real_star_specific_value_filter);
                unset($shelving_real_image_filename);
            }
            else
            {
                $this->y = $this->yItemPos;
                $shelving_3 = $product_build_value['shelving3'];
                if (is_array($shelving_3)) 
					$shelving_3 = implode(',', $shelving_3);
                $shelving_3 = trim($shelving_3);
                $next_col_to_shelving_3 = getPrevNext2($this->columns_xpos_array, 'shelf3X', 'next', $pageConfig['padded_right'] - $pageConfig['page_pad_leftright']);
                $max_shelving_3_length = ($next_col_to_shelving_3 - $shelf2X);
                $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $line_width_shelving_3 = $this->parseString('1234567890', $font_temp_shelf2, ($generalConfig['font_size_body']));
                $char_width_shelving_3 = $line_width_shelving_3 / 11;
                $max_chars_shelving_3 = round($max_shelving_3_length / $char_width_shelving_3);
                $shelving_3 = wordwrap($shelving_3, $max_chars_shelving_3, "\n");
                $shelving_3_trim = str_trim($shelving_3, 'WORDS', $max_chars_shelving_3 - 3, '...');
                $token = strtok($shelving_3, "\n");
                $msg_line_count = 2;
                if ($token != false) {
                    while ($token != false) {
                        $shelving_3_array[] = $token;
                        $msg_line_count++;
                        $token = strtok("\n");
                    }
                } else
                    $shelving_3_array[] = $shelving_3;
                if ($this->_getConfig('shelving_3_trim_content_yn', 0, false, $wonder, $storeId)) {
                    if($combine_custom_attribute_yn == 1)
                        $this->custom_attribute_combined_array[$shelving_3_title] = $shelving_3_trim;
                    else{
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $this->fontColorBodyItem);
                        $this->_drawText($shelving_3_trim, $shelf4X, $this->y);
                        $this->y -= $line_height;
                    }
                } else {
                    if($combine_custom_attribute_yn == 1)
                        $this->custom_attribute_combined_array[$shelving_3_title] = $shelving_3;
                    else
                        foreach ($shelving_3_array as $shelving_3_line) {
                            $this->_drawText($shelving_3_line, $shelf4X, $this->y);
                            $this->checkNewPageNeeded();
                            $page = $this->getPage();
                        }
                }

                unset($shelving_3_array);
                unset($shelving_3);
            }
        }
    }

    protected function getEbayOption($order, $sku, $productId) {
        $ebay_option_item = array();
        $collection_order = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
        $collection_order->addFieldToFilter('magento_order_id',$order->getData('entity_id'));
        $order_id = '';
        foreach($collection_order as $ebay_order){
            $order_id = $ebay_order->getData("order_id");
        }
        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Order_Item')
            ->addFieldToFilter('order_id', $order_id)
            ->addFieldToFilter('sku', $sku);
        $items = $collection->getData();
        foreach($items as $item){
            if($productId != $item['product_id'])
                continue;
            $variation_details = json_decode($item['variation_details'], true);
            if(isset($variation_details["options"])){
                $options = $variation_details["options"];
                foreach($options as $key=>$value){
                    $option_array['label'] = $key;
                    $option_array['value'] = $value;
                }
                $ebay_option_item[] = $option_array;
                unset($options);
                unset($variation_details);
				$variation_details = null;
                unset($option_array);
                unset($item);
                unset($key);
                unset($value);
            }
        }
        return $ebay_option_item;
    }

    protected function createArraySort($sort_packing,$product_build, $sku,$product_id) {
        if ($sort_packing != 'none' && $sort_packing != '') {
            $product_build[$sku][$sort_packing] = '';
            $attributeName = $sort_packing;

            if ($attributeName == 'Mcategory')
                $product_build[$sku][$sort_packing] = $product_build['%category%']; //$category_label;
            elseif ($sort_packing == 'sku')
                $product_build[$sku][$sort_packing] = $sku;
            else {
                $product = Mage::helper('pickpack')->getProduct($product_id);
                if ($product->getData($attributeName)) {
                    $attributeValue = $product->getData($attributeName);
                    $attribute = $product->getResource()->getAttribute($attributeName);
                    if ($attribute->usesSource())
                        $return_value = $product->getAttributeText($attributeName, $attributeValue);
                    else
                        $return_value = $attributeValue;

                    $product_build[$sku][$sort_packing] = $return_value;
                }
            }
            unset($attributeName);
            unset($attribute);
            unset($attributeOptions);
            return $product_build[$sku][$sort_packing];
        }
    }

    private function skuWordwrap($minDistanceSku, $font_size_sku, $sku_print) {
        $maxWidthPage = $minDistanceSku - 10;
        $chunks = array();
        if (strlen($sku_print) > 0) {
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $line_width = $this->parseString($sku_print, $font_temp, $font_size_sku);
            $char_width = $line_width / strlen($sku_print);
            $max_chars = round($maxWidthPage / $char_width);

            if ($this->packingsheetConfig['product_sku_trim_yn']){
                $max_chars -= 3;
            }

            if (strlen($sku_print) > $max_chars)
                $chunks = str_split($sku_print, $max_chars);
            else
                $chunks[] = $sku_print;

            //return trim sku
            if ($this->packingsheetConfig['product_sku_trim_yn'])
                if (count($chunks)>1)
                    return array($chunks[0].'...');
        }

        return $chunks;
    }

    protected function printingImage($product_build_value) {
        /***************************PRINTING IMAGE**********************/
        $page = $this->getPage();
        $helper = Mage::helper('pickpack');
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $wonder = $this->getWonder();

        $product_images_maxdimensions = $this->getProductImagesMaxDimensions();
        $product_sku = $this->getProductSku($product_build_value['item']);

        $product_images_line_nudge = $this->packingsheetConfig['product_images_line_nudge'];
        if ($product_images_line_nudge > 0) 
			$product_images_line_nudge = -abs($product_images_line_nudge);
        if ($this->packingsheetConfig['product_images_yn'] == 0) 
			$product_images_line_nudge = 0;

        $product_images_border_color_temp = strtoupper($this->packingsheetConfig['product_images_border_color']);
        $product_images_border_color = new Zend_Pdf_Color_Html($product_images_border_color_temp);

        $black_color = Mage::helper('pickpack/config_color')->getPdfColor('black_color');

        $product_sku_md5 = $product_build_value['product_sku_md5'];

        $this->y = $this->yItemPos;
        $this->before_print_image_y = $this->y;
        $this->befor_print_image_y_newpage = $this->y;
        $this->after_print_image_y = $this->y;
        $this->after_print_image_y_newpage = $this->y;

        $resize_x = null;
        $resize_y = null;
        if ($this->packingsheetConfig['product_images_yn'] == 1 && $this->sku_ProductId[$product_sku] != '' && !$this->hide_bundle_parent_f) {
            $product_id = $product_build_value['product_id'];
            $product = $product_build_value['product'];
            if ($this->packingsheetConfig['product_parent_image_yn'] == 1) {
                $product_id = Mage::helper("pickpack")->getParentProId($product_id);
                $product = $_newProduct = $helper->getProduct($product_id);;
            }
            $product_images_source_res = $helper->getSourceImageRes($this->packingsheetConfig['product_images_source'], $product);
            $img_demension = $helper->getWidthHeightImage($product, $product_images_source_res, $product_images_maxdimensions);
            if (is_array($img_demension) && count($img_demension)) {
                $this->img_width = $img_demension[0];
                $this->img_height = $img_demension[1];
            }
            $imagePaths = $helper->getImagePaths($product, $this->packingsheetConfig['product_images_source'], $product_images_maxdimensions);
            $x1 = $this->packingsheetConfig['product_images_x_pos'];
            $y1 = ($this->y - $this->img_height + $this->packingsheetConfig['product_images_y_nudge']);
            $x2 = ($this->packingsheetConfig['product_images_x_pos'] + $this->img_width);
            $y2 = ($this->y + $this->packingsheetConfig['product_images_y_nudge']);
            $this->before_print_image_y = $y1;
            $image_x_addon = 0;
            $image_x_addon_2 = 0;
            $page_prev = $page;
            $count = 1;
            foreach ($imagePaths as $imagePath) {
                $imagePath = trim($imagePath);
                if ($imagePath != '') {
                    $image_x_addon += ($count * ($this->img_width + 10)); // shift the 2nd image over
                    $image_x_addon_2 += (($count - 1) * ($this->img_width + 10)); // shift the 2nd image over
                    $count++;
                    $media_path = Mage::getBaseDir('media');
                    $image_url = $imagePath;
                    $image_url_after_media_path_with_media = strstr($image_url, '/media/');
                    $image_url_after_media_path = strstr_after($image_url, '/media/');

                    $final_image_path = $media_path . '/' . $image_url_after_media_path;
                    $final_image_path2 = $media_path . '/' . $image_url_after_media_path;
                    $image_ext = '';
                    $image_part = explode('.', $image_url_after_media_path);
                    $image_ext = array_pop($image_part);
                    $image_ext = strtolower($image_ext);
                    if (($image_ext != 'jpg') && ($image_ext != 'JPG') && ($image_ext != 'jpeg') && ($image_ext != 'png') && ($image_ext != 'PNG')) 
						continue;

                    //Check to print image in current page or in a new page.
					if($this->checkNewPageNeeded() === true) {
                        $page = $this->getPage();
						$this->flag_image_newpage = 1;
						
                        $x1 = ($this->packingsheetConfig['product_images_x_pos']);
                        $y1 = $this->y - $this->img_height - 5 + $this->packingsheetConfig['product_images_y_nudge'];
                        $x2 = ($this->packingsheetConfig['product_images_x_pos'] + $this->img_width);
                        $y2 = $this->y - 5 + $this->packingsheetConfig['product_images_y_nudge'];
                        $this->befor_print_image_y_newpage = $y1;
					}
				   
                    if ($product_images_border_color_temp != '#FFFFFF') {
                        $this->getPage()->setLineWidth(0.5);
                        $this->getPage()->setFillColor($product_images_border_color);
                        $this->getPage()->setLineColor($product_images_border_color);
                        $this->getPage()->drawRectangle(($x1 - 1 + $image_x_addon_2), ($y1 - 1 +7), ($x2 + 1 + $image_x_addon_2), ($y2 + 1 +7));
                        $this->getPage()->setFillColor($black_color);
                    }
                    try {
                        $image_source = $final_image_path2;
                        $io = new Varien_Io_File();
                        $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
                        $ext = substr($image_source, strrpos($image_source, '.') + 1);
                        if($ext != 'jpg' && $ext != 'JPG')
                        {
                            $image_zebra = new Zebra_Image();
                            $image_zebra->source_path = $final_image_path2;
                            $image_zebra->target_path = $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$product_sku_md5.'.jpeg';;
                            if(!(file_exists($image_zebra->target_path)))
                            {
                                $size_1 = $this->img_width*300/72;
                                $size_2 = $this->img_height*300/72;
                                if (!$image_zebra->resize($size_1, $size_2, ZEBRA_IMAGE_NOT_BOXED, -1))
                                    show_error($image_zebra->error, $image_zebra->source_path, $image_zebra->target_path);

                            }
                            $final_image_path = $image_target;
                        }
                        else
                        {
                            $ext = 'jpeg';
                            $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$product_sku_md5.'.'.$ext;

                            if(!(file_exists($image_target)))
                            {
                                $size_1 = $this->img_width*300/72;
                                $size_2 = $this->img_height*300/72;
                                $image_simple = new SimpleImage();
                                $image_simple->load($image_source);
                                $image_simple->resize($size_1,$size_2);
                                $image_simple->save($image_target);
                            }
                            $final_image_path = $image_target;
                        }

                        $image = Zend_Pdf_Image::imageWithPath($final_image_path);
                        $this->getPage()->drawImage($image, $x1 + $image_x_addon_2, $y1 +7, $x2 + $image_x_addon_2, $y2 + 7);
                    } catch (Exception $e) {
                        echo $e->getMessage(); exit;
                        if ($product_images_border_color_temp != '#FFFFFF') {
                            $this->getPage()->setLineWidth(0.5);
                            $this->getPage()->setFillColor($white_color);
                            $this->getPage()->setLineColor($white_color);
                            $this->getPage()->drawRectangle(($x1 - 2 + $image_x_addon_2), ($y1 - 3 + 7), ($x2 + 2 + $image_x_addon_2), ($y2 + 3 + 7));
                            $this->getPage()->setFillColor($black_color);
                        }
                        continue;
                    }
                    $this->has_shown_product_image = 1;
                    $this->after_print_image_y = $this->y - $this->img_height + 5 + 3 - 15 + $this->packingsheetConfig['product_images_y_nudge']; // $this->y;
                    if ($this->flag_image_newpage)
                        $this->after_print_image_y_newpage = $this->y - $this->img_height + 5 + 3 - 15 + $this->packingsheetConfig['product_images_y_nudge'];;
                }
            }
            if ($this->has_shown_product_image == 0) {
                $product_images_source_res = $this->packingsheetConfig['product_images_source'];
                if ($product) {
                    if (($product_images_source_res == 'thumbnail') && (!$product->getThumbnail() || ($product->getThumbnail() == 'no_selection'))) 
						$product_images_source_res = 'image';
                    elseif (($product_images_source_res == 'small_image') && (!$product->getSmallImage() || ($product->getSmallImage() == 'no_selection'))) 
						$product_images_source_res = 'image';
                    if (($product_images_source_res == 'image') && (!$product->getImage() || ($product->getImage() == 'no_selection'))) 
						$product_images_source_res = 'small_image';
                    if (($product_images_source_res == 'small_image') && (!$product->getSmallImage() || ($product->getSmallImage() == 'no_selection'))) 
						$product_images_source_res = 'thumbnail';
                    $image_galleries = $product->getData('media_gallery');
                    if (isset($image_galleries['images'])) {
                        if (count($image_galleries['images']) > 0) {
                            if ($product->getData($product_images_source_res) != 'no_selection') // continue; // if no images are valid, skip it
                            {
                                try{
                                    $image_obj = Mage::helper('catalog/image')->init($product, $product_images_source_res);
                                }
                                catch(Exception $e){}
                                if (isset($image_obj)) {
                                    $this->img_width = $product_images_maxdimensions[0];
                                    $this->img_height = $product_images_maxdimensions[1];

                                    $orig_img_width = $image_obj->getOriginalWidth();
                                    $orig_img_height = $image_obj->getOriginalHeigh(); // getOriginalHeigh() = spell mistake
                                    if ($orig_img_width != $orig_img_height) {
                                        if ($orig_img_width > $orig_img_height)
                                            $this->img_height = ceil(($orig_img_height / $orig_img_width) * $product_images_maxdimensions[1]);
                                        elseif ($orig_img_height > $orig_img_width)
                                            $this->img_width = ceil(($orig_img_width / $orig_img_height) * $product_images_maxdimensions[0]);
                                    }

                                    $x1 = $this->packingsheetConfig['product_images_x_pos'];
                                    $y1 = ($this->y - $this->img_height);
                                    $x2 = ($this->packingsheetConfig['product_images_x_pos'] + $this->img_width);
                                    $y2 = ($this->y);

                                    if (is_integer($this->img_width)) 
										$resize_x = ($this->img_width * 4);
                                    if (is_integer($this->img_height)) 
										$resize_y = ($this->img_height * 4);

                                    $image_placeholder_height = ($y2 - $y1);

                                    // product_images_source = $thumbnail, small_image, image, gallery
                                    if ($this->packingsheetConfig['product_images_source'] == 'gallery') {
                                        $gallery = $product->getMediaGalleryImages();
                                        // can get posiiton here

                                        $image_urls = array();
                                        foreach ($gallery as $image) {
                                            $imagePath_temp = Mage::helper('catalog/image')->init($product, 'image', $image->getFile())
                                                ->constrainOnly(TRUE)
                                                ->keepAspectRatio(TRUE)
                                                ->keepFrame(FALSE)
                                                ->resize($resize_x, $resize_y)
                                                ->__toString();

                                            if (strpos($imagePath_temp, 'placeholder') === false) 
												$imagePaths[] = $imagePath_temp;
                                        }
                                    } else {
                                        try{
                                            $imagePath_temp = Mage::helper('catalog/image')->init($product, $product_images_source_res)
                                                ->constrainOnly(TRUE)
                                                ->keepAspectRatio(TRUE)
                                                ->keepFrame(FALSE)
                                                ->resize($resize_x, $resize_y)
                                                ->__toString();
                                        }
                                        catch(Exception $e){
                                        }
                                        if (strpos($imagePath_temp, 'placeholder') === false) 
											$imagePaths[] = $imagePath_temp;
                                    }

                                    $imagePath = '';
                                    $image_x_addon = 0;
                                    $image_x_addon_2 = 0;
                                    $count = 1;

                                    foreach ($imagePaths as $imagePath) {
                                        $imagePath = trim($imagePath);
                                        if ($imagePath != '') {
                                            $image_x_addon += ($count * ($this->img_width + 10)); // shift the 2nd image over
                                            $image_x_addon_2 += (($count - 1) * ($this->img_width + 10)); // shift the 2nd image over
                                            $count++;
                                            $media_path = Mage::getBaseDir('media');
                                            $image_url = $imagePath;
                                            $image_url_after_media_path_with_media = strstr($image_url, '/media/');
                                            $image_url_after_media_path = strstr_after($image_url, '/media/');

                                            $final_image_path = $media_path . '/' . $image_url_after_media_path;
                                            $final_image_path2 = $media_path . '/' . $image_url_after_media_path;
                                            $image_ext = '';
                                            $image_part = explode('.', $image_url_after_media_path);
                                            $image_ext = array_pop($image_part);
                                            if (($image_ext != 'jpg') && ($image_ext != 'jpeg') && ($image_ext != '.png')) 
												continue;
											
                                            //Check to print image in current page or in a new page.
											if($this->checkNewPageNeeded() === true) {
                                                $page = $this->getPage();
												$this->flag_image_newpage = 1;
                                                $x1 = ($this->packingsheetConfig['product_images_x_pos']);
                                                $y1 = ($this->y - $this->img_height - 5);
                                                $x2 = ($this->packingsheetConfig['product_images_x_pos'] + $this->img_width);
                                                $y2 = ($this->y - 5);
                                                $this->befor_print_image_y_newpage = $y1;
											}
                                           
                                            if ($product_images_border_color_temp != '#FFFFFF') {
                                                $this->getPage()->setLineWidth(0.5);
                                                $this->getPage()->setFillColor($product_images_border_color);
                                                $this->getPage()->setLineColor($product_images_border_color);
                                                $this->getPage()->drawRectangle(($x1 - 1 + $image_x_addon_2), ($y1 - 1 +7), ($x2 + 1 + $image_x_addon_2), ($y2 + 1 +7));
                                                $this->getPage()->setFillColor($black_color);
                                            }
                                            try {
                                                $image_source = $final_image_path2;
                                                $io = new Varien_Io_File();
                                                $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
                                                $ext = substr($image_source, strrpos($image_source, '.') + 1);
                                                if($ext != 'jpg' && $ext != 'JPG')
                                                {
                                                    $image_zebra->source_path = $final_image_path2;
                                                    $image_zebra->target_path = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$product_sku_md5.'.jpeg';;
                                                    //                                              $image->jpeg_quality = 100;
                                                    if(!(file_exists($image_zebra->target_path)))
                                                    {
                                                        $size_1 = $this->img_width*300/72;
                                                        $size_2 = $this->img_height*300/72;
                                                        if (!$image_zebra->resize($size_1, $size_2, ZEBRA_IMAGE_NOT_BOXED, -1))
                                                            show_error($image_zebra->error, $image_zebra->source_path, $image_zebra->target_path);

                                                    }
                                                    $final_image_path = $image_target;
                                                } else {
                                                    $ext = 'jpeg';
                                                    $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$product_sku_md5.'.'.$ext;

                                                    if(!(file_exists($image_target)))
                                                    {
                                                        $size_1 = $this->img_width*300/72;
                                                        $size_2 = $this->img_height*300/72;
                                                        $image_simple->load($image_source);
                                                        $image_simple->resize($size_1,$size_2);
                                                        $image_simple->save($image_target);
                                                    }
                                                }
                                                $final_image_path = $image_target;
                                                $image = Zend_Pdf_Image::imageWithPath($final_image_path);
                                                $this->getPage()->drawImage($image, $x1 + $image_x_addon_2, $y1 +7, $x2 + $image_x_addon_2, $y2 +7);
                                            } catch (Exception $e) {
                                                echo $e->getMessage(); exit;
                                                if ($product_images_border_color_temp != '#FFFFFF') {
                                                    $this->getPage()->setLineWidth(0.5);
                                                    $this->getPage()->setFillColor($white_color);
                                                    $this->getPage()->setLineColor($white_color);
                                                    $this->getPage()->drawRectangle(($x1 - 2 + $image_x_addon_2), ($y1 - 3 + 7), ($x2 + 2 + $image_x_addon_2), ($y2 + 3 + 7));
                                                    $this->getPage()->setFillColor($black_color);
                                                }
                                                continue;
                                            }
                                            $this->has_shown_product_image = 1;
                                            $this->after_print_image_y = $this->y - $this->img_height + 5 + 3 - 15; // $this->y;
                                            if ($this->flag_image_newpage)
                                                $this->after_print_image_y_newpage = $this->y - $this->img_height + 5 + 3 - 15;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this->y -= 15;
            if ($product_images_line_nudge != 0 && !isset($product_build_value['bundle_options_sku']))
                $this->y = ($this->y + $product_images_line_nudge);
            unset($product_id);
        }
        //Goto before print image.
        if ($this->flag_image_newpage && isset($page_prev)) {
            $page = $page_prev;
            $this->y = $this->befor_print_image_y_newpage;
        }
        else
            $this->y = $this->before_print_image_y;
        /***************************END PRINT ITEM IMAGE*********************/
    }

    public function getProductSku($item) {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();

        if ($this->packingsheetConfig['product_sku_yn'] == 'fullsku')
            $product_sku = $item->getSku();
        else {
            $product = Mage::helper('pickpack/product')->getProductFromItem($item);
            $product_sku = $product->getSku();
        }

        return $product_sku;
    }

    protected function getProductImagesMaxDimensions() {
        $storeId = $this->getOrder()->getStoreId();
        $wonder = $this->getWonder();

        $product_images_maxdimensions = $this->packingsheetConfig['product_images_maxdimensions'];
        if ($product_images_maxdimensions[0] == '' || $product_images_maxdimensions[1] == '') {
            if ($product_images_maxdimensions[0] == '') 
				$product_images_maxdimensions[0] = NULL;
            if ($product_images_maxdimensions[1] == '') 
				$product_images_maxdimensions[1] = NULL;
            if ($product_images_maxdimensions[0] == NULL && $product_images_maxdimensions[1] == NULL)
            {
                $product_images_maxdimensions[0] = 50;
                $product_images_maxdimensions[1] = 50;
            }
        }

        return $product_images_maxdimensions;
    }

    public function printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX,$sku_barcodeY,$padded_right,$font_family_barcode,$barcode_font_size,$white_color) {
        $generalConfig = $this->getGeneralConfig();
        $nextCollumnX = getPrevNext2($this->columns_xpos_array, 'sku_barcodeX', 'next');

        $after_print_barcode_y = ($sku_barcodeY - 2 - ($barcode_font_size * 2));
        $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($barcode, $barcode_type);
        $barcodeWidth = $this->parseString($barcode, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);

		// Print a white rectangle to make a usable barcode on pages with full image background
		$this->getPage()->setFillColor($white_color);
        $this->getPage()->setLineColor($white_color);
        $this->getPage()->drawRectangle(($sku_barcodeX - 5), ($sku_barcodeY - 2), ($sku_barcodeX + $barcodeWidth + 5), ($sku_barcodeY - 2 - ($barcode_font_size * 1.6)));

        $this->getPage()->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $this->getPage()->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);

        if (($sku_barcodeX + $barcodeWidth) > $padded_right){
            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], '#FF3333');
            $this->_drawText("!! TRIMMED BARCODE !!", ($sku_barcodeX), ($sku_barcodeY));
        }
        else if ( ($sku_barcodeX + $barcodeWidth) >= $nextCollumnX)
            $this->_drawText($barcodeString, ($sku_barcodeX), ($sku_barcodeY - (1.3*$barcode_font_size)), 'CP1252');
        else
            $this->_drawText($barcodeString, ($sku_barcodeX), ($sku_barcodeY), 'CP1252');

        return $after_print_barcode_y;
    }

    /*
     * This function prints the bundle product shelving
     *
     */
    protected function printBundleShelving($page,$shelving_yn,$shelving_attribute,$product,$child,$columns_xpos_array,$padded_right,$shelfX,$addon_shift_x,$store_id,$page_count,$items_header_top_firstpage,$page_top,$order_number_display) {
        $helper = Mage::helper('pickpack');
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $order->getStore()->getId();
        $generalConfig = Mage::helper('pickpack/config')->getGeneralConfigArray($storeId);

        $shelving_real_yn = $this->_getConfig('shelving_real_yn', 'shelf', false, $wonder, $store_id);
        $combine_custom_attribute_yn = $this->_getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
        $shelving_real = '';
        $combine_custom_attribute_Xpos = $this->_getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
        if ( $shelving_real_yn == 1 && $shelving_yn == 1 && $product->offsetExists($shelving_attribute)) {
            $option = '';
            switch (trim($shelfX)){
                case 'shelfX':
                    $option = 'shelving_real_trim_content_yn';
                    break;
                case 'shelf2X';
                    $option = 'shelving_trim_content_yn';
                    break;
                case 'shelf3X';
                    $option = 'shelving_2_trim_content_yn';
                    break;
                case 'shelf4X';
                    $option = 'shelving_3_trim_content_yn';
                    break;
            }
            if ($product->getData($shelving_attribute))
                $shelving_real = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute);//$product->getData($shelving_attribute);
            elseif ($helper->getProductForStore($child->getProductId(), $store_id)->getAttributeText($shelving_attribute))
                $shelving_real = $helper->getProductForStore($child->getProductId(), $store_id)->getAttributeText($shelving_attribute);
			elseif ($product[$shelving_attribute]) 
				$shelving_real = $product[$shelving_attribute];
           
		    if (is_array($shelving_real)) 
				$shelving_real = implode(',', $shelving_real);
            $shelving_real = trim($shelving_real);
            if($this->packingsheetConfig['custom_round_yn'] != 0)
                $shelving_real = $this->_roundNumber($shelving_real,$this->packingsheetConfig['custom_round_yn']);

            $next_col_to_shelving_real = getPrevNext2($columns_xpos_array,$shelfX, 'next', $padded_right);
            $shelfX = $this->_getConfig('pricesN_'.$shelfX, 0, false, $wonder, $store_id);
            $max_shelving_real_length = ($next_col_to_shelving_real - $shelfX);
            $font_temp_shelf1 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
            $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $line_width_shelving_real = $this->parseString('1234567890', $font_temp_shelf2, ($generalConfig['font_size_body'] - 2));
            $char_width_shelving_real = $line_width_shelving_real / 11;
            $max_chars_shelving_real = round($max_shelving_real_length / $char_width_shelving_real);

            $shelving_real = wordwrap($shelving_real, $max_chars_shelving_real, "\n");
            if($combine_custom_attribute_yn == 1)
                $shelfX = $combine_custom_attribute_Xpos;

            $shelving_real_trim = str_trim($shelving_real, 'WORDS', $max_chars_shelving_real - 3, '...');
            $token = strtok($shelving_real, "\n");

            $msg_line_count = 2;
            if ($token != false) {
                while ($token != false) {
                    $shelving_real_array[] = $token;
                    $msg_line_count++;
                    $token = strtok("\n");
                }
            } else
                $shelving_real_array[] = $shelving_real & nbsp;
            //End
            if ($this->_getConfig($option, 0, false, $wonder, $storeId))
                $this->_drawText($shelving_real_trim, $shelfX +$addon_shift_x, $this->y);
            else {
                $count_shelving_row = count($shelving_real_array);

                foreach ($shelving_real_array as $shelving_real_line) {
                    $this->_drawText($shelving_real_line, $shelfX +$addon_shift_x, $this->y);
                    if($count_shelving_row >0)
                        $this->y -= $generalConfig['font_size_body']*0.8;

                    $this->checkNewPageNeeded();
                    $page = $this->getPage();
                }
            }
            unset($shelving_real_array);
            unset($shelving_real);
        }
        return $page;
    }

    protected function getQtyString($from_shipment, $shiped_items_qty, $item, $qty, $wonder, $invoice_id = '', $shipment_ids = '') {
        $qty_string = $qty;
        if (!empty($invoice_id) || !empty($shipment_ids)) {
            if ($invoice_id) {
                if ($this->checkItemBelongInvoiceDetail($item->getSku(), $invoice_id)) {
                    $item_belong_invoice = $this->getItemBelongInvoice($item->getSku(), $invoice_id);
                    if ($item_belong_invoice != '')
                        $qty_string = (int)$item_belong_invoice->getQty();
                }
            }
            if ($shipment_ids) {
                if ($this->checkItemBelongShipment($item->getSku(), $shipment_ids)) {
                    $item_belong_shipment = $this->getItemBelongShipment($item->getSku(), $shipment_ids);
                    if ($item_belong_shipment != '')
                        $qty_string = (int)$item_belong_shipment->getQty();
                }
            }
            return $qty_string;
        }

        if ($from_shipment == 'shipment') {
            switch ($this->packingsheetConfig['show_qty_options']) {
                case 1:
                    $qty_string = $qty;

                    break;
                case 2:
                    $qty_string = 'q:' . ($qty - (int)$shiped_items_qty[$item->getData('product_id')]) . ' s:' . (int)$shiped_items_qty[$item->getData('product_id')] . ' o:' . (int)$item->getData('qty_ordered');

                    break;
                case 3:
                    $qty_string = ($qty - (int)$shiped_items_qty[$item->getData('product_id')]);

                    break;

                case 4:
                    $qty_string = (int)$item->getData("qty_invoiced");

                    break;
            }
        } else {
            switch ($this->packingsheetConfig['show_qty_options']) {
                case 1:
                    $qty_string = $qty;

                    break;
                case 2:
                    $qty_string = 'q:' . ($qty - (int)$item->getQtyShipped()) . ' s:' . (int)$item->getQtyShipped() . ' o:' . $qty;

                    break;
                case 3:
                    $qty_string = ($qty - (int)$item->getQtyShipped());

                    break;

                case 4:
                    $qty_string = (int)$item->getData("qty_invoiced");

                    break;
            }

        }
        return $qty_string;
    }

    protected function getQtyStringBundle($isShipment, $product_build_value, $qty, $wonder, $invoice_id = '', $shipment_id = '') {
        $qty_string = 0;
        if ($isShipment) {
            switch ($this->packingsheetConfig['show_qty_options']) {
                case 1:
                    $qty_string = $qty;

                    break;
                case 2:
                    $qty_string = 'q:' . ($qty - (int)$product_build_value['bundle_qty_shipped']) . ' s:' . (int)$product_build_value['bundle_qty_shipped'] . ' o:' . (int)$qty;

                    break;
                case 3:
                    $qty_string = ($qty - (int)$product_build_value['bundle_qty_shipped']);

                    break;

                case 4:
                    $qty_string = (int)$product_build_value['bundle_qty_invoiced'];

                    break;
            }
        } else {
            switch ($this->packingsheetConfig['show_qty_options']) {
                case 1:
                    $qty_string = $qty;

                    break;
                case 2:
                    $qty_string = 'q:' . ($qty - (int)$product_build_value['bundle_qty_shipped']) . ' s:' . (int)$product_build_value['bundle_qty_shipped'] . ' o:' . (int)$qty;

                    break;
                case 3:
                    $qty_string = ($qty - (int)$product_build_value['bundle_qty_shipped']);

                    break;

                case 4:
                    $qty_string = (int)$product_build_value['bundle_qty_invoiced'];

                    break;
            }

        }
        return $qty_string;
    }

    protected function checkItemBelongShipment($item_sku, $shipment_ids) {
        $isBelong = false;
        $shipmentModel = Mage::getModel('sales/order_shipment')->load($shipment_ids);
        $items = $shipmentModel->getAllItems();
        foreach ($items as $item) {
            if ($item->getOrderItem()->getParentItem())
                continue;

            if ($item->getSku() == $item_sku) {
                $isBelong = true;
                break;
            }
        }
        return $isBelong;
    }

    public function getItemBelongInvoice($item_sku, $invoice_id) {
        $item_belong = '';
        if ($invoice_id != '') {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoice_id);
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem())
                    continue;

                if ($item->getSku() == $item_sku) {
                    $item_belong = $item;
                    break;
                }
            }
        }
        return $item_belong;
    }

    public function checkItemBelongInvoiceDetail($item_sku, $invoice_id) {
        $isBelong = false;
        if ($invoice_id != '') {

            $invoice = Mage::getModel('sales/order_invoice')->load($invoice_id);
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem())
                    continue;

                if ($item->getSku() == $item_sku) {
                    $isBelong = true;
                    break;
                }
            }

        }
        return $isBelong;
    }

    public function getItemBelongShipment($item_sku, $shipment_ids) {
        $item_belong = '';
        if ($shipment_ids != '') {
            $invoice = Mage::getModel('sales/order_shipment')->load($shipment_ids);
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                if ($item->getOrderItem()->getParentItem())
                    continue;

                if ($item->getSku() == $item_sku) {
                    $item_belong = $item;
                    break;
                }
            }
        }
        return $item_belong;
    }

    protected function getCombineAttribute($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $storeId) {
        $barcode_array = array();
        $new_product_barcode = '';
        $product_attributes[] = trim($this->packingsheetConfig['product_attribute_1']);
        $product_attributes[] = trim($this->packingsheetConfig['product_attribute_2']);
        $product_attributes[] = trim($this->packingsheetConfig['product_attribute_3']);
        $product_attributes[] = trim($this->packingsheetConfig['product_attribute_4']);
        $product_attributes[] = trim($this->packingsheetConfig['product_attribute_5']);

        $barcode_array['spacer'] = $product_sku_barcode_spacer = ",";
        foreach ($product_attributes as $product_attribute) {
            $new_product_barcode = $this->getSkuBarcodeByAttribute2($product_attribute, $barcode_array, $new_product_barcode, $product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute);
        }
        $new_product_barcode = trim($new_product_barcode);
        return $new_product_barcode;
    }

    protected function getNameDefaultStore($item) {
        $product_id      = $item->getProductId();
        $default_storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        $_newProduct     = Mage::helper('pickpack')->getProductForStore($product_id, $default_storeId);
        $name            = trim($_newProduct->getName());
        return $name;
    }

    /**
     * @return Zend_Pdf_Page
     */
    public function newPage() {
        //echo "<pre>";
        //print_r(debug_backtrace(false));
        //echo "</pre>";
        $page = parent::newPage();
        $this->yItemPos = $this->y;
        $this->yItemPosCombine = $this->y;
        return $page;
    }
}