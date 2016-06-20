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
 * File        Csvorders.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Csvorders extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    private function erase_val(&$myarr) {
        /*
            Purpose:
                To clear values of an array (but not keys)
            Usage:
                Called by reference so you don't need to assign array to a variable.
                 Just call the function upon it
            Syntax:
                erase_val($array);
        */
        $myarr = array_map(create_function('$n', 'return null;'), $myarr);
    }

    public function getCsvOrders($orders = array(), $from_shipment = 'order') {
        $this->setGeneralCsvConfig();

        $debug = 0;
        $column_headers = '';
        $csv_output = '';
        $field_quotes = '"';
        $column_map = array();
        $row = array();

        $address_format_default = '{if ship_company}{ship_company},{/if ship_company}
{if ship_name}{ship_name},{/if ship_name}
{if ship_streets}{ship_streets},{/if ship_streets}
{if ship_city}{ship_city},{/if ship_city}
{if ship_postcode}{ship_postcode} {/if ship_postcode}{if ship_region}{ship_region},{/if ship_region}
{ship_country}';
        $address_format = $this->_getConfig('address_format', $address_format_default, false, 'csvorders');

        $date_format = Mage::getStoreConfig('pickpack_options/csvorders/date_format');
        $strip_line_comma = Mage::getStoreConfig('pickpack_options/csvorders/strip_line_commas');
        $address_countryskip = $this->_getConfig('address_countryskip', 0, false, 'general');

        $column_separator 	= $this->_general['csv_field_separator'];
        $column_mapping_pre = trim($this->_getConfig('column_mapping_csvorders', '', false, 'csvorders'));
        $column_mapping_pre = trim(str_replace(array("\n", "\r", "\t", ';;;', ';;'), ';', $column_mapping_pre));
        $column_mapping_pre = str_ireplace(array(':;'), ':[ ];', $column_mapping_pre);
        $column_mapping_pre = preg_replace('~[;]$~', '', $column_mapping_pre);

        $column_mapping_array = explode(';', $column_mapping_pre);
        foreach ($column_mapping_array as $key => $value) {
            $key = trim($key);
            $column_mapping_sub_array = explode(':', $value);

            if ($key != '') {
                $key = trim($column_mapping_sub_array[0]);
                if (!isset($column_map[$key]))
                    $column_headers .= $field_quotes . $key . $field_quotes . $column_separator;
                if (isset($column_mapping_sub_array[1])) $value = trim($column_mapping_sub_array[1]);

                $column_map[$key] = $value;
            }
            $key = '';
            $value = '';
        }

        // get rid of trailing comma
        $column_headers = preg_replace('~[;,' . $column_separator . ']$~', "\n", $column_headers);
        $csv_output = $column_headers;

        // amasty custom attributes
        $active_amasty_order_flags = false;
        $amastyFlagsArr = Array();
        if (Mage::helper('pickpack')->isInstalled('Amasty_Flags') && Mage::helper('pickpack')->isInstalled('Amasty_Ogrid')) {
            $active_amasty_order_flags = true;
            $amastyFlags = Mage::getModel('amflags/flag')->getCollection();
            if ($amastyFlags->getSize() > 0) {
                foreach ($amastyFlags as $amastyFlag) {
                    $amastyFlagsArr[] = $amastyFlag->getData();
                }
            }
        }

        $order_collection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' => ($orders)))->setPageSize(count($orders))
            ->setCurPage(1);

        foreach ($order_collection as $order) {
            $order_id = $order->getRealOrderId();
            $row[$order_id] = array();
            $row[$order_id]['order_id'] = $order_id;
            $row[$order_id]['order_date'] = '';
            $ebay_order_id = '';
            $amazon_order_id = '';
            $warehouse_title = '';

            if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {

                $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
                $collection->addFieldToFilter('magento_order_id', $order->getData('entity_id'));
                $collection->setCurPage(1)->setPageSize(1);
                $collection_data = $collection->getData();
                if (is_array($collection_data) && isset($collection_data[0]['selling_manager_id']))
                    $ebay_order_id = $collection_data[0]['selling_manager_id'];
                else
                    $ebay_order_id = '';

                $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');
                $collection->addFieldToFilter('magento_order_id', $order->getData('entity_id'));
                $collection->setCurPage(1)->setPageSize(1);
                if (($collection->getData('amazon_order_id'))) {
                    $collection_data = $collection->getData();
                    if (is_array($collection_data))
                        $amazon_order_id = $collection_data[0]['amazon_order_id'];
                    else
                        $amazon_order_id = '';

                    if ($ebay_order_id != '')
                        $row[$order_id]['marketplace_order_id'] = $ebay_order_id;
                    else
                        $row[$order_id]['marketplace_order_id'] = $amazon_order_id;
                }

            }

            /***** Get Innoexts Warehouse information ****/
            if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')) {
                $warehouse_helper = Mage::helper('warehouse');
                $warehouse_collection = Mage::getSingleton('warehouse/warehouse')->getCollection();
                $resource = Mage::getSingleton('core/resource');
                // Get the read connection
                $readConnection = $resource->getConnection('core_read');
                $query = 'SELECT `stock_id` FROM `' . $resource->getTableName("warehouse/order_grid_warehouse") . '` WHERE `entity_id`=' . $order->getData('entity_id');
                $warehouse_stock_id = $readConnection->fetchOne($query);
                if ($warehouse_stock_id) {
                    $warehouse = $warehouse_helper->getWarehouseByStockId($warehouse_stock_id);
                    $warehouse_title = ($warehouse->getData('title'));
                }
            }

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
                $row[$order_id]['chanel_unity_origin'] = implode(', ', $sources);
            }

            /***** Get Amasty Flag / Custom attributes ****/
            if ($active_amasty_order_flags == true) {
                $orderFlags = Mage::getModel('amflags/order_flag')->getCollection();
                $orderFlags->getSelect()->where('order_id = ?', $order->getId());
                if ($orderFlags->getSize() > 0) {
                    foreach ($orderFlags as $orderFlag) {
                        $flag_id = $orderFlag->getData('flag_id');
                        $column_id = $orderFlag->getData('column_id');
                        $column_name = 'Amasty_column_#' . $column_id;
                        $flag_name = 'Amasty_flag_#' . $flag_id;

                        foreach ($amastyColumnsArr as $order_column) {
                            if ($order_column['entity_id'] == $column_id) {
                                $column_name = $order_column['alias'];
                            }
                        }

                        foreach ($amastyFlagsArr as $order_flag) {
                            if ($order_flag['entity_id'] == $flag_id) {
                                $flag_name = $order_flag['alias'];
                            }
                        }
                        $this->orderModelArray[$order_id][$column_name] = $flag_name;
                        $row[$order_id][$column_name] = $flag_name;
                        $this->orderModelArray[$order_id][strtolower($column_name)] = $flag_name;
                        $row[$order_id][strtolower($column_name)] = $flag_name;
                    }
                }
                unset($column_name);
                unset($column_id);
                unset($flag_name);
                unset($flag_id);
            }

            $row[$order_id]['warehouse'] = $warehouse_title;
            $row[$order_id]['base_total_purchased'] = $order->getBaseGrandTotal();
            $row[$order_id]['total_purchased'] = $order->getGrandTotal();

            $row[$order_id]['base_total_paid'] = $order->getBaseTotalPaid();
            $row[$order_id]['total_paid'] = $order->getTotalPaid();

            if ($order->getCreatedAtStoreDate())
				$row[$order_id]['order_date'] = Mage::getSingleton('core/date')->date($date_format,$order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
			
            $store_id = $order->getStore()->getId();
            $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($store_id, $date_format);
            $order_date = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $date_format);

            $order_currency_code = $order->getOrderCurrencyCode();
            $store_currency_code = $order->getStore()->getCurrentCurrencyCode();
            $row[$order_id]['order-currency'] = $order_currency_code;
            $row[$order_id]['base-currency'] = $store_currency_code;
            $row[$order_id]['shipping_paid_plus_tax'] = number_format($order->getShippingInclTax(), 2); // number_format as well as round to show eg 2.00
            $row[$order_id]['shipping_paid'] = number_format($order->getShippingAmount(), 2);

            $shipping_method = clean_method($order->getShippingDescription(), 'shipping');
            if (trim($shipping_method) != '')
                $row[$order_id]['ship_method'] = $shipping_method;//clean_method($order->getShippingDescription(), 'shipping');
            else
                $row[$order_id]['ship_method'] = 'not set';

            $row[$order_id]['payment_method'] = clean_method($order->getPayment()->getMethodInstance()->getTitle(), 'payment');
            $row[$order_id]['order_status'] = $order->getStatus();
            $row[$order_id]['order_status_label'] = $order->getStatusLabel();
            $row[$order_id]['store_id'] = $order->getStore()->getId();
            $row[$order_id]['store'] = $order->getStore()->getGroup()->getName();
            $row[$order_id]['website'] = $order->getStore()->getWebsite()->getName();

            if (trim($order->getCustomerId()) != '')
                $row[$order_id]['customer_id'] = trim($order->getCustomerId());
            else
                $row[$order_id]['customer_id'] = 'not registered';


            $row[$order_id]['bill_prefix'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getPrefix(), 'pdf');
            $row[$order_id]['bill_suffix'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getSuffix(), 'pdf');
            $row[$order_id]['bill_name'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getName(), 'pdf');
            $row[$order_id]['bill_firstname'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getFirstname(), 'pdf');
            $row[$order_id]['bill_lastname'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getLastname(), 'pdf');
            $row[$order_id]['bill_fullname'] = trim(Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getPrefix() . ' ' . $order->getBillingAddress()->getFirstname() . ' ' . $order->getBillingAddress()->getLastname() . ' ' . $order->getBillingAddress()->getSuffix(), 'pdf'));

            $row[$order_id]['bill_company'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getCompany(), 'pdf');
            $row[$order_id]['bill_address'] = Mage::helper('pickpack/functions')->clean_method(trim(str_replace(array('|', $address_countryskip, ',,'), ',', implode(',', $this->_formatAddress($order->getBillingAddress()->format('pdf'))))), 'pdf');
            $row[$order_id]['bill_address'] = Mage::helper('pickpack/functions')->clean_method(preg_replace('~,$~', '', $row[$order_id]['bill_address']), 'pdf');
            $row[$order_id]['bill_streets'] = '';

            if ($order->getBillingAddress()->getStreetFull())
                $row[$order_id]['bill_streets'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getStreetFull(), 'pdf');

            $row[$order_id]['bill_street1'] = '';
            if ($order->getBillingAddress()->getStreet(1))
                $row[$order_id]['bill_street1'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getStreet(1), 'pdf');

            $row[$order_id]['bill_street2'] = '';
            if ($order->getBillingAddress()->getStreet(2))
                $row[$order_id]['bill_street2'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getStreet(2), 'pdf');

            $row[$order_id]['bill_street3'] = '';
            if ($order->getBillingAddress()->getStreet(2))
                $row[$order_id]['bill_street3'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getStreet(3), 'pdf');

            $row[$order_id]['bill_street4'] = '';
            if ($order->getBillingAddress()->getStreet(4))
                $row[$order_id]['bill_street4'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getStreet(4), 'pdf');

            $row[$order_id]['bill_city'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getCity(), 'pdf');
            $row[$order_id]['bill_region'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getRegion(), 'pdf');
            $row[$order_id]['bill_region_code'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getRegionCode(), 'pdf');
            $row[$order_id]['bill_postcode'] = Mage::helper('pickpack/functions')->clean_method(strtoupper($order->getBillingAddress()->getPostcode()), 'pdf');
            $row[$order_id]['bill_country_code'] = Mage::helper('pickpack/functions')->clean_method($order->getBillingAddress()->getCountry(), 'pdf');
            $row[$order_id]['bill_country'] = Mage::helper('pickpack/functions')->clean_method(Mage::app()->getLocale()->getCountryTranslation($order->getBillingAddress()->getCountryId()), 'pdf');
            $row[$order_id]['bill_region_filtered'] = '';
            if (trim(strtolower($row[$order_id]['bill_country'])) != 'us') {
                $row[$order_id]['bill_region_filtered'] = Mage::helper('pickpack/functions')->clean_method($row[$order_id]['bill_region_code'], 'pdf');
            }
            $row[$order_id]['bill_telephone'] = $order->getBillingAddress()->getTelephone();
            $row[$order_id]['bill_email'] = trim($order->getCustomerEmail());

            $row[$order_id]['newsletter_subscription_status'] = '0';
            if (isset($row[$order_id]['bill_email'])) {
                $email = $row[$order_id]['bill_email'];
                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                if (($subscriber->getData('subscriber_status') == null))
                    $row[$order_id]['newsletter_subscription_status'] = 0;
                else
                    $row[$order_id]['newsletter_subscription_status'] = $subscriber->getData('subscriber_status');

                $row[$order_id]['newsletter_subscription_status'] = 0;
            }

            // process shipping address
            $address_format_this_order = $address_format;
            $address_format_pieces_pre = array();
            $address_pieces = array();
            $address_piece = '';
            preg_match_all('~\{(.+)\}~U', $address_format, $address_pieces_pre);
            $address_pieces = array_unique($address_pieces_pre[1]);
            foreach ($address_pieces as $address_piece) {
                $address_value = '';
                if (isset($row[$order_id][$address_piece]) && ($row[$order_id][$address_piece] != '')) {
                    $address_value = str_replace("\n", ', ', $row[$order_id][$address_piece]);
                }
                $address_format_this_order = str_ireplace('{' . $address_piece . '}', $address_value, $address_format_this_order);
            }
            $address_format_this_order = preg_replace('~\{(.*)}~U', '', $address_format_this_order);
            $address_format_this_order = str_ireplace(array("\n", ',,'), ',', $address_format_this_order);
            $address_format_this_order = preg_replace('~,$~', '', $address_format_this_order);
            $address_format_this_order = preg_replace('~^,~', '', $address_format_this_order);
            $row[$order_id]['ship_address'] = $address_format_this_order;
            $order_qty = $order->getData('total_qty_ordered');
            $order_weight = $order->getData('weight');
            $row[$order_id]['qty'] = $order_qty;
            $row[$order_id]['qty_items'] = $order_qty; // maybe qty is more obvious tag...

            $shipping_weight = $order_weight;
            $row[$order_id]['weight'] = $shipping_weight;
            $row[$order_id]['weight_kg_suffix'] = $shipping_weight . ' kg';
            $row[$order_id]['weight_lb_suffix'] = $shipping_weight . ' lb';
            $row[$order_id]['weight_kg_to_lb'] = round(($shipping_weight * '2.2046226'), 2);
            $row[$order_id]['weight_lb_to_kg'] = round(($shipping_weight * '0.45359237'), 2);
            $row[$order_id]['weight_kg_to_lb_suffix'] = round(($shipping_weight * '2.2046226'), 2) . ' lb';
            $row[$order_id]['weight_lb_to_kg_suffix'] = round(($shipping_weight * '0.45359237'), 2) . ' kg';

            $row[$order_id]['order_status'] = $order->getData('status');
            $row[$order_id]['order_subtotal'] = $order->getData('subtotal');
            $row[$order_id]['order_tax_amount'] = $order->getData('tax_amount');
            //TODO tracking number

            $row[$order_id]['description'] = $this->getOrderDescription($order, 'description');
            $row[$order_id]['description_qty'] = $this->getOrderDescription($order, 'description_qty');
            $row[$order_id]['order_tax_amount'] = $this->getOrderDescription($order, 'description_categories');

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
            $row[$order_id]['tracking'] = trim($tracking_number);

            //to do comment
            $orderComments = $order->getAllStatusHistory();
            foreach ($orderComments as $comment) {
                $body = $comment->getData('comment');
                if ($body !== NULL) {
                    if (isset($row[$order_id]['comment']))
                        $row[$order_id]['comments_unfiltered'] = $row[$order_id]['comments_unfiltered'] . "\n" . trim($body);
                    else
                        $row[$order_id]['comments_unfiltered'] = trim($body);

                    $body = trim(Mage::helper('pickpack/functions')->clean_method($body, 'pdf_more'));

                    if (isset($row[$order_id]['comments']))
                        $row[$order_id]['comments'] = $row[$order_id]['comments'] . "\n" . trim($body);
                    else
                        $row[$order_id]['comments'] = trim($body);

                    if ($comment->getData("is_visible_on_front") == 1) {
                        if (isset($row[$order_id]['comments_frontend_only']))
                            $row[$order_id]['comments_frontend_only'] = $row[$order_id]['comments_frontend_only'] . "\n" . trim($body);
                        else
                            $row[$order_id]['comments_frontend_only'] = trim($body);
                    }
                }
            }
            // to do gift message
            $message = Mage::getModel('giftmessage/message');
            $gift_message_id = $order->getGiftMessageId();
            if (!is_null($gift_message_id)) {
                $message->load((int)$gift_message_id);
                $gift_sender = $message->getData('sender');
                $row[$order_id]['gift_messages'] = 'From: ' . $gift_sender;
                $gift_recipient = $message->getData('recipient');
                $row[$order_id]['gift_messages'] = $row[$order_id]['gift_messages'] . "\n" . 'To: ' . $gift_recipient;
                $gift_message = $message->getData('message');
                $row[$order_id]['gift_messages'] = $row[$order_id]['gift_messages'] . "\n" . 'Message: ' . $gift_message;
            }
            //TODO total price
            $row[$order_id]['total_price'] = $order->getSubtotal();

            /* Grand Total */
            if ( $order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' || (isset($row[$order_id]['payment_method']) && ($row[$order_id]['payment_method'] == 'COD' || $row[$order_id]['payment_method'] == 'cod')) )
                $row[$order_id]['cod_owed'] = round($order->getGrandTotal(), 2);
            else{
                $row[$order_id]['cod_owed'] = '';
            }

            /* courierrules_shipping_method */
            $courierrules_shipping_method = $order->getData('courierrules_shipping_method');
            if (isset($courierrules_shipping_method) && $courierrules_shipping_method != '')
                $row[$order_id]['courierrules_shipping_method'] = $order->getData('courierrules_shipping_method');

            /* today date */
            $row[$order_id]['today_date'] = Mage::getSingleton('core/date')->date($date_format);

            /* coupon code */
            $row[$order_id]['coupon_code'] = $order->getCouponCode();

            if (isset($row[$order_id]['coupon_code']) && $row[$order_id]['coupon_code'] != '') {
                $coupon_rule = Mage::getModel('salesrule/coupon')->load($row[$order_id]['coupon_code'], 'code');
                if (is_object($coupon_rule)){
                    $rule_id = $coupon_rule->getData('rule_id');
                    if (!is_null($rule_id)){
                        $store_id = $order->getStore()->getId();
                        $label = get_coupon_label($rule_id, $store_id);
                        $row[$order_id]['coupon_label'] = $label;
                    }else $row[$order_id]['coupon_label'] = '';
                }
            }

            /* Moogento Retail Express Attributes */
            if (getMooRetailExpressAttribute()) {
                $row[$order_id]['retail_express_id '] = $order->getData('retail_express_id');
                $row[$order_id]['retail_express_status'] = $order->getData('retail_express_status ');
                $row[$order_id]['retail_express_message'] = $order->getData('retail_express_message ');
            }
            if (is_object($order->getShippingAddress())) {
                $row[$order_id]['ship_prefix'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getPrefix(), 'pdf');
                $row[$order_id]['ship_suffix'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getSuffix(), 'pdf');
                $row[$order_id]['ship_name'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getName(), 'pdf');
                $row[$order_id]['ship_firstname'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getFirstname(), 'pdf');
                $row[$order_id]['ship_lastname'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getLastname(), 'pdf');
                //@TODO check clean_method trims and remove the trim from here
                $row[$order_id]['ship_fullname'] = trim(Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getPrefix() . ' ' . $order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname() . ' ' . $order->getShippingAddress()->getSuffix(), 'pdf'));
                $row[$order_id]['ship_company'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getCompany(), 'pdf');
                $row[$order_id]['ship_address'] = Mage::helper('pickpack/functions')->clean_method(preg_replace('~,$~', '', $row[$order_id]['ship_address']), 'pdf');

                $row[$order_id]['ship_streets'] = '';
                if ($order->getShippingAddress()->getStreetFull())
                    $row[$order_id]['ship_streets'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getStreetFull(), 'pdf');

                $row[$order_id]['ship_street1'] = '';
                if ($order->getShippingAddress()->getStreet(1))
                    $row[$order_id]['ship_street1'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getStreet(1), 'pdf');

                $row[$order_id]['ship_street2'] = '';
                if ($order->getShippingAddress()->getStreet(2))
                    $row[$order_id]['ship_street2'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getStreet(2), 'pdf');

                $row[$order_id]['ship_street3'] = '';
                if ($order->getShippingAddress()->getStreet(3))
                    $row[$order_id]['ship_street3'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getStreet(3), 'pdf');

                $row[$order_id]['ship_street4'] = '';
                if ($order->getShippingAddress()->getStreet(4))
                    $row[$order_id]['ship_street4'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getStreet(4), 'pdf');

                $row[$order_id]['ship_city'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getCity(), 'pdf');
                $row[$order_id]['ship_region'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getRegion(), 'pdf');
                $row[$order_id]['ship_region_code'] = Mage::helper('pickpack/functions')->clean_method($order->getShippingAddress()->getRegionCode(), 'pdf');
                $row[$order_id]['ship_postcode'] = Mage::helper('pickpack/functions')->clean_method(strtoupper($order->getShippingAddress()->getPostcode()), 'pdf');

                $ship_country_id = $order->getShippingAddress()->getCountry();
                $row[$order_id]['ship_country_code'] = Mage::helper('pickpack')->get3digitcountry($ship_country_id);
                $row[$order_id]['ship_country_code_2char'] = $ship_country_id;
                $row[$order_id]['ship_country'] = Mage::helper('pickpack/functions')->clean_method(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()), 'pdf');
                $row[$order_id]['ship_region_filtered'] = '';
                if (trim(strtolower($row[$order_id]['ship_country'])) != 'us') {
                    $row[$order_id]['ship_region_filtered'] = $row[$order_id]['ship_region_code']; //'Y';
                }
                $row[$order_id]['ship_telephone'] = $order->getShippingAddress()->getTelephone();
                $row[$order_id]['ship_email'] = $order->getShippingAddress()->getEmail();
            }
            /*
             // country-specific results like this :
             switch($order->getShippingAddress()->getCountry()){
             case 'DE':
             //2. Process-ID
             $row[] = '01';
             //3. Participation-ID
             $row[] = '10';
             // 4. Product-ID
             $row[] = '101';
             break;
             default:
             //Todo: Switch between worldwide and europe;
             // 2. Process-ID
             $row[] = '54';
             // 3. Participation-ID
             $row[] = '07';
             // 4. Product-ID
             $row[] = '5401';
             break;
             }
             */
        }

        /**
         * can sort $row here
         */

        if ($this->_general['csv_quote_values_yn'] == '0'){
            $field_quotes = '';
        }

        $print_value = 0;

        foreach ($row as $order_id => $field_value) {
            if ($this->_general['csv_strip_linebreaks_yn']) {
                foreach ($row[$order_id] as $key => $value) {
                    if (is_string($value)) {
                        $row[$order_id][$key] = trim($value);
                        if (strpos($key, 'ship_') === false && strpos($key, 'bill_') === false) {
                            $row[$order_id][$key] = str_replace($column_separator, " ", trim($value));
                        }
                    }
                }
            }
            foreach ($column_map as $column_header => $column_attribute) {
                // [column_header] Shipping First Name + Last name
                // [column_attribute] %ship_firstname% %ship_lastname%
                if (preg_match('~%~', $column_attribute))
                {
                    $attribute_arr = array();
                    $pattern = '%';
                    $start = 0;
                    while(($newLine = strpos($column_attribute, $pattern, $start)) !== false){
                        $start = $newLine + 1;
                        $attribute_arr[] = $newLine;
                    }

                    //Multiple attributes
                    if(count($attribute_arr) > 1)
                    {
                        $column_attribute2 = $column_attribute;
                        for($i=0;$i<count($attribute_arr); $i=$i+2)
                        {

                            $from_to = $attribute_arr[$i].' '.$attribute_arr[$i+1];
                            $temp_str = substr($column_attribute,$attribute_arr[$i],$attribute_arr[$i+1]);
                            if(substr($temp_str,-1)!='%'){
                                $temp_str = $temp_str.'%';
                            }


                            $attribute_name = strtolower(trim(str_replace('%', '', $temp_str)));

                            if (isset($row[$order_id][$attribute_name])){
                                $attribute_value = $row[$order_id][$attribute_name];
                            }else $attribute_value = '';
                            if( $strip_line_comma == 1 ){
                                $attribute_value = split_line_comma($attribute_value);
                            }
                            $column_attribute2 =	str_replace($temp_str,$attribute_value,$column_attribute2);
                        }

                        $output_value = trim($column_attribute2);
                        if ($this->_general['csv_quote_values_yn'] == 'double'){
                            $output_value = str_replace('"', '""', $output_value);
                        }elseif ($this->_general['csv_quote_values_yn'] == 'strip_all'){
                            $output_value = str_replace('"', '', $output_value);
                        }

                        $csv_output .= $field_quotes . $output_value . $field_quotes . $column_separator;
                    }
                    //Single attribute
                    else
                    {
                        $attribute_base = strtolower(trim(str_replace('%', '', $column_attribute)));
                        if (!isset($row[$order_id][$attribute_base])) {
                            $row[$order_id][$attribute_base] = '';
                        }

                        if( $strip_line_comma == 1 ){
                            $row[$order_id][$attribute_base] = split_line_comma($row[$order_id][$attribute_base]);
                        }

                        $output_value = trim($row[$order_id][$attribute_base]);
                        if ($this->_general['csv_quote_values_yn'] == 'double'){
                            $output_value = str_replace('"', '""', $output_value);
                        }elseif ($this->_general['csv_quote_values_yn'] == 'strip_all'){
                            $output_value = str_replace('"', '', $output_value);
                        }

                        $csv_output .= $field_quotes . $output_value . $field_quotes . $column_separator;
                    }
                }
                elseif (preg_match('~\[~', $column_attribute)){
                    if( $strip_line_comma == 1 ){
                        $column_attribute = split_line_comma($column_attribute);
                    }

                    $output_value = trim(str_replace(array('[', ']'), '', $column_attribute));
                    if ($this->_general['csv_quote_values_yn'] == 'double'){
                        $output_value = str_replace('"', '""', $output_value);
                    }elseif ($this->_general['csv_quote_values_yn'] == 'strip_all'){
                        $output_value = str_replace('"', '', $output_value);
                    }

                    $csv_output .= $field_quotes . $output_value . $field_quotes . $column_separator;
                }
                elseif ($column_attribute != ''){
                    if( $strip_line_comma == 1 ){
                        $column_attribute = split_line_comma($column_attribute);
                    }

                    $column_attribute = trim(str_replace(array('{', '}'), '', $column_attribute));

                    $output_value = $this->getOrderProductAttributeTextByCode($order, $column_attribute);

                    if ($this->_general['csv_quote_values_yn'] == 'double'){
                        $output_value = str_replace('"', '""', $output_value);
                    }
                    elseif ($this->_general['csv_quote_values_yn'] == 'strip_all'){
                        $output_value = str_replace('"', '', $output_value);
                    }

                    $csv_output .= $field_quotes . $output_value . $field_quotes . $column_separator;
                }
                else{
                    $csv_output .= $field_quotes . $field_quotes . $column_separator;
                }
            }
            $csv_output = preg_replace('~[' . $column_separator . ']$~', "\n", $csv_output);
        }
        return $csv_output;
    }
}