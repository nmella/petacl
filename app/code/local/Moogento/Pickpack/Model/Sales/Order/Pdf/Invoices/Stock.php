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
* File        Stock.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Stock extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
	protected $warehouse_title = array();
	
    public function __construct() {
        parent::__construct();
    }

    public function getGeneralConfig() {
        return Mage::helper('pickpack/config')->getGeneralConfigArray($this->getStoreId());
    }
	
    public function getPickStock($orders = array()) {
        /*************************** BEGIN PDF GENERAL CONFIG *******************************/
        $this->setGeneralConfig(Mage::app()->getStore()->getStoreId());
        /*************************** END PDF GLOBAL PAGE CONFIG *******************************/

        $helper = Mage::helper('pickpack');
        $generalConfig = $this->getGeneralConfig();
        /**
         * get store id
         */
        $store_id = Mage::app()->getStore()->getId();

        $csv_or_pdf = trim($this->_getConfig('csv_or_pdf', 'pdf', false, 'stock'));
        $csv_output = '';

        if ($csv_or_pdf == 'pdf') {
            $this->_beforeGetPdf();
            $this->_initRenderer('invoices');

            $pdf = new Zend_Pdf();
            $this->_setPdf($pdf);
            $style = new Zend_Pdf_Style();

            $page_size = $this->_getConfig('page_size', 'a4', false, 'general');

            if ($page_size == 'letter') {
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
                $page_top = 770;
                $padded_right = 587;
            } elseif ($page_size == 'a4') {
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $page_top = 820;
                $padded_right = 570;
            }/*
             elseif ($page_size == 'a5-landscape') {
                            $page = $pdf->newPage('596:421');
                            $page_top = 395;
                            $padded_right = 573;
                        } elseif ($page_size == 'a5-landscape') {
                            $page = $pdf->newPage('421:596');
                            $page_top = 573;
                            $padded_right = 395;
                        }*/
            

            $pdf->pages[] = $page;


            $skuX = 67;
            $qtyX = 40;
            $productX = 250;
            $font_size_overall = 15;
            $font_size_productline = 9;
            $total_quantity = 0;
            $total_cost = 0;
            $red_bkg_color = new Zend_Pdf_Color_Html('lightCoral');
            $green_bkg_color = new Zend_Pdf_Color_Html('lightGreen');
            $white_bkg_color = new Zend_Pdf_Color_Html('white');
            $orange_bkg_color = new Zend_Pdf_Color_Html('Orange');

            $black_color = new Zend_Pdf_Color_Rgb(0, 0, 0);
            $greyout_color = new Zend_Pdf_Color_GrayScale(0.6);
            $white_color = new Zend_Pdf_Color_GrayScale(1);
           
		    $background_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['background_color_subtitles']);
            $font_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['font_color_subtitles']);
            $font_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['font_color_subtitles']);
            $font_color_body_zend = new Zend_Pdf_Color_Html($generalConfig['font_color_body']);

            $shelvingpos = $this->_getConfig('shelvingpos', 'col', false, 'general'); //col/sku
            $shelvingX = $this->_getConfig('shelving_nudge', 200, true, 'general');
            $namenudge = $this->_getConfig('stock_nudge_name', 250, true, 'messages');
        } elseif ($csv_or_pdf == 'csv') {
            $csv_output = '';
            $column_separator = ','; //$this->_getConfig('csv_field_separator',',', false,'general');
            $row_ending = "\n";
        }

        $product_id = NULL; // get it's ID
        $qty_in_stock = NULL;
        $sku_stock = array();
        $sku_qty = array();
        $sku_cost = array();
        // pickpack_cost
        $showcost_yn_default = 0;
        $showcount_yn_default = 0;
        $currency_default = 'USD';

        $shelving_yn_default = 0;
        $shelving_attribute_default = 'shelf';
        $shelvingX_default = 200;
        $supplier_yn_default = 0;
        $supplier_attribute_default = 'supplier';
        $namenudgeYN_default = 0;

        $split_supplier_yn_default = 'no';
        $supplier_attribute_default = 'supplier';
        $supplier_options_default = 'filter';
        $tickbox_default = 'no'; //no, pick, pickpack

        $add_ordered_qty = $this->_getConfig('add_ordered_qty', 0, false, 'stock');
        // $split_supplier_yn             = $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false,'general');
        $split_supplier_yn_temp = $this->_getConfig('pickpack_split_supplier_yn', $split_supplier_yn_default, false, 'general');
        $split_supplier_options_temp = $this->_getConfig('pickpack_split_supplier_options', 'no', false, 'general');
        $split_supplier_options = explode(',',$split_supplier_options_temp);
        $split_supplier_yn      = 'no';
        $supplierKey = 'stock';
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
                if ($supplier_login_single[1] != 'all') $supplier_login = trim($supplier_login_single[1]);
                else $supplier_login = '';
            }
        }

        $tickbox = $this->_getConfig('pickpack_tickbox', $tickbox_default, false, 'general');
		
		$tickbox = $this->_getConfig('pickpack_tickbox_yn_stock', $tickbox_default, false, 'stock');
        $tickbox_X = $this->_getConfig('pickpack_tickboxnudge_stock', 7, false, 'stock');
        $tickbox2 = $this->_getConfig('pickpack_tickbox2_yn_stock', $tickbox_default, false, 'stock');
        $tickbox2_X = $this->_getConfig('pickpack_tickbox2nudge_stock', 7, false, 'stock');
        
        if($tickbox == 0)
        {
        	$tickbox_X = 0;
        	$tickbox2 =0;
        	$tickbox2_X = 0;
        }
		else
		{
			$qtyX = ($tickbox_X > $tickbox2_X)?($tickbox_X + 20 + 15) : ($tickbox2_X + 20 + 15);
			$skuX = ($tickbox_X > $tickbox2_X)?($tickbox_X + 20 + 50) : ($tickbox2_X + 20 + 50);
		}
        $picklogo = $this->_getConfig('pickpack_picklogo', 0, false, 'general');
		$logo_maxdimensions = explode(',', '269,41');
		
        $showcount_yn = $this->_getConfig('pickpack_count', $showcount_yn_default, false, 'messages');

        $showcost_yn = $this->_getConfig('pickpack_cost', $showcost_yn_default, false, 'messages');
        $currency = $this->_getConfig('pickpack_currency', $currency_default, false, 'messages');
        $currency_symbol = Mage::app()->getLocale()->currency($currency)->getSymbol();

        $stockcheck_yn = 1;
        $stockcheck_solo = $this->_getConfig('stock_stock', 1, false, 'stock');
        $min_qty_in_stock = array();

        $shelving_yn = $this->_getConfig('pickpack_shelving_yn_stock', 0, false, 'stock');

        $infoline_yn = $this->_getConfig('pickpack_infoline_yn_stock', 0, false, 'stock');
        $name_yn = $this->_getConfig('pickpack_name_yn_stock', 0, false, 'stock');
        $shelving_attribute = trim($this->_getConfig('pickpack_shelving_stock', '', false, 'stock'));

        $nameyn = $name_yn; 
        if ($shelving_attribute == '') $shelving_yn = 0;

        $min_qty_in_stock = array();
        $sku_master = array();
        $pre_stock1 = trim(preg_replace("/[^\n\r;,\"\'a-zA-Z0-9\-\_]/", '', $this->_getConfig('min_qty_in_stock', '', false, 'stock')));
        if ($pre_stock1 != '') {
            $pre_stock1 = str_replace(array("\n", "\r", ';;'), ';', $pre_stock1);

            // no empty lines
            $pre_stock2 = preg_split('~;~', $pre_stock1, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($pre_stock2 as $pre_stock) {
                $pre_stock3 = explode(',', $pre_stock);
                foreach ($pre_stock3 as $del) // as $sku => $value)
                {
                    $sku = $pre_stock3[0];
                    $value = $pre_stock3[1];
                    $product_id = Mage::getModel('catalog/product')->setStoreId($store_id)->getIdBySku($sku);

                    if (Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) {
                        // $product_temp     = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
                        $product_temp = Mage::getModel('catalog/product')->setStoreId($store_id)->loadByAttribute('sku', $sku, array('cost', 'name', 'simple_sku', 'qty'));

                        $qty_in_stock = round($product_temp->getQty());
                        $cost = (is_object($product_temp) ? $product_temp->getCost() : 0);
                    } else {
                        $qty_in_stock = 0;
                        $cost = 0;
                    }

                    if ($qty_in_stock < $value) {
                        // comment next lines to not have these custom SKUs added when there are no orders for them
                        // (will still not show if there is enough stock)
                        $sku_master[$sku] = $sku;
                        $min_qty_in_stock[$sku] = $value;
                        $sku_stock[$sku] = $qty_in_stock;
                        $sku_cost[$sku] = $cost;
                        $sku_qty_ordered[$sku] = 0;
                    }

                }
            }
        }

        foreach ($orders as $orderSingle) {
            $order = $helper->getOrder($orderSingle);

            $order_id = $order->getRealOrderId();

            if (!isset($order_id_master[$order_id])) $order_id_master[$order_id] = 0;

            $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
            // $total_items     = count($itemsCollection);

            foreach ($itemsCollection as $item) {
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    // any products actually go thru here?
                    $sku = $item->getProductOptionByCode('simple_sku');
                    $product_id = Mage::getModel('catalog/product')->setStoreId($store_id)->getIdBySku($sku);
                } else {
                    $sku = $item->getSku();
                    $product_id = $item->getProductId(); // get it's ID
                }

                $qty_in_stock = 0;
                $product = Mage::getModel('catalog/product')->setStoreId($store_id)->loadByAttribute('sku', $sku, array('cost', $shelving_attribute, 'name', 'simple_sku', 'qty'));
                if (Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) {
                    $qty_in_stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
                }

                $shelving = '';
                $supplier = '';

                if ($shelving_yn == 1) {
                    $shelving = Mage::helper('pickpack')->getProductAttributeValue($product,$shelving_attribute);
                    if (is_array($shelving)) $shelving = implode(',', $shelving);

                    if (isset($sku_shelving[$sku]) && trim(strtoupper($sku_shelving[$sku])) != trim(strtoupper($shelving))) $sku_shelving[$sku] .= ',' . trim($shelving);
                    else $sku_shelving[$sku] = trim($shelving);
                    $sku_shelving[$sku] = preg_replace('~,$~', '', $sku_shelving[$sku]);
                }


                if ($split_supplier_yn != 'no') {
                	
                	//TODO 1
                	$is_warehouse_supplier = 0;
					if((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
					{
						if($supplier_attribute == 'warehouse')
						{
							$is_warehouse_supplier = 1;
						}
					}
					if($is_warehouse_supplier == 1)
					{
						$warehouse = $item->getWarehouse();
						$warehouse_code = $warehouse->getData('code');
						$supplier = $warehouse_code;
						$warehouse_code = trim(strtoupper($supplier));
						$this->warehouse_title[$warehouse_code] = $item->getWarehouseTitle();
					}
					else
					{
						
						$_newProduct = $helper->getProductForStore($product_id, $storeId);
                    if ($_newProduct) {
                        if ($_newProduct->getData($supplier_attribute)) $supplier = $_newProduct->getData('' . $supplier_attribute . '');
                    } elseif ($product->getData('' . $supplier_attribute . '')) {
                        $supplier = $product->getData($supplier_attribute);
                    }
                    if ($_newProduct->getAttributeText($supplier_attribute)) {
                        $supplier = $_newProduct->getAttributeText($supplier_attribute);
                    } elseif ($product[$supplier_attribute]) $supplier = $product[$supplier_attribute];
					}

                    if (is_array($supplier)) $supplier = implode(',', $supplier);
                    if (!$supplier) $supplier = '~Not Set~';

                    $supplier = trim(strtoupper($supplier));

                    if (isset($sku_supplier[$sku]) && $sku_supplier[$sku] != $supplier) $sku_supplier[$sku] .= ',' . $supplier;
                    else $sku_supplier[$sku] = $supplier;
                    $sku_supplier[$sku] = preg_replace('~,$~', '', $sku_supplier[$sku]);

                    if (!isset($supplier_master[$supplier])) $supplier_master[$supplier] = $supplier;
                    $order_id_master[$order_id] .= ',' . $supplier;
                }


                // qty in this order of this sku
                $qty_ordered = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int)$item->getQtyOrdered();

                if ($add_ordered_qty == 1) {
                    if (isset($sku_qty_ordered[$sku])) $sku_qty_ordered[$sku] += $qty_ordered;
                    else $sku_qty_ordered[$sku] = $qty_ordered;
                } else $sku_qty_ordered[$sku] = 0;

                $sku_qty[$sku] = $qty_in_stock;

                $sku_cost[$sku] = (is_object($product) ? $product->getCost() : 0);
                // $total_cost = $total_cost + $cost;

                $sku_master[$sku] = $sku;
                if (isset($configurable_names) && $configurable_names == 'simple' && $_newProduct = $helper->getProductForStore($product_id, $store_id)) {
                    if ($_newProduct->getData('name')) $sku_name[$sku] = $_newProduct->getData('name');
                } else $sku_name[$sku] = $item->getName();

                $sku_stock[$sku] = $qty_in_stock;

                // here we can set custom min-qty-in-stock for skus which we want custom stock minimums
                if (!isset($min_qty_in_stock[$sku])) $min_qty_in_stock[$sku] = $stockcheck_solo;

                if (($qty_in_stock - $qty_ordered) < $min_qty_in_stock[$sku]) {
                    if (!isset($supplier_master[$supplier])) $supplier_master[$supplier] = $supplier;
                    else $supplier_master[$supplier] .= ',' . $supplier;
                }


            }

        }

        // $nitems = array();

        if (is_array($sku_master)) ksort($sku_master);

        if (isset($supplier_master) && is_array($supplier_master)) ksort($supplier_master);
        elseif (!isset($supplier_master)) {
            $split_supplier_yn == 0;
            $supplier_master = '';
        }
        $supplier_previous = '';
        $supplier_item_action = '';
        $first_page_yn = 'y';


        if ($split_supplier_yn == 1) {
            foreach ($supplier_master as $supplier => $value) {
                $supplier_skip_order = FALSE;

                if ($supplier_skip_order == FALSE) {
                    $total_quantity = 0;
                    $total_cost = 0;

                    if ($first_page_yn == 'n') $page = $this->newPage();
                    else $first_page_yn = 'n';

                    if ($picklogo == 1 && ($csv_or_pdf == 'pdf')) {
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
						
						$this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText($helper->__('Out-of-stock Pick List'), 325, ($page_top - $page_top_spacer - (41/2) + 10), 'UTF-8');
                        $page->drawText($helper->__('Supplier') . ' : ' . $supplier, 325, 790, 'UTF-8');
			            
						$page->setFillColor($background_color_subtitles_zend);
			            $page->setLineColor($background_color_subtitles_zend);
			            $page->setLineWidth(0.5);
			            if($generalConfig['line_width_company'] > 0)
			                $page->drawRectangle(304, $y1, (304 + $generalConfig['line_width_company']), ($page_top + 5));
						
                        $page->drawRectangle(27, $this->y, $padded_right, ($this->y + 1));
                        $this->y -= 40;
                    } elseif ($csv_or_pdf == 'pdf') {
						$this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                        $page->drawText($helper->__('Out-of-stock Pick List, Supplier') . ' : ' . $supplier, 325, ($page_top - $page_top_spacer - (41/2) + 10), 'UTF-8');
                        $page->setLineColor($font_color_subtitles_zend);
                        $page->setFillColor($font_color_subtitles_zend);
                        $page->setLineWidth(0.5);
                        $page->drawRectangle(27, 803, $padded_right, 804);
						$this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
						 $this->y -= ($generalConfig['font_size_subtitles']*1.5);
                    } elseif ($csv_or_pdf == 'csv')
                        $csv_output .= $column_separator . $helper->__('Out-of-stock Pick List') . $row_ending . $row_ending;

                    $this->y = 777;
                    $processed_skus = array();
                    $outofstock_check = false;

                    // roll_SKU
                    foreach ($sku_master as $key => $sku) {
                        if (!in_array($sku, $processed_skus) && ($sku_stock[$sku] < $min_qty_in_stock[$sku])) {
                            $processed_skus[] = $sku;
                            $outofstock_check = true;

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
                            } elseif ($supplier_options == 'grey' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier))) {
                                $supplier_item_action = 'keepGrey';
                            } elseif ($supplier_options == 'filter' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) != strtoupper($supplier))) {
                                $supplier_item_action = 'hide';
                            } elseif ($supplier_options == 'grey' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier))) {
                                $supplier_item_action = 'keep';
                            } elseif ($supplier_options == 'filter' && ($supplier_login == '') && (strtoupper($sku_supplier[$sku]) == strtoupper($supplier))) {
                                $supplier_item_action = 'keep';
                            } elseif ($supplier_options == 'grey') $supplier_item_action = 'keepGrey'; elseif ($supplier_options == 'filter') $supplier_item_action = 'hide';
							//to do tickbox
							if ($csv_or_pdf == 'pdf'){
								if ($tickbox != 0) {
									$page->setFillColor($white_color);
									$page->setLineColor($black_color);
									$page->setLineWidth(0.5);
									$page->drawRectangle($tickbox_X + 20, ($this->y), $tickbox_X + 20 + 7, ($this->y + 7));
									
									if($tickbox2 != 0)
									{
										$page->drawRectangle($tickbox2_X + 20, ($this->y), $tickbox2_X + 20+ 7, ($this->y + 7));
									}
									$page->setLineWidth(1);
								}
							}
                            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                            if ($supplier_item_action != 'hide' && trim($supplier_item_action) != '') {
                                if ($this->y < 15) $page = $this->newPage();
                                if ($csv_or_pdf == 'pdf') $page->setFillColor($black_color);
                                if ($supplier_item_action == 'keepGrey' && ($csv_or_pdf == 'pdf')) {
                                    $page->setFillColor($greyout_color);
                                } else {

                                    $total_quantity = $total_quantity + ($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]); //$sku_qty[$sku];
                                    $total_cost = $total_cost + (($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]) * $sku_cost[$sku]);

                                }
                               
                                if ($csv_or_pdf == 'pdf') {
                                    if ($this->y < 15) $page = $this->newPage();
                                    $page->setFillColor($black_color);
                                    $page->drawText(($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]) . ' x ', $qtyX, $this->y, 'UTF-8');

                                    $page->drawText($sku, $skuX, $this->y, 'UTF-8');
                                    if ($nameyn == 1) $page->drawText($sku_name[$sku], intval($namenudge), $this->y, 'UTF-8');
                                    if ($shelving_yn == 1 && $sku_shelving[$sku]) $page->drawText('[' . $sku_shelving[$sku] . ']', $shelvingX, $this->y, 'UTF-8');
                                    if (($infoline_yn == 1) && (($sku_stock[$sku] + $sku_qty_ordered[$sku]) < $min_qty_in_stock[$sku]) && ($stockcheck_yn == 1)) {
                                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                                        $this->y -= 14;
                                        $page->setFillColor($red_bkg_color);
                                        $page->setLineColor($red_bkg_color);
                                        $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                                        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                                        $warning = 'Stock Warning      SKU: ' . $sku . '    Net Stock After All Picks : ' . ($sku_stock[$sku] - $sku_qty_ordered[$sku]);
                                        $page->drawText($warning, 60, $this->y, 'UTF-8');

                                        $need = 'Need ' . ($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]) . ' to restock to ' . $min_qty_in_stock[$sku] . ' . . (' . $sku_qty[$sku] . ' going out in all picks)';
                                        $this->y -= 14;
                                        $page->setFillColor($green_bkg_color);
                                        $page->setLineColor($green_bkg_color);
                                        $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                                        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                                        $page->drawText($need, 60, $this->y, 'UTF-8');

                                        $this->y -= 4;

                                    }
                                    $this->y -= 12;
                                } // end if pdf
                                elseif ($csv_or_pdf == 'csv') {
                                    $csv_output .= ($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]) . $column_separator . $sku . $column_separator;
                                    if ($nameyn == 1) $csv_output .= $sku_name[$sku] . $column_separator;
                                    if ($shelving_yn == 1 && $sku_shelving[$sku]) $csv_output .= '[' . $sku_shelving[$sku] . $column_separator;
                                }
                            }
                        }
                        // end if supplier_skip
                    }
                }
                // end roll_SKU
                if ($csv_or_pdf == 'pdf') {
                    $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                    $this->y -= 30;
                    $this->_setFontItalic($page, 12);

                    if (($showcount_yn == 1) || $showcost_yn == 1) {
                        $page->setFillColor($white_bkg_color);
                        $page->setLineColor($orange_bkg_color);
                        $page->setLineWidth(1);
                        $page->drawRectangle(355, ($this->y - 34), 568, ($this->y + 10 + $generalConfig['font_size_body']));
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    }
                } else 
					$csv_output .= $row_ending;

                if ($showcount_yn == 1) {
                    if ($csv_or_pdf == 'pdf'){
						$this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
						$page->drawText($helper->__('Total quantity'), 375, $this->y, 'UTF-8');
						$this->_setFont($page, 'regular', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
						$page->drawText($total_quantity, 465, $this->y, 'UTF-8');
					}
                    elseif ($csv_or_pdf == 'csv') $csv_output .= "\n\n" . $column_separator . $helper->__('Total quantity') . ' : ' . $total_quantity . $column_separator;
                    $this->y -= 20;
                }

                if ($showcost_yn == 1) {
                    if ($csv_or_pdf == 'pdf') $page->drawText($helper->__('Total cost') . ' : ' . $currency_symbol . "  " . $total_cost, 375, $this->y, 'UTF-8');
                    elseif ($csv_or_pdf == 'csv') $csv_output .= "\n" . $column_separator . $helper->__('Total cost') . ' : ' . $currency_symbol . "  " . $total_cost;
                }

                $printdates = Mage::getStoreConfig('pickpack_options/picks/pickpack_packprint');
                if ($printdates != 1) {
                    if ($csv_or_pdf == 'pdf') {
                        $this->_setFontBold($page);
                        $page->drawText($helper->__('Printed:') . '   ' . date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())), 210, 18, 'UTF-8') . $column_separator;
                    } elseif ($csv_or_pdf == 'csv') {
                        $csv_output .= "\n" . $column_separator . $helper->__('Printed:') . '   ' . date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())) . $column_separator;
                    }
                }
            }
        } else {
            $total_quantity = 0;
            $total_cost = 0;
            if ($csv_or_pdf == 'pdf') {
                $this->y = 777;

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
					
					$this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText($helper->__('Out-of-stock Pick List'), 325, ($page_top - $page_top_spacer - (41/2) + 10), 'UTF-8');
                   
		            $page->setFillColor($background_color_subtitles_zend);
		            $page->setLineColor($background_color_subtitles_zend);
		            $page->setLineWidth(0.5);
		            if($generalConfig['line_width_company'] > 0)
		                $page->drawRectangle(304, $y1, (304 + $generalConfig['line_width_company']), ($page_top + 5));
					
                    $page->drawRectangle(27, $this->y, $padded_right, ($this->y + 1));
                    $this->y -= 40;
                } else {
					$this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                    $page->drawText($helper->__('Out-of-stock Pick List'), 325, ($page_top - $page_top_spacer - (41/2) + 10), 'UTF-8');
                    $page->setLineColor($font_color_subtitles_zend);
                    $page->setFillColor($font_color_subtitles_zend);
                    $page->drawRectangle(27, 803, $padded_right, 803);
                }

				//draw title
				//$this->y -= 40;
				$this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
				//TODO add show sku options here.
                // $page->drawText(Mage::helper('sales')->__("SKU"),$skuX , $this->y, 'UTF-8');
				//$page->drawText(Mage::helper('sales')->__("QTY"),$qtyX , $this->y, 'UTF-8');
				//$page->drawText(Mage::helper('sales')->__("Name"),intval($namenudge) , $this->y, 'UTF-8');
				 $this->y -= ($generalConfig['font_size_subtitles']*1.5);
            } elseif ($csv_or_pdf == 'csv')
                $csv_output .= $column_separator . $helper->__('Out-of-stock Pick List') . $row_ending . $row_ending;

            if (!isset($sku_test)) $sku_test = array();

            // roll sku foreach
            // roll_SKU
            $processed_skus = array();

            foreach ($sku_master as $key => $sku) {
                
                if (!in_array($sku, $processed_skus) && ($sku_stock[$sku] < $min_qty_in_stock[$sku])) {
					if ($csv_or_pdf == 'pdf'){ 
						//to do tickbox
						if ($tickbox != 0) {
							$page->setFillColor($white_color);
							$page->setLineColor($black_color);
							$page->setLineWidth(0.5);
							$page->drawRectangle($tickbox_X + 20, ($this->y), $tickbox_X + 20 + 7, ($this->y + 7));
							
							if($tickbox2 != 0)
							{
								$page->drawRectangle($tickbox2_X + 20, ($this->y), $tickbox2_X + 20+ 7, ($this->y + 7));
							}
							$page->setLineWidth(1);
						}
						$this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
					}
                    $processed_skus[] = $sku;

                    $total_quantity = $total_quantity + ($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]); //$sku_qty[$sku];
                    $total_cost = $total_cost + (($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]) * $sku_cost[$sku]);

                    if ($csv_or_pdf == 'pdf') {
                        if ($this->y < 15) $page = $this->newPage();
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], 'Black');

                        $page->drawText(($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]) . ' x ', $qtyX, $this->y, 'UTF-8');
						
                        $page->drawText($sku, $skuX, $this->y, 'UTF-8');
						
						$sku_name[$sku] = trim(Mage::helper('pickpack/functions')->clean_method($sku_name[$sku],'pdf_more'));
                        if ($nameyn == 1) $page->drawText($sku_name[$sku], intval($namenudge), $this->y, 'UTF-8');
						
                        if ($shelving_yn == 1 && $sku_shelving[$sku]) $page->drawText('[' . $sku_shelving[$sku] . ']', $shelvingX, $this->y, 'UTF-8');
                        if (($infoline_yn == 1) && ($sku_stock[$sku] < ($min_qty_in_stock[$sku] + $sku_qty_ordered[$sku])) && $stockcheck_yn == 1) {
                            $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
						
                            $this->y -= 14;
                            $page->setFillColor($red_bkg_color);
                            $page->setLineColor($red_bkg_color);
                            $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                            $warning = $helper->__('Stock Warning') . '      ' . $helper->__('SKU') . ': ' . $sku . '    ' . $helper->__('Net Stock After All Picks') . ' : ' . $sku_stock[$sku];
                            $page->drawText($warning, 60, $this->y, 'UTF-8');

                            $need = $helper->__('Need') . ' ' . ($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]) . ' ' . $helper->__('to restock to') . ' ' . $min_qty_in_stock[$sku] . ' . . (' . $sku_qty[$sku] . ' ' . $helper->__('going out in all picks') . ')';
                            $this->y -= 14;
                            $page->setFillColor($green_bkg_color);
                            $page->setLineColor($green_bkg_color);
                            $page->drawRectangle(55, ($this->y - 4), $padded_right, ($this->y + 10));
                            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                            $page->drawText($need, 60, $this->y, 'UTF-8');

                            $this->y -= 4;

                        }
                        $this->y -= 12;
                    } //end if pdf
                    elseif ($csv_or_pdf == 'csv') {
                        $csv_output .= ($min_qty_in_stock[$sku] - $sku_stock[$sku] + $sku_qty_ordered[$sku]) . $column_separator . $sku . $column_separator;
                        if ($nameyn == 1) $csv_output .= $sku_name[$sku] . $column_separator;
                        if ($shelving_yn == 1 && $sku_shelving[$sku]) $csv_output .= '[' . $sku_shelving[$sku] . $column_separator;
                        $csv_output .= $row_ending;
                    }
                }
            }

            // end roll_SKU
            if ($csv_or_pdf == 'pdf') {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                $this->y -= 30;
                $this->_setFontItalic($page, 12);

                if (($showcount_yn == 1) || $showcost_yn == 1) {
                    $page->setFillColor($white_bkg_color);
                    $page->setLineColor($orange_bkg_color);
                    $page->setLineWidth(1);
                    $page->drawRectangle(355, ($this->y - 34), 568, ($this->y + 10 + $generalConfig['font_size_body']));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                }
            } else $csv_output .= $row_ending;

            if ($showcount_yn == 1) {
                if ($csv_or_pdf == 'pdf') {
					$this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
					$page->drawText($helper->__('Total quantity'), 375, $this->y, 'UTF-8');
					$this->_setFont($page, 'regular', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
					$page->drawText($total_quantity, 465, $this->y, 'UTF-8');
                    $this->y -= 20;
                } elseif ($csv_or_pdf == 'csv') $csv_output .= $column_separator . $helper->__('Total quantity') . ' : ' . $total_quantity . $row_ending;
            }
            if ($showcost_yn == 1) {
                if ($csv_or_pdf == 'pdf') $page->drawText($helper->__('Total cost') . ' : ' . $currency_symbol . "  " . $total_cost, 375, $this->y, 'UTF-8');
                elseif ($csv_or_pdf == 'csv') $csv_output .= $column_separator . $helper->__('Total cost') . ' : ' . $currency_symbol . "  " . $total_cost . $row_ending;
            }
            $printdates = Mage::getStoreConfig('pickpack_options/picks/pickpack_packprint');
            if ($printdates != 1) {
                if ($csv_or_pdf == 'pdf') {
                    $this->_setFontBold($page);
                    $page->drawText($helper->__('Printed:') . '   ' . date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())), 210, 18, 'UTF-8');
                } elseif ($csv_or_pdf == 'csv') {
                    $csv_output .= $column_separator . $helper->__('Printed:') . '   ' . date('D jS M Y G:i', Mage::getModel('core/date')->timestamp(time())) . $row_ending;
                }
            }
        } // end 'no supplier split'

        if ($csv_or_pdf == 'pdf') {
            $this->_afterGetPdf();
            return $pdf;
        } else return $csv_output;
        /**
        getPickSTOCK 22222 Template - END
         *********************************************
         ************************************************/
    }
}