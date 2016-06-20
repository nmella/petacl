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
 * File        Combined.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Combined extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    public function __construct() {
        parent::__construct();
    }

    public function getGeneralConfig() {
        return Mage::helper('pickpack/config')->getGeneralConfigArray($this->getStoreId());
    }
	
    public function getPageConfig() {
        return Mage::helper('pickpack/config')->getPageConfigArray();
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
	
    private function getLineCountOption($sku_order_id_options) {
        $line_count = 0;
        $options_splits = array_filter(explode('newline', $sku_order_id_options));
        $options_splits = $this->groupOptionProduct($options_splits);
        foreach ($options_splits as $options_split => $value) {
            //$temp_str1 = substr($options_split, strpos($options_split, 'qty_ordered'), strlen($options_split));
            //$temp_str1 = str_replace('qty_ordered', '', $temp_str1);
            $temp_str2 = $options_split;

            $temp_str2 = trim($temp_str2);
            $temp_str = array_filter(explode('] [', $temp_str2));
            $line_count += count($temp_str);
        }
        return $line_count;
    }

    public function getQtyString($shiped_sku_qty, $invoiced_sku_qty, $qty) {
        $store_id = Mage::app()->getStore()->getId();
        $show_qty_options = $this->_getConfig('show_qty_options', 1, false, 'messages', $store_id);
        switch ($show_qty_options) {
            case 1:
                $qty_string = $qty;

                break;
            case 2:
                $qty_string = 'q:' . ($qty - (int)$shiped_sku_qty) . ' s:' . (int)$shiped_sku_qty . ' o:' . (int)$qty;

                break;
            case 3:
                $qty_string = ($qty - (int)$shiped_sku_qty);

                break;

            case 4:
                $qty_string = $invoiced_sku_qty;

                break;
        }
        return $qty_string;
    }

	//$shipments = array(), $from_shipment = 'order') 
    public function getPickCombined($orders = array(), $output = 'order_combined'){
        /*************************** BEGIN PDF GENERAL CONFIG *******************************/
        $this->setGeneralConfig(Mage::app()->getStore()->getStoreId());
        /*************************** END PDF GLOBAL PAGE CONFIG *******************************/

        $helper = Mage::helper('pickpack');
		$pageConfig = $this->getPageConfig('default_page');
		$generalConfig = $this->getGeneralConfig();
		
        $from_shipment = 'order';
        $trolleybox_yn = 0;
        $trolleybox_max = null;
        $show_orderid_with_trolleyboxid_yn = 0;
        $qty_yn = 1;
        $config_group = 'messages';
        $is_warehouse_supplier = 0;
       

        $show_bundle_parent_yn = $this->_getConfig('combined_show_bundle_parent_yn', 1, false, $config_group);
        //$show_bundle_parent_yn = $this->_getConfig('show_bundle_parent', "no", false, 'general');
        if ($output == 'trolleybox') {
            $config_group = 'trolleybox_picklist';
            $trolleybox_yn = $this->_getConfig('trolleybox_yn', 1, false, $config_group);
            $qty_yn = $this->_getConfig('qty_yn', 0, false, $config_group);
            $show_orderid_with_trolleyboxid_yn = $this->_getConfig('show_orderid_with_trolleyboxid_yn', 1, false, $config_group);
            $show_bundle_parent_yn = $this->_getConfig('show_bundle_parent_yn', 0, false, $config_group);

            if ($trolleybox_yn == 1) {
                $trolleybox_max = $this->_getConfig('trolleybox_max', 6, false, $config_group);
            }
        }
        $shipments = array();
        $store_id = Mage::app()->getStore()->getId();

        // product_separated or order_combined
        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();

        $page_size = $generalConfig['page_size'];

        $padded_left = 20;
        if ($page_size == 'letter') {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $page_top = 770;
            $padded_right = 587;
            //} elseif ($page_size == 'a4') {
        } else {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $page_top = 820;
            $padded_right = 570;
        } /*
        elseif ($page_size == 'a5-landscape') {
                    $page = $pdf->newPage('596:421');
                    $page_top = 395;
                    $padded_right = 573;
                } elseif ($page_size == 'a5-portrait') {
                    $page = $pdf->newPage('421:596');
                    $page_top = 573;
                    $padded_right = 395;
                }*/
        

        $pdf->pages[] = $page;

        $qtyX = 40;
        $productX = 250;
        $font_size_overall = 15;
        $font_size_productline = 9;
        $total_quantity = 0;
        $total_quantity_ordered = 0;
        $total_quantity_shipped = 0;
        $total_quantity_invoiced = 0;
        $total_quantity_shipped = 0;
        $total_quantity_invoiced = 0;
        $total_cost = 0;
        $error_product_count = 0;

        $shipping_subtotal_yn = $this->_getConfig('shipping_subtotal_yn', 0, false, $config_group);
        $total_paid_subtotal_yn = $this->_getConfig('total_paid_subtotal_yn', 0, false, $config_group);

        $red_bkg_color = new Zend_Pdf_Color_Html('lightCoral');
        $grey_bkg_color = new Zend_Pdf_Color_GrayScale(0.85);
        $alternate_row_color_temp = $this->_getConfig('alternate_row_color', '#DDDDDD', false, "general");
        $alternate_row_color = new Zend_Pdf_Color_Html($alternate_row_color_temp);
        $white_bkg_color = new Zend_Pdf_Color_Html('white');
        $orange_bkg_color = new Zend_Pdf_Color_Html('Orange');
        $black_color = new Zend_Pdf_Color_Rgb(0, 0, 0);
        $greyout_color = new Zend_Pdf_Color_GrayScale(0.6);
        $white_color = new Zend_Pdf_Color_GrayScale(1);

        $background_color_subtitles = $generalConfig['background_color_subtitles'];
        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($background_color_subtitles);
        $background_color_header_zend = new Zend_Pdf_Color_Html($background_color_subtitles);

        $font_color_header_zend = new Zend_Pdf_Color_Html($generalConfig['font_color_subtitles']);
        $font_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['font_color_subtitles']);
        $font_color_body_zend = new Zend_Pdf_Color_Html($generalConfig['font_color_body']);

        $show_qty_options = $this->_getConfig('show_qty_options', 1, false, 'messages', $store_id);
        $text_addon_totalqty = '';
        switch ($show_qty_options) {
            case 1:
                $text_addon_totalqty = '';
                break;
            case 2:
                $text_addon_totalqty = '';
                break;
            case 3:
                $text_addon_totalqty = ' (unshipped)';
                break;
            case 4:
                $text_addon_totalqty = ' (invoiced)';
                break;
        }

        $barcode_type =$generalConfig['barcode_type'];

        $printdates = Mage::getStoreConfig('pickpack_options/messages/pickpack_packprint');
        $date_format = $this->_getConfig('date_format', 'M. j, Y', false, 'general');

        $shelvingpos = $this->_getConfig('shelvingpos', 'col', false, 'general');

        $product_images_yn = $this->_getConfig('product_images_yn', 0, false, $config_group);
        if ($product_images_yn == 1) {
            $product_images_source = $this->_getConfig('product_images_source', 'thumbnail', false, $config_group);
            $product_images_parent_yn = $this->_getConfig('parent_image_yn', 0, false, $config_group);
            $col_title_product_images = explode(',', trim($this->_getConfig('col_title_product_images', ',150', false, $config_group)));
            $product_images_border_color_temp = strtoupper(trim($this->_getConfig('product_images_border_color', '#CCCCCC', false, $config_group)));
            $product_images_border_color = new Zend_Pdf_Color_Html($product_images_border_color_temp);
            $product_images_maxdimensions = explode(',', str_ireplace('null', '', $this->_getConfig('product_images_maxdimensions', '25,25', false, $config_group)));
            if ( ($product_images_maxdimensions[0] == '') || ($product_images_maxdimensions[1] == '') ) {
                if ($product_images_maxdimensions[0] == '')
                    $product_images_maxdimensions[0] = NULL;
                if ($product_images_maxdimensions[1] == '')
                    $product_images_maxdimensions[1] = NULL;
                if ( ($product_images_maxdimensions[0] == NULL) && ($product_images_maxdimensions[1] == NULL) )
                    $product_images_maxdimensions[0] = 50;
            }
            $product_images_source_res = $product_images_source;
            if ($product_images_source == 'gallery')
                $product_images_source_res = 'image';
        }

        $media_path = Mage::getBaseDir('media');

        $product_id = NULL;
        $stock = NULL;
        $sku_stock = array();
        $sku_qty = array();
        $sku_cost = array();
        $showcost_yn_default = 0;
        $currency_default = 'USD';

        $shelving_yn_default = 0;
        $shelving_attribute_default = '';
        $shelvingX_default = 200;
        $supplier_yn_default = 0;
        $supplier_attribute_default = 'supplier';
        $stockcheck_yn_default = 0;
        $stockcheck_default = 1;

        $tickbox_default = 'no';
        $supplierKey = 'order_combined';
        $split_supplier_yn_temp = $this->_getConfig('pickpack_split_supplier_yn', 'no', false, 'general');
        $split_supplier_options_temp = $this->_getConfig('pickpack_split_supplier_options', 'no', false, 'general');
        $split_supplier_options = explode(',', $split_supplier_options_temp);
        $split_supplier_yn = 'no';
        if ($split_supplier_yn_temp == 1) {
            if (in_array($supplierKey, $split_supplier_options))
                $split_supplier_yn = 'pickpack';
            else
                $split_supplier_yn = 'no';

        }

        $supplier_attribute = $this->_getConfig('pickpack_supplier_attribute', 'supplier', false, 'general');
        $supplier_options = $this->_getConfig('pickpack_supplier_options', 'filter', false, 'general');

        $userId = Mage::getSingleton('admin/session')->getUser() ? Mage::getSingleton('admin/session')->getUser()->getId() : 0;
        $user = ($userId !== 0) ? Mage::getModel('admin/user')->load($userId) : '';
        $username = (!empty($user['username'])) ? $user['username'] : '';

        $supplier_login_pre = $this->_getConfig('pickpack_supplier_login', '', false, 'general');
        $supplier_login_pre = str_replace(array(
            "\n",
            ','
        ), ';', $supplier_login_pre);
        $supplier_login_pre = explode(';', $supplier_login_pre);
        $supplier_login = '';
        foreach ($supplier_login_pre as $key => $value) {
            $supplier_login_single = explode(':', $value);
            if (preg_match('~' . $username . '~i', $supplier_login_single[0])) {
                if ($supplier_login_single[1] != 'all')
                    $supplier_login = trim($supplier_login_single[1]);
                else
                    $supplier_login = '';
            }
        }

        // double line spacing
        $doubleline_yn = $this->_getConfig('doubleline_yn', 1, false, $config_group);
        $sort_packing_yn = $this->_getConfig('sort_packing_yn', 1, false, $config_group);
        $sort_packing = $this->_getConfig('sort_packing', 'sku', false, $config_group);
        $sortorder_packing = $this->_getConfig('sort_packing_order', 'ascending', false, $config_group);
        $sort_packing_attribute = null;
        if ($sort_packing == 'attribute') {
            $sort_packing_attribute = trim($this->_getConfig('sort_packing_attribute', '', false, $config_group));
            if ($sort_packing_attribute != '')
                $sort_packing = $sort_packing_attribute;
            else
                $sort_packing = 'sku';
        } elseif ($sort_packing == 'sku') {
            $sort_packing_attribute = 'sku';
            $sort_packing = 'sku';
        } elseif ($sort_packing == 'name') {
            $sort_packing_attribute = 'name';
            $sort_packing = 'name';
        }

        $sort_packing_secondary = $this->_getConfig('sort_packing_secondary', 'sku', false, $config_group, $store_id);
        $sortorder_packing_secondary = $this->_getConfig('sort_packing_secondary_order', 'ascending', false, $config_group, $store_id);
        $sort_packing_secondary_attribute = null;
        if ($sort_packing_secondary == 'attribute') {
            $sort_packing_secondary_attribute = trim($this->_getConfig('sort_packing_secondary_attribute', '', false, $config_group, $store_id));
            if ($sort_packing_secondary_attribute != '') $sort_packing_secondary = $sort_packing_secondary_attribute;
            else $sort_packing_secondary = 'sku';
        }

        if ($sort_packing_yn == 0) {
            $sort_packing = null;
            $sortorder_packing = 'none'; // ascending/descending
            $sort_packing_secondary = 'none';
            $sort_packing_attribute = '';
        }

        $product_sku_barcode_yn = $this->_getConfig('pickpack_pickproductbarcode_combined_yn', 0, false, $config_group);
        //$sku_barcodeX = $this->_getConfig('pickpack_pickproductbarcode_combined_X_Pos', 0, false, $config_group);

        $tickbox = $this->_getConfig('pickpack_tickbox', $tickbox_default, false, 'general');
        $tickbox_X = 30;
        $tickbox2_X = 40;
        $tickbox = $this->_getConfig('combined_tickbox_yn', $tickbox_default, false, $config_group);
        $tickbox_title_X = $this->_getConfig('col_title_position_tickbox', 7, false, $config_group);
        $tickbox2 = $this->_getConfig('combined_tickbox2_yn', $tickbox_default, false, $config_group);
        $tickbox2_title_X = $this->_getConfig('col_title_position_tickbox2', 27, false, $config_group);
        $tickbox_title_X = explode(',', $tickbox_title_X);
        $tickbox_title = $tickbox_title_X[0];
        if (count($tickbox_title) > 1)
            $tickbox_X = $tickbox_title_X[1];
        $tickbox2_title_X = explode(',', $tickbox2_title_X);
        if (is_array($tickbox2_title_X)) {
            $tickbox2_title = $tickbox2_title_X[0];
            if (count($tickbox2_title_X) > 1 && $tickbox2_title_X[1] > 0)
                $tickbox2_X = $tickbox2_title_X[1];
        }
        if ($tickbox == 0) {
            $tickbox_X = 0;
            $tickbox2 = 0;
            $tickbox2_X = 0;
        } else
            $qtyX = ($tickbox_X > $tickbox2_X) ? ($tickbox_X + 20 + 15) : ($tickbox2_X + 20 + 15);
        $picklogo = $this->_getConfig('pickpack_picklogo', 0, false, 'general');

        $logo_maxdimensions = explode(',', '269,41');

        $showcount_yn = $this->_getConfig('pickpack_count', 0, false, $config_group);
        $showcount_shipped_yn = $this->_getConfig('pickpack_count_shipped', 0, false, $config_group);
        $showcount_invoiced_yn = $this->_getConfig('pickpack_count_invoiced', 0, false, $config_group);

        $order_count_yn = $this->_getConfig('order_count_yn', 1, false, $config_group);

        $showcost_yn = $this->_getConfig('pickpack_cost', 0, false, $config_group);
        $currency = $this->_getConfig('pickpack_currency', $currency_default, false, $config_group);
        $currency_symbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
        $shipping_currency = Mage::app()->getConfig()->getNode('default/' . Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        $shipping_currency_symbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        $stockcheck_yn = $this->_getConfig('pickpack_showstock_yn_combined', 0, false, $config_group);
        $stockcheck = $this->_getConfig('pickpack_stock_combined', $stockcheck_default, false, $config_group);

        $combined_sku_yn = $this->_getConfig('combined_sku_yn', 1, false, $config_group);
        $combined_sku_trim = $this->_getConfig('combined_sku_trim', 1, false, $config_group);
        $combined_total_product_paid_options = $this->_getConfig('combined_total_product_paid_options', 0, false, $config_group);
        if ($combined_total_product_paid_options != 0) {
            $col_total_paid_position_sku = explode(',', $this->_getConfig('col_total_paid_position_sku', '0,50', false, $config_group));
        }
        $shelving_yn = $this->_getConfig('pickpack_shelving_yn', 0, false, $config_group);
        $shelving_attribute = $this->_getConfig('pickpack_shelving', '', false, $config_group);

        $extra_yn = $this->_getConfig('pickpack_extra_yn', 0, false, $config_group);
        $extra_attribute = $this->_getConfig('pickpack_extra', '', false, $config_group);

        $extra3_yn = $this->_getConfig('pickpack_extra3_yn', 0, false, $config_group);
        $extra3_attribute = $this->_getConfig('pickpack_extra3', '', false, $config_group);
        $extra4_yn = $this->_getConfig('pickpack_extra4_yn', 0, false, $config_group);
        $extra4_attribute = $this->_getConfig('pickpack_extra4', '', false, $config_group);


        $combine_attribute_yn = $this->_getConfig('pickpack_combine_attribute_yn', 0, false, $config_group);

        if ($shelving_yn == 0) {
            $extra_yn = 0;
            $extra3_yn = 0;
            $extra4_yn = 0;
            $combine_attribute_yn = 0;
        }

        if ($extra_yn == 0) {
            $extra3_yn = 0;
            $extra4_yn = 0;
        }

        $stock_qty_yn = $this->_getConfig('combined_stock_qty', 0, false, $config_group);
        $nameyn = $this->_getConfig('pickpack_name_yn_combined', 0, false, $config_group);
        $trim_product_name_yn = $this->_getConfig('trim_product_name_yn', 0, false, $config_group);
        $combined_weight_yn = $this->_getConfig('combined_weight_yn', 0, false, $config_group);
        $combined_weight_rounding = $this->_getConfig('combined_weight_rounding', 2, false, $config_group);
        $combined_total_weight_yn = $this->_getConfig('combined_total_weight_yn', 0, false, $config_group);
        $combined_total_weight_rounding = $combined_weight_rounding;
        // warehouse
        $warehouseyn = $this->_getConfig('combined_warehouse_yn', 0, false, $config_group);
        $col_title_position_warehouse = explode(',', $this->_getConfig('col_title_position_warehouse', 'Qty,50', false, $config_group));

        $col_title_position_qty = explode(',', $this->_getConfig('col_title_position_qty', 'Qty,10', false, $config_group));
        $col_title_position_sku = explode(',', $this->_getConfig('col_title_position_sku', 'Sku,120', false, $config_group));
        $col_title_position_name = explode(',', $this->_getConfig('col_title_position_name', 'Name,250', false, $config_group));
        $col_title_position_weight = explode(',', $this->_getConfig('col_title_position_weight', 'Weight,200', false, $config_group));
        $col_title_position_trolleybox = explode(',', $this->_getConfig('col_title_position_trolleybox', 'Trolleybox,350', false, $config_group));
        $col_title_position_stock = explode(',', $this->_getConfig('col_title_position_stock', 'Stock,460', false, $config_group));
        $col_title_position_attr1 = explode(',', $this->_getConfig('col_title_position_attr1', 'Attr.1,380', false, $config_group));
        $col_title_position_attr2 = explode(',', $this->_getConfig('col_title_position_attr2', 'Attr.2,410', false, $config_group));
        $col_title_position_attr3 = explode(',', $this->_getConfig('col_title_position_attr3', 'Attr.3,300', false, $config_group));
        $col_title_position_attr4 = explode(',', $this->_getConfig('col_title_position_attr4', 'Attr.4,520', false, $config_group));
        $col_title_position_combine_attribute = explode(',', $this->_getConfig('col_title_position_combine_attribute', 'Combine,300', false, $config_group));

        $col_title_product_stock_qty = explode(',', $this->_getConfig('col_title_product_stock_qty', 'Stock qty,460', false, $config_group));
        $col_title_position_productsku_barcode = explode(',', $this->_getConfig('col_title_position_productsku_barcode', 'BarcodeSku,300', false, $config_group));

        $sku_barcodeX = $col_title_position_productsku_barcode[1];
        $store_view = $this->_getConfig('name_store_view', 'storeview', false, "messages");
        $specific_store_id = $this->_getConfig('specific_store', '', false, "messages", $store_id);
        $columns_xpos_array = array();

        if ($show_qty_options == 2) {
            $col_title_position_sku[1] = $col_title_position_sku[1] + 25;
        }

        if ($col_title_position_qty[0]) {
            $columns_xpos_array[$col_title_position_qty[0]] = $col_title_position_qty[1];
        }

        if ($stock_qty_yn) {
            $columns_xpos_array[$col_title_product_stock_qty[0]] = $col_title_product_stock_qty[1];
        }

        if ($warehouseyn == 1) {
            $columns_xpos_array[$col_title_position_warehouse[0]] = $col_title_position_warehouse[1];

        }
        if ($col_title_position_sku[0])
            $columns_xpos_array[$col_title_position_sku[0]] = $col_title_position_sku[1];
        if ($nameyn == 1)
            $columns_xpos_array[$col_title_position_name[0]] = $col_title_position_name[1];
        if ($combined_weight_yn == 1)
            if ($col_title_position_weight[0])
                $columns_xpos_array[$col_title_position_weight[0]] = $col_title_position_weight[1];
        if ($trolleybox_yn == 1)
            $columns_xpos_array[$col_title_position_trolleybox[0]] = $col_title_position_trolleybox[1];

        if ($stockcheck_yn && $col_title_position_stock[0]) {
            $columns_xpos_array[$col_title_position_stock[0]] = $col_title_position_stock[1];
        }
        if ($shelving_yn == 1) {
            $columns_xpos_array[$col_title_position_attr1[0]] = $col_title_position_attr1[1];
        }
        if ($extra_yn == 1) {
            $columns_xpos_array[$col_title_position_attr2[0]] = $col_title_position_attr2[1];
        }
        if ($extra3_yn == 1) {
            $columns_xpos_array[$col_title_position_attr3[0]] = $col_title_position_attr3[1];
        }
        if ($extra4_yn == 1) {
            $columns_xpos_array[$col_title_position_attr4[0]] = $col_title_position_attr4[1];
        }
        if ($product_sku_barcode_yn == 1) {
            $columns_xpos_array[$col_title_position_productsku_barcode[0]] = $col_title_position_productsku_barcode[1];
        }
        asort($columns_xpos_array);
        $configurable_names = $this->_getConfig('pickpack_configname_combined', 'simple', false, $config_group); //col/sku
        $configurable_names_attribute = trim($this->_getConfig('pickpack_configname_attribute_separated', '', false, 'picks')); //col/sku
        if ($configurable_names != 'custom')
            $configurable_names_attribute = '';

        $pickpack_options_filter_array = array();
        $pickpack_options_count_filter_array = array();

        $options_yn = $this->_getConfig('pickpack_options_yn', 0, false, $config_group); // no, inline, newline
        $hide_label_option = $this->_getConfig('hide_label_option', 0, false, "general");
        $pickpack_options_filter_yn = $this->_getConfig('pickpack_options_filter_yn', 0, false, $config_group);
        $pickpack_options_filter = $this->_getConfig('pickpack_options_filter', 0, false, $config_group);
        if ($pickpack_options_filter_yn == 0)
            $pickpack_options_filter = '';
        elseif (trim($pickpack_options_filter) != '') {
            $pickpack_options_filter_array = explode(',', $pickpack_options_filter);
            foreach ($pickpack_options_filter_array as $key => $value) {
                $pickpack_options_filter_array[$key] = trim($value);
            }
        }
        $pickpack_options_count_filter = $this->_getConfig('pickpack_options_count_filter', 0, false, $config_group);
        if ($pickpack_options_filter_yn == 0)
            $pickpack_options_count_filter = '';
        elseif (trim($pickpack_options_count_filter) != '') {
            $pickpack_options_count_filter_array = explode(',', $pickpack_options_count_filter);
            foreach ($pickpack_options_count_filter_array as $key => $value) {
                $pickpack_options_count_filter_array[$key] = trim($value);
            }
        }

        $sku_master = array();
        $sku_paid = array();
        $process_list = array();

        if ($from_shipment == 'shipment') {
            $process_list = $shipments;
        } else {
            $process_list = $orders;
        }
        $shipment_list = array();
        unset($shipment_list);

        $options_sku_array = array();
        $options_sku = array();
        $count = 0;
        $count_all = 0;
        $_count = 0;
        $i = 0;
        $total_shipping_cost = array();
        $order_count = 0;
        $productXInc = 0;
        $first_item_title_shift_sku = 0;
        $first_item_title_shift_items = 0;
        $total_item_paid = 0;
        $sku_image_paths = array();
        $flat_item_list = array();
        $trolleybox_order_id = array();
        $sku_qty_orderid = array();
        $total_weight = 0;
        $total_paid = 0;
        $sku_stock = array();

        //NEW TODO 
        $sku_qty_supplier = array();
        $sku_sqty_supplier = array();
        $sku_iqty_supplier = array();
        $sku_tqty_supplier = array();

        foreach ($process_list as $orderSingle) {
            $order_count++;

            if ($from_shipment == 'shipment') {
                unset($_items);
                unset($_item);

                $_shipment = Mage::getModel('sales/order_shipment')->load($orderSingle);
                $_items = $_shipment->getAllItems();

                $itemsCollection = $_items;

                $order = $helper->getOrder($orders[$i]);
                $order_items = Mage::helper('pickpack/order')->getItemsToProcess($order);
                $shipment_list[] = $_shipment->getIncrementId();
                $order_list[] = $order->getRealOrderId();
                if (strlen($order->getShippingAddress()->getName()) > 20) {
                    $name_list[] = substr(htmlspecialchars_decode($order->getShippingAddress()->getName()), 0, 20) . '…';
                } else
                    $name_list[] = $order->getShippingAddress()->getName();
            } else {
                $order = $helper->getOrder($orderSingle);
				$itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
            }

            $suppiler_all = Mage::helper("pickpack/config_supplier")->getAllSupplier($order, $supplier_attribute);

            if (!isset($total_shipping_cost['shipping_ex_tax']))
                $total_shipping_cost['shipping_ex_tax'] = $order->getBaseShippingAmount();
            else
                $total_shipping_cost['shipping_ex_tax'] += $order->getBaseShippingAmount();

            if (!isset($total_shipping_cost['shipping_plus_tax']))
                $total_shipping_cost['shipping_plus_tax'] = $order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount();
            else
                $total_shipping_cost['shipping_plus_tax'] += $order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount();

            $total_paid += $order->getBaseGrandTotal();
            $product_build_item = array();
            $product_build = array();
            $options_name_temp = array();
            unset($options_name_temp);
            $total_item_paid = 0;

            $coun = 1;
            foreach ($itemsCollection as $item) {
                $item_product_type = $item->getProductType();
                if($generalConfig['filter_virtual_products_yn'] == 1
                && ($item_product_type == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL
                    || $item_product_type == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE)) {
                    continue;
                }
                if (!isset($item_product_type)) {
                    $error_product_count++;
                    continue;
                }
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $sku = $item->getProductOptionByCode('simple_sku');
                } else {
                    $sku = $item->getSku();
                }
                // edit here for get full sku instead of parent sku
                if ($options_yn == 'newskuparent') {
                    $full_sku = trim($item->getSku());

                    $parent_sku = preg_replace('~\-(.*)$~', '', $full_sku);
                    $sku = $parent_sku;
                }


                $product = Mage::getModel('catalog/product')->setStoreId($store_id)->loadByAttribute('sku', $sku, array(
                    'cost',
                    'name',
                    'simple_sku',
                    'qty',
                    $sort_packing_attribute,
                    $shelving_attribute,
                    $extra_attribute,
                    $extra3_attribute,
                    $extra4_attribute,
                    $supplier_attribute
                ));

                if (is_object($product) && $item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $product_id = $product->setStoreId($store_id)->getIdBySku($sku);
                } else {
                    $product_id = $item->getProductId();
                }
                //Calculate stock for each item.
                if ($stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) {
                    $stock = round($stock);
                } else
                    $stock = 0;
                $sku_stock[$sku] = $stock;
                // unique item id
                $product_build_item[] = $sku . '-' . $coun;
                $product_build[$sku . '-' . $coun]['sku'] = $sku;
                $product_sku = $sku;

                $product_build[$sku]['sku'] = $product_sku;

                /**
                 * images PRELOADER start
                 */

                $has_shown_product_image = 0;

                if (($product_images_yn == 1) && !isset($sku_image_paths[$sku]['path'][0])) // ie only get sku image paths if not previously got in this combined request
                {
                    $imagePaths = array();
                    if ($product_images_parent_yn == 1)
                        $product_id = Mage::helper("pickpack")->getParentProId($product_id);
                    $product_images_source_res = $helper->getSourceImageRes($product_images_source, $product_id);
                    $img_demension = $helper->getWidthHeightImage($product_id, $product_images_source_res, $product_images_maxdimensions);
                    if (is_array($img_demension) && count($img_demension) > 1) {
                        $sku_image_paths[$sku]['width'] = $img_demension[0];
                        $sku_image_paths[$sku]['height'] = $img_demension[1];
                    }
                    $imagePaths = $helper->getImagePaths($product_id, $product_images_source, $product_images_maxdimensions);
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
                 * images PRELOADER end
                 */
                $shelving = '';
                $supplier = '';
                //TODO for sort first
                if ($sort_packing_attribute != null && $sort_packing_attribute != 'none') {
                    $sku_master[$sku][$sort_packing_attribute] = $this->createArraySort($sort_packing_attribute, $sku_master, $sku, $product_id, null, $product);
                }
                //TODO for sort secondary
                if ($sort_packing_secondary != 'none' && $sort_packing_secondary != '') {
                    $sku_master[$sku][$sort_packing_secondary] = $this->createArraySort($sort_packing_secondary, $sku_master, $sku, $product_id, null, $product);
                }
                if (count($order->getAllVisibleItems()) > 1)
                    $sku_master[$sku]['sort_item_count'] = 2;
                else
                    $sku_master[$sku]['sort_item_count'] = 1;

                if ($shelving_yn == 1) {
                    if ($generalConfig['non_standard_characters'] != 0) {
                        $shelving = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute, false);
                    } else $shelving = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute);
                    $shelving = trim($shelving);
                    if ($shelving != '') {
                        if (isset($sku_shelving[$sku]) && strtoupper($sku_shelving[$sku]) != strtoupper($shelving))
                            $sku_shelving[$sku] .= ',' . $shelving;
                        else
                            $sku_shelving[$sku] = $shelving;
                        $sku_shelving[$sku] = preg_replace('~,$~', '', $sku_shelving[$sku]);
                        unset($shelving);
                    }
                }
                if ($extra_yn == 1) {
                    if ($generalConfig['non_standard_characters'] != 0) {
                        $extra_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra_attribute, false);
                    } else $extra_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra_attribute);
                    $extra_var = trim($extra_var);
                    $sku_master[$sku]['extra'] = $extra_var;
                    $sku_extra[$sku] = $extra_var;
                    unset($extra_var);
                }

                if ($extra3_yn == 1) {
                    if ($generalConfig['non_standard_characters'] != 0) {
                        $extra3_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra3_attribute, false);
                    } else $extra3_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra3_attribute);
                    $extra3_var = trim($extra3_var);
                    $sku_master[$sku]['extra3'] = $extra3_var;
                    $sku_extra3[$sku] = $extra3_var;
                    unset($extra3_var);
                }
                if ($extra4_yn == 1) {
                    if ($generalConfig['non_standard_characters'] != 0) {
                        $extra4_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra4_attribute, false);
                    } else $extra4_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra4_attribute);
                    $extra4_var = trim($extra4_var);
                    $sku_master[$sku]['extra4'] = $extra4_var;
                    $sku_extra4[$sku] = $extra4_var;
                    unset($extra4_var);
                }
                if ((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse'))) {
                    if ($supplier_attribute == 'warehouse') {
                        $is_warehouse_supplier = 1;
                    }
                }

                if ($split_supplier_yn != 'no') {
                    $supplier_var = '';
                    if ($is_warehouse_supplier == 1) {
                        $warehouse_title = $item->getWarehouseTitle();
                        $warehouse = $item->getWarehouse();
                        $warehouse_code = $warehouse->getData('code');
                        $supplier = $warehouse_code;
                    } else {
                        $_newProduct = $helper->getProductForStore($product_id, $store_id);
                        if ($_newProduct) {
                            if ($_newProduct->getData($supplier_attribute)) {
                                $supplier = $_newProduct->getData('' . $supplier_attribute . '');
                            }
                        } elseif ($product->getData('' . $supplier_attribute . '')) {
                            $supplier = $product->getData($supplier_attribute);
                        }
                        if ($_newProduct->getAttributeText($supplier_attribute)) {
                            $supplier = $_newProduct->getAttributeText($supplier_attribute);
                        } elseif ($product[$supplier_attribute]) {
                            $supplier = $product[$supplier_attribute];
                        }
                    }
                    if (!$supplier) {
                        $supplier = '~Not Set~';
                    }
                    $supplier = strtoupper($supplier);

                    $sku_master[$sku]['supplier'] = $supplier;

                    if (isset($sku_supplier[$sku]) && $sku_supplier[$sku] != $supplier)
                        $sku_supplier[$sku] .= ',' . $supplier;
                    else
                        $sku_supplier[$sku] = $supplier;
                    $sku_supplier[$sku] = preg_replace('~,$~', '', $sku_supplier[$sku]);
                    if (!isset($supplier_master[$supplier]))
                        $supplier_master[$supplier] = $supplier;

                    if (isset($order_id)) {
                        if (!isset($order_id_master[$order_id]))
                            $order_id_master[$order_id] = $supplier;
                        else
                            $order_id_master[$order_id] .= ',' . $supplier;
                    }

//                     unset($supplier);
                }

                // qty in this order of this sku
                $sqty = 0;
                if ($from_shipment == 'shipment') {
                    $qty = (int)$item->getOrderItem()->getQtyShipped();
                    $sqty = $item->getIsQtyDecimal() ? $item->getQty() : (int)$item->getQty();

                    $qty_shipped_now = (int)$item->getQty();
                    $qty_shipped_total = (int)$item->getOrderItem()->getQtyShipped();
                    $qty_ordered = (int)$order_items[($coun - 1)]['qty_ordered'];

                    $count = ($count + $qty_shipped_now);
                    $count_all = ($count_all + $qty_shipped_total);
                    $tqty = $qty_shipped_total;
                    $sqty = $qty_shipped_now;
                    $qty = $qty_ordered;
                } else {
                    $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int)$item->getQtyOrdered();
                    //get qty shipped from item
                    $sqty = (int)$item->getQtyShipped();
                    //get qty invoiced from item
                    $iqty = (int)$item->getData('qty_invoiced');
                }

                if (!isset($order_id))
                    $order_id = $order->getRealOrderId();
                $sku_qty_orderid[$sku][$order_id] = $qty;

                // total qty in all orders for this sku
                if (isset($sku_qty[$sku])) {
                    $sku_qty[$sku] = ($sku_qty[$sku] + $qty);
                    $sku_sqty[$sku] = ($sku_sqty[$sku] + $sqty);

                    ////set qty invoiced/////
                    $sku_iqty[$sku] = ($sku_iqty[$sku] + $iqty);

                    if (isset($tqty))
                        $sku_tqty[$sku] = ($sku_tqty[$sku] + $tqty);
                    else
                        $sku_tqty[$sku] = 0;
                    $sku_sku[$sku] = $product_sku;
                    $sku_master[$sku]['qty'] += $qty;
                } else {
                    $sku_qty[$sku] = $qty;
                    $sku_sqty[$sku] = $sqty;
                    ////set qty invoiced/////
                    $sku_iqty[$sku] = $iqty;

                    if (isset($tqty))
                        $sku_tqty[$sku] = $tqty;
                    else
                        $sku_tqty[$sku] = 0;
                    $sku_sku[$sku] = $product_sku;
                    $sku_master[$sku]['qty'] = $qty;
                }
                $sku_master[$sku]['product_id'] = $product_id;
                //New TODO
                if ($split_supplier_yn != 'no') {
                    if (isset($sku_qty_supplier[$supplier][$sku])) {
                        $sku_qty_supplier[$supplier][$sku] = ($sku_qty_supplier[$supplier][$sku] + $qty);
                        $sku_sqty_supplier[$supplier][$sku] = ($sku_sqty_supplier[$supplier][$sku] + $sqty);

                        ////set qty invoiced/////
                        $sku_iqty_supplier[$supplier][$sku] = ($sku_iqty_supplier[$supplier][$sku] + $iqty);

                        if (isset($tqty))
                            $sku_tqty_supplier[$supplier] = ($sku_tqty_supplier[$supplier] + $tqty);
                        else
                            $sku_tqty_supplier[$supplier] = 0;
                    } else {
                        $sku_qty_supplier[$supplier][$sku] = $qty;
                        $sku_sqty_supplier[$supplier][$sku] = $sqty;
                        ////set qty invoiced/////
                        $sku_iqty_supplier[$supplier][$sku] = $iqty;

                        if (isset($tqty))
                            $sku_tqty_supplier[$supplier] = ($tqty);
                        else
                            $sku_tqty_supplier[$supplier] = 0;
                    }
                }
                //weight
                $unit_weight = '';
                if (($combined_weight_yn == 1) || ($combined_total_weight_yn == 1)) {
                    $unit_weight = $item->getWeight();
                    $unit_weight = round($unit_weight, $combined_weight_rounding);
                    $total_weight += ($unit_weight * $qty);
                }
                $sku_master[$sku]['weight'] = $unit_weight;
                $sku_weight[$sku] = $unit_weight;
                $unit_weight = null;

                $unit_paid = 0;
                $qty_invoiced = 0;
                $paid_invoiced = 0;

                if ($combined_total_product_paid_options != 0) {
                    switch ($combined_total_product_paid_options) {
                        case 1:
                            $unit_paid = $item->getData('price');
                            $total_item_paid = ($unit_paid * $qty);
                            break;
                        case 2:
                            $unit_paid = $item->getData('price_incl_tax');
                            $total_item_paid = ($unit_paid * $qty);
                            break;
                        case 3:
                            $unit_paid = $item->getData('price_incl_tax');
                            $qty_invoiced = $item->getData('qty_invoiced');
                            $paid_invoiced = $item->getData('row_invoiced');
                            $total_item_paid = $paid_invoiced; //Just show avarage price. Price include tax of one product.
                            break;
                        default:
                            $unit_paid = $item->getData('price');
                            $total_item_paid = ($unit_paid * $qty);
                            break;

                    }
                }

                $sku_master[$sku]['paid'] = $unit_paid;

                if (isset($sku_master[$sku]['paid_invoiced'])) {
                    $sku_master[$sku]['paid_invoiced'] += $total_item_paid;
                } else {
                    $sku_master[$sku]['paid_invoiced'] = $total_item_paid;
                }

                if (isset($sku_master[$sku]['qty_invoiced'])) {
                    $sku_master[$sku]['qty_invoiced'] += $qty_invoiced;
                } else {
                    $sku_master[$sku]['qty_invoiced'] = $qty_invoiced;
                }

                $sku_paid[$sku] = $unit_paid;

                if (!isset($max_qty_length))
                    $max_qty_length = 2;
                if (strlen($sku_qty[$sku]) > $max_qty_length)
                    $max_qty_length = strlen($sku_qty[$sku]);
                if (strlen($sku_sqty[$sku]) > $max_qty_length)
                    $max_qty_length = strlen($sku_sqty[$sku]);

                $options = $item->getProductOptions();
                if (isset($options['options']) && is_array($options['options'])) {
                    // note : prepare product options

                    if ($options_yn == 'newline' || $options_yn == 'newskuparent') {
                        $j = 0;
                        $continue = 0;
                        if (isset($options['options'][$j]))
                            $continue = 1;

                        while ($continue == 1) {
                            if (!isset($sku_order_id_options[$sku]))
                                $sku_order_id_options[$sku] = '';
                            if ($j > 0)
                                $sku_order_id_options[$sku] .= ' ';
                            $options_store = $this->getOptionProductByStore($store_view, $helper, $product_id, $store_id, $specific_store_id, $options, $j);
                            $options['options'][$j]['label'] = $options_store["label"];
                            $options['options'][$j]['value'] = $options_store["value"];
                            if ($options_yn == 'inline' || $options_yn == 'newline' || $options_yn == 'newskuparent')
                                if ($hide_label_option == 0)
                                    $sku_order_id_options[$sku] .= htmlspecialchars_decode('[ ' . $options['options'][$j]['label'] . ' : ' . $options['options'][$j]['value'] . ' ]');
                                else
                                    $sku_order_id_options[$sku] .= htmlspecialchars_decode('[ ' . $options['options'][$j]['value'] . ' ]');

                            if ($options_yn == 'newsku' || $options_yn == 'newskuparent')
                                if ($hide_label_option == 0)
                                    $options_name_temp[] = htmlspecialchars_decode('[ ' . $options['options'][$j]['label'] . ' : ' . $options['options'][$j]['value'] . ' ]');
                                else
                                    $options_name_temp[] = htmlspecialchars_decode('[ ' . $options['options'][$j]['value'] . ' ]');
                            $j++;
                            $continue = 0;
                            if (isset($options['options'][$j]))
                                $continue = 1;
                        }
                        $temp_newline = $sku_order_id_options[$sku];
                        if ($options_yn == 'newskuparent')
                            $sku_order_id_options[$sku] = $temp_newline . 'qty_ordered ' . ceil($item->getData('qty_ordered')) . ' newline';
                        else
                            $sku_order_id_options[$sku] = $temp_newline . 'qty_ordered ' . ceil($item->getData('qty_ordered')) . ' x ' . 'newline';
                    } else {
                        $j = 0;
                        $continue = 0;
                        if (isset($options['options'][$j]))
                            $continue = 1;

                        while ($continue == 1) {
                            if (!isset($sku_order_id_options[$sku]))
                                $sku_order_id_options[$sku] = '';
                            if ($j > 0)
                                $sku_order_id_options[$sku] .= ' ';
                            $options_store = $this->getOptionProductByStore($store_view, $helper, $product_id, $store_id, $specific_store_id, $options, $j);
                            $options['options'][$j]['label'] = $options_store["label"];
                            $options['options'][$j]['value'] = $options_store["value"];
                            if ($options_yn == 'inline' || $options_yn == 'newline')
                                $sku_order_id_options[$sku] .= htmlspecialchars_decode('[ ' . $options['options'][$j]['label'] . ' : ' . $options['options'][$j]['value'] . ' ]');
                            if ($options_yn == 'newsku' || $options_yn == 'newskuparent')
                                $options_name_temp[] = htmlspecialchars_decode('[ ' . $options['options'][$j]['label'] . ' : ' . $options['options'][$j]['value'] . ' ]');
                            $j++;
                            $continue = 0;
                            if (isset($options['options'][$j]))
                                $continue = 1;
                        }
                    }

                    if ($options_yn == 'newsku' || $options_yn == 'newskuparent') {
                        $full_sku = trim($item->getSku());
                        $parent_sku = preg_replace('~\-(.*)$~', '', $full_sku);
                        $full_sku = preg_replace('~^' . $parent_sku . '\-~', '', $full_sku);

                        $options_sku_array = explode('-', $full_sku);

                        $opt_count = 0;
                        foreach ($options_sku_array as $k => $options_sku_single) {
                            if (isset($options_sku[$options_sku_single]) && (!in_array($options_sku_single, $pickpack_options_filter_array))) {
                                $options_sku[$options_sku_single] = ($sku_qty[$sku] + $options_sku[$options_sku_single]);
                                $options_sku_parent[$sku][$options_sku_single] = ($qty + $options_sku_parent[$sku][$options_sku_single]);
                                if ($options_name_temp[$opt_count])
                                    $sku_name[$options_sku_single] = $options_name_temp[$opt_count];
                            } elseif (!in_array($options_sku_single, $pickpack_options_filter_array)) {
                                $options_sku[$options_sku_single] = ($sku_qty[$sku]);
                                $options_sku_parent[$sku][$options_sku_single] = $qty;
                                if ($options_name_temp[$opt_count])
                                    $sku_name[$options_sku_single] = $options_name_temp[$opt_count];
                            }
                            $opt_count++;
                        }
                        unset($options_name_temp);
                    }
                }

                $sku_cost[$sku] = (is_object($product) ? $product->getCost() : 0);
                $sku_master[$sku]['sku'] = $sku;
                $store_view = $this->_getConfig('name_store_view', 'storeview', false, "messages", $store_id);
                $specific_store_id = $this->_getConfig('specific_store', '', false, 'messages', $store_id);

                if (is_object($product) && isset($configurable_names) && ($configurable_names == 'simple')) {

                    switch ($store_view) {
                        case 'itemname':
                            $name = trim($item->getName());
                            break;
                        case 'default':
                            $_newProduct = $helper->getProduct($product_id);
                            if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                            if ($name == '') $name = trim($item->getName());
                            break;
                        case 'storeview':
                            $_newProduct = $helper->getProductForStore($product_id, $store_id);
                            if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                            if ($name == '') $name = trim($item->getName());
                            break;
                        case 'specificstore':
                            $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
                            if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                            if ($name == '') $name = trim($item->getName());
                            break;
                        default:
                            $_newProduct = $helper->getProduct($product_id);
                            if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                            if ($name == '') $name = trim($item->getName());
                            break;
                    }

                } else {
                    if ($store_view == "storeview")
                        $name = trim($item->getName());
                    elseif ($store_view == "specificstore" && $specific_store_id != "") {
                        $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
                        if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                        if ($sku_name[$sku] == '') $name = trim($item->getName());

                    } else
                        $name = $this->getNameDefaultStore($item);
                }
                $sku_name[$sku] = $name;
                $sku_type[$sku] = 'normal';

                $sku_master[$sku]['sku'] = $sku;
                $sku_master[$sku]['name'] = $sku_name[$sku];
                $sku_master[$sku]['product_type'] = 'normal';
                $sku_master[$sku]['index'] = '2';
                if (Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) {
                    $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
                } else
                    $stock = 0;
                $sku_master[$sku]['stock_qty'] = $stock; //$item->getProduct()->getData('stock_item')->getData('qty');

                // bundle SKUs
                //$options = $item->getProductOptions();
                if (isset($options['bundle_options']) && is_array($options['bundle_options'])) {
                    $sku_master[$sku]['product_type'] = 'bundle-parent';
                    $children = $item->getChildrenItems();
                    if (count($children) > 0) {
                        foreach ($children as $child) {
                            $child_product_id = $child->getProductId();
                            $sku_b = $child->getSku();
                            $price_b = $child->getPriceInclTax();
                            $qty_b = (int)$child->getQtyOrdered();
                            $sqty_b = (int)$child->getQtyShipped();
                            $iqty_b = (int)$child->getData('qty_invoiced');
                            $name_b = $child->getName();

                            //weight
                            $unit_weight = '';
                            if (($combined_weight_yn == 1) || ($combined_total_weight_yn == 1)) {
                                $unit_weight = $child->getWeight();
                                $unit_weight = round($unit_weight, $combined_weight_rounding);
                                $total_weight += ($unit_weight * $qty_b);
                            }
                            $sku_master[$sku_b]['weight'] = $unit_weight;
                            $sku_weight[$sku_b] = $unit_weight;
                            $unit_weight = null;

                            $unit_paid = 0;
                            $qty_invoiced = 0;
                            $paid_invoiced = 0;
                            if ($combined_total_product_paid_options != 0) {
                                switch ($combined_total_product_paid_options) {
                                    case 1:
                                        $unit_paid = $child->getData('price');
                                        $total_item_paid = ($unit_paid * $qty_b);
                                        break;
                                    case 2:
                                        $unit_paid = $child->getData('price_incl_tax');
                                        $total_item_paid = ($unit_paid * $qty_b);
                                        break;
                                    case 3:
                                        $unit_paid = $child->getData('price_incl_tax');
                                        $qty_invoiced = $child->getData('qty_invoiced');
                                        $paid_invoiced = $child->getData('row_invoiced');
                                        $total_item_paid = $paid_invoiced; //Just show avarage price. Price include tax of one product.
                                        break;
                                    default:
                                        $unit_paid = $child->getData('price');
                                        $total_item_paid = ($unit_paid * $qty_b);
                                        break;

                                }
                            }
                            $sku_master[$sku_b]['paid'] = $total_item_paid;
                            $sku_paid[$sku_b] = $unit_paid;

                            $product = Mage::getModel('catalog/product')->setStoreId($store_id)->loadByAttribute('sku', $sku_b);
                            if ($shelving_yn == 1) {
                                if ($generalConfig['non_standard_characters'] != 0) {
                                    $shelving = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute, false);
                                } else $shelving = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute);
                                $shelving = trim($shelving);
                                if ($shelving != '') {
                                    if (isset($sku_shelving[$sku_b]) && strtoupper($sku_shelving[$sku_b]) != strtoupper($shelving))
                                        $sku_shelving[$sku_b] .= ',' . $shelving;
                                    else
                                        $sku_shelving[$sku_b] = $shelving;
                                    $sku_shelving[$sku_b] = preg_replace('~,$~', '', $sku_shelving[$sku_b]);
                                    unset($shelving);
                                }
                            }

                            if ($extra_yn == 1) {
                                if ($generalConfig['non_standard_characters'] != 0) {
                                    $extra_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra_attribute, false);
                                } else $extra_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra_attribute);
                                $extra_var = trim($extra_var);
                                $sku_master[$sku_b]['extra'] = $extra_var;
                                $sku_extra[$sku_b] = $extra_var;
                                unset($extra_var);
                            }

                            if ($extra3_yn == 1) {
                                if ($generalConfig['non_standard_characters'] != 0) {
                                    $extra3_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra3_attribute, false);
                                } else $extra3_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra3_attribute);
                                $extra3_var = trim($extra3_var);
                                $sku_master[$sku_b]['extra3'] = $extra3_var;
                                $sku_extra3[$sku_b] = $extra3_var;
                                unset($extra3_var);
                            }

                            if ($extra4_yn == 1) {
                                if ($generalConfig['non_standard_characters'] != 0) {
                                    $extra4_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra4_attribute, false);
                                } else $extra4_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra4_attribute);
                                $extra4_var = trim($extra4_var);
                                $sku_master[$sku_b]['extra4'] = $extra4_var;
                                $sku_extra4[$sku_b] = $extra4_var;
                                unset($extra4_var);
                            }

                            if ($split_supplier_yn != 'no') {

                                $supplier_var = '';

                                //NEW TODO
                                if ($is_warehouse_supplier == 1) {
                                    $warehouse_title = $child->getWarehouseTitle();
                                    $warehouse = $child->getWarehouse();
                                    $warehouse_code = $warehouse->getData('code');
                                    $supplier = $warehouse_code;
                                } else {
                                    $_newProduct = $helper->getProductForStore($child_product_id, $store_id);
                                    if ($_newProduct) {
                                        if ($_newProduct->getData($supplier_attribute)) {
                                            $supplier = $_newProduct->getData('' . $supplier_attribute . '');
                                        }
                                    } elseif ($product->getData('' . $supplier_attribute . '')) {
                                        $supplier = $product->getData($supplier_attribute);
                                    }
                                    if ($_newProduct->getAttributeText($supplier_attribute)) {
                                        $supplier = $_newProduct->getAttributeText($supplier_attribute);
                                    } elseif ($product[$supplier_attribute]) {
                                        $supplier = $product[$supplier_attribute];
                                    }
                                }
                                if (!isset($supplier) || !$supplier) {
                                    $supplier = '~Not Set~';
                                }
                                $supplier = strtoupper($supplier);
                                if ($split_supplier_yn != 'no') {
                                    if (isset($sku_qty_supplier[$supplier][$sku_b])) {
                                        $sku_qty_supplier[$supplier][$sku_b] = ($sku_qty_supplier[$supplier][$sku_b] + $qty_b);
                                        $sku_sqty_supplier[$supplier][$sku_b] = ($sku_sqty_supplier[$supplier][$sku_b] + $sqty_b);

                                        ////set qty invoiced/////
                                        $sku_iqty_supplier[$supplier][$sku_b] = ($sku_iqty_supplier[$supplier][$sku_b] + $iqty_b);

                                        if (isset($tqty))
                                            $sku_tqty_supplier[$supplier] = ($sku_tqty_supplier[$supplier] + $tqty);
                                        else
                                            $sku_tqty_supplier[$supplier] = 0;
                                    } else {
                                        $sku_qty_supplier[$supplier][$sku_b] = $qty_b;
                                        $sku_sqty_supplier[$supplier][$sku_b] = $sqty_b;
                                        $sku_iqty_supplier[$supplier][$sku_b] = $iqty_b;

                                        if (isset($tqty))
                                            $sku_tqty_supplier[$supplier] = ($tqty);
                                        else
                                            $sku_tqty_supplier[$supplier] = 0;
                                    }
                                }
                                $sku_master[$sku_b]['supplier'] = $supplier;
                                $sku_supplier[$sku_b] = $supplier;
                                unset($supplier_var);

                                if (isset($sku_supplier[$sku_b]) && $sku_supplier[$sku_b] != $supplier)
                                    $sku_supplier[$sku_b] .= ',' . $supplier;
                                else
                                    $sku_supplier[$sku_b] = $supplier;
                                $sku_supplier[$sku_b] = preg_replace('~,$~', '', $sku_supplier[$sku_b]);
                                if (!isset($supplier_master[$supplier]))
                                    $supplier_master[$supplier] = $supplier;

                                if (isset($order_id)) {
                                    if (!isset($order_id_master[$order_id]))
                                        $order_id_master[$order_id] = $supplier;
                                    else
                                        $order_id_master[$order_id] .= ',' . $supplier;
                                }

                                unset($supplier);
                            }
                            ////
                            if (isset($sku_master[$sku_b]['paid_invoiced'])) {
                                $sku_master[$sku_b]['paid_invoiced'] += $total_item_paid;
                            } else {
                                $sku_master[$sku_b]['paid_invoiced'] = $total_item_paid;
                            }

                            if (isset($sku_master[$sku_b]['qty_invoiced'])) {
                                $sku_master[$sku_b]['qty_invoiced'] += $qty_invoiced;
                            } else {
                                $sku_master[$sku_b]['qty_invoiced'] = $qty_invoiced;
                            }
                            if (Mage::getModel('cataloginventory/stock_item')->loadByProduct($child_product_id)->getQty()) {
                                $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($child_product_id)->getQty());
                            } else
                                $stock = 0;
                            $sku_master[$sku_b]['stock_qty'] = $stock; //$child->getProduct()->getData('stock_item')->getData('qty');
                            $this->y -= 10;
                            $offset = 20;

                            $shelving_real = '';
                            $shelving_real_b = '';
                            if (isset($shelving_real_yn) && $shelving_real_yn == 1) {
                                $shelving_real_b = Mage::getResourceModel('catalog/product')->getAttributeRawValue($child_product_id, $shelving_real_attribute, $store_id);
                                /*
                                if($child_product->getData($shelving_real_attribute))
                                {
                                $shelving_real_b = $child_product->getData($shelving_real_attribute);
                                }
                                elseif($child_product->setStoreId($store_id)->load($child->getProductId())->getAttributeText($shelving_real_attribute))
                                {
                                $shelving_real_b = $child_product->setStoreId($store_id)->load($child->getProductId())->getAttributeText($shelving_real_attribute);
                                }
                                elseif($child_product[$shelving_real_attribute]) $shelving_real_b = $child_product[$shelving_real_attribute];
                                else $shelving_real_b = '';*/

                                if (is_array($shelving_real))
                                    $shelving_real_b = implode(',', $shelving_real_b);
                                if (isset($shelving_real_b))
                                    $shelving_real_b = trim($shelving_real_b);
                            }

                            $sort_packing_value = '';
                            if ($sort_packing_attribute != null) {
                                if ($sort_packing_value == '') {
                                    $sort_packing_value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($child_product_id, $sort_packing_attribute, $store_id);
                                }

                                if (is_array($sort_packing_value)) {
                                    $sort_packing_value = implode(',', $sort_packing_value);
                                    $sort_packing_value = preg_replace('~^,~', '', $sort_packing_value);
                                }
                                $sort_packing_value = trim($sort_packing_value);
                                $sku_master[$sku_b][$sort_packing_attribute] = $sort_packing_value;

                                unset($sort_packing_value);
                            }

                            if ($from_shipment == 'shipment') {
                                $qty_string_b = 's:' . (int)$item->getQtyShipped() . ' / o:' . $qty;
                                $price_qty_b = (int)$item->getQtyShipped();
                                $productXInc = 25;
                            } else {
                                $qty_string_b = $qty_b;
                                $price_qty_b = (int)$item->getQtyShipped();
                                $invoiced_qty_b = (int)$item->getData('qty_invoiced');
                                $productXInc = 0;
                            }

                            $display_name_b = '';

                            $max_flat_name_width = ($padded_right);
                            $character_breakpoint_name = stringBreak($name_b, $max_flat_name_width, $generalConfig['font_size_body'], $generalConfig['font_style_body']);

                            if (strlen($name_b) > ($character_breakpoint_name + 2)) {
                                $display_name_b = substr(htmlspecialchars_decode($name_b), 0, ($character_breakpoint_name)) . '…';
                            } else
                                $display_name_b = htmlspecialchars_decode($name_b);

                            $sku_cost[$sku_b] = (is_object($child) ? $child->getCost() : 0);
                            $sku_name[$sku_b] = $name_b;

                            if (isset($sku_qty[$sku_b]) && ($sku_qty[$sku_b] > 0)) {
                                $sku_qty[$sku_b] = ($sku_qty[$sku_b] + $qty_b);
                                $sku_master[$sku_b]['qty'] += $qty_b;
                            } else {
                                $sku_qty[$sku_b] = $qty_b;
                                $sku_master[$sku_b]['qty'] = $qty_b;
                            }
                            if (isset($sku_sqty[$sku_b]) && ($sku_sqty[$sku_b] > 0)) {
                                $sku_sqty[$sku_b] = ($sku_sqty[$sku_b] + $price_qty_b);
                                $sku_iqty[$sku_b] = ($sku_iqty[$sku_b] + $invoiced_qty_b);
                            } else {
                                $sku_sqty[$sku_b] = $price_qty_b;
                                $sku_iqty[$sku_b] = $invoiced_qty_b;
                            }
                            $sku_sku[$sku_b] = $sku_b;
                            $sku_type[$sku_b] = 'bundle';
                            $sku_master[$sku_b]['sku'] = $sku_b;
                            $sku_master[$sku_b]['name'] = $name_b;
                            $sku_master[$sku_b]['product_type'] = 'bundle-child';
                            $sku_master[$sku_b]['index'] = '4';

                            /**
                             * Bundled Trolley
                             */
                            if ($output == 'trolleybox') {
                                $trolleybox_this_order_id = $order->getRealOrderId();

                                if (!isset($trolleybox_item_id))
                                    $trolleybox_item_id = 0;
                                else
                                    $trolleybox_item_id++;

                                if (!isset($trolleybox_order_id[$trolleybox_this_order_id])) {
                                    $trolleybox_order_id[$trolleybox_this_order_id] = true;

                                    if (!isset($trolleybox_trolley_id))
                                        $trolleybox_trolley_id = 1;

                                    if (!isset($trolleybox_box_id)) {
                                        $trolleybox_box_id = 1;
                                    } else {
                                        $trolleybox_box_id++;
                                        if ($trolleybox_box_id > $trolleybox_max) {
                                            $trolleybox_trolley_id++;
                                            $trolleybox_box_id = 1;
                                        }
                                    }
                                }

                                $flat_item_list[$trolleybox_item_id]['sku'] = $sku_b;
                                $flat_item_list[$trolleybox_item_id]['name'] = $name_b;
                                $flat_item_list[$trolleybox_item_id]['order_id'] = $trolleybox_this_order_id;
                                $flat_item_list[$trolleybox_item_id]['trolleybox_box_id'] = $trolleybox_box_id;
                                $flat_item_list[$trolleybox_item_id]['trolleybox_trolley_id'] = $trolleybox_trolley_id;
                                $flat_item_list[$trolleybox_item_id]['sort'] = $sku_master[$sku_b]['sort'];
                                $flat_item_list[$trolleybox_item_id]['sort_item_count'] = $sku_master[$sku]['sort_item_count'];
                                $flat_item_list[$trolleybox_item_id]['product_type'] = 'bundle-child';
                                if ($sku_qty[$sku] > 1) {
                                    $trolleybox_qty_cycle = 1;
                                    while ($trolleybox_qty_cycle < $qty_b) {
                                        $trolleybox_item_id++;
                                        $trolleybox_qty_cycle++;
                                        $flat_item_list[$trolleybox_item_id]['sku'] = $sku_b;
                                        $flat_item_list[$trolleybox_item_id]['name'] = $name_b;
                                        $flat_item_list[$trolleybox_item_id]['order_id'] = $trolleybox_this_order_id;
                                        $flat_item_list[$trolleybox_item_id]['trolleybox_box_id'] = $trolleybox_box_id;
                                        $flat_item_list[$trolleybox_item_id]['trolleybox_trolley_id'] = $trolleybox_trolley_id;
                                        $flat_item_list[$trolleybox_item_id]['sort'] = $sku_master[$sku_b]['sort'];
                                        $flat_item_list[$trolleybox_item_id]['sort_item_count'] = $sku_master[$sku]['sort_item_count'];
                                        $flat_item_list[$trolleybox_item_id]['product_type'] = 'bundle-child';
                                    }
                                }
                                $qty_b = 0;
                                unset($sort_packing_value);
                            }
                            /**
                             * END bundled trolley
                             */
                        }
                    }
                }

                if ($output == 'trolleybox') {
                    $trolleybox_this_order_id = $order->getRealOrderId();


                    if (!isset($trolleybox_item_id))
                        $trolleybox_item_id = 0;
                    else
                        $trolleybox_item_id++;


                    if (!isset($trolleybox_order_id[$trolleybox_this_order_id])) {
                        $trolleybox_order_id[$trolleybox_this_order_id] = true;

                        if (!isset($trolleybox_trolley_id)) {
                            $trolleybox_trolley_id = 1;
                            $oneortwoprevious = $sku_master[$sku]['sort_item_count'];
                            $sku_master[$sku]['flag_trolley'] = 0;
                        }

                        if ($sku_master[$sku]['sort_item_count'] != $oneortwoprevious) {
                            $flag_trolley_id = $trolleybox_trolley_id;
                            $oneortwoprevious = $sku_master[$sku]['sort_item_count'];

                        }


                        if (!isset($trolleybox_box_id)) {
                            $trolleybox_box_id = 1;

                        } else {
                            $trolleybox_box_id++;
                            $sku_master[$sku]['flag_trolley'] = 0;
                            if ($trolleybox_box_id > $trolleybox_max) {

                                $trolleybox_trolley_id++;
                                $trolleybox_box_id = 1;

                            }
                        }
                    }

                    $flat_item_list[$trolleybox_item_id]['sku'] = $sku;
                    $flat_item_list[$trolleybox_item_id]['name'] = $sku_name[$sku];
                    $flat_item_list[$trolleybox_item_id]['order_id'] = $trolleybox_this_order_id;
                    $flat_item_list[$trolleybox_item_id]['trolleybox_box_id'] = $trolleybox_box_id;
                    $flat_item_list[$trolleybox_item_id]['trolleybox_trolley_id'] = $trolleybox_trolley_id;
                    $flat_item_list[$trolleybox_item_id]['sort'] = $sku_master[$sku]['sort'];
                    $flat_item_list[$trolleybox_item_id]['sort_item_count'] = $sku_master[$sku]['sort_item_count'];
                    $flat_item_list[$trolleybox_item_id]['product_type'] = $sku_type[$sku];
                }

                $coun++;
            }
            $i++;
        }
        if ($options_yn == 'newsku') {
            foreach ($options_sku as $sku_option_key => $sku_option_value) {
                $sku_master['sku'][$sku_option_key] = $sku_option_key;
                $qty = $sku_option_value;
                $sqty = $sku_option_value;

                if (isset($sku_qty[$sku_option_key])) {
                    $sku_qty[$sku_option_key] = ($sku_qty[$sku_option_key] + $qty);
                    $sku_sqty[$sku_option_key] = ($sku_sqty[$sku_option_key] + $sqty);
                    if (isset($tqty))
                        $sku_tqty[$sku_option_key] = ($sku_tqty[$sku_option_key] + $tqty);
                    else
                        $sku_tqty[$sku_option_key] = 0;
                    $sku_sku[$sku_option_key] = $sku_option_key;
                } else {
                    $sku_qty[$sku_option_key] = $qty;
                    $sku_sqty[$sku_option_key] = $sqty;
                    if (isset($tqty))
                        $sku_tqty[$sku_option_key] = $tqty;
                    else
                        $sku_tqty[$sku_option_key] = 0;
                    $sku_sku[$sku_option_key] = $sku_option_key;
                }
            }
        }

        $sortorder_packing_bool = false;

        $supplier_previous = '';
        $supplier_item_action = '';
        $first_page_yn = 'y';
        if ($output == 'trolleybox') {
            $sku_master = $flat_item_list;
        }

        // sort items and loop
        if (($sort_packing != 'none') && ($output != 'trolleybox')) {

            if ($sortorder_packing == 'ascending')
                $sortorder_packing_bool = true;
            if ($sort_packing_secondary == 'none' || $sort_packing_secondary == '')
                sksort($sku_master, $sort_packing_attribute, $sortorder_packing_bool);
            else {
                $sortorder_packing_secondary_bool = false;
                if ($sortorder_packing_secondary == 'ascending') $sortorder_packing_secondary_bool = true;
                $this->sortMultiDimensional($sku_master, $sort_packing_attribute, $sort_packing_secondary, $sortorder_packing_bool, $sortorder_packing_secondary_bool);
            }
        } elseif (($sort_packing != 'none') && ($output == 'trolleybox')) {
            // Obtain a list of columns
            foreach ($sku_master as $key => $row) {
                $trolley_id[$key] = $row['trolleybox_trolley_id'];
                $sort[$key] = $row['sort'];
                if ($row['sort_item_count'] > 1)
                    $order_type[$key] = 2;
                else
                    $order_type[$key] = 1;
            }

            if (version_compare(phpversion(), '5.4', '>=')) {
                // Add $data as the last parameter, to sort by the common key
                // array_multisort($trolley_id, SORT_ASC, SORT_REGULAR, $sort, SORT_ASC, SORT_NATURAL, $sku_master); // php>5.4 can use SORT_NATURAL
                array_multisort($order_type, SORT_ASC, SORT_REGULAR, $sort, SORT_ASC, SORT_NATURAL, $sku_master); // php>5.4 can use SORT_NATURAL
            } else {
                array_multisort($order_type, SORT_ASC, SORT_REGULAR, $sort, SORT_ASC, SORT_REGULAR, $sku_master);
            }
        }

        if ((isset($flag_trolley_id)) && ($flag_trolley_id != 0)) {
            $sub_troylley_id_arr = array();
            foreach ($sku_master as $k => $v) {
                if ($v['trolleybox_trolley_id'] == $flag_trolley_id) {
                    $sub_troylley_id_arr[$k] = $v;
                    unset($sku_master[$k]);
                    $sub_trolley_sort[$k] = $v['sort'];
                    if (!isset($start_to_merg))
                        $start_to_merg = $k;
                }
            }

            if (version_compare(phpversion(), '5.4', '>=')) {
                // Add $data as the last parameter, to sort by the common key
                // array_multisort($trolley_id, SORT_ASC, SORT_REGULAR, $sort, SORT_ASC, SORT_NATURAL, $sku_master); // php>5.4 can use SORT_NATURAL
                array_multisort($sub_trolley_sort, SORT_ASC, SORT_NATURAL, $sub_troylley_id_arr); // php>5.4 can use SORT_NATURAL

            } else {
                //usort( $sku_master, function( $el1, $el2) { return strnatcmp( $el1['sort'], $el2['sort']); });
                // array_multisort($trolley_id, SORT_ASC, SORT_REGULAR, $sort, SORT_ASC, SORT_REGULAR, $sku_master);
                array_multisort($sub_trolley_sort, SORT_ASC, SORT_REGULAR, $sub_troylley_id_arr);
            }


            if (isset($start_to_merg)) {
                foreach ($sub_troylley_id_arr as $k2 => $v2) {
                    $sku_master[$start_to_merg] = $v2;
                    $start_to_merg++;
                }

                ksort($sku_master);
            }
        }


        if ($split_supplier_yn != 'no') {

            foreach ($supplier_master as $key => $supplier) {
                if ((isset($supplier_login) && ($supplier_login != '') && (strtoupper($supplier) == strtoupper($supplier_login))) || !isset($supplier_login) || $supplier_login == '') {

                    // new page
                    $total_quantity = 0;
                    $total_cost = 0;
                    $total_bundle_quantity = 0;
                    $sheet_has_bundles = false;
                    if ($first_page_yn == 'n')
                        $page = $this->newPage();
                    else
                        $first_page_yn = 'n';

                    $this->y = ($page_top - 43);

                    if ($picklogo == 1) {
                        $packlogo = Mage::getStoreConfig('pickpack_options/wonder/pack_logo', $order->getStore()->getId());
                        if ($packlogo) {
                            $packlogo = Mage::getBaseDir('media') . '/moogento/pickpack/logo_pack/' . $packlogo;
                            $image_ext = '';
                            $image_part = explode('.', $packlogo);
                            $image_ext = array_pop($image_part);
                            if ((($image_ext == 'jpg') || ($image_ext == 'JPG') || ($image_ext == 'jpeg') || ($image_ext == 'png') || ($image_ext == 'PNG')) && (is_file($packlogo))) {
                                $packlogo = Zend_Pdf_Image::imageWithPath($packlogo);
                                $page->drawImage($packlogo, 27, 784, 296, 825);
                            }
                        }
						
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                        $picklist_title = '';

                        if ($output == 'trolleybox') {
                            $picklist_title .= $helper->__('Trolleybox Pick List');
                        } else {
                            if ($from_shipment == 'shipment') {
                                $picklist_title .= $helper->__('Shipment-combined Pick List');
                            } else {
                                $picklist_title .= $helper->__('Order-combined Pick List');
                            }
                        }
                        $page->drawText($picklist_title, 325, (784 + (825-784-$generalConfig['font_size_subtitles'])), 'UTF-8');

                        $page->drawText(Mage::helper('sales')->__($supplier), 325, 790, 'UTF-8');
                        $page->setFillColor($background_color_header_zend);
                        $page->setLineColor($background_color_header_zend);
                        $page->setLineWidth(0.5);
                        if($generalConfig['line_width_company'] > 0)
							$page->drawRectangle(304, 784, (304 + $generalConfig['line_width_company']), 825);
                        $page->drawRectangle(27, $this->y, $padded_right, ($this->y - 0.5));
                        $this->y -= 20;
                    } else {
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] + 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                        if ($output == 'trolleybox')
                            $picklist_title .= $helper->__('Trolleybox Pick List');
                        else {
                            if ($from_shipment == 'shipment')
                                $page->drawText($helper->__('Shipment-combined Pick List') . ', ' . $supplier, 31, 810, 'UTF-8');
                            else
                                $page->drawText($helper->__('Order-combined Pick List') . ', ' . $supplier, 31, 810, 'UTF-8');
                        }
                        $page->setLineColor($background_color_header_zend);
                        $page->setFillColor($background_color_header_zend);
                        $page->drawRectangle(27, 803, $padded_right, 804);
                    }
                    
					$max_qty_length_display = 0;
                    if ($from_shipment != 'shipment')
                        $max_qty_length_display = (($max_qty_length - 2) * $generalConfig['font_size_body']);
                    else
                        $max_qty_length_display = ((($max_qty_length + 5) * ($generalConfig['font_size_body'] * 1.1) * 1.4) - 57);
                    
                    $xX = ($qtyX + 10);
                    $skuXreal = $col_title_position_sku[1];//+ $max_qty_length_display);
                    if ($col_title_position_attr2[1] < ($xX + (2 * $generalConfig['font_size_body']) + 5))
                        $col_title_position_attr2[1] = ($xX + (2 * $generalConfig['font_size_body']) + 5);
                    if ($col_title_position_name[1] < ($xX + (2 * $generalConfig['font_size_body']) + 5))
                        $col_title_position_name[1] = ($xX + (2 * $generalConfig['font_size_body']) + 5);
                    if ($skuXreal > $col_title_position_attr2[1]) {
                        $maxWidthSku = ($padded_right - $skuXreal + 20);
                        $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        $font_size_compare = ($generalConfig['font_size_body']);
                        $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare);
                        $char_width = $line_width / 10;
                        $max_chars = round($maxWidthSku / $char_width);
                    }
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
					
                    if ($qty_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_qty[0]), $col_title_position_qty[1], $this->y, 'UTF-8');

                    if ($stock_qty_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_product_stock_qty[0]), $col_title_product_stock_qty[1], $this->y, 'UTF-8');

                    if ($nameyn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_name[0]), ($col_title_position_name[1] + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');

                    if ($warehouseyn == 1)
                        $page->drawText($col_title_position_warehouse[0], intval($col_title_position_warehouse[1]), $this->y, 'UTF-8');

                    if ($combined_weight_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_weight[0]), ($col_title_position_weight[1] + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');

                    if ($product_images_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_product_images[0]), ($col_title_product_images[1]), $this->y, 'UTF-8');

                    if ($combined_sku_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_sku[0]), ($col_title_position_sku[1]), $this->y, 'UTF-8');

                    if ($combined_total_product_paid_options != 0)
                        $page->drawText(Mage::helper('sales')->__($col_total_paid_position_sku[0]), ($col_total_paid_position_sku[1]), $this->y, 'UTF-8');

                    if ($shelving_yn == 1 && $combine_attribute_yn == 0)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_attr1[0]), ($col_title_position_attr1[1]), $this->y, 'UTF-8');

                    if ($extra_yn == 1 && $combine_attribute_yn == 0)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_attr2[0]), ($col_title_position_attr2[1]), $this->y, 'UTF-8');

                    if ($extra3_yn == 1 && $combine_attribute_yn == 0)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_attr3[0]), ($col_title_position_attr3[1]), $this->y, 'UTF-8');

                    if ($extra4_yn == 1 && $combine_attribute_yn == 0)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_attr4[0]), ($col_title_position_attr4[1]), $this->y, 'UTF-8');

                    if ($stockcheck_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_stock[0]), $col_title_position_stock[1], $this->y, 'UTF-8');

                    if ($trolleybox_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_trolleybox[0]), $col_title_position_trolleybox[1], $this->y, 'UTF-8');

                    if ($product_sku_barcode_yn == 1)
                        $page->drawText(Mage::helper('sales')->__($col_title_position_productsku_barcode[0]), $col_title_position_productsku_barcode[1], $this->y, 'UTF-8');

                    $this->y -= ($generalConfig['font_size_subtitles'] + 10);
                    $processed_skus = array();
                    // roll_SKU
                    $page_count = 1;
                    $grey_next_line = 0;
                    $trolleybox_current_trolley_id = 1;

                    /* splits bundle options */
                    $config_group = 'messages';
                    $group_bundle_parent_child_yn = $this->_getConfig('group_bundle_parent_child_yn', 1, false, $config_group);
                    if ($group_bundle_parent_child_yn && $show_bundle_parent_yn == 1) {

                        $sortArray = array();
                        foreach ($sku_master as $master) {
                            foreach ($master as $key => $value) {
                                if (!isset($sortArray[$key]))
                                    $sortArray[$key] = array();
                                $sortArray[$key][] = $value;
                            }
                        }
                        $orderby = "index"; //change this to whatever key you want from the array
                        array_multisort($sortArray[$orderby], SORT_ASC, $sku_master);
                        $array_bundle = array();
                        foreach ($sku_master as $key => $value) {
                            if ($value['product_type'] == 'bundle-child') {
                                $array_bundle[$key] = $value;
                                unset($sku_master[$key]);
                            }
                        }
                        $sort_array2 = array();
                        foreach ($array_bundle as $master) {
                            foreach ($master as $key => $value) {
                                if (!isset($sort_array2[$key]))
                                    $sort_array2[$key] = array();
                                $sort_array2[$key][] = $value;
                            }
                        }


                        $sort_packing_order = $this->_getConfig('sort_packing_yn', 1, false, $config_group);

                        if ($sort_packing_order) {
                            $orderby = $this->_getConfig('sort_packing', 1, false, $config_group); //change this to whatever key you want from the array
                            $orderby_secondary = $this->_getConfig('sort_packing_secondary', 1, false, $config_group); //change this to whatever key you want from the array
                            $position = $this->_getConfig('sort_packing_order', 1, false, $config_group);
                            $position_secondary = $this->_getConfig('sort_packing_secondary_order', 1, false, $config_group);

                            if ($position == 'descending' && $position_secondary == 'descending')
                                array_multisort($sort_array2[$orderby], SORT_DESC, $sort_array2[$orderby_secondary], SORT_DESC, $array_bundle);
                            else
                                array_multisort($sort_array2[$orderby], SORT_ASC, $sort_array2[$orderby_secondary], SORT_ASC, $array_bundle);
                        }
                        $sku_master = array_merge($sku_master, $array_bundle);
                    }
                    /* splits bundle options */

                    foreach ($sku_master as $key => $value) {
                        if (isset($value['sku']))
                            $sku = $value['sku'];
                        else
                            $sku = 'Bundle';

                        if (($show_bundle_parent_yn != 1) && ($sku_master[$key]['product_type'] == 'bundle-parent'))
                            continue;

                        if (($this->y < 60) || (($output == 'trolleybox') && ($sku_master[$key]['trolleybox_trolley_id'] != $trolleybox_current_trolley_id))) {
                            $trolleybox_current_trolley_id = $sku_master[$key]['trolleybox_trolley_id'];
                            if ($page_count == 1) {
                                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - 15), 'UTF-8');
                            }
                            $page = $this->newPage();
                            $page_count++;
                            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                        }

                        if (!in_array($sku, $processed_skus) || ($trolleybox_yn == 1)) {
                            $processed_skus[] = $sku;

                            $supplier_item_action = 'keep';
                            // if set to filter and a name and this is the name, then print
                            if (isset($sku_supplier[$sku])) {
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
                                    // grey/login and this product is not same supplier as login
                                    $supplier_item_action = 'keepGrey';
                                } elseif ($supplier_options == 'grey' && (!isset($supplier_login) || $supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier))) {
                                    $supplier_item_action = 'keepGrey';
                                } elseif ($supplier_options == 'filter' && (!isset($supplier_login) || $supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier))) {
                                    $supplier_item_action = 'hide';
                                    if (strpos($sku_supplier[$sku], ',')) {
                                        $temp_arr = explode(',', $sku_supplier[$sku]);
                                        if (in_array(strtoupper($supplier), $temp_arr)) {
                                            $supplier_item_action = 'keep';
                                        }
                                        unset($temp_arr);
                                    }
                                } elseif ($supplier_options == 'grey' && (!isset($supplier_login) || $supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier)))
                                    $supplier_item_action = 'keep';
                                elseif ($supplier_options == 'filter' && (!isset($supplier_login) || $supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier)))
                                    $supplier_item_action = 'keep';
                                elseif ($supplier_options == 'grey')
                                    $supplier_item_action = 'keepGrey';
                                elseif ($supplier_options == 'filter')
                                    $supplier_item_action = 'hide';
                            } else {
                                if ($supplier_options == 'grey')
                                    $supplier_item_action = 'keepGrey';
                                elseif ($supplier_options == 'filter')
                                    $supplier_item_action = 'hide';
                            }
                            if ($supplier_item_action != 'hide' && $supplier_item_action != '') {
                                if (($sku_type[$sku] == 'bundle') && ($show_bundle_parent_yn == 0))
                                    continue;
                                if ($grey_next_line == 1) {
                                    $page->setFillColor($alternate_row_color);
                                    $page->setLineColor($alternate_row_color);
                                    $page->drawRectangle(25, ($this->y - 2), $padded_right, ($this->y + 9));
                                    $grey_next_line = 0;
                                } else
                                    $grey_next_line = 1;
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                if ($supplier_item_action == 'keepGrey') {
                                    $page->setFillColor($greyout_color);
                                } else {
                                    if (isset($sku_qty[$sku]) && $sku_master[$sku]['product_type'] != 'bundle-parent') {
                                        if ($split_supplier_yn != 'no')
                                            $total_quantity += $sku_qty_supplier[$supplier][$sku];
                                        else
                                            $total_quantity += $sku_qty[$sku];
                                    } else if (isset($sku_qty[$sku]) && $sku_master[$sku]['product_type'] == 'bundle-parent') {
                                        if ($split_supplier_yn != 'no')
                                            $total_quantity += $sku_qty_supplier[$supplier][$sku];
                                        else
                                            $total_bundle_quantity += $sku_qty[$sku];
                                    } else
                                        $sku_qty[$sku] = 0;
                                    //$total_quantity = $total_quantity + $sku_qty[$sku];
                                    if ($from_shipment == 'shipment')
                                        $total_cost = $total_cost + ($sku_qty[$sku] * $sku_cost[$sku]);
                                    else
                                        $total_cost = $total_cost + ($sku_sqty[$sku] * $sku_cost[$sku]);

                                    if ($tickbox != 0) {
                                        $page->setLineWidth(0.5);
                                        $page->setFillColor($white_color);
                                        $page->setLineColor($black_color);
                                        if ($sku_master[$sku]['product_type'] != 'bundle-parent')
                                            $page->drawRectangle($tickbox_X, ($this->y), $tickbox_X + 7, ($this->y + 7));
                                        else {
                                            $page->drawCircle($tickbox_X + 3.5, ($this->y + 3.5), 3.5);
                                            $sheet_has_bundles = true;
                                        }
                                        if ($tickbox2 != 0) {
                                            if ($sku_master[$sku]['product_type'] != 'bundle-parent')
                                                $page->drawRectangle($tickbox2_X, ($this->y), $tickbox2_X + 7, ($this->y + 7));
                                            else {
                                                $page->drawCircle($tickbox2_X + 3.5, ($this->y + 3.5), 3.5);
                                                $sheet_has_bundles = true;
                                            }
                                        }
                                        $page->setFillColor($font_color_body_zend);
                                    }
                                    /*if($tickbox != 'no')
                                    {
                                    $page->setFillColor($white_color);
                                    $page->setLineColor($black_color);
                                    if($sku_type[$sku] != 'bundle') $page->drawRectangle(27, ($this->y), 34, ($this->y+7));
                                    else $page->drawCircle(27, ($this->y), 7);
                                    $page->setFillColor($font_color_body_zend);
                                    }*/
                                }

                                if ($split_supplier_yn != 'no')
                                    $qty_string = $this->getQtyString($sku_sqty_supplier[$supplier][$sku], $sku_iqty_supplier[$supplier][$sku], $sku_qty_supplier[$supplier][$sku]);
                                else
                                    $qty_string = $this->getQtyString($sku_sqty[$sku], $sku_iqty[$sku], $sku_qty[$sku]);
                                $sku_addon = '';

                                if ($shelving_yn == 1 && isset($sku_shelving[$sku]) && $combine_attribute_yn == 0) {
                                    if ($shelvingpos == 'col')
                                        $page->drawText($sku_shelving[$sku], $col_title_position_attr1[1], $this->y, 'UTF-8');
                                    else
                                        $sku_addon = ' /' . $sku_shelving[$sku];
                                }

                                $x_string = '';
                                if ($qty_yn == 1) {
                                    //TODO DEVSITE
                                    $page->drawText($qty_string, $col_title_position_qty[1], $this->y, 'UTF-8');
                                    //                                         $page->drawText($qty_string, $qtyX, $this->y , 'UTF-8');
                                    $x_string = ' x   ';
                                }

                                if ($stock_qty_yn == 1)
                                    $page->drawText($value['stock_qty'], $col_title_product_stock_qty[1], $this->y, 'UTF-8');

                                $skuXreal = ($col_title_position_sku[1] + $max_qty_length_display);
                                if ($extra_yn == 1 && $combine_attribute_yn == 0)
                                    $page->drawText($sku_extra[$sku], $col_title_position_attr2[1], $this->y, 'UTF-8');
                                if ($extra3_yn == 1 && $combine_attribute_yn == 0)
                                    $page->drawText($sku_extra3[$sku], $col_title_position_attr3[1], $this->y, 'UTF-8');
                                if ($extra4_yn == 1 && $combine_attribute_yn == 0)
                                    $page->drawText($sku_extra4[$sku], $col_title_position_attr4[1], $this->y, 'UTF-8');
                                if ((($product_sku_barcode_yn == 1) || ($product_sku_barcode_yn == 2))) {
                                    $barcode = $sku;
                                    if ($product_sku_barcode_yn == 2)
                                        $barcode = $this->getSkuBarcode($sku, $product_id, $store_id);
                                    $barcode_font_size = 11;
                                    $barcode_Y = $this->y - 4;
                                    $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($barcode, $barcode_type);
                                    $barcodeWidth = $this->parseString($barcode, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                                    $page->setFillColor($white_color);
                                    $page->setLineColor($white_color);
                                    $page->drawRectangle(($sku_barcodeX - 5), ($barcode_Y - 2), ($sku_barcodeX + $barcodeWidth + 5), ($barcode_Y - 2 + ($barcode_font_size * 1.6)));
                                    $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                                    $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                                    if ($barcodeWidth < ($padded_right / 2)) {
                                        $page->drawText($barcodeString, ($sku_barcodeX), ($barcode_Y), 'CP1252');
                                        //$after_print_barcode_y = $this->y - $barcode_font_size * 1.6;
                                    } else {
                                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], '#FF3333');
                                        $page->drawText("!! TRIMMED BARCODE !!", ($sku_barcodeX), ($barcode_Y), 'UTF-8');
                                    }

                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                }

                                if ($warehouseyn == 1)
                                    $page->drawText($supplier, intval($col_title_position_warehouse[1]), $this->y, 'UTF-8');
                                if ($nameyn == 1)
                                    $page->drawText($sku_name[$sku], intval($col_title_position_name[1]), $this->y, 'UTF-8');
                                if ($combined_weight_yn == 1)
                                    $page->drawText($sku_weight[$sku], intval($col_title_position_weight[1]), $this->y, 'UTF-8');
                                if ($combined_total_product_paid_options != 0) {
                                    if ($combined_total_product_paid_options == 3)
                                        $page->drawText(round((isset($sku_master[$sku]['qty_invoiced']) && $sku_master[$sku]['qty_invoiced'] != 0 ? $sku_master[$sku]['paid_invoiced'] / $sku_master[$sku]['qty_invoiced'] : $sku_master[$sku]['paid_invoiced']), 2), $col_total_paid_position_sku[1], $this->y, 'UTF-8');
                                    else
                                        $page->drawText(round($sku_master[$sku]['paid'], 2), $col_total_paid_position_sku[1], $this->y, 'UTF-8');
                                }

                                if (($stockcheck_yn == 1) && ($supplier_item_action != 'keepGrey')) {
                                    if ($stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty())
                                        $stock = round($stock);
                                    $sku_stock[$sku] = $stock;
                                    if ($sku_stock[$sku] < $stockcheck) {
                                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                                        $this->y -= 12;
                                        $page->setFillColor($red_bkg_color);
                                        $page->setLineColor(new Zend_Pdf_Color_Rgb(1, 1, 1));
                                        $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                                        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                                        $warning = $helper->__('Stock Warning') . '      ' . $helper->__('SKU') . ': ' . $sku . '    ' . $helper->__('Net Stock After All Picks') . ' : ' . $sku_stock[$sku];
                                        $page->drawText($warning, 60, $this->y, 'UTF-8');
                                        $this->y -= 4;
                                    }
                                }
                                $this->y -= ($generalConfig['font_size_body'] + 2);
                            }
                        }
                    }

                    if ($from_shipment == 'shipment') {
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $this->y -= 30;
                        $page->drawText($helper->__('Shipments included') . ':', 30, $this->y, 'UTF-8');

                        foreach ($shipment_list as $k => $value) {
                            $this->y -= $generalConfig['font_size_body'];
                            $page->drawText('#' . $value . ' (' . $helper->__('order') . ' #' . $order_list[$k] . ' : ' . $name_list[$k] . ')', 30, $this->y, 'UTF-8');
                        }
                    }

                    // end roll_SKU
                    $this->y -= 30;
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                    if (($showcount_yn == 1) || ($showcost_yn == 1) || ($order_count_yn == 1) || ($total_paid_subtotal_yn == 1) || (($combined_total_weight_yn == 1) && ($total_weight > 0))) {
                        //if ($page_count == 1)
                        //   $this->y = $thisYbase;
                        $shipYbox = 0;
                        //calculate totals.
                        if ($showcount_yn == 1)
                            $shipYbox += ($generalConfig['font_size_body'] * 2);

                        if ($order_count_yn == 1)
                            $shipYbox += $generalConfig['font_size_body'] * 1.4;

                        if ($showcost_yn == 1)
                            $shipYbox += $generalConfig['font_size_body'] * 1.4;

                        if ($total_paid_subtotal_yn == 1)
                            $shipYbox += $generalConfig['font_size_body'] * 1.4;

                        if ((($shipping_subtotal_yn == 1) && ($total_shipping_cost['shipping_plus_tax'] > 0)) || (($shipping_subtotal_yn == 2) && ($total_shipping_cost['shipping_ex_tax'] > 0)))
                            $shipYbox += $generalConfig['font_size_body'] * 1.4;

                        if (($combined_total_weight_yn == 1) && ($total_weight > 0))
                            $shipYbox += $generalConfig['font_size_body'] * 1.4;

                        if ($sheet_has_bundles === true)
                            $shipYbox += $generalConfig['font_size_body'] * 1.4;

                        if ($error_product_count > 0)
                            $shipYbox += $generalConfig['font_size_body'] * 1.4;

                        if (($this->y - $shipYbox - $generalConfig['font_size_body'] * 1.4) < 60) {
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

                        $page->drawRectangle(340, ($this->y - $shipYbox - $generalConfig['font_size_body'] * 1.4), ($padded_right - 2), $this->y);
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    }

                    if ($showcount_yn == 1) {
                        $this->y -= ($generalConfig['font_size_body'] * 2);

                        if ($from_shipment == 'shipment') {
							$this->writeSummary($helper->__('Total item quantity shipped this time'), $total_quantity, $page, 'none');
							$this->writeSummary($helper->__('Total item quantity shipped all time'), $count_all, $page, 'none');
							$this->writeSummary($helper->__('Total item quantity ordered'), $total_quantity, $page, 'none');
							$this->writeSummary($helper->__('---------------------------------------------------'), '', $page, 'none');
							$this->writeSummary($helper->__('Qty remaining to be shipped'), ($total_quantity - $count_all), $page, 'none');
                        } else
							$this->writeSummary($helper->__('Items') . $text_addon_totalqty, $total_quantity, $page, 'none');
                    }
                    if ($sheet_has_bundles === true)
						$this->writeSummary($helper->__('Bundle'), $total_bundle_quantity, $page, 'circle');						

                    if ($order_count_yn == 1)
						$this->writeSummary($helper->__('Orders'), $order_count, $page, 'none');

                    if (($showcost_yn == 1) && ($total_cost > 0)) {
                        $this->y -= $generalConfig['font_size_body'] * 1.4;
                        if ($from_shipment == 'shipment')
							$this->writeSummary($helper->__('Total cost these shipments'), $currency_symbol . "  " . $total_cost, $page, 'none');
                        else
							$this->writeSummary($helper->__('Total cost'), $currency_symbol . "  " . $total_cost, $page, 'none');
                    }

                    if ($total_paid_subtotal_yn == 1) {
                        $this->y -= $generalConfig['font_size_body'] * 1.4;
                        if ($from_shipment == 'shipment')
                            $this->writeSummary($helper->__('Total paid this shipment'), $currency_symbol . "  " . round($total_paid, 2), $page, 'none');
                        else
                            $this->writeSummary($helper->__('Total paid'), $currency_symbol . "  " . round($total_paid, 2), $page, 'none');
                    }

                    if ($shipping_subtotal_yn == 2) {

                        if ($total_shipping_cost['shipping_ex_tax'] > 0)
                            $this->writeSummary($helper->__('Total shipping paid (ex. tax)'), $shipping_currency . $shipping_currency_symbol . ' ' . round($total_shipping_cost['shipping_ex_tax'], 2), $page, 'none');						
                    } else if ($shipping_subtotal_yn == 1) {

                        if ($total_shipping_cost['shipping_plus_tax'] > 0)
                            $this->writeSummary($helper->__('Total shipping paid (inc. tax)'), $shipping_currency . $shipping_currency_symbol . ' ' . round($total_shipping_cost['shipping_plus_tax'], 2), $page, 'none');						
                    }

                    if (($combined_total_weight_yn == 1) && ($total_weight > 0))
						$this->writeSummary($helper->__('Total weight'), round($total_weight, $combined_total_weight_rounding), $page, 'none');

                    if ($error_product_count > 0)
						$this->writeSummary($helper->__('Error Product Count'), $error_product_count, $page, 'none');
						
                    if ($printdates == 1) {
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 3), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText($helper->__('Printed') . ':   ' . date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())), 160, 18, 'UTF-8');
                    }
                }
                unset($processed_skus);
            }
        } else {
            $this->y = ($page_top - 43);

            $total_quantity = 0;
            $total_bundle_quantity = 0;
            $total_cost = 0;
            $sheet_has_bundles = false;


            if ($picklogo == 1) {
                $sub_folder = 'logo_pack';
                $option_group = 'wonder';
                $suffix_group = '/pack_logo';
                $x1 = 27;
				$page_top_spacer = 0;//10
                $y2 = ($page_top - $page_top_spacer);
                $y1 = $this->printHeaderLogo($page, $store_id, $picklogo, $page_top, $logo_maxdimensions, $sub_folder, $option_group, $suffix_group, $x1, $y2);
                $this->y = $y1 - 10;
                $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                if ($output == 'trolleybox') {
                    $picklist_title .= $helper->__('Trolleybox Pick List');
                } else {
                    if ($from_shipment == 'shipment')
                        $page->drawText($helper->__('Shipment-combined Pick List'), 325, ($page_top - $page_top_spacer - (41/2)), 'UTF-8');
                    else
                        $page->drawText($helper->__('Order-combined Pick List'), 325, ($page_top - $page_top_spacer - (41/2)), 'UTF-8');
                }
				
                $page->setFillColor($background_color_header_zend);
                $page->setLineColor($background_color_header_zend);
                $page->setLineWidth(0.5);
                if($generalConfig['line_width_company'] > 0)
	                $page->drawRectangle(304, $y1, (304 + $generalConfig['line_width_company']), ($page_top + 5));
            } else {
                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] + 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

                $mage_timestamp = Mage::getModel('core/date')->timestamp(time());
                $date_format = preg_replace('~(.*): ~', '', $date_format);
                $order_date = date($date_format, $mage_timestamp);

                $picklist_title = '';
                if ($printdates == 'yestitle')
                    $picklist_title = $order_date . ' ';

                if ($output == 'trolleybox')
                    $picklist_title .= $helper->__('Trolleybox Pick List');
                else {
                    if ($from_shipment == 'shipment')
                        $picklist_title .= $helper->__('Shipment-combined Pick List');
                    else
                        $picklist_title .= $helper->__('Order-combined Pick List');
                }
                $page->drawText($picklist_title, 31, ($page_top - 10), 'UTF-8');				
            }
            $max_qty_length_display = 0;

            if ($from_shipment != 'shipment')
                $max_qty_length_display = (($max_qty_length - 2) * $generalConfig['font_size_body']);
            else
                $max_qty_length_display = ((($max_qty_length + 5) * ($generalConfig['font_size_body'] * 1.1) * 1.4) - 57);

            $xX = ($qtyX + 10);

            $skuXreal = $col_title_position_sku[1];
            if ($col_title_position_attr2[1] < ($xX + (2 * $generalConfig['font_size_body']) + 5))
                $col_title_position_attr2[1] = ($xX + (2 * $generalConfig['font_size_body']) + 5);
            if ($col_title_position_name[1] < ($xX + (2 * $generalConfig['font_size_body']) + 5))
                $col_title_position_name[1] = ($xX + (2 * $generalConfig['font_size_body']) + 5);
            if ($skuXreal > $col_title_position_attr2[1]) {
                $maxWidthSku = ($padded_right - $skuXreal + 20);
                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $font_size_compare = ($generalConfig['font_size_body']);
                $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare);
                $char_width = $line_width / 10;
                $max_chars = round($maxWidthSku / $char_width);
            }

			
	        if (strtoupper($generalConfig['background_color_subtitles']) != '#FFFFFF') {
		        $fillbar_padding = explode(",", $generalConfig['fillbar_padding']);
		        $line_widths = explode(",", $generalConfig['bottom_line_width']);
				
	            $page->setFillColor($background_color_subtitles_zend);
	            $page->setLineColor($background_color_subtitles_zend);
	            $page->setLineWidth(0.5);

				// make space for company vert bar
				if($generalConfig['line_width_company'] > 0)
					$this->y -= $generalConfig['font_size_subtitles'];
				// make some space for lines
				$this->y -= ($generalConfig['font_size_subtitles'] / 2);
				
				$top_fillbar = $this->y + ($generalConfig['font_size_subtitles'] + 2) + $fillbar_padding[0];
				$bottom_fillbar = $this->y - ($generalConfig['font_size_subtitles'] / 2) - $fillbar_padding[1];
              
			    switch ($generalConfig['fill_bars_subtitles']) {
                    case 0:
                        $page->drawRectangle($pageConfig['padded_left'], $bottom_fillbar, $pageConfig['padded_right'], $top_fillbar);
                        break;
                    case 1:		
                        if($line_widths[0] > 0){
                            $page->setLineWidth($line_widths[0]-0.5);
                            $page->drawLine($pageConfig['padded_left'], $top_fillbar, $pageConfig['padded_right'], $top_fillbar);
                        }
                        if($line_widths[1] > 0){
                            $page->setLineWidth($line_widths[1]-0.5);
                            $page->drawLine($pageConfig['padded_left'], $bottom_fillbar, $pageConfig['padded_right'], $bottom_fillbar);
                        }
						break;
                    case 2:
                        break;
                }

            }
			
            $processed_skus = array();
            $page_count = 1;
            $grey_next_line = 0;
            $thisYnext = 0;
            $thisYorig = 0;

            $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

            if ($qty_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_position_qty[0]), $col_title_position_qty[1], $this->y, 'UTF-8');

            if ($stock_qty_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_product_stock_qty[0]), $col_title_product_stock_qty[1], $this->y, 'UTF-8');

            if ($nameyn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_position_name[0]), ($col_title_position_name[1] + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');

            if ($warehouseyn == 1)
                $page->drawText($col_title_position_warehouse[0], intval($col_title_position_warehouse[1]), $this->y, 'UTF-8');

            if ($combined_weight_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_position_weight[0]), ($col_title_position_weight[1] + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');

            if ($product_images_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_product_images[0]), ($col_title_product_images[1]), $this->y, 'UTF-8');

            if ($combined_sku_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_position_sku[0]), ($col_title_position_sku[1]), $this->y, 'UTF-8');

            if ($combined_total_product_paid_options != 0)
                $page->drawText(Mage::helper('sales')->__($col_total_paid_position_sku[0]), ($col_total_paid_position_sku[1]), $this->y, 'UTF-8');

            if ($shelving_yn == 1 && $combine_attribute_yn == 0)
                $page->drawText(Mage::helper('sales')->__($col_title_position_attr1[0]), ($col_title_position_attr1[1]), $this->y, 'UTF-8');

            if ($extra_yn == 1 && $combine_attribute_yn == 0)
                $page->drawText(Mage::helper('sales')->__($col_title_position_attr2[0]), ($col_title_position_attr2[1]), $this->y, 'UTF-8');

            if ($extra3_yn == 1 && $combine_attribute_yn == 0)
                $page->drawText(Mage::helper('sales')->__($col_title_position_attr3[0]), ($col_title_position_attr3[1]), $this->y, 'UTF-8');

            if ($extra4_yn == 1 && $combine_attribute_yn == 0)
                $page->drawText(Mage::helper('sales')->__($col_title_position_attr4[0]), ($col_title_position_attr4[1]), $this->y, 'UTF-8');

            if ($combine_attribute_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_position_combine_attribute[0]), ($col_title_position_combine_attribute[1]), $this->y, 'UTF-8');

            if ($stockcheck_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_position_stock[0]), $col_title_position_stock[1], $this->y, 'UTF-8');

            if ($trolleybox_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_position_trolleybox[0]), $col_title_position_trolleybox[1], $this->y, 'UTF-8');

            if ($product_sku_barcode_yn == 1)
                $page->drawText(Mage::helper('sales')->__($col_title_position_productsku_barcode[0]), $col_title_position_productsku_barcode[1], $this->y, 'UTF-8');
			
	        if (strtoupper($generalConfig['background_color_subtitles']) != '#FFFFFF')
					$this->y -= ($generalConfig['font_size_subtitles']/2);// + ($fillbar_padding[1] + $line_widths[1]);
				
            $this->y -= ($generalConfig['font_size_body'] / 5);
            $sku_master_runcount = 0;
            $trolleybox_current_trolley_id = 1;
            $store_id = Mage::app()->getStore()->getId();
            /* splits bundle options  */
            $config_group = 'messages';
            $group_bundle_parent_child_yn = $this->_getConfig('group_bundle_parent_child_yn', 1, false, $config_group);
            if ($group_bundle_parent_child_yn && $show_bundle_parent_yn == 1) {
                $show_bundle_parent_yn = 1;
                $sortArray = array();
                foreach ($sku_master as $master) {
                    foreach ($master as $key => $value) {
                        if (!isset($sortArray[$key]))
                            $sortArray[$key] = array();
                        $sortArray[$key][] = $value;
                    }
                }
                $orderby = "index"; //change this to whatever key you want from the array
                array_multisort($sortArray[$orderby], SORT_ASC, $sku_master);
                $array_bundle = array();
                foreach ($sku_master as $key => $value) {
                    if ($value['product_type'] == 'bundle-child' || $value['product_type'] == 'normal') {
                        $array_bundle[$key] = $value;
                        unset($sku_master[$key]);
                    }
                }
                $sort_array2 = array();
                foreach ($array_bundle as $master) {
                    foreach ($master as $key => $value) {
                        if (!isset($sort_array2[$key]))
                            $sort_array2[$key] = array();
                        $sort_array2[$key][] = $value;
                    }
                }


                $sort_packing_order = $this->_getConfig('sort_packing_yn', 1, false, $config_group);

                if ($sort_packing_order) {
                    $orderby = $this->_getConfig('sort_packing', 1, false, $config_group); //change this to whatever key you want from the array
                    $orderby_secondary = $this->_getConfig('sort_packing_secondary', 1, false, $config_group); //change this to whatever key you want from the array
                    $position = $this->_getConfig('sort_packing_order', 1, false, $config_group);
                    $position_secondary = $this->_getConfig('sort_packing_secondary_order', 1, false, $config_group);
					$sort_dir_primary = SORT_ASC;
					$sort_dir_secondary = SORT_ASC;
                    
					if ($position == 'descending')
						$sort_dir_primary = SORT_DESC;
					
					if ($position_secondary == 'descending')
						$sort_dir_secondary = SORT_DESC;
					
					if(isset($sort_dir_secondary) && isset($sort_array2[$orderby_secondary]))
                        array_multisort($sort_array2[$orderby], $sort_dir_primary, $sort_array2[$orderby_secondary], $sort_dir_secondary, $array_bundle);
					else
						array_multisort($sort_array2[$orderby], $sort_dir_primary, $array_bundle);
                }
                $sku_master = array_merge($sku_master, $array_bundle);
            }
            /* splits bundle options */
            foreach ($sku_master as $key => $value) {
                $sku_master_runcount++;
                if (($show_bundle_parent_yn != 1) && isset($sku_master[$key]['product_type']) && ($sku_master[$key]['product_type'] == 'bundle-parent'))
                    continue;

                if (($this->y < 60) || (($output == 'trolleybox') && ($sku_master[$key]['trolleybox_trolley_id'] != $trolleybox_current_trolley_id))) {
                    if ($output == 'trolleybox')
                        $trolleybox_current_trolley_id = $sku_master[$key]['trolleybox_trolley_id'];
                    if ($page_count == 1) {
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - 15), 'UTF-8');
                    }
                    $page = $this->newPage();
                    $page_count++;
                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                }

                if (isset($value['sku']))
                    $sku = $value['sku'];
                else
                    $sku = 'Bundle';

                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                if ((!in_array($sku, $processed_skus) || ($trolleybox_yn == 1)) && $sku != '') {
                    $processed_skus[] = $sku;
                    if (isset($sku_qty[$sku]) && isset($value['product_type']) && ($value['product_type'] != 'bundle-parent')) {
                        $total_quantity += $sku_qty[$sku];//$this->getQtyString($sku_sqty[$sku], $sku_iqty[$sku], $sku_qty[$sku]);
                        $total_quantity_ordered += $sku_qty[$sku];
                        $total_quantity_shipped += $sku_sqty[$sku];
                        $total_quantity_invoiced += $sku_iqty[$sku];
                    } else if (isset($sku_qty[$sku]) && isset($value['product_type']) && ($value['product_type'] == 'bundle-parent'))
                        $total_bundle_quantity += $sku_qty[$sku];
					else
                        $sku_qty[$sku] = 0;
                    if (!isset($sku_cost[$sku]))
                        $sku_cost[$sku] = 0;
                    if ($from_shipment == 'shipment')
                        $total_cost = $total_cost + ($sku_cost[$sku] * $sku_sqty[$sku]);
                    else
                        $total_cost = $total_cost + ($sku_cost[$sku] * $sku_qty[$sku]);

                    if ($grey_next_line == 1) {
                        $page->setFillColor($grey_bkg_color);
                        $page->setLineColor($grey_bkg_color);

                        $this->y -= 2;

                        $grey_box_y1 = ($this->y - ($generalConfig['font_size_body'] / 5));
                        $grey_box_y2 = ($this->y + ($generalConfig['font_size_body'] * 0.85));

                        if (($product_images_yn == 1) && isset($sku_image_paths[$sku]['path'][0])) {
                            $grey_box_y1 = ($this->y - $sku_image_paths[$sku]['height'] - 1.5);
                            $grey_box_y2 = ($this->y + 1.5);
                        } else {
                            $grey_box_y1 -= ($generalConfig['font_size_body'] + 1);
                            $grey_box_y2 -= $generalConfig['font_size_body'];
                        }
                        if (isset($sku_order_id_options[$sku]) && $sku_order_id_options[$sku] != '' && $options_yn == 'newline') {
                            $line_count = $this->getLineCountOption($sku_order_id_options[$sku]);
                            $grey_box_y1 = ($this->y - $generalConfig['font_size_body'] / 5 - ($line_count + 2) * $generalConfig['font_size_body'] - count(array_filter(explode('newline', $sku_order_id_options[$sku]))));
                            //$grey_box_y2 = ($this->y + 1.5);
                        }
                        if ($combine_attribute_yn == 1) {
                            $count_attribute = $shelving_yn + $extra_yn + $extra3_yn + $extra4_yn;
                            if ($grey_box_y1 > $this->y - $generalConfig['font_size_body'] / 5 - ($count_attribute) * ($generalConfig['font_size_body'] + 2))
                                $grey_box_y1 = $this->y - $generalConfig['font_size_body'] / 5 - ($count_attribute) * ($generalConfig['font_size_body'] + 2);
                        }
                        $page->drawRectangle(25, $grey_box_y1, $padded_right, $grey_box_y2);
                        $grey_next_line = 0;
                    }
                    else {
                        $grey_next_line = 1;
                        $this->y -= 2;
                    }

                    $has_shown_product_image = 0;
                    /**
                     * images start
                     */
                    if (($product_images_yn == 1) && isset($sku_image_paths[$sku]['path'][0])) {
                        $product_images_line_nudge = 0;
                        $product_images_line_nudge = ($sku_image_paths[$sku]['height'] / 2);
                        if ($product_images_border_color_temp != '#FFFFFF')
                            $product_images_line_nudge += 1.5;

                        if ($sku_master_runcount == 1)
                            $this->y += ($product_images_line_nudge);

                        $image_x_addon = 0;
                        $image_x_addon_2 = 0;
                        $x1 = $col_title_product_images[1];
                        $y1 = ($this->y - $sku_image_paths[$sku]['height']);
                        $x2 = ($col_title_product_images[1] + $sku_image_paths[$sku]['width']);
                        $y2 = ($this->y);

                        $image_ext = '';
                        $image_part = explode('.', $sku_image_paths[$sku]['path'][0]);
                        $image_ext = array_pop($image_part);
                        if ((($image_ext == 'jpg') || ($image_ext == 'JPG') || ($image_ext == 'jpeg') || ($image_ext == 'png') || ($image_ext == 'PNG'))) {
                            //continue;
                            try {
                                if (!$helper->checkTypeImageProduct($sku_image_paths[$sku]['path'][0], $image_ext)) {
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

                                $this->y -= ($sku_image_paths[$sku]['height']);
                                if (!isset($product_build_value['bundle_options_sku']))
                                    $this->y += ($product_images_line_nudge - ($generalConfig['font_size_body'] / 2));
                                $has_shown_product_image = 1;
                            } catch (Exception $e) {
                            }
                        }
                    }
                    if (isset($has_shown_product_image) && ($has_shown_product_image == 1)) {
                        if (isset($sku_type[$sku]) && ($sku_type[$sku] == 'bundle'))
                            $this->y -= $generalConfig['font_size_body'] * 2;
                    } else
                        $this->y -= $generalConfig['font_size_body'] + 1;

                    /**
                     * images end
                     */


                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    if ($tickbox != 0) {
                        $page->setLineWidth(0.5);
                        $page->setFillColor($white_color);
                        $page->setLineColor($black_color);
                        if (isset($sku_master[$sku]) && $sku_master[$sku]['product_type'] == 'bundle-parent') {
                            $page->drawCircle($tickbox_X + 3.5, ($this->y + 3.5), 3.5);
                            $sheet_has_bundles = true;
                        } else
                            $page->drawRectangle($tickbox_X, ($this->y), $tickbox_X + 7, ($this->y + 7));
                        if ($this->_getConfig('combined_tickbox_signature_yn', 0, false, $config_group)) {
                            $page->drawLine(($tickbox_X + 10), ($this->y), ($tickbox_X * 3), ($this->y));
                        }

                        if ($tickbox2 != 0) {
                            if (isset($sku_master[$sku]) && $sku_master[$sku]['product_type'] == 'bundle-parent') {
                                $page->drawCircle($tickbox2_X + 3.5, ($this->y + 3.5), 3.5);
                                $sheet_has_bundles = true;
                            } else
                                $page->drawRectangle($tickbox2_X, ($this->y), $tickbox2_X + 7, ($this->y + 7));
                        }
                        $page->setFillColor($font_color_body_zend);
                    }
                    if ($from_shipment == 'shipment')
                        $qty_string = 's:' . (int)$sku_sqty[$sku] . ' / ts:' . $sku_tqty[$sku] . ' / o:' . $sku_qty[$sku];
                    else
                        $qty_string = $sku_qty[$sku];
                   
                    if ($nameyn == 1) {
                        $name_addon = '';
                        if (isset($options_sku_parent[$sku]) && is_array($options_sku_parent[$sku])) {
                            if ($this->_getConfig('product_name_bold_yn_combined', 0, false, $config_group))
                                $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            $page->drawText($sku_name[$sku] . $name_addon, $col_title_position_name[1], $this->y, 'UTF-8');
                            $this->y -= ($generalConfig['font_size_body'] + 3);
                        } else {
                            $print_name = $sku_name[$sku] . $name_addon;
                            if ($print_name != '') {

                                if ($this->_getConfig('product_name_bold_yn_combined', 0, false, $config_group)) {
                                    $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                }
                                $next_col_to_name = getPrevNext2($columns_xpos_array, 'Name', 'next', $padded_right);
                                $max_width_length = ($next_col_to_name - $col_title_position_name[1]);
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $line_width_name = $this->parseString($print_name, $font_temp, $generalConfig['font_size_body']);
                                $char_width_name = $line_width_name / (strlen($print_name));
                                $max_chars_name = round($max_width_length / $char_width_name);
                                $name_trim = str_trim($print_name, 'WORDS', $max_chars_name - 10, '...');
                                $name_trim2 = str_trim($print_name, 'CHARS', $max_chars_name - 10, '...');
                                $multiline_name = wordwrap($print_name, $max_chars_name - 10, "\n");
                                if ($trim_product_name_yn == 1)
                                    $page->drawText($name_trim, $col_title_position_name[1], $this->y, 'UTF-8');
                                else {
                                    $line_height = (1.15 * $generalConfig['font_size_body']);
                                    $token = strtok($multiline_name, "\n");
                                    $multiline_name_array = array();
                                    $temp_y = $this->y;
                                    $namey_after = 0;
                                    $lines_of_name = 0;
                                    if ($token != false) {
                                        while ($token != false) {
                                            $multiline_name_array[] = $token;
                                            $token = strtok("\n");
                                        }

                                        if ($grey_next_line == 0 && count($multiline_name_array) > 1 && !isset($sku_image_paths[$sku]['path'][0])) {
                                            $page->setFillColor($grey_bkg_color);
                                            $page->setLineColor($grey_bkg_color);
                                            $page->drawRectangle(25, ($this->y - (($generalConfig['font_size_body'] + 4) * (count($multiline_name_array) - 1))), $padded_right, $this->y - 2);
                                            $page->setFillColor($black_color);
                                            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                                        }

                                        foreach ($multiline_name_array as $name_in_line) {
                                            $lines_of_name++;
                                            $namey_after += $line_height;
                                            $page->drawText($name_in_line, $col_title_position_name[1], $this->y, 'UTF-8');
                                            $this->y -= $line_height;
                                        }
                                    }
                                    $namey_after -= $line_height;
                                    $this->y = $temp_y;
                                }
                            }
                        }
                    }
                    /*get qty string**/
                    $qty_string = $this->getQtyString($sku_sqty[$sku], $sku_iqty[$sku], $sku_qty[$sku]);

                    if ($stock_qty_yn == 1)
                        $page->drawText(round($value['stock_qty'], 0), $col_title_product_stock_qty[1], $this->y, 'UTF-8');

                    /***************/
                    $name_addon = '';
                    $sku_addon = '';
                    $combine_attribute_array = array();
                    if ($shelving_yn == 1) {
                        $show_this = '';
                        if (($trolleybox_yn == 0) && isset($sku_shelving[$sku]))
                            $show_this = $sku_shelving[$sku];
                        elseif (($trolleybox_yn == 1) && isset($sku_master[$key]['sort']))
                            $show_this = $sku_master[$key]['sort'];
                        if ($combine_attribute_yn == 1) {
                            $combine_attribute_array[$col_title_position_attr1[0]] = $show_this;
                        } else
                            if ($shelvingpos == 'col' && $show_this != '') {
                                $print_attr = $show_this;
                                $next_col_to_attr = getPrevNext2($columns_xpos_array, $col_title_position_attr1[0], 'next', $padded_right);
                                $max_width_length = $next_col_to_attr - $col_title_position_attr1[1];
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $line_width_attr = $this->parseString($print_attr, $font_temp, $generalConfig['font_size_body']);
                                $char_width_attr = $line_width_attr / (mb_strlen($print_attr, 'UTF-8'));
                                $max_chars_attr = round($max_width_length / $char_width_attr);
                                $attr_trim2 = str_trim($print_attr, 'CHARS', $max_chars_attr, '...');
                                $page->drawText($attr_trim2, $col_title_position_attr1[1], $this->y, 'UTF-8');
                            } else {
                                if ($combined_sku_yn == 1)
                                    if (strlen(trim($show_this)) > 0)
                                        $sku_addon = ' /' . $show_this;
                                    else
                                        $sku_addon = '';
                                elseif ($nameyn == 1)
                                    if (strlen(trim($show_this)) > 0)
                                        $name_addon = ' /' . $show_this;
                                    else
                                        $name_addon = '';
                            }
                    }

                    if ($trolleybox_yn == 1) {
                        $trolley_id_display = $sku_master[$key]['trolleybox_box_id'];
                        if ($show_orderid_with_trolleyboxid_yn == 1)
                            $trolley_id_display .= ' (' . $sku_master[$key]['order_id'] . ')';
                        $page->drawText($trolley_id_display, $col_title_position_trolleybox[1], $this->y, 'UTF-8');
                    }

                    $display_sku = '';
                    if (isset($sku_sku[$sku]))
                        $display_sku = htmlspecialchars_decode($sku_sku[$sku]);
                    elseif ($trolleybox_yn == 0)
                        continue; // i.e. skip this one if no sku record

                    if (isset($sku_order_id_options[$sku]) && $sku_order_id_options[$sku] != '' && ($options_yn == 'inline')) {
                        $display_sku .= ' ' . $sku_order_id_options[$sku];
                    }
                    /*********************/
                    $red_color = new Zend_Pdf_Color_Html('darkRed');
                    $product_qty_upsize_yn = $this->_getConfig('product_qty_upsize_yn', 1, false, $config_group);
                    $product_qty_rectangle = 0;
                    if ($product_qty_upsize_yn == '1' || $product_qty_upsize_yn == '1') {
                        if ($product_qty_upsize_yn == '1')
                            $product_qty_rectangle = 1;
                        $product_qty_upsize_yn = 1;
                    }

                    $qtyX = $col_title_position_qty[1];
                    $stock_qty_X = $col_title_product_stock_qty[1];
                    if ($show_qty_options != 2)
                        $qty_string = round($qty_string, 2);
                    if ($product_qty_upsize_yn == 1 && $qty_string > 1) {
                        if ($product_qty_rectangle == 1) {
                            $page->setLineWidth(1);
                            $page->setLineColor($black_color);
                            $page->setFillColor($black_color);
                            if (($qty_string >= 100) || (strlen($qty_string) > 3))
                                $page->drawRectangle(($qtyX), ($this->y - 1), ($qtyX - 18 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                            else if (($qty_string >= 10) || (strlen($qty_string) >= 2))
                                $page->drawRectangle(($qtyX - 1), ($this->y - 1), ($qtyX - 8 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                            else
                                $page->drawRectangle(($qtyX - 1), ($this->y - 1), ($qtyX - 2 + (strlen($qty_string) * $generalConfig['font_size_body'])), ($this->y - 4 + $generalConfig['font_size_body'] * 1.2));
                            $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'white');
                            $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                        } else {
                            $this->_setFont($page, 'bold', ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                        }
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    } else
                        $page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');
                    if ($combined_sku_yn == 1) {

                        $displaySkuReal = $display_sku . $sku_addon;
                        // if sku and extra attribute switched pos
                        if (($extra_yn == 1) && $skuXreal > $col_title_position_attr2[1]) {
                            if (strlen($displaySkuReal) > $max_chars) {
                                $chunks = str_split($displaySkuReal, $max_chars);

                                $thisYorig = $this->y;
                                $lines = 0;
                                foreach ($chunks as $key => $chunk) {
                                    if ($grey_next_line == 0 && $lines > 0) {
                                        $page->setFillColor($grey_bkg_color);
                                        $page->setLineColor($grey_bkg_color);
                                        $page->drawRectangle(25, ($this->y - ($generalConfig['font_size_body'] / 5)), $padded_right, ($this->y + ($generalConfig['font_size_body'] * 1.1)));
                                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                        $page->drawText($chunk, ($skuXreal + 8), $this->y, 'UTF-8');
                                    } else {
                                        $page->drawText($chunk . '...', $skuXreal, $this->y, 'UTF-8');
                                    }
                                    $this->y -= ($generalConfig['font_size_body'] + 3);
                                    $lines++;
                                }
                                $this->y = $thisYorig;

                                unset($chunks);
                            } else {
                                $page->drawText($displaySkuReal, $skuXreal, $this->y, 'UTF-8');
                            }

                        } else {
                            if ($combined_sku_trim == 1){
                                $next_col_to_name = getPrevNext2($columns_xpos_array, 'SKU', 'next', $padded_right);
                                $max_width_length = ($next_col_to_name - $col_title_position_sku[1]);
                                $font_temp        = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $line_width_sku  = $this->parseString($displaySkuReal, $font_temp, $generalConfig['font_size_body']);
                                $char_width_sku  = $line_width_sku / (strlen($displaySkuReal));
                                $max_chars_name   = round($max_width_length / $char_width_sku);
                                $displaySkuReal        = str_trim($displaySkuReal, 'WORDS', $max_chars_name - 5, '...');

                                $page->drawText($displaySkuReal, $skuXreal, $this->y, 'UTF-8');
                            }
                            else{
                                $maxWidthSku = ($col_title_position_name[1] - $col_title_position_sku[1]) - 20;
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $font_size_compare = ($generalConfig['font_size_body']);
                                $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare);
                                $char_width = $line_width / 10;
                                $max_chars = round($maxWidthSku / $char_width);

                                $chunks = str_split($displaySkuReal, $max_chars);
                                if($chunks > 1){
                                    $thisYorig = $this->y;
                                    $lines = 0;
                                    foreach ($chunks as $key => $chunk) {
                                        if ($grey_next_line == 0 && $lines > 0) {
                                            $page->setFillColor($grey_bkg_color);
                                            $page->setLineColor($grey_bkg_color);
                                            $page->drawRectangle(25, ($this->y - ($generalConfig['font_size_body'] / 5)), $padded_right, ($this->y + ($generalConfig['font_size_body'] * 1.1)));
                                        }
                                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                        $page->drawText($chunk, ($skuXreal + 8), $this->y, 'UTF-8');

                                        $this->y -= ($generalConfig['font_size_body'] + 3);
                                        $lines++;
                                    }

                                    $this->y = $thisYorig;
                                    unset($chunks);
                                }
                                else{
                                    $page->drawText($displaySkuReal, $skuXreal, $this->y, 'UTF-8');
                                }
                            }
                        }
                    }
                    if ($extra_yn == 1) {
                        $print_attr = isset($sku_extra[$sku]) ? $sku_extra[$sku] : '';
                        if (strlen($print_attr) > 0) {
                            if ($combine_attribute_yn == 1) {
                                $combine_attribute_array[$col_title_position_attr2[0]] = $print_attr;
                            } else {
                                $next_col_to_attr = getPrevNext2($columns_xpos_array, $col_title_position_attr2[0], 'next', $padded_right);
                                $max_width_length = $next_col_to_attr - $col_title_position_attr2[1];
                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $line_width_attr = $this->parseString($print_attr, $font_temp, $generalConfig['font_size_body']);
                                $char_width_attr = $line_width_attr / (mb_strlen($print_attr, 'UTF-8'));
                                $max_chars_attr = round($max_width_length / $char_width_attr);
                                $attr_trim2 = str_trim($print_attr, 'CHARS', $max_chars_attr, '...');
                                $page->drawText($attr_trim2, $col_title_position_attr2[1], $this->y, 'UTF-8');
                            }
                        }
                    }

                    if ($extra3_yn == 1) {
                        $print_attr = $sku_extra3[$sku];
                        if (strlen($print_attr) > 0) {
                            if ($combine_attribute_yn == 1)
                                $combine_attribute_array[$col_title_position_attr3[0]] = $print_attr;
                            else {
                                $next_col_to_attr = getPrevNext2($columns_xpos_array, $col_title_position_attr3[0], 'next', $padded_right);
                                $max_width_length = $next_col_to_attr - $col_title_position_attr3[1];

                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $line_width_attr = $this->parseString($print_attr, $font_temp, $generalConfig['font_size_body']);

                                $char_width_attr = $line_width_attr / (mb_strlen($print_attr, 'UTF-8'));

                                $max_chars_attr = round($max_width_length / $char_width_attr);
                                $attr_trim2 = str_trim($print_attr, 'CHARS', $max_chars_attr, '...');

                                $page->drawText($attr_trim2, $col_title_position_attr3[1], $this->y, 'UTF-8');
                            }
                        }
                    }
                    if ($extra4_yn == 1) {
                        $print_attr = $sku_extra4[$sku];
                        if (strlen($print_attr) > 0) {
                            if ($combine_attribute_yn == 1)
                                $combine_attribute_array[$col_title_position_attr3[0]] = $print_attr;
                            else {
                                $next_col_to_attr = getPrevNext2($columns_xpos_array, $col_title_position_attr4[0], 'next', $padded_right);
                                $max_width_length = $next_col_to_attr - $col_title_position_attr4[1];

                                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $line_width_attr = $this->parseString($print_attr, $font_temp, $generalConfig['font_size_body']);

                                $char_width_attr = $line_width_attr / (strlen($print_attr));

                                $max_chars_attr = round($max_width_length / $char_width_attr);
                                $attr_trim2 = str_trim($print_attr, 'CHARS', $max_chars_attr, '...');

                                $page->drawText($attr_trim2, $col_title_position_attr4[1], $this->y, 'UTF-8');
                            }
                        }
                    }
                    // combine attribute 
                    $yTempPosCombine = $this->y;

                    if ($combine_attribute_yn == 1 && $combine_attribute_array != '') {
                        foreach ($combine_attribute_array as $key => $value_attribute) {
                            $page->drawText($value_attribute, $col_title_position_combine_attribute[1], $yTempPosCombine, 'UTF-8');
                            $yTempPosCombine -= ($generalConfig['font_size_body'] + 2);
                        }
                        $yTempPosCombine += ($generalConfig['font_size_body'] + 2);
                        unset($combine_attribute_array);
                    }
                    // weight
                    if ($combined_weight_yn == 1)
                        $page->drawText($sku_weight[$sku], $col_title_position_weight[1], $this->y, 'UTF-8');

                    if ($combined_total_product_paid_options != 0) {
                        if ($combined_total_product_paid_options == 3) {
                            $page->drawText(round(($value['paid_invoiced'] / $value['qty_invoiced']), 2), $col_total_paid_position_sku[1], $this->y, 'UTF-8');
                        } else
                            $page->drawText(round($value['paid'], 2), $col_total_paid_position_sku[1], $this->y, 'UTF-8');
                    }
                    //sku barcode
                    if ((($product_sku_barcode_yn == 1) || ($product_sku_barcode_yn == 2))) {
                        $barcode = $sku;
                        if ($product_sku_barcode_yn == 2)
                            $barcode = $this->getSkuBarcode($sku, $value['product_id'], $store_id);
                        $barcode_font_size = 10;
                        $barcode_Y = $this->y - 2;
                        $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($barcode, $barcode_type);
                        $barcodeWidth = 1.25 * $this->parseString($barcode, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                        $page->setFillColor($white_color);
                        $page->setLineColor($white_color);
                        $page->drawRectangle(($sku_barcodeX - 2), ($barcode_Y), ($sku_barcodeX + $barcodeWidth + 5), ($barcode_Y + ($barcode_font_size * 1.6)));
                        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
                        if ($barcodeWidth < ($padded_right / 2)) {
                            $page->drawText($barcodeString, ($sku_barcodeX), ($barcode_Y), 'CP1252');
                            //$after_print_barcode_y = $this->y - $barcode_font_size * 1.6;
                        } else {
                            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], '#FF3333');
                            $page->drawText("!! TRIMMED BARCODE !!", ($sku_barcodeX), ($barcode_Y), 'UTF-8');
                        }

                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    }
                    if ($warehouseyn == 1) {
                        /*****  Get Warehouse information ****/
                        if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) {
                            $warehouse_helper = Mage::helper('warehouse');
                            $warehouse_collection = Mage::getSingleton('warehouse/warehouse')->getCollection();
                            $resource = Mage::getSingleton('core/resource');
                            /**
                             * Retrieve the read connection
                             */
                            $readConnection = $resource->getConnection('core_read');
                            $query = 'SELECT stock_id FROM ' . $resource->getTableName("warehouse/order_grid_warehouse") . ' WHERE entity_id=' . $order->getData('entity_id');
                            $warehouse_stock_id = $readConnection->fetchOne($query);
                            if ($warehouse_stock_id) {
                                $warehouse = $warehouse_helper->getWarehouseByStockId($warehouse_stock_id);
                                $warehouse_title = ($warehouse->getData('title'));
                            } else
                                $warehouse_title = '';
                        } else
                            $warehouse_title = '';
                        $page->drawText($warehouse_title, intval($col_title_position_warehouse[1]), $this->y, 'UTF-8');
                    }


                    $options_splits = array();

                    if (isset($sku_order_id_options[$sku]) && $sku_order_id_options[$sku] != '' && $options_yn == 'newline') {
                        //print options after multi-rows name.
                        $this->y -= $lines_of_name * $generalConfig['font_size_body'];
                        $options_splits = array_filter(explode('newline', $sku_order_id_options[$sku]));
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $options_splits = $this->groupOptionProduct($options_splits);
                        foreach ($options_splits as $options_split => $qty_option) {
                            $temp_str1 = trim($qty_option);
                            $temp_str2 = $options_split;
                            $temp_str2 = trim($temp_str2);
                            $temp_str = array_filter(explode('] [', $temp_str2));
                            $temp_options_count = count($temp_str);
                            foreach ($temp_str as $str_key => $str_value) {
                                if ($this->y < 70) {
                                    if ($page_count == 1) {
                                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - ($generalConfig['font_size_subtitles'] * 2)), 'UTF-8');
                                    }
                                    $page = $this->newPage();
                                    $page_count++;
                                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                                }
                                $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                if ($str_key == 0) {
                                    if (strpos($str_value, ']') !== false) {
                                        $page->drawText($temp_str1, ($skuXreal + 4), $this->y, 'UTF-8');
                                        $page->drawText($str_value, ($skuXreal + 24), $this->y, 'UTF-8');
                                    } else {
                                        $page->drawText($temp_str1, ($skuXreal + 4), $this->y, 'UTF-8');
                                        $page->drawText($str_value . ']', ($skuXreal + 24), $this->y, 'UTF-8');
                                    }
                                } else
                                    $page->drawText('[' . $str_value, ($skuXreal + 24), $this->y, 'UTF-8');
                                $this->y -= 12;
                            }
                            if ($temp_options_count > 0) {
                                $this->y += 8;
                                //TODO remove the line after options?
                                $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));
                                $page->drawLine($skuXreal + 4, ($this->y), $skuXreal + 120, ($this->y));
                                $this->y -= 10;
                            } else
                                $this->y += 8;
                        }
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    } elseif (isset($sku_order_id_options[$sku]) && $sku_order_id_options[$sku] != '' && $options_yn == 'newskuparent') {
                        //TODO check logic here
                        $options_splits = array_filter(explode('newline', $sku_order_id_options[$sku]));
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $temp_skuparent_arr = array();
                        foreach ($options_splits as $key => $options_split) {
                            $temp_str1 = trim(substr($options_split, strpos($options_split, 'qty_ordered'), strlen($options_split)));
                            $temp_str1 = trim(str_replace('qty_ordered', '', $temp_str1));
                            $temp_str2 = trim(substr($options_split, 0, strpos($options_split, 'qty_ordered')));

                            $options_splits_new = array_filter(explode('[', $temp_str2));

                            foreach ($options_splits_new as $options_split_new) {
                                $options_split_new = trim('[' . $options_split_new);
                                if (!isset($temp_skuparent_arr[$options_split_new]) || $temp_skuparent_arr[$options_split_new] == '') {
                                    $temp_skuparent_arr[$options_split_new] = $temp_str1;
                                } else
                                    $temp_skuparent_arr[$options_split_new] += $temp_str1;
                            }

                        }

                        $k = 0;
                        $grey_bkg_color_option = new Zend_Pdf_Color_GrayScale(0.9);
                        foreach ($temp_skuparent_arr as $skuparent_key => $skuparent_value) {
                            if ((($k % 2) == 1)) {
                                $page->setFillColor($grey_bkg_color_option);
                                $page->setLineColor($grey_bkg_color_option);
                                $page->drawRectangle(25, ($this->y - (($generalConfig['font_size_body'] - 2) / 4)), $padded_right, ($this->y + ($generalConfig['font_size_body'] - 2)));
                                $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            }

                            if ($tickbox != 'no' && !in_array($sku, $pickpack_options_count_filter_array)) {
                                $page->setFillColor($white_color);
                                $page->setLineColor($black_color);
                                $page->setLineWidth(0.5);
                                $page->drawRectangle(28, ($this->y - 1), 34, ($this->y + 5));
                                $page->setFillColor($font_color_body_zend);
                                $page->setLineWidth(1);
                            }
                            $page->drawText($skuparent_value . ' x ' . $skuparent_key, ($skuXreal + 4), $this->y, 'UTF-8');
                            $k++;
                            $total_quantity += $skuparent_value;
                            //$option_total_quantity += $skuparent_value;
                            $this->y -= 12;

                        }
                        $this->y += 12;
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        if (is_array($options_sku_parent[$sku])) {
                            ksort($options_sku_parent[$sku]);


                            foreach ($options_sku_parent[$sku] as $options_sku_single => $options_sku_qty) {
                                if ($this->y < 70) {
                                    if ($page_count == 1) {
                                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - ($generalConfig['font_size_subtitles'] * 2)), 'UTF-8');
                                    }
                                    $page = $this->newPage();
                                    $page_count++;
                                    $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                                }

                                $options_sku_single = trim($options_sku_single);
                                $options_sku_qty = trim($options_sku_qty);


                                $sku_name[$options_sku_single] = trim(str_replace(array(
                                    '[',
                                    ']'
                                ), '', $sku_name[$options_sku_single]));
                                $sku_name[$options_sku_single] = preg_replace('~^select ~i', '', $sku_name[$options_sku_single]);
                                $sku_name[$options_sku_single] = preg_replace('~^enter ~i', '', $sku_name[$options_sku_single]);
                                $sku_name[$options_sku_single] = preg_replace('~^would you Like to ~i', '', $sku_name[$options_sku_single]);
                                $sku_name[$options_sku_single] = preg_replace('~\((.*)\)~i', '', $sku_name[$options_sku_single]);
                            }
                        }
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    }

                    if ($stockcheck_yn == 1) {
                        if ($sku_stock[$sku] < $stockcheck) {
                            $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                            $page->setFillColor($red_bkg_color);
                            $page->setLineColor(new Zend_Pdf_Color_Rgb(1, 1, 1));

                            if ($col_title_position_stock[1] > 1) {
                                $page->setLineColor($red_bkg_color);
                                $page->drawRectangle($col_title_position_stock[1] - 2 + 7, ($this->y - 1.5), ($col_title_position_stock[1] + ($generalConfig['font_size_body'] * (1.5 + (0.5 * strlen($sku_stock[$sku])))) + 2 + 7), ($this->y + ($generalConfig['font_size_body'] - 1.5)));
                                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                                $page->drawText('{ ! ' . $sku_stock[$sku] . '}', $col_title_position_stock[1] + 7, $this->y, 'UTF-8');
                            } else {
                                $this->y -= 12;
                                $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                                $warning = 'Stock Warning      ' . '    Net Stock After All Picks : ' . $sku_stock[$sku];
                                $page->drawText($warning, 60, $this->y, 'UTF-8');
                                $this->y -= 4;
                            }
                        }
                        // else {
                        // $page->drawText($sku_stock[$sku], $col_title_position_stock[1] + 7, $this->y, 'UTF-8');
                        // }
                    }
                    if ($combine_attribute_yn == 1 && $this->y > $yTempPosCombine)
                        $this->y = $yTempPosCombine;
                    if (isset($lines) && $lines != 0) {
                        $this->y -= (($lines - 1) * ($generalConfig['font_size_body'] + 3));
                        $lines = 0;
                        if ($doubleline_yn == 2)
                            $this->y -= 15;
                        if ($doubleline_yn == 1.5)
                            $this->y -= 7.5;
                        if ($doubleline_yn == 3)
                            $this->y -= 22.5;
                    } else {
                        if ($doubleline_yn == 2)
                            $this->y -= 2 * $generalConfig['font_size_body'];
                        else if ($doubleline_yn == 1.5)
                            $this->y -= 1.5 * $generalConfig['font_size_body'];
                        else if ($doubleline_yn == 3) {
                            $this->y -= 3 * $generalConfig['font_size_body'];
                        }

                    }

                    if (isset($has_shown_product_image) && ($has_shown_product_image == 1))
                        $this->y -= $generalConfig['font_size_body'] * 1.5;
                    elseif (isset($namey_after) && !isset($temp_options_count))
                        $this->y -= $namey_after;
                }
            }
            $thisYbase = ($this->y - 50);

            if ($from_shipment == 'shipment') {

                $thisYbase = ($thisYbase + ($generalConfig['font_size_body'] * 3));
                $this->y = $thisYbase;

                $box_top = $this->y;
                $box_bottom = ($this->y - (($generalConfig['font_size_body'] + 1) * (count($shipment_list) + 1)) - ($generalConfig['font_size_body'] * 2));
                $box_bottom_page2 = 0;

                if ($box_bottom < 60) {
                    // total height = box_top - box_bottom;
                    if ((($box_bottom * -1) + $box_bottom) < 0)
                        $box_bottom_page2 = $box_top + ($box_bottom - 60);
                    else
                        $box_bottom_page2 = ($box_top - $box_bottom + 60);

                    $box_bottom = 50;
                }
                $page->setFillColor($white_bkg_color);
                $page->setLineColor($orange_bkg_color);
                $page->setLineWidth(1);
                $page->drawRectangle(25, $box_bottom, 320, $box_top);

                $this->y -= ($generalConfig['font_size_body'] * 2);

                $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $page->drawText($helper->__('Shipments included') . ':', 35, $this->y, 'UTF-8');
                $this->y -= 5;
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                $i = 0;
                foreach ($shipment_list as $k => $value) {
                    if ($this->y < 70) {
                        if ($page_count == 1) {
                            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - ($generalConfig['font_size_subtitles'] * 2)), 'UTF-8');
                        }
                        $page = $this->newPage();
                        $page_count++;
                        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');

                        if ($page_count > 1) {
                            $page->setFillColor($white_bkg_color);
                            $page->setLineColor($orange_bkg_color);
                            $page->setLineWidth(1);
                            $page->drawRectangle(25, $box_bottom_page2, 320, ($page_top - ($generalConfig['font_size_body'] * 2)));
                            $this->y = ($page_top - ($generalConfig['font_size_body'] * 3));
                        }
                    }

                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                    $this->y -= ($generalConfig['font_size_body'] + 1);

                    $page->drawText('#' . $value . ' [' . $name_list[$k] . '] (order #' . $order_list[$k] . ')', 35, $this->y, 'UTF-8');
                    $i++;
                }
                $this->y += 30;
                $this->y += (15 * $i);
            }

            $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 2), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);

            if (($showcount_yn == 1) || ($showcount_shipped_yn == 1) || ($showcount_invoiced_yn == 1) || ($showcost_yn == 1) || ($order_count_yn == 1) || ($total_paid_subtotal_yn == 1) || (($combined_total_weight_yn == 1) && ($total_weight > 0))) {
                if ($page_count == 1) $this->y = $thisYbase;
                else $this->y -= $generalConfig['font_size_body'] * 2;
                $shipYbox = 0;
                //calculate totals.
                if ($showcount_yn == 1)
                    $shipYbox += ($generalConfig['font_size_body'] * 3);

                if ($showcount_shipped_yn == 1)
                    $shipYbox += ($generalConfig['font_size_body']);

                if ($showcount_invoiced_yn == 1)
                    $shipYbox += ($generalConfig['font_size_body']);

                if ($order_count_yn == 1)
                    $shipYbox += $generalConfig['font_size_body'];

                if ($showcost_yn == 1)
                    $shipYbox += $generalConfig['font_size_body'];

                if ($total_paid_subtotal_yn == 1)
                    $shipYbox += $generalConfig['font_size_body'];

                if ((($shipping_subtotal_yn == 1) && ($total_shipping_cost['shipping_plus_tax'] > 0)) || (($shipping_subtotal_yn == 2) && ($total_shipping_cost['shipping_ex_tax'] > 0)))
                    $shipYbox += $generalConfig['font_size_body'];

                if (($combined_total_weight_yn == 1) && ($total_weight > 0))
                    $shipYbox += $generalConfig['font_size_body'];

                if ($sheet_has_bundles === true)
                    $shipYbox += $generalConfig['font_size_body'];

                if ($error_product_count > 0)
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

            if ($showcount_yn == 1) {
               // $this->y -= ($generalConfig['font_size_body'] * 2);

                if ($from_shipment == 'shipment') {
					$this->writeSummary($helper->__('Total item quantity shipped this time'), $count, $page, 'none');
					$this->writeSummary($helper->__('Total item quantity shipped all time'), $count_all, $page, 'none');
					$this->writeSummary($helper->__('Total item quantity ordered'), $total_quantity, $page, 'none');					
					$this->writeSummary($helper->__('---------------------------------------------------'), '', $page, 'none');
					$this->writeSummary($helper->__('Qty remaining to be shipped'), $count, $page, 'none');
                } else
					$this->writeSummary($helper->__('Items') . $text_addon_totalqty, $total_quantity_ordered, $page, 'square');
            }

            if ($showcount_shipped_yn == 1 && $from_shipment != 'shipment')				
				$this->writeSummary($helper->__('Shipped items'), $total_quantity_shipped, $page, 'square');

            if ($showcount_invoiced_yn == 1 && $from_shipment != 'shipment')
				$this->writeSummary($helper->__('Invoiced items'), $total_quantity_invoiced, $page, 'square');

            if ($sheet_has_bundles === true) 
				$this->writeSummary($helper->__('Bundle'), $total_bundle_quantity, $page, 'circle');

            if ($order_count_yn == 1)
				$this->writeSummary($helper->__('Orders'), $order_count, $page, 'none');

            if (($showcost_yn == 1) && ($total_cost > 0)) {
                $this->y -= $generalConfig['font_size_body'] * 1.4;
                if ($from_shipment == 'shipment')
					$this->writeSummary($helper->__('Total cost these shipments'), $currency_symbol . '  ' . $total_cost, $page, 'none');
                else
					$this->writeSummary($helper->__('Total cost'), $currency_symbol . '  ' . $total_cost, $page, 'none');
            }

            if ($total_paid_subtotal_yn == 1) {
                $this->y -= $generalConfig['font_size_body'] * 1.4;
                if ($from_shipment == 'shipment')
					$this->writeSummary($helper->__('Total paid this shipment'), $currency_symbol . '  ' . round($total_paid, 2), $page, 'none');
                else
					$this->writeSummary($helper->__('Total paid'), $currency_symbol . '  ' . round($total_paid, 2), $page, 'none');
            }

            if ($shipping_subtotal_yn == 2) {
                if ($total_shipping_cost['shipping_ex_tax'] > 0)
					$this->writeSummary($helper->__('Total shipping paid (ex. tax)'), $shipping_currency . $shipping_currency_symbol . ' ' . round($total_shipping_cost['shipping_ex_tax'], 2), $page, 'none');
            } elseif ( ($shipping_subtotal_yn == 1) && ($total_shipping_cost['shipping_plus_tax'] > 0) )
				$this->writeSummary($helper->__('Total shipping paid (inc. tax)'), $shipping_currency . $shipping_currency_symbol . ' ' . round($total_shipping_cost['shipping_plus_tax'], 2), $page, 'none');

            if (($combined_total_weight_yn == 1) && ($total_weight > 0))
				$this->writeSummary($helper->__('Total weight'), round($total_weight, $combined_total_weight_rounding), $page, 'none');

            if ($error_product_count > 0)
				$this->writeSummary($helper->__('Error Product Count'), $error_product_count, $page, 'none');

            if ($printdates == 1) {
                $this->_setFont($page, $generalConfig['font_style_subtitles'], ($generalConfig['font_size_subtitles'] - 3), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                $page->drawText($helper->__('Printed') . ':   ' . date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())), 160, 18, 'UTF-8');
            }
        }

        $this->_afterGetPdf();
        return $pdf;
    }
}

?>