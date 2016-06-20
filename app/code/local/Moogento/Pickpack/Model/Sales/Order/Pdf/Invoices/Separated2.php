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

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Separated2 extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
	protected $warehouse_title = array();
	
    public function __construct() {
        parent::__construct();
    }

    public function getGeneralConfig() {
        return Mage::helper('pickpack/config')->getGeneralConfigArray($this->getStoreId());
    }
	
    private function getMaxchars($font_size_return_label, $value, $label_height) {
         $max_chars = 0;
        $font_temp      = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $max_line_width = ($label_height * 0.86);
        $value          = trim($value);
        $line_width     = $this->parseString($value, $font_temp, $font_size_return_label);
        if($value != "")
            $max_chars      = round($max_line_width / ($line_width / strlen($value)));
        return $max_chars;
    }
    private function getPaymentOrder($order) {
        $allAvailablePaymentMethods = Mage::getModel('payment/config')->getAllMethods();
        $payment_order              = $order->getPayment();
        foreach ($allAvailablePaymentMethods as $payment) {
            if ($payment->getId() == $payment_order->getMethod())
                return $payment_order;
        }
        return $payment_order = '';
    }
    //function for show qty//
    public function getQtyString($from_shipment, $sku_qty_shipped, $qty, $sku_qty_invoiced) {
        $store_id         = Mage::app()->getStore()->getId();
        $show_qty_options = $this->_getConfig('show_qty_options', 1, false, 'picks2', $store_id);
        switch ($show_qty_options) {
            case 1:
                $qty_string = $qty;
                
                break;
            case 2:
                $qty_string = 'q:' . ($qty - (int) $sku_qty_shipped) . ' s:' . (int) $sku_qty_shipped . ' o:' . (int) $qty;
                
                break;
            case 3:
                $qty_string = ($qty - (int) $sku_qty_shipped);
                
                break;
            
            case 4: //qty invoiced. 
                $qty_string = (int) $sku_qty_invoiced;
                
                break;
        }
        return $qty_string;
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
                    $found  = false;
                    foreach ($temp_array as $tmp_key => $tmp_val) {
                        if (!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey])) {
                            $temp_array = array_merge((array) array_slice($temp_array, 0, $offset), 
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
        
        $page_width               = $padded_right;
        $skuX                     = 67;
        $qtyX                     = 40;
        $productX                 = 250;
        $font_size_overall        = 15;
        $font_size_productline    = 9;
        $total_quantity           = 0;
        $total_cost               = 0;
        $red_bkg_color            = new Zend_Pdf_Color_Html('lightCoral');
        $config_group             = 'messages';
        $alternate_row_color_temp = $this->_getConfig('alternate_row_color', '#DDDDDD', false, 'general');
        $alternate_row_color      = new Zend_Pdf_Color_Html($alternate_row_color_temp);
        $white_bkg_color   = new Zend_Pdf_Color_Html('white');
        $orange_bkg_color  = new Zend_Pdf_Color_Html('Orange');
        $black_color       = new Zend_Pdf_Color_Rgb(0, 0, 0);
        $white_color       = new Zend_Pdf_Color_GrayScale(1);
                
		$background_color_orderdetails_zend = new Zend_Pdf_Color_Html('#CCCCCC');
		$background_color_orderdetails_lighter_zend = new Zend_Pdf_Color_Html('#EEEEEE');
        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['background_color_subtitles']);
        $font_color_body_zend            = new Zend_Pdf_Color_Html($generalConfig['font_color_body']);
        
        $shelvingpos      = $this->_getConfig('shelvingpos', 'col', false, 'general'); //col/sku
        $order_address_yn = $this->_getConfig('pickpack_order_address_yn', 0, false, 'picks2'); //col/sku
        $order_billing_address_yn = $this->_getConfig('pickpack_order_billing_address_yn', 0, false, 'picks2'); //col/sku
        $configurable_names           = $this->_getConfig('pickpack_configname_separated', 'simple', false, 'picks2'); //col/sku
        $configurable_names_attribute = trim($this->_getConfig('pickpack_configname_attribute_separated', '', false, 'picks2')); //col/sku
        if ($configurable_names != 'custom')
            $configurable_names_attribute = '';
        $barcodes                         = Mage::getStoreConfig('pickpack_options/picks2/pickpack_pickbarcode');
        $product_barcode_yn               = Mage::getStoreConfig('pickpack_options/picks2/pickpack_pickproductbarcode_yn');
        $product_barcode_X                = Mage::getStoreConfig('pickpack_options/picks2/pickpack_pickproductbarcode_X_Pos');
        $product_barcode_bottom_yn        = Mage::getStoreConfig('pickpack_options/picks2/pickpack_pickproductbarcode_bottom_yn');
        $product_barcode_bottom_font_size = Mage::getStoreConfig('pickpack_options/picks2/pickpack_pickproductbarcode_bottom_font_size');
        
        $printdates = Mage::getStoreConfig('pickpack_options/picks2/pickpack_pickprint'); //, $order->getStore()->getId());

        $fill_product_header_yn  = 0;

        $product_id           = NULL; // get it's ID
        $stock                = NULL;
        $sku_stock            = array();
        // pickpack_cost
        $showcost_yn_default  = 0;
        $showcount_yn_default = 0;
        $total_price = 0;
        $currency_default     = 'USD';
        
        $shelving_yn_default        = 0;
        $shelving_attribute_default = 'shelf';
        $shelvingX_default          = 200;
        $supplier_yn_default        = 0;
        $supplier_attribute_default = 'supplier';
        $namenudgeYN_default        = 0;
        $stockcheck_yn_default      = 0;
        $stockcheck_default         = 1;
        $shipping_method_x          = 0;
        $warehouse_x                = 0;
        
        /////// from 1
        $product_id                 = NULL; // get it's ID
        $stock                      = NULL;
        ///////// /from 1
        $split_supplier_yn_default  = 'no';
        $supplier_attribute_default = 'supplier';
        $supplier_options_default   = 'filter';
        $tickbox_default            = 0; //no, pick, pickpack
        
        // $split_supplier_yn             = $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
        $split_supplier_yn_temp = $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false, 'general');
        $split_supplier_options_temp = $this->_getConfig('pickpack_split_supplier_options', 'no', false, 'general');
        $split_supplier_options = explode(',',$split_supplier_options_temp);
        $split_supplier_yn      = 'no';
        $supplierKey = 'order_summary';
        if ($split_supplier_yn_temp == 1) {
			if(in_array($supplierKey,$split_supplier_options))
				$split_supplier_yn = 'pickpack';
			else
				$split_supplier_yn = 'no';
			
        }
        
        $supplier_attribute = $this->_getConfig('pickpack_supplier_attribute', $supplier_attribute_default, false, 'general');
        $supplier_options   = $this->_getConfig('pickpack_supplier_options', $supplier_options_default, false, 'general');
        
        $userId   = Mage::getSingleton('admin/session')->getUser() ? Mage::getSingleton('admin/session')->getUser()->getId() : 0;
        $user     = ($userId !== 0) ? Mage::getModel('admin/user')->load($userId) : '';
        $username = (!empty($user['username'])) ? $user['username'] : '';
        
        $supplier_login_pre = $this->_getConfig('pickpack_supplier_login', '', false, 'general');
        $supplier_login_pre = str_replace(array(
            "\n",
            ','
        ), ';', $supplier_login_pre);
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
        
        $tickbox    = $this->_getConfig('pickpack_tickbox_yn_separated', $tickbox_default, false, 'picks2');
        $tickbox_X  = $this->_getConfig('pickpack_tickboxnudge_separated', 7, false, 'picks2');
        $tickbox2   = $this->_getConfig('pickpack_tickbox2_yn_separated', $tickbox_default, false, 'picks2');
        $tickbox2_X = $this->_getConfig('pickpack_tickbox2nudge_separated', 27, false, 'picks2');
        
        if ($tickbox == 0) {
            $tickbox_X  = 0;
            $tickbox2   = 0;
            $tickbox2_X = 0;
        } else
            $qtyX = ($tickbox_X > $tickbox2_X) ? ($tickbox_X + 15) : ($tickbox2_X + 15);

        $logo_maxdimensions = explode(',', '269,41');
        $picklogo = $this->_getConfig('pickpack_picklogo', 0, false, 'general');
        $showcount_yn = $this->_getConfig('pickpack_count', $showcount_yn_default, false, 'picks2');
        $showcost_yn     = $this->_getConfig('pickpack_cost', $showcost_yn_default, false, 'picks2');
        $currency        = $this->_getConfig('pickpack_currency', $currency_default, false, 'picks2');
        $currency_symbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
        $currency_symbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        $stockcheck_yn = $this->_getConfig('pickpack_stock_yn_separated', $stockcheck_yn_default, false, 'picks2');
        $stockcheck    = $this->_getConfig('pickpack_stock_separated', $stockcheck_default, false, 'picks2');
        
        $shelving_yn        = $this->_getConfig('pickpack_shelving_yn_separated', $shelving_yn_default, false, 'picks2');
        $shelving_attribute = $this->_getConfig('pickpack_shelving_separated', $shelving_attribute_default, false, 'picks2');
        $shelvingX          = intval($this->_getConfig('pickpack_shelving_separated_Xnudge', $shelvingX_default, false, 'picks2'));
        
        $skuyn    = $this->_getConfig('pickpack_sku_yn_separated', $namenudgeYN_default, false, 'picks2');
        $skunudge = intval($this->_getConfig('pickpack_skunudge_separated', 0, false, 'picks2'));
        if ($skuyn == 1)
            $skuX = $this->_getConfig('pickpack_skunudge_separated', $namenudgeYN_default, false, 'picks2');
        
        $nameyn            = $this->_getConfig('pickpack_name_yn_separated', $namenudgeYN_default, false, 'picks2');
        $namenudge         = intval($this->_getConfig('pickpack_namenudge_separated', 0, false, 'picks2'));
        $config_group      = 'picks2';
        $product_images_yn = $this->_getConfig('product_images_yn', 0, false, $config_group);
        
        if ($product_images_yn == 1) {
            $product_images_source = $this->_getConfig('product_images_source', 'thumbnail', false, $config_group);
            $product_images_Xpos   = $this->_getConfig('pricesN_images_priceX', 'thumbnail', false, $config_group);
            
            $col_title_product_images         = explode(',', trim($this->_getConfig('col_title_product_images', ',150', false, $config_group)));
            $product_images_border_color_temp = strtoupper(trim($this->_getConfig('product_images_border_color', '#CCCCCC', false, $config_group)));
            $product_images_border_color      = new Zend_Pdf_Color_Html($product_images_border_color_temp);
            $product_images_maxdimensions     = explode(',', str_ireplace('null', '', $this->_getConfig('product_images_maxdimensions', '50,', $config_group)));
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
        /*get config price/shipping price*/
        $product_price_yn = $this->_getConfig('pickpack_price_yn_separated', 1, false, 'picks2');
        if ($product_price_yn == 1) {
            $nudge_price   = $this->_getConfig('pickpack_pricenudge_separated', 150, false, 'picks2');
            $incltax_price = $this->_getConfig('pickpack_price_tax', 1, false, 'picks2');
        }

        $product_total_column_yn = $this->_getConfig('product_total_column_yn', 1, false, 'picks2');
        if ($product_total_column_yn == 1) {
            $total_column_Xpos   = $this->_getConfig('total_column_Xpos', 500, false, 'picks2');
        }

        
        $media_path = Mage::getBaseDir('media');
        
        $columns_xpos_array = array();
        
        if ($nameyn == 1) {
            $columns_xpos_array['Name'] = $namenudge;
        }
        
        if ($product_images_yn == 1)
            $columns_xpos_array['Image'] = $col_title_product_images[1];
        
        if ($skuyn == 1)
            $columns_xpos_array['Sku'] = $skuX;
        
        if ($stockcheck_yn == 1)
            $columns_xpos_array['Stock'] = $stockcheck;
        
        if ($product_price_yn == 1) {
            $columns_xpos_array['Price'] = $nudge_price;
        }

        if ($product_total_column_yn == 1) {
            $columns_xpos_array['Total'] = $total_column_Xpos;
        }
        
		asort($columns_xpos_array);
		
        $override_address_format_yn = 1;
        $address_format_default     = '{if company}{company},|{/if company}
{if name}{name},|{/if name}
{if street}{street},|{/if street}
{if city}{city},{/if city} {if region}{region}|{/if region} 
{if postcode}{postcode}|{/if postcode}
{if country}{country}|{/if country}';
        $address_format             = $this->_getConfig('address_format', $address_format_default, false, 'general'); //col/sku
        $address_countryskip        = $this->_getConfig('address_countryskip', 0, false, 'general');
        
        $shmethod      = Mage::getStoreConfig('pickpack_options/picks2/pickpack_shipmethod');
        $warehouseyn   = Mage::getStoreConfig('pickpack_options/picks2/pickpack_warehouse');
        $giftwrapyn    = Mage::getStoreConfig('pickpack_options/picks2/pickpack_giftwrap');
        if ($giftwrapyn == 1) {
            $giftWrap_info = array();
            $gift_message_array = array();
        }
        $sku_warehouse = array();
        $sku_giftwrap  = array();
        
        
        $options_yn_base = $this->_getConfig('separated_options_yn_base', 0, false, 'picks2'); // no, inline, newline
        
        $pickpack_options_filter                 = '';
        $pickpack_options_count_filter_attribute = 'separated_options_count_filter';
        $pickpack_options_filter_yn              = 0;
        
        if ($options_yn_base == 0) {
            $options_yn = 0;
        } elseif ($options_yn_base == 'yesstacked') {
            $options_yn                              = $this->_getConfig('separated_options_yn_stacked', 0, false, 'picks2'); // no, inline, newline
            $pickpack_options_filter_yn              = $this->_getConfig('separated_options_filter_yn_stacked', 0, false, 'picks2');
            $pickpack_options_filter                 = trim($this->_getConfig('separated_options_filter_stacked', '', false, 'picks2'));
            $pickpack_options_count_filter_attribute = 'separated_options_count_filter_stacked';
        } else {
            $options_yn                 = $this->_getConfig('separated_options_yn', 0, false, 'picks2'); // no, inline, newline
            $pickpack_options_filter_yn = $this->_getConfig('separated_options_filter_yn', 0, false, 'picks2');
            $pickpack_options_filter    = trim($this->_getConfig('separated_options_filter', '', false, 'picks2'));
        }
        
        
        $pickpack_options_filter_array       = array();
        $pickpack_options_count_filter_array = array();
        
        if ($pickpack_options_filter_yn == 0)
            $pickpack_options_filter = '';
        elseif ($pickpack_options_filter == '' && $pickpack_options_filter_yn == 1)
            $pickpack_options_filter_yn = 0;
        elseif ($pickpack_options_filter_yn == 1) {
            $pickpack_options_filter_array = explode(',', $pickpack_options_filter);
            foreach ($pickpack_options_filter_array as $key => $value) {
                $pickpack_options_filter_array[$key] = trim($value);
            }
            $pickpack_options_count_filter = $this->_getConfig($pickpack_options_count_filter_attribute, 0, false, 'picks2');
            
            if (trim($pickpack_options_count_filter) != '') {
                $pickpack_options_count_filter_array = explode(',', $pickpack_options_count_filter);
                foreach ($pickpack_options_count_filter_array as $key => $value) {
                    $pickpack_options_count_filter_array[$key] = trim($value);
                }
            }
        }
        
        $sort_packing_yn   = $this->_getConfig('sort_packing_yn', 1, false, 'general');
        $sort_packing      = $this->_getConfig('sort_packing', 'sku', false, 'general');
        $sortorder_packing = $this->_getConfig('sort_packing_order', 'ascending', false, 'general');
        
        if ($sort_packing == 'attribute') {
            $sort_packing_attribute = trim($this->_getConfig('sort_packing_attribute', '', false, 'general'));
            if ($sort_packing_attribute != '')
                $sort_packing = $sort_packing_attribute;
        }
        if ($sort_packing_yn == 0)
            $sortorder_packing = 'none';
        
        $skuXInc = 0;
        $storeId          = Mage::app()->getStore()->getId();
		
        /*get config price/shipping price*/
        $product_price_yn = $this->_getConfig('pickpack_price_yn_separated', 1, false, 'picks2');
        if ($product_price_yn == 1) {
            $nudge_price   = $this->_getConfig('pickpack_pricenudge_separated', 150, false, 'picks2');
            $incltax_price = $this->_getConfig('pickpack_price_tax', 1, false, 'picks2');
        }
        $shipping_price_yn = $this->_getConfig('pickpack_priceshipping_yn_separated', 1, false, 'picks2');
        if ($shipping_price_yn == 1) {
            $nudge_shipping_price   = $this->_getConfig('pickpack_priceshipping_nudge_separated', 200, false, 'picks2');
            $incltax_shipping_price = $this->_getConfig('pickpack_priceshipping_incltax', 1, false, 'picks2');
        }
        $payment_method_yn   = $this->_getConfig('pickpack_paymentmethod', 1, false, 'picks2');
        $show_sheet_total_yn = $this->_getConfig('show_sheet_total', 1, false, 'picks2');
        if ($show_sheet_total_yn == 1) {
            $show_tax_withouttax = $this->_getConfig('show_sheet_total_tax', 1, false, 'picks2');
            $show_sheet_grand_total_tax = $this->_getConfig('show_sheet_grand_total_tax', 1, false, 'picks2');
            $show_total_order    = $this->_getConfig('show_total_order', 1, false, 'picks2');
        }
        $packed_by_yn = $this->_getConfig('packed_by_yn', 0, false, 'picks2');
        if ($packed_by_yn == 1) {
            $packed_by_text = trim($this->_getConfig('packed_by_text', '', false, 'picks2'));
            $packedByXY     = explode(",", $this->_getConfig('packed_by_nudge', '520,20', true, 'picks2'));
        }
        
        $packed2_by_yn = $this->_getConfig('packed2_by_yn', 0, false, 'picks2');
        if ($packed2_by_yn == 1) {
            $packed2_by_text = trim($this->_getConfig('packed2_by_text', '', false, 'picks2'));
            $packed2ByXY     = explode(",", $this->_getConfig('packed2_by_nudge', '120,20', true, 'picks2'));
        }
        $order_number_yn = $this->_getConfig('pickpack_order_number_yn', 1, false, 'picks2');
		$order_date_yn = 0;
		$invoiced_date_yn = 0;
        if ($order_number_yn == 1) {
            $order_date_yn    = $this->_getConfig('pickpack_order_date_yn', 1, false, 'picks2');
            $invoiced_date_yn = $this->_getConfig('pickpack_invoiced_date_yn', 1, false, 'picks2');
        }
		
        /*get config show product/ship/order*/
        $show_product_total       = $this->_getConfig('show_product_total', 1, false, 'picks2');
        $show_product_tax_total   = $this->_getConfig('show_product_tax_total', 1, false, 'picks2');
        $show_product_grand_total = $this->_getConfig('show_product_grand_total', 1, false, 'picks2');
        
        $show_ship_total       = $this->_getConfig('show_ship_total', 1, false, 'picks2');
        $show_ship_tax_total   = $this->_getConfig('show_ship_tax_total', 1, false, 'picks2');
        $show_ship_grand_total = $this->_getConfig('show_ship_grand_total', 1, false, 'picks2');
        
        $show_order_total                 = $this->_getConfig('show_order_total', 1, false, 'picks2');
        $show_order_tax_total             = $this->_getConfig('show_order_tax_total', 1, false, 'picks2');
        $show_order_grand_total           = $this->_getConfig('show_order_grand_total', 1, false, 'picks2');
        $store_view                       = $this->_getConfig('name_store_view', 'storeview', false, "picks2");
        $specific_store_id = $this->_getConfig('specific_store', '', false, "picks2");
        $order_id_master                  = array();
        $sku_order_suppliers              = array();
        $sku_shelving                     = array();
        $sku_shipping_address             = array();
        $sku_order_id_options             = array();
        $sku_bundle                       = array();
        $product_build_item               = array();
        $product_build                    = array();
        $total_price_with_tax             = 0;
        $total_grand_with_tax             = 0;
        $total_price_shipping_with_tax    = 0;
        $total_price_without_tax          = 0;
        $total_grand_without_tax          = 0;
        $total_price_shipping_without_tax = 0;
        $total_price_column = 0;
		
        foreach ($orders as $orderSingle) {
            $order      = $helper->getOrder($orderSingle);
            $putOrderId = $order->getRealOrderId();
            $order_id   = $putOrderId;
            $store_id = Mage::app()->getStore()->getId();
			
            $has_shipping_address = false;
            $has_billing_address  = false;
            // test for addresses
            foreach ($order->getAddressesCollection() as $address) {
                if ($address->getAddressType() == 'shipping' && !$address->isDeleted())
                    $has_shipping_address = true;
                elseif ($address->getAddressType() == 'billing' && !$address->isDeleted())
                    $has_billing_address = true;
            }
            
            /*get payment method*/
            if ($payment_method_yn == 1) {
                $payment_order = $this->getPaymentOrder($order);
                if ($payment_order) {
                    Mage::unregister('current_order');
                    Mage::register('current_order', $order);
                    $paymentInfo = Mage::helper('payment')->getInfoBlock($payment_order)
                        ->setIsSecureMode(true)
                        ->toPdf();
                } else {
                    $paymentInfo = '';
                }
                $payment_print                  = clean_method($paymentInfo, 'payment-full');
                $payment_print                  = str_replace('Money order', 'MO', $payment_print);
                $payment_method[$order_id]      = "Paid: " . $payment_print;
                $payment_method_x               = 455;
                $max_payment_description_length = 40; //37
                if (strlen($payment_method[$order_id]) > $max_payment_description_length) {
                    $payment_method[$order_id] = trim(substr(htmlspecialchars_decode($payment_method[$order_id]), 0, ($max_payment_description_length)) . '…');
                }
            }
            if ($show_sheet_total_yn == 1) {
                if ($show_tax_withouttax == 1) {
                    $total_price_with_tax += $order->getBaseSubtotalInclTax();
                    $total_price_shipping_with_tax += $order->getBaseShippingInclTax();
                } else {
                    $total_price_without_tax += $order->getBaseSubtotal();
                    $total_price_shipping_without_tax += $order->getBaseShippingAmount();
                }
				if($show_sheet_grand_total_tax == 1){
					$total_grand_with_tax += round($order->getBaseGrandTotal(), 2);
				}
				else{
					$total_grand_without_tax += round($order->getBaseGrandTotal(),2) - round($order->getBaseTaxAmount(), 2);
					
				}
            }
			$sku_shipping_address_temp       = '';
            $sku_shipping_address[$order_id] = '';
            $shippingAddressFlat             = '';
            $shippingAddressArray            = array();
			$address_format_set = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $address_format);
            if ($has_shipping_address === true) {
				$shipping_address = $this->getShippingAddressOrder($order);
                if (trim($address_countryskip) != ''){
                    $shipping_address['country'] = str_ireplace($address_countryskip, '', $shipping_address['country']);
					/*TODO filter city if country = singapore or monaco*/
					if(!is_array($address_countryskip) && (strtolower(trim($address_countryskip)) == "singapore" || strtolower(trim($address_countryskip)) =="monaco")){
						$shipping_address['city'] = str_ireplace($address_countryskip, '', $shipping_address['city']);
					}
				}
                foreach ($shipping_address as $key => $value) {
                    $address_format_set = $this->getAddressFormatByValue($key, $value, $address_format_set);
                }

                $address_format_set = str_replace(array('||', '|'), "\n", trim($address_format_set));
                $address_format_set = str_replace(array('{if city}', '{if postcode}', '{if region}', '{if firstname}', '{firstname}', '{/if firstname}', '{if lastname}', '{lastname}', '{/if lastname}'), '', $address_format_set);
                
                $shippingAddressArray = explode("\n", $address_format_set);
                
                $sku_shipping_address[$order_id] = $this->addressPrintLine($shippingAddressArray, $black_color, $page, $sku_shipping_address_temp);
                $sku_shipping_address[$order_id] = Mage::helper('pickpack/functions')->clean_method($sku_shipping_address[$order_id],'pdf');
            }
            //TODO billing address
			$sku_billing_address[$order_id] = '';
			$billingAddressArray = array();
			$sku_billing_address_temp = '';
			if ($has_billing_address === true) {
                $billing_address = $this->getBillingAddressOrder($order);
                if (trim($address_countryskip) != ''){
					$billing_address['country'] = str_ireplace($address_countryskip, '', $billing_address['country']);
					/*TODO filter city if country = singapore or monaco*/
					if(!is_array($address_countryskip) && (strtolower(trim($address_countryskip)) == "singapore" || strtolower(trim($address_countryskip)) =="monaco")){
						$billing_address['city'] = str_ireplace($address_countryskip, '', $billing_address['city']);
					}
				}
                foreach ($billing_address as $key => $value) {
                    $address_format_set = $this->getAddressFormatByValue($key, $value, $address_format_set);
                }

                $address_format_set = str_replace(array('||', '|'), "\n", trim($address_format_set));
                $address_format_set = str_replace(array('{if city}', '{if postcode}', '{if region}', '{if firstname}', '{firstname}', '{/if firstname}', '{if lastname}', '{lastname}', '{/if lastname}'), '', $address_format_set);

                $billingAddressArray = explode("\n", $address_format_set);

                $sku_billing_address[$order_id] = $this->addressPrintLine($billingAddressArray, $black_color, $page, $sku_billing_address_temp);
                $sku_billing_address[$order_id] = Mage::helper('pickpack/functions')->clean_method($sku_billing_address[$order_id],'pdf');
            }
            
            if (!isset($order_id_master[$order_id]))
                $order_id_master[$order_id] = 0;
            
            $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
            // $total_items     = count($itemsCollection);
            $product_build[$order_id]["temp_order"] = $order;
            $max_name_length  = 0;
            $test_name        = 'abcdefghij'; //10
            $font_temp        = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $test_name_length = round($this->parseString($test_name, $font_temp, ($generalConfig['font_size_body']))); //*0.77)); // bigger = left
            
            $pt_per_char     = ($test_name_length / 10);
            $max_name_length = (550 - $skuX);
            if (!isset($productXInc))
                $productXInc = 0;
            $max_sku_length            = (($productX + $productXInc) - 27 - $skuX);
            $character_breakpoint_name = round($max_name_length / $pt_per_char);
            $character_breakpoint_sku  = round($max_sku_length / $pt_per_char);
            // $product_build_item = array();
            //     $product_build = array();
            $sku_category              = array();
            
            $coun                                      = 1;
            $product_total[$order_id]["product_grand"] = 0;
            $product_total[$order_id]["product_total"] = 0;
            $product_total[$order_id]["ship_grand"]    = 0;
            $product_total[$order_id]["ship_total"]    = 0;
            $product_total[$order_id]["order_grand"]   = 0;
            $product_total[$order_id]["order_total"]   = 0;
            
            $product_total[$order_id]["product_grand"] = $order->getSubtotalInclTax();
            $product_total[$order_id]["product_total"] = $order->getSubtotal();
            
            $product_total[$order_id]["ship_grand"] = $order->getShippingInclTax();
            $product_total[$order_id]["ship_total"] = $order->getShippingAmount();
            
            $product_total[$order_id]["order_grand"] = $order->getData('grand_total');
            $product_total[$order_id]["order_total"] = $order->getSubtotal() + $order->getShippingAmount();
            
            $order_date[$order_id] = $order->getCreatedAtDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            if ($order->hasInvoices()) {
                $inv_first = null;
                foreach ($order->getInvoiceCollection() as $inv) {
                    $inv_first = $inv;
                    break;
                }
                $invoice_date[$order_id] = $inv_first->getCreatedAtDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            }

            foreach ($itemsCollection as $item) {
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    // any products actually go thru here?
                    $sku        = $item->getProductOptionByCode('simple_sku');
                    $product_id = Mage::getModel('catalog/product')->setStoreId($storeId)->getIdBySku($sku);
                } else {
                    $sku        = $item->getSku();
                    $product_id = $item->getProductId(); // get it's ID
                }

                // if show options as counted groups
                if ($options_yn == 1) {
                    $full_sku                              = trim($item->getSku());
                    $parent_sku                            = $full_sku; //preg_replace('~\-(.*)$~','',$full_sku);
                    $sku                                   = $parent_sku;
                    $product_build_item[]                  = $sku;
                    $product_sku                           = $sku;
                    $product_build[$order_id][$sku]['sku'] = $product_sku;
                    
                } else {
                    // unique item id
                    $product_build_item[]                  = $sku . '-' . $coun;
                    $product_sku                           = $sku;
                    $sku                                   = $sku . '-' . $coun;
                    $product_build[$order_id][$sku]['sku'] = $product_sku;
                }
                $product = Mage::getModel('catalog/product')->setStoreId($storeId)->loadByAttribute('sku', $sku, array(
                    'cost',
                    $shelving_attribute,
                    'name',
                    'simple_sku',
                    'qty'
                ));
                if ($stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty())
                    $stock = round($stock); //Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
                
                $options = $item->getProductOptions();
                
                if (isset($options['bundle_options']) && is_array($options['bundle_options'])) {
                    $children = $item->getChildrenItems();
                    if (count($children)) {
                        $price_bundle_item = 0;
                        foreach ($children as $child) {
                            $product_child = $helper->getProductForStore($child->getProductId(), $storeId);
                            $sku_b         = $child->getSku();
                            $price_b       = $child->getPriceInclTax();
                            $price_bundle_item += $price_b;
                            $qty_b = (int) $child->getQtyOrdered();
                            if ($store_view == "storeview")
                                $name_b = $child->getName();
                            elseif($store_view == "specificstore" && $specific_store_id != ""){
                                $_product = $helper->getProductForStore($child->getProductId(), $specific_store_id);
                                if ($_product->getData('name')) $name_b = trim($_product->getData('name'));
                                if ($name_b == '') $name_b = trim($child->getName());
                            }
                            else
                                $name_b = $this->getNameDefaultStore($child);
                            $childProductId = $child->getProductId();
                            $this->y -= 10;
                            $offset = 20;
                            
                            $shelving_real   = '';
                            $shelving_real_b = '';
                            
                            if (isset($shelving_yn) && $shelving_yn == 1) {
                                if ($shelving_real_b = $product_child->getData($shelving_attribute)) {
                                    $shelving_real_b = Mage::helper('pickpack')->getProductAttributeValue($product_child, $shelving_attribute);
                                } elseif ($shelving_real_b = $helper->getProductForStore($child->getProductId(), $storeId)->getAttributeText($shelving_attribute)) {
                                    // $shelving_real_b = Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId())->getAttributeText($shelving_attribute);
                                } elseif ($product_child[$shelving_attribute])
                                    $shelving_real_b = $product_child[$shelving_attribute];
                                else
                                    $shelving_real_b = '';
                                
                                if (is_array($shelving_real))
                                    $shelving_real_b = implode(',', $shelving_real_b);
                                if (isset($shelving_real_b))
                                    $shelving_real_b = trim($shelving_real_b);
                            }
                            
                            if ($from_shipment == 'shipment') {
                                $qty_string_b = 's:' . (int) $item->getQtyShipped() . ' / o:' . $item->getQtyOrdered();
                                $price_qty_b  = (int) $item->getQtyShipped();
                                $productXInc  = 25;
                            } else {
                                $qty_string_b = $qty_b;
                                $price_qty_b  = $qty_b;
                                $productXInc  = 0;
                            }
                            $display_name_b = '';
                            if (strlen($name_b) > ($character_breakpoint_name + 2))
                                $display_name_b = substr(htmlspecialchars_decode($name_b), 0, ($character_breakpoint_name)) . '…';
                            else
                                $display_name_b = htmlspecialchars_decode($name_b);
                            
                            $sku_bundle[$order_id][$sku][] = $sku_b . '##' . $display_name_b . '##' . $shelving_real_b . '##' . $qty_string_b . '##' . $childProductId;
                        }
                    }
                } else {
                    $sku_bundle[$order_id][$sku] = '';
                }
                
                
                $category_label = '';
                $product        = $helper->getProduct($product_id);
                $catCollection = $product->getCategoryCollection();

                $categsToLinks = array();
                # Get categories names
                foreach ($catCollection as $cat) {
                    if ($cat->getName() != '') {
                        $categsToLinks[] = $cat->getName();
                    }
                }
                $category_label = implode(', ', $categsToLinks);

                $sku_category[$sku] = $category_label;
                unset($category_label);
                
                
                $shelving = '';
                $supplier = '';
                $extra    = '';
                
                /**
                images PRELOADER start
                */
                
                $has_shown_product_image = 0;
                if (($product_images_yn == 1) && !isset($sku_image_paths[$sku]['path'][0])) // ie only get sku image paths if not previously got in this combined request
                    {
                    $image_product = Mage::helper('catalog/product')->getProduct($product_id, null, null);
                    
                    $options_y_counter  = 0;
                    $img_width          = 0;
                    $img_height         = 0;
                    $resize_x           = null;
                    $resize_y           = null;
                    $has_real_image_set = null;
                    $parent_ids         = array();
                    $imagePaths         = array();     
                    $image_path = '';
                    $image_path = $image_product->getImage();
                    $filePath   = Mage::getBaseDir() . '/media/catalog/product' . $image_path;
					
                    if (file_exists($filePath)) {
                        $image_parent_sku   = $product->getSku();
                        $has_real_image_set = (($image_path != null) && ($image_path != "no_selection") && ($image_path != ''));
                        
                        if ($image_path != '') {
                            $sku_image_paths[$sku]['width']  = $product_images_maxdimensions[0];
                            $sku_image_paths[$sku]['height'] = $product_images_maxdimensions[1];
                            $imagePaths = $helper->getImagePaths($image_product, $product_images_source_res, $product_images_maxdimensions);

                            foreach ($imagePaths as $imagePath) {
                                $image_url = $imagePath;
                                $image_url_after_media_path_with_media = strstr($image_url, '/media/');                 
                                $image_url_after_media_path = strstr_after($image_url, '/media/');                     
                                $final_image_path = $media_path . '/' . $image_url_after_media_path;
                                $sku_image_paths[$sku]['path'][] = $final_image_path;
                            }
                        }
                    }
                }
                /**
                images PRELOADER end
                */
				
                /*get price each item*/
                if ($product_price_yn == 1) {
                    if ($incltax_price == 1)
                        $price_item = $item->getPriceInclTax();
					else
                        $price_item = $item->getPrice();
                    $product_build[$order_id][$sku]['price'] = $price_item;
                }
                
                if ($shelving_yn == 1) {
                    if ($_newProduct = $helper->getProductForStore($product_id, $storeId))
                        $shelving    = Mage::helper('pickpack')->getProductAttributeValue($_newProduct, $shelving_attribute);
                    elseif ($product->getData($shelving_attribute))
                        $shelving = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute);
                    else
                        $shelving = '';
                    
                    if (is_array($shelving))
                        $shelving = implode(',', $shelving);
                    if (trim($shelving) != '') {
                        if (isset($sku_shelving[$sku]) && trim(strtoupper($sku_shelving[$sku])) != trim(strtoupper($shelving)))
                            $sku_shelving[$sku] .= ',' . trim($shelving);
                        else
                            $sku_shelving[$sku] = trim($shelving);
                        $sku_shelving[$sku] = preg_replace('~,$~', '', $sku_shelving[$sku]);
                    } else
                        $sku_shelving[$sku] = '';
                }
                if ($sort_packing != 'none' && $sort_packing != '') {
					if($sort_packing != "sku")
						$product_build[$order_id][$sku][$sort_packing] = '';
                    
                    $attributeName = $sort_packing;
                    if ($attributeName == 'Mcategory') {
                        $product_build[$order_id][$sku][$sort_packing] = $sku_category[$sku]; 
                    } else {
                        $product = $helper->getProduct($product_id);
                        
                        if ($product->getData($attributeName))
                            $product_build[$order_id][$sku][$sort_packing] = Mage::helper('pickpack')->getProductAttributeValue($product, $attributeName, false);
                        
                    }
                    unset($attributeName);
                    unset($attribute);
                    unset($attributeOptions);
                    unset($result);
                }
                
                if ($split_supplier_yn != 'no') {
                	//TODO 1
                	$is_warehouse_supplier = 0;
					if( (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) 
						&& ($supplier_attribute == 'warehouse') )
							$is_warehouse_supplier = 1;

					if($is_warehouse_supplier == 1) {
						$warehouse = $item->getWarehouse();
						$warehouse_code = $warehouse->getData('code');
						$supplier = $warehouse_code;
						$warehouse_code = trim(strtoupper($supplier));
						$this->warehouse_title[$warehouse_code] = $item->getWarehouseTitle();
					}
					else {
	                    $_newProduct = $helper->getProductForStore($product_id, $storeId);
                    
						if ($_newProduct && $_newProduct->getData($supplier_attribute)) 
								$supplier = $_newProduct->getData('' . $supplier_attribute . '');
	                    elseif ($product->getData('' . $supplier_attribute . ''))
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
					//TODO 2
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
                    $sku_shipping_method[$order_id] = clean_method($order->getShippingDescription(), 'shipping');
                    
                    if ($sku_shipping_method[$order_id] != '')
                        $sku_shipping_method[$order_id] = 'Ship: ' . $sku_shipping_method[$order_id];
                    
                    $shipping_method_x               = 455;
                    //if ($barcodes == 1) $shipping_method_x = 270; //$barcodeWidth = 250;
                    $max_shipping_description_length = 41; //37
                    if (strlen($sku_shipping_method[$order_id]) > $max_shipping_description_length)
                        $sku_shipping_method[$order_id] = trim(substr(htmlspecialchars_decode($sku_shipping_method[$order_id]), 0, ($max_shipping_description_length)) . '…');
					$font_temp         = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $font_size_compare = ($generalConfig['font_size_subtitles'] - 2);
                    $line_width        = $this->parseString($sku_shipping_method[$order_id], $font_temp, $font_size_compare); // bigger = left   
                    
                    if ($shipping_price_yn == 1) {
                        if ($incltax_shipping_price == 1)
                            $price_item = $order->getShippingInclTax();
                        else
                            $price_item = $order->getShippingAmount();
                        $price_shipping[$order_id]['price_shipping'] = $price_item;
                    }
                }
                
                if ($warehouseyn == 1) {
                    /***** Get Warehouse information ****/
                    if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) {
                        $warehouse_helper     = Mage::helper('warehouse');
                        $warehouse_collection = Mage::getSingleton('warehouse/warehouse')->getCollection();
                        $resource             = Mage::getSingleton('core/resource');
                        /**
                         * Retrieve the read connection
                         */
                        $readConnection       = $resource->getConnection('core_read');
                        $query                = 'SELECT stock_id FROM ' . $resource->getTableName("warehouse/order_grid_warehouse") . ' WHERE entity_id=' . $order->getData('entity_id');
                        $warehouse_stock_id   = $readConnection->fetchOne($query);
                        if ($warehouse_stock_id) {
                            $warehouse       = $warehouse_helper->getWarehouseByStockId($warehouse_stock_id);
                            $warehouse_title = ($warehouse->getData('title'));
                        } else
                            $warehouse_title = '';
                    } else
                        $warehouse_title = '';
                    $sku_warehouse[$order_id] = $warehouse_title;
                    $warehouse_x              = 420;
                    if ($barcodes == 1)
                        $warehouse_x = 490; //$barcodeWidth = 250;
                    
                }
                
                if ($giftwrapyn == 1) {
                    if (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') || Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
                        if (Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
                            $quoteId            = $order->getQuoteId();
                            $selections         = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
                            $giftwrapCollection = array();
                            if ($quoteId) {
                                $giftwrapCollection = Mage::getModel('giftwrap/selection')->getSelectionByQuoteId($quoteId);
                                foreach ($giftwrapCollection as $info_collection) {
                                    $giftWrap_info['message'] .= "\n" . $info_collection['giftwrap_message'];
                                    $style_gift = Mage::getModel('giftwrap/giftwrap')->load($info_collection['styleId']);
                                    $giftWrap_info['wrapping_paper'] .= $style_gift->getData('title');
                                    $giftWrap_info['style'] .= $style_gift->getData('title');
                                }
                            }
                            $giftWrapInfos = Mage::getModel('giftwrap/giftwrap')->getCollection()->addFieldToFilter('store_id', '0');
                            
                            foreach ($giftWrapInfos as $info) {
                                $giftWrap_info['wrapping_paper'] .= str_ireplace(array(
                                    '.jpg',
                                    '.jpeg',
                                    '.gif',
                                    '.png'
                                ), '', $info->getData('image'));
                            }
                        } elseif (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') && (Mage::getModel('giftwrap/order'))) {
                            $orderId       = $order->getId();
                            $giftWrapInfos = Mage::getModel('giftwrap/order')->getCollection()->addFieldToFilter('order_id', $orderId); //
                            foreach ($giftWrapInfos as $info) {
                                $giftWrap_info['message'] .= $info->getData('message');
                                if (isset($giftWrap_info['wrapping_paper']))
                                    $giftWrap_info['wrapping_paper'] .= ' | ';
                                $giftWrap_info['wrapping_paper'] .= trim(str_ireplace(array(
                                    'xmage_giftwrap/',
                                    '.jpg',
                                    '.jpeg',
                                    '.gif',
                                    '.png'
                                ), '', $info->getData('giftbox_image')));
                            }
                        }
                        unset($giftWrapInfos);
                        if (!(isset($giftWrap_info['wrapping_paper'])))
                            $giftWrap_info['wrapping_paper'] = '';
                        
                        $sku_giftwrap[$order_id] = $giftWrap_info; //['wrapping_paper'];
                        unset($giftWrap_info);
                    }
                    
                    $giftwrap_x = 320;
                    if ($barcodes == 1)
                        $giftwrap_x = 470;
                    
                }
                /********************************************************/
                // qty in this order of this sku
                $qty  = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int) $item->getQtyOrdered();
                $sqty = $item->getIsQtyDecimal() ? $item->getQtyShipped() : (int) $item->getQtyShipped();
                $iqty = $item->getIsQtyDecimal() ? $item->getData('qty_invoiced') : (int) $item->getData('qty_invoiced');
                // total qty in all orders for this sku
                if (isset($sku_qty[$sku]))
                    $sku_qty[$sku] = ($sku_qty[$sku] + $qty);
                else
                    $sku_qty[$sku] = $qty;
                $total_quantity = $total_quantity + $qty;
                
                $cost       = $qty * (is_object($product) ? $product->getCost() : 0);
                $total_cost = $total_cost + $cost;
                
                $sku_master[$sku] = $sku;
                $store_view       = $this->_getConfig('name_store_view', 'storeview', false, "picks2");
                if ($configurable_names == 'simple' && $_newProduct = $helper->getProductForStore($product_id, $storeId)) {
                    if ($store_view == "storeview") {
                        if ($_newProduct->getData('name'))
                            $sku_name[$sku] = $_newProduct->getData('name');
						else
							$sku_name[$sku] = $item->getName();
                    }elseif($store_view == "specificstore" && $specific_store_id != "") {
                        $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
                        if ($_newProduct->getData('name')) $sku_name[$sku] = trim($_newProduct->getData('name'));
                        if ($sku_name[$sku] == '') $sku_name[$sku] = trim($item->getName());
                                    
                    }  
                    else
                        $sku_name[$sku] = $this->getNameDefaultStore($item);
                } else {
                    if ($store_view == "storeview")
                        $sku_name[$sku] = $item->getName();
                    elseif($store_view == "specificstore" && $specific_store_id != "") {
                        $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
                        if ($_newProduct->getData('name')) 
							$sku_name[$sku] = trim($_newProduct->getData('name'));
                        if ($sku_name[$sku] == '') 
							$sku_name[$sku] = trim($item->getName());
                    } 
                    else
                        $sku_name[$sku] = $this->getNameDefaultStore($item);
                }

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
                        if ($i > 0) 
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
                        if (isset($options['options'][$i]))
							$continue = 1;
                    }
                }
                unset($options_name_temp);
                
                $sku_stock[$sku] = $stock;
                if (isset($sku_order_id_qty[$order_id][$sku]) && isset($sku_order_id_sqty[$order_id][$sku]) && $sku_order_id_iqty[$order_id][$sku]) {
                    $sku_order_id_qty[$order_id][$sku]  = ($sku_order_id_qty[$order_id][$sku] + $qty);
                    $sku_order_id_sqty[$order_id][$sku] = ($sku_order_id_sqty[$order_id][$sku] + $sqty);
                    $sku_order_id_iqty[$order_id][$sku] = ($sku_order_id_iqty[$order_id][$sku] + $iqty);
                    $sku_order_id_sku[$order_id][$sku]  = $product_sku;
                    //New TODO 
                    $product_build[$order_id][$sku]['qty']  = $sku_order_id_qty[$order_id][$sku];
                    $product_build[$order_id][$sku]['sqty'] = $sku_order_id_sqty[$order_id][$sku];
                    $product_build[$order_id][$sku]['id']   = $product_id;
                } else {
                    $sku_order_id_qty[$order_id][$sku]  = $qty;
                    $sku_order_id_sqty[$order_id][$sku] = $sqty;
                    $sku_order_id_iqty[$order_id][$sku] = $iqty;
                    $sku_order_id_sku[$order_id][$sku]  = $product_sku;
                    
                    $product_build[$order_id][$sku]['qty']  = $qty;
                    $product_build[$order_id][$sku]['sqty'] = $sqty;
                    $product_build[$order_id][$sku]['id']   = $product_id;
                }
                
                if (!isset($max_qty_length))
                    $max_qty_length = 2;
                if (strlen($sku_order_id_qty[$order_id][$sku]) > $max_qty_length)
                    $max_qty_length = strlen($sku_order_id_qty[$order_id][$sku]);
                if (strlen($sku_order_id_sqty[$order_id][$sku]) > $max_qty_length)
                    $max_qty_length = strlen($sku_order_id_sqty[$order_id][$sku]);
                
                if ($split_supplier_yn != 'no') {
                    // if(!in_array($supplier,$sku_order_suppliers[$order_id])) $sku_order_suppliers[$order_id][] = $supplier;
                    if (!isset($sku_order_suppliers[$order_id]))
                        $sku_order_suppliers[$order_id][] = $supplier;
                    elseif (!in_array($supplier, $sku_order_suppliers[$order_id]))
                        $sku_order_suppliers[$order_id][] = $supplier;
                }
                $coun++;
                
            }
            
            //var_dump($product_total[$order_id]["product_grand"], $product_total[$order_id]["product_total"]);die("test");
            if (isset($sku_bundle[$order_id]) && is_array($sku_bundle[$order_id]))
                ksort($sku_bundle[$order_id]);
        }
        
        $processed_skus = array();
        
        ksort($order_id_master);
        ksort($sku_order_id_qty);
        ksort($sku_master);
        //ksort($product_build);
        $order_count = count($product_build);
        if (isset($supplier_master))
            ksort($supplier_master);
        $supplier_previous    = '';
        $supplier_item_action = '';
        $first_page_yn        = 'y';
        
		// ADDING LOGO ******************************************************************************
        if ($picklogo == 1) {
            $sub_folder = 'logo_pack';              
            $option_group = 'wonder';               
            $suffix_group = '/pack_logo';  
			
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
               $page_top_spacer = 10;
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
            
			if ($from_shipment == 'shipment')
				$page->drawText($helper->__('Shipment-separated Orders Summary'), 325, ($page_top - $page_top_spacer - (41/2) + 10), 'UTF-8');
			else
                $page->drawText($helper->__('Orders Summary'), 325, ($page_top - $page_top_spacer - (41/2) + 10), 'UTF-8');

            // $page->drawText($helper->__('Supplier : '.$supplier), 325, 790, 'UTF-8');
            $page->setFillColor($background_color_subtitles_zend);
            $page->setLineColor($background_color_subtitles_zend);
            $page->setLineWidth(0.5);
            if($generalConfig['line_width_company'] > 0)
                $page->drawRectangle(304, $y1, (304 + $generalConfig['line_width_company']), ($page_top + 5));
            
            $this->y = ($page_top - 43); //777;
        } else {
            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] + 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

            if ($from_shipment == 'shipment')
                $page->drawText($helper->__('Shipment-separated Orders Summary'), 31, ($page_top - 5), 'UTF-8');
            else
                $page->drawText($helper->__('Orders Summary'), 31, ($page_top - 5), 'UTF-8');

            $page->setLineColor($background_color_subtitles_zend);
            $page->setFillColor($background_color_subtitles_zend);
            $page->drawRectangle(27, ($page_top - 12), $padded_right, ($page_top - 11));

            $this->y = 800;
        }
		
		$this->y -= 10;
        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);        
        
        // roll_orderID
        $page_count = 1;
        // foreach($order_id_master as $order_id =>$value)
        //             {
        foreach ($product_build as $order_id => $order_build) {
            $order_temp = $order_build["temp_order"];
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
            
            $page->setFillColor($background_color_orderdetails_zend);
            $page->setLineColor($background_color_orderdetails_zend);
            $page->drawRectangle(27, $this->y, $padded_right, ($this->y - 28));
            $page->setFillColor($font_color_body_zend);
            $page->setLineColor($font_color_body_zend);
            $page->setLineWidth(0.5);
			$order_detail_line_Y = ($this->y - 28);
            if($barcodes == 0)
				$page->drawLine(27, $order_detail_line_Y, $padded_right, $order_detail_line_Y);
            // order #
            if ($order_number_yn == 1) {
                $this->_setFont($page, 'bold', ($generalConfig['font_size_forced_picklist']-1), $generalConfig['font_family_forced_picklist'], $generalConfig['non_standard_characters_forced_picklist'], $generalConfig['font_color_subtitles']);
                $page->drawText($helper->__('#') . $order_id, 29, ($this->y - 11), 'UTF-8');
                
                if ($order_date_yn == 1) {
                    $order_date_print = date($generalConfig['date_format'], strtotime($order_date[$order_id]));
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_forced_picklist'] - 6), $generalConfig['font_family_forced_picklist'], $generalConfig['non_standard_characters_forced_picklist'], $generalConfig['font_color_subtitles']);
                    $page->drawText($helper->__('Order date') . ': ', 29, ($this->y - 11 - ($generalConfig['font_size_forced_picklist'] - 6)), 'UTF-8');
                    $page->drawText($order_date_print, 29 + 40, ($this->y - 11 - ($generalConfig['font_size_forced_picklist'] - 6)), 'UTF-8');
                }
				
                if ($invoiced_date_yn == 1 && isset($invoice_date[$order_id])) {
                    $invoice_date_print = date($generalConfig['date_format'], strtotime($invoice_date[$order_id]));
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_forced_picklist'] - 6), $generalConfig['font_family_forced_picklist'], $generalConfig['non_standard_characters_forced_picklist'], $generalConfig['font_color_subtitles']);
                    $page->drawText($helper->__('Invoiced date') . ': ', 29, ($this->y - 11 - (2 * ($generalConfig['font_size_forced_picklist'] - 6)) ), 'UTF-8');
                    $page->drawText($invoice_date_print, 29 + 48, ($this->y - 11 - (2 * ($generalConfig['font_size_forced_picklist'] - 6)) ), 'UTF-8');
                }
            }
            $barcodes = Mage::getStoreConfig('pickpack_options/picks2/pickpack_pickbarcode');
            
            if ($tickbox != 0) {
                $page->setFillColor($white_color);
                $page->setLineColor($black_color);
                $page->drawRectangle(($padded_right - 2 - 16), ($this->y - 2), ($padded_right - 2), ($this->y - 18));
                $page->setFillColor($black_color);
            }
            
            if ($barcodes == 1) {
                $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($order_id, $generalConfig['barcode_type']);
                $barcodeWidth  = $this->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 21);
                $page->setFillColor($white_color);
                $page->setLineColor($white_color);
                $page->drawRectangle(108, ($this->y - 4), (110 + $barcodeWidth - 14), ($this->y - 24));
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 13);
                $page->drawText($barcodeString, 110, ($this->y - 22), 'CP1252');
            }
            /**draw product/ship/order **/
            
            $this->_setFont($page, 'regular', ($generalConfig['font_size_forced_picklist'] - 6.5), $generalConfig['font_family_forced_picklist'], $generalConfig['non_standard_characters_forced_picklist'], $generalConfig['font_color_subtitles']);
            $product_total_price = $product_total[$order_id]["product_total"];
            $product_grand       = $product_total[$order_id]["product_grand"];
            $product_tax_total   = $product_total[$order_id]["product_grand"] - $product_total[$order_id]["product_total"];
            
            $ship_total     = $product_total[$order_id]["ship_total"];
            $ship_tax_total = $product_total[$order_id]["ship_grand"] - $product_total[$order_id]["ship_total"];
            $ship_grand     = $product_total[$order_id]["ship_grand"];
            
            $order_total     = $product_total[$order_id]["order_total"];
            $order_tax_total = $product_total[$order_id]["order_grand"] - $product_total[$order_id]["order_total"];
            $order_grand     = $product_total[$order_id]["order_grand"];
            
			if ($barcodes == 0)
                $barcodeWidth = 50;
			
			$col_1_label_X = 100 + $barcodeWidth;
			$col_1_value_X = $col_1_label_X + 68;
			$col_2_label_X = 35 + $col_1_value_X;
			$col_2_value_X = $col_2_label_X + 60;
			$col_3_label_X = 35 + $col_2_value_X;
			$col_3_value_X = $col_3_label_X + 63;
			
            if ($show_product_total == 1) {
                $page->drawText($helper->__('Product Total(no Tax)') . ': ', $col_1_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3), 'UTF-8');
                //$product_total_Width = $this->parseString($helper->__('Product Total(no Tax)') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $product_total_price = $this->formatPriceTxt($order_temp,$product_total_price, 2, '.', ',');
                $page->drawText($product_total_price, $col_1_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3), 'UTF-8');
            }
            
			if ($show_product_tax_total == 1) {
                $page->drawText($helper->__('Product Tax Total') . ': ', $col_1_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles'] / 2), 'UTF-8');
                //$product_total_Width = $this->parseString($helper->__('Product Tax Total') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $product_tax_total = $this->formatPriceTxt($order_temp,$product_tax_total, 2, '.', ',');
                $page->drawText($product_tax_total, $col_1_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles'] / 2), 'UTF-8');
            }
            
			if ($show_product_grand_total == 1) {
                $page->drawText($helper->__('Product GT') . ': ', $col_1_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles']), 'UTF-8');
                //$product_total_Width = $this->parseString($helper->__('Product Grand Total') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $product_grand = $this->formatPriceTxt($order,$product_grand, 2, '.', ',');
                $page->drawText($product_grand, $col_1_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles']), 'UTF-8');
            }
            
			// 2nd Col ******************************************************************************
            if ($show_ship_total == 1) {
                $page->drawText($helper->__('Ship Total (no Tax)') . ': ', $col_2_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3), 'UTF-8');
                $ship_total = $this->formatPriceTxt($order_temp,$ship_total, 2, '.', ',');
                //$product_total_Width = $this->parseString($helper->__('Product Total(no Tax)') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $page->drawText($ship_total, $col_2_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3), 'UTF-8');
            }
            if ($show_ship_tax_total == 1) {
                $page->drawText($helper->__('Ship Tax Total') . ': ', $col_2_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles'] / 2), 'UTF-8');
                $ship_tax_total = $this->formatPriceTxt($order_temp,$ship_tax_total, 2, '.', ',');
                //$product_total_Width = $this->parseString($helper->__('Product Tax Total') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $page->drawText($ship_tax_total, $col_2_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles'] / 2), 'UTF-8');
            }
            if ($show_ship_grand_total == 1) {
                $page->drawText($helper->__('Ship GT') . ': ', $col_2_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles']), 'UTF-8');
                $ship_grand = $this->formatPriceTxt($order_temp,$ship_grand, 2, '.', ',');
                //$product_total_Width = $this->parseString($helper->__('Product Grand Total') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $page->drawText($ship_grand, $col_2_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles']), 'UTF-8');
            }
            
			// 3rd Col ******************************************************************************
            if ($show_order_total == 1) {
                $page->drawText($helper->__('Order Total (no Tax)') . ': ', $col_3_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3), 'UTF-8');
                $order_total = $this->formatPriceTxt($order, $order_total, 2, '.', ',');
                //$product_total_Width = $this->parseString($helper->__('Product Total(no Tax)') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $page->drawText($order_total, $col_3_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3), 'UTF-8');
            }
            if ($show_order_tax_total == 1) {
                $page->drawText($helper->__('Order Tax Total') . ': ', $col_3_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles'] / 2), 'UTF-8');
                $order_tax_total = $this->formatPriceTxt($order_temp,$order_tax_total, 2, '.', ',');
                //$product_total_Width = $this->parseString($helper->__('Product Tax Total') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $page->drawText($order_tax_total, $col_3_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles'] / 2), 'UTF-8');
            }
            if ($show_order_grand_total == 1) {
                $page->drawText($helper->__('Order GT') . ': ', $col_3_label_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles']), 'UTF-8');
                $order_grand = $this->formatPriceTxt($order_temp, $order_grand, 2, '.', ',');
                //$product_total_Width = $this->parseString($helper->__('Product Grand Total') . ': ', Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), 24);
                $page->drawText($order_grand, $col_3_value_X, ($this->y - $generalConfig['font_size_subtitles'] + 3 - $generalConfig['font_size_subtitles']), 'UTF-8');
            }
            $adjust_address = 0;
            //TODO
			
			// if showing a barcode, no space, so let's add ship and payment method on a new line
			if (($barcodes == 1) && (($shmethod == 1) || ($payment_method_yn == 1)) ) {
				$this->y = ( $order_detail_line_Y + (($generalConfig['font_size_subtitles'] - 6)/2) );
				$shipping_method_x = $padded_left + 9;
				$payment_method_x = $padded_left + 9;
				$top_box_Y = $order_detail_line_Y-0.5;
				$bottom_box_Y = ($this->y - 9 - ($generalConfig['font_size_subtitles'] - 5) - (($generalConfig['font_size_subtitles'] - 6)/2) );

	            $page->setFillColor($background_color_orderdetails_lighter_zend);
	            $page->setLineColor($background_color_orderdetails_lighter_zend);
	            $page->drawRectangle(27, $top_box_Y, $padded_right, $bottom_box_Y);
			
	            $page->setLineColor($font_color_body_zend);
	            $page->setLineWidth(0.5);
	            $page->drawLine(27, $bottom_box_Y, $padded_right, $bottom_box_Y);
			} else {
                $payment_method_x = $padded_left + 9;
                if ($payment_method_x <= 110 + $barcodeWidth + 170)
                    $payment_method_x = 110 + $barcodeWidth + 175;
			}
				
            if ($shmethod == 1) {
                //if($shipping_method_x <= 110 + $barcodeWidth + 170 + 30 + 60)
                //$shipping_method_x = 110 + $barcodeWidth + 170 + 30 + 60 ;
                $max_width = $padded_right - $shipping_method_x + 15;
                if (($tickbox != 0) && ($barcodes == 1))
                    $max_width -= 18;
                $max_chars              = $this->getMaxchars(($generalConfig['font_size_body'] * 0.7), $sku_shipping_method[$order_id], $max_width);
                $shipping_method        = wordwrap($sku_shipping_method[$order_id], $max_chars - 2, "\n", false);
                $return_shipping_method = explode("\n", $shipping_method);
                $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] * 0.7), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText($return_shipping_method[0], $shipping_method_x, ($this->y - ($generalConfig['font_size_body'])), 'UTF-8');
                if($barcodes == 1)
					$adjust_address += $generalConfig['font_size_body'] / 2;
                foreach ($return_shipping_method as $key => $value) {
                    if ($value !== '' && $key > 0) {
                        $this->y -= $generalConfig['font_size_body'] / 2;
                        $adjust_address += $generalConfig['font_size_body'] / 2;
                        $page->drawText($value, $shipping_method_x, ($this->y - ($generalConfig['font_size_body'])), 'UTF-8');
                    }
                }
            }
            
            if ($payment_method_yn == 1) {
				if($shmethod == 1)
					$this->y -= (($generalConfig['font_size_body'] * 0.7)/2);
                $max_width                  = $padded_right - $payment_method_x + 15;
                $max_chars                  = $this->getMaxchars(($generalConfig['font_size_body'] * 0.7), $payment_method[$order_id], $max_width);
                $payment_method_wrap        = wordwrap($payment_method[$order_id], $max_chars, "\n", false);
                $return_payment_method_wrap = explode("\n", $payment_method_wrap);
                
                $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] * 0.7), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText($return_payment_method_wrap[0], $payment_method_x, ($this->y - 7 - ($generalConfig['font_size_body'] - 3)), 'UTF-8');
                if($barcodes == 1)
					$adjust_address += $generalConfig['font_size_body'] / 2;
                foreach ($return_payment_method_wrap as $key => $value) {
                    if ($value !== '' && $key > 0) {
                        $this->y -= $generalConfig['font_size_body'] / 2;
                        $adjust_address += $generalConfig['font_size_body'] / 2;
                        $page->drawText($value, $payment_method_x, ($this->y - 7 - ($generalConfig['font_size_body'] - 3)), 'UTF-8');
                    }
                }
            }
			
	
            
            if ($warehouseyn == 1) {
                $this->_setFont($page, 'bold', ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                $page->drawText($sku_warehouse[$order_id], $warehouse_x, ($this->y - 15), 'UTF-8');
            }
            
            if ($giftwrapyn == 1 && isset($sku_giftwrap[$order_id])) {
                $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                $page->drawText($sku_giftwrap[$order_id]['wrapping_paper'], $giftwrap_x, ($this->y - 15), 'UTF-8');
                if (isset($sku_giftwrap[$order_id]['message']) && (strlen($sku_giftwrap[$order_id]['message']) > 0)) {
                    $page->setFillColor($background_color_subtitles_zend);
                    $page->setLineColor($background_color_subtitles_zend);
                    $page->drawRectangle(27, ($this->y - 31), $padded_right, ($this->y - 20));
                    $this->y -= 10;
                    $this->_setFont($page, 'regular', ($generalConfig['font_size_subtitles'] - 4), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText($sku_giftwrap[$order_id]['message'], $giftwrap_x, ($this->y - 15), 'UTF-8');
                }
            }
            //TODO shipping/billing address
            if ( ($order_address_yn == 1) || ($order_billing_address_yn ==1) ) {
                $this->y += $adjust_address + 2;
                if (isset($sku_giftwrap[$order_id]['message']) && (strlen($sku_giftwrap[$order_id]['message']) > 0)) {
                    $this->_setFont($page, 'regular', $generalConfig['font_size_body']*0.8, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                   $this->y -= ($generalConfig['font_size_body'] * 1.1);
                    $page->drawText($sku_shipping_address[$order_id], 31, $this->y + 12, 'UTF-8');
                    $this->y -= ($generalConfig['font_size_body'] * 1.5);
                    
                } else {
                    // order #
                    $this->y -= ($generalConfig['font_size_body'] * 2.8);
					if($order_address_yn == 1){
						$this->_setFont($page, 'regular', $generalConfig['font_size_body']*0.8, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	                    $this->y -= ($generalConfig['font_size_body'] * 1);
						$page->drawText($sku_shipping_address[$order_id], 31, $this->y, 'UTF-8');
					}
					if($order_billing_address_yn == 1){
						$this->_setFont($page, 'regular', $generalConfig['font_size_body']*0.8, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
						$this->y -= ($generalConfig['font_size_body'] * 1);
						$page->drawText($sku_billing_address[$order_id], 31, $this->y, 'UTF-8');
					}
					$this->y -= ($generalConfig['font_size_body'] * 1.5);
                }
            }
			
		
			if( ($order_billing_address_yn == 0) && ($order_address_yn==0) ) {
                $this->y += $adjust_address + 2;
                $this->y -= 42;
            }
            
            if ($sort_packing != 'none') {
                $sortorder_packing_bool = false;
                if ($sortorder_packing == 'ascending')
                    $sortorder_packing_bool = true;
                sksort($order_build, $sort_packing, $sortorder_packing_bool);
                // sksort($product_build_item,$sort_packing,$sortorder_packing_bool);
            }
            $grey_next_line = 0;
            unset($order_build["temp_order"]);
            foreach ($order_build as $sku => $value) {
                
                $dsku      = $value['sku'];
                $qty       = $value['qty'];
                $sqty      = $value['sqty'];
                $productId = $value['id'];
                
                if ($grey_next_line == 1) {
                    $page->setFillColor($alternate_row_color);
                    $page->setLineColor($alternate_row_color);
                    
                    $grey_box_y1 = ($this->y - ($generalConfig['font_size_body'] / 5));
                    $grey_box_y2 = ($this->y + ($generalConfig['font_size_body'] * 0.85));
                    
                    $page->drawRectangle(25, $grey_box_y1, $padded_right, $grey_box_y2);
                    $grey_next_line = 0;
                } else
                    $grey_next_line = 1;
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
                
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                
                if ($tickbox != 0) {
                    $page->setLineWidth(1);
                    $page->setFillColor($white_color);
                    $page->setLineColor($black_color);
                    $page->drawRectangle($tickbox_X, ($this->y), $tickbox_X + 7, ($this->y + 7));
                    if ($tickbox2 != 0) {
                        $page->drawRectangle($tickbox2_X, ($this->y), $tickbox2_X + 7, ($this->y + 7));
                    }
                }
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                
                $sku_addon = '';
                if ($shelving_yn == 1 && trim($sku_shelving[$sku]) != '') {
                    if ($shelvingpos == 'col') {
                        $page->drawText('[' . $sku_shelving[$sku] . ']', $shelvingX, $this->y, 'UTF-8');
                    } else {
                        $sku_addon = ' / [' . $sku_shelving[$sku] . ']';
                    }
                }
                
                $max_qty_length_display = 0;
                
                if ($from_shipment == 'shipment') {
                    $max_qty_length_display = ((($max_qty_length + 5) * ($generalConfig['font_size_body'] * 1.1)) - 57);
                    $qty_string             = 's:' . (int) $sku_order_id_sqty[$order_id][$sku] . ' / o:' . $qty;
                } else {
                    $qty_string = $qty;
                }
                

                /*get qty base on config setting**/
                if (isset($sku_order_id_sqty[$order_id][$sku]))
                    $sku_qty_shipped = $sku_order_id_sqty[$order_id][$sku];
                else
                    $sku_qty_shipped = 0;
                if (isset($sku_order_id_iqty[$order_id][$sku]))
                    $sku_qty_invoiced = $sku_order_id_iqty[$order_id][$sku];
                else
                    $sku_qty_invoiced = 0;
                
                
                $qty_string             = $this->getQtyString($from_shipment, $sku_qty_shipped, $qty, $sku_qty_invoiced);
                /*************/
                $product_qty_upsize_yn  = $this->_getConfig('product_qty_upsize_yn', 1, false, 'picks2');
                $product_qty_rectangle  = 0;
                if ($product_qty_upsize_yn == '1' || $product_qty_upsize_yn == '1') {
                    if ($product_qty_upsize_yn == '1')
                        $product_qty_rectangle = 1;
                    $product_qty_upsize_yn  = 1;
                }
                /**************/
                $red_color  = new Zend_Pdf_Color_Html('darkRed');
                $qty_string = round($qty_string, 2);
                if ($product_qty_upsize_yn == 1 && $qty_string > 1) {
                    if ($product_qty_rectangle == 1) {
                        $page->setLineWidth(1);
                        $page->setLineColor($black_color);
                        $page->setFillColor($black_color);
                       
                        if (($qty_string >= 100) || (strlen($qty_string) > 3))
                            $page->drawRectangle(($qtyX), ($this->y - 1), ($qtyX + (strlen($qty_string) * $generalConfig['font_size_body']*2/3)), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                        else if (($qty_string >= 10) || (strlen($qty_string) >= 2))
                            $page->drawRectangle(($qtyX - 1), ($this->y - 1), ($qtyX - 8 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                        else
                            $page->drawRectangle(($qtyX - 1), ($this->y - 1), ($qtyX - 2 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                        //}
                        $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                        //$page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');
                        $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                    } else {
                        $this->_setFont($page, 'bold', ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                    }
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                } else
                    $page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');
                $display_sku = '';
                if (strlen($sku) > ($character_breakpoint_sku + 2)) {
                    $display_sku = substr(htmlspecialchars_decode($dsku), 0, ($character_breakpoint_sku)) . '…';
                }
                else
                    $display_sku = htmlspecialchars_decode($dsku);
                
                if ($skuyn == 1)
                    $page->drawText($display_sku . $sku_addon, $skuX + $max_qty_length_display, $this->y, 'UTF-8');
                
                if ($nameyn == 1 && isset($sku_name[$sku])) {
                    $print_name       = $sku_name[$sku] . (isset($name_addon) ? $name_addon : '');
                    if(strlen($print_name) > 0)
                    {
                        $next_col_to_name = getPrevNext2($columns_xpos_array, 'Name', 'next', $padded_right - 30);
                        $max_width_length = ($next_col_to_name - $namenudge);
                        $font_temp        = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $line_width_name  = $this->parseString($print_name, $font_temp, $generalConfig['font_size_body']);
                        $char_width_name  = $line_width_name / (strlen($print_name));
                        $max_chars_name   = round($max_width_length / $char_width_name);
                        $name_trim        = str_trim($print_name, 'WORDS', $max_chars_name - 3, '...');
                        $page->drawText($name_trim, intval($namenudge), $this->y, 'UTF-8');
                    }
                }
                
                /*price/shipping price*/
                if ($product_price_yn == 1) {
                    $priceX      = $nudge_price;
                    $print_price = $this->formatPriceTxt($order,$value["price"], 2, '.', ',');
                    $page->drawText($print_price, $priceX, $this->y, 'UTF-8');
                    
                    if($product_total_column_yn == 1)
                    {
                        $total_price_column += $value["price"] * $value["qty"];
                        $total_price_column_print = $this->formatPriceTxt($order,$total_price_column, 2, '.', ',');
                        $page->drawText($total_price_column_print, $total_column_Xpos, $this->y, 'UTF-8');
                    }
                    
                }
                
                /**
                images start
                */
                if (($product_images_yn == 1) && isset($sku_image_paths[$sku]['path'][0])) {
                    $product_images_line_nudge = 0;
                    $product_images_line_nudge = ($sku_image_paths[$sku]['height'] / 2);
                    if ($product_images_border_color_temp != '#FFFFFF') {
                        $product_images_line_nudge += 1.5;
                    }

                    $image_x_addon   = 0;
                    $image_x_addon_2 = 0;
                    $x1              = $col_title_product_images[1];
                    $y1              = ($this->y - $sku_image_paths[$sku]['height']);
                    $x2              = ($col_title_product_images[1] + $sku_image_paths[$sku]['width']);
                    $y2              = ($this->y);
                    
                    if (($this->y - $sku_image_paths[$sku]['height'] - 3) < (20 + ($generalConfig['font_size_subtitles'] * 2))) {
                        if ($page_count == 1 && $return_address_yn == 0 && $bottom_shipping_address_yn == 0) {
                            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($generalConfig['font_size_subtitles'] * 2), 'UTF-8');
                        }
                        $page = $this->newPage();
                        $page_count++;
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        //Check
                        if ($second_page_start == 'asfirst')
                            $this->y = $items_header_top_firstpage;
                        else
                            $this->y = $page_top;
                        
                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y), 'UTF-8');
                        $this->y = ($this->y - ($generalConfig['font_size_subtitles'] * 2));
                        
                        if (strtoupper($generalConfig['background_color_subtitles']) != '#FFFFFF') {
                            $page->setFillColor($background_color_subtitles_zend);
                            $page->setLineColor($background_color_subtitles_zend);
                            $page->setLineWidth(0.5);
                            if ($fill_product_header_yn == 0) {
                                $page->drawLine($x1, ($this->y - ($generalConfig['font_size_subtitles'] / 1.5) - 1.5), ($padded_right), ($this->y - ($generalConfig['font_size_subtitles'] / 1.5) - 1.5));
                                $page->drawLine($x1, ($this->y + $generalConfig['font_size_subtitles'] + 1.5 + 1.5), ($padded_right), ($this->y + $generalConfig['font_size_subtitles'] + 1.5 + 1.5));
                            } else {
                                $page->drawRectangle($x1, ($this->y - ($generalConfig['font_size_subtitles'] / 1.5)), $padded_right, ($this->y + $generalConfig['font_size_subtitles'] + 1.5));
                            }
                        }
                        
                        $this->_setFont($page, 'bold', $generalConfig['font_size_body'] + 2, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        
                        
                        $this->y -= ($generalConfig['font_size_subtitles'] * 1.5);
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    }
                    
                    $y1 = ($this->y - $sku_image_paths[$sku]['height'] - 5);
                    $y2 = ($this->y - 5);
                    
                    $image_ext = '';
                    $ext_tmp = explode('.', $sku_image_paths[$sku]['path'][0]);
                    $image_ext = array_pop($ext_tmp);
                    if (($image_ext != 'jpg') && ($image_ext != 'jpeg') && ($image_ext != '.png'))
                        continue;
                    
                    if ($product_images_border_color_temp != '#FFFFFF') {
                        $page->setLineWidth(0.5);
                        $page->setFillColor($product_images_border_color);
                        $page->setLineColor($product_images_border_color);
                        $page->drawRectangle(($x1 - 1.5 + $image_x_addon_2), ($y1 - 1.5), ($x2 + 1.5 + $image_x_addon_2), ($y2 + 1.5));
                        $page->setFillColor($black_color);
                    }
                    
                    $image = Zend_Pdf_Image::imageWithPath($sku_image_paths[$sku]['path'][0]);
                    $page->drawImage($image, $x1 + $image_x_addon_2, $y1, $x2 + $image_x_addon_2, $y2);
                    
                    $this->y -= (2 * $generalConfig['font_size_body'] + 5 + $sku_image_paths[$sku]['height']);
                    if (!isset($product_build_value['bundle_options_sku'])) {
                        $this->y += ($product_images_line_nudge - ($generalConfig['font_size_body'] / 2));
                    }
                    $has_shown_product_image = 1;
                }
                /**
                images end
                */
                
                if ($product_barcode_yn == 1) {
                    $barcode_font_size    = 20;
                    //$page->drawText($productId, $product_barcode_X -20, $this->y , 'UTF-8');
                    $productbarcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($productId, $generalConfig['barcode_type']);
                    $productbarcodeWidth  = $this->parseString($productId, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                    $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                    $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                    $page->drawText($productbarcodeString, ($product_barcode_X), ($this->y + 3 - $barcode_font_size), 'CP1252');
                    //print white rectangle
                    $page->setFillColor($white_color);
                    $page->setLineColor($white_color);
                    $page->drawRectangle(($product_barcode_X - 2), ($this->y - 2), ($product_barcode_X + $productbarcodeWidth + $productbarcodeWidth + $barcode_font_size), ($this->y - $barcode_font_size + 2));
                    if ($product_barcode_bottom_yn == 1) {
                        $this->_setFont($page, $generalConfig['font_style_body'], ($product_barcode_bottom_font_size), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $page->drawText($childProductId, $product_barcode_X + $productbarcodeWidth - 4, $this->y - 8, 'UTF-8');
                    }
                }
                
                
                if (isset($sku_stock[$sku]) && ($sku_stock[$sku] < $stockcheck) && $stockcheck_yn == 1) {
                    $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    
                    $this->y -= ($generalConfig['font_size_body'] + 2);
                    $page->setFillColor($red_bkg_color);
                    $page->setLineColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
                    $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    $warning = 'Stock Warning      SKU: ' . $sku . '    Net Stock After All Picks : ' . $sku_stock[$sku];
                    $page->drawText($warning, 60, $this->y, 'UTF-8');
                    $this->y -= 4;
                }
                
                if (isset($sku_order_id_options[$order_id][$sku]) && $sku_order_id_options[$order_id][$sku] != '') {
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_options'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    if ($options_yn == 0 && $options_yn_base == 1) {
                        $this->y -= ($generalConfig['font_size_body']);
                        $page->drawText($helper->__('Options') . ': ' . $sku_order_id_options[$order_id][$sku], ($namenudge + 20), $this->y, 'UTF-8');
                    } elseif ($options_yn_base == 'yesstacked') {
                        /**
                        
                        */
                        $this_item_options = '';
                        $this_item_options = trim(str_replace('Array[', '[', $sku_order_id_options[$order_id][$sku]));
                        
                        $maxWidthPage      = ($padded_right + 20 - 80);
                        $font_temp         = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $font_size_compare = ($generalConfig['font_size_options']);
                        $line_width        = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                        $char_width        = $line_width / 10;
                        $max_chars         = round($maxWidthPage / $char_width);
                        
                        if (strlen($this_item_options) > $max_chars) {
                            if ($options_yn_base == '1') {
                                $chunks = split_words($this_item_options, '/ /', $max_chars);
                            } elseif ($options_yn_base == 'yesstacked') {
                                $chunks = explode('[', $this_item_options);
                            }
                            
                            $lines = 0;
                            foreach ($chunks as $key => $chunk) {
                                $chunk_display = '';
                                
                                if (trim($chunk != '')) {
                                    $this->y -= ($generalConfig['font_size_options']);
                                    $options_y_counter += $generalConfig['font_size_options'];
                                    
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
                                        $options_y_counter += $generalConfig['font_size_options'];
                                        $chunk_display = str_replace('[[', '[', '[' . $chunk);
                                        $page->drawText($chunk_display, 80, $this->y, 'UTF-8');
                                        $lines++;
                                    }
                                }
                                unset($chunks);
                            } else {
                                $this->y -= ($generalConfig['font_size_options']);
                                $options_y_counter += $generalConfig['font_size_options'];
                                $page->drawText($this_item_options, 80, $this->y, 'UTF-8');
                            }
                        }
                    } elseif ($options_yn_base == 1) {
                        ksort($options_sku_parent[$order_id][$sku]);
                        
                        foreach ($options_sku_parent[$order_id][$sku] as $options_sku => $options_qty) {
                            
                            
                            if (!in_array($options_sku, $pickpack_options_filter_array)) {
                                $this->y -= ($generalConfig['font_size_body'] - 2);
                                
                                if ($tickbox != 0) {
                                    $page->setFillColor($white_color);
                                    $page->setLineColor($black_color);
                                    $page->setLineWidth(0.5);
                                    $page->drawRectangle($tickbox_X + 4, ($this->y), $tickbox_X + 4 + 7, ($this->y + 7));
                                    
                                    if ($tickbox2 != 0) {
                                        $page->drawRectangle($tickbox2_X + 20, ($this->y), $tickbox2_X + 20 + 7, ($this->y + 7));
                                    }
                                    $page->setLineWidth(1);
                                }
                                $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                
                                if (!in_array($options_sku, $pickpack_options_count_filter_array)) {
                                    $page->drawText($options_qty, ($qtyX + 4), $this->y, 'UTF-8');
                                    // $page->drawText('x [ '.$options_sku.' ]', ($skuX+$max_qty_length_display + 4) , $this->y , 'UTF-8');
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
                    $box_x  = ($qtyX - $offset);
                    $this->y -= ($generalConfig['font_size_body']);
                    $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $page->drawText($helper->__('Bundle Options') . ' : ', $box_x, $this->y, 'UTF-8');
                    $grey_next_line_bundle = 0;
                    
                    foreach ($sku_bundle[$order_id][$sku] as $key => $value) {
                        
                        $sku              = '';
                        $name             = '';
                        $shelf            = '';
                        $qty              = '';
                        $sku_bundle_array = explode('##', $value);
                        $sku              = $sku_bundle_array[0];
                        $name             = $sku_bundle_array[1];
                        $shelf            = $sku_bundle_array[2];
                        $qty              = $sku_bundle_array[3];
                        $childProductId   = $sku_bundle_array[4];
                        
                        
                        
                        $this->y -= ($generalConfig['font_size_body'] - 0);
                        
                        if ($grey_next_line_bundle == 1) {
                            $page->setFillColor($alternate_row_color);
                            $page->setLineColor($alternate_row_color);
                            
                            $grey_box_y1 = ($this->y - (($generalConfig['font_size_body'] - 2) / 5));
                            $grey_box_y2 = ($this->y + (($generalConfig['font_size_body'] - 2) * 0.85));
                            
                            $page->drawRectangle(40, $grey_box_y1, $padded_right, $grey_box_y2);
                            $grey_next_line_bundle = 0;
                        } else
                            $grey_next_line_bundle = 1;
                        
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        if ($skuyn == 1)
                            $page->drawText($sku, ($skuX + $skuXInc + 10), $this->y, 'UTF-8');
                        $page->drawText($sku, ($skuX + $skuXInc + 10), $this->y, 'UTF-8');
                        $page->drawText($name, intval($namenudge + 10), $this->y, 'UTF-8');
                        $page->drawText($shelf, $shelvingX, $this->y, 'UTF-8');
                        /*************************/
                        if ($product_qty_upsize_yn == 1 && $qty > 1) {
                            if ($product_qty_rectangle == 1) {
                                $page->setLineWidth(1);
                                $page->setLineColor($black_color);
                                $page->setFillColor($black_color);
                                if ($qty >= 100)
                                    $page->drawRectangle(($qtyX), $this->y, ($qtyX - 1 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                                else if ($qty >= 10)
                                    $page->drawRectangle(($qtyX - 1), $this->y, ($qtyX - 7 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                                else
                                    $page->drawRectangle(($qtyX - 1), $this->y, ($qtyX - 2 + (strlen($qty) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                                $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                                //$page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');
                                $page->drawText($qty, ($qtyX), ($this->y), 'UTF-8');
                            } else {
                                $this->_setFont($page, 'bold', ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $page->drawText($qty, ($qtyX), ($this->y), 'UTF-8');
                            }
                            
                        } else
                            $page->drawText($qty, $qtyX, $this->y, 'UTF-8');
                        
                        //print child barcode
                        if ($product_barcode_yn == 1) {
                            $barcode_font_size         = 20;
                            $productbarcodeString      = Mage::helper('pickpack/barcode')->convertToBarcodeString($childProductId, $generalConfig['barcode_type']);
                            $productbarcodeStringWidth = $this->parseString($productbarcodeString, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                            $productbarcodeWidth       = $this->parseString($childProductId, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                            $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                            $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                            //$page->drawText($productbarcodeString, ($product_barcode_X), ($this->y), 'CP1252');
                            $page->drawText($productbarcodeString, ($product_barcode_X), ($this->y + 3 - $barcode_font_size), 'CP1252');
                            $page->setFillColor($white_color);
                            $page->setLineColor($white_color);
                            //$page->drawRectangle(($productbarcodeWidth + 38), ($this->y-2), (40+$productbarcodeWidth+$productbarcodeWidth+10), ($this->y - 18));
                            $page->drawRectangle(($product_barcode_X - 2), ($this->y - 2), ($product_barcode_X + $productbarcodeWidth + $productbarcodeWidth + $barcode_font_size), ($this->y - $barcode_font_size + 2));
                            
                            
                            if ($product_barcode_bottom_yn == 1) {
                                $this->_setFont($page, $generalConfig['font_style_body'], ($product_barcode_bottom_font_size), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $page->drawText($childProductId, $product_barcode_X + $productbarcodeWidth - 4, $this->y - 8, 'UTF-8');
                            }
                            
                        }
                        
                        if ($tickbox != 0) {
                            $page->setFillColor($white_color);
                            $page->setLineColor($black_color);
                            $page->setLineWidth(0.5);
                            $page->drawRectangle($tickbox_X + 4, ($this->y), $tickbox_X + 4 + 7, ($this->y + 7));
                            
                            if ($tickbox2 != 0) {
                                $page->drawRectangle($tickbox2_X + 20, ($this->y), $tickbox2_X + 20 + 7, ($this->y + 7));
                            }
                            $page->setLineWidth(1);
                        }
                        
                        // if(((isset($this->skuSupplierItemAction[$supplier][$sku]) && $this->skuSupplierItemAction[$supplier][$sku] != 'keepGrey')) && ($tickbox == 'pickpack'))
                        //                         {
                        //                             $page->setLineWidth(0.5);
                        //                             $page->setFillColor($white_color);
                        //                             $page->setLineColor($black_color);
                        //                             $page->drawRectangle($box_x, ($this->y), ($box_x + ($generalConfig['font_size_body']-2-4)), ($this->y+($generalConfig['font_size_body']-2-4)));
                        //                             $page->setFillColor($black_color);
                        //                         }
                        if ($product_barcode_yn == 1) {
                            $this->y -= 4;
                            if ($product_barcode_bottom_yn == 1)
                                $this->y -= 6;
                            
                        }
                    }
                }
                
                //More space for barcode
                if ($product_barcode_yn == 1) {
                    $this->y -= 4;
                    if ($product_barcode_bottom_yn == 1)
                        $this->y -= 4;
                }
                $this->y -= ($generalConfig['font_size_body'] + 1);
                // }
            }
            // end roll_SKU
            unset($order_temp);
        }
        // end roll_Order
        $this->y -= 30;
        if ($show_sheet_total_yn == 1) {
            if (($this->y - 3 * $generalConfig['font_size_body'] * 1.4) < 60) {
                if ($page_count == 1) {
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - 15), 'UTF-8');
                }
                $page = $this->newPage();
                $page_count++;
                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
            }
            if ($show_total_order == 1)
                $addjust_line = 4;
            else
                $addjust_line = 3;
            
			$page->setFillColor($white_bkg_color);
            $page->setLineColor($orange_bkg_color);
            $page->setLineWidth(1);
			
            $page->drawRectangle(300, ($this->y - $addjust_line * ($generalConfig['font_size_body'] - 1) * 1.5 - 1.5 * ($generalConfig['font_size_body'] - 1)), 570, ($this->y + 10 + ($generalConfig['font_size_body'] - 1)));
            $this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            $page->drawText($helper->__('Total quantity') , 320, $this->y, 'UTF-8');
            $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            $page->drawText($helper->__($total_quantity), 490, $this->y, 'UTF-8');
            $this->y -= ($generalConfig['font_size_body'] - 1) + ($generalConfig['font_size_body'] - 1) / 2;
          
		    if ($show_total_order == 1) {
				$this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);	
	            $page->drawText($helper->__('Total orders')  , 320, $this->y, 'UTF-8');
	            $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	            $page->drawText($helper->__($order_count), 490, $this->y, 'UTF-8');
                $this->y -= ($generalConfig['font_size_body'] - 1) + ($generalConfig['font_size_body'] - 1) / 2;
            }
          
		    if ($show_tax_withouttax == 1) {
                $total_price = $total_price_with_tax;
                $this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_std_characters'], $generalConfig['font_color_body']);
				$page->drawText($helper->__('Total price (excl. Tax)') , 320, $this->y, 'UTF-8');
	            $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	            $page->drawText($currency_symbol . "  " . $total_price, 490, $this->y, 'UTF-8');
            } else {
                $total_price = $total_price_without_tax;
				$this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText($helper->__('Total price (excl. Tax)') , 320, $this->y, 'UTF-8');
	            $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	            $page->drawText($currency_symbol . "  " . $total_price, 490, $this->y, 'UTF-8');
            }
			
            $this->y -= ($generalConfig['font_size_body'] - 1) + ($generalConfig['font_size_body'] - 1) / 2;
            
			if ($show_tax_withouttax == 1) {
                $total_price_ship = $total_price_shipping_with_tax;
				$this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText($helper->__('Total shipping price (incl. Tax)') , 320, $this->y, 'UTF-8');
	            $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	            $page->drawText($currency_symbol . "  " . $total_price_ship, 490, $this->y, 'UTF-8');
            } else {
                $total_price_ship = $total_price_shipping_without_tax;
				$this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText($helper->__('Total shipping price (excl. Tax)') , 320, $this->y, 'UTF-8');
	            $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	            $page->drawText($currency_symbol . "  " . $total_price_ship, 490, $this->y, 'UTF-8');
            }
            $this->y -= ($generalConfig['font_size_body'] - 1) + ($generalConfig['font_size_body'] - 1) / 2;
            
			if ($show_sheet_grand_total_tax == 1) {
				$this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText($helper->__('Sheet Grand Total (incl. Tax)')  , 320, $this->y, 'UTF-8');
	            $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	            $page->drawText($currency_symbol . "  " . $total_grand_with_tax, 490, $this->y, 'UTF-8');
			} else {
				$this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText($helper->__('Sheet Grand Total (excl. Tax)') , 320, $this->y, 'UTF-8');
	            $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
	            $page->drawText($currency_symbol . "  " . $total_grand_without_tax, 490, $this->y, 'UTF-8');
			}
        }
		
        $page->setLineColor($background_color_orderdetails_zend);
        $page->setFillColor($background_color_orderdetails_zend);
        $page->drawRectangle(27, 22, $padded_right, 22);
		
		$this->_setFont($page, 'semibold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        $page->drawText($helper->__('Printed:'), 210, 8, 'UTF-8');
        $this->_setFont($page, 'regular', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        $page->drawText(date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())), 250, 8, 'UTF-8');
		
        /* Add QC Message*/
        if ($packed_by_yn == 1) {
            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            $page->drawText(Mage::helper('sales')->__($packed_by_text), $packedByXY[0], $packedByXY[1], 'UTF-8');
        }
        if ($packed2_by_yn == 1) {
            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            $page->drawText(Mage::helper('sales')->__($packed2_by_text), $packed2ByXY[0], $packed2ByXY[1], 'UTF-8');
        }
        $this->_afterGetPdf();
        
        return $pdf;
    }
}