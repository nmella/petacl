<?php 
/** 
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
* File        Separated.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Separated extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    protected $warehouse_title = array();

    public function __construct() {
        parent::__construct();
    }

    public function getGeneralConfig() {
        return Mage::helper('pickpack/config')->getGeneralConfigArray($this->getStoreId());
    }

    public function getQtyString($from_shipment, $sku_qty_shipped, $qty, $sku_qty_invoiced){
        $store_id = Mage::app()->getStore()->getId();
        $show_qty_options = $this->_getConfig('show_qty_options', 1, false,'picks', $store_id);
            switch($show_qty_options) {
                case 1:
                    $qty_string = $qty;    
	                break;
            
			    case 2:
                    $qty_string = 'q:'.($qty - (int)$sku_qty_shipped).' s:' . (int)$sku_qty_shipped . ' o:' . (int)$qty;    
	                break;
                case 3: 
                    $qty_string = ($qty - (int)$sku_qty_shipped);
	                break;
				
                case 4:
                    $qty_string =  (int)$sku_qty_invoiced;
	                break;
            }
        return $qty_string;
    }
	
	/*
		Writes the end-of-doc total items / orders / etc
	*/
    private function writeSummary($label, $value, $page, $key_symbol = 'square'){
        $this->setGeneralConfig(Mage::app()->getStore()->getStoreId());
        $helper = Mage::helper('pickpack');
        $generalConfig = $this->getGeneralConfig();

		$white_color = new Zend_Pdf_Color_GrayScale(1);
		$black_color = new Zend_Pdf_Color_Rgb(0, 0, 0);
		$font_color_body_zend = new Zend_Pdf_Color_Html($generalConfig['font_color_body']);
				
        $this->y -= ($generalConfig['font_size_body'] * 2);
		
        $page->setFillColor($white_color);
        $page->setLineColor($black_color);
        $page->setLineWidth(0.5);
        
		if($key_symbol == 'square')
        	$page->drawRectangle(360, ($this->y), 366, ($this->y + 6));
		elseif($key_symbol == 'circle')
			$page->drawCircle(363.5, ($this->y + 3.5), 3.5);
		
        $page->setFillColor($font_color_body_zend);
        $page->setLineWidth(0.5);
		
		$this->_setFont($page, 'semibold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        $page->drawText($label, 375, $this->y, 'UTF-8');
        $this->_setFont($page, 'bold', ($generalConfig['font_size_body']+0.5), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        $page->drawText($value, (380 + ( strlen($label) * $generalConfig['font_size_body'] * 0.8)), ($this->y-0.5), 'UTF-8');
		$this->y += ($generalConfig['font_size_body'] * 0.5);
		
		return;
    }
	
    private function getShippingAddressLabelArray($address_format_label, $shipping_address, $address_countryskip_label){
        $shippingAddressLabelArray = array();
        $address_format_set = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $address_format_label);
        //TODO shipping address
        if (trim($address_countryskip_label) != ''){
            $shipping_address['country'] = str_ireplace($address_countryskip_label, '', $shipping_address['country']);
            /*TODO filter city if country = singapore or monaco*/
            if(is_array($address_countryskip_label) && (strtolower(trim($address_countryskip_label)) == "singapore" || strtolower(trim($address_countryskip_label)) =="monaco")){
                $shipping_address['city'] = str_ireplace($address_countryskip_label, '', $shipping_address['city']);
            }
        }
        foreach ($shipping_address as $key => $value) {
            $value = Mage::helper('pickpack/functions')->clean_method($value, 'pdf');
            $address_format_set = $this->getAddressFormatByValue($key, $value, $address_format_set);
        }
        $address_format_set = str_replace(array('||', '|'), "\n", trim($address_format_set));
        $address_format_set = str_replace(array('{if city}', '{if postcode}', '{if region}', '{if firstname}', '{firstname}', '{/if firstname}', '{if lastname}', '{lastname}', '{/if lastname}'), '', $address_format_set);
        $address_format_set   = str_replace("\n\n", "\n", $address_format_set);
        $address_format_set   = str_replace(",", " ", $address_format_set);
        $shippingAddressLabelArray = explode("\n", $address_format_set);
        return $shippingAddressLabelArray;
    }
	
    private function drawAddressLabelText($address_format_label, $shipping_address, $address_countryskip_label,$label_width,$label_padding,$font_size_label,$y_start_order,$first_start_y,$nudge_shipping_address, $padded_right, $label_height,$page, $font_style_label, $font_family_label, $non_standard_characters, $font_color_label){
        $shippingAddressLabelArray = $this->getShippingAddressLabelArray($address_format_label, $shipping_address, $address_countryskip_label);
        $font_temp      = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $max_line_width = $label_width - $label_padding[1] - $label_padding[3];
        $addressLabelArray = array();
        $i=0;
        foreach ($shippingAddressLabelArray as $value) {
            $value = trim($value);
            if (strlen($value) < 1)
                continue;
            $line_width = $this->parseString($value, $font_temp, $font_size_label);
            $max_chars  = round($max_line_width / ($line_width / strlen($value)));
            if(strlen($value) > $max_chars){
                $value_arr      = explode("\n", wordwrap($value, $max_chars, "\n"));
                if(is_array($value_arr)){
                    for($k = 0; $k < count($value_arr); $k++){
                        $addressLabelArray[$i] = $value_arr[$k];
                        $i++;
                    }
                }
                else{
                    $addressLabelArray[$i] = $value; 
                    $i++;
                }
            }
            else{
                $addressLabelArray[$i] = $value; 
                $i++;
            }
        }
        $line_count_label = count($addressLabelArray);
        $line_height = $line_count_label * ($font_size_label + 2);
        $top_label_address = $y_start_order - $first_start_y + $nudge_shipping_address[1] - $label_padding[0];
        $left_label_address = $padded_right - ($label_width + 5) + $nudge_shipping_address[0] + $label_padding[3];
        $label_height_padding = $label_height - $label_padding[0] - $label_padding[2];
        if($line_height > $label_height_padding){
            $font_size_label = $label_height_padding / $line_count_label;
        }
        if (isset($addressLabelArray) && is_array($addressLabelArray)) {
            $this->_setFont($page, $font_style_label, $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);
            foreach ($addressLabelArray as $key => $chunk) {
                $chunk_display = '';
                if (trim($chunk) != '') {
                    $top_label_address -= ($font_size_label + 2);
                    $page->drawText($chunk, ($left_label_address), $top_label_address, 'UTF-8');
                }
            }
            unset($addressLabelArray);
        }
    }
	
    private function drawBorderLabel($y_start_order, $first_start_y, $nudge_shipping_address, $label_height, $padded_right, $label_width, $white_color, $black_color, $page){
        $top_label_address = $y_start_order - $first_start_y + $nudge_shipping_address[1];
        $bottom_label_address = $top_label_address - $label_height;
        $left_label_address = $padded_right - ($label_width + 5) + $nudge_shipping_address[0];
        $right_label_address = $left_label_address + $label_width;
        $page->setFillColor($white_color);
        $page->setLineColor($black_color);
        $page->setLineWidth(0.5);
        $page->drawRectangle($left_label_address, $bottom_label_address, $right_label_address, $top_label_address);
    }
	
    private function getBarcodeByAttribute($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $product_id) {
        if ($product_sku_barcode_attribute != '') {
            switch ($product_sku_barcode_attribute) {
                case 'product_id':
                    $barcode_array[$product_sku_barcode_attribute] = $product_id;
                    break;
                default:
                    $attributeName = $product_sku_barcode_attribute;
                    $product = Mage::helper('pickpack')->getProduct($product_id);
                    $barcode_array[$product_sku_barcode_attribute] = Mage::helper('pickpack')->getProductAttributeValue($product, $attributeName);
                    break;
            }
            $new_product_barcode = $new_product_barcode . $barcode_array[$product_sku_barcode_attribute] . $barcode_array['spacer'];
        }
        return $new_product_barcode;
    }
	
    private function getBarcode($product_id, $wonder, $store_id) {
        $barcode_array = array();
        $new_product_barcode = '';
        $product_id_barcode_attributes[] = $this->_getConfig('product_id_barcode_attribute_1', '', false, $wonder, $store_id);
        $product_id_barcode_attributes[] = $this->_getConfig('product_id_barcode_attribute_2', '', false, $wonder, $store_id);
        $product_id_barcode_attributes[] = $this->_getConfig('product_id_barcode_attribute_3', '', false, $wonder, $store_id);
        $product_id_barcode_attributes[] = $this->_getConfig('product_id_barcode_attribute_4', '', false, $wonder, $store_id);
        $product_id_barcode_attributes[] = $this->_getConfig('product_id_barcode_attribute_5', '', false, $wonder, $store_id);
        $product_id_barcode_spacer = $this->_getConfig('product_id_barcode_spacer', '', false, $wonder, $store_id);
        if ($product_id_barcode_spacer != '')
            $barcode_array['spacer'] = $product_id_barcode_spacer;
        else
            $barcode_array['spacer'] = '';
        foreach ($product_id_barcode_attributes as $product_id_barcode_attribute)
            $new_product_barcode = $this->getBarcodeByAttribute($product_id_barcode_attribute, $barcode_array, $new_product_barcode, $product_id);
        return $new_product_barcode;
    }
	
    private function getCustomAttribute($product,$shelving_attribute, $sku_shelving, $sku){
        $shelving = Mage::helper('pickpack')->getProductAttributeValue($product,$shelving_attribute,false);
        if (trim($shelving) != '') {
            if (isset($sku_shelving[$sku]) && trim(strtoupper($sku_shelving[$sku])) != trim(strtoupper($shelving))) 
				$sku_shelving[$sku] .= ',' . trim($shelving);
            else 
				$sku_shelving[$sku] = trim($shelving);
            $sku_shelving[$sku] = preg_replace('~,$~', '', $sku_shelving[$sku]);
        } else 
			$sku_shelving[$sku] = '';
        return $sku_shelving[$sku];
    }
	
    private function getCustomAttributeBundle($product_child, $shelving_attribute, $shelving_real_b){
        if ($product_child->getData($shelving_attribute))
            $shelving_real_b = Mage::helper('pickpack')->getProductAttributeValue($product_child,$shelving_attribute, false);
        else {
            if ($product_child[$shelving_attribute])
                $shelving_real_b = $product_child[$shelving_attribute];
            else
                $shelving_real_b = '';
        } 

        if (is_array($shelving_real_b)) 
			$shelving_real_b = implode(',', $shelving_real_b);
        if (isset($shelving_real_b)) 
			$shelving_real_b = trim($shelving_real_b);
        return $shelving_real_b;
    }
	
    function getPickSeparated($orders = array(), $from_shipment = 'order') {
        /*************************** BEGIN PDF GENERAL CONFIG *******************************/
        $this->setGeneralConfig(Mage::app()->getStore()->getStoreId());
        /*************************** END PDF GLOBAL PAGE CONFIG *******************************/

        $helper = Mage::helper('pickpack');
        $generalConfig = $this->getGeneralConfig();
		
        if (function_exists('sksort')) {
        } else {
            function sksort(&$array, $subkey, $sort_ascending = false)
            {
                if (count($array))
                    $temp_array[key($array)] = array_shift($array);

                foreach ($array as $key => $val) {
                    $offset = 0;
                    $found = false;
                    foreach ($temp_array as $tmp_key => $tmp_val) {
                        if (!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                            $temp_array = array_merge((array)array_slice($temp_array, 0, $offset),
                                array($key => $val),
                                array_slice($temp_array, $offset)
                            );
                            $found = true;
                        }
                        $offset++;
                    }
                    if (!$found) 
						$temp_array = array_merge($temp_array, array($key => $val));
                }

                if ($sort_ascending) 
					$array = array_reverse($temp_array);
                else 
					$array = $temp_array;
            }
        }

        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $page_size = $generalConfig['page_size'];

        if ($page_size == 'letter') {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $page_top = 770;
            $padded_right = 587;
            $padded_left = 20;
        } elseif ($page_size == 'a4') {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $page_top = 820;
            $padded_right = 570;
            $padded_left = 20;
        }

        $pdf->pages[] = $page;

        $skuX = 67;
        $qtyX = 50;
        $productX = 250;
        $total_quantity = 0;
        $total_cost = 0;
        $red_bkg_color = new Zend_Pdf_Color_Html('lightCoral');
        $lt_grey_bkg_color = new Zend_Pdf_Color_GrayScale(0.9);
        $config_group = 'messages';
        $alternate_row_color_temp = $this->_getConfig('alternate_row_color', '#DDDDDD', false,$config_group);
        $alternate_row_color    = new Zend_Pdf_Color_Html($alternate_row_color_temp);
        $white_bkg_color = new Zend_Pdf_Color_Html('white');
        $orange_bkg_color = new Zend_Pdf_Color_Html('Orange');
        $black_color = new Zend_Pdf_Color_Rgb(0, 0, 0);
        $greyout_color = new Zend_Pdf_Color_GrayScale(0.6);
        $white_color = new Zend_Pdf_Color_GrayScale(1);

        $giftmessage_yn = $this->_getConfig('giftmessage_yn_separated', 0, false, 'picks'); //col/sku
        $message_title_tofrom_yn = $this->_getConfig('giftmessage_title_separated', 0, false, 'picks'); //col/sku
        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['background_color_subtitles']);
        $background_color_orderdetails_zend = new Zend_Pdf_Color_Html('#CCCCCC');
        $font_color_body_zend = new Zend_Pdf_Color_Html($generalConfig['font_color_body']);
       
        $shelvingpos = $this->_getConfig('shelvingpos', 'col', false, 'general'); //col/sku
        $order_address_yn = $this->_getConfig('pickpack_order_address_yn', 0, false, 'picks'); //col/sku
        $order_billing_address_yn = $this->_getConfig('pickpack_order_billing_address_yn', 0, false, 'picks'); //col/sku
        $store_id = Mage::app()->getStore()->getId();
        $address_format_default = "{if company}{company},|{/if}
{if name}{name},|{/if}
{if street}{street},|{/if}
{if city}{city},{/if} {if region}{region}{/if} {if postcode}{postcode}|{/if}
{if country}{country}|{/if}";
        $address_label_yn = $this->_getConfig('address_label_yn', 0, false, 'picks'); //col/sku
        if($address_label_yn == 1){
            $order_address_yn =0;
            $order_billing_address_yn=0;
            $demension_label_address = explode(",", $this->_getConfig('label_demension_fields', '250,200', false, 'picks'));
            $label_width     = $demension_label_address[0];
            $label_height    = $demension_label_address[1];
            $font_family_label = $this->_getConfig('font_family_label', 'helvetica', false, 'picks', $store_id);
            $font_style_label  = $this->_getConfig('font_style_label', 'regular', false, 'picks', $store_id);
            $font_size_label   = $this->_getConfig('font_size_label', 15, false, 'picks', $store_id);
            $font_color_label  = trim($this->_getConfig('font_color_label', 'Black', false, 'picks', $store_id));
            $address_format_label = $this->_getConfig('address_format', $address_format_default, false, 'picks'); //col/sku
            $address_countryskip_label = $this->_getConfig('address_countryskip', 0, false, 'picks');
            $nudge_shipping_address = explode(",", $this->_getConfig('nudge_shipping_address', '0,0', false, 'picks'));
            $label_padding = explode(",", $this->_getConfig('label_padding', '5,5,5,5', false, 'picks'));
            $subsection_order_height   = $this->_getConfig('subsection_order_height', 300, false, 'picks', $store_id);
            $first_start_y   = $this->_getConfig('first_start_y', 10, false, 'picks', $store_id);
        }
        
        $configurable_names = $this->_getConfig('pickpack_configname_separated', 'simple', false, 'picks'); //col/sku
        if ($configurable_names != 'custom') 
			$configurable_names_attribute = '';
        $barcodes = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickbarcode');
        $product_barcode_yn = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickproductbarcode_yn');
        $product_barcode_X = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickproductbarcode_X_Pos');
        $product_barcode_bottom_yn = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickproductbarcode_bottom_yn');
        $product_barcode_bottom_font_size = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickproductbarcode_bottom_font_size');
        $store_view = $this->_getConfig('name_store_view', 'storeview', false, "picks",$store_id);
        $specific_store_id = $this->_getConfig('specific_store', '', false,'picks', $store_id);
        
        $printdates = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickprint'); //, $order->getStore()->getId());
        $show_bundle_parent_yn = $this->_getConfig('show_bundle_parent_yn', 0, false, 'picks');
        $product_id = NULL; // get it's ID
        $stock = NULL;
        $sku_stock = array();
        $currency_default = 'USD';

        $shelving_yn_default = 0;
        $shelving_attribute_default = 'shelf';
        $shelvingX_default = 200;
        $namenudgeYN_default = 0;
        $stockcheck_yn_default = 0;
        $stockcheck_default = 1;
        $shipping_method_x = 0;
        $warehouse_x = 0;
        $product_id = NULL; // get it's ID
        $stock = NULL;
        $split_supplier_yn_default = 'no';
        $supplier_attribute_default = 'supplier';
        $supplier_options_default = 'filter';
        $tickbox_default = 0; //no, pick, pickpack
        $split_supplier_yn_temp = $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false, 'general');
        $supplierKey = 'order_separated';
        $split_supplier_options_temp = $this->_getConfig('pickpack_split_supplier_options', 'no', false, 'general');
        $split_supplier_options = explode(',',$split_supplier_options_temp);
        $split_supplier_yn      = 'no';
        if ($split_supplier_yn_temp == 1) {
            if(in_array($supplierKey,$split_supplier_options))
                $split_supplier_yn = 'pickpack';
            else
                $split_supplier_yn = 'no';
            
        }

        $supplier_attribute = $this->_getConfig('pickpack_supplier_attribute', $supplier_attribute_default, false, 'general');
        $supplier_options = $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false, 'general');

        $userId = Mage::getSingleton('admin/session')->getUser() ? Mage::getSingleton('admin/session')->getUser()->getId() : 0;
        $user = ($userId !== 0) ? Mage::getModel('admin/user')->load($userId) : '';
        $username = (!empty($user['username'])) ? $user['username'] : '';

        $supplier_login_pre = $this->_getConfig('pickpack_supplier_login', '', false, 'general');
        $supplier_login_pre = str_replace(array("\n", ','), ';', $supplier_login_pre);
        $supplier_login_pre = explode(';', $supplier_login_pre);

        foreach ($supplier_login_pre as $key => $value) {
            $supplier_login_single = explode(':', $value);
            if (preg_match('~' . $username . '~i', $supplier_login_single[0])) {
                if ($supplier_login_single[1] != 'all')
					$supplier_login = trim($supplier_login_single[1]);
                else 
					$supplier_login = '';
            }
        }

        $tickbox = $this->_getConfig('pickpack_tickbox_yn_separated', $tickbox_default, false, 'picks');
        $tickbox_X = $this->_getConfig('pickpack_tickboxnudge_separated', 7, false, 'picks');
        $tickbox2 = $this->_getConfig('pickpack_tickbox2_yn_separated', $tickbox_default, false, 'picks');
        $tickbox2_X = $this->_getConfig('pickpack_tickbox2nudge_separated', 27, false, 'picks');
        
        if($tickbox == 0)
        {
            $tickbox_X = 0;
            $tickbox2 =0;
            $tickbox2_X = 0;
        }
        else
            $qtyX = ($tickbox_X > $tickbox2_X)?($tickbox_X + 15) : ($tickbox2_X + 20);
        
        $logo_maxdimensions = explode(',', '269,41');
        $picklogo = $this->_getConfig('pickpack_picklogo', 0, false, 'general');
        $showcount_yn = $this->_getConfig('pickpack_count', 1, false, 'picks');
        $showcost_yn = $this->_getConfig('pickpack_cost', 0, false, 'picks');
        $currency = $this->_getConfig('pickpack_currency', $currency_default, false, 'picks');
        $currency_symbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
        $stockcheck_yn = $this->_getConfig('pickpack_stock_yn_separated', $stockcheck_yn_default, false, 'picks');
        $stockcheck = $this->_getConfig('pickpack_stock_separated', $stockcheck_default, false, 'picks');

        $show_column_title        = $this->_getConfig('show_column_title', 0, false, 'picks');
        if($show_column_title == 1){
            $col_title_qty        = $this->_getConfig('col_title_position_qty', 'Qty', false, 'picks');
            $col_title_sku        = $this->_getConfig('col_title_position_sku', 'Sku', false, 'picks');
            $col_title_name       = $this->_getConfig('col_title_position_name', 'Name', false, 'picks');
            $col_title_product_barcode     = $this->_getConfig('col_title_product_barcode', 'Product Barcode', false, 'picks');
        }
       
 	   $shelving_yn = 0;
	   $shelving2_yn = 0;
	   $shelving3_yn = 0;
	   $shelving4_yn = 0;
	   
	   $shelvingX = 0;
	   $shelving2X = 0;
	   $shelving3X = 0;
	   $shelving4X = 0;
	   
	   $shelving_attribute = null;
	   $shelving2_attribute = null;	   
	   $shelving3_attribute = null;
	   $shelving4_attribute = null;
	   	   
        $shelving_yn = $this->_getConfig('pickpack_shelving_yn_separated', $shelving_yn_default, false, 'picks');
	   	if($shelving_yn == 1)
		{
	        $shelving_attribute = $this->_getConfig('pickpack_shelving_separated', $shelving_attribute_default, false, 'picks');
	        $shelvingX = intval($this->_getConfig('pickpack_shelving_separated_Xnudge', $shelvingX_default, false, 'picks'));
			
	        //for custom attribute 2
	        $shelving2_yn = $this->_getConfig('pickpack_shelving2_yn_separated', $shelving_yn_default, false, 'picks');
	     
		   	if(($shelving_yn == 1) && ($shelving2_yn == 1))
			{
		        $shelving2_attribute = $this->_getConfig('pickpack_shelving2_separated', $shelving_attribute_default, false, 'picks');
		        $shelving2X = intval($this->_getConfig('pickpack_shelving2_separated_Xnudge', $shelvingX_default, false, 'picks'));
				
		        //for custom attribute 3
		        $shelving3_yn = $this->_getConfig('pickpack_shelving3_yn_separated', $shelving_yn_default, false, 'picks');
			   
			   	if(($shelving_yn == 1) && ($shelving2_yn == 1) && ($shelving3_yn == 1))
				{
			        $shelving3_attribute = $this->_getConfig('pickpack_shelving3_separated', $shelving_attribute_default, false, 'picks');
			        $shelving3X = intval($this->_getConfig('pickpack_shelving3_separated_Xnudge', $shelvingX_default, false, 'picks'));
					
			        //for custom attribute 4
			        $shelving4_yn = $this->_getConfig('pickpack_shelving4_yn_separated', $shelving_yn_default, false, 'picks');
			        
				   	if(($shelving_yn == 1) && ($shelving2_yn == 1) && ($shelving3_yn == 1) && ($shelving4_yn == 1))
					{
						$shelving4_attribute = $this->_getConfig('pickpack_shelving4_separated', $shelving_attribute_default, false, 'picks');
				        $shelving4X = intval($this->_getConfig('pickpack_shelving4_separated_Xnudge', $shelvingX_default, false, 'picks'));
					}
				}
			}
		}
        //for combine attribute
        $combine_attribute_separated = $this->_getConfig('pickpack_combine_attribute_separated', $shelving_yn_default, false, 'picks');
        //$shelving4_attribute = $this->_getConfig('pickpack_shelving4_separated', $shelving_attribute_default, false, 'picks');
        $combine_attribute_separated_xpos = intval($this->_getConfig('pickpack_combine_attribute_separated_xpos', $shelvingX_default, false, 'picks'));
        
        $skuyn = $this->_getConfig('pickpack_sku_yn_separated', $namenudgeYN_default, false, 'picks');

        if ($skuyn == 1)
            $skuX = $this->_getConfig('pickpack_skunudge_separated', $namenudgeYN_default, false, 'picks');

        $nameyn = $this->_getConfig('pickpack_name_yn_separated', $namenudgeYN_default, false, 'picks');
        $namenudge = intval($this->_getConfig('pickpack_namenudge_separated', 0, false, 'picks'));
        $config_group = 'picks';
        $product_images_yn = $this->_getConfig('product_images_yn', 0, false, $config_group);

        if ($product_images_yn == 1) {
            $product_images_source = $this->_getConfig('product_images_source', 'thumbnail', false, $config_group);
            $product_images_parent_yn = $this->_getConfig('parent_image_yn', 0, false, $config_group);
            $col_title_product_images = explode(',', trim($this->_getConfig('col_title_product_images', ',150', false, $config_group)));
            $product_images_border_color_temp = strtoupper(trim($this->_getConfig('product_images_border_color', '#CCCCCC', false, $config_group)));
            $product_images_border_color = new Zend_Pdf_Color_Html($product_images_border_color_temp);
            $product_images_maxdimensions = explode(',',$this->_getConfig('product_images_maxdimensions', '50,50', false, $config_group));
            if ($product_images_maxdimensions[0] == '' || $product_images_maxdimensions[1] == '') {
                if ($product_images_maxdimensions[0] == '') 
					$product_images_maxdimensions[0] = NULL;
                if ($product_images_maxdimensions[1] == '') 
					$product_images_maxdimensions[1] = NULL;
                if ($product_images_maxdimensions[0] == NULL && $product_images_maxdimensions[1] == NULL) 
					$product_images_maxdimensions[0] = 50;
            }
            $product_images_source_res = $product_images_source;
            if ($product_images_source == 'gallery') 
				$product_images_source_res = 'image';
        }
         $show_qty_options = $this->_getConfig('show_qty_options', 1, false,'picks', $store_id);

        if($show_qty_options == 2){
            $skuX = $skuX + 40;
            $namenudge = $namenudge + 50;
        }
        $media_path = Mage::getBaseDir('media');

        $columns_xpos_array = array();

        if($nameyn == 1) {   
            $name_trim_wrap = $this->_getConfig('name_trim_wrap', 'trim', false, 'picks');
            $columns_xpos_array['Name'] = $namenudge;
        }

        if($product_images_yn == 1)
            $columns_xpos_array['Image'] = isset($col_title_product_images[1]) ? $col_title_product_images[1] : null;

        if($skuyn == 1)
            $columns_xpos_array['Sku'] = $skuX;
        
        if($address_label_yn == 1)
            $columns_xpos_array['label'] = $padded_right - ($label_width + 5) + $nudge_shipping_address[0] - 5;
       
	    if($stockcheck_yn == 1)        
           $columns_xpos_array['Stock'] = $stockcheck; 
        
        asort($columns_xpos_array);

        $override_address_format_yn = 1;
        $address_format = $this->_getConfig('address_format', $address_format_default, false, 'general'); //col/sku
        $address_countryskip = $this->_getConfig('address_countryskip', 0, false, 'general');

        $shmethod = Mage::getStoreConfig('pickpack_options/picks/pickpack_shipmethod');
        $warehouseyn = Mage::getStoreConfig('pickpack_options/picks/pickpack_warehouse');
        $giftwrapyn = Mage::getStoreConfig('pickpack_options/picks/pickpack_giftwrap');
        $sku_warehouse = array();
        $sku_giftwrap = array();

        $options_yn_base = $this->_getConfig('separated_options_yn_base', 0, false, 'picks'); // no, inline, newline
        
        $pickpack_options_filter = '';
        $pickpack_options_count_filter_attribute = 'separated_options_count_filter';
        $pickpack_options_filter_yn = 0;

        if ($options_yn_base == 0)
            $options_yn = 0;
        elseif ($options_yn_base == 'yesstacked') {
            $options_yn = $this->_getConfig('separated_options_yn_stacked', 0, false, 'picks'); // no, inline, newline
            $pickpack_options_filter_yn = $this->_getConfig('separated_options_filter_yn_stacked', 0, false, 'picks');
            $pickpack_options_filter = trim($this->_getConfig('separated_options_filter_stacked', '', false, 'picks'));
            $pickpack_options_count_filter_attribute = 'separated_options_count_filter_stacked';
        } else {
            $options_yn = $this->_getConfig('separated_options_yn', 0, false, 'picks'); // no, inline, newline
            $pickpack_options_filter_yn = $this->_getConfig('separated_options_filter_yn', 0, false, 'picks');
            $pickpack_options_filter = trim($this->_getConfig('separated_options_filter', '', false, 'picks'));
        }

        $product_qty_upsize_yn = $this->_getConfig('product_qty_upsize_yn', 1, false, 'picks');
        $product_qty_rectangle = 0;
        if ($product_qty_upsize_yn == '1' || $product_qty_upsize_yn == '1') {
            if ($product_qty_upsize_yn == '1')
            	$product_qty_rectangle = 1;
			$product_qty_upsize_yn  = 1;
        }

        $pickpack_options_filter_array = array();
        $pickpack_options_count_filter_array = array();

        if ($pickpack_options_filter_yn == 0) 
			$pickpack_options_filter = '';
        elseif ($pickpack_options_filter == '' && $pickpack_options_filter_yn == 1) $pickpack_options_filter_yn = 0; elseif ($pickpack_options_filter_yn == 1) {
            $pickpack_options_filter_array = explode(',', $pickpack_options_filter);
            foreach ($pickpack_options_filter_array as $key => $value) {
                $pickpack_options_filter_array[$key] = trim($value);
            }
            $pickpack_options_count_filter = $this->_getConfig($pickpack_options_count_filter_attribute, 0, false, 'picks');

            if (trim($pickpack_options_count_filter) != '') {
                $pickpack_options_count_filter_array = explode(',', $pickpack_options_count_filter);
                foreach ($pickpack_options_count_filter_array as $key => $value) {
                    $pickpack_options_count_filter_array[$key] = trim($value);
                }
            }
        }

        $sort_packing_yn = $this->_getConfig('sort_packing_yn', 1, false, 'general');
        $sort_packing = $this->_getConfig('sort_packing', 'sku', false, 'general');
        $sortorder_packing = $this->_getConfig('sort_packing_order', 'ascending', false, 'general');

        if ($sort_packing == 'attribute') {
            $sort_packing_attribute = trim($this->_getConfig('sort_packing_attribute', '', false, 'general'));
            if ($sort_packing_attribute != '') 
				$sort_packing = $sort_packing_attribute;
        }
        if ($sort_packing_yn == 0) 
			$sortorder_packing = 'none';

        $skuXInc = 0;
        $storeId = Mage::app()->getStore()->getId();

        $order_id_master = array();
        $sku_order_suppliers = array();
        $sku_shelving = array();
        $sku_shelving2 = array();
        $sku_shelving3 = array();
        $sku_shelving4 = array();
        $sku_shipping_address = array();
        $sku_order_id_options = array();
        $sku_bundle = array();
        $product_build_item = array();
        $product_build = array();
        $order_count = 0;
        $store_id_arr = array();
		
        foreach ($orders as $orderSingle) {
            $order = $helper->getOrder($orderSingle);
            $putOrderId = $order->getRealOrderId();
            $order_id = $putOrderId;
            $store_id_arr[$order_id] = $order->getStoreId();
            $store_id_arr[$orderSingle] = $order->getStoreId();
            
            $order_count ++;
            $has_shipping_address = false;
            $has_billing_address = false;
            foreach ($order->getAddressesCollection() as $address) {
                if ($address->getAddressType() == 'shipping' && !$address->isDeleted())
                    $has_shipping_address = true;
				elseif ($address->getAddressType() == 'billing' && !$address->isDeleted())
                    $has_billing_address = true;
            }

            $sku_shipping_address_temp = '';
            $sku_shipping_address[$order_id] = '';
            $shippingAddressArray = array();
            $address_format_set = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $address_format);
            //TODO shipping address
            if ($has_shipping_address === true) {
                $shipping_address = $this->getShippingAddressOrder($order);
                $shipping_address_label[$order_id] = $shipping_address;
                if (trim($address_countryskip) != ''){
                    $shipping_address['country'] = str_ireplace($address_countryskip, '', $shipping_address['country']);
					
                    if(is_array($address_countryskip) && (strtolower(trim($address_countryskip)) == "singapore" || strtolower(trim($address_countryskip)) =="monaco"))
                        $shipping_address['city'] = str_ireplace($address_countryskip, '', $shipping_address['city']);
                }
                foreach ($shipping_address as $key => $value) {
                    $value = Mage::helper('pickpack/functions')->clean_method($value, 'pdf');
                    $address_format_set = $this->getAddressFormatByValue($key, $value, $address_format_set);
                }

                $address_format_set = str_replace(array('||', '|'), "\n", trim($address_format_set));
                $address_format_set = str_replace(array('{if city}', '{if postcode}', '{if region}', '{if firstname}', '{firstname}', '{/if firstname}', '{if lastname}', '{lastname}', '{/if lastname}','{if street1}','{if street2}','{/if street1}','{/if street2}'), '', $address_format_set);

                $shippingAddressArray = explode("\n", $address_format_set);
                $sku_shipping_address[$order_id] = $this->addressPrintLine($shippingAddressArray, $black_color, $page, $sku_shipping_address_temp);
                //
            }
            //TODO billing address
            $sku_billing_address[$order_id] = '';
            $billingAddressArray = array();
            $sku_billing_address_temp = '';
            if ($has_billing_address === true) {
                $billing_address = $this->getBillingAddressOrder($order);
                if (trim($address_countryskip) != ''){
                    $billing_address['country'] = str_ireplace($address_countryskip, '', $billing_address['country']);

                    if(!is_array($address_countryskip) && (strtolower(trim($address_countryskip)) == "singapore" || strtolower(trim($address_countryskip)) =="monaco"))
                        $billing_address['city'] = str_ireplace($address_countryskip, '', $billing_address['city']);
                }
                foreach ($billing_address as $key => $value) {
                    $address_format_set = $this->getAddressFormatByValue($key, $value, $address_format_set);
                }

                $address_format_set = str_replace(array('||', '|'), "\n", trim($address_format_set));
                $address_format_set = str_replace(array('{if city}', '{if postcode}', '{if region}', '{if firstname}', '{firstname}', '{/if firstname}', '{if lastname}', '{lastname}', '{/if lastname}'), '', $address_format_set);
                $billingAddressArray = explode("\n", $address_format_set);
                $sku_billing_address[$order_id] = $this->addressPrintLine($billingAddressArray, $black_color, $page, $sku_billing_address_temp);
            }
            if (!isset($order_id_master[$order_id])) 
				$order_id_master[$order_id] = 0;

            //Order gift messages
            if ($giftmessage_yn == 1) {
                $gift_message_item = Mage::getModel('giftmessage/message');
                $gift_message_id = $order->getGiftMessageId();
                $gift_message_yn = "yes";
                $giftWrap_info = array();
                $gift_message_array = array();
                $gift_msg_array[$order_id] = $this->getOrderGiftMessage($gift_message_id,$gift_message_yn, $gift_message_item, $giftWrap_info, $gift_message_array);
            }
            $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
            $max_name_length = 0;
            $test_name = 'abcdefghij'; //10
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $test_name_length = round($this->parseString($test_name, $font_temp, ($generalConfig['font_size_body']))); //*0.77)); // bigger = left

            $pt_per_char = ($test_name_length / 10);
            $max_name_length = (550 - $skuX);
            if (!isset($productXInc)) 
				$productXInc = 0;
            $max_sku_length = (($productX + $productXInc) - 27 - $skuX);
            $character_breakpoint_name = round($max_name_length / $pt_per_char);
            $character_breakpoint_sku = round($max_sku_length / $pt_per_char);
            $sku_category = array();

            $coun = 1;

            foreach ($itemsCollection as $item) {
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    // any products actually go thru here?
                    $sku = $item->getProductOptionByCode('simple_sku');
                    $product_id = Mage::getModel('catalog/product')->setStoreId($storeId)->getIdBySku($sku);
                } else {
                    $sku = $item->getSku();
                    $product_id = $item->getProductId(); // get it's ID
                }
                #nu
                // if show options as counted groups
                if ($options_yn == 1) {
                    $full_sku = trim($item->getSku());
                    $parent_sku = $full_sku; 
                    $sku = $parent_sku;
                    $product_build_item[] = $sku; 
                    $product_sku = $sku;
                    $product_build[$order_id][$sku]['sku'] = $product_sku;
                    $product_build[$order_id][$sku]['display_sku'] = $product_sku;
                } else {
                    // unique item id
                    $product_build_item[] = $sku . '-' . $coun;
                    $product_sku = $sku;
                    $sku = $sku . '-' . $coun;
                    $product_build[$order_id][$sku]['sku'] = $product_sku;
                    $product_build[$order_id][$sku]['display_sku'] = $product_sku;
                }
                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku', $sku, array('cost', $shelving_attribute, 'name', 'simple_sku', 'qty'));
                if ($stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) 
					$stock = round($stock); //Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());

                $options = $item->getProductOptions();

                if (isset($options['bundle_options']) && is_array($options['bundle_options'])) {
                    $children = $item->getChildrenItems();
                    if (count($children)) {
                        foreach ($children as $child) {
                            $product_child = $helper->getProductForStore($child->getProductId(), $storeId);
                            $sku_b = $child->getSku();
                            $price_b = $child->getPriceInclTax();
                            $qty_b = (int)$child->getQtyOrdered();
                            if($store_view=="storeview")
                                $name_b = $child->getName();
                            elseif($store_view == "specificstore" && $specific_store_id != ""){
                                $_product = $helper->getProductForStore($child->getProductId(), $specific_store_id);
                                if ($_product->getData('name')) 
									$name_b = trim($_product->getData('name'));
                                if ($name_b == '') 
									$name_b = trim($child->getName());
                            }
                            else
                                $name_b = $this->getNameDefaultStore($child);
                            $childProductId = $child->getProductId();
                            $this->y -= 10;
                            $offset = 20;

                            $shelving_real = '';
                            $shelving_real_b = '';
                            $shelving2_real_b = '';
                            $shelving3_real_b = '';
                            $shelving4_real_b = '';

                            if (isset($shelving_yn) && $shelving_yn == 1)
                                $shelving_real_b = $this->getCustomAttributeBundle($product_child, $shelving_attribute, $shelving_real_b);
                            
                            //TO DO custom attribute 2 bundle product
                            if (isset($shelving2_yn) && $shelving2_yn == 1)
                                $shelving2_real_b = $this->getCustomAttributeBundle($product_child, $shelving2_attribute, $shelving2_real_b);

                            //TO DO custom attribute 3 bundle product
                            if (isset($shelving3_yn) && $shelving3_yn == 1)
                                $shelving3_real_b = $this->getCustomAttributeBundle($product_child, $shelving3_attribute, $shelving3_real_b);

                            //TO DO custom attribute 4 bundle product
                            if (isset($shelving4_yn) && $shelving4_yn == 1)
                                $shelving4_real_b = $this->getCustomAttributeBundle($product_child, $shelving4_attribute, $shelving4_real_b);
                            
                            if ($from_shipment == 'shipment') {
                                $qty_string_b = 's:' . (int)$item->getQtyShipped() . ' / o:' . $qty;
                                $price_qty_b = (int)$item->getQtyShipped();
                                $productXInc = 25;
                            } else {
                                $qty_string_b = $qty_b;
                                $price_qty_b = $qty_b;
                                $productXInc = 0;
                            }
                            $display_name_b = '';
                            if (strlen($name_b) > ($character_breakpoint_name + 2))
                                $display_name_b = substr(htmlspecialchars_decode($name_b), 0, ($character_breakpoint_name)) . '…';
							else 
								$display_name_b = htmlspecialchars_decode($name_b);

                            $sku_bundle[$order_id][$sku][] = $sku_b . '##' . $display_name_b . '##' . $shelving_real_b . '##' . $qty_string_b . '##' . $childProductId . '##' . $shelving2_real_b;
                        }
                    }
                }
                else {
                    $sku_bundle[$order_id][$sku] = '';
                }


                $category_label = '';
                $product = $helper->getProduct($product_id);
                $catCollection = $product->getCategoryCollection();

                $categsToLinks = array();
                # Get categories names
                foreach ($catCollection as $cat) {
                    if ($cat->getName() != '')
                        $categsToLinks[] = $cat->getName();
                }
                $category_label = implode(', ', $categsToLinks);
                $sku_category[$sku] = $category_label;
                unset($category_label);


                $shelving = '';
                $supplier = '';
                $extra = '';

                /**
                images PRELOADER start
                 */
                $has_shown_product_image = 0;
				// ie only get sku image paths if not previously got in this combined request
                if (($product_images_yn == 1) && !isset($sku_image_paths[$sku]['path'][0]))  {
                    $imagePaths = array();
                    if($product_images_parent_yn == 1)
                        $product_id = Mage::helper("pickpack")->getParentProId($product_id);
                    $product_images_source_res = $helper->getSourceImageRes($product_images_source, $product_id);
                    $img_demension = $helper->getWidthHeightImage($product_id, $product_images_source_res, $product_images_maxdimensions);
                    if(is_array($img_demension) && count($img_demension) > 1){
                        $sku_image_paths[$sku]['width'] = $img_demension[0];
                        $sku_image_paths[$sku]['height'] = $img_demension[1];
                    }
                    $imagePaths = $helper->getImagePaths($product_id, $product_images_source , $product_images_maxdimensions);
                    $imagePath = '';
                    
                    if(count($imagePaths) == 0)
                    {
                        $product_images_source_res = $product_images_source;
                        if ($image_product = $helper->getProduct($product_id))
                        {
                            $image_path = $image_product->getImage();
                            $image_parent_sku = $image_product->getSku();
                            $has_real_image_set = ($image_path != null && $image_path != "no_selection" && $image_path != '');
                            $image_product_id = $product_id;
                            if (($has_real_image_set !== true) && (is_object($image_product)) && ($image_product->isConfigurable() === false)) // if is child (not parent)
                            {
                                $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product_id);
                                if (is_array($parent_ids)) {
                                    foreach ($parent_ids as $key => $parent_id) {
                                        if ($image_product = $helper->getProduct($parent_id)) {
                                            $image_path = $image_product->getImage();
                                            $image_parent_sku = $image_product->getSku();
                                            $image_product_id = $parent_id;
                                            $has_real_image_set = ($image_path != null
                                                && $image_path != "no_selection"
                                                && $image_path != ''
                                                && (strpos($image_path, 'placeholder') === false));
                                        }
                                    }
                                }
                            }
                            if (($product_images_source_res == 'thumbnail') && (!$image_product->getThumbnail() || ($image_product->getThumbnail() == 'no_selection'))) $product_images_source_res = 'image';
                            elseif (($product_images_source_res == 'small_image') && (!$image_product->getSmallImage() || ($image_product->getSmallImage() == 'no_selection'))) $product_images_source_res = 'image';
                            if (($product_images_source_res == 'image') && (!$image_product->getImage() || ($image_product->getImage() == 'no_selection'))) $product_images_source_res = 'small_image';
                            if (($product_images_source_res == 'small_image') && (!$image_product->getSmallImage() || ($image_product->getSmallImage() == 'no_selection'))) $product_images_source_res = 'thumbnail';
                            $image_galleries = $image_product->getData('media_gallery');
                            
                            if(isset($image_galleries['images']))
                            {
                                if(count($image_galleries['images']) > 0)
                                {
                                    if ($image_product->getData($product_images_source_res) != 'no_selection') // continue; // if no images are valid, skip it
                                    {
                                        $image_obj = Mage::helper('catalog/image')->init($image_product, $product_images_source_res);
                                        if (isset($image_obj)) {
                                            $img_width = $product_images_maxdimensions[0];
                                            $img_height = $product_images_maxdimensions[1];

                                            $orig_img_width = $image_obj->getOriginalWidth();
                                            $orig_img_height = $image_obj->getOriginalHeigh(); // getOriginalHeigh() = spell mistake
                                            if ($orig_img_width != $orig_img_height) {
                                                if ($orig_img_width > $orig_img_height)
                                                    $img_height = ceil(($orig_img_height / $orig_img_width) * $product_images_maxdimensions[1]);
												elseif ($orig_img_height > $orig_img_width)
                                                    $img_width = ceil(($orig_img_width / $orig_img_height) * $product_images_maxdimensions[0]);
                                            }
                                            if (is_integer($img_width))
												$resize_x = ($img_width * 4);
                                            if (is_integer($img_height))
												$resize_y = ($img_height * 4);

                                            $image_placeholder_height = ($y2 - $y1);
                                            $sku_image_paths[$sku]['width'] = $img_width;
                                            $sku_image_paths[$sku]['height'] = $img_height;

                                            // product_images_source = $thumbnail, small_image, image, gallery
                                            if ($product_images_source == 'gallery') {
                                                $gallery = $helper->getProduct($image_product_id)->getMediaGalleryImages();
                                                $image_urls = array();
                                                foreach ($gallery as $image) {
                                                    $imagePath_temp = Mage::helper('catalog/image')->init($image_product, 'image', $image->getFile())
                                                        ->constrainOnly(TRUE)
                                                        ->keepAspectRatio(TRUE)
                                                        ->keepFrame(FALSE)
                                                        ->resize($resize_x, $resize_y)
                                                        ->__toString();

                                                    if (strpos($imagePath_temp, 'placeholder') === false) $imagePaths[] = $imagePath_temp;
                                                }
                                            } 
                                            else {
                                                $imagePath_temp = Mage::helper('catalog/image')->init($image_product, $product_images_source_res)
                                                    ->constrainOnly(TRUE)
                                                    ->keepAspectRatio(TRUE)
                                                    ->keepFrame(FALSE)
                                                    ->resize($resize_x, $resize_y)
                                                    ->__toString();

                                                if (strpos($imagePath_temp, 'placeholder') === false) $imagePaths[] = $imagePath_temp;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                     
                    foreach ($imagePaths as $imagePath) {
                        $image_url = $imagePath;
                        $image_url_after_media_path_with_media = strstr($image_url, '/media/'); 
                        $image_url_after_media_path = strstr_after($image_url, '/media/');
                        $final_image_path = $media_path . '/' . $image_url_after_media_path;
                        $sku_image_paths[$sku]['path'][] = $final_image_path;
                    }
                }
               
                /**
                images PRELOADER end
                 */
                //custom attribute 1
                if ($shelving_yn == 1)
                    $sku_shelving[$sku] = $this->getCustomAttribute($product,$shelving_attribute, $sku_shelving, $sku);
                
                //custom attribute 2
                if ($shelving2_yn == 1)
                    $sku_shelving2[$sku] = $this->getCustomAttribute($product,$shelving2_attribute, $sku_shelving2, $sku);

                //custom attribute 3
                if ($shelving3_yn == 1)
                    $sku_shelving3[$sku] = $this->getCustomAttribute($product,$shelving3_attribute, $sku_shelving3, $sku);

				//custom attribute 4
                if ($shelving4_yn == 1)
                    $sku_shelving4[$sku] = $this->getCustomAttribute($product,$shelving4_attribute, $sku_shelving4, $sku);
                
                if ($sort_packing != 'none' && $sort_packing != '') {                    
                    $attributeName = $sort_packing;
                    if ($attributeName == 'Mcategory') {
                        $product_build[$order_id][$sku][$sort_packing] = '';
                        $product_build[$order_id][$sku][$sort_packing] = $sku_category[$sku]; //$product_build[$sku]['%category%'];//$category_label;
                    } else {
                        $product = $helper->getProduct($product_id);

                        if ($product->getData($attributeName)) {
                            $product_build[$order_id][$sku][$sort_packing] = Mage::helper('pickpack')->getProductAttributeValue($product, $attributeName, false);
                        }
                    }
                    unset($attributeName);
                    unset($attribute);
                    unset($attributeOptions);
                    unset($result);
                }
                if ($split_supplier_yn != 'no') {
                    
                    //Innoexts_Warehouse
                    $is_warehouse_supplier = 0;
                    if((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse'))) {
                        if($supplier_attribute == 'warehouse')
                            $is_warehouse_supplier = 1;
                    }
                    if($is_warehouse_supplier == 1) {
                        $warehouse = $item->getWarehouse();
                        $warehouse_code = $warehouse->getData('code');
                        $supplier = $warehouse_code;
                        $warehouse_code = trim(strtoupper($supplier));
                        $this->warehouse_title[$warehouse_code] = $item->getWarehouseTitle();
                    }
                    else {
	                    $_newProduct = $helper->getProductForStore($product_id, $storeId);
	                    if ($_newProduct) {
	                        if ($_newProduct->getData($supplier_attribute)) 
								$supplier = $_newProduct->getData('' . $supplier_attribute . '');
	                    } elseif ($product->getData('' . $supplier_attribute . ''))
	                        $supplier = $product->getData($supplier_attribute);

	                    if ($_newProduct->getAttributeText($supplier_attribute))
	                        $supplier = $_newProduct->getAttributeText($supplier_attribute);
	                    elseif ($product[$supplier_attribute]) 
							$supplier = $product[$supplier_attribute];
                    }

                    if (is_array($supplier)) 
						$supplier = implode(',', $supplier);
                    if (!$supplier) 
						$supplier = '~Not Set~';

                    $supplier = trim(strtoupper($supplier));

                    if (isset($sku_supplier[$sku]) && $sku_supplier[$sku] != $supplier) 
						$sku_supplier[$sku] .= ',' . $supplier;
                    else 
						$sku_supplier[$sku] = $supplier;
                    $sku_supplier[$sku] = preg_replace('~,$~', '', $sku_supplier[$sku]);

                    if (!isset($supplier_master[$supplier]))
						$supplier_master[$supplier] = $supplier;

                    $order_id_master[$order_id] .= ',' . $supplier;
                }
                if ($shmethod == 1) {
                    $sku_shipping_method[$order_id] = Mage::helper('pickpack/functions')->clean_method($order->getShippingDescription(), 'shipping');
                    if ($sku_shipping_method[$order_id] != '') 
						$sku_shipping_method[$order_id] = $sku_shipping_method[$order_id];
                    $shipping_method_x = 200;
                    $max_shipping_description_length = 41; //37
                    if (strlen($sku_shipping_method[$order_id]) > $max_shipping_description_length)
                        $sku_shipping_method[$order_id] = trim(substr(htmlspecialchars_decode($sku_shipping_method[$order_id]), 0, ($max_shipping_description_length)) . '…');
                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $font_size_compare = ($generalConfig['font_size_subtitles'] - 2);
                    $line_width = $this->parseString($sku_shipping_method[$order_id], $font_temp, $font_size_compare); // bigger = left                  
                }       
                if ($warehouseyn == 1) {
                    if(Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse'))
                    {
                        $warehouse_helper =  Mage::helper('warehouse');
                        $warehouse_collection = Mage::getSingleton('warehouse/warehouse')->getCollection();
                        $resource = Mage::getSingleton('core/resource');
                        /**
                         * Retrieve the read connection
                         */
                        $readConnection = $resource->getConnection('core_read');
                        $query = 'SELECT stock_id FROM ' . $resource->getTableName("warehouse/order_grid_warehouse").' WHERE entity_id='.$order->getData('entity_id');
                        $warehouse_stock_id = $readConnection->fetchOne($query);
                        if($warehouse_stock_id) {
                            $warehouse = $warehouse_helper->getWarehouseByStockId($warehouse_stock_id);
                            $warehouse_title = ($warehouse->getData('title'));
                        }
                        else
                            $warehouse_title = '';
                    }
                    else
                        $warehouse_title = '';
					
                    $sku_warehouse[$order_id] = $warehouse_title; 
                    $warehouse_x = 420;
                    if ($barcodes == 1)
						$warehouse_x = 490;
                    
                }
                
                if ($giftwrapyn == 1) {
                    if (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') || Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
                        if (Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
                            $quoteId=$order->getQuoteId();                    
                            $giftwrapCollection = array();
                            if ($quoteId) {
                                $giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
                                foreach ($giftwrapCollection as $info_collection) {
                                    $giftWrap_info['message'] .= "\n".$info_collection['giftwrap_message'];
                                    $style_gift = Mage::getModel('giftwrap/giftwrap')->load($info_collection['styleId']);                                   
                                        $giftWrap_info['wrapping_paper'] .=$style_gift->getData('title');                                   
                                        $giftWrap_info['style'] .=$style_gift->getData('title');
                                }
                            }
                            $giftWrapInfos = Mage::getModel('giftwrap/giftwrap')
                                ->getCollection()
                                ->addFieldToFilter('store_id', '0'); 
                            
                            foreach ($giftWrapInfos as $info) {
                                $giftWrap_info['wrapping_paper'] .= str_ireplace(array('.jpg', '.jpeg', '.gif', '.png'), '', $info->getData('image'));
                            }
                        } 
                        elseif (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') && (Mage::getModel('giftwrap/order'))) {
                            $orderId = $order->getId();
                            $giftWrapInfos = Mage::getModel('giftwrap/order')->getCollection()->addFieldToFilter('order_id', $orderId); //
                            foreach ($giftWrapInfos as $info) {
                                $giftWrap_info['message'] .= $info->getData('message');
                                if (isset($giftWrap_info['wrapping_paper'])) 
									$giftWrap_info['wrapping_paper'] .= ' | ';
                                $giftWrap_info['wrapping_paper'] .= trim(str_ireplace(array('xmage_giftwrap/', '.jpg', '.jpeg', '.gif', '.png'), '', $info->getData('giftbox_image')));
                            }
                        }
                        unset($giftWrapInfos);
                        if(!(isset($giftWrap_info['wrapping_paper'])))
                            $giftWrap_info['wrapping_paper'] = '';
                        
                        $sku_giftwrap[$order_id] = $giftWrap_info;//['wrapping_paper'];
                        unset($giftWrap_info);
                    }
                    
					$giftwrap_x = 320;
                    if ($barcodes == 1) 
						$giftwrap_x = 470;
                    
                }

                //Option to show qty
                $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int)$item->getQtyOrdered();
                $sqty = $item->getIsQtyDecimal() ? $item->getQtyShipped() : (int)$item->getQtyShipped();
                $iqty = $item->getIsQtyDecimal() ? $item->getData('qty_invoiced') : (int)$item->getData('qty_invoiced');
                if (isset($sku_qty[$sku]))
					$sku_qty[$sku] = ($sku_qty[$sku] + $qty);
                else
					$sku_qty[$sku] = $qty;
                $total_quantity = $total_quantity + $qty;

                $cost = $qty * (is_object($product) ? $product->getCost() : 0);
                $total_cost = $total_cost + $cost;

                $sku_master[$sku] = $sku;              
                if ($configurable_names == 'simple')
                {
                    switch ($store_view) {
                        case 'itemname':
                            $_newProduct =$helper->getProduct($product_id);
                            $name = trim($item->getName());
                            break;
                        case 'default':
                            $_newProduct = $helper->getProduct($product_id);
                            if ($_newProduct->getData('name'))
								$name = trim($_newProduct->getData('name'));
                            if ($name == '')
								$name = trim($item->getName());
                            break;
                        case 'storeview':
                            $_newProduct = $helper->getProductForStore($product_id, $storeId);
                            if ($_newProduct->getData('name'))
								$name = trim($_newProduct->getData('name'));
                            if ($name == '')
								$name = trim($item->getName());
                            break;
                        case 'specificstore':
                            $_newProduct = $helper->getProductForStore($product_id,$specific_store_id);
                            if ($_newProduct->getData('name'))
								$name = trim($_newProduct->getData('name'));
                            if ($name == '')
								$name = trim($item->getName());
                            break;
                        default:
                            $_newProduct =$helper->getProduct($product_id);
                            if ($_newProduct->getData('name'))
								$name = trim($_newProduct->getData('name'));
                            if ($name == '')
								$name = trim($item->getName());
                            break;
                    }
                } else {
                    if($store_view=="storeview")
                        $name = trim($item->getName());
                    elseif($store_view == "specificstore" && $specific_store_id != "") {
                        $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
                        if ($_newProduct->getData('name'))
							$name = trim($_newProduct->getData('name'));
                        if ($sku_name[$sku] == '')
							$name = trim($item->getName());
                    }
                    else
                        $name = $this->getNameDefaultStore($item);
                }
                $sku_name[$sku] = $name;

                if(isset($options['additional_options']) && is_array($options['additional_options']))
                    $options['options'] = $options['additional_options'];
                elseif(isset($options['attributes_info']) && is_array($options['attributes_info']))
					$options['options'] = $options['attributes_info'];

                if (isset($options['options']) && is_array($options['options'])) {
                    $i = 0;
                    if (isset($options['options'][$i])) 
						$continue = 1;
                    while ($continue == 1) {
                        $options_name_temp[$i] = trim(htmlspecialchars_decode($options['options'][$i]['label'] . ' : ' . $options['options'][$i]['value']));
                        $options_name_temp[$i] = preg_replace('~^select ~i', '', $options_name_temp[$i]);
                        $options_name_temp[$i] = preg_replace('~^enter ~i', '', $options_name_temp[$i]);
                        $options_name_temp[$i] = preg_replace('~^would you Like to ~i', '', $options_name_temp[$i]);
                        $options_name_temp[$i] = preg_replace('~^please enter ~i', '', $options_name_temp[$i]);
                        $options_name_temp[$i] = preg_replace('~^your ~i', '', $options_name_temp[$i]);
                        $options_name_temp[$i] = preg_replace('~\((.*)\)~i', '', $options_name_temp[$i]);
                        $_eachOption = $options['options'][$i];
                        if(isset($_eachOption['option_value']))
                            $option_value = $_eachOption['option_value'];
                        else
                            $option_value = $_eachOption['value'];
                        $objModel = Mage::getModel('catalog/product_option_value')->load($option_value);
                        $options['options'][$i]["sku"] = $objModel->getSku();
                        unset($objModel);
                        $i++;
                        $continue = 0;
                        if (isset($options['options'][$i])) 
							$continue = 1;
                    }
                    $i = 0;
                    $continue = 0;
                    $opt_count = 0;
                    if (isset($options['options'][$i])) 
						$continue = 1;
                    $sku_order_id_options[$order_id][$sku] = '';
                    $sku_order_id_options_sku[$order_id][$sku] = '';
                    while ($continue == 1)
                    {
                        if($i > 0) 
							$sku_order_id_options[$order_id][$sku] .= ' ';
                        $options_store = $this->getOptionProductByStore($store_view, $helper, $product_id, $storeId, $specific_store_id, $options, $i);
                        $options['options'][$i]['label'] = $options_store['label'];
                        $options['options'][$i]['value'] = $options_store['value'];
                        $sku_order_id_options[$order_id][$sku] .= htmlspecialchars_decode('[ ' . $options['options'][$i]['label'] . ' : ' . $options['options'][$i]['value'] . ' ]');
                        $sku_order_id_options_sku[$order_id][$sku] .= htmlspecialchars_decode('[ ' . $options['options'][$i]['sku'] . ' ]');
						
                        // if show options as a group
                        if ($options_yn != 0 && $opt_count == 0 && $options_yn_base == 1) {
                            $full_sku = trim($item->getSku());
                            $parent_sku = preg_replace('~\-(.*)$~', '', $full_sku);
                            $full_sku = preg_replace('~^' . $parent_sku . '\-~', '', $full_sku);
                            $options_sku_array = array();
                            $options_sku_array = explode('-', $full_sku);
							
                            if (!isset($options_sku_parent[$order_id])) 
								$options_sku_parent[$order_id] = array();
                            if (!isset($options_sku_parent[$order_id][$sku])) 
								$options_sku_parent[$order_id][$sku] = array();

                            $opt_count = 0;
                            foreach ($options_sku_array as $k => $options_sku_single) {
                                if (!isset($options_sku_parent[$order_id][$sku][$options_sku_single])) 
									$options_sku_parent[$order_id][$sku][$options_sku_single] = '';

                                if (!isset($options_name_temp[$opt_count])) 
									$options_name_temp[$opt_count] = '';
								
                                $options_name[$order_id][$sku][$options_sku_single] = implode(' ; ',$options_name_temp);
                                if (isset($options_sku[$order_id][$options_sku_single]) && (!in_array($options_sku_single, $pickpack_options_filter_array))) {
                                    $options_sku[$order_id][$options_sku_single] = ($sku_qty[$sku] + $options_sku[$order_id][$options_sku_single]);
                                    $options_sku_parent[$order_id][$sku][$options_sku_single] = ($qty + $options_sku_parent[$order_id][$sku][$options_sku_single]);
                                } elseif (!in_array($options_sku_single, $pickpack_options_filter_array)) {
                                    $options_sku[$order_id][$options_sku_single] = ($sku_qty[$sku]);
                                    $options_sku_parent[$order_id][$sku][$options_sku_single] = $qty;
                                }

                                $opt_count++;
                            }
                            unset($options_name_temp);
                        }
                        $i++;
                        $continue = 0;
                        if (isset($options['options'][$i])) $continue = 1;
                    }
                }
                unset($options_name_temp);

                //TODO Moo continue here

                $sku_stock[$sku] = $stock;

                if (isset($sku_order_id_qty[$order_id][$sku])) {
                    $sku_order_id_qty[$order_id][$sku] = ($sku_order_id_qty[$order_id][$sku] + $qty);
                    $sku_order_id_sqty[$order_id][$sku] = ($sku_order_id_sqty[$order_id][$sku] + $sqty);
                    $sku_order_id_iqty[$order_id][$sku] = ($sku_order_id_iqty[$order_id][$sku] + $iqty);
                    $sku_order_id_sku[$order_id][$sku] = $product_sku;
                    $sku_order_id_product_id[$order_id][$sku] = $product_id;
                    $product_build[$order_id][$sku]['qty'] = ($sku_order_id_qty[$order_id][$sku] + $qty);
                    $product_build[$order_id][$sku]['sqty'] = ($sku_order_id_sqty[$order_id][$sku] + $sqty);
                    $product_build[$order_id][$sku]['id'] = $product_id;
                }
                else {
                    $sku_order_id_qty[$order_id][$sku] = $qty;
                    $sku_order_id_sqty[$order_id][$sku] = $sqty;
                    $sku_order_id_iqty[$order_id][$sku] = $iqty;
                    $sku_order_id_sku[$order_id][$sku] = $product_sku;
                    $sku_order_id_product_id[$order_id][$sku] = $product_id;

                    $product_build[$order_id][$sku]['qty'] = $qty;
                    $product_build[$order_id][$sku]['sqty'] = $sqty;
                    $product_build[$order_id][$sku]['id'] = $product_id;
                }
                // Product gift message
                $max_chars_message = $this->getMaxCharMessage($padded_right, $generalConfig['font_size_options'], $font_temp);
                $product_build[$order_id][$sku]['has_message'] = 0;
                if (Mage::helper('giftmessage/message')->getIsMessagesAvailable('order_item', $item) && $item->getGiftMessageId()) {
                    $product_build[$order_id][$sku]['has_message'] = 1;
                    $item_msg_array = $this->getItemGiftMessageSeprated($item,$max_chars_message, $message_title_tofrom_yn);
                    $product_build[$order_id][$sku]['message-content'] = $item_msg_array;
                }
                if (!isset($max_qty_length)) 
					$max_qty_length = 2;
                if (strlen($sku_order_id_qty[$order_id][$sku]) > $max_qty_length) 
					$max_qty_length = strlen($sku_order_id_qty[$order_id][$sku]);
                if (strlen($sku_order_id_sqty[$order_id][$sku]) > $max_qty_length) 
					$max_qty_length = strlen($sku_order_id_sqty[$order_id][$sku]);


                if ($split_supplier_yn != 'no') {
                    if (!isset($sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
                    elseif (!in_array($supplier, $sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
                }
                $coun++;
            }

            if (isset($sku_bundle[$order_id]) && is_array($sku_bundle[$order_id])) ksort($sku_bundle[$order_id]);
        }

        ksort($order_id_master);
        ksort($sku_order_id_qty);
        ksort($sku_master);
        //krsort($product_build);
        if (isset($supplier_master))
			ksort($supplier_master);
        $supplier_item_action = '';
        $first_page_yn = 'y';


        /* split bundle option */
        if ($split_supplier_yn != 'no') {
            $childArray = array();
            $store_id = Mage::app()->getStore()->getId();
            $is_split = $this->_getConfig('split_bundles',0, false,'picks', $store_id);         
            
			if( $is_split && $show_bundle_parent_yn == 1 )
				$show_bundle_parent_yn = 1;
			
            foreach ($supplier_master as $key => $supplier) {
                if ((isset($supplier_login) && ($supplier_login != '') && (strtoupper($supplier) == strtoupper($supplier_login))) || !isset($supplier_login) || $supplier_login == '') {

                    if ($first_page_yn == 'n') 
						$page = $this->newPage();
                    else 
						$first_page_yn = 'n';

                    if ($picklogo == 1) {
                        $packlogo = Mage::getStoreConfig('pickpack_options/wonder/pack_logo', $order->getStore()->getId());
                        
                        if ($packlogo) {
                            $packlogo = Mage::getBaseDir('media') . '/moogento/pickpack/logo_pack/' . $packlogo;
                            $img_width = $logo_maxdimensions[0];
                            $img_height = $logo_maxdimensions[1];

                            $imageObj = new Varien_Image($packlogo);
                            $orig_img_width = $imageObj->getOriginalWidth();
                            $orig_img_height = $imageObj->getOriginalHeight();

                            $img_width = $orig_img_width;
                            $img_height = $orig_img_height;

                            /*************************** RESIZE IMAGE BY "AUTO-RESIZE" VALUE *******************************/


                            if ($orig_img_width > $logo_maxdimensions[0]) {
                                $img_height = ceil(($logo_maxdimensions[0] / $orig_img_width) * $orig_img_height);
                                $img_width = $logo_maxdimensions[0];
                            } //Fix for auto height --> Need it?
                            else
                            if ($orig_img_height > $logo_maxdimensions[1]) {
                                $temp_var = $logo_maxdimensions[1] / $orig_img_height;
                                //$img_height = ceil(($logo_maxdimensions[1] / $orig_img_height) * $orig_img_height);
                                $img_height = $logo_maxdimensions[1];
                                $img_width = $temp_var * $orig_img_width;
                            }
                            $x1 = 27;
							$page_top_spacer = 0;
                            $y2 = ($page_top + $page_top_spacer);
                            $y1 = ($y2 - $img_height);
                            $x2 = ($x1 + $img_width);
                            $image_ext = '';
                            $image_part = explode('.', $packlogo);
                            $image_ext = array_pop($image_part);
                            if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($packlogo))) { 
                                $packlogo = Zend_Pdf_Image::imageWithPath($packlogo);
                                $page->drawImage($packlogo,$x1, $y1, $x2, $y2);
                                unset($packlogo);
                            }
                        }

						// ADDING PAGE TITLE ******************************************************************************
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText($helper->__('Order-separated Pick List'), 325, ($page_top - $page_top_spacer - (41/2)), 'UTF-8');
                        $page->drawText(Mage::helper('sales')->__($supplier), 325, ($page_top - $page_top_spacer - (41/2) - 20), 'UTF-8');
                        $page->setFillColor($background_color_subtitles_zend);
                        $page->setLineColor($background_color_subtitles_zend);
                        $page->setLineWidth(0.5);
                        $page->drawRectangle($x2 + 10, $y1, 316, ($page_top + 10));

                        $this->y = $y1 - 10;
                    } else {
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] + 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        if(isset($this->warehouse_title[$supplier]))
                            $page->drawText(Mage::helper('sales')->__($helper->__('Order-separated Pick List') . ', ' . $this->warehouse_title[$supplier]), 31, ($page_top - 5), 'UTF-8');
                        else
                        	$page->drawText(Mage::helper('sales')->__($helper->__('Order-separated Pick List') . ', ' . $supplier), 31, ($page_top - 5), 'UTF-8');
                        //$page->drawText($helper->__('Supplier : '.$supplier), 325, ($page_top-25), 'UTF-8');
                        $page->setLineColor($background_color_subtitles_zend);
                        $page->setFillColor($background_color_subtitles_zend);
                        $page->drawRectangle(27, ($page_top - 12), $padded_right, ($page_top - 11));

                        $this->y = 800;
                    }

                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                    $this->skuSupplierItemAction = array();

                    $page_count = 1;
                    $grey_next_line = 0;
                    $fist_order = true;

                    $condition_new_page = 0;
                    $pre_condition_new_page = 0;
                    foreach ($order_id_master as $order_id => $value) {
                    $each_order_per_page =  $this->_getConfig('pickpack_newpage_per_order',0, false,'picks', $store_id_arr[$order_id]);
                    
                        $condition_new_page = 0;
                        if($each_order_per_page == 1 && ($fist_order == false))
                            $condition_new_page =1;
                        else
                        if($this->y < $generalConfig['font_size_subtitles'] + 20)
                            $condition_new_page = 1;
                        
                        if(($condition_new_page == 0) && ($pre_condition_new_page == 1))
                        {
                            $pre_condition_new_page = 0;
                            $condition_new_page =1;
                    
                        }
                        //else
                        //    $pre_condition_new_page = $condition_new_page;
                    
                        if ($condition_new_page == 1){
                            if ($page_count == 1) {
                                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - 15), 'UTF-8');
                            }
                            $page = $this->newPage();
                            $page_count++;
                            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                        }
                        $fist_order = false;

                        $grey_title = FALSE;
                        $supplier_array = explode(',', $value);
                        $supplier_skip_order = TRUE;
                        if (in_array(strtoupper(trim($supplier)), $supplier_array)) $supplier_skip_order = FALSE;
                        elseif ($supplier_options == 'grey') {
                            $supplier_skip_order = FALSE;
                            $grey_title = TRUE;
                        } // hide only 'filter' settings, if 'grey' show non-fulfileld orders greyed out

                        if ($supplier_skip_order == FALSE) {
                            $page->setFillColor($background_color_orderdetails_zend);
                            $page->setLineColor($background_color_orderdetails_zend);
                            if ($grey_title == TRUE) {
                                $page->setFillColor($lt_grey_bkg_color);
                                $page->setLineColor($lt_grey_bkg_color);
                            }
                            $page->drawRectangle(27, $this->y, $padded_right, ($this->y - 20));

                            $this->_setFont($page, 'bold', $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                            // order #
                            if ($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
                            $page->drawText($helper->__('#') . $order_id, 31, ($this->y - 15), 'UTF-8');
                            
                            
                            //Todo tickbox 1
                            if ($tickbox != 0 && $grey_title != TRUE) {
                                $page->setFillColor($white_color);
                                $page->setLineColor($black_color);
                                $page->drawRectangle(552, ($this->y - 2), 568, ($this->y - 18));
                            }

                            if ($barcodes == 1) {
                                $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($order_id, $generalConfig['barcode_type']);
                                $barcodeWidth = $this->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 16);
                                $page->setFillColor($white_color);
                                $page->setLineColor($white_color);
                                $page->drawRectangle(($barcodeWidth + 45), ($this->y - 2), ($barcodeWidth + $barcodeWidth + 95), ($this->y - 18));
                                $page->setFillColor($black_color);
                                if ($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
                                $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 16);
                                $page->drawText($barcodeString, ($barcodeWidth + 50), ($this->y - 20), 'CP1252');
                            }

                            if ($shmethod == 1) {
                                if ($barcodes == 1) $shipping_method_x = 2*$barcodeWidth + 110; //$barcodeWidth = 250;
                                if ($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
                                $this->_setFont($page, 'bold', ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                $page->drawText($sku_shipping_method[$order_id], $shipping_method_x, ($this->y - 15), 'UTF-8');
                            }
                            if ($warehouseyn == 1) {
                                $this->_setFont($page, 'bold', ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                $page->drawText($sku_warehouse[$order_id], $warehouse_x, ($this->y - 15), 'UTF-8');
                            }
                            
                            if ($giftwrapyn == 1) {
                                $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                $page->drawText($sku_giftwrap[$order_id], $giftwrap_x, ($this->y - 15), 'UTF-8');
                            }
                            //TODO shipping address
                            if ($order_address_yn == 1) {
                                $this->y -= 30;

                                if ($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
                                $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] - 4), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                $page->drawText($sku_shipping_address[$order_id], 60, ($this->y - 15), 'UTF-8');
                            }
                            //TODO billing address
                            if ($order_billing_address_yn == 1) {
                                $this->y -= 30;

                                if ($grey_title == TRUE) $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.4));
                                $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] - 4), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                $page->drawText($sku_billing_address[$order_id], 60, ($this->y - 15), 'UTF-8');
                            }
                            $this->y -= 30;
                            //Draw title
                            if($show_column_title == 1){
                                $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $page->drawText(Mage::helper('sales')->__($col_title_qty), $qtyX, $this->y, 'UTF-8');
                                if (isset($skuyn) && $skuyn == 1)
                                   $page->drawText(Mage::helper('sales')->__($col_title_sku), ($skuX + 10), $this->y, 'UTF-8');
                                if (isset($nameyn) && $nameyn == 1)
                                    $page->drawText(Mage::helper('sales')->__($col_title_name), intval($namenudge), $this->y, 'UTF-8');
                                if ($product_barcode_yn == 1 || $product_barcode_yn == 2)
                                    $page->drawText(Mage::helper('sales')->__($col_title_product_barcode), $product_barcode_X, $this->y, 'UTF-8');
                                $this->y-= ($generalConfig['font_size_body'] + 2);
                            }
                            $show_bundle_parent = true;
                            foreach ($sku_order_id_qty[$order_id] as $sku => $qty) {
                                if(isset($sku_bundle[$order_id][$sku])  && $sku_bundle[$order_id][$sku] != "" && $show_bundle_parent_yn != 1)
                                    $show_bundle_parent = false;
                                $supplier_item_action = 'keep';
                                // if set to filter and a name and this is the name, then print
                                if ($supplier_options == 'filter' && isset($supplier_login) && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier_login))) //grey //split
                                {
                                    $supplier_item_action = 'keep';
                                } elseif ($supplier_options == 'filter' && isset($supplier_login) && ($supplier_login != '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login))) //grey //split
                                {
                                    $supplier_item_action = 'hide';
                                } elseif ($supplier_options == 'grey' && isset($supplier_login) && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier_login))) //grey //split
                                {
                                    $supplier_item_action = 'keep';
                                } elseif ($supplier_options == 'grey' && isset($supplier_login) && $supplier_login != '' && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier_login))) //grey //split
                                {
                                    $supplier_item_action = 'keepGrey';
                                } elseif ($supplier_options == 'grey' && (!isset($supplier_login) || $supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier))) {
                                    $supplier_item_action = 'keepGrey';
                                } elseif ($supplier_options == 'filter' && (!isset($supplier_login) || $supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier))) {
                                    $supplier_item_action = 'hide';
                                    //NEW TODO 
                                    if(strpos($sku_supplier[$sku], ','))
                                    {
                                        $temp_arr = explode(',',$sku_supplier[$sku]);
                                        if (in_array(strtoupper($supplier), $temp_arr)) {
                                            $supplier_item_action = 'keep';
                                        }
                                        unset($temp_arr);
                                    } 
                                } elseif ($supplier_options == 'grey' && (!isset($supplier_login) || $supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier))) {
                                    $supplier_item_action = 'keep';
                                } elseif ($supplier_options == 'filter' && (!isset($supplier_login) || $supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier))) {
                                    $supplier_item_action = 'keep';
                                } elseif ($supplier_options == 'grey') $supplier_item_action = 'keepGrey'; elseif ($supplier_options == 'filter') $supplier_item_action = 'hide';

                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                                if ($supplier_item_action != 'hide' && trim($supplier_item_action) != '') {
                                    if ($this->y < 60) {
                                        if ($page_count == 1) {
                                            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - 15), 'UTF-8');
                                        }
                                        $page = $this->newPage();
                                        $page_count++;
                                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                                    }

                                    // if ($this->y<15) $page = $this->newPage();
                                    if ($supplier_item_action == 'keepGrey') {
                                        $page->setFillColor($greyout_color);
                                    } elseif ($tickbox != 0 && $show_bundle_parent) {
                                    //Todo tickbox 2
                                        $page->setFillColor($white_color);
                                        $page->setLineColor($black_color);
                                        $page->drawRectangle($tickbox_X, ($this->y), $tickbox_X + 7, ($this->y + 7));
                                        
                                        if($tickbox2 != 0)
                                        {
                                            $page->drawRectangle($tickbox2_X, ($this->y), $tickbox2_X + 7, ($this->y + 7));
                                        }
                                        $page->setFillColor($font_color_body_zend);
                                            
                                    }
                                    $combine_attribute_array = array();
                                    // custom attribute
                                    $sku_addon = '';
                                    if ($shelving_yn == 1 && trim($sku_shelving[$sku]) != '' && $show_bundle_parent) {
                                        if($combine_attribute_separated == 1){
                                            $combine_attribute_array[] = '[' . $sku_shelving[$sku] . ']';
                                        }else{
                                            if ($shelvingpos == 'col') {
                                                $page->drawText('[' . $sku_shelving[$sku] . ']', $shelvingX, $this->y, 'UTF-8');
                                            } else {
                                                $sku_addon = ' / [' . $sku_shelving[$sku] . ']';
                                            }    
                                        }
                                    }
                                    //custom attribute 2
                                    $sku_addon2 = '';
                                    if ($shelving2_yn == 1 && trim($sku_shelving2[$sku]) != '' && $show_bundle_parent) {
                                        if($combine_attribute_separated == 1){
                                            $combine_attribute_array[] = '[' . $sku_shelving2[$sku] . ']';
                                        }else{
                                            if ($shelvingpos == 'col') {
                                                $page->drawText('[' . $sku_shelving2[$sku] . ']', $shelving2X, $this->y, 'UTF-8');
                                            } else {
                                                $sku_addon2 = ' / [' . $sku_shelving2[$sku] . ']';
                                            }
                                        }
                                    }
                                    //custom attribute 3

                                    $sku_addon3 = '';
                                    if ($shelving3_yn == 1 && trim($sku_shelving3[$sku]) != '') {
                                        if($combine_attribute_separated == 1){
                                            $combine_attribute_array[] = '[' . $sku_shelving3[$sku] . ']';
                                        }else{
                                            if ($shelvingpos == 'col') {
                                                $page->drawText('[' . $sku_shelving3[$sku] . ']', $shelving3X, $this->y, 'UTF-8');
                                            } else {
                                                $sku_addon3 = ' / [' . $sku_shelving3[$sku] . ']';
                                            }
                                        }
                                    }
                                    //custom attribute 4
                                    $sku_addon4 = '';
                                    if ($shelving4_yn == 1 && trim($sku_shelving4[$sku]) != '') {
                                        if($combine_attribute_separated == 1){
                                            $combine_attribute_array[] = '[' . $sku_shelving4[$sku] . ']';
                                        }else{
                                            if ($shelvingpos == 'col') {
                                                $page->drawText('[' . $sku_shelving4[$sku] . ']', $shelving4X, $this->y, 'UTF-8');
                                            } else {
                                                $sku_addon4 = ' / [' . $sku_shelving4[$sku] . ']';
                                            }
                                        }
                                    }
                                    // combine attribute
                                    if($combine_attribute_separated == 1 && $combine_attribute_array != ''){
                                        $yTempCombinePos = $this->y;
                                        foreach ($combine_attribute_array as $key => $value_attribute) {
                                            $page->drawText($value_attribute, $combine_attribute_separated_xpos, $this->y, 'UTF-8');
                                            $this->y -= ($generalConfig['font_size_body'] + 2);
                                        }
                                        $this->y += $generalConfig['font_size_body'] + 2;
                                    }
                                    $max_qty_length_display = 0;

                                    if ($from_shipment == 'shipment') {
                                        $max_qty_length_display = ((($max_qty_length + 5) * ($generalConfig['font_size_body'] * 1.1)) - 57);
                                        $qty_string = 's:' . (int)$sku_order_id_sqty[$order_id][$sku] . ' / o:' . $qty;
                                    } else {
                                        $qty_string = $qty;
                                    }
                                    
                                    /*get qty base on config setting**/
                                    $sku_qty_shipped = $sku_order_id_sqty[$order_id][$sku];
                                    $sku_qty_invoiced = $sku_order_id_iqty[$order_id][$sku];
                                    $qty_string = $this->getQtyString($from_shipment, $sku_qty_shipped,$qty, $sku_qty_invoiced);
                                    /*************************/
                                    $display_sku = '';
                                    if (strlen($sku) > ($character_breakpoint_sku + 2)) {
                                        $display_sku = substr(htmlspecialchars_decode($sku_order_id_sku[$order_id][$sku]), 0, ($character_breakpoint_sku)).'…';
                                    }  else $display_sku = htmlspecialchars_decode($sku_order_id_sku[$order_id][$sku]);
                                    $red_color = new Zend_Pdf_Color_Html('darkRed');
                                    if($show_qty_options != 2)
                                        $qty_string = round($qty_string, 2);
                                    if($generalConfig['font_family_body'] == 'traditional_chinese' || $generalConfig['font_family_body'] == 'simplified_chinese'){
                                        $font_family_body_temp = $generalConfig['font_family_body'];
                                        $generalConfig['font_family_body'] = 'helvetica';
                                    }
                                    $this->_setFont($page, 'bold', ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    if ($product_qty_upsize_yn == 1 && $qty_string > 1 && $show_bundle_parent) {
                                        if ($product_qty_rectangle == 1) {
                                            $page->setLineWidth(0.5);
                                            $page->setLineColor($black_color);
                                            $page->setFillColor($black_color);
                                            if(($qty_string >= 100) || (strlen($qty_string) > 3))
                                                $page->drawRectangle(($qtyX), ($this->y - 1), ($qtyX - 18 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                            else 
                                                if(($qty_string >= 10) || (strlen($qty_string) >= 2))
                                                    $page->drawRectangle(($qtyX-1), ($this->y - 1), ($qtyX - 8 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                                else
                                                    $page->drawRectangle(($qtyX - 1), ($this->y - 1), ($qtyX - 2 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                            $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                                            //$page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');
                                            $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                                        }
                                        else{
                                            $this->_setFont($page, 'bold', ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                            $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                                        }
                                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    }
                                    elseif($show_bundle_parent)
                                        $page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');
                                    if(isset($font_family_body_temp)){
                                        $generalConfig['font_family_body'] = $font_family_body_temp;
                                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    }
                                    //$page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');
                                    if($show_bundle_parent)
                                    	$page->drawText('   ' . $display_sku . $sku_addon, $skuX + $max_qty_length_display, $this->y, 'UTF-8');
                                    if ($nameyn == 1 && $show_bundle_parent) 
										$page->drawText($sku_name[$sku], intval($namenudge), $this->y, 'UTF-8');
                                    $store_id = Mage::app()->getStore()->getId();
                                    if ($show_bundle_parent && ($product_barcode_yn == 1 || $product_barcode_yn == 2)) {
                                        $barcode_font_size = 20;
                                        //$page->drawText($productId, $product_barcode_X -20, $this->y , 'UTF-8');
                                        $productId = $sku_order_id_product_id[$order_id][$sku];
                                        
                                        if($product_barcode_yn == 2)
                                            $barcodeString = $this->getBarcode($product_id, "picks", $store_id);
                                        else
                                            $barcodeString = $productId;
                                        $productbarcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($barcodeString, $generalConfig['barcode_type']);
                                        $productbarcodeWidth = $this->parseString($barcodeString, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                                        if($grey_next_line == 0)
                                        {
                                            $page->setFillColor($white_color);
                                            $page->setLineColor($white_color);
                                            $page->drawRectangle(($product_barcode_X - 5), ($this->y+ 10), ($productbarcodeWidth + $product_barcode_X + 5), ($this->y -5));
                                        }
                                    
                                        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                                        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                                        $page->drawText($productbarcodeString, ($product_barcode_X), ($this->y + 3 - $barcode_font_size), 'CP1252');
                                        //print white rectangle
                                        $page->setFillColor($white_color);
                                        $page->setLineColor($white_color);
                                        $page->drawRectangle(($product_barcode_X - 2), ($this->y - 3), ($product_barcode_X + $productbarcodeWidth + $productbarcodeWidth + $barcode_font_size), ($this->y - $barcode_font_size -5));
                                        if ($product_barcode_bottom_yn == 1) {
                                            $this->_setFont($page, $generalConfig['font_style_body'], ($product_barcode_bottom_font_size), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                            $page->drawText($childProductId, $product_barcode_X + $productbarcodeWidth*0.7, $this->y - 9, 'UTF-8');
                                        }
                                    }

                                 

                                    if (($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1 && $supplier_item_action != 'keepGrey' && $show_bundle_parent) {
                                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                                        $this->y -= 12;
                                        $page->setFillColor($red_bkg_color);
                                        $page->setLineColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
                                        $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                                        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                                        $warning = 'Stock Warning      ' . '    Net Stock After All Picks : ';
                                        if (isset($sku_stock[$sku])) $warning .= $sku_stock[$sku];
                                        $page->drawText($warning, 60, $this->y, 'UTF-8');
                                        $this->y -= 4;
                                    }
                                    if (isset($sku_order_id_options[$order_id][$sku]) && $sku_order_id_options[$order_id][$sku] != '' && $show_bundle_parent) {
                                        $this_item_options = '';
                                        $this_item_options = trim(str_replace('Array[', '[', $sku_order_id_options[$order_id][$sku]));
                                        $this->y -= 10;
                                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_options']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                                        $maxWidthPage = ($padded_right + 20 - 80);
                                        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                        $font_size_compare = ($generalConfig['font_size_options']);
                                        $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                                        $char_width = $line_width / 10;
                                        $max_chars = round($maxWidthPage / $char_width);
                                        if (strlen($this_item_options) > $max_chars) {
                                            if ($options_yn_base == '1') $chunks = split_words($this_item_options, '/ /', $max_chars);
                                            elseif ($options_yn_base == 'yesstacked') $chunks = explode('[', $this_item_options);

                                            $lines = 0;
                                            foreach ($chunks as $key => $chunk) {
                                                $chunk_display = '';

                                                if (trim($chunk != '')) {
                                                    $this->y -= ($generalConfig['font_size_options']);
                                                    $chunk_display = str_replace('[[', '[', '[ ' . $chunk);
                                                    $page->drawText($chunk_display, 80, $this->y, 'UTF-8');
                                                    $lines++;
                                                }
                                            }

                                            unset($chunks);
                                        } else {
                                            if ($options_yn_base == 'yesstacked') {
                                                $chunks = explode('[', $this_item_options);

                                                $lines = 0;

                                                foreach ($chunks as $key => $chunk) {
                                                    $chunk_display = '';
                                                    if (trim($chunk != '')) {
                                                        $this->y -= ($generalConfig['font_size_options']);
                                                        $chunk_display = str_replace('[[', '[', '[' . $chunk);
                                                        $page->drawText($chunk_display, 80, $this->y, 'UTF-8');
                                                        $lines++;
                                                    }
                                                }
                                                unset($chunks);
                                            } else {
                                                $this->y -= ($generalConfig['font_size_options']);
                                                $page->drawText($this_item_options, 80, $this->y, 'UTF-8');
                                            }
                                        }
                                    }
                                     
                                    if ($sku_bundle[$order_id][$sku]) {
                                        $offset = 10;
                                        $box_x = ($qtyX - $offset);
                                        if($show_bundle_parent)
                                        $this->y -= 10;
                                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                        if( $is_split && $show_bundle_parent_yn == 1 ) {
                                           

                                          foreach( $sku_bundle[$order_id][$sku] as $key => $value ) {
                                               $childArray[] = $value;
                                          }
                                           
                                        }else{
                                        $page->drawText($helper->__('Bundle Options') . ' : ', $box_x, $this->y, 'UTF-8');

                                        foreach ($sku_bundle[$order_id][$sku] as $key => $value) {
                                            $sku = '';
                                            $name = '';
                                            $shelf = '';
                                            $qty = '';
                                            $sku_bundle_array = explode('##', $value);
                                            $sku = $sku_bundle_array[0];
                                            $name = $sku_bundle_array[1];
                                            $shelf = $sku_bundle_array[2];
                                            $qty = $sku_bundle_array[3];

                                            $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                            $this->y -= 10;
                                            
                                            if ($skuyn == 1)
                                                $page->drawText($sku, ($skuX + $skuXInc + 10), $this->y, 'UTF-8');
                                            $page->drawText($name, intval($namenudge + 10), $this->y, 'UTF-8');
                                            $page->drawText($shelf, $shelvingX, $this->y, 'UTF-8');
                                            $page->drawText($qty, $qtyX, $this->y, 'UTF-8');
                                        }
                                        
                                     }
                                 }

                                    //$this->y -= 12;
                                    if($combine_attribute_separated == 1 && $this->y > $yTempCombinePos)
                                        $this->y = $yTempCombinePos;
                                    $this->y -= ($generalConfig['font_size_body'] + 6);
                                }
                            }
                        } //end if in supplier_array
                    }
                    
       /* split bundle option */                    
       
          if( count( $childArray ) > 0 ) {
                   $arr = array();
                   $arr_key = array();

                   foreach($childArray as $key => $value){
                      $sku_bundle_array = explode('##', $value);
                      
                      if( in_array($sku_bundle_array[0],$arr) ) {
                            $sku_bundle_array[3] =  $arr_qty[$sku_bundle_array[0]] + $sku_bundle_array[3];
                            $childArray[$key] = implode("##",$sku_bundle_array);
                            unset($childArray[$arr_key[$sku_bundle_array[0]]]);
                        }                     
                      $arr[] = $sku_bundle_array[0];    
                      $arr_qty[$sku_bundle_array[0]] = $sku_bundle_array[3];                      
                      $arr_key[$sku_bundle_array[0]] = $key;
                   }
                  $grey_next_line_bundle = 0;      
                    foreach ($childArray as $key => $value) {
                        $sku = '';
                        $name = '';
                        $shelf = '';
                        $qty = '';
                        $sku_bundle_array = explode('##', $value);
                        $sku = $sku_bundle_array[0];
                        $name = $sku_bundle_array[1];
                        $shelf = $sku_bundle_array[2];
                        $qty = $sku_bundle_array[3];

                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $this->y -= 10;
                        
                        if ($skuyn == 1)
                            $page->drawText($sku, ($skuX + $skuXInc + 10), $this->y, 'UTF-8');
                        //$page->drawText($sku, ($skuX+$skuXInc+10) , $this->y , 'UTF-8');
                        $page->drawText($name, intval($namenudge + 10), $this->y, 'UTF-8');
                        $page->drawText($shelf, $shelvingX, $this->y, 'UTF-8');
                        $page->drawText($qty, $qtyX, $this->y, 'UTF-8');

                        // if (($this->skuSupplierItemAction[$supplier][$sku] != 'keepGrey') && ($tickbox == 'pickpack')) {
//                                                 $page->setLineWidth(0.5);
//                                                 $page->setFillColor($white_color);
//                                                 $page->setLineColor($black_color);
//                                                 $page->drawRectangle($box_x, ($this->y - 1), ($box_x + 7), ($this->y + 6));
//                                                 $page->setFillColor($black_color);
//                                             }
                    }                 
            }
       /* split bundle option */                    
                    // end roll_Order
                }
                // end hide/grey sheets for suppliers
            }
        } 
        else 
        {
            if ($picklogo == 1) {               
                $sub_folder = 'logo_pack';              
                $option_group = 'wonder';               
                $suffix_group = '/pack_logo';  
				$page_top_spacer = 0; 
                $x1 = 27;
                $y2 = ($page_top - $page_top_spacer);         
                $y1 = $this->printHeaderLogo($page, $store_id, $picklogo, $page_top, $logo_maxdimensions, $sub_folder, $option_group, $suffix_group, $x1, $y2);
                if($address_label_yn == 0){
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    if ($from_shipment == 'shipment')
                        $page->drawText($helper->__('Shipment-separated Pick List'), 325, ($page_top - $page_top_spacer - (41/2)), 'UTF-8');
					else
                        $page->drawText($helper->__('Order-separated Pick List'), 325, ($page_top - $page_top_spacer - (41/2)), 'UTF-8');

                    // $page->drawText($helper->__('Supplier : '.$supplier), 325, 790, 'UTF-8');
                    $page->setFillColor($background_color_subtitles_zend);
                    $page->setLineColor($background_color_subtitles_zend);
                    $page->setLineWidth(0.5);
	                if($generalConfig['line_width_company'] > 0)
		                $page->drawRectangle(304, $y1, (304 + $generalConfig['line_width_company']), ($page_top + 5));
                }
                $this->y = ($y1 - $page_top_spacer); 
                
            } 
            else {
                if($address_label_yn == 0){
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] + 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                    if ($from_shipment == 'shipment')
                        $page->drawText($helper->__('Shipment-separated Pick List'), 31, ($page_top - 5), 'UTF-8');
					else
                        $page->drawText($helper->__('Order-separated Pick List'), 31, ($page_top - 5), 'UTF-8');

                    $page->setLineColor($background_color_subtitles_zend);
                    $page->setFillColor($background_color_subtitles_zend);
                    $page->drawRectangle(27, ($page_top - 12), $padded_right, ($page_top - 11));

                    $this->y = ($page_top - 20); //800;
                }
                else
                    $this->y = ($page_top - 2); //800;
            }

            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            $page_count = 1;
            $total_bundle_quantity = 0;
            $total_quantity = 0;
             $fist_order = true;
             $condition_new_page = 0;
             $pre_condition_new_page = 0;
             $config_group = 'messages'; 
             $childArray = array();
            /* split bundle option */
            $is_split = $this->_getConfig('split_bundles',0, false,'picks', $store_id);
            
            if( $is_split && $show_bundle_parent_yn == 1 ) { $show_bundle_parent_yn = 1; }


            
            foreach ($product_build as $order_id => $order_build) {
                $each_order_per_page =  $this->_getConfig('pickpack_newpage_per_order',0, false,'picks', $store_id_arr[$order_id]);
                $condition_new_page = 0;
                if($each_order_per_page == 1 && ($fist_order == false))
                    $condition_new_page =1;
                elseif($this->y < $generalConfig['font_size_subtitles'] + 20)
                    $condition_new_page = 1;
                if(($condition_new_page == 0) && ($pre_condition_new_page == 1))
                {
                    $pre_condition_new_page = 0;
                    $condition_new_page =1;
                }
                //else
                //    $pre_condition_new_page = $condition_new_page;
                
                
                if ($condition_new_page == 1){
                    if ($page_count == 1) {
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($generalConfig['font_size_subtitles'] - 7), 'UTF-8');
                    }
                    $page = $this->newPage();
                    $page_count++;
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                }
                $fist_order = false;
                $y_start_order = $this->y;
                $page->setFillColor($background_color_orderdetails_zend);
                $page->setLineColor($background_color_orderdetails_zend);
                $page->drawRectangle(27, $this->y, $padded_right, ($this->y - 20));
                $this->_setFont($page, 'bold', $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                $page->drawText($helper->__('#') . $order_id, 31, ($this->y - 15), 'UTF-8');

                $barcodes = Mage::getStoreConfig('pickpack_options/picks/pickpack_pickbarcode');
                //Show order tickbox
                if ($tickbox != 0 && $address_label_yn == 0) {
                    $page->setFillColor($white_color);
                    $page->setLineColor($black_color);
                    $page->drawRectangle(($padded_right - 2 - 16), ($this->y - 2), ($padded_right - 2), ($this->y - 18));
                    $page->setFillColor($black_color);
                }

                if ($barcodes == 1) {
                    $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($order_id, $generalConfig['barcode_type']);
                    $barcodeWidth = $this->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 16);
                    $page->setFillColor($white_color);
                    $page->setLineColor($white_color);
                    $page->drawRectangle(($barcodeWidth + 45), ($this->y - 2), ($barcodeWidth + $barcodeWidth + 95), ($this->y - 18));
                    $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                    $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 16);
                    $page->drawText($barcodeString, ($barcodeWidth + 55), ($this->y - 20), 'CP1252');
                }

                if ($shmethod == 1) {
                    if ($barcodes == 1) $shipping_method_x = 2*$barcodeWidth + 110; //$barcodeWidth = 250;
                    $this->_setFont($page, 'bold', ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText($sku_shipping_method[$order_id], $shipping_method_x, ($this->y - 15), 'UTF-8');
                }
                
                if ($warehouseyn == 1) {
                    $this->_setFont($page, 'bold', ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText($sku_warehouse[$order_id], $warehouse_x, ($this->y - 15), 'UTF-8');
                }
                
                if ($giftwrapyn == 1) {
                    $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText($sku_giftwrap[$order_id]['wrapping_paper'], $giftwrap_x, ($this->y - 15), 'UTF-8');
                    if(isset($sku_giftwrap[$order_id]['message']) && (strlen($sku_giftwrap[$order_id]['message']) > 0))
                    {
                        $page->setFillColor($background_color_orderdetails_zend);
                        $page->setLineColor($background_color_orderdetails_zend);
                        $page->drawRectangle(27, ($this->y - 31), $padded_right, ($this->y - 20));
                        $this->y -= 10;
                        $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] - 4), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText($sku_giftwrap[$order_id]['message'], $giftwrap_x, ($this->y - 15), 'UTF-8');
                    }
                }
                //TODO shipping/billing address
                
                if ($order_address_yn == 1 || $order_billing_address_yn ==1) {
                    if(isset($sku_giftwrap[$order_id]['message']) && (strlen($sku_giftwrap[$order_id]['message']) > 0))
                    {
                        $this->y -= 28;
                        if($order_address_yn == 1){
                            $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] * 0.6), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText($sku_shipping_address[$order_id], 31, $this->y, 'UTF-8');
                            $this->y -= ($generalConfig['font_size_subtitles'] * 0.6);
                        }
                        if($order_billing_address_yn == 1){
                            $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] * 0.6), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText($sku_billing_address[$order_id], 31, $this->y, 'UTF-8');
                        }
                        $this->y -= 16;
                    }
                    else
                    {
                        $this->y -= 28;
                        if($order_address_yn == 1){
                            $page->setFillColor($background_color_orderdetails_zend);
                            $page->setLineColor($background_color_orderdetails_zend);
                            $page->drawRectangle(27, ($this->y - 3), $padded_right, ($this->y + 8));
                            $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] * 0.6), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText($sku_shipping_address[$order_id], 31, $this->y, 'UTF-8');
                            $this->y -= ($generalConfig['font_size_subtitles'] * 0.6);
                        }
                        if($order_billing_address_yn == 1){
                            $page->setFillColor($background_color_orderdetails_zend);
                            $page->setLineColor($background_color_orderdetails_zend);
                            $page->drawRectangle(27, ($this->y - 3 ), $padded_right, ($this->y + 8));
                            $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] * 0.6), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText($sku_billing_address[$order_id], 31, $this->y, 'UTF-8');
                            $this->y -= 10;
                        }
                        $this->y -= 7;
                    }
                }
                if($order_billing_address_yn == 0 && $order_address_yn==0)
                    $this->y -= ($generalConfig['font_size_body'] + 20);
                // order gift message.
                if($giftmessage_yn == 1){
                    if(isset($gift_msg_array[$order_id])){
                        $gift_sender = $gift_msg_array[$order_id][1];
                        $gift_recipient = $gift_msg_array[$order_id][2];
                        $gift_message = $gift_msg_array[$order_id][0];
                        $to_from = '';
                        $to_from_from = '';
                        $msgX = $padded_left + 5;
                        if (isset($gift_recipient) && $gift_recipient != '') {
                            $to_from .= 'To: ' . $gift_recipient;
                        }
                        if (isset($gift_sender) && $gift_sender != '') $to_from_from = 'From : ' . $gift_sender;
                        
                        
                        if($message_title_tofrom_yn == 1){
                            $gift_message = $to_from . ' ' . $to_from_from . ' ' . "Message : "  . $gift_message ;
                        }
                        $gift_msg_array = $this->createMsgArray($gift_message);
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $page->drawText("Gift Message: ", $msgX, $this->y, 'UTF-8');
                        $width_title = $this->getWidthString("Gift Message: ", $generalConfig['font_size_body']);
                        // print the gift message content
                        //$this->_setFont($page, $font_style_message, ($font_size_gift_message - 1), $font_family_message, $generalConfig['non_standard_characters'], $font_color_message);
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $this->y = $this->drawOrderGiftMessage($gift_msg_array, $msgX + $generalConfig['font_size_body']/3 + $width_title, $generalConfig['font_size_body'], $this->y, $page);
                        unset($gift_msg_array);
                    }
                }
                if ($sort_packing != 'none') {
                    $sortorder_packing_bool = false;
                    if ($sortorder_packing == 'ascending') $sortorder_packing_bool = true;
                    sksort($order_build, $sort_packing, $sortorder_packing_bool);
                }

                //            $this->y -= 30;
                //Draw title

                if($show_column_title == 1){

                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $page->drawText(Mage::helper('sales')->__($col_title_qty), $qtyX, $this->y, 'UTF-8');
                    if (isset($skuyn) && $skuyn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_sku), ($skuX + 10), $this->y, 'UTF-8');
                    if (isset($nameyn) && $nameyn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_name), intval($namenudge), $this->y, 'UTF-8');
                    if ($product_barcode_yn == 1 || $product_barcode_yn == 2)
                        $page->drawText(Mage::helper('sales')->__($col_title_product_barcode), $product_barcode_X, $this->y, 'UTF-8');
                    $this->y-= ($generalConfig['font_size_body'] + 2);
                }



                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                $grey_next_line = 0;
                //TODO first each section
                if($address_label_yn == 1 && isset($shipping_address_label[$order_id])){
                    $this->drawBorderLabel($y_start_order, $first_start_y, $nudge_shipping_address, $label_height, $padded_right, $label_width, $white_color, $black_color, $page);
                    $this->drawAddressLabelText($address_format_label, $shipping_address_label[$order_id], $address_countryskip_label,$label_width,$label_padding,$font_size_label,$y_start_order,$first_start_y,$nudge_shipping_address, $padded_right, $label_height,$page, $font_style_label, $font_family_label, $generalConfig['non_standard_characters'], $font_color_label);
                }
                if( $is_split && $show_bundle_parent_yn == 1 ){
                        $bundle_array = array();
                        foreach ($order_build as $sku => $value) {
                            $product = Mage::getModel('catalog/product')->load($value['id']);
                            if( $product->getData('type_id') == 'bundle' ){
                              $bundle_array[$sku] = $value;
                              unset($order_build[$sku]);                      
                            }
                        }
                        $order_build = array_merge($bundle_array,$order_build);     
                }
                
                foreach ($order_build as $sku => $value) {
                    $show_bundle_parent = true;
                    if(isset($sku_bundle[$order_id][$sku]) && $sku_bundle[$order_id][$sku] != "" && $show_bundle_parent_yn != 1)
                        $show_bundle_parent = false;
                    //TODO show item full sku
                    $dsku = $value['display_sku'];
                    //TODO show parent sku
//                     $dsku = $value['sku'];
                    $qty = $value['qty'];
                    if(isset($sku_bundle[$order_id][$sku]) && $sku_bundle[$order_id][$sku] != "")
                        $total_bundle_quantity += $qty;
                    else
                        $total_quantity += $qty;
                    $sqty = $value['sqty'];
                    $productId = $value['id'];
                    if($address_label_yn == 0){
                    if($grey_next_line == 1)
                    {
                        $page->setFillColor($alternate_row_color);
                        $page->setLineColor($alternate_row_color);

                        $grey_box_y1 = ($this->y-($generalConfig['font_size_body']/5));
                        $grey_box_y2 = ($this->y+($generalConfig['font_size_body']*0.85));

                        $page->drawRectangle(25, $grey_box_y1, $padded_right, $grey_box_y2);
                        $grey_next_line = 0;
                    }
                    else $grey_next_line = 1;
                    }

                    if ($this->y < $generalConfig['font_size_subtitles'] + 5) {
                        if ($page_count == 1) {
                            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, $generalConfig['font_size_subtitles'] + 2, 'UTF-8');
                        }
                        $page = $this->newPage();
                        $page_count++;
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                    }

                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                    if ($tickbox != 0 && $show_bundle_parent) {
                        $page->setLineWidth(0.5);
                        $page->setFillColor($white_color);
                        $page->setLineColor($black_color);
                        if(isset($sku_bundle[$order_id][$sku]) && $sku_bundle[$order_id][$sku] != "")
                            $page->drawCircle($tickbox_X + 3.5, ($this->y + 3.5), 3.5);
                        else
                            $page->drawRectangle($tickbox_X, ($this->y), $tickbox_X + 7, ($this->y + 7));                                        
                        if($tickbox2 != 0)
                        {
                            if(isset($sku_bundle[$order_id][$sku]) && $sku_bundle[$order_id][$sku] != "")
                                $page->drawCircle($tickbox2_X + 3.5, ($this->y + 3.5), 3.5);
                            else
                   $page->drawRectangle($tickbox2_X, ($this->y), $tickbox2_X + 7, ($this->y + 7));
            }
                        if($this->_getConfig('pickpack_tickbox_separated_signature_line', $namenudgeYN_default, false, 'picks')){
                              $page->drawLine(($tickbox_X + 8), ($this->y), ($tickbox_X * 2 - 5 ), ($this->y));                     
                         }                        
                    }
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                    $sku_addon = '';
                    $combine_attribute_array = array();
                    if ($shelving_yn == 1 && trim($sku_shelving[$sku]) != '' && $show_bundle_parent) {
                        if($combine_attribute_separated == 1){
                            $combine_attribute_array[] = $sku_shelving[$sku];
                        }else{
                            if ($shelvingpos == 'col') {
                                $page->drawText('[' . $sku_shelving[$sku] . ']', $shelvingX, $this->y, 'UTF-8');
                            } else {
                                $sku_addon = ' / [' . $sku_shelving[$sku] . ']';
                            }
                        }
                    }
                    //custom attribute 2
                    $sku_addon2 = '';
                    if ($shelving2_yn == 1 && trim($sku_shelving2[$sku]) != '' && $show_bundle_parent){
                        if($combine_attribute_separated == 1){
                            $combine_attribute_array[] = $sku_shelving2[$sku];
                        }else{
                            if ($shelvingpos == 'col') {
                                $page->drawText('[' . $sku_shelving2[$sku] . ']', $shelving2X, $this->y, 'UTF-8');
                            } else {
                                $sku_addon2 = ' / [' . $sku_shelving2[$sku] . ']';
                            }
                        }
                    }
                    
                    //custom attribute 3
                    $sku_addon3 = '';
                    if ($shelving3_yn == 1 && trim($sku_shelving3[$sku]) != '') {
                        if($combine_attribute_separated == 1){
                            $combine_attribute_array[] = $sku_shelving3[$sku];
                        }else{
                            if ($shelvingpos == 'col') {
                                $page->drawText('[' . $sku_shelving3[$sku] . ']', $shelving3X, $this->y, 'UTF-8');
                            } else {
                                $sku_addon3 = ' / [' . $sku_shelving3[$sku] . ']';
                            }
                        }
                    }
                    //custom attribute 4
                    $sku_addon4 = '';
                    if ($shelving4_yn == 1 && trim($sku_shelving4[$sku]) != '') {
                        if($combine_attribute_separated == 1){
                            $combine_attribute_array[] = $sku_shelving4[$sku];
                        }else{
                            if ($shelvingpos == 'col') {
                                $page->drawText('[' . $sku_shelving4[$sku] . ']', $shelving4X, $this->y, 'UTF-8');
                            } else {
                                $sku_addon4 = ' / [' . $sku_shelving4[$sku] . ']';
                            }
                        }
                    }
                    // combine attribute
                    if($combine_attribute_separated == 1 && $combine_attribute_array != ''){
                        $yTempCombinePos = $this->y;
                        foreach ($combine_attribute_array as $key => $value_attribute) {
                            $page->drawText($value_attribute, $combine_attribute_separated_xpos, $this->y, 'UTF-8');
                            $this->y -= ($generalConfig['font_size_body'] + 2);
                        }
                        $this->y += $generalConfig['font_size_body'] + 2;
                    }
                    unset($combine_attribute_array);
                    $max_qty_length_display = 0;

                    if ($from_shipment == 'shipment') {
                        $max_qty_length_display = ((($max_qty_length + 5) * ($generalConfig['font_size_body'] * 1.1)) - 57);
                        $qty_string = 's:' . (int)$sku_order_id_sqty[$order_id][$sku] . ' / o:' . $qty;
                    } else {
                        $qty_string = $qty;
                    }
                    
                    /*get qty base on config setting**/
                    $sku_qty_shipped = $sku_order_id_sqty[$order_id][$sku];
                    $sku_qty_invoiced = $sku_order_id_iqty[$order_id][$sku];
                    
                    $qty_string = $this->getQtyString($from_shipment, $sku_qty_shipped, $qty, $sku_qty_invoiced);

                    if($generalConfig['font_family_body'] == 'traditional_chinese' || $generalConfig['font_family_body'] == 'simplified_chinese'){
                        $font_family_body_temp = $generalConfig['font_family_body'];
                        $generalConfig['font_family_body'] = 'helvetica';
                    }
                    $red_color = new Zend_Pdf_Color_Html('darkRed');
                    if($show_qty_options != 2)
                        $qty_string = round($qty_string, 2);
                    if ($product_qty_upsize_yn == 1 && $qty_string > 1 && $show_bundle_parent) {
                        if ($product_qty_rectangle == 1) {
                            $page->setLineWidth(0.5);
                            $page->setLineColor($black_color);
                            $page->setFillColor($black_color);
                            if(($qty_string >= 100) || (strlen($qty_string) > 3))
                                $page->drawRectangle(($qtyX), ($this->y - 1), ($qtyX - 18 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                            else 
                                if(($qty_string >= 10) || (strlen($qty_string) >= 2))
                                    $page->drawRectangle(($qtyX-1), ($this->y - 1), ($qtyX - 8 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                else
                                    $page->drawRectangle(($qtyX - 1), ($this->y - 1), ($qtyX - 2 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                            $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                            if(isset($yTempCombinePos)&&$yTempCombinePos)
                                $page->drawText($qty_string, ($qtyX), ($yTempCombinePos), 'UTF-8');
                            else
                                $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                        }
                        else{
                            $this->_setFont($page, 'bold', ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            if(isset($yTempCombinePos) && $yTempCombinePos)
                                $page->drawText($qty_string, ($qtyX), ($yTempCombinePos), 'UTF-8');
                            else
                                $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                        }
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    }
                    elseif($show_bundle_parent){
                        if(isset($yTempCombinePos)&&$yTempCombinePos)
                            $page->drawText($qty_string, $qtyX, $yTempCombinePos, 'UTF-8');
                        else
                            $page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');
                    }
                    if(isset($font_family_body_temp)){
                        $generalConfig['font_family_body'] = $font_family_body_temp;
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    }
                    $display_sku = '';
                    if (strlen($sku) > ($character_breakpoint_sku + 2)) {
                        $display_sku = substr(htmlspecialchars_decode($dsku), 0, ($character_breakpoint_sku)) . '…';
                    }
                    else $display_sku = htmlspecialchars_decode($dsku);

                    if ($skuyn == 1 && $show_bundle_parent){
                        if(isset($yTempCombinePos)&&$yTempCombinePos)
                            $page->drawText($display_sku . $sku_addon, $skuX + $max_qty_length_display, $yTempCombinePos, 'UTF-8');
                        else
                            $page->drawText($display_sku . $sku_addon, $skuX + $max_qty_length_display, $this->y, 'UTF-8');
                    }

                    if ($nameyn == 1 && isset($sku_name[$sku]) && $show_bundle_parent)
                    {

                        if($this->_getConfig('product_name_bold_yn_separated', $namenudgeYN_default, false, 'picks')){
                           $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        }
                        $print_name = $sku_name[$sku]. (isset($name_addon) ? $name_addon : '');
                        $next_col_to_name= getPrevNext2($columns_xpos_array,'Name','next',$padded_right - 30);
                        $max_width_length = ($next_col_to_name - $namenudge);
                        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $line_width_name= $this->parseString($print_name,$font_temp,$generalConfig['font_size_body']);
                        $char_width_name= $line_width_name/(strlen($print_name));
                        $max_chars_name = round($max_width_length/$char_width_name);
                        if($name_trim_wrap == "trim"){
                        $name_trim = str_trim($print_name,'WORDS',$max_chars_name-3,'...');
                        $name_trim2 = str_trim($print_name,'CHARS',$max_chars_name-3,'...');
                        if(isset($yTempCombinePos)&&$yTempCombinePos)
                            $page->drawText($name_trim2,intval($namenudge), $yTempCombinePos , 'UTF-8');
                        else
                            $page->drawText($name_trim2,intval($namenudge), $this->y , 'UTF-8');
                    }
                        else{
                            $name_arr = explode("\n", wordwrap($print_name, $max_chars_name, "\n"));
                            if(is_array($name_arr)){
                                for($j =0; $j< count($name_arr); $j++){
                                    $page->drawText($name_arr[$j],intval($namenudge), $yTempCombinePos , 'UTF-8');
                                    if($j < count($name_arr)-1)
                                        $this->y -= ($generalConfig['font_size_body'] + 2);
                                }
                            }
                            else
                                $page->drawText($print_name,intval($namenudge), $yTempCombinePos , 'UTF-8');
                        }
                        
                    }
                    
                    if ($product_barcode_yn == 1 && $show_bundle_parent) {
                        $barcode_font_size = 20;
                        //$page->drawText($productId, $product_barcode_X -20, $this->y , 'UTF-8');
                        $productbarcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($productId, $generalConfig['barcode_type']);
                        $productbarcodeWidth = $this->parseString($productId, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                        if($grey_next_line == 0)
                        {
                            $page->setFillColor($white_color);
                            $page->setLineColor($white_color);
                            $page->drawRectangle(($product_barcode_X - 5), ($this->y+ 10), ($productbarcodeWidth*2+$product_barcode_X - 10), ($this->y -5));
                        }
                    
                        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                        $page->drawText($productbarcodeString, ($product_barcode_X), ($yTempCombinePos + 3 - $barcode_font_size), 'CP1252');
                        //print white rectangle
                        $page->setFillColor($white_color);
                        $page->setLineColor($white_color);
                        $page->drawRectangle(($product_barcode_X - 2), ($this->y - 3), ($product_barcode_X + $productbarcodeWidth + $productbarcodeWidth + $barcode_font_size), ($this->y - $barcode_font_size -5));
                        if ($product_barcode_bottom_yn == 1) {
                            $this->_setFont($page, $generalConfig['font_style_body'], ($product_barcode_bottom_font_size), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            $page->drawText($childProductId, $product_barcode_X + $productbarcodeWidth*0.7, $yTempCombinePos - 9, 'UTF-8');
                        }
                    }

                    /**
                    images start
                     */
                    if (($product_images_yn == 1) && isset($sku_image_paths[$sku]['path'][0]) && $show_bundle_parent) {
                        $product_images_line_nudge = 0;
                        $product_images_line_nudge = ($sku_image_paths[$sku]['height'] / 2);
                        if ($product_images_border_color_temp != '#FFFFFF') {
                            $product_images_line_nudge += 1.5;
                        }

                        if (isset($sku_master_runcount) && $sku_master_runcount == 1) $this->y += ($product_images_line_nudge);

                        $image_x_addon = 0;
                        $image_x_addon_2 = 0;
                        $x1 = $col_title_product_images[1];
                        $y1 = ($this->y - $sku_image_paths[$sku]['height'] );
                        $x2 = ($col_title_product_images[1] + $sku_image_paths[$sku]['width']);
                        $y2 = ($this->y);

                        if (($this->y - $sku_image_paths[$sku]['height'] - 3) < (20 + ($generalConfig['font_size_subtitles'] * 2))) {
                            if ($page_count == 1 && isset($return_address_yn) && $return_address_yn == 0 && isset($bottom_shipping_address_yn) && $bottom_shipping_address_yn == 0) {
                                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($generalConfig['font_size_subtitles'] * 2), 'UTF-8');
                            }
                            $page = $this->newPage();
                            $page_count++;
                            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                            if (isset($second_page_start) && $second_page_start == 'asfirst') $this->y = $items_header_top_firstpage;
                            else $this->y = $page_top;

                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y), 'UTF-8');
                            $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                            $this->_setFont($page, 'bold', $generalConfig['font_size_body'] + 2, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);


                            $this->y -= ($generalConfig['font_size_subtitles'] * 1.5);
                            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        }
                        
                        $y1 = ($this->y - $sku_image_paths[$sku]['height'] - 5);
                        $y2 = ($this->y - 5);
                        
                        $image_ext = '';
                        $image_part = explode('.', $sku_image_paths[$sku]['path'][0]);
                        $image_ext = array_pop($image_part);
                        if (($image_ext != 'jpg') && ($image_ext != 'JPG') && ($image_ext != 'jpeg') && ($image_ext != 'png') && ($image_ext != 'PNG')) continue;
                        try{
                            if(!$helper->checkTypeImageProduct($sku_image_paths[$sku]['path'][0], $image_ext)){
                                    imagepng(imagecreatefromstring(file_get_contents($sku_image_paths[$sku]['path'][0])), $image_part[0] . '.png');
                                    $sku_image_paths[$sku]['path'][0] = $image_part[0] . '.png';
                            }
                            $image = Zend_Pdf_Image::imageWithPath($sku_image_paths[$sku]['path'][0]);
                            if ($product_images_border_color_temp != '#FFFFFF') {
                                $page->setLineWidth(0.5);
                                $page->setFillColor($product_images_border_color);
                                $page->setLineColor($product_images_border_color);
                                $page->drawRectangle(($x1 - 1.5 + $image_x_addon_2), ($y1 - 1.5), ($x2 + 1.5 + $image_x_addon_2), ($y2 + 1.5));
                                $page->setFillColor($black_color);
                            }

                            
                            $page->drawImage($image, $x1 + $image_x_addon_2, $y1, $x2 + $image_x_addon_2, $y2);
//                            if($giftmessage_yn == 1 && $value['has_message'] == 1)
//                                $this->y -= (2*$generalConfig['font_size_body'] + 20 + $sku_image_paths[$sku]['height']);
//                            else
//                                $this->y -= (2*$generalConfig['font_size_body'] + 35 + $sku_image_paths[$sku]['height']);
                            $this->y = $y1 - $generalConfig['font_size_body'];
//                            if (!isset($product_build_value['bundle_options_sku'])) {
//                                $this->y += ($product_images_line_nudge - ($generalConfig['font_size_body'] / 2));
//                            }
                            $has_shown_product_image = 1;
                        }
                        catch(Exception $e){

                        }
                    }
                    /**
                    images end
                     */
                    //  product gift message
                    if($giftmessage_yn == 1 && $show_bundle_parent){
                        $product_giftmessage_xpos = 45;
                        if ($value['has_message'] == 1) {
                            if($has_shown_product_image == 0)
                                $this->y -= $generalConfig['font_size_body'] ;                                
                            $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            $page->drawText("Gift Message: ", $product_giftmessage_xpos, $this->y, 'UTF-8');
                            $width_title = $this->getWidthString("Gift Message: ", $generalConfig['font_size_body']);
                            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                            $temp_height = 0;
                            foreach ($value['message-content'] as $gift_msg_line) {
                                $temp_height += 2 * $generalConfig['font_size_body'] + 3;
                            }
                            foreach ($value['message-content'] as $gift_msg_line) {
                                if (($this->y) < 40) {
                                    if ($page_count == 1 && isset($return_address_yn) && $return_address_yn == 0 && isset($bottom_shipping_address_yn) && $bottom_shipping_address_yn == 0) {
                                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($generalConfig['font_size_subtitles'] * 2), 'UTF-8');
                                    }
                                    $page = $this->newPage();
                                    $page_count++;
                                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                                    if (isset($second_page_start) && $second_page_start == 'asfirst') $this->y = $items_header_top_firstpage;
                                    else $this->y = $page_top;

                                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y), 'UTF-8');
                                    $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                                    $this->_setFont($page, 'bold', $generalConfig['font_size_body'] + 2, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                    $this->y -= ($generalConfig['font_size_subtitles'] * 1.5);
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                }
                               $page->drawText(trim($gift_msg_line), $product_giftmessage_xpos + $width_title + 2, $this->y, 'UTF-8');
                               $this->y -= ($generalConfig['font_size_body'] + 3);
                            }
                            $this->y -= $generalConfig['font_size_body'];
                        }
                    }
                    
                    if (isset($sku_stock[$sku]) && ($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1 && $show_bundle_parent) {
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                        $this->y -= ($generalConfig['font_size_body'] + 2);
                        $page->setFillColor($red_bkg_color);
                        $page->setLineColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
                        $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                        $warning = 'Stock Warning      ' . '    Net Stock After All Picks : ' . $sku_stock[$sku];
                        $page->drawText($warning, 60, $this->y, 'UTF-8');
                        $this->y -= 4;
                    }


                    if (isset($sku_order_id_options[$order_id][$sku]) && $sku_order_id_options[$order_id][$sku] != '') {
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        //TODO Moo Continue here 2
                        if ($options_yn == 0 && $options_yn_base == 1) {
                            $this->y -= ($generalConfig['font_size_body']);
                            $page->drawText($helper->__('Options') . ': ' . $sku_order_id_options[$order_id][$sku], ($namenudge + 20), $this->y, 'UTF-8');
                        }
                        elseif ($options_yn_base == 'yesstacked')
                        {
                            $this_item_options = '';
                            $this_item_options_sku = '';
                            $this_item_options = trim(str_replace('Array[', '[', $sku_order_id_options[$order_id][$sku]));
                            $this_item_options_sku = trim(str_replace('Array[', '[', $sku_order_id_options_sku[$order_id][$sku]));

                            $maxWidthPage = ($padded_right + 20 - 80);
                            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            $font_size_compare = ($generalConfig['font_size_options']);
                            $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                            $char_width = $line_width / 10;
                            $max_chars = round($maxWidthPage / $char_width);
                            //TODO add show sku of option: 
                            $chunks_sku = explode('[', $this_item_options_sku);
                                $chunks = explode('[', $this_item_options);
                                
                                $offset = 20;
                                $lines = 0;
                                foreach ($chunks as $key => $chunk) {
                                    $chunk_display = '';
                                $chunk_display_sku = '';
                                $chunk = str_replace('(Select Qty)', '',$chunk);
                                    if (trim($chunk != '')) {
                                        $this->y -= ($generalConfig['font_size_options']);
                                        $chunk_display = str_replace(']', '',$chunk);
                                        $page->drawText($chunk_display,intval($namenudge) + $offset, $this->y, 'UTF-8');
                                        $lines++;
                                    }
                                    if($chunks_sku[$key] != ''){
                                        $chunk_display_sku = str_replace(']', '', $chunks_sku[$key]);
                                        $page->drawText($chunk_display_sku, $skuX + $offset, $this->y, 'UTF-8');
                                    }
                                }

                                unset($chunks);
                        } 
                        elseif ($options_yn_base == 1) {
                            ksort($options_sku_parent[$order_id][$sku]);
                            foreach ($options_sku_parent[$order_id][$sku] as $options_sku => $options_qty) {
                                if (!in_array($options_sku, $pickpack_options_filter_array))
                                {
                                    $this->y -= $generalConfig['font_size_body'];
                                    if ($tickbox != 0) {
                                        $page->setFillColor($white_color);
                                        $page->setLineColor($black_color);
                                        $page->setLineWidth(0.5);
                                        $page->drawRectangle($tickbox_X + 4, ($this->y), $tickbox_X + 4 + 7, ($this->y + 7));
                                        
                                        if($tickbox2 != 0)
                                        {
                                            $page->drawRectangle($tickbox2_X , ($this->y), $tickbox2_X + 7, ($this->y + 7));
                                        }
                                        $page->setLineWidth(0.5);
                                    }
                                    $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                                    if (!in_array($options_sku, $pickpack_options_count_filter_array)) {
                                        $page->drawText($options_qty, ($qtyX + 4), $this->y, 'UTF-8');
                                        if ($skuyn == 1)
                                            $page->drawText(' [ ' . $options_sku . ' ]', ($skuX + $max_qty_length_display + 6), $this->y, 'UTF-8');
                                    } else {
                                        if ($skuyn == 1)
                                            $page->drawText('   [ ' . $options_sku . ' ]', ($skuX + $max_qty_length_display + 6), $this->y, 'UTF-8');
                                    }

                                    if (isset($options_name[$order_id][$sku][$options_sku]) && $nameyn == 1) {
                                        $page->drawText($options_name[$order_id][$sku][$options_sku], ($namenudge + 4), $this->y, 'UTF-8');
                                    }
                                }
                            }
                        }
                    }

                    if (isset($sku_bundle[$order_id][$sku]) && is_array($sku_bundle[$order_id][$sku])) {
                        $offset = 10;
                        $box_x = ($qtyX - $offset);
                        if($show_bundle_parent)
                        $this->y -= ($generalConfig['font_size_body']);
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'],
                        $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        if( $is_split && $show_bundle_parent_yn == 1) {
                             
                             foreach( $sku_bundle[$order_id][$sku] as $key => $value ) {
                                $childArray[] = $value;
                             }
                             
                        } else {
                        $page->drawText($helper->__('Bundle Options') . ' : ', $box_x, $this->y, 'UTF-8');
                        $grey_next_line_bundle = 0;                     
                        foreach ($sku_bundle[$order_id][$sku] as $key => $value) {

                            $sku = '';
                            $name = '';
                            $shelf = '';
                            $shelf2 = '';
                            $qty = '';
                            $sku_bundle_array = explode('##', $value);
                            $sku = $sku_bundle_array[0];
                            $name = $sku_bundle_array[1];
                            $shelf = $sku_bundle_array[2];
                            $qty = $sku_bundle_array[3];
                            $total_quantity += $qty;
                            $childProductId = $sku_bundle_array[4];
                            $shelf2 = $sku_bundle_array[5];
                            
                            $this->y -= ($generalConfig['font_size_body'] - 0);
                            
                            if($grey_next_line_bundle == 1)
                            {
                                $page->setFillColor($alternate_row_color);
                                $page->setLineColor($alternate_row_color);

                                $grey_box_y1 = ($this->y-(($generalConfig['font_size_body']-2)/5));
                                $grey_box_y2 = ($this->y+(($generalConfig['font_size_body']-2)*0.85));

                                $page->drawRectangle(40, $grey_box_y1, $padded_right, $grey_box_y2);
                                $grey_next_line_bundle = 0;
                            }
                            else $grey_next_line_bundle = 1;
                            
                            if ($tickbox != 0) {
                                $page->setFillColor($white_color);
                                $page->setLineColor($black_color);
                                $page->setLineWidth(0.5);
                                $page->drawRectangle($tickbox_X + 4, ($this->y), $tickbox_X + 4 + 7, ($this->y + 7));
                                        
                                if($tickbox2 != 0)
                                {
                                    $page->drawRectangle($tickbox2_X , ($this->y), $tickbox2_X +  7, ($this->y + 7));
                                }
                                $page->setLineWidth(0.5);
                            }
                            
                            $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            if ($skuyn == 1)
                                $page->drawText($sku, ($skuX + $skuXInc + 10), $this->y, 'UTF-8');
                                
                            //$page->drawText($sku, ($skuX + $skuXInc + 10), $this->y, 'UTF-8');
                            if($nameyn ==1){
                                  if($this->_getConfig('product_name_bold_yn_separated', $namenudgeYN_default, false, 'picks')){
                                      $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                  }
                                $page->drawText($name, intval($namenudge + 10), $this->y, 'UTF-8');
                            }
                            if ($shelving_yn == 1 && trim($shelf) != '') {                
                                $page->drawText($shelf, $shelvingX, $this->y, 'UTF-8');
                            }
                            //shelving 2
                            if ($shelving2_yn == 1 && trim($shelf2) != '') {                
                                $page->drawText($shelf2, $shelving2X, $this->y, 'UTF-8');
                            }
                          /************/
                            if ($product_qty_upsize_yn == 1 && $qty > 1) {
                            if ($product_qty_rectangle == 1) {
                                $page->setLineWidth(0.5);
                                $page->setLineColor($black_color);
                                $page->setFillColor($black_color);
                                if($qty >= 100)
                                    $page->drawRectangle(($qtyX), $this->y, ($qtyX - 1 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                    else 
                                        if($qty >= 10)
                                            $page->drawRectangle(($qtyX-1), $this->y, ($qtyX - 7 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                        else
                                            $page->drawRectangle(($qtyX - 1), $this->y, ($qtyX - 2 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                                $page->drawText($qty, ($qtyX), ($this->y), 'UTF-8');
                            }
                            else{
                                $this->_setFont($page, 'bold', ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $page->drawText($qty, ($qtyX), ($this->y), 'UTF-8');
                            }
                            
                        }
                        else
                            $page->drawText($qty, $qtyX, $this->y, 'UTF-8');
                            //print child barcode
                            if ($product_barcode_yn == 1) {
                                $barcode_font_size = 20;
                                $productbarcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($childProductId, $generalConfig['barcode_type']);
                                $productbarcodeStringWidth = $this->parseString($productbarcodeString, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                                if($grey_next_line_bundle == 0)
                                {
                                    $page->setFillColor($white_color);
                                    $page->setLineColor($white_color);
                                    $page->drawRectangle(($product_barcode_X - 5), ($this->y+ 10), ($productbarcodeWidth*2+$product_barcode_X - 10), ($this->y -5));
                                }
                
                                $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                                $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                                $page->drawText($productbarcodeString, ($product_barcode_X), ($this->y + 3 - $barcode_font_size), 'CP1252');
                                //print white rectangle
                                $page->setFillColor($white_color);
                                $page->setLineColor($white_color);
                                $page->drawRectangle(($product_barcode_X - 2), ($this->y - 3), ($product_barcode_X + $productbarcodeWidth + $productbarcodeWidth + $barcode_font_size), ($this->y - $barcode_font_size -5));
                                if ($product_barcode_bottom_yn == 1) {
                                    $this->_setFont($page, $generalConfig['font_style_body'], ($product_barcode_bottom_font_size), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    $page->drawText($childProductId, $product_barcode_X + $productbarcodeWidth*0.7, $this->y - 9, 'UTF-8');
                                }

                            }
                            
                            
                            $has_shown_product_image = 0;
                            if (($product_images_yn == 1) && !isset($sku_image_paths[$sku]['path'][0])) // ie only get sku image paths if not previously got in this combined request
                            {
                                $imagePaths = array();
                                if($product_images_parent_yn == 1)
                                    $product_id = Mage::helper("pickpack")->getParentProId($product_id);
                                $product_images_source_res = $helper->getSourceImageRes($product_images_source, $product_id);
                                $img_demension = $helper->getWidthHeightImage($product_id, $product_images_source_res, $product_images_maxdimensions);
                                if(is_array($img_demension) && count($img_demension) > 1){
                                    $sku_image_paths[$sku]['width'] = $img_demension[0];
                                    $sku_image_paths[$sku]['height'] = $img_demension[1];
                                }
                                $imagePaths = $helper->getImagePaths($product_id,$product_images_source , $product_images_maxdimensions);
                                $imagePath = '';
                                foreach ($imagePaths as $imagePath) {
                                    $image_url = $imagePath;
                                    $image_url_after_media_path_with_media = strstr($image_url, '/media/'); 
                                    $image_url_after_media_path = strstr_after($image_url, '/media/');
                                    $final_image_path = $media_path . '/' . $image_url_after_media_path;
                                    $sku_image_paths[$sku]['path'][] = $final_image_path;
                                }
                            }
                            /**
                            images PRELOADER end
                             */
                 
                             /**
                            images start
                             */
                            if (($product_images_yn == 1) && isset($sku_image_paths[$sku]['path'][0])) {
                                $product_images_line_nudge = 0;
                                $product_images_line_nudge = ($sku_image_paths[$sku]['height'] / 2);
                                if ($product_images_border_color_temp != '#FFFFFF') {
                                    $product_images_line_nudge += 1.5;
                                }

                                if (isset($sku_master_runcount) && $sku_master_runcount == 1) $this->y += ($product_images_line_nudge);

                                $image_x_addon = 0;
                                $image_x_addon_2 = 0;
                                $x1 = $col_title_product_images[1];
                                $y1 = ($this->y - $sku_image_paths[$sku]['height'] );
                                $x2 = ($col_title_product_images[1] + $sku_image_paths[$sku]['width']);
                                $y2 = ($this->y);

                                if (($this->y - $sku_image_paths[$sku]['height'] - 3) < (20 + ($generalConfig['font_size_subtitles'] * 2))) {
                                    if ($page_count == 1) {
                                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($generalConfig['font_size_subtitles'] * 2), 'UTF-8');
                                    }
                                    $page = $this->newPage();
                                    $page_count++;
                                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                                    $this->y = $page_top;

                                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y), 'UTF-8');
                                    $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                                    $this->_setFont($page, 'bold', $generalConfig['font_size_body'] + 2, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                    $this->y -= ($generalConfig['font_size_subtitles'] * 1.5);
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                }
                        
                                $y1 = ($this->y - $sku_image_paths[$sku]['height'] - 5);
                                $y2 = ($this->y - 5);
                                $image_ext = '';
                                $image_part = explode('.', $sku_image_paths[$sku]['path'][0]);
                                $image_ext = array_pop($image_part);
                                if (($image_ext != 'jpg') && ($image_ext != 'jpeg') && ($image_ext != '.png')) continue;

                                if ($product_images_border_color_temp != '#FFFFFF') {
                                    $page->setLineWidth(0.5);
                                    $page->setFillColor($product_images_border_color);
                                    $page->setLineColor($product_images_border_color);
                                    $page->drawRectangle(($x1 - 1.5 + $image_x_addon_2), ($y1 - 1.5), ($x2 + 1.5 + $image_x_addon_2), ($y2 + 1.5));
                                    $page->setFillColor($black_color);
                                }

                                $image = Zend_Pdf_Image::imageWithPath($sku_image_paths[$sku]['path'][0]);
                                $page->drawImage($image, $x1 + $image_x_addon_2, $y1, $x2 + $image_x_addon_2, $y2);
//                                 $this->y -= (2*$generalConfig['font_size_body'] + 35 + $sku_image_paths[$sku]['height']);
//                                if (!isset($product_build_value['bundle_options_sku'])) {
//                                    $this->y += ($product_images_line_nudge - ($generalConfig['font_size_body'] / 2));
//                                }
                                $this->y = $y1 - $generalConfig['font_size_body'];
                                $has_shown_product_image = 1;
                            }
                            //images end
                            if ($product_barcode_yn == 1) {
                                $this->y -= 4;
                                if ($product_barcode_bottom_yn == 1)
                                    $this->y -= 6;

                            }
                        }
                     }  
                   }

                    //More space for barcode
                    if ($product_barcode_yn == 1) {
                        $this->y -= 4;
                        if ($product_barcode_bottom_yn == 1)
                            $this->y -= 4;
                    }
                    if($address_label_yn == 1){
                        $left_label_address = $padded_right - ($label_width + 5) + $nudge_shipping_address[0];
                        $page->setLineWidth(0.5);
                        $page->drawLine($qtyX, $this->y - 3, $left_label_address - 5, $this->y - 3);
                        $this->y -= 2;
                    }
                        $doubleline_yn =  $this->_getConfig('doubleline_yn_separted',0 , false, 'picks');
                        if ($doubleline_yn == 2)
                            $this->y -= 2 * $generalConfig['font_size_body'];
                        else if ($doubleline_yn == 1.5)
                            $this->y -= 1.5 * $generalConfig['font_size_body'];
                        else if ( $doubleline_yn == 3 )
                            $this->y -= 3 * $generalConfig['font_size_body'];
                        else
                           $this->y -= ($generalConfig['font_size_body'] + 1);                        
                }
                // end roll_SKU
                // end each section
                
                if($address_label_yn == 1){
                    $this->y = $y_start_order - $subsection_order_height;
                    $page->setLineWidth(2);
                    $page->drawLine($padded_left + 7, $this->y + 10, ($padded_right), $this->y + 10);
                }
            }
            //end roll orders
            
             if( count( $childArray ) > 0 ) {
                   $arr = array();
                       $arr_key = array();
                    $this->y += 10;
                   foreach($childArray as $key => $value){
                      $sku_bundle_array = explode('##', $value);
                      
                      if( in_array($sku_bundle_array[0],$arr) ) {
                            $sku_bundle_array[3] =  $arr_qty[$sku_bundle_array[0]] + $sku_bundle_array[3];
                            $childArray[$key] = implode("##",$sku_bundle_array);
                            unset($childArray[$arr_key[$sku_bundle_array[0]]]);
                        }                     
                      $arr[] = $sku_bundle_array[0];    
                      $arr_qty[$sku_bundle_array[0]] = $sku_bundle_array[3];                      
                      $arr_key[$sku_bundle_array[0]] = $key;
                   }
                        $grey_next_line_bundle = 0;
                        foreach ($childArray as $key => $value) {
                            $sku = '';
                            $name = '';
                            $shelf = '';
                            $shelf2 = '';
                            $qty = '';
                            $sku_bundle_array = explode('##', $value);
                            $sku = $sku_bundle_array[0];
                            $name = $sku_bundle_array[1];
                            $shelf = $sku_bundle_array[2];
                            $qty = $sku_bundle_array[3];
                            $total_quantity += $qty;
                            $childProductId = $sku_bundle_array[4];
                            $shelf2 = $sku_bundle_array[5];
                            
                            $this->y -= ($generalConfig['font_size_body'] - 0);
                            
                            if($grey_next_line_bundle == 1)
                            {
                                $page->setFillColor($alternate_row_color);
                                $page->setLineColor($alternate_row_color);

                                $grey_box_y1 = ($this->y-(($generalConfig['font_size_body']-2)/5));
                                $grey_box_y2 = ($this->y+(($generalConfig['font_size_body']-2)*0.85));

                                $page->drawRectangle(40, $grey_box_y1, $padded_right, $grey_box_y2);
                                $grey_next_line_bundle = 0;
                            }
                            else $grey_next_line_bundle = 1;
                            
                             if ($tickbox != 0) {
                                $page->setFillColor($white_color);
                                $page->setLineColor($black_color);
                                $page->setLineWidth(0.5);
                                $page->drawRectangle($tickbox_X, ($this->y), $tickbox_X + 7, ($this->y + 7));
                                        
                                if($tickbox2 != 0)
                                {
                                        $page->drawRectangle($tickbox2_X , ($this->y), $tickbox2_X +  7, ($this->y + 7));
                                }
                                if($this->_getConfig('pickpack_tickbox_separated_signature_line', $namenudgeYN_default, false, 'picks')){
                                      $page->drawLine(($tickbox_X + 8), ($this->y), ($tickbox_X * 2 - 5 ), ($this->y));                     
                                 }
                                $page->setLineWidth(0.5);
                            }
                            
                            $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            if ($skuyn == 1)
                                $page->drawText($sku, ($skuX + $skuXInc), $this->y, 'UTF-8');
                                
                            //$page->drawText($sku, ($skuX + $skuXInc + 10), $this->y, 'UTF-8');
                            if($nameyn ==1){                                                                
                                if($this->_getConfig('product_name_bold_yn_separated', $namenudgeYN_default, false, 'picks')){
                                        $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                 }
                                $page->drawText($name, intval($namenudge), $this->y, 'UTF-8');
                            }
                            if ($shelving_yn == 1 && trim($shelf) != '') {                
                                $page->drawText($shelf, $shelvingX, $this->y, 'UTF-8');
                            }
                            //shelving 2
                            if ($shelving2_yn == 1 && trim($shelf2) != '') {                
                                $page->drawText($shelf2, $shelving2X, $this->y, 'UTF-8');
                            }
                          /************/
                            if ($product_qty_upsize_yn == 1 && $qty > 1) {
                            if ($product_qty_rectangle == 1) {
                                $page->setLineWidth(0.5);
                                $page->setLineColor($black_color);
                                $page->setFillColor($black_color);
                                if($qty >= 100)
                                    $page->drawRectangle(($qtyX), $this->y, ($qtyX - 1 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                    else 
                                        if($qty >= 10)
                                            $page->drawRectangle(($qtyX-1), $this->y, ($qtyX - 7 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                        else
                                            $page->drawRectangle(($qtyX - 1), $this->y, ($qtyX - 2 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body']*1.2));
                                $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                                $page->drawText($qty, ($qtyX), ($this->y), 'UTF-8');
                            }
                            else{
                                $this->_setFont($page, 'bold', ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $page->drawText($qty, ($qtyX), ($this->y), 'UTF-8');
                            }
                            
                        }
                        else
                            $page->drawText($qty, $qtyX, $this->y, 'UTF-8');
                            //print child barcode
                            if ($product_barcode_yn == 1) {
                                $barcode_font_size = 20;
                                $productbarcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($childProductId, $generalConfig['barcode_type']);
                                $productbarcodeStringWidth = $this->parseString($productbarcodeString, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                                if($grey_next_line_bundle == 0)
                                {
                                    $page->setFillColor($white_color);
                                    $page->setLineColor($white_color);
                                    $page->drawRectangle(($product_barcode_X - 5), ($this->y+ 10), ($productbarcodeWidth*2+$product_barcode_X - 10), ($this->y -5));
                                }
                
                                $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                                $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                                $page->drawText($productbarcodeString, ($product_barcode_X), ($this->y + 3 - $barcode_font_size), 'CP1252');
                                //print white rectangle
                                $page->setFillColor($white_color);
                                $page->setLineColor($white_color);
                                $page->drawRectangle(($product_barcode_X - 2), ($this->y - 3), ($product_barcode_X + $productbarcodeWidth + $productbarcodeWidth + $barcode_font_size), ($this->y - $barcode_font_size -5));
                                if ($product_barcode_bottom_yn == 1) {
                                    $this->_setFont($page, $generalConfig['font_style_body'], ($product_barcode_bottom_font_size), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    $page->drawText($childProductId, $product_barcode_X + $productbarcodeWidth*0.7, $this->y - 9, 'UTF-8');
                                }

                            }

                            $has_shown_product_image = 0;
                            if (($product_images_yn == 1) && !isset($sku_image_paths[$sku]['path'][0])) // ie only get sku image paths if not previously got in this combined request
                            {
                                $imagePaths = array();
                                if($product_images_parent_yn == 1)
                                    $product_id = Mage::helper("pickpack")->getParentProId($product_id);
                                $product_images_source_res = $helper->getSourceImageRes($product_images_source, $product_id);
                                $img_demension = $helper->getWidthHeightImage($product_id, $product_images_source_res, $product_images_maxdimensions);
                                if(is_array($img_demension) && count($img_demension) > 1){
                                    $sku_image_paths[$sku]['width'] = $img_demension[0];
                                    $sku_image_paths[$sku]['height'] = $img_demension[1];
                                }
                                $imagePaths = $helper->getImagePaths($product_id,$product_images_source , $product_images_maxdimensions);
                                $imagePath = '';
                                foreach ($imagePaths as $imagePath) {
                                    $image_url = $imagePath;
                                    $image_url_after_media_path_with_media = strstr($image_url, '/media/'); 
                                    $image_url_after_media_path = strstr_after($image_url, '/media/');
                                    $final_image_path = $media_path . '/' . $image_url_after_media_path;
                                    $sku_image_paths[$sku]['path'][] = $final_image_path;
                                }
                            }
                             /**images start*/
                            if (($product_images_yn == 1) && isset($sku_image_paths[$sku]['path'][0])) {
                                $product_images_line_nudge = 0;
                                $product_images_line_nudge = ($sku_image_paths[$sku]['height'] / 2);
                                if ($product_images_border_color_temp != '#FFFFFF') {
                                    $product_images_line_nudge += 1.5;
                                }

                                if (isset($sku_master_runcount) && $sku_master_runcount == 1) $this->y += ($product_images_line_nudge);

                                $image_x_addon = 0;
                                $image_x_addon_2 = 0;
                                $x1 = $col_title_product_images[1];
                                $y1 = ($this->y - $sku_image_paths[$sku]['height'] );
                                $x2 = ($col_title_product_images[1] + $sku_image_paths[$sku]['width']);
                                $y2 = ($this->y);

                                if (($this->y - $sku_image_paths[$sku]['height'] - 3) < (20 + ($generalConfig['font_size_subtitles'] * 2))) {
                                    if ($page_count == 1 && $return_address_yn == 0 && $bottom_shipping_address_yn == 0) {
                                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($generalConfig['font_size_subtitles'] * 2), 'UTF-8');
                                    }
                                    $page = $this->newPage();
                                    $page_count++;
                                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                                    if ($second_page_start == 'asfirst') $this->y = $items_header_top_firstpage;
                                    else $this->y = $page_top;

                                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y), 'UTF-8');
                                    $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                                    $this->_setFont($page, 'bold', $generalConfig['font_size_body'] + 2, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                    $this->y -= ($generalConfig['font_size_subtitles'] * 1.5);
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                }
                        
                                $y1 = ($this->y - $sku_image_paths[$sku]['height'] - 5);
                                $y2 = ($this->y - 5);
                        
                                $image_ext = '';
                                $image_part = explode('.', $sku_image_paths[$sku]['path'][0]);
                                $image_ext = array_pop($image_part);
                                if (($image_ext != 'jpg') && ($image_ext != 'jpeg') && ($image_ext != '.png')) continue;

                                if ($product_images_border_color_temp != '#FFFFFF') {
                                    $page->setLineWidth(0.5);
                                    $page->setFillColor($product_images_border_color);
                                    $page->setLineColor($product_images_border_color);
                                    $page->drawRectangle(($x1 - 1.5 + $image_x_addon_2), ($y1 - 1.5), ($x2 + 1.5 + $image_x_addon_2), ($y2 + 1.5));
                                    $page->setFillColor($black_color);
                                }

                                $image = Zend_Pdf_Image::imageWithPath($sku_image_paths[$sku]['path'][0]);
                                $page->drawImage($image, $x1 + $image_x_addon_2, $y1, $x2 + $image_x_addon_2, $y2);
//                                $this->y -= (2*$generalConfig['font_size_body'] + 35 + $sku_image_paths[$sku]['height']);
//                                if (!isset($product_build_value['bundle_options_sku'])) {
//                                    $this->y += ($product_images_line_nudge - ($generalConfig['font_size_body'] / 2));
//                                }
                                $this->y = $y1 - $generalConfig['font_size_body'];
                                $has_shown_product_image = 1;
                            }
                            /**
                            images end
                             */
                            
                           
                            if ($product_barcode_yn == 1) {
                                $this->y -= 4;
                                if ($product_barcode_bottom_yn == 1)
                                    $this->y -= 6;

                            }
                            
                        $doubleline_yn =  $this->_getConfig('doubleline_yn_separted',0 , false, 'picks');               
                        if ($doubleline_yn == 2)
                            $this->y -= 2 * $generalConfig['font_size_body'];
                        else if ($doubleline_yn == 1.5)
                            $this->y -= 1.5 * $generalConfig['font_size_body'];
                        else if ( $doubleline_yn == 3 )
                            $this->y -= 3 * $generalConfig['font_size_body'];
                        else
                           $this->y -= ($generalConfig['font_size_body'] + 1); 
                        }
            }
            $order_count_yn = 1;
            $thisYbase = $this->y - 30;
			
            if (($showcount_yn == 1) || ($showcost_yn == 1) || ($order_count_yn == 1)) {
                if ($page_count == 1)
                    $this->y = $thisYbase;
                $shipYbox = 0;
                if ($showcount_yn == 1)
                    $shipYbox += ($generalConfig['font_size_body'] * 2);
                
                if ($order_count_yn == 1)
                    $shipYbox += $generalConfig['font_size_body'];
                
                if ($showcost_yn == 1)
                    $shipYbox += $generalConfig['font_size_body'];
                
                if ($show_bundle_parent === true)
                    $shipYbox += $generalConfig['font_size_body'];

                if (($this->y - $shipYbox - $generalConfig['font_size_body']) < 60) {
                    if ($page_count == 1) {
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - 15), 'UTF-8');
                    }
                    $page = $this->newPage();
                    $page_count++;
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                }
                
                $page->setFillColor($white_bkg_color);
                $page->setLineColor($orange_bkg_color);
                $page->setLineWidth(1);
                
                $page->drawRectangle(340, ($this->y - $shipYbox - $generalConfig['font_size_body']), ($padded_right - 2), $this->y);
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            }
			
            if ($showcount_yn == 1)
				$this->writeSummary($helper->__('Items'), $total_quantity, $page, 'square');
			
            if ($show_bundle_parent === true && ($total_bundle_quantity > 0 ))
				$this->writeSummary($helper->__('Bundles'), $total_bundle_quantity, $page, 'circle');
			
            if ($order_count_yn == 1)
				$this->writeSummary($helper->__('Orders'), $order_count, $page, 'none');
						
            if (($showcost_yn == 1) && ($total_cost > 0))
				$this->writeSummary($helper->__('Total cost'), $currency_symbol . '  ' . $total_cost, $page, 'none');
							
            if ($printdates == 1) {
                $this->y -= $generalConfig['font_size_body'] * 1.4;
                $this->_setFontBold($page);
                $this->_printing_format['date_format'] = $this->_getConfig('date_format', 'M. j, Y', false, 'general');
                $currentTimestamp = Mage::getModel('core/date')->timestamp(time()); //Magento's timestamp function makes a usage of timezone and converts it to timestamp
                $printed_date = date($this->_printing_format['date_format'], $currentTimestamp); 
                //$page->drawText($helper->__('Printed:') . '   ' . date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())), 210, 18, 'UTF-8');
                $page->drawText($helper->__('Printed:') . '   ' . $printed_date, 210, 18, 'UTF-8');
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }
}

?>
