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
 * File        Csvseparated.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Csvseparated extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    public function getCsvPickSeparated($orders = array(), $from_shipment = 'order') {
        $this->setGeneralCsvConfig();
        $date_format = Mage::getStoreConfig('pickpack_options/csvpick/date_format');
        $strip_line_comma = Mage::getStoreConfig('pickpack_options/csvpick/strip_line_commas');
        $total_quantity = 0;
        $total_cost = 0;
        $column_headers = '';
        $column_separator = $this->_general['csv_field_separator'];
        $field_quotes = '"';
        $column_map = array();
        $custom_attributes = array();
        $custom_option_attributes = array();
        $column_mapping_pre = trim($this->_getConfig('column_mapping', '', false, 'csvpick')); //col/sku
        $attribute_array = array('color', 'Size', 'style', 'siennastyle', 'season');
        $column_mapping_pre = trim(str_replace(array("\n", "\r", "\t", ';;;', ';;'), ';', $column_mapping_pre));
        $column_mapping_pre = str_ireplace(array(':;'), ':[ ];', $column_mapping_pre);
        $column_mapping_array = explode(';', $column_mapping_pre);
        $i = 0;
        foreach ($column_mapping_array as $key => $value) {
            $column_mapping_sub_array = explode(':', $value);
            if (trim($key) != '') {
                $key = trim($column_mapping_sub_array[0]);
                $column_headers .= $field_quotes . $key . $field_quotes . $column_separator;
                if (isset($column_mapping_sub_array[1]))
					$value = trim($column_mapping_sub_array[1]);
                $column_map[$i] = $value;
                $i++;
                // add custom columns to pull attribute info
                if ((strpos($value, '%') === false) && (strpos($value, '[') === false) && ($value != '')) {
                    if (is_array($value))
						$value = implode(',', $value);
                    $custom_attributes[] = $value;
                }
            }
            $key = '';
            $value = '';
        }
        // add in custom attributes to pull details
        if (is_array($custom_attributes)) {
            foreach ($custom_attributes as $value) {
                $attribute_array[] = $value;
            }
        }
        $column_headers = preg_replace('~[;,]$~', "\n", $column_headers);
        $csv_output = $column_headers;
		if($column_separator != ',')
			$csv_output .= "\n";
        $configurable_names = $this->_getConfig('pickpack_configname_separated', 'simple', false, 'picks'); //col/sku
        $product_id = NULL; // get it's ID
        $stock = NULL;
        $sku_stock = array();
        $product_id = NULL; // get it's ID
        $stock = NULL;

        $options_yn_base = $this->_getConfig('separated_options_yn', 0, false, 'picks'); // no, inline, newline
        $options_yn = $this->_getConfig('separated_options_yn', 0, false, 'picks'); // no, inline, newline

        if ($options_yn_base == 0)
			$options_yn = 0;
        $pickpack_options_filter_yn = $this->_getConfig('separated_options_filter_yn', 0, false, 'picks');
        $pickpack_options_filter = $this->_getConfig('separated_options_filter', 0, false, 'picks');
        $custom_rule1_yn = $this->_getConfig('custom_rule1_yn', 0, false, 'picks');
        $custom_rule2_yn = $this->_getConfig('custom_rule2_yn', 0, false, 'picks');
        $custom_weightbased_count = $this->_getConfig('custom_weightbased_count', 0, false, 'picks');
        $custom_weight_operand_1 = $this->_getConfig('custom_weight_operand_1', 0, false, 'picks');
        $custom_weightbased_1 = $this->_getConfig('custom_weightbased_1', 0, false, 'picks');
        $custom_weightbased_text_1 = $this->_getConfig('custom_weightbased_text_1', 0, false, 'picks');
        $custom_weight_operand_2 = $this->_getConfig('custom_weight_operand_2', 0, false, 'picks');
        $custom_weightbased_2 = $this->_getConfig('custom_weightbased_2', 0, false, 'picks');
        $custom_weightbased_text_2 = $this->_getConfig('custom_weightbased_text_2', 0, false, 'picks');
        $custom_weightbased = $this->_getConfig('custom_weightbased', 0, false, 'picks');
        $custom_weightbased_label = $this->_getConfig('custom_weightbased', 0, false, 'picks');

        $pickpack_options_filter_array = array();
        if ($pickpack_options_filter_yn == 0)
			$pickpack_options_filter = '';
        elseif (trim($pickpack_options_filter) != '') {
            $pickpack_options_filter_array = explode(',', $pickpack_options_filter);
            foreach ($pickpack_options_filter_array as $key => $value) {
                $pickpack_options_filter_array[$key] = trim($value);
            }
        }
        $pickpack_options_count_filter_array = array();
        $pickpack_options_count_filter = $this->_getConfig('separated_options_count_filter', 0, false, 'picks');
        if ($pickpack_options_filter_yn == 0)
			$pickpack_options_count_filter = '';
        elseif (trim($pickpack_options_count_filter) != '') {
            $pickpack_options_count_filter_array = explode(',', $pickpack_options_count_filter);
            foreach ($pickpack_options_count_filter_array as $key => $value) {
                $pickpack_options_count_filter_array[$key] = trim($value);
            }
        }

        $skuXInc = 0;

        /**
         * get store id
         */
        //$store_id = Mage::app()->getStore()->getId();

        $order_id_master = array();
        $sku_order_suppliers = array();
        $sku_order_id_options = array();
        $sku_bundle = array();
        $address_data = array();
        $skuoptions = array();

        foreach ($orders as $orderSingle) {
        	if(isset($orderItemCheck)) 
              unset($orderItemCheck);  
            $orderItemCheck = array();
            $order = Mage::getModel('sales/order')->load($orderSingle);
            Mage::app()->setCurrentStore($order->getStore()->getStoreId());
            $store_id = $order->getStore()->getId();
            $putOrderId = $order->getRealOrderId();
            $order_id = $putOrderId;
            $address_data[$order_id]['order_date'] = '';
            $address_data[$order_id]['order_date_plus48h'] = '';

            if ($order->getCreatedAtStoreDate())
                $address_data[$order_id]['order_date'] = Mage::getSingleton('core/date')->date($date_format,$order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));

            $extra_order_info_array = $order->getPayment()->getAdditionalInformation();

            if (isset($extra_order_info_array['personalnumber']))
				$address_data[$order_id]['personalnumber'] = $extra_order_info_array['personalnumber'];
            if (isset($extra_order_info_array['klarna_personalnumber']))
                $address_data[$order_id]['personalnumber'] = $extra_order_info_array['klarna_personalnumber'];
			else
				$address_data[$order_id]['personalnumber'] = '';

            $sku_shipping_method[$order_id] = clean_method($order->getShippingDescription(), 'shipping');
            $address_data[$order_id]['shipping_description'] = $sku_shipping_method[$order_id];
            $paymentInfo = $order->getPayment()->getMethodInstance()->getTitle();
            $payment_test = clean_method($paymentInfo, 'payment');

            $address_data[$order_id]['order_status'] = $order->getData('status');
            $address_data[$order_id]['order_subtotal'] = $order->getData('subtotal');
            $address_data[$order_id]['order_tax_amount'] = round($order->getData('tax_amount'), 2);
            $address_data[$order_id]['payment_method'] = $payment_test;
            $address_data[$order_id]['order_status'] = $order->getStatus();
			$address_data[$order_id]['store_id'] = $order->getStore()->getStoreId();
			$address_data[$order_id]['website_id'] = $order->getStore()->getWebsiteId();
			$address_data[$order_id]['store'] = $order->getStore()->getGroup()->getName();
            $address_data[$order_id]['website'] = $order->getStore()->getWebsite()->getName();
            $address_data[$order_id]['store_view'] = $order->getStore()->getName();

            if (Mage::helper('pickpack')->isInstalled('Camiloo_Channelunity')) {
                $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
                    ->setOrderFilter($order);
                $sources = array();
                foreach ($collection as $txn) {
                    $infoArray = $txn->getAdditionalInformation();
                    if (isset($infoArray['ServiceType'])) {
                        $sources[] = $infoArray['ServiceType'];
                    }
                }
                $address_data[$order_id]['chanel_unity_origin'] = implode(', ', $sources);
            }
				
            if (trim($order->getCustomerId()) != '' && !isset($address_data[$order_id]['customer_id']))
                $address_data[$order_id]['customer_id'] = trim($order->getCustomerId());
			else
                $address_data[$order_id]['customer_id'] = '';

            /*****  Get Warehouse information ****/
            if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) {
                $warehouse_helper = Mage::helper('warehouse');
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
			
            $address_data[$order_id]['warehouse'] = $warehouse_title;
            if (is_object($order->getShippingAddress())) {
                $address_data[$order_id]['ship-fullname'] = $order->getShippingAddress()->getName();
                $address_data[$order_id]['ship-firstname'] = $order->getShippingAddress()->getFirstname();
                $address_data[$order_id]['ship-lastname'] = $order->getShippingAddress()->getLastname();
                $address_data[$order_id]['ship-companyname'] = $order->getShippingAddress()->getCompany();
                $address_data[$order_id]['ship-street1'] = '';

                if ($order->getShippingAddress()->getStreet(1))
                    $address_data[$order_id]['ship-street1'] = $order->getShippingAddress()->getStreet(1);
                
                $address_data[$order_id]['ship-street2'] = '';
                if ($order->getShippingAddress()->getStreet(2))
                    $address_data[$order_id]['ship-street2'] = $order->getShippingAddress()->getStreet(2);

                $address_data[$order_id]['ship-streets'] = implode(',', $order->getShippingAddress()->getStreet());
                $address_data[$order_id]['ship-city'] = $order->getShippingAddress()->getCity();
                $address_data[$order_id]['ship-region'] = $order->getShippingAddress()->getRegion();
                $address_data[$order_id]['ship-region-code'] = $order->getShippingAddress()->getRegionCode();
                $address_data[$order_id]['ship-postcode'] = strtoupper($order->getShippingAddress()->getPostcode());

                $ship_country_id = $order->getShippingAddress()->getCountry(); 
                $address_data[$order_id]['ship-country-code'] = Mage::helper('pickpack')->get3digitcountry($ship_country_id);
                $address_data[$order_id]['ship-country-code-2char'] = $ship_country_id;
                $address_data[$order_id]['ship-country'] = Mage::helper('pickpack/functions')->clean_method(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()),'pdf');
                $address_data[$order_id]['ship-region-filtered'] = '';
                if (trim(strtolower($address_data[$order_id]['ship-country'])) != 'us')
                    $address_data[$order_id]['ship-region-filtered'] = $address_data[$order_id]['ship-region-code']; //'Y';
                
                $address_data[$order_id]['ship-telephone'] = $order->getShippingAddress()->getTelephone();
                $csv_data['ship-email'] = $order->getShippingAddress()->getEmail();
            }
            $address_data[$order_id]['customer_email'] = trim($order->getData('customer_email'));

            if (is_object($order->getBillingAddress())) {
                $address_data[$order_id]['bill-fullname'] = $order->getBillingAddress()->getName();
                $address_data[$order_id]['bill-firstname'] = $order->getBillingAddress()->getFirstname();
                $address_data[$order_id]['bill-lastname'] = $order->getBillingAddress()->getLastname();
                $address_data[$order_id]['bill-companyname'] = $order->getBillingAddress()->getCompany();
                $address_data[$order_id]['bill-street1'] = '';
                if ($order->getBillingAddress()->getStreet(1))
                    $address_data[$order_id]['bill-street1'] = $order->getBillingAddress()->getStreet(1);
                
                $address_data[$order_id]['bill-street2'] = '';
                if ($order->getBillingAddress()->getStreet(2))
                    $address_data[$order_id]['bill-street2'] = $order->getBillingAddress()->getStreet(2);
                
                $address_data[$order_id]['bill-city'] = $order->getBillingAddress()->getCity();
                $address_data[$order_id]['bill-region'] = $order->getBillingAddress()->getRegion();
                $address_data[$order_id]['bill-region-code'] = $order->getBillingAddress()->getRegionCode();
                $address_data[$order_id]['bill-postcode'] = strtoupper($order->getBillingAddress()->getPostcode());
				
                $bill_country_id = $order->getBillingAddress()->getCountry(); 
                $address_data[$order_id]['bill-country-code'] = Mage::helper('pickpack')->get3digitcountry($bill_country_id);
                $address_data[$order_id]['bill-country-code-2char'] = $bill_country_id;
                $address_data[$order_id]['bill-country'] = Mage::helper('pickpack/functions')->clean_method(Mage::app()->getLocale()->getCountryTranslation($order->getBillingAddress()->getCountryId()),'pdf');
								
                $address_data[$order_id]['bill-region-filtered'] = '';
                if (trim(strtolower($address_data[$order_id]['bill-country'])) != 'us')
                    $address_data[$order_id]['bill-region-filtered'] = $address_data[$order_id]['bill-region-code']; //'Y';

                $address_data[$order_id]['bill-telephone'] = $order->getBillingAddress()->getTelephone();
            }
            $address_data[$order_id]['bill-email'] = trim($order->getCustomerEmail());

            $address_data[$order_id]['subscription_status'] = '0';
            if (isset($address_data[$order_id]['bill-email'])) {
                $resource = Mage::getSingleton('core/resource');
                $readConnection = $resource->getConnection('core_read');
                $newsletter_table_name = $resource->getTableName('newsletter_subscriber');
                $sql_newsletter = "SELECT `subscriber_status` FROM `" . $newsletter_table_name . "` WHERE `subscriber_email` = '" . $address_data[$order_id]['bill-email'] . "' LIMIT 1;";
                $newsletter_sub_result = $readConnection->fetchAll($sql_newsletter);
                if (is_array($newsletter_sub_result) || !isset($newsletter_sub_result))
					$newsletter_sub_result = 0;
                $address_data[$order_id]['newsletter_subscription_status'] = trim($newsletter_sub_result);
            }

            if (!isset($order_id_master[$order_id]))
				$order_id_master[$order_id] = 0;

            $address_data[$order_id]['ddate'] = '';
            $address_data[$order_id]['dtime'] = '';
            $address_data[$order_id]['ddatetime'] = '';

            if (Mage::getResourceModel('ddate/ddate')) {
                $ddate = Mage::getResourceModel('ddate/ddate')->getDdateByOrder($order->getIncrementId());
                /*  $ddate is an array()   and delivery date and delivery time is one of
                 elements of this array */
                $address_data[$order_id]['ddate'] = $ddate['ddate']; /* return ddate. example : 2011-12-27 */
                $address_data[$order_id]['dtime'] = $ddate['dtime']; /* return ddtime . Example : 3:00-7:00 */
                $address_data[$order_id]['ddatetime'] = $ddate['ddate'] . ' ' . $ddate['dtime']; /* return ddtime . Example : 3:00-7:00 */
            }
            $order_currency_code = $order->getOrderCurrencyCode();
            $store_currency_code = $order->getStore()->getCurrentCurrencyCode();
            $address_data[$order_id]['order-currency'] = $order_currency_code;
            $address_data[$order_id]['base-currency'] = $store_currency_code;

            //TODO add new options
            $address_data[$order_id]['base-currency'] = $store_currency_code;

            $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);

            if (!isset($productXInc))
				$productXInc = 0;

            $product_build_item = array();
            $product_build = array();
            $coun = 1;
            $configurable_id = '';
            $skuoptions[$order_id]= array();
            foreach ($itemsCollection as $item) {

                if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $configurable_id = $item->getProductId();
                    $sku = $item->getProductOptionByCode('simple_sku');
                    $product_id = Mage::getModel('catalog/product')->setStoreId($store_id)->getIdBySku($sku);
                } else {
                    $sku = $item->getSku();
                    $product_id = $item->getProductId(); // get it's ID
                }
                
                if(!isset($orderItemCheck[$sku]))
					$orderItemCheck[$sku] = 1;
                else
                    $orderItemCheck[$sku] = $orderItemCheck[$sku]+=1;
                
                if($orderItemCheck[$sku]!=1 )
                  $sku = $sku.'_'.$orderItemCheck[$sku];

                $sku = $order_id.'_'.$sku;
                $simpleSku = $sku;
                $product = Mage::getModel('catalog/product')->setStoreId($store_id)->load($product_id);
                $product_build[$sku]['item_weight'] = $item->getData('weight');

                //$_manufacturerName = $product->getAttributeText('manufacturer');
                /**
                 * Get manufacturer id
                 */
                $_manufacturerId = $product->getManufacturer();
                if ($options_yn == 1) {
					$full_sku = trim($sku);
                    $parent_sku = $full_sku;
                    $sku = $parent_sku;
                    $product_build_item[] = $sku; //.'-'.$coun; // commented by 2jdesign
                    $product_build[$sku]['sku'] = $sku;
                    $product_sku = $sku;
                    $product_build[$sku]['sku'] = $product_sku;
                } else {
                    $product_build_item[] = $sku;
                    $product_build[$sku]['sku'] = $sku;
                    $product_sku = $sku;
                    $sku = $sku ;
                    $product_build[$sku]['sku'] = $product_sku;
                }
                
                $skuoptions[$order_id][$sku] = array();
                $options = $item->getProductOptions();
                $skuoptions[$order_id][$sku]['options'] = '';

                /* displaying custom option */
                if( isset($options['options']) && is_array($options['options']) ){
                   foreach($options['options'] as $key => $value ){
                        $address_data[$sku][strtolower($value['label'])] = $value['value'];                  
                        $custom_option_attributes[] = strtolower($value['label']);
                        $skuoptions[$order_id][$sku]['options'] .= $value['label'].' : '.$address_data[$sku][strtolower($value['label'])].'   ';

                   } 

              }
                // show for bundle children.
                $storeId = Mage::app()->getStore()->getId();
                
				if (isset($options['bundle_options']) && is_array($options['bundle_options'])) {
                    $children = $item->getChildrenItems();
                  
				    if (count($children)) {
                        foreach ($children as $child) {
                            $product_child = Mage::getModel('catalog/product')->setStoreId($storeId)->load($child->getProductId());
                            $sku_b = $child->getSku();
                            $price_b = $child->getPriceInclTax();
                            $qty_b = (int)$child->getQtyOrdered();
                            $name_b = $child->getName();
                            $childProductId = $child->getProductId();
                            $sku_bundle[$order_id][$sku][] = $sku_b;
                            $sku_order_id_sku_simple[$order_id][$sku_b] = $sku_b;
                            $sku_order_id_qty[$order_id][$sku_b] = $qty_b;
                            $sku_order_id_sku[$order_id][$sku_b] = $sku_b;
                            $address_data[$sku_b]['sku_price'] = $price_b;
                            $address_data[$sku_b]['sku_name'] = preg_replace('/[^a-zA-Z0-9\.\s]/', '', $name_b);
                           
						    if ($child->getWeight())
                                $address_data[$sku_b]['weight'] = $child->getWeight();
                            else
								$address_data[$sku_b]['weight'] = '';

                        }
                    }
                } else
                    $sku_bundle[$order_id][$sku] = '';

                /*
                 [options] => Array
                 (
                 [0] => Array
                 (
                 [label] => Styrka
                 [value] => -6.50
                 [print_value] => -6.50
                 [option_id] => 278
                 [option_type] => drop_down
                 [option_value] => 4152
                 [custom_view] =>
                 )

                 )
                 */
                //Get custom attribute data
                $shelving = '';
                $supplier = '';
                foreach ($attribute_array as $key => $value) {
                    $shelving_attribute = $value;
                    $sku_color[$order_id][$sku][$shelving_attribute] = '';

                    if ($product->getData($shelving_attribute)) {
                        $attributeValue = $product->getData($shelving_attribute);
                        $attributeName = $shelving_attribute;
                        $attributeOptions = getProductAttributeOptions($product_id, $attributeName);

                        if (!empty($attributeOptions)) {
                            $result = search($attributeOptions, 'value', $attributeValue);

                            if (isset($result[0]['label']))
                                $sku_color[$order_id][$sku][$shelving_attribute] = preg_replace('/[^a-zA-Z0-9\s\.\-\:\?\/\&\=]/', '', $result[0]['label']);
                        } else
                            $sku_color[$order_id][$sku][$shelving_attribute] = preg_replace('/[^a-zA-Z0-9\s\.\-\:\?\/\&\=]/', '', $attributeValue);
                    }
                }
                /********************************************************/
                // qty in this order of this sku
                $qty = $item->getIsQtyDecimal() ? $item->getQtyOrdered() : (int)$item->getQtyOrdered();
                $sqty = $item->getIsQtyDecimal() ? $item->getQtyShipped() : (int)$item->getQtyShipped();
                // total qty in all orders for this sku
                if (isset($sku_qty[$sku]))
					$sku_qty[$sku] = ($sku_qty[$sku] + $qty);
                else
					$sku_qty[$sku] = $qty;
                $total_quantity = $total_quantity + $qty;

                $cost = 0;
                if (isset($product))
					$cost = ($qty * $product->getCost());
                $total_cost = $total_cost + $cost;
                $sku_master[$sku] = $sku;
                $price = $item->getPrice();
                $address_data[$sku]['sku_price'] = $price;
          
		        if (isset($address_data[$order_id]['order_qty']))
					$address_data[$order_id]['order_qty'] = ($address_data[$order_id]['order_qty'] + $qty);
                else 
					$address_data[$order_id]['order_qty'] = $qty;

                $address_data[$sku]['product_id'] = $product_id;
                $address_data[$sku]['product_id_simple'] = $product_id;
                $address_data[$sku]['product_id_configurable'] = $configurable_id;

                if ($product && $configurable_names == 'simple') {
                    $_newProduct = $product;
                    if ($_newProduct->getData('name'))
						$sku_name[$sku] = $_newProduct->getData('name');
                } elseif ($configurable_names == 'configurable')
                    $sku_name[$sku] = $item->getName();
                elseif ($configurable_names == 'custom' && $configurable_names_attribute != '') {
                    $customname = '';

                    if ($product) {
                        $_newProduct = $product;
                        if ($_newProduct->getData($configurable_names_attribute))
							$customname = $_newProduct->getData('' . $configurable_names_attribute . '');
                    } elseif ($product->getData('' . $configurable_names_attribute . ''))
                        $customname = $product->getData($configurable_names_attribute);
					else
						$customname = '';

                    if (trim($customname) != '') {
                        if ($product->getAttributeText($configurable_names_attribute))
                            $customname = $product->getAttributeText($configurable_names_attribute);
                        elseif ($product[$configurable_names_attribute])
							$customname = $product[$configurable_names_attribute];
                        else $customname = '';
                    }

                    if (trim($customname) != '') {
                        if (is_array($customname))
							$customname = implode(',', $customname);
                        $customname = preg_replace('~,$~', '', $customname);
                    } else $customname = '';

                    $sku_name[$sku] = $customname;
                }

                $address_data[$sku]['sku_name'] = $item->getName();
                $temp_product = $product;
                $address_data[$sku]['description'] = strip_tags($temp_product->getDescription());
                $address_data[$sku]['product_description'] = strip_tags($temp_product->getDescription());
                $address_data[$sku]['short_description'] = strip_tags($temp_product->getShortDescription());
                $address_data[$sku]['url_key'] = '';
                $address_data[$sku]['product_url'] = '';
                if($temp_product->getUrlPath()){
                    $address_data[$sku]['url_key'] = ($temp_product->getUrlPath());
                    $address_data[$sku]['product_url'] = ($temp_product->getProductUrl(array('_store_to_url' => true)));
                }
                if ($item->getWeight())
                    $address_data[$sku]['weight'] = $item->getWeight();
                else 
					$address_data[$sku]['weight'] = '';
                $address_data[$sku]['sku_name'] = trim($address_data[$sku]['sku_name']);

                if (isset($options['options']) && is_array($options['options'])) {
                    $i = 0;
                    if (isset($options['options'][$i]))
						$continue = 1;
                    while ($continue == 1) {
                        $options_name_temp[$i] = trim(htmlspecialchars_decode($options['options'][$i]['label'] . ' : ' . $options['options'][$i]['value']));
                        $options_name_temp[$i] = str_replace(array('select ', 'enter ', 'would you Like to ', 'please enter ', 'your '), '', $options_name_temp[$i]);
                        $options_name_temp[$i] = preg_replace('~\((.*)\)~i', '', $options_name_temp[$i]);

                        $options_name_temp_nolabel[$i] = trim(htmlspecialchars_decode($options['options'][$i]['value']));
                        $options_name_temp_nolabel[$i] = str_replace(array('select ', 'enter ', 'would you Like to ', 'please enter ', 'your '), '', $options_name_temp_nolabel[$i]);
                        $options_name_temp_nolabel[$i] = preg_replace('~\((.*)\)~i', '', $options_name_temp_nolabel[$i]);

                        $i++;
                        $continue = 0;
                        if (isset($options['options'][$i]))
							$continue = 1;
                    }

                    $plus_options[$order_id][$sku] = implode('_', $options_name_temp);
                    $plus_options_nolabel[$order_id][$sku] = implode('_', $options_name_temp_nolabel);
                    $i = 0;
                    $continue = 0;
                    $opt_count = 0;

                    if (isset($options['options'][$i]))
						$continue = 1;

                    $sku_order_id_options[$order_id][$sku] = ' ';

                    while ($continue == 1) {
                        if ($i > 0) 
							$sku_order_id_options[$order_id][$sku] .= ' ';
                        $sku_order_id_options[$order_id][$sku] .= htmlspecialchars_decode('[ ' . $options['options'][$i]['label'] . ' : ' . $options['options'][$i]['value'] . ' ]');

                        // if show options as a group
                        if ($options_yn != 0 && $opt_count == 0) {
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

                                if (isset($options_name_temp[$opt_count]))
									$options_name[$order_id][$sku][$options_sku_single] = $options_name_temp[$opt_count];

                                if (isset($options_sku[$order_id][$options_sku_single]) && (!in_array($options_sku_single, $pickpack_options_filter_array))) {
                                    $options_sku[$order_id][$options_sku_single] = ($sku_qty[$sku] + $options_sku[$order_id][$options_sku_single]);
                                    $options_sku_parent[$order_id][$sku][$options_sku_single] = ($qty + $options_sku_parent[$order_id][$sku][$options_sku_single]);
                                } elseif (!in_array($options_sku_single, $pickpack_options_filter_array)) {
                                    $options_sku[$order_id][$options_sku_single] = ($sku_qty[$sku]);
                                    $options_sku_parent[$order_id][$sku][$options_sku_single] = $qty; //($sku_qty[$sku]);
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

                if (isset($sku_order_id_qty[$order_id][$sku])) {
                    $sku_order_id_qty[$order_id][$sku] = ($sku_order_id_qty[$order_id][$sku] + $qty);
                    $sku_order_id_sku[$order_id][$sku] = $product_sku;
                } else {
                    $sku_order_id_qty[$order_id][$sku] = $qty;
                    $sku_order_id_sku[$order_id][$sku] = $product_sku;
                }
                if (isset($sku_order_id_sqty[$order_id][$sku]))
                    $sku_order_id_sqty[$order_id][$sku] = ($sku_order_id_sqty[$order_id][$sku] + $sqty);
                else
                    $sku_order_id_sqty[$order_id][$sku] = $sqty;
                
                $sku_order_id_sku_simple[$order_id][$sku] = $simpleSku;

                if (!isset($max_qty_length))
					$max_qty_length = 2;

                if (strlen($sku_order_id_qty[$order_id][$sku]) > $max_qty_length)
					$max_qty_length = strlen($sku_order_id_qty[$order_id][$sku]);

                if (strlen($sku_order_id_sqty[$order_id][$sku]) > $max_qty_length)
					$max_qty_length = strlen($sku_order_id_sqty[$order_id][$sku]);


                if (isset($split_supplier_yn) && $split_supplier_yn != 'no') {
                    if (!isset($sku_order_suppliers[$order_id]))
						$sku_order_suppliers[$order_id][] = $supplier;
                    elseif (!in_array($supplier, $sku_order_suppliers[$order_id]))
						$sku_order_suppliers[$order_id][] = $supplier;
                }
                $coun++;

            }
            $orderComments = $order->getAllStatusHistory();
            foreach ($orderComments as $comment) {
                $body = $comment->getData('comment');
                if ($body !== NULL) {
                    if (isset($comments_unfiltered[$order_id]['comment']))
                        $comments_unfiltered[$order_id]['comments_unfiltered'] = $comments_unfiltered[$order_id]['comment'] . "\n" . trim($body);
                    else
                        $comments_unfiltered[$order_id]['comments_unfiltered'] = trim($body);
                    $body = trim(Mage::helper('pickpack/functions')->clean_method($body, 'pdf_more'));
                    $body = $this->_getTruncatedComment($body);
                    if (isset($comment_data[$order_id]['comments']))
                        $comment_data[$order_id]['comments'] = $comment_data[$order_id]['comments'] . "\n" . trim($body);
                    else
                        $comment_data[$order_id]['comments'] = trim($body);
                    if ($comment->getData("is_visible_on_front") == 1) {
                        if (isset($comment_frontend_data[$order_id]['comments_frontend_only']))
                            $comment_frontend_data[$order_id]['comments_frontend_only'] = $comment_frontend_data[$order_id]['comments_frontend_only'] . "\n" . trim($body);
                        else
                            $comment_frontend_data[$order_id]['comments_frontend_only'] = trim($body);
                    }
                }
            }
            $tracking = array();
            $tracking_number = array();
            $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($order)
                ->load();
            foreach ($shipmentCollection as $shipment) {
                foreach ($shipment->getAllTracks() as $tracknum) {
                    $tracking_number[] = $tracknum->getNumber();
                }
            }
            $tracking_number = implode(',', $tracking_number);
            $tracking[$order_id]['tracking'] = trim($tracking_number);

            $base_total_purchased[$order_id]['base_total_purchased'] = round($order->getBaseGrandTotal(), 2);
            $total_purchased[$order_id]['total_purchased'] = round($order->getGrandTotal(), 2);

            $base_total_paid[$order_id]['base_total_paid'] = round($order->getBaseTotalPaid(), 2);
            $total_paid[$order_id]['total_paid'] = round($order->getTotalPaid(), 2);

            $shipping_price[$order_id]['shipping_price'] = round($order->getShippingAmount(), 2);
            $base_shipping_price[$order_id]['base_shipping_price'] = round($order->getBaseShippingAmount(), 2);

            $total_invoiced[$order_id]['total_invoiced'] = round($order->getTotalInvoiced(), 2);
            $base_total_invoiced[$order_id]['base_total_invoiced'] = round($order->getBaseTotalInvoiced(), 2);

            $order_subtotal[$order_id]['order_subtotal'] = round($order->getSubtotal(), 2);
            $base_order_subtotal[$order_id]['base_order_subtotal'] = round($order->getBaseSubtotal(), 2);
            // Get gift message
            $message_data = array();
            $message = Mage::getModel('giftmessage/message');
            $gift_message_id = $order->getGiftMessageId();
            if (!is_null($gift_message_id)) {
                $message->load((int)$gift_message_id);
                $gift_sender = $message->getData('sender');
                $message_data[$order_id]['gift_messages'] = 'From: ' . $gift_sender;
                $gift_recipient = $message->getData('recipient');
                $message_data[$order_id]['gift_messages'] = $message_data[$order_id]['gift_messages'] . "\n" . 'To: ' . $gift_recipient;

                $gift_message = $message->getData('message');
                //$gift_message = trim(Mage::helper('pickpack/functions')->clean_method($gift_message, 'pdf_more'));
                $message_data[$order_id]['gift_messages'] = $message_data[$order_id]['gift_messages'] . "\n" . 'Message: ' . $gift_message;
            }
            //Get shipping method
            $shipping_method = clean_method($order->getShippingDescription(), 'shipping');
            if (trim($shipping_method) != '')
                $ship_method[$order_id]['ship-method'] = trim($shipping_method); //clean_method($order->getShippingDescription(), 'shipping');
            else
                $ship_method[$order_id]['ship-method'] = 'not set';

            //Get market place order id
            $ebay_order_id = '';
            $ebay_final_fee = '';
            $paypal_transaction_id = '';
            $paypal_fee = '';
            if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
                if ((Mage::helper('core')->isModuleEnabled('Ess_M2ePro'))) {
                    $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
                    $collection->addFieldToFilter('magento_order_id', $order->getData('entity_id'));
                    $collection->setCurPage(1) // 2nd page
                        ->setPageSize(1);
                    $collection_data = $collection->getData();

                    if (is_array($collection_data) && isset($collection_data[0]['ebay_order_id']))
                        $ebay_order_id = $collection_data[0]['ebay_order_id'];
                    else
                        $ebay_order_id = '';
                    if ($ebay_order_id) {
                        $transaction_item_id = explode('-', $ebay_order_id);
                        $item_collection = Mage::getModel('M2ePro/Ebay_Order_Item')->getCollection();
                        $item_collection->addFieldToFilter('transaction_id', $transaction_item_id[1]);
                        $item_collection->addFieldToFilter('item_id', $transaction_item_id[0]);
                        $item_collection_data = $item_collection->getData();
                        $paypal = Mage::getModel('M2ePro/Ebay_Order')->load($collection_data[0]['id'])->getExternalTransactionsCollection()->getData();
                        $ebay_final_fee = round($item_collection_data[0]['final_fee'], 2);
                        $paypal_transaction_id = $paypal[0]['transaction_id'];
                        $paypal_fee = round($paypal[0]['fee'], 2);
                    }
                }

            }

            $amazon_order_id = '';
            if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
                if ((Mage::helper('core')->isModuleEnabled('Ess_M2ePro'))) {
                    $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');
                    $collection->addFieldToFilter('magento_order_id', $order->getData('entity_id'));
                    $collection->setCurPage(1) // 2nd page
                        ->setPageSize(1);
                    if (($collection->getData('amazon_order_id'))) {
                        $collection_data = $collection->getData();
                        if (is_array($collection_data))
                            $amazon_order_id = $collection_data[0]['amazon_order_id'];
                        else
                            $amazon_order_id = '';
                        if ($amazon_order_id) {
                            // $transaction_item_id = explode('-', $amazon_order_id);
                            // $item_collection = Mage::getModel('M2ePro/Ebay_Order_Item')->getCollection();
                            // $item_collection->addFieldToFilter('transaction_id', $transaction_item_id[1]);
                            // $item_collection->addFieldToFilter('item_id', $transaction_item_id[0]);
                            // $item_collection_data = $item_collection->getData();
                            // $paypal = Mage::getModel('M2ePro/Ebay_Order')->load($collection_data[0]['id'])->getExternalTransactionsCollection()->getData();
                            // $ebay_final_fee = $item_collection_data['final_fee'];
                            // $paypal_transaction_id = $paypal['transaction_id'];
                            // $paypal_fee = $paypal['fee'];
                            $ebay_final_fee = "";
                            $paypal_transaction_id = "";
                            $paypal_fee = "";
                        }
                    }

                }

            }
            if ($ebay_order_id != '') {
                $marketplace[$order_id]['marketplace_id'] = $ebay_order_id;
                $marketplace[$order_id]['ebay_order_id'] = $ebay_order_id;
                $marketplace[$order_id]['ebay_final_fee'] = $ebay_final_fee;
                $marketplace[$order_id]['paypal_transaction_id'] = $paypal_transaction_id;
                $marketplace[$order_id]['paypal_fee'] = $paypal_fee;
            } else {
                $marketplace[$order_id]['marketplace_id'] = $amazon_order_id;
                $marketplace[$order_id]['amazon_order_id'] = $amazon_order_id;
                $marketplace[$order_id]['ebay_final_fee'] = $ebay_final_fee;
                $marketplace[$order_id]['paypal_transaction_id'] = $paypal_transaction_id;
                $marketplace[$order_id]['paypal_fee'] = $paypal_fee;
            }

            if (isset($sku_bundle[$order_id]) && is_array($sku_bundle[$order_id])) 
				ksort($sku_bundle[$order_id]);
            $order_weight = $order->getData('weight');
            $order_weight = round($order_weight, 2);
            $shipping_weight = $order_weight;
            $row_weight[$order_id]['weight'] = $shipping_weight;
            $row_weight[$order_id]['custom_weightbased_text'] = '';
            $row_weight[$order_id]['custom_weightbased_count'] = '';
         
		    if (isset($custom_weight_yn) && $custom_weight_yn == 1) {
         
		        if ($custom_rule1_yn == 1) 
                    $row_weight[$order_id]['custom_weightbased_text'] = $this->getCustomWeightText($shipping_weight, $custom_weight_operand_1, $custom_weightbased_1, $custom_weightbased_text_1);
                
                if ($custom_rule2_yn == 1 && $row_weight[$order_id]['custom_weightbased_text'] == '')
                    $row_weight[$order_id]['custom_weightbased_text'] = $this->getCustomWeightText($shipping_weight, $custom_weight_operand_2, $custom_weightbased_2, $custom_weightbased_text_2);
                
                if ($custom_weightbased_count == 1)
                    $row_weight[$order_id]['custom_weightbased_count'] = $this->getCustomWeightCount($shipping_weight, $custom_weightbased, $custom_weightbased_label);
                
            }
            $address_data[$order_id]['order_price'] = (float)($order->getSubtotal());

            /* courierrules_shipping_method */
            $courierrules_shipping_method = $order->getData('courierrules_shipping_method');
            if( isset($courierrules_shipping_method) && $courierrules_shipping_method != '')
                $address_data[$order_id]['courierrules_shipping_method'] = $order->getData('courierrules_shipping_method');
            
            /* today date */
            $address_data[$order_id]['today_date'] = Mage::getSingleton('core/date')->date($date_format);            
			
            /* coupon code */            
            $address_data[$order_id]['coupon_code'] = $order->getCouponCode();                      
            if( isset($address_data[$order_id]['coupon_code']) && $address_data[$order_id]['coupon_code'] != '' ){
                  $coupon_rule = Mage::getModel('salesrule/coupon')->loadByCode($address_data[$order_id]['coupon_code']);
                  $rule_id = $coupon_rule->getData('rule_id');
                  $store_id = $order->getStore()->getId();
                  $label = get_coupon_label($rule_id,$store_id);
                  $address_data[$order_id]['coupon_label'] = $label;
            }            
            
            /* Moogento Retail Express Attributes */
            if( getMooRetailExpressAttribute() ){
                $address_data[$order_id]['retail_express_id '] = $order->getData('retail_express_id');
                $address_data[$order_id]['retail_express_status'] = $order->getData('retail_express_status ');
                $address_data[$order_id]['retail_express_message'] = $order->getData('retail_express_message ');
            }  
            
            if (isset($sku_bundle[$order_id]) && is_array($sku_bundle[$order_id])) 
				ksort($sku_bundle[$order_id]);
        }

        if (isset($order_id_master) && is_array($order_id_master)) 
			ksort($order_id_master);

        if (isset($sku_order_id_qty) && is_array($sku_order_id_qty)) 
			ksort($sku_order_id_qty);

        if (isset($sku_master) && is_array($sku_master)) 
			ksort($sku_master);

        if (isset($supplier_master) && is_array($supplier_master)) 
			ksort($supplier_master);

        if ($this->_general['csv_quote_values_yn'] == '0')
            $field_quotes = '';

        foreach ($order_id_master as $order_id => $value) {
            unset($csv_data);
            $csv_data['order_price'] = isset($address_data[$order_id]['order_price'])?round($address_data[$order_id]['order_price'], 2):'';
            $csv_data['order_qty'] = isset($address_data[$order_id]['order_qty'])?$address_data[$order_id]['order_qty']:'';
            $csv_data['chanel_unity_origin'] = isset($address_data[$order_id]['chanel_unity_origin'])?$address_data[$order_id]['chanel_unity_origin']:'';
            $csv_data['website'] = isset($address_data[$order_id]['website'])?$address_data[$order_id]['website']:'';
            $csv_data['store'] = isset($address_data[$order_id]['store'])?$address_data[$order_id]['store']:'';
            $csv_data['store_view'] = isset($address_data[$order_id]['store_view'])?$address_data[$order_id]['store_view']:'';
            $csv_data['store_id'] = isset($address_data[$order_id]['store_id'])?$address_data[$order_id]['store_id']:'';
            $csv_data['website_id'] = isset($address_data[$order_id]['website_id'])?$address_data[$order_id]['website_id']:'';
            $csv_data['order_id'] = isset($order_id)?$order_id:'';

            $csv_data['warehouse'] = isset($address_data[$order_id]['warehouse'])?$address_data[$order_id]['warehouse']:'';
            $csv_data['subscription_status'] = isset($address_data[$order_id]['subscription_status'])?$address_data[$order_id]['subscription_status']:'';
            $csv_data['personalnumber'] = isset($address_data[$order_id]['personalnumber'])?$address_data[$order_id]['personalnumber']:'';
            $csv_data['order_status'] = isset($address_data[$order_id]['order_status'])?$address_data[$order_id]['order_status']:'';
            $csv_data['payment_method'] = isset($address_data[$order_id]['payment_method'])?$address_data[$order_id]['payment_method']:'';
            $csv_data['shipping_description'] = isset($address_data[$order_id]['shipping_description'])?$address_data[$order_id]['shipping_description']:'';
            $csv_data['customer_id'] = isset($address_data[$order_id]['customer_id'])?$address_data[$order_id]['customer_id']:'';
            $csv_data['customer_email'] = isset($address_data[$order_id]['customer_email'])?$address_data[$order_id]['customer_email']:'';
            $csv_data['order_date'] = isset($address_data[$order_id]['order_date'])?$address_data[$order_id]['order_date']:'';
            $csv_data['order_date_plus48h'] = isset($address_data[$order_id]['order_date_plus48h'])?$address_data[$order_id]['order_date_plus48h']:'';

            $csv_data['ddate'] = isset($address_data[$order_id]['ddate'])?$address_data[$order_id]['ddate']:'';
            $csv_data['dtime'] = isset($address_data[$order_id]['dtime'])?$address_data[$order_id]['dtime']:'';
            $csv_data['ddatetime'] = isset($address_data[$order_id]['ddatetime'])?$address_data[$order_id]['ddatetime']:'';

            $csv_data['ship_name'] = isset($address_data[$order_id]['ship-fullname'])?$address_data[$order_id]['ship-fullname']:'';
            $csv_data['ship_firstname'] = isset($address_data[$order_id]['ship-firstname'])?$address_data[$order_id]['ship-firstname']:'';
            $csv_data['ship_lastname'] = isset($address_data[$order_id]['ship-lastname'])?$address_data[$order_id]['ship-lastname']:'';
            $csv_data['ship_company'] = isset($address_data[$order_id]['ship-companyname'])?$address_data[$order_id]['ship-companyname']:'';
            $csv_data['ship_streets'] = isset($address_data[$order_id]['ship-streets'])?$address_data[$order_id]['ship-streets']:'';
            $csv_data['ship_street1'] = isset($address_data[$order_id]['ship-street1'])?$address_data[$order_id]['ship-street1']:'';
            $csv_data['ship_street2'] = isset($address_data[$order_id]['ship-street2'])?$address_data[$order_id]['ship-street2']:'';
            $csv_data['ship_city'] = isset($address_data[$order_id]['ship-city'])?$address_data[$order_id]['ship-city']:'';
            $csv_data['ship_region'] = isset($address_data[$order_id]['ship-region'])?$address_data[$order_id]['ship-region']:'';
            $csv_data['ship_region_code'] = isset($address_data[$order_id]['ship-region-code'])?$address_data[$order_id]['ship-region-code']:'';
            $csv_data['ship_postcode'] = isset($address_data[$order_id]['ship-postcode'])?$address_data[$order_id]['ship-postcode']:'';
            $csv_data['ship_country'] = isset($address_data[$order_id]['ship-country'])?$address_data[$order_id]['ship-country']:'';
            $csv_data['ship_country_code'] = isset($address_data[$order_id]['ship-country-code'])?$address_data[$order_id]['ship-country-code']:'';
            $csv_data['ship_country_code_2char'] = isset($address_data[$order_id]['ship-country-code-2char'])?$address_data[$order_id]['ship-country-code-2char']:'';
            $csv_data['ship_region_filtered'] = isset($address_data[$order_id]['ship-region-filtered'])?$address_data[$order_id]['ship-region-filtered']:'';
            $csv_data['ship_telephone'] = isset($address_data[$order_id]['ship-telephone'])?$address_data[$order_id]['ship-telephone']:'';
            $csv_data['ship_email'] = isset($address_data[$order_id]['ship-email'])?$address_data[$order_id]['ship-email']:'';

            $csv_data['bill_name'] = isset($address_data[$order_id]['bill-fullname'])?$address_data[$order_id]['bill-fullname']:'';
            $csv_data['bill_firstname'] = isset($address_data[$order_id]['bill-firstname'])?$address_data[$order_id]['bill-firstname']:'';
            $csv_data['bill_lastname'] = isset($address_data[$order_id]['bill-lastname'])?$address_data[$order_id]['bill-lastname']:'';
            $csv_data['bill_company'] = isset($address_data[$order_id]['bill-companyname'])?$address_data[$order_id]['bill-companyname']:'';
            $csv_data['bill_streets'] = isset($address_data[$order_id]['bill-streets'])?$address_data[$order_id]['bill-streets']:'';
            $csv_data['bill_street1'] = isset($address_data[$order_id]['bill-street1'])?$address_data[$order_id]['bill-street1']:'';
            $csv_data['bill_street2'] = isset($address_data[$order_id]['bill-street2'])?$address_data[$order_id]['bill-street2']:'';
            $csv_data['bill_city'] = isset($address_data[$order_id]['bill-city'])?$address_data[$order_id]['bill-city']:'';
            $csv_data['bill_region'] = isset($address_data[$order_id]['bill-region'])?$address_data[$order_id]['bill-region']:'';
            $csv_data['bill_region_code'] = isset($address_data[$order_id]['bill-region-code'])?$address_data[$order_id]['bill-region-code']:'';
            $csv_data['bill_postcode'] = isset($address_data[$order_id]['bill-postcode'])?$address_data[$order_id]['bill-postcode']:'';
            $csv_data['bill_country'] = isset($address_data[$order_id]['bill-country'])?$address_data[$order_id]['bill-country']:'';
            $csv_data['bill_country_code'] = isset($address_data[$order_id]['bill-country-code'])?$address_data[$order_id]['bill-country-code']:'';
            $csv_data['bill_country_code_2char'] = isset($address_data[$order_id]['bill-country-code-2char'])?$address_data[$order_id]['bill-country-code-2char']:'';
            $csv_data['bill_region_filtered'] = isset($address_data[$order_id]['bill-region-filtered'])?$address_data[$order_id]['bill-region-filtered']:'';
            $csv_data['bill_telephone'] = isset($address_data[$order_id]['bill-telephone'])?$address_data[$order_id]['bill-telephone']:'';
            $csv_data['bill_email'] = isset($address_data[$order_id]['bill-email'])?$address_data[$order_id]['bill-email']:'';
          
		    $csv_data['gift_messages'] = (isset($message_data[$order_id]['gift_messages']) ? $message_data[$order_id]['gift_messages'] : '');
            $csv_data['order_currency'] = isset($address_data[$order_id]['order-currency'])?$address_data[$order_id]['order-currency']:'';
            $csv_data['base_currency'] = isset($address_data[$order_id]['base-currency'])?$address_data[$order_id]['base-currency']:'';
			
			// backwards compatible
			$csv_data['ship-fullname'] = $csv_data['ship_name']; 
			$csv_data['ship-name'] = $csv_data['ship_name']; 
			$csv_data['ship-firstname'] = $csv_data['ship_firstname'];
			$csv_data['ship-lastname'] = $csv_data['ship_lastname'];
			$csv_data['ship-company'] = $csv_data['ship_company'];
			$csv_data['ship-companyname'] = $csv_data['ship_company'];
			$csv_data['ship_companyname'] = $csv_data['ship_company'];
			$csv_data['ship-streets'] = $csv_data['ship_streets'];
			$csv_data['ship-street1'] = $csv_data['ship_street1'];
			$csv_data['ship-street2'] = $csv_data['ship_street2'];
			$csv_data['ship-city'] = $csv_data['ship_city'];
			$csv_data['ship-region'] = $csv_data['ship_region'];
			$csv_data['ship-region-code'] = $csv_data['ship_region_code'];
			$csv_data['ship-postcode'] = $csv_data['ship_postcode'];
			$csv_data['ship-country'] = $csv_data['ship_country'];
			$csv_data['ship-country-code'] = $csv_data['ship_country_code'];
			$csv_data['ship-country-code-2char'] = $csv_data['ship_country_code_2char'];
			$csv_data['ship-region-filtered'] = $csv_data['ship_region_filtered'];
			$csv_data['ship-telephone'] = $csv_data['ship_telephone'];
			$csv_data['ship-email'] = $csv_data['ship_email'];
			
			$csv_data['bill-fullname'] = $csv_data['bill_name']; 
			$csv_data['bill-name'] = $csv_data['bill_name'];
			$csv_data['bill-lastname'] = $csv_data['bill_lastname'];
			$csv_data['bill-company'] = $csv_data['bill_company'];
			$csv_data['bill-companyname'] = $csv_data['bill_company'];
			$csv_data['bill_companyname'] = $csv_data['bill_company'];
			$csv_data['bill-streets'] = $csv_data['bill_streets'];
			$csv_data['bill-street1'] = $csv_data['bill_street1'];
			$csv_data['bill-street2'] = $csv_data['bill_street2'];
			$csv_data['bill-city'] = $csv_data['bill_city'];
			$csv_data['bill-region'] = $csv_data['bill_region'];
			$csv_data['bill-region-code'] = $csv_data['bill_region_code'];
			$csv_data['bill-postcode'] = $csv_data['bill_postcode'];
			$csv_data['bill-country'] = $csv_data['bill_country'];
			$csv_data['bill-country-code'] = $csv_data['bill_country_code'];
			$csv_data['bill-country-code-2char'] = $csv_data['bill_country_code_2char'];
			$csv_data['bill-region-filtered'] = $csv_data['bill_region_filtered'];
			$csv_data['bill-telephone'] = $csv_data['bill_telephone'];
			$csv_data['bill-email'] = $csv_data['bill_email'];
			
			$csv_data['gift-messages'] = $csv_data['gift_messages'];
			$csv_data['order-currency'] = $csv_data['order_currency'];
			$csv_data['base-currency'] = $csv_data['base_currency'];
			// END backwards compatible
			
            $csv_data['comments'] = (isset($comment_data[$order_id]['comments']) ? $comment_data[$order_id]['comments'] : '');
            $csv_data['gift_messages'] = (isset($message_data[$order_id]['gift_messages']) ? $message_data[$order_id]['gift_messages'] : '');
            $csv_data['tracking'] = (isset($tracking[$order_id]['tracking']) ? $tracking[$order_id]['tracking'] : '');
            $csv_data['order_tax_amount'] = isset($address_data[$order_id]['order_tax_amount']) ? $address_data[$order_id]['order_tax_amount'] : '';
            $csv_data['base_total_purchased'] = (isset($base_total_purchased[$order_id]['base_total_purchased']) ? $base_total_purchased[$order_id]['base_total_purchased'] : '');
            $csv_data['total_purchased'] = (isset($total_purchased[$order_id]['total_purchased']) ? $total_purchased[$order_id]['total_purchased'] : '');
            $csv_data['base_total_paid'] = (isset($base_total_paid[$order_id]['base_total_paid']) ? $base_total_paid[$order_id]['base_total_paid'] : '');
            $csv_data['total_paid'] = (isset($total_paid[$order_id]['total_paid']) ? $total_paid[$order_id]['total_paid'] : '');
            $csv_data['shipping_price'] = (isset($shipping_price[$order_id]['shipping_price']) ? $shipping_price[$order_id]['shipping_price'] : '');
            $csv_data['base_shipping_price'] = (isset($base_shipping_price[$order_id]['base_shipping_price']) ? $base_shipping_price[$order_id]['base_shipping_price'] : '');
            $csv_data['total_invoiced'] = (isset($total_invoiced[$order_id]['total_invoiced']) ? $total_invoiced[$order_id]['total_invoiced'] : '');
            $csv_data['base_total_invoiced'] = (isset($base_total_invoiced[$order_id]['base_total_invoiced']) ? $base_total_invoiced[$order_id]['base_total_invoiced'] : '');
            $csv_data['order_subtotal'] = (isset($order_subtotal[$order_id]['order_subtotal']) ? $order_subtotal[$order_id]['order_subtotal'] : '');
            $csv_data['base_order_subtotal'] = (isset($base_order_subtotal[$order_id]['base_order_subtotal']) ? $base_order_subtotal[$order_id]['base_order_subtotal'] : '');
            $csv_data['weight'] = (isset($row_weight[$order_id]['weight']) ? $row_weight[$order_id]['weight'] : '');
            $csv_data['custom_weightbased_text'] = (isset($row_weight[$order_id]['custom_weightbased_text']) ? $row_weight[$order_id]['custom_weightbased_text'] : '');
            $csv_data['custom_weightbased_count'] = (isset($row_weight[$order_id]['custom_weightbased_count']) ? $row_weight[$order_id]['custom_weightbased_count'] : '');
            $csv_data['ship-method'] = (isset($ship_method[$order_id]['ship-method']) ? $ship_method[$order_id]['ship-method'] : '');
            $csv_data['comments_frontend_only'] = (isset($comment_frontend_data[$order_id]['comments_frontend_only']) ? $comment_frontend_data[$order_id]['comments_frontend_only'] : '');
            $csv_data['comments_unfiltered'] = (isset($comments_unfiltered[$order_id]['comments_unfiltered']) ? $comments_unfiltered[$order_id]['comments_unfiltered'] : '');
            $csv_data['marketplace_id'] = (isset($marketplace[$order_id]['marketplace_id']) ? $marketplace[$order_id]['marketplace_id'] : '');
            $csv_data['ebay_order_id'] = (isset($marketplace[$order_id]['ebay_order_id']) ? $marketplace[$order_id]['ebay_order_id'] : '');
            $csv_data['amazon_order_id'] = (isset($marketplace[$order_id]['amazon_order_id']) ? $marketplace[$order_id]['amazon_order_id'] : '');
            $csv_data['ebay_final_fee'] = (isset($marketplace[$order_id]['ebay_final_fee']) ? $marketplace[$order_id]['ebay_final_fee'] : '');
            $csv_data['paypal_transaction_id'] = (isset($marketplace[$order_id]['paypal_transaction_id']) ? $marketplace[$order_id]['paypal_transaction_id'] : '');
            $csv_data['paypal_fee'] = (isset($marketplace[$order_id]['paypal_fee']) ? $marketplace[$order_id]['paypal_fee'] : '');
           
		    /* courierrules */
            if( isset($address_data[$order_id]['courierrules_shipping_method']) && $address_data[$order_id]['courierrules_shipping_method'] != '')
                  $csv_data['courierrules_shipping_method'] = $address_data[$order_id]['courierrules_shipping_method'];
            
            $csv_data['today_date'] = $address_data[$order_id]['today_date'];            
           
		    /* coupon code and label */
            $csv_data['coupon_code'] = (isset($address_data[$order_id]['coupon_code']) ? $address_data[$order_id]['coupon_code'] : '');
            $csv_data['coupon_label'] = (isset($address_data[$order_id]['coupon_label']) ? $address_data[$order_id]['coupon_label'] : '');
           
		    /*Moogento RetailExpress Fields*/            
            $csv_data['retail_express_id'] = (isset($address_data[$order_id]['retail_express_id']) ? $address_data[$order_id]['retail_express_id'] : '');
            $csv_data['retail_express_status'] = (isset($address_data[$order_id]['retail_express_status']) ? $address_data[$order_id]['retail_express_status'] : '');
            $csv_data['retail_express_message'] = (isset($address_data[$order_id]['retail_express_message']) ? $address_data[$order_id]['retail_express_message'] : '');
            
            foreach ($sku_order_id_qty[$order_id] as $sku => $qty) {
            	if(isset($skuoptions[$order_id][$sku]['options']))
                  $csv_data['options'] = $skuoptions[$order_id][$sku]['options'];
            	
                $display_sku = '';
                $display_sku = htmlspecialchars_decode($sku_order_id_sku[$order_id][$sku]);
                if(strpos($display_sku,$order_id.'_') !== FALSE)
                $display_sku = str_replace($order_id.'_','',$display_sku);
                $csv_data['qty'] = $qty;
                if(isset($address_data[$sku]['url_key']))
                    $csv_data['product_key'] = $address_data[$sku]['url_key'];
                if(isset($address_data[$sku]['product_url']))
                    $csv_data['product_url'] = $address_data[$sku]['product_url'];
                $address_data[$sku]['url_key'] = strip_tags($temp_product->getUrlPath());
                $csv_data['sku'] = $display_sku;
                $csv_data['product_id'] = isset($address_data[$sku]['product_id']) ? $address_data[$sku]['product_id'] : '';
                $csv_data['product_id_simple'] = isset($address_data[$sku]['product_id_simple']) ? $address_data[$sku]['product_id_simple'] : '';
                $csv_data['product_id_configurable'] = isset($address_data[$sku]['product_id_configurable']) ? $address_data[$sku]['product_id_configurable'] : '';
                if (isset($sku_order_id_sku_simple[$order_id][$sku]))
                    $csv_data['simple_sku'] = $sku_order_id_sku_simple[$order_id][$sku];
                else
                    $csv_data['simple_sku'] = '';

                $linker = '';
                if (isset($plus_options[$order_id][$sku]) != '') $linker = '_';
                $csv_data['order_sku_count'] = $order_id . '_' . $csv_data['sku'];
                $csv_data['sku_plus_options'] = $display_sku . $linker . (isset($plus_options[$order_id][$sku]) ? $plus_options[$order_id][$sku] : '');
                $csv_data['sku_plus_options_nolabel'] = $display_sku . $linker . (isset($plus_options_nolabel[$order_id][$sku]) ? $plus_options_nolabel[$order_id][$sku] : '');
                $csv_data['product_name'] = $address_data[$sku]['sku_name'];
                $csv_data['sku_price'] = round($address_data[$sku]['sku_price'], 2);

                $csv_data['description'] = (isset($address_data[$sku]['description']) ? $address_data[$sku]['description'] : '');
                $csv_data['product_description'] = (isset($address_data[$sku]['description']) ? $address_data[$sku]['description'] : '');
                $csv_data['short_description'] = (isset($address_data[$sku]['short_description']) ? $address_data[$sku]['short_description'] : '');
                $csv_data['weight'] = $address_data[$sku]['weight'];
                /* include product options */
                if (count($custom_option_attributes) > 0) :
                    foreach ($custom_option_attributes as $key => $value) {
                        if (isset($address_data[$sku][$value]) && $address_data[$sku][$value] != '')
                            $csv_data[$value] = $address_data[$sku][$value];
                    }
                endif;				
                if (!isset($csv_data['order_sku_count_pre'][$order_id][$sku]))
					$csv_data['order_sku_count_pre'][$order_id][$sku] = 1;
                else
					$csv_data['order_sku_count_pre'][$order_id][$sku]++;

                if ($csv_data['order_sku_count_pre'][$order_id][$sku] > 1)
					$csv_data['order_sku_count'] .= '_' . $csv_data['order_sku_count_pre'][$order_id][$sku];

				$field_quotes_escaped_container = '';
				$field_quotes_escaped = '~~';
				if (($this->_general['csv_quote_values_yn'] != 'double') && ($this->_general['csv_quote_values_yn'] != 'strip_all'))
					$field_quotes_escaped_container = '~~';
				
                foreach ($csv_data as $key => $value){
                    if(is_string($csv_data[$key])) {
                       if ($this->_general['csv_strip_linebreaks_yn'] && (strpos($key, 'ship-') === false) && (strpos($key, 'bill-') === false))
						   $csv_data[$key] = trim($value);
					   else
						   $csv_data[$key] = $value; 
						
						if (strpos($value, $field_quotes) !== false)
							$csv_data[$key] = $field_quotes_escaped_container.str_replace($field_quotes, $field_quotes_escaped.$field_quotes_escaped.$field_quotes_escaped.$field_quotes.$field_quotes_escaped.$field_quotes_escaped.$field_quotes_escaped, $value).$field_quotes_escaped_container;
						elseif (strpos($value, $column_separator) !== false)
							$csv_data[$key] = $field_quotes_escaped_container.$value.$field_quotes_escaped_container;
						else $csv_data[$key] = str_replace($column_separator, " ", $value);
                    }
                }
                	
                for ($i = 0; $i < count($column_map); $i++) {
                    $column_attribute = $column_map[$i];
                    $column_attribute . '<br />';
                    // if value needs filling
                    $attribute_base = strtolower(trim(str_replace('%', '', $column_attribute)));
                    if (preg_match('~%~', $column_attribute)) {
                        if (!isset($csv_data[$attribute_base])){ 
                            $csv_data[$attribute_base] = '';
                            if(isset($csv_data[str_replace('_', '-', $attribute_base)]) && ($csv_data[str_replace('_', '-', $attribute_base)])){
                                $csv_data[$attribute_base] = $csv_data[str_replace('_', '-', $attribute_base)];
                                $attribute_base = str_replace('_', '-', $attribute_base);
                            }elseif(isset($csv_data[str_replace('-', '_', $attribute_base)]) && ($csv_data[str_replace('-', '_', $attribute_base)])){
                                $csv_data[$attribute_base] = $csv_data[str_replace('-', '_', $attribute_base)];
                                $attribute_base = str_replace('-', '_', $attribute_base);
                            }
                        }
                        if( $strip_line_comma == 1 )
                            $csv_data[$attribute_base] = split_line_comma ($csv_data[$attribute_base]);
                        $output_value = trim($csv_data[$attribute_base]);
                        if ($this->_general['csv_quote_values_yn'] == 'double')
                            $output_value = str_replace('"', '""', $output_value);
                        elseif ($this->_general['csv_quote_values_yn'] == 'strip_all')
                            $output_value = str_replace('"', '', $output_value);
                        
                        $csv_output .= $field_quotes . $output_value . $field_quotes . $column_separator;
                    } elseif (preg_match('~\[~', $column_attribute)) {
                        if( $strip_line_comma == 1 )
                             $column_attribute = split_line_comma ($column_attribute);
                        $output_value = trim(str_replace(array('[', ']'), '', $column_attribute));
                        if ($this->_general['csv_quote_values_yn'] == 'double')
                            $output_value = str_replace('"', '""', $output_value);
						elseif ($this->_general['csv_quote_values_yn'] == 'strip_all')
                            $output_value = str_replace('"', '', $output_value);
                        
                        $csv_output .= $field_quotes . $output_value . $field_quotes . $column_separator;
                    } elseif ($attribute_base != '') {
                        if (isset($sku_color[$order_id][$sku][$column_attribute])) {
                            if( $strip_line_comma == 1 )
                                $sku_color[$order_id][$sku][$column_attribute] = split_line_comma ($sku_color[$order_id][$sku][$column_attribute]);
                            $output_value = trim($sku_color[$order_id][$sku][$column_attribute]);
                            if ($this->_general['csv_quote_values_yn'] == 'double')
                                $output_value = str_replace('"', '""', $output_value);
							elseif ($this->_general['csv_quote_values_yn'] == 'strip_all')
                                $output_value = str_replace('"', '', $output_value);
                            
                            $csv_output .= $field_quotes . $output_value . $field_quotes . $column_separator;
                        } else 
							$csv_output .= $field_quotes . $field_quotes . $column_separator;
                    } else 
						$csv_output .= $field_quotes . $field_quotes . $column_separator;
                }
                $csv_output .= "\n";
            }
        }
		$csv_output = str_replace(array($field_quotes_escaped,$field_quotes_escaped_container),$field_quotes,$csv_output);
        return $csv_output;
    }
}