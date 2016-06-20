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
 * File        Csvcombined.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Csvcombined extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    public function getCsvPickCombined($orders = array(), $from_shipment = 'order', $manifest_or_picklist = 'picklist') {
        $this->setGeneralCsvConfig();
        $date_format = Mage::getStoreConfig('pickpack_options/csvpickcombined/date_format');
        $strip_line_comma = Mage::getStoreConfig('pickpack_options/csvpickcombined/strip_line_commas');
        $helper = Mage::helper('pickpack');
        $magentoVersion = Mage::getVersion();
        $debug = 0;
        $total_quantity = 0;
        $total_cost = 0;
        $column_headers = '';
        // $column_separator                 = ',';
        $column_separator = $this->_general['csv_field_separator'];
        $field_quotes = '"';
        if ($this->_general['csv_quote_values_yn'] == '0') {
            $field_quotes = '';
        }
        $csv_field_separator = $column_separator;


        $column_separator_headers = $column_separator; //',';
        $column_map = array();
        $group_manifest_by_category_yn = 0;
        $cost_multiplier = 1;
        $cost_base_to_display_rate = 1;
        $cost_round = 0;
        $weight_round = 0;
        $is_excel = 0;
        $skuXInc = 0;
        //$store_id = Mage::app()->getStore()->getId();
        $order_id_master = array();
        $sku_shelving = array();
        $sku_shipping_address = array();
        $sku_order_id_options = array();
        $sku_bundle = array();
        $address_data = array();
        $sku_stock = array();
        $product_id = NULL;
        $stock = NULL;
        $key = '';
        $value = '';

        $cost_prefix = trim($this->_getConfig('cost_prefix', '', false, 'csvmanifestcombined'));
        $column_mapping_pre = $this->_getConfig('column_mapping', '', false, 'csvpickcombined');
        $configurable_names = $this->_getConfig('name_display_choice', 'simple', false, 'csvpickcombined');
        $configurable_names_attribute = trim($this->_getConfig('name_display_choice_attribute_separated', '', false, 'csvpickcombined'));


        $sku_order_id_qty = array();
        $combo_sku_order_id_qty = array();
        $combo_category_qty = array();

        /**
         * cargo manifest csv/xml
         */
        if ($manifest_or_picklist == 'manifest') {
            $store_id = 0; //$order->getStore()->getId();

            $is_excel = $this->_getConfig('is_excel_yn', 0, false, 'csvmanifestcombined');
            $column_mapping_pre_a = $this->_getConfig('pre_cells', '', false, 'csvmanifestcombined');
            $column_mapping_pre_a = str_replace(array('"', ',', '][', '[', ']'), array('""', '', '"' . $column_separator_headers . '"', '"', '"'), $column_mapping_pre_a);
            //$date_format = str_replace(',', ' ', $this->_getConfig('date_format', '', false, 'general', $store_id));
            $column_mapping_pre_a = str_replace('%date%', date($date_format, time()), $column_mapping_pre_a);
            $column_mapping_pre_a = '{b}' . $column_mapping_pre_a . "\n" . '" "' . "\n";
            $column_mapping_pre = $this->_getConfig('column_mapping', '', false, 'csvmanifestcombined');
            $category_translation = $this->_getConfig('category_translation', '', false, 'csvmanifestcombined');
            $group_manifest_by_category_yn = $this->_getConfig('group_manifest_by_category_yn', 1, false, 'csvmanifestcombined', $store_id);

            if ($group_manifest_by_category_yn == 1) {
                $column_mapping_pre = $this->_getConfig('column_mapping_grouped_cats', '', false, 'csvmanifestcombined');
            }
            $cost_multiplier = $this->_getConfig('cost_multiplier', 1, false, 'csvmanifestcombined');
            $cost_base_to_display_rate = $this->_getConfig('cost_base_to_display_rate', 1, false, 'csvmanifestcombined');
            $cost_round = $this->_getConfig('cost_round', 0, false, 'csvmanifestcombined');
            $weight_round = $this->_getConfig('weight_round', 2, false, 'csvmanifestcombined');
            $weight_multiplier = $this->_getConfig('weight_multiplier', 1, false, 'csvmanifestcombined');
            $configurable_names = $this->_getConfig('name_display_choice', 'simple', false, 'csvmanifestcombined');
            $configurable_names_attribute = trim($this->_getConfig('name_display_choice_attribute_separated', '', false, 'csvmanifestcombined'));
            $date_format = Mage::getStoreConfig('pickpack_options/csvmanifestcombined/date_format');
            $strip_line_comma = Mage::getStoreConfig('pickpack_options/csvmanifestcombined/strip_line_commas');
        }

        if ($configurable_names != 'custom') {
            $configurable_names_attribute = '';
        }

        $attribute_array = array('color', 'size', 'style', 'cost', 'season');
        $column_mapping_pre = trim(str_ireplace(array("\n", "\r", "\t"), '', $column_mapping_pre));
        $column_mapping_array = explode(';', $column_mapping_pre);
        foreach ($column_mapping_array as $key => $value) {
            $column_mapping_sub_array = explode(':', $value);

            if (trim($value) != '') {
                $key = trim($column_mapping_sub_array[0]);
                if (!isset($column_map[$key])) {
                    if ($is_excel == 1) {
                        if (preg_match('~product_category~', $value)) $column_headers .= '{merge}';
                        $column_headers .= $field_quotes . '{b}' . $key . $field_quotes . $column_separator;
                    } else $column_headers .= $field_quotes . $key . $field_quotes . $column_separator;
                }
                if (isset($column_mapping_sub_array[1])) {
                    $value = trim($column_mapping_sub_array[1]);
                }
                $column_map[$key] = $value;
                if (!preg_match('~%~', $value) && !preg_match('~\[~', $value)) {
                    $attribute_array[] = $value;
                }

            }
            unset($key);
            unset($value);
        }

        if (($manifest_or_picklist == 'manifest') && isset($category_translation)) {
            $category_translation = trim(str_ireplace(array("\n", "\r", "\t"), '', $category_translation));
            $category_translation_array = explode(';', $category_translation);
            foreach ($category_translation_array as $key => $value) {
                $category_translation_sub_array = explode(':', $value);

                if (trim($key) != '') {
                    $key = trim(strtolower($category_translation_sub_array[0]));
                    if (isset($category_translation_sub_array[1])) {
                        $value = trim($category_translation_sub_array[1]);
                    }
                    $category_map[$key] = $value;
                }
                unset($key);
                unset($value);
            }
        }

        $box_number = 1;
        $can_subtract = Mage::getStoreConfig('cataloginventory/options/can_subtract');
        $column_headers = trim(str_replace('\n', '', $column_headers));
        $column_headers = preg_replace('~,$~', '', trim(str_replace(',,', ',', $column_headers)));
        $csv_output_start = $column_headers . "\n";

        $message_data = array();
        $orderCollections = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('entity_id', array(
            'in' => $orders
        ))->load();

        foreach ($orderCollections as $order) {
            Mage::app()->setCurrentStore($order->getStore()->getStoreId());
            $order_id = $order->getRealOrderId();
            $store_id = $order->getStore()->getId();
            $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
            $product_build_item = array();
            $product_build = array();
            $order_status = $order->getStatus();
            $box_number = preg_replace('~[^0-9]~', '', $order_status);

            if (!is_numeric($box_number)) $box_number = 1;

            if (!isset($csv_output[$box_number])) $csv_output[$box_number] = '"{h2-i}Box #' . $box_number . '"' . $csv_output_start;

            $coun = 1;

            /* courierrules_shipping_method */
            $courierrules_shipping_method = $order->getData('courierrules_shipping_method');
            if (isset($courierrules_shipping_method) && $courierrules_shipping_method != '')
                $address_data[$order_id]['courierrules_shipping_method'] = $order->getData('courierrules_shipping_method');

            /* today date */
            $address_data[$order_id]['today_date'] = Mage::getSingleton('core/date')->date($date_format);

            /* coupon code */
            $address_data[$order_id]['coupon_code'] = $order->getCouponCode();

            /* Moogento Retail Express Attributes */
            if (getMooRetailExpressAttribute()) {
                $address_data[$order_id]['retail_express_id '] = $order->getData('retail_express_id');
                $address_data[$order_id]['retail_express_status'] = $order->getData('retail_express_status ');
                $address_data[$order_id]['retail_express_message'] = $order->getData('retail_express_message ');
            }

            if (isset($address_data[$order_id]['coupon_code']) && $address_data[$order_id]['coupon_code'] != '') {
                //$coupon_rule = Mage::getModel('salesrule/coupon')->loadByCode($address_data[$order_id]['coupon_code']);
                $oCoupon = Mage::getModel('salesrule/coupon')->load($address_data[$order_id]['coupon_code'], 'code');
                $coupon_rule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());
                $rule_id = $coupon_rule->getData('rule_id');
                $store_id = $order->getStore()->getId();
                $label = get_coupon_label($rule_id, $store_id);
                $address_data[$order_id]['coupon_label'] = $label;
            }

            foreach ($itemsCollection as $item) {
                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    // any products actually go thru here?
                    $sku = $item->getProductOptionByCode('simple_sku');
                    $configurable_id = $item->getProductId();
                    $product = Mage::getModel('catalog/product')->setStoreId($store_id)->loadByAttribute('sku', $sku);
                    if ($product)
                        $product_id = $product->getData('entity_id');//Mage::getModel('catalog/product')->setStoreId($store_id)->getIdBySku($sku);
                    $address_data[$sku]["product_id_configurable"] = $configurable_id;
                } else {
                    $sku = $item->getSku();
                    $product_id = $item->getProductId(); // get it's ID
                    if (version_compare($magentoVersion, '1.7', '>=')) {
                        //version is 1.7 or greater
                        $product = $item->getProduct();
                    } else {
                        //version is below 1.7
                        $product = $helper->getProductForStore($item->getData('product_id'), $store_id);
                    }

                }
                if (!$product)
                    continue;
                $product = Mage::getModel('catalog/product')->setStoreId($store_id)->load($product->getId());
                $address_data[$sku]["product_id"] = $product_id;
                $address_data[$sku]["product_id_simple"] = $product_id;
                # Get product's category collection object
                $catCollection = $product->getCategoryCollection();
                # export this collection to array so we could iterate on it's elements
                $categs = $catCollection->exportToArray();
                $categsToLinks = array();
                # Get categories names
                foreach ($categs as $cat) {
                    $categsToLinks [] = Mage::getModel('catalog/category')->load($cat['entity_id'])->getName();
                }
                $category_label = '';
                foreach ($categsToLinks as $ind => $cat) {
                    if (isset($category_map[strtolower($cat)])) $cat = $category_map[strtolower($cat)];
                    if (!empty($category_label)) $category_label = $category_label . ', ' . $cat;
                    else $category_label = $cat;
                }
                $address_data[$sku]['category'] = $category_label;
                unset($category_label);
                $address_data[$sku]['url_key'] = '';
                $address_data[$sku]['product_url'] = '';
                if ($product->getUrlPath()) {
                    $address_data[$sku]['url_key'] = ($product->getUrlPath());
                    $address_data[$sku]['product_url'] = ($product->getProductUrl(array('_store_to_url' => false)));
                }
                $address_data[$sku]['description'] = strip_tags($product->getDescription());
                $address_data[$sku]['product_description'] = strip_tags($product->getDescription());
                $address_data[$sku]['short_description'] = strip_tags($product->getShortDescription());
                //get Website name
                $websiteIds = $product->getWebsiteIds();
                $website_name = "";
                foreach ($websiteIds as $key => $websiteId) {
                    $website_name = $website_name . Mage::app()->getWebsite($websiteId)->getName() . ",";
                }
                $website_name = trim($website_name, ',');
                $address_data[$sku]['website'] = $website_name;
                //get store name
                $storeIds = $product->getStoreIds();
                $store_name = '';
                foreach ($storeIds as $key => $storeId) {
                    $store_name = $store_name . Mage::app()->getStore($storeId)->getName() . ',';
                }
                $store_name = trim($store_name, ',');
                $address_data[$sku]['store'] = $store_name;
                /***** Get Warehouse information ****/
                if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) {
                    $warehouse_title = $item->getWarehouseTitle();
                } else {
                    $warehouse_title = '';
                }
                $address_data[$sku]['warehouse'] = $warehouse_title;
                unset($warehouse_title);

                // unique item id
                $product_build_item[] = $sku . '-' . $coun;
                $product_build[$sku . '-' . $coun]['sku'] = $sku;
                $product_sku = $sku;
                $sku = $sku . '-' . $coun;
                $product_build[$sku]['sku'] = $product_sku;

                $shelving = '';
                $supplier = '';
                $sku_colors_sizes = array();
                $category_cost = array();

                foreach ($attribute_array as $key => $value) {
                    if (strlen(trim($value)) == 0)
                        unset($attribute_array[$key]);
                }

                foreach ($attribute_array as $key => $value) {
                    if (isset($shelving_attribute)) {
                        $sku_color[$product_sku][$shelving_attribute] = '';
                    }

                    if ($product->getData($value)) {
                        $attributeValue = $product->getData($value);
                        $collection = Mage::getResourceModel('eav/entity_attribute_collection')
                            ->setEntityTypeFilter($product->getResource()->getTypeId())
                            ->addFieldToFilter('attribute_code', $value);
                        $attribute = $collection->getFirstItem()->setEntity($product->getResource());
                        $attributeOptions = $attribute->getSource()->getAllOptions(false);

                        $attributeName = $value;
                        $attributeOptions = getProductAttributeOptions($product_id, $attributeName);


                        if (!empty($attributeOptions)) {
                            $result = search($attributeOptions, 'value', $attributeValue);

                            if (isset($result[0]['label'])) {
                                $sku_color[$product_sku][$value] = preg_replace('/[^a-zA-Z0-9\s\.\-]/', '', $result[0]['label']);
                            }
                        } else
                            if (!is_array($attributeValue)) {
                                $sku_color[$product_sku][$value] = preg_replace('/[^a-zA-Z0-9\s\.\-]/', '', $attributeValue);
                            }
                    }
                }
                /********************************************************/
                $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int)$item->getQtyOrdered();
                // total qty in all orders for this sku
                if (isset($sku_qty[$sku])) $sku_qty[$sku] = ($sku_qty[$sku] + $qty);
                else $sku_qty[$sku] = $qty;
                $total_quantity = $total_quantity + $qty;

                $cost = 0;
                if (isset($product)) {
                    $cost = ($qty * $product->getCost());
                }
                $total_cost = $total_cost + $cost;
                $sku_master[$sku] = $sku;

                $price = $item->getPrice();
                $address_data[$product_sku]['sku_price'] = $price;

                if (isset($address_data[$product_sku]['order_qty'])) $address_data[$product_sku]['order_qty'] = ($address_data[$product_sku]['order_qty'] + $qty);
                else $address_data[$product_sku]['order_qty'] = $qty;
                //stock qty
                if (Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty()) {
                    $stock = round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id)->getQty());
                }
                $address_data[$product_sku]['stock_qty'] = $stock;
                //stock qty before / after these orders

                if ($can_subtract == 1) {
                    $address_data[$product_sku]['qty_in_stock_before_these_orders'] = $address_data[$product_sku]['stock_qty'] + $address_data[$product_sku]['order_qty'];
                    $address_data[$product_sku]['qty_in_stock_after_these_orders'] = $address_data[$product_sku]['stock_qty'];
                } else {
                    $address_data[$product_sku]['qty_in_stock_before_these_orders'] = $address_data[$product_sku]['stock_qty'];
                    $address_data[$product_sku]['qty_in_stock_after_these_orders'] = $address_data[$product_sku]['stock_qty'] - $address_data[$product_sku]['order_qty'];
                }
                $address_data['order_price'] = (float)($order->getSubtotal());
                //to do gift product

                $message = Mage::getModel('giftmessage/message');
                $gift_message_id = $item->getGiftMessageId();
                if (!is_null($gift_message_id)) {
                    $message->load((int)$gift_message_id);

                    $gift_sender = $message->getData('sender');
                    $message_data[$product_sku]['gift_product'] = 'From: ' . $gift_sender;
                    $gift_recipient = $message->getData('recipient');
                    $message_data[$product_sku]['gift_product'] = $message_data[$product_sku]['gift_product'] . "\n" . 'To: ' . $gift_recipient;

                    $gift_message = $message->getData('message');
                    //$gift_message = trim(Mage::helper('pickpack/functions')->clean_method($gift_message, 'pdf_more'));
                    $message_data[$product_sku]['gift_product'] = $message_data[$product_sku]['gift_product'] . "\n" . 'Message: ' . $gift_message;
                }
                //TODO tracking number
                $tracking = array();
                $tracking_number = array();
                $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($order)->load();
                foreach ($shipmentCollection as $shipment) {
                    foreach ($shipment->getAllTracks() as $tracknum) {
                        $tracking_number[] = $tracknum->getNumber();
                    }
                }
                $tracking_string = implode(',', $tracking_number);
                $tracking[$product_sku]["tracking"] = $tracking_string;

                if ($product && $configurable_names == 'simple') {
                    $_newProduct = $product;
                    if ($_newProduct->getData('name')) $sku_name[$sku] = $_newProduct->getData('name');
                } elseif ($configurable_names == 'configurable') $sku_name[$sku] = $item->getName();
                elseif ($configurable_names == 'custom' && $configurable_names_attribute != '') {
                    $customname = '';

                    if ($product) {
                        $_newProduct = $product;
                        if ($_newProduct->getData($configurable_names_attribute)) $customname = $_newProduct->getData('' . $configurable_names_attribute . '');
                    } elseif ($product->getData('' . $configurable_names_attribute . '')) {
                        $customname = $product->getData($configurable_names_attribute);
                    } else $customname = '';

                    if (trim($customname) != '') {
                        if (Mage::getModel('catalog/product')->setStoreId($store_id)->load($product_id)->getAttributeText($configurable_names_attribute)) {
                            $customname = Mage::getModel('catalog/product')->setStoreId($store_id)->load($product_id)->getAttributeText($configurable_names_attribute);
                        } elseif ($product[$configurable_names_attribute]) $customname = $product[$configurable_names_attribute];
                        else $customname = '';
                    }

                    if (trim($customname) != '') {
                        if (is_array($customname)) $customname = implode(',', $customname);
                        $customname = preg_replace('~,$~', '', $customname);
                    } else $customname = '';

                    $sku_name[$sku] = $customname;
                }

                if (isset($sku_name[$sku]))
                    $address_data[$product_sku]['sku_name'] = trim($sku_name[$sku]); //$item->getName();
                else
                    $address_data[$product_sku]['sku_name'] = trim($item->getName());

                //$address_data[$product_sku]['sku_name'] = preg_replace('/[^a-zA-Z0-9\.\s]/', '', $address_data[$product_sku]['sku_name']);

                $sku_stock[$sku] = $stock;
                $real_sku = $product_build[$sku]['sku'];

                if (isset($sku_order_id_qty[$box_number][$real_sku])) {
                    $sku_order_id_qty[$box_number][$real_sku] += $qty;
                    $combo_sku_order_id_qty[$real_sku] += $qty;
                    // $sku_order_id_sqty[$real_sku] += $sqty;
                    $sku_order_id_sku[$real_sku] = $product_sku;
                } else {
                    $sku_order_id_qty[$box_number][$real_sku] = $qty;
                    $combo_sku_order_id_qty[$real_sku] = $qty;
                    // $sku_order_id_sqty[$real_sku] = $sqty;
                    $sku_order_id_sku[$real_sku] = $product_sku;
                }

                $product_category = $address_data[$real_sku]['category'];

                if (isset($category_qty[$box_number][$product_category][$real_sku])) {
                    $category_qty[$box_number][$product_category][$real_sku] += $qty;
                    $combo_category_qty[$product_category][$real_sku] += $qty;
                    // $category_sqty[$product_category][$real_sku] += $sqty;
                } else {
                    $category_qty[$box_number][$product_category][$real_sku] = $qty;
                    $combo_category_qty[$product_category][$real_sku] = $qty;
                    // $category_sqty[$product_category][$real_sku] = $sqty;
                }

                if (!isset($address_data[$box_number][$product_sku]['sku_cost'])) $address_data[$box_number][$product_sku]['sku_cost'] = 0;
                if (!isset($address_data[$box_number][$product_sku]['sku_weight'])) $address_data[$box_number][$product_sku]['sku_weight'] = 0;

                // category
                if ($item->getData('cost') || $item->getData('base_cost') || (isset($sku_color) && isset($sku_color[$real_sku]['cost']))) {
                    $unit_cost = 0;

                    if ($item->getData('cost') && $item->getData('cost') > 0) $unit_cost = $item->getData('cost');
                    elseif ($item->getData('base_cost') && $item->getData('base_cost') > 0) $unit_cost = $item->getData('base_cost');
                    elseif (isset($sku_color[$real_sku]['cost'])) $unit_cost = ($sku_color[$real_sku]['cost'] / 10000);

                    if (isset($unit_cost)) $unit_cost = ($cost_multiplier * $cost_base_to_display_rate * $unit_cost);

                    $address_data[$box_number][$product_sku]['sku_cost'] = $unit_cost;
                }

                if ($item->getWeight()) {
                    $unit_weight = 0;

                    if ($item->getWeight() && $item->getWeight() > 0) $unit_weight = $item->getWeight();

                    if (isset($unit_weight) && isset($weight_multiplier)) $unit_weight = ($weight_multiplier * $unit_weight);

                    $address_data[$box_number][$product_sku]['sku_weight'] = $unit_weight;
                }
                $coun++;
            }

            if (isset($sku_bundle) && is_array($sku_bundle)) ksort($sku_bundle);
        }

        if (isset($sku_order_id_qty) && is_array($sku_order_id_qty)) ksort($sku_order_id_qty);

        $supplier_previous = '';
        $supplier_item_action = '';
        $csv_output_product_list = '';
        $processed_skus = array();


        if ($group_manifest_by_category_yn == 0) {
            foreach ($sku_order_id_qty as $box_number => $sku_qty_array) {

                unset($csv_output_product_list);
                foreach ($sku_qty_array as $sku => $qty) {
                    unset($csv_data);
                    $sku_addon = '';
                    $max_qty_length_display = 0;
                    $display_sku = '';
                    $display_sku = htmlspecialchars_decode($sku_order_id_sku[$sku]);

                    $csv_data['qty'] = $qty;
                    if (isset($address_data[$sku]['url_key']))
                        $csv_data['product_key'] = $address_data[$sku]['url_key'];
                    if (isset($address_data[$sku]['product_url']))
                        $csv_data['product_url'] = $address_data[$sku]['product_url'];
                    $csv_data['sku'] = $display_sku;
                    $csv_data['product_id'] = isset($address_data[$sku]['product_id']) ? $address_data[$sku]['product_id'] : '';
                    $csv_data['product_id_simple'] = isset($address_data[$sku]['product_id_simple']) ? $address_data[$sku]['product_id_simple'] : '';
                    $csv_data['product_id_configurable'] = isset($address_data[$sku]['product_id_configurable']) ? $address_data[$sku]['product_id_configurable'] : '';
                    $csv_data['product_category'] = str_replace(',', ' +', @$address_data[$sku]['category']);
                    $csv_data['product_name'] = @$address_data[$sku]['sku_name']; //@$product_name;
                    $csv_data['average_unit_weight'] = round(@$address_data[$sku]['sku_weight'], 2);
                    $csv_data['total_weight'] = round((@$address_data[$sku]['sku_weight'] * $qty), 2);
                    $csv_data['sku_price'] = round(@$address_data[$sku]['sku_price'], 2);
                    $csv_data['sku_cost'] = round(@$address_data[$sku]['sku_cost'], 2);
                    $csv_data['average_unit_cost'] = round(@$address_data[$sku]['sku_cost'], 2);
                    $csv_data['total_cost'] = round((@$address_data[$sku]['sku_cost'] * $qty), 2);
                    $csv_data['total_price'] = round((@$address_data[$sku]['sku_price'] * $qty), 2);
                    $csv_data['order_price'] = round(@$address_data['order_price'], 2);
                    $csv_data['order_qty'] = @$address_data[$sku]['order_qty'];
                    $csv_data['stock_qty'] = @$address_data[$sku]['stock_qty'];
                    $csv_data['qty_in_stock_before_these_orders'] = @$address_data[$sku]['qty_in_stock_before_these_orders'];
                    $csv_data['qty_in_stock_after_these_orders'] = @$address_data[$sku]['qty_in_stock_after_these_orders'];
                    // $csv_data['description'] = trim($item->getDescription());
                    // $csv_data['short_description'] = trim($item->getShortDescription());
                    $csv_data['description'] = $address_data[$sku]['description'];
                    $csv_data['warehouse'] = $address_data[$sku]['warehouse'];
                    $csv_data['product_description'] = $address_data[$sku]['description'];
                    $csv_data['short_description'] = $address_data[$sku]['short_description'];
                    $csv_data['website'] = @$address_data[$sku]['website'];
                    $csv_data['store'] = @$address_data[$sku]['store'];
                    $csv_data['order_id'] = $order_id;
                    /* coupon code and label */
                    $csv_data['coupon_code'] = (isset($address_data[$order_id]['coupon_code']) ? $address_data[$order_id]['coupon_code'] : '');
                    $csv_data['coupon_label'] = (isset($address_data[$order_id]['coupon_label']) ? $address_data[$order_id]['coupon_label'] : '');
                    $csv_data['gift_product'] = (isset($message_data[$sku]['gift_product']) ? $message_data[$sku]['gift_product'] : '');
                    /* courierrules */
                    if (isset($address_data[$order_id]['courierrules_shipping_method']) && $address_data[$order_id]['courierrules_shipping_method'] != '')
                        $csv_data['courierrules_shipping_method'] = $address_data[$order_id]['courierrules_shipping_method'];
                    $csv_data['today_date'] = $address_data[$order_id]['today_date'];
                    /*Moogento RetailExpress Fields*/
                    $csv_data['retail_express_id'] = (isset($address_data[$order_id]['retail_express_id']) ? $address_data[$order_id]['retail_express_id'] : '');
                    $csv_data['retail_express_status'] = (isset($address_data[$order_id]['retail_express_status']) ? $address_data[$order_id]['retail_express_status'] : '');
                    $csv_data['retail_express_message'] = (isset($address_data[$order_id]['retail_express_message']) ? $address_data[$order_id]['retail_express_message'] : '');
                    $csv_output_product_list = '';
                    foreach ($column_map as $column_header => $column_attribute) {
                        // if value needs filling
                        $attribute_base = strtolower(trim(str_replace('%', '', $column_attribute)));
                        if (preg_match('~%~', $column_attribute)) {
                            //if (!isset($csv_data[$attribute_base])) $csv_data[$attribute_base] = '';
                            if (!isset($csv_data[$attribute_base])) {
                                $csv_data[$attribute_base] = '';
                                if (isset($csv_data[str_replace('_', '-', $attribute_base)]) && ($csv_data[str_replace('_', '-', $attribute_base)])) {
                                    $csv_data[$attribute_base] = $csv_data[str_replace('_', '-', $attribute_base)];
                                    $attribute_base = str_replace('_', '-', $attribute_base);
                                } else {
                                    if (isset($csv_data[str_replace('-', '_', $attribute_base)]) && ($csv_data[str_replace('-', '_', $attribute_base)])) {
                                        $csv_data[$attribute_base] = $csv_data[str_replace('-', '_', $attribute_base)];
                                        $attribute_base = str_replace('-', '_', $attribute_base);
                                    }
                                }
                            }
                            $csv_output_product_list .= $field_quotes . $csv_data[$attribute_base] . $field_quotes . $column_separator;
                        } elseif (preg_match('~\[~', $column_attribute)) {
                            $csv_output_product_list .= $field_quotes . str_replace(array('[', ']'), '', $column_attribute) . $field_quotes . $column_separator;
                        } else {
                            if (isset($sku_color[$sku][$column_attribute])) {
                                $csv_output_product_list .= $field_quotes . $sku_color[$sku][$column_attribute] . $field_quotes . $column_separator;
                            } else $csv_output_product_list .= $column_separator;
                        }
                    }
                    $csv_output_product_list = preg_replace('~,$~', '', $csv_output_product_list) . "\n";
                    $csv_output[$box_number] .= $csv_output_product_list;

                }
                // end roll_sku
            } // end roll box

        } // end if group by category == 0
        else {

            $_cat_qty = array();
            $_cat_cost = array();
            $sku_qty = array();
            $grand_total = array();
            $sku_addon = '';
            $max_qty_length_display = 0;
            $grand_total['cost'] = 0;
            $grand_total['qty'] = 0;
            unset($csv_data);
            $csv_data = array();
            $csv_data['total_cost'] = 0;
            $super_grand_total = array();
            $super_grand_total['qty'] = 0;
            $super_grand_total['cost'] = 0;
            $combo_cat_cost = array();
            $combo_cat_qty = array();
            $csv_output[99] = ''; //combo

            foreach ($category_qty as $box_number => $sku_qty_array) {
                $_cat_qty[$box_number] = array();

                foreach ($sku_qty_array as $cat_name => $sku_qty) {

                    foreach ($sku_qty as $cat_sku => $cat_sku_qty) {

                        if (isset($_cat_qty[$box_number][$cat_name])) $_cat_qty[$box_number][$cat_name] += $cat_sku_qty;
                        else $_cat_qty[$box_number][$cat_name] = $cat_sku_qty;

                        //combo
                        if (isset($_cat_qty[99][$cat_name])) $_cat_qty[99][$cat_name] += $cat_sku_qty;
                        else $_cat_qty[99][$cat_name] = $cat_sku_qty;

                        /**
                         * Could make this commented out but an option, so that it shows each boxes specific weights and costs, instead of the averages
                         */
                        /*
                         if(isset($_cat_cost[$box_number][$cat_name]) && isset($address_data[$box_number][$cat_sku]['sku_cost']) && isset($cat_sku_qty))
                         {
                         $_cat_cost[$box_number][$cat_name] += ($address_data[$box_number][$cat_sku]['sku_cost']*$cat_sku_qty);
                         }
                         elseif(isset($address_data[$box_number][$cat_sku]['sku_cost']) && isset($cat_sku_qty))
                         {
                         $_cat_cost[$box_number][$cat_name] = ($address_data[$box_number][$cat_sku]['sku_cost']*$cat_sku_qty);
                         }

                         if(isset($_cat_weight[$box_number][$cat_name]) && isset($address_data[$box_number][$cat_sku]['sku_weight']) && isset($cat_sku_qty))
                         {
                         $_cat_weight[$box_number][$cat_name] += ($address_data[$box_number][$cat_sku]['sku_weight']*$cat_sku_qty);
                         }
                         elseif(isset($address_data[$box_number][$cat_sku]['sku_weight']) && isset($cat_sku_qty))
                         {
                         $_cat_weight[$box_number][$cat_name] = ($address_data[$box_number][$cat_sku]['sku_weight']*$cat_sku_qty);
                         }*/


                        //combo
                        if (isset($_cat_cost[99][$cat_name]) && isset($address_data[$box_number][$cat_sku]['sku_cost']) && isset($cat_sku_qty)) {
                            $_cat_cost[99][$cat_name] += ($address_data[$box_number][$cat_sku]['sku_cost'] * $cat_sku_qty);
                        } elseif (isset($address_data[$box_number][$cat_sku]['sku_cost']) && isset($cat_sku_qty)) {
                            $_cat_cost[99][$cat_name] = ($address_data[$box_number][$cat_sku]['sku_cost'] * $cat_sku_qty);
                        }

                        if (isset($_cat_weight[99][$cat_name]) && isset($address_data[$box_number][$cat_sku]['sku_weight']) && isset($cat_sku_qty)) {
                            $_cat_weight[99][$cat_name] += ($address_data[$box_number][$cat_sku]['sku_weight'] * $cat_sku_qty);
                        } elseif (isset($address_data[$box_number][$cat_sku]['sku_weight']) && isset($cat_sku_qty)) {
                            $_cat_weight[99][$cat_name] = ($address_data[$box_number][$cat_sku]['sku_weight'] * $cat_sku_qty);
                        }
                        $message_data[$cat_name]['gift_product'] = (isset($message_data[$cat_sku]['gift_product']) ? $message_data[$cat_sku]['gift_product'] : '');

                    }
                }
            }


            // put compilation at end, to get correct grand totals
            ksort($_cat_qty);

            foreach ($_cat_qty as $box_number => $sku_qty_array) {
                $csv_output_product_list = '';
                $grand_total['cost'] = 0;
                $grand_total['qty'] = 0;
                foreach ($sku_qty_array as $_cat_name => $cat_qty) {
                    reset($csv_data);
                    $csv_data['qty'] = $cat_qty;
                    $csv_data['product_category'] = '{merge}' . str_replace(',', ' +', $_cat_name);
                    // use average value for all items in this category
                    if (isset($_cat_cost[99][$_cat_name])) $csv_data['total_cost'] = round($_cat_cost[99][$_cat_name], $cost_round);
                    // elseif(isset($_cat_cost[$box_number][$_cat_name])) $csv_data['total_cost'] = round($_cat_cost[$box_number][$_cat_name],$cost_round);
                    $csv_data['average_unit_cost'] = round(($csv_data['total_cost'] / $_cat_qty[99][$_cat_name]), $cost_round);
                    // flip back to make total cost = qty * rounded unit cost
                    $csv_data['total_cost'] = round(($csv_data['average_unit_cost'] * $csv_data['qty']), $cost_round);

                    if (isset($_cat_weight[99][$_cat_name])) $csv_data['total_weight'] = round($_cat_weight[99][$_cat_name], $weight_round);
                    // elseif(isset($_cat_weight[$box_number][$_cat_name])) $csv_data['total_weight'] = round($_cat_weight[$box_number][$_cat_name],$weight_round);
                    $csv_data['average_unit_weight'] = round(($csv_data['total_weight'] / $_cat_qty[99][$_cat_name]), $weight_round);
                    // flip back to make total weight = qty * rounded unit weight
                    $csv_data['total_weight'] = round(($csv_data['average_unit_weight'] * $csv_data['qty']), $weight_round);

                    $grand_total['cost'] += $csv_data['total_cost'];
                    $grand_total['qty'] += $csv_data['qty'];

                    $csv_data['gift_product'] = (isset($message_data[$_cat_name]['gift_product']) ? $message_data[$_cat_name]['gift_product'] : '');
                    $csv_data['coupon_code'] = (isset($address_data[$order_id]['coupon_code']) ? $address_data[$order_id]['coupon_code'] : '');
                    $csv_data['coupon_label'] = (isset($address_data[$order_id]['coupon_label']) ? $address_data[$order_id]['coupon_label'] : '');
                    $csv_data['today_date'] = $address_data[$order_id]['today_date'];
                    /*Moogento RetailExpress Fields*/
                    $csv_data['retail_express_id'] = (isset($address_data[$order_id]['retail_express_id']) ? $address_data[$order_id]['retail_express_id'] : '');
                    $csv_data['retail_express_status'] = (isset($address_data[$order_id]['retail_express_status']) ? $address_data[$order_id]['retail_express_status'] : '');
                    $csv_data['retail_express_message'] = (isset($address_data[$order_id]['retail_express_message']) ? $address_data[$order_id]['retail_express_message'] : '');
                    foreach ($column_map as $column_header => $column_attribute) {
                        $currency_prefix = '';
                        if (preg_match('~cost~i', $column_header)) $currency_prefix = '{rt}' . $cost_prefix . ' ';
                        if (preg_match('~weight~i', $column_header)) $currency_prefix = '{rt}';

                        // if value needs filling
                        $attribute_base = strtolower(trim(str_replace('%', '', $column_attribute)));
                        if (preg_match('~%~', $column_attribute)) {
                            //if (!isset($csv_data[$attribute_base])) $csv_data[$attribute_base] = '';
                            if (!isset($csv_data[$attribute_base])) {
                                $csv_data[$attribute_base] = '';
                                if (isset($csv_data[str_replace('_', '-', $attribute_base)]) && ($csv_data[str_replace('_', '-', $attribute_base)])) {
                                    $csv_data[$attribute_base] = $csv_data[str_replace('_', '-', $attribute_base)];
                                    $attribute_base = str_replace('_', '-', $attribute_base);
                                } else {
                                    if (isset($csv_data[str_replace('-', '_', $attribute_base)]) && ($csv_data[str_replace('-', '_', $attribute_base)])) {
                                        $csv_data[$attribute_base] = $csv_data[str_replace('-', '_', $attribute_base)];
                                        $attribute_base = str_replace('-', '_', $attribute_base);
                                    }
                                }
                            }
                            if ($strip_line_comma == 1)
                                $csv_data[$attribute_base] = split_line_comma($csv_data[$attribute_base]);

                            //quote output value
                            $output_value = trim($csv_data[$attribute_base]);
                            if ($this->_general['csv_quote_values_yn'] == 'double'){
                                $output_value = str_replace('"', '""', $output_value);
                            }elseif ($this->_general['csv_quote_values_yn'] == 'strip_all'){
                                $output_value = str_replace('"', '', $output_value);
                            }
                            //end quote output value

                            $csv_output_product_list .= $field_quotes . $currency_prefix . $output_value . $field_quotes . $column_separator;
                        } elseif (preg_match('~\[~', $column_attribute)) {
                            if ($strip_line_comma == 1)
                                $column_attribute = split_line_comma($column_attribute);

                            //quote output value
                            $output_value = trim(str_replace(array('[', ']'), '', $column_attribute));
                            if ($this->_general['csv_quote_values_yn'] == 'double'){
                                $output_value = str_replace('"', '""', $output_value);
                            }elseif ($this->_general['csv_quote_values_yn'] == 'strip_all'){
                                $output_value = str_replace('"', '', $output_value);
                            }
                            //end quote output value

                            $csv_output_product_list .= $field_quotes . $currency_prefix . $output_value . $field_quotes . $column_separator;
                        } else {
                            if (isset($sku_color[$sku][$column_attribute])) {
                                if ($strip_line_comma == 1)
                                    $sku_color[$sku][$column_attribute] = split_line_comma($sku_color[$sku][$column_attribute]);

                                //quote output value
                                $output_value = trim($sku_color[$sku][$column_attribute]);
                                if ($this->_general['csv_quote_values_yn'] == 'double'){
                                    $output_value = str_replace('"', '""', $output_value);
                                }elseif ($this->_general['csv_quote_values_yn'] == 'strip_all'){
                                    $output_value = str_replace('"', '', $output_value);
                                }
                                //end quote output value

                                $csv_output_product_list .= $field_quotes . $currency_prefix . $output_value . $field_quotes . $column_separator;
                            } else $csv_output_product_list .= $column_separator;
                        }
                    }

                    $csv_output_product_list = preg_replace('~,$~', '', $csv_output_product_list) . "\n";
                }
                $csv_output[$box_number] .= $csv_output_product_list;

                if ($box_number != 99) {
                    $csv_output[$box_number] .= "\n" . '" "," ","{merge}{b-rt}Total Value Of Goods:","{b-rt}' . $cost_prefix . ' ' . $grand_total['cost'] . '"';
                    $csv_output[$box_number] .= "\n" . '" "," ","{merge}{b-rt}Total Qty:","{b-rt}' . $grand_total['qty'] . '"';

                    $super_grand_total['qty'] += $grand_total['qty'];
                    $super_grand_total['cost'] += $grand_total['cost'];
                }

            }
            $csv_output_complete = $column_mapping_pre_a . '{h2}{merge2}PACKING LIST';
            ksort($csv_output);
            foreach ($csv_output as $box_number => $box_contents) {
                if ($box_number != 99) $csv_output_complete .= "\n\n" . $csv_output[$box_number];
            }
            $csv_output_complete .= "\n\n\n" . '{h2}{merge2}CLASSIFICATION SUMMARY' . "\n\n" . $csv_output_start; //.$csv_output_product_list."\n";
            $csv_output[99] .= "\n" . '" "," ","{merge}{b-rt}Total Value Of Goods:","{b-rt}' . $cost_prefix . ' ' . $super_grand_total['cost'] . '"';
            $csv_output[99] .= "\n" . '" "," ","{merge}{b-rt}Total Qty:","{b-rt}' . $super_grand_total['qty'] . '"';
            $csv_output_complete .= $csv_output[99];
            /**
             * If Excel...
             */
            if ($is_excel == 1) {
                require($this->action_path . '/lib/php-export-data.class.php');

                // 'browser' tells the library to stream the data directly to the browser.
                // other options are 'file' or 'string'
                $fileName = 'combined-shipping-manifest_' . Mage::getSingleton('core/date')->date('Y-m-d_H') . '.xml';
                $exporter = new ExportDataExcel('browser', $fileName);
                $exporter->initialize(); // starts streaming data to web browser

                $csv_output_array = explode("\n", $csv_output_complete);
                foreach ($csv_output_array as $key => $csv_line) {
                    $exporter->addRow(explode(',', str_replace(array('\"', '"', "\n"), array("'", '', ''), $csv_line)));
                }
                $exporter->finalize();
                exit;
            } else {
                $csv_output_complete = str_replace(array('{right}', '{left}', '{rt}', '{lt}', '{b-rt}', '{b-lt}', '{b}', '{u}', '{i}', '{ub}', '{ubi}', '{h1}', '{h2}', '{h1-i}', '{h2-i}', '{merge}', '{merge2}', '{merge3}'), '', $csv_output_complete);
            }
        }


        if ($manifest_or_picklist != 'manifest') {
            // ie order-combined csv, not cargo manifest
            //$csv_output_complete = $column_headers . "\n" . $csv_output[$box_number];
            $temp_str = '"{h2-i}Box #' . $box_number . '"';

            $output_str = $csv_output[$box_number];
            $output_str = str_replace($temp_str, '', $output_str);
            $csv_output_complete = $output_str;
        }
        if (!isset($csv_output_complete)) $csv_output_complete = '';

        return $csv_output_complete;

        /**
         * getCSVPickCombined 222222222 Template - END
         *********************************************
         ************************************************/
    }
}