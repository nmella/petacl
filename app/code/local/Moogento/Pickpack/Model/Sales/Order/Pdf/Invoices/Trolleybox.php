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
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Trolleybox extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{

    protected function _getConfig($field, $default = '', $add_default = true, $group = 'trolleybox_picklist', $store = null, $trim = true,$section = 'trolleybox_options') {
        if($group=='general')
        {
            return parent::_getConfig($field,$default,$add_default,$group,$store);
        }
        if ($trim)
            $value = trim(Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store));
        else
            $value = Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store);
        if (strstr($field, '_color') !== FALSE) {
            if ($value != 0 && $value != 1) {
                $value = checkColor($value);
            }
        }
        
        if ($value == '') {
            return $default;
        } else {
            if ($field == 'csv_field_separator' && $value == ',')
                return $value;
            if (($value !== '') && (strpos($value, ',') !== false) && (strpos($default, ',') !== false)) 
                {
                $values   = explode(",", $value);
                $defaults = explode(",", $default);
                
                if ($add_default === true) {
                    $value         = '';
                    $count         = 0;
                    $default_count = count($defaults);
                    foreach ($defaults as $i => $v) {
                        if (($count != ($default_count)) && ($count != 0))
                            $value .= ',';
                        if (isset($values[$i]) && $values[$i] != '')
                            $value .= ($values[$i] + $defaults[$i]);
                        else
                            $value .= $v;
                        $count++;
                    }
                } else {
                    $value         = '';
                    $count         = 0;
                    $default_count = count($defaults);
                    foreach ($defaults as $i => $v) {
                        if (($count != ($default_count)) && ($count != 0))
                            $value .= ',';
                        if (isset($values[$i]) && $values[$i] != '')
                            $value .= $values[$i];
                        else
                            $value .= $v;
                        $count++;
                    }
                }
            } else {
                $value = ($add_default) ? ($value + $default) : $value;
            }
            return $value;
        }
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

//     public function getPickCombined($orders = array(), $output = 'order_combined')
    public function getPickCombined($orders = array(), $output = 'trolleybox') {
        $helper = Mage::helper('pickpack');
        $from_shipment = 'order';
        $trolleybox_yn = 0;
        $trolleybox_max = null;
        $show_orderid_with_trolleyboxid_yn = 0;
        $qty_yn = 1;
        $config_group = 'messages';
        $show_bundle_parent_yn = $this->_getConfig('show_bundle_parent', "no", false, 'general');
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
        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();

        $page_size = $this->_getConfig('page_size', 'a4', false, 'general');

        $padded_left = 20;
        if ($page_size == 'letter') {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $page_top = 770;
            $padded_right = 587;
        } elseif ($page_size == 'a4') {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $page_top = 820;
            $padded_right = 570;
        } elseif ($page_size == 'a5-landscape') {
            $page = $pdf->newPage('596:421');
            $page_top = 395;
            $padded_right = 573;
        } elseif ($page_size == 'a5-portrait') {
            $page = $pdf->newPage('421:596');
            $page_top = 573;
            $padded_right = 395;
        }

        $pdf->pages[] = $page;


        $productX = 250;
        $font_size_overall = 15;
        $font_size_productline = 9;
        $total_quantity = 0;
        $total_cost = 0;
        $error_product_count = 0;

        $shipping_subtotal_yn = $this->_getConfig('shipping_subtotal_yn', 0, false, $config_group);
        $total_paid_subtotal_yn = $this->_getConfig('total_paid_subtotal_yn', 0, false, $config_group);

        $red_bkg_color = new Zend_Pdf_Color_Html('lightCoral');
        $grey_bkg_color = new Zend_Pdf_Color_GrayScale(0.85);
        $alternate_row_color_temp = $this->_getConfig('alternate_row_color', '#DDDDDD', false, $config_group);
        $alternate_row_color = new Zend_Pdf_Color_Html($alternate_row_color_temp);
        $dk_grey_bkg_color = new Zend_Pdf_Color_GrayScale(0.3);
        $dk_cyan_bkg_color = new Zend_Pdf_Color_Html('darkCyan');
        $dk_og_bkg_color = new Zend_Pdf_Color_Html('darkOliveGreen');
        $white_bkg_color = new Zend_Pdf_Color_Html('white');
        $orange_bkg_color = new Zend_Pdf_Color_Html('Orange');
        $black_color = new Zend_Pdf_Color_Rgb(0, 0, 0);
        $grey_color = new Zend_Pdf_Color_GrayScale(0.3);
        $greyout_color = new Zend_Pdf_Color_GrayScale(0.6);
        $white_color = new Zend_Pdf_Color_GrayScale(1);

        $font_family_header_default = 'helvetica';
        $font_size_header_default = 16;
        $font_style_header_default = 'bolditalic';
        $font_color_header_default = 'darkOliveGreen';
        $font_family_subtitles_default = 'helvetica';
        $font_style_subtitles_default = 'bold';
        $font_size_subtitles_default = 15;
        $font_color_subtitles_default = '#222222';
        $background_color_subtitles_default = '#999999';
        $font_family_body_default = 'helvetica';
        $font_size_body_default = 10;
        $font_style_body_default = 'regular';
        $font_color_body_default = 'Black';

        $font_family_header = $this->_getConfig('font_family_header', $font_family_header_default, false, 'general');
        $font_style_header = $this->_getConfig('font_style_header', $font_style_header_default, false, 'general');
        $font_size_header = $this->_getConfig('font_size_header', $font_size_header_default, false, 'general');
        $font_color_header = trim($this->_getConfig('font_color_header', $font_color_header_default, false, 'general'));

        $font_family_body = $this->_getConfig('font_family_body', $font_family_body_default, false, 'general');
        $font_style_body = $this->_getConfig('font_style_body', $font_style_body_default, false, 'general');
        $font_size_body = $this->_getConfig('font_size_body', $font_size_body_default, false, 'general');
        $font_color_body = trim($this->_getConfig('font_color_body', $font_color_body_default, false, 'general'));


        $font_family_subtitles = $this->_getConfig('font_family_subtitles', $font_family_subtitles_default, false, 'general');
        $font_style_subtitles = $this->_getConfig('font_style_subtitles', $font_style_subtitles_default, false, 'general');
        $font_size_subtitles = $this->_getConfig('font_size_subtitles', $font_size_subtitles_default, false, 'general');
        $font_color_subtitles = $font_color_body; //trim($this->_getConfig('font_color_subtitles', $font_color_subtitles_default, false,'general'));
        $background_color_subtitles = trim($this->_getConfig('background_color_subtitles', $background_color_subtitles_default, false, 'general'));
        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($background_color_subtitles);

        $font_color_header_zend = new Zend_Pdf_Color_Html($font_color_header);
        $font_color_subtitles_zend = new Zend_Pdf_Color_Html($font_color_subtitles);
        $font_color_body_zend = new Zend_Pdf_Color_Html($font_color_body);


        $non_standard_characters = $this->_getConfig('non_standard_characters', 0, false, 'general');

        $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $store_id);
        switch ($barcode_type) {
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
            $product_images_maxdimensions = explode(',', str_ireplace('null', '', $this->_getConfig('product_images_maxdimensions', '50,', $config_group)));
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

        $media_path = Mage::getBaseDir('media');

        $product_id = NULL;
        $stock = NULL;
        $sku_stock = array();
        $sku_qty = array();
        $sku_cost = array();
        $showcost_yn_default = 0;
        $showcount_yn_default = 0;
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
        } 
        elseif ($sort_packing == 'sku') {
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

        $tickbox = $this->_getConfig('combined_tickbox_yn', $tickbox_default, false, $config_group);
        $tickbox_title_X = $this->_getConfig('col_title_position_tickbox', 7, false, $config_group);
        $tickbox2 = $this->_getConfig('combined_tickbox2_yn', $tickbox_default, false, $config_group);
        $tickbox2_title_X = $this->_getConfig('col_title_position_tickbox2', 27, false, $config_group);
        $tickbox_title_X = explode(',', $tickbox_title_X);
        $tickbox_title = $tickbox_title_X[0];
        if (count($tickbox_title) > 1)
            $tickbox_X = $tickbox_title_X[1];
        $tickbox2_title_X = explode(',', $tickbox2_title_X);
        $tickbox2_X = 0;
        if (is_array($tickbox2_title_X)) {
            $tickbox2_title = $tickbox2_title_X[0];
            if (count($tickbox2_title_X) > 1)
                $tickbox2_X = $tickbox2_title_X[1];
        }
        
        $picklogo = $this->_getConfig('pickpack_picklogo', 0, false, 'general');

        $logo_maxdimensions = explode(',', '269,41');

        $showcount_yn = $this->_getConfig('pickpack_count', $showcount_yn_default, false, $config_group);
        $order_count_yn = $this->_getConfig('order_count_yn', 1, false, $config_group);

        $showcost_yn = $this->_getConfig('pickpack_cost', $showcost_yn_default, false, $config_group);
        $currency = $this->_getConfig('pickpack_currency', $currency_default, false, $config_group);
        $currency_symbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
        $shipping_currency = Mage::app()->getConfig()->getNode('default/' . Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE);
        $shipping_currency_symbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        $stockcheck_yn = $this->_getConfig('pickpack_showstock_yn_combined', 0, false, $config_group);
        $stockcheck = $this->_getConfig('pickpack_stock_combined', $stockcheck_default, false, $config_group);

        $sku_yn = $this->_getConfig('sku_yn', 1, false, $config_group);
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

        if ($shelving_yn == 0) {
            $extra_yn = 0;
            $extra3_yn = 0;
        }

        if ($extra_yn == 0) {
            $extra3_yn = 0;
        }

        $stock_qty_yn = $this->_getConfig('combined_stock_qty', 0, false, $config_group);
        $nameyn = $this->_getConfig('pickpack_name_yn_combined', 0, false, $config_group);
        $combined_weight_yn = $this->_getConfig('combined_weight_yn', 0, false, $config_group);
        $combined_weight_rounding = $this->_getConfig('combined_weight_rounding', 2, false, $config_group);
        $combined_total_weight_yn = $this->_getConfig('combined_total_weight_yn', 0, false, $config_group);
        $combined_total_weight_rounding = $combined_weight_rounding;
        // warehouse
        $warehouseyn = $this->_getConfig('combined_warehouse_yn', 0, false, $config_group);
        $col_title_position_warehouse = explode(',', $this->_getConfig('col_title_position_warehouse', 'Qty,50', false, $config_group));

        $col_title_position_qty = explode(',', $this->_getConfig('col_title_position_qty', 'Qty,20', false, $config_group));
        $col_title_position_sku = explode(',', $this->_getConfig('col_title_position_sku', 'Sku,80', false, $config_group));
        $col_title_position_name = explode(',', $this->_getConfig('col_title_position_name', 'Name,150', false, $config_group));
        $col_title_position_weight = explode(',', $this->_getConfig('col_title_position_weight', 'Weight,320', false, $config_group));
        $col_title_position_trolleybox = explode(',', $this->_getConfig('col_title_position_trolleybox', 'Trolleybox,350', false, $config_group));
        $col_title_position_stock = explode(',', $this->_getConfig('col_title_position_stock', 'Stock,500', false, $config_group));
        $col_title_position_attr1 = explode(',', $this->_getConfig('col_title_position_attr1', 'Attr.1,410', false, $config_group));
        $col_title_position_attr2 = explode(',', $this->_getConfig('col_title_position_attr2', 'Attr.2,460', false, $config_group));
        $col_title_position_attr3 = explode(',', $this->_getConfig('col_title_position_attr3', 'Attr.3,360', false, $config_group));

        $col_title_product_stock_qty = explode(',', $this->_getConfig('col_title_product_stock_qty', 'Stock qty,460', false, $config_group));
        $col_title_position_productsku_barcode = explode(',', $this->_getConfig('col_title_position_productsku_barcode', 'BarcodeSku,300', false, $config_group));

        $sku_barcodeX = $col_title_position_productsku_barcode[1];
        $store_view = $this->_getConfig('name_store_view', 'storeview', false, "messages");
        $specific_store_id = $this->_getConfig('specific_store', '', false, "messages", $store_id);
        $columns_xpos_array = array();

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
                if ($sort_packing_attribute != null && $sort_packing_attribute != 'none') {
                    $sku_master[$sku][$sort_packing_attribute] = $this->createArraySort($sort_packing_attribute, $sku_master, $sku, $product_id, null);
                }
                
                if ($sort_packing_secondary != 'none' && $sort_packing_secondary != '') {
                    $sku_master[$sku][$sort_packing_secondary] = $this->createArraySort($sort_packing_secondary, $sku_master, $sku, $product_id, null);
                }
                
                if (count($order->getAllVisibleItems()) > 1)
                    $sku_master[$sku]['sort_item_count'] = 2;
                else
                    $sku_master[$sku]['sort_item_count'] = 1;
				
                if ($shelving_yn == 1) {
                    $shelving = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute);
                    $shelving = trim($shelving);
                    if ($shelving != '') {
                        if (isset($sku_shelving[$sku]) && strtoupper($sku_shelving[$sku]) != strtoupper($shelving))
                            $sku_shelving[$sku] .= ',' . $shelving;
                        else
                            $sku_shelving[$sku] = $shelving;
                        $sku_shelving[$sku] = preg_replace('~,$~', '', $sku_shelving[$sku]);
                        $sku_master[$sku]['attribute_1'] = $sku_shelving[$sku];
                        unset($shelving);
                    }
                }
                if ($extra_yn == 1) {
                    $extra_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra_attribute);
                    $extra_var = trim($extra_var);
                    $sku_master[$sku]['extra'] = $extra_var;
                    $sku_master[$sku]['attribute_2'] = $extra_var;
                    $sku_extra[$sku] = $extra_var;
                    unset($extra_var);
                }
                if ($extra3_yn == 1) {
                    $extra3_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra3_attribute);
                    $extra3_var = trim($extra3_var);
                    $sku_master[$sku]['extra3'] = $extra3_var;
                    $sku_master[$sku]['attribute_3'] = $extra3_var;
                    $sku_extra3[$sku] = $extra3_var;
                    unset($extra3_var);
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
                } 
                else {
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
                } 
                else {
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
                } 
                else {
                    if ($store_view == "storeview")
                        $name = trim($item->getName());
                    elseif ($store_view == "specificstore" && $specific_store_id != "") {
                        $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
                        if ($_newProduct->getData('name')) $name = trim($_newProduct->getData('name'));
                        if ($sku_name[$sku] == '') $name = trim($item->getName());

                    } else
                        $name = $this->getNameDefaultStore($item);
                }
                
                if (Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) {
                    $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
                } 
                else
                    $stock = 0;
                    
                $sku_name[$sku] = $name;
                $sku_type[$sku] = 'normal';
                $sku_master[$sku]['sku'] = $sku;
                $sku_master[$sku]['name'] = $sku_name[$sku];
                $sku_master[$sku]['product_type'] = 'normal';
                $sku_master[$sku]['stock_qty'] = $stock; //$item->getProduct()->getData('stock_item')->getData('qty');


                if ($output == 'trolleybox') {
                    $trolleybox_this_order_id = $order->getRealOrderId();
                    if (!isset($trolleybox_item_id))
                        $trolleybox_item_id = 0;
                    else
                        $trolleybox_item_id++;

                     if (!isset($trolleybox_trolley_id)) {
                            $trolleybox_trolley_id = 1;
                            $oneortwoprevious = $sku_master[$sku]['sort_item_count'];
                            $sku_master[$sku]['flag_trolley'] = 0;
                        }

                        if ($sku_master[$sku]['sort_item_count'] != $oneortwoprevious) {
                            $flag_trolley_id = $trolleybox_trolley_id;
                            $oneortwoprevious = $sku_master[$sku]['sort_item_count'];

                        }

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
					
					
                    $flat_item_list[$trolleybox_item_id]['sku'] = $sku;
                    $flat_item_list[$trolleybox_item_id]['name'] = $name;
                    $flat_item_list[$trolleybox_item_id]['order_id'] = $trolleybox_this_order_id;
                    $flat_item_list[$trolleybox_item_id]['trolleybox_box_id'] = $trolleybox_box_id;
                    $flat_item_list[$trolleybox_item_id]['trolleybox_trolley_id'] = $trolleybox_trolley_id;
                    $flat_item_list[$trolleybox_item_id]['sort']  = $sku_master[$sku][$sort_packing_attribute];
                    $flat_item_list[$trolleybox_item_id]['sort_packing_attribute'] = $sku_master[$sku][$sort_packing_attribute];
                    $flat_item_list[$trolleybox_item_id]['sort_item_count'] = $sku_master[$sku]['sort_item_count'];
                    // $flat_item_list[$trolleybox_item_id]['stock_qty'] = $sku_master[$sku]['stock'];
                    $flat_item_list[$trolleybox_item_id]['product_id'] = $product_id;
                    $flat_item_list[$trolleybox_item_id]['product_type'] = $sku_master[$sku]['product_type'];
                    
                    $flat_item_list[$trolleybox_item_id]['qty'] = $item->getData('qty_ordered');                          
                    $flat_item_list[$trolleybox_item_id]['qty_invoice'] = $item->getData('qty_invoiced'); 
                    $flat_item_list[$trolleybox_item_id]['qty_shipped'] = $item->getData('qty_shipped'); 
					if ($shelving_yn == 1) {
						if(isset($sku_master[$sku]['attribute_1']))
						$flat_item_list[$trolleybox_item_id]['attribute_1'] = $sku_master[$sku]['attribute_1'];
					}
					if ($extra_yn == 1) {
						if(isset($sku_master[$sku]['attribute_2']))
						$flat_item_list[$trolleybox_item_id]['attribute_2'] = $sku_master[$sku]['attribute_2'];
					}
					if ($extra3_yn == 1) {
						if(isset($sku_master[$sku]['attribute_3']))
						$flat_item_list[$trolleybox_item_id]['attribute_3'] = $sku_master[$sku]['attribute_3'];
					}	


//                     $sku_master[$sku]['attribute_2'] 
                                             

                    // $sku_master[$sku]['product_type']
                    // $sku_master[$sku]['weight'] = $unit_weight;
                    // $sku_master[$sku]['paid'] = $unit_paid;
                    // $sku_master[$sku]['qty'] = $qty;
                    // $sku_master[$sku]['supplier'] = $supplier;

                    // $sku_master[$sku]['extra'] = $extra_var;
                    // $sku_master[$sku]['extra3'] = $extra3_var;

                    // $sku_master[$sku][$sort_packing_attribute]
                    // $sku_master[$sku][$sort_packing_secondary]
                }

                // bundle SKUs
                //$options = $item->getProductOptions();
                if (isset($options['bundle_options']) && is_array($options['bundle_options'])) {
                    $sku_master[$sku]['product_type'] = 'bundle-parent';
                    $flat_item_list[$trolleybox_item_id]['product_type'] = $sku_master[$sku]['product_type'];
                    $children = $item->getChildrenItems();
                    if (count($children) > 0) {
                        foreach ($children as $child) {
                            $child_product_id = $child->getProductId();

                            $sku_b = $child->getSku();

							$product = Mage::getModel('catalog/product')->setStoreId($store_id)->loadByAttribute('sku', $sku_b, array(
												'cost',
												'name',
												'simple_sku',
												'qty',
												$sort_packing_attribute,
												$shelving_attribute,
												$extra_attribute,
												$extra3_attribute,
												$supplier_attribute
											));
				

                            if(strlen(trim($sku_b)) == 0)
                                continue;
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
                          
                            $sku_weight[$sku_b] = $unit_weight;
                            $unit_weight = null;

                            $unit_paid = 0;
                            $qty_invoiced = 0;
                            $paid_invoiced = 0;
                            


                            if (Mage::getModel('cataloginventory/stock_item')->loadByProduct($child_product_id)->getQty()) {
                                $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($child_product_id)->getQty());
                            } else
                                $stock = 0;

                           if ($shelving_yn == 1) {
								$shelving = Mage::helper('pickpack')->getProductAttributeValue($product, $shelving_attribute);
								$shelving = trim($shelving);
								if ($shelving != '') {
									if (isset($sku_shelving[$sku_b]) && strtoupper($sku_shelving[$sku_b]) != strtoupper($shelving))
										$sku_shelving[$sku_b] .= ',' . $shelving;
									else
										$sku_shelving[$sku_b] = $shelving;
									$sku_shelving[$sku_b] = preg_replace('~,$~', '', $sku_shelving[$sku_b]);
									$sku_master[$sku_b]['attribute_1'] = $sku_shelving[$sku_b];
									unset($shelving);
								}
							}
							if ($extra_yn == 1) {
								$extra_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra_attribute);
								$extra_var = trim($extra_var);
								$sku_master[$sku_b]['extra'] = $extra_var;
								$sku_master[$sku_b]['attribute_2'] = $extra_var;
								$sku_extra[$sku_b] = $extra_var;
								unset($extra_var);
							}
							if ($extra3_yn == 1) {
								$extra3_var = Mage::helper('pickpack')->getProductAttributeValue($product, $extra3_attribute);
								$extra3_var = trim($extra3_var);
								$sku_master[$sku_b]['extra3'] = $extra3_var;
								$sku_master[$sku_b]['attribute_3'] = $extra3_var;
								$sku_extra3[$sku_b] = $extra3_var;
								unset($extra3_var);
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
                            $character_breakpoint_name = stringBreak($name_b, $max_flat_name_width, $font_size_body);

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

                            /**
                             * Bundled Trolley
                             */

							$sku_parent = $sku;
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
                                $flat_item_list[$trolleybox_item_id]['sort'] = $sku_master[$sku_b][$sort_packing_attribute];                                
                                $flat_item_list[$trolleybox_item_id]['sort_packing_attribute'] = $sku_master[$sku_b][$sort_packing_attribute];
                                $flat_item_list[$trolleybox_item_id]['sort_item_count'] = $sku_master[$sku_parent]['sort_item_count'];
                                $flat_item_list[$trolleybox_item_id]['stock_qty'] = $stock;
                                $flat_item_list[$trolleybox_item_id]['product_id'] = $child->getProductId(); 
                                $flat_item_list[$trolleybox_item_id]['product_type'] = 'bundle-child';
                                
                                $flat_item_list[$trolleybox_item_id]['qty'] = $child->getData('qty_ordered');                          
                                $flat_item_list[$trolleybox_item_id]['qty_invoice'] = $child->getData('qty_invoiced'); 
                                $flat_item_list[$trolleybox_item_id]['qty_shipped'] = $child->getData('qty_shipped'); 
								if ($shelving_yn == 1) {
									if(isset($sku_master[$sku_b]['attribute_1']))
										$flat_item_list[$trolleybox_item_id]['attribute_1'] = $sku_master[$sku_b]['attribute_1'];
								}
								if ($extra_yn == 1) {
									if(isset($sku_master[$sku_b]['attribute_2']))
										$flat_item_list[$trolleybox_item_id]['attribute_2'] = $sku_master[$sku_b]['attribute_2'];
								}
								if ($extra3_yn == 1) {
									if(isset($sku_master[$sku_b]['attribute_3']))
										$flat_item_list[$trolleybox_item_id]['attribute_3'] = $sku_master[$sku_b]['attribute_3'];
								}	
                            }
                            /**
                             * END bundled trolley
                             */
                        }
                    }
                }              

                $coun++;
            }
            $i++;
        }
                

        $sortorder_packing_bool = false;

        $supplier_previous = '';
        $supplier_item_action = '';


        $first_page_yn = 'y';
        if ($output == 'trolleybox') {
            unset($sku_master);
            $sku_master = $flat_item_list;
        }
		//Sort : number -- Uppercase -- lowercase
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
        }
        elseif (($sort_packing != 'none') && ($output == 'trolleybox')) {
            // Obtain a list of columns
            foreach ($sku_master as $key => $row) {
                $trolley_id[$key] = $row['trolleybox_trolley_id'];
                $sort[$key] = strtolower($row['sort']);
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
       
        $this->y = ($page_top - 43);

        $total_quantity = 0;
        $total_bundle_quantity = 0;
        $total_cost = 0;
        $sheet_has_bundles = false;



        $this->_setFont($page, $font_style_header, ($font_size_header + 2), $font_family_header, $non_standard_characters, $font_color_header);

        $mage_timestamp = Mage::getModel('core/date')->timestamp(time());
        $date_format = preg_replace('~(.*): ~', '', $date_format);
        $order_date = date($date_format, $mage_timestamp);

        $picklist_title = '';        

        $picklist_title .= $helper->__('Trolleybox Pick List');
        $page->drawText($picklist_title, 31, ($page_top - 10), 'UTF-8');

        $page->setLineColor($font_color_header_zend);
        $page->setFillColor($font_color_header_zend);
        $page->drawRectangle(27, ($page_top - 17), $padded_right, ($page_top - 16));
        $max_qty_length_display = 0;
        $skuXreal = $col_title_position_sku[1];

        if ($skuXreal > $col_title_position_attr2[1]) {
            $maxWidthSku = ($padded_right - $skuXreal + 20);
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $font_size_compare = ($font_size_body);
            $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare);
            $char_width = $line_width / 10;
            $max_chars = round($maxWidthSku / $char_width);
        }

        $processed_skus = array();

        $page_count = 1;
        $grey_next_line = 0;
        $thisYnext = 0;
        $thisYorig = 0;


        $this->_setFont($page, 'bold', $font_size_body + 2, $font_family_body, $non_standard_characters, $font_color_subtitles);

        if ($qty_yn == 1)
            $page->drawText(Mage::helper('sales')->__($col_title_position_qty[0]), $col_title_position_qty[1], $this->y, 'UTF-8');

        if ($stock_qty_yn == 1)
            $page->drawText(Mage::helper('sales')->__($col_title_product_stock_qty[0]), $col_title_product_stock_qty[1], $this->y, 'UTF-8');

        if ($nameyn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_name[0]), ($col_title_position_name[1] + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');
        }

        if ($warehouseyn == 1)
            $page->drawText($col_title_position_warehouse[0], intval($col_title_position_warehouse[1]), $this->y, 'UTF-8');

        if ($combined_weight_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_weight[0]), ($col_title_position_weight[1] + $productXInc + $first_item_title_shift_items), $this->y, 'UTF-8');
        }

        if ($product_images_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_product_images[0]), ($col_title_product_images[1]), $this->y, 'UTF-8');
        }

        if ($sku_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_sku[0]), ($col_title_position_sku[1]), $this->y, 'UTF-8');
        }

        if ($combined_total_product_paid_options != 0) {
            $page->drawText(Mage::helper('sales')->__($col_total_paid_position_sku[0]), ($col_total_paid_position_sku[1]), $this->y, 'UTF-8');
        }

        if ($shelving_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_attr1[0]), ($col_title_position_attr1[1]), $this->y, 'UTF-8');
        }

        if ($extra_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_attr2[0]), ($col_title_position_attr2[1]), $this->y, 'UTF-8');
        }

        if ($extra3_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_attr3[0]), ($col_title_position_attr3[1]), $this->y, 'UTF-8');
        }

        if ($stockcheck_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_stock[0]), $col_title_position_stock[1], $this->y, 'UTF-8');
        }

        if ($trolleybox_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_trolleybox[0]), $col_title_position_trolleybox[1], $this->y, 'UTF-8');
        }

        if ($product_sku_barcode_yn == 1) {
            $page->drawText(Mage::helper('sales')->__($col_title_position_productsku_barcode[0]), $col_title_position_productsku_barcode[1], $this->y, 'UTF-8');
        }

        $this->y -= ($font_size_subtitles * 1.5);
        $sku_master_runcount = 0;
        $trolleybox_current_trolley_id = 1;


        foreach ($sku_master as $key => $value) {
            $sku_master_runcount++;
            if ($sku_master[$key]['product_type'] == 'bundle-parent')
                if($show_bundle_parent_yn == 0)
                {
                    continue;
                }
                else
                    $sheet_has_bundles = true;

            if (($this->y < 60) || (($output == 'trolleybox') && ($sku_master[$key]['trolleybox_trolley_id'] != $trolleybox_current_trolley_id))) {
                if ($output == 'trolleybox')
                    $trolleybox_current_trolley_id = $sku_master[$key]['trolleybox_trolley_id'];
                if ($page_count == 1) {
                    $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - 15), 'UTF-8');
                }
                $page = $this->newPage();
                $page_count++;
                $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
            }

            if (isset($value['sku']))
            {
                if($sku_master[$key]['product_type'] == 'bundle-parent')
                    $sku = '(Bundle) '.$value['sku'];    
                else
                    if($sku_master[$key]['product_type'] == 'bundle-child')
                    $sku = '(Child) '.$value['sku'];
                else
                   $sku = $value['sku'];
           }
            else
                $sku = 'Bundle';

            $this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
            if ((!in_array($sku, $processed_skus) || ($trolleybox_yn == 1)) && $sku != '') {
                $processed_skus[] = $sku;
               if($value['product_type'] != 'bundle-parent')
                    $total_quantity += $value['qty'];
               else               
                    $total_bundle_quantity += $value['qty'];
               
                if (!isset($sku_cost[$sku]))
                    $sku_cost[$sku] = 0;
                if ($grey_next_line == 1) {
                    $page->setFillColor($grey_bkg_color);
                    $page->setLineColor($grey_bkg_color);

                    $grey_box_y1 = ($this->y - ($font_size_body / 5));
                    $grey_box_y2 = ($this->y + ($font_size_body * 0.85));

                    if (($product_images_yn == 1) && isset($sku_image_paths[$sku]['path'][0])) {
                        $grey_box_y1 = ($this->y - $sku_image_paths[$sku]['height'] - 1.5);
                        $grey_box_y2 = ($this->y + 1.5);
                    } else {
                        $grey_box_y1 -= ($font_size_body + 1);
                        $grey_box_y2 -= $font_size_body;
                    }
                    if (isset($sku_order_id_options[$sku]) && $sku_order_id_options[$sku] != '' && $options_yn == 'newline') {
                        $line_count = $this->getLineCountOption($sku_order_id_options[$sku]);
                        $grey_box_y1 = ($this->y - $font_size_body / 5 - ($line_count + 2) * $font_size_body - count(array_filter(explode('newline', $sku_order_id_options[$sku]))));
                        //$grey_box_y2 = ($this->y + 1.5);
                    }
                    $page->drawRectangle(25, $grey_box_y1, $padded_right, $grey_box_y2);
                    $grey_next_line = 0;
                } else
                    $grey_next_line = 1;
                $has_shown_product_image = 0;

                $this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
                //1. Print ticbox
                if ($tickbox != 0) {
                    $page->setLineWidth(1.5);
                    $page->setFillColor($white_color);
                    $page->setLineColor($black_color);
                    if ($sku_master[$sku]['product_type'] == 'bundle-parent') {
                        $page->drawCircle($tickbox_X + 3.5, ($this->y + 3.5), 3.5);
                        $sheet_has_bundles = true;
                    } else
                        $page->drawRectangle($tickbox_X, ($this->y), $tickbox_X + 7, ($this->y + 7));
                    if ($tickbox2 != 0) {
                        if ($sku_master[$sku]['product_type'] == 'bundle-parent') {
                            $page->drawCircle($tickbox2_X + 3.5, ($this->y + 3.5), 3.5);
                            $sheet_has_bundles = true;
                        } else
                            $page->drawRectangle($tickbox2_X, ($this->y), $tickbox2_X + 7, ($this->y + 7));
                    }
                    $page->setFillColor($font_color_body_zend);
                }

                //2. Print qty 
                /*get qty string**/
                $qty_string = $value['qty'];
                $qty_string = round($qty_string, 2);
                $qtyX = $col_title_position_qty[1];
                $red_color              = new Zend_Pdf_Color_Html('darkRed');
				$product_qty_upsize_yn  = $this->_getConfig('product_qty_upsize_yn', 0, false, $config_group);
				$product_qty_underlined = 0;
				$product_qty_red        = 0;
				$product_qty_rectangle  = 0;
				if ($product_qty_upsize_yn == 'u' || $product_qty_upsize_yn == 'c' || $product_qty_upsize_yn == 'b') {
					if ($product_qty_upsize_yn == 'c')
						$product_qty_red = 1;
					if ($product_qty_upsize_yn == 'b')
						$product_qty_rectangle = 1;
					$product_qty_upsize_yn  = 1;
					$product_qty_underlined = 1;
				}
                if ($product_qty_upsize_yn == 1 && $qty_string > 1) {
                    if ($product_qty_red == 1)
                        $this->_setFont($page, 'bold', ($font_size_body + 1), $font_family_body, $non_standard_characters, 'darkRed');
                    if ($product_qty_rectangle == 1) {
                        $page->setLineWidth(1);
                        $page->setLineColor($black_color);
                        $page->setFillColor($black_color);
                        if (($qty_string >= 100) || (strlen($qty_string) > 3))
                            $page->drawRectangle(($qtyX), ($this->y - 1), ($qtyX - 18 + (strlen($qty_string) * $font_size_body)), ($this->y - 4 + $font_size_body * 1.2));
                        else if (($qty_string >= 10) || (strlen($qty_string) >= 2))
                            $page->drawRectangle(($qtyX - 1), ($this->y - 1), ($qtyX - 8 + (strlen($qty_string) * $font_size_body)), ($this->y - 4 + $font_size_body * 1.2));
                        else
                            $page->drawRectangle(($qtyX - 1), ($this->y - 1), ($qtyX - 2 + (strlen($qty_string) * $font_size_body)), ($this->y - 4 + $font_size_body * 1.2));
                        $this->_setFont($page, 'bold', ($font_size_body + 1), $font_family_body, $non_standard_characters, 'white');
                        $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                    } else {
                        if ($product_qty_underlined == 1) {
                            $page->setLineWidth(1);
                            $page->setLineColor($black_color);
                            $page->setFillColor($white_color);
                            if ($product_qty_red == 1)
                                $page->setLineColor($red_color);
                            $page->drawLine(($qtyX - 1), ($this->y - 1), ($qtyX - 3 + (strlen($qty_string) * $font_size_body)), ($this->y - 1));
                        }
                        $this->_setFont($page, 'bold', ($font_size_body), $font_family_body, $non_standard_characters, $font_color_body);
                        $page->drawText($qty_string, ($qtyX), ($this->y), 'UTF-8');
                    }
                    $this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
                } else
                    $page->drawText($qty_string, $qtyX, $this->y, 'UTF-8');

                //3. Print stock
                if ($stock_qty_yn == 1)
                    $page->drawText(round($value['stock_qty'], 0), $col_title_product_stock_qty[1], $this->y, 'UTF-8');

                /***************/
                $name_addon = '';
                $sku_addon = '';

                //4. Print shelving
                if ($shelving_yn == 1) {
                	if(isset($value['attribute_1']))
                	{
	                	$show_this = $value['attribute_1'];
                    	if (strlen($show_this)>0) {
							$print_attr = $show_this;
							$next_col_to_attr = getPrevNext2($columns_xpos_array, $col_title_position_attr1[0], 'next', $padded_right);
							$max_width_length = $next_col_to_attr - $col_title_position_attr1[1];
							$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
							$line_width_attr = $this->parseString($print_attr, $font_temp, $font_size_body);
							$char_width_attr = $line_width_attr / (strlen($print_attr));
							$max_chars_attr = round($max_width_length / $char_width_attr);
							$attr_trim2 = str_trim($print_attr, 'CHARS', $max_chars_attr, '...');
							$page->drawText($attr_trim2, $col_title_position_attr1[1], $this->y, 'UTF-8');
						} 
					}
                }

                if ($extra_yn == 1) {
                    if(isset($value['attribute_2']))
                	{
	                	$print_attr = $value['attribute_2'];
						if (strlen($print_attr) > 0) {
							$next_col_to_attr = getPrevNext2($columns_xpos_array, $col_title_position_attr2[0], 'next', $padded_right);
							$max_width_length = $next_col_to_attr - $col_title_position_attr2[1];
							$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
							$line_width_attr = $this->parseString($print_attr, $font_temp, $font_size_body);
							$char_width_attr = $line_width_attr / (strlen($print_attr));
							$max_chars_attr = round($max_width_length / $char_width_attr);
							$attr_trim2 = str_trim($print_attr, 'CHARS', $max_chars_attr, '...');
							$page->drawText($attr_trim2, $col_title_position_attr2[1], $this->y, 'UTF-8');
						}
					}
                }

                if ($extra3_yn == 1) {
                    if(isset($value['attribute_3']))
                	{
	                	$print_attr = $value['attribute_3'];
						if (strlen($print_attr) > 0) {
							$next_col_to_attr = getPrevNext2($columns_xpos_array, $col_title_position_attr3[0], 'next', $padded_right);
							$max_width_length = $next_col_to_attr - $col_title_position_attr3[1];

							$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
							$line_width_attr = $this->parseString($print_attr, $font_temp, $font_size_body);

							$char_width_attr = $line_width_attr / (strlen($print_attr));

							$max_chars_attr = round($max_width_length / $char_width_attr);
							$attr_trim2 = str_trim($print_attr, 'CHARS', $max_chars_attr, '...');

							$page->drawText($attr_trim2, $col_title_position_attr3[1], $this->y, 'UTF-8');
						}
					}
                }

                //5. Print trolleybox id

                if ($trolleybox_yn == 1) {
                    $trolley_id_display = $sku_master[$key]['trolleybox_box_id'];
                    if ($show_orderid_with_trolleyboxid_yn == 1)
                        $trolley_id_display .= ' (' . $sku_master[$key]['order_id'] . ')';
                    $page->drawText($trolley_id_display, $col_title_position_trolleybox[1], $this->y, 'UTF-8');
                }

                $display_sku = $value['sku'];
               
                /*********************/
                $red_color = new Zend_Pdf_Color_Html('darkRed');
                $product_qty_upsize_yn = $this->_getConfig('product_qty_upsize_yn', 0, false, $config_group);
                $product_qty_underlined = 0;
                $product_qty_red = 0;
                $product_qty_rectangle = 0;
                if ($product_qty_upsize_yn == 'u' || $product_qty_upsize_yn == 'c' || $product_qty_upsize_yn == 'b') {
                    if ($product_qty_upsize_yn == 'c')
                        $product_qty_red = 1;
                    if ($product_qty_upsize_yn == 'b')
                        $product_qty_rectangle = 1;
                    $product_qty_upsize_yn = 1;
                    $product_qty_underlined = 1;
                }
                $stock_qty_X = $col_title_product_stock_qty[1];

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
                        } else {
                            $warehouse_title = '';
                        }
                    } else {
                        $warehouse_title = '';
                    }
                    $page->drawText($warehouse_title, intval($col_title_position_warehouse[1]), $this->y, 'UTF-8');
                }

                if ($nameyn == 1) {
                    if (isset($options_sku_parent) && is_array($options_sku_parent[$sku])) {
                        $page->drawText($value['name'] . $name_addon, $col_title_position_name[1], $this->y, 'UTF-8');
                        $this->y -= ($font_size_body + 3);
                    } else {
                        $print_name = $value['name'] . $name_addon;
                        if ($print_name != '') {
                            $next_col_to_name = getPrevNext2($columns_xpos_array, 'Name', 'next', $padded_right);
                            $max_width_length = ($next_col_to_name - $col_title_position_name[1]);
                            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            $line_width_name = $this->parseString($print_name, $font_temp, $font_size_body);
                            $char_width_name = $line_width_name / (strlen($print_name));
                            $max_chars_name = round($max_width_length / $char_width_name);
                            $name_trim = str_trim($print_name, 'WORDS', $max_chars_name - 3, '...');
                            $name_trim2 = str_trim($print_name, 'CHARS', $max_chars_name - 3, '...');
                            $page->drawText($name_trim, $col_title_position_name[1], $this->y, 'UTF-8');
                        }
                    }
                }

                if ($sku_yn ==1)
                    $page->drawText($sku . $sku_addon, $skuXreal, $this->y, 'UTF-8');

                $options_splits = array();

                if (isset($sku_order_id_options[$sku]) && $sku_order_id_options[$sku] != '' && $options_yn == 'newline') {
                    $this->y -= 12;
                    $options_splits = array_filter(explode('newline', $sku_order_id_options[$sku]));
                    $this->_setFont($page, $font_style_body, ($font_size_body - 2), $font_family_body, $non_standard_characters, $font_color_body);
                    $options_splits = $this->groupOptionProduct($options_splits);
                    //natcasesort($options_splits);
                    //usort($options_splits, "strnatcmp");
                    foreach ($options_splits as $options_split => $qty_option) {
                        //$temp_str1 = substr($options_split, strpos($options_split, 'qty_ordered'), strlen($options_split));
                        $temp_str1 = trim($qty_option);
                        $temp_str2 = $options_split;

                        $temp_str2 = trim($temp_str2);
                        $temp_str = array_filter(explode('] [', $temp_str2));

                        foreach ($temp_str as $str_key => $str_value) {
                            if ($this->y < 70) {
                                if ($page_count == 1) {
                                    $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - ($font_size_subtitles * 2)), 'UTF-8');
                                }
                                $page = $this->newPage();
                                $page_count++;
                                $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                                $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                            }
                            $this->_setFont($page, $font_style_body, ($font_size_body - 2), $font_family_body, $non_standard_characters, $font_color_body);
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
                        $this->y += 8;
                        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.8));
                        $page->drawLine($skuXreal + 4, ($this->y), $skuXreal + 120, ($this->y));
                        $this->y -= 10;
                    }
                    $this->_setFont($page, $font_style_body, ($font_size_body), $font_family_body, $non_standard_characters, $font_color_body);
                } 
                elseif (isset($sku_order_id_options[$sku]) && $sku_order_id_options[$sku] != '' && $options_yn == 'newskuparent') {
                    $options_splits = array_filter(explode('newline', $sku_order_id_options[$sku]));
                    $this->_setFont($page, $font_style_body, ($font_size_body - 2), $font_family_body, $non_standard_characters, $font_color_body);
                    $temp_skuparent_arr = array();
                    foreach ($options_splits as $key => $options_split) {
                        $temp_str1 = trim(substr($options_split, strpos($options_split, 'qty_ordered'), strlen($options_split)));
                        $temp_str1 = trim(str_replace('qty_ordered', '', $temp_str1));
                        $temp_str2 = trim(substr($options_split, 0, strpos($options_split, 'qty_ordered')));

                        $options_splits_new = array_filter(explode('[', $temp_str2));

                        foreach ($options_splits_new as $options_split_new) {
                            $options_split_new = trim('[' . $options_split_new);
                            if ($temp_skuparent_arr[$options_split_new] == '') {
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
                            $page->drawRectangle(25, ($this->y - (($font_size_body - 2) / 4)), $padded_right, ($this->y + ($font_size_body - 2)));
                            $this->_setFont($page, $font_style_body, ($font_size_body - 2), $font_family_body, $non_standard_characters, $font_color_body);
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
                        $option_total_quantity += $skuparent_value;
                        $this->y -= 12;

                    }
                    $this->y += 12;
                    $this->_setFont($page, $font_style_body, ($font_size_body - 2), $font_family_body, $non_standard_characters, $font_color_body);
                    if (is_array($options_sku_parent[$sku])) {
                        ksort($options_sku_parent[$sku]);


                        foreach ($options_sku_parent[$sku] as $options_sku_single => $options_sku_qty) {
                            if ($this->y < 70) {
                                if ($page_count == 1) {
                                    $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - ($font_size_subtitles * 2)), 'UTF-8');
                                }
                                $page = $this->newPage();
                                $page_count++;
                                $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
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
                    $this->_setFont($page, $font_style_body, ($font_size_body), $font_family_body, $non_standard_characters, $font_color_body);
                }
                
              
                if (isset($lines) && $lines != 0) {
                    $this->y -= (($lines - 1) * ($font_size_body + 3));
                    $lines = 0;
                    if ($doubleline_yn == 2)
                        $this->y -= 15;
                    if ($doubleline_yn == 1.5)
                        $this->y -= 7.5;
                } else {
                    if ($doubleline_yn == 2)
                        $this->y -= 2 * $font_size_body;
                    else if ($doubleline_yn == 1.5)
                        $this->y -= 1.5 * $font_size_body;
                    else
                        $this->y -= ($font_size_body*1.2);
                }

                if (isset($has_shown_product_image) && ($has_shown_product_image == 1)) {
                    $this->y -= $font_size_body * 1.5;
                }

            }
        }

        $thisYbase = ($this->y - 50);

            if ($from_shipment == 'shipment') {

                $thisYbase = ($thisYbase + ($font_size_body * 3));
                $this->y = $thisYbase;

                $box_top = $this->y;
                $box_bottom = ($this->y - (($font_size_body + 1) * (count($shipment_list) + 1)) - ($font_size_body * 2));
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
                $page->setLineWidth(4);
                $page->drawRectangle(25, $box_bottom, 320, $box_top);

                $this->y -= ($font_size_body * 2);

                $this->_setFont($page, 'bold', $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
                $page->drawText($helper->__('Shipments included') . ':', 35, $this->y, 'UTF-8');
                $this->y -= 5;
                $this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);

                $i = 0;
                foreach ($shipment_list as $k => $value) {
                    if ($this->y < 70) {
                        if ($page_count == 1) {
                            $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                            $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - ($font_size_subtitles * 2)), 'UTF-8');
                        }
                        $page = $this->newPage();
                        $page_count++;
                        $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');

                        if ($page_count > 1) {
                            $page->setFillColor($white_bkg_color);
                            $page->setLineColor($orange_bkg_color);
                            $page->setLineWidth(4);
                            $page->drawRectangle(25, $box_bottom_page2, 320, ($page_top - ($font_size_body * 2)));
                            $this->y = ($page_top - ($font_size_body * 3));
                        }
                    }

                    $this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);

                    $this->y -= ($font_size_body + 1);

                    $page->drawText('#' . $value . ' [' . $name_list[$k] . '] (order #' . $order_list[$k] . ')', 35, $this->y, 'UTF-8');
                    $i++;
                }
                $this->y += 30;
                $this->y += (15 * $i);
            }

            $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);

            if (($showcount_yn == 1) || ($showcost_yn == 1) || ($order_count_yn == 1) || ($total_paid_subtotal_yn == 1) || (($combined_total_weight_yn == 1) && ($total_weight > 0))) {
                if ($page_count == 1)
                    $this->y = $thisYbase;
                $shipYbox = 0;
                //calculate totals.
                if ($showcount_yn == 1) {
                    $shipYbox += ($font_size_body * 2);
                }

                if ($order_count_yn == 1) {
                    $shipYbox += $font_size_body * 1.4;
                }

                if ($showcost_yn == 1) {
                    $shipYbox += $font_size_body * 1.4;
                }


                if ($total_paid_subtotal_yn == 1) {
                    $shipYbox += $font_size_body * 1.4;
                }

                if ((($shipping_subtotal_yn == 1) && ($total_shipping_cost['shipping_plus_tax'] > 0)) || (($shipping_subtotal_yn == 2) && ($total_shipping_cost['shipping_ex_tax'] > 0))) {
                    $shipYbox += $font_size_body * 1.4;
                }

                if (($combined_total_weight_yn == 1) && ($total_weight > 0)) {
                    $shipYbox += $font_size_body * 1.4;
                }

                if ($sheet_has_bundles === true) {
                    $shipYbox += $font_size_body * 1.4;
                }

                if ($error_product_count > 0) {
                    $shipYbox += $font_size_body * 1.4;
                }

                if (($this->y - $shipYbox - $font_size_body * 1.4) < 60) {
                    if ($page_count == 1) {
                        $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                        $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y - 15), 'UTF-8');
                    }
                    $page = $this->newPage();
                    $page_count++;
                    $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 2), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                    $page->drawText('-- ' . $helper->__('Page') . ' ' . $page_count . ' --', 250, ($this->y + 15), 'UTF-8');
                }

                $page->setFillColor($white_bkg_color);
                $page->setLineColor($orange_bkg_color);
                $page->setLineWidth(4);

                $page->drawRectangle(340, ($this->y - $shipYbox - $font_size_body * 1.4), ($padded_right - 2), $this->y);
                $this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
            }

            if ($showcount_yn == 1) {
                $this->y -= ($font_size_body * 2);

                if ($from_shipment == 'shipment') {
                    $this->_setFont($page, 'bold', $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
                    $page->drawText($helper->__('Total item quantity shipped this time') . ' : ' . $count, 360, $this->y, 'UTF-8');
                    $this->_setFont($page, $font_style_body, $font_size_body, $font_family_body, $non_standard_characters, $font_color_body);
                    $this->y -= 12;
                    $page->drawText($helper->__('Total item quantity shipped all time') . ' : ' . $count_all, 360, $this->y, 'UTF-8');
                    $this->y -= 12;
                    $page->drawText($helper->__('Total item quantity ordered') . ' : ' . $total_quantity, 360, $this->y, 'UTF-8');
                    $this->y -= 8;
                    $page->drawText('---------------------------------------------------', 360, $this->y, 'UTF-8');
                    $this->y -= 8;
                    $page->drawText($helper->__('Qty remaining to be shipped') . ' : ' . ($total_quantity - $count_all), 360, $this->y, 'UTF-8');

                } else {
                    $page->setFillColor($white_color);
                    $page->setLineColor($black_color);
                    $page->setLineWidth(0.5);

                    $page->drawRectangle(360, ($this->y), 366, ($this->y + 6));
                    $page->setFillColor($font_color_body_zend);
                    $page->setLineWidth(1);


                    $page->drawText($helper->__('Item quantity') . ' : ' . $total_quantity, 370, $this->y, 'UTF-8');
                }
            }

            if ($order_count_yn == 1) {
                $this->y -= $font_size_body * 1.4;
                $page->drawText($helper->__('Total orders') . ' : ' . $order_count, 360, $this->y, 'UTF-8');
            }
			
            if ($total_paid_subtotal_yn == 1) {
                $this->y -= $font_size_body * 1.4;
                if ($from_shipment == 'shipment')
                    $page->drawText($helper->__('Total paid this shipment') . ' : ' . $currency_symbol . "  " . round($total_paid, 2), 360, $this->y, 'UTF-8');
                else
                    $page->drawText($helper->__('Total paid') . ' : ' . $currency_symbol . "  " . round($total_paid, 2), 360, $this->y, 'UTF-8');
            }

            if ($shipping_subtotal_yn == 2) {
                if ($total_shipping_cost['shipping_ex_tax'] > 0) {
                    $this->y -= $font_size_body * 1.4;
                    $page->drawText($helper->__('Total shipping paid (ex. tax)') . ' : ' . $shipping_currency . $shipping_currency_symbol . ' ' . round($total_shipping_cost['shipping_ex_tax'], 2), 360, $this->y, 'UTF-8');
                }
            } else if ($shipping_subtotal_yn == 1) {
                if ($total_shipping_cost['shipping_plus_tax'] > 0) {
                    $this->y -= $font_size_body * 1.4;
                    $page->drawText($helper->__('Total shipping paid (inc. tax)') . ' : ' . $shipping_currency . $shipping_currency_symbol . ' ' . round($total_shipping_cost['shipping_plus_tax'], 2), 360, $this->y, 'UTF-8');
                }
            }

            if (($combined_total_weight_yn == 1) && ($total_weight > 0)) {
                $this->y -= $font_size_body * 1.4;
                $page->drawText($helper->__('Total weight') . ' : ' . round($total_weight, $combined_total_weight_rounding), 360, $this->y, 'UTF-8');
            }

            if ($sheet_has_bundles === true) {
                $this->y -= $font_size_body * 1.4;
                $page->setFillColor($white_color);
                $page->setLineColor($black_color);
                $page->setLineWidth(0.5);
                $page->drawCircle(363.5, ($this->y + 3.5), 3.5);
                $page->setFillColor($black_color);
                $page->drawText(($helper->__('Key : Bundle Parent ') . $total_bundle_quantity), 373.5, $this->y, 'UTF-8');


            }

            if ($error_product_count > 0) {
                $this->y -= $font_size_body * 1.4;
                $page->drawText($helper->__('Error Product Count') . ' : ' . $error_product_count, 360, $this->y, 'UTF-8');
            }

            if ($printdates == 1) {
                $this->_setFont($page, $font_style_subtitles, ($font_size_subtitles - 3), $font_family_subtitles, $non_standard_characters, $font_color_subtitles);
                $page->drawText($helper->__('Printed') . ':   ' . date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())), 160, 18, 'UTF-8');
            }
        $this->_afterGetPdf();

        return $pdf;
    }
}