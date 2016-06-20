<?php
/**
 *
 * Date: 01.12.15
 * Time: 18:49
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Address extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public $subheader_start;
    public $shippingAddressArray;
    public $shippingAddressArrayBottom;
    public $billingAddressArray;
    public $fontSizeShippingAddress;
    public $caseRotate;
    public $addressFooterXY_xtra;
    public $font_size_shipaddress_xtra;
    public $flat_address_margin_rt_xtra;
    public $float_top_address_yn;
    public $address_top_y = 0;
    public $bottom_ispace = 0;
    public $customer_phone = '';
    public $filter_custom_attributes_array = array();
    public $max_chars;
    public $line_height;
    public $line_height_top;
    public $line_height_bottom;
    public $email_X;
    public $title_date_xpos;
    public $bottom_shipping_address_pos = array();
    public $line_count = 0;
    public $headerBarXY = array();
    public $minY = array();

    public $shipping_method = '';

    public $top_y_left_column;
    public $top_y_right_column;
    public $address_title_left_x;
    public $address_title_right_x;

    protected $bottomOrderIdY;
    protected $i_space;
    protected $string_2nd_shipping_address = "";
    protected $max_chars_shipping_bottom;
    protected $multiline_shipping_bottom;
    public $shipping_address_flat = '';
    protected $line_addon = 0;
    protected $orderdetailsX = 304;

    const BOTTOM_LABEL_IMAGE_DEFAULT_NUDGE_X = 0;
    const BOTTOM_LABEL_IMAGE_DEFAULT_NUDGE_Y = 60;
    const BOTTOM_LABEL_ORDERID_DEFAULT_NUDGE_X = 0;
    const BOTTOM_LABEL_ORDERID_DEFAULT_NUDGE_Y = -10;
    const BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X = -20;
    const BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y = 10;
    const BOTTOM_BARCODE_DEFAULT_NUDGE_X = 0;
    const BOTTOM_BARCODE_DEFAULT_NUDGE_Y = -23;

    public function __construct($arguments) {
        parent::__construct($arguments);
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $this->prepareOrderCustomAttributeFilter();

        $this->caseRotate = $this->_getConfig('case_rotate_address_label',0, false, $wonder, $storeId);
        $billing_address_with_gift_yn = $this->_getConfig('billing_address_with_gift_yn', 0, false, $wonder, $storeId);
        $billing_details_yn = $this->_getConfig('billing_details_yn', 0, false, $wonder, $storeId);
        if (($this->hasBillingAddress() === false) || ($billing_address_with_gift_yn == 1))
            $billing_details_yn = 0;

        $shipping_billing_title_position = $this->_getConfig('shipping_billing_title_position', 'above', false, $wonder, $storeId);
        $this->title_date_xpos = trim($this->_getConfig('title_date_xpos', 'auto', false, $wonder, $storeId));
        if (($shipping_billing_title_position == 'beside') && ($this->title_date_xpos < 100) && ($billing_details_yn == 1))
            $this->title_date_xpos = 350;

        if ($shipping_billing_title_position == 'beside' && $this->title_date_xpos != 'auto')
            $this->orderdetailsX = $this->title_date_xpos;

        $packingsheetConfig = $this->getPackingsheetConfig($wonder, $storeId);
        if ($packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup')
            $this->fontSizeShippingAddress = $packingsheetConfig['pickpack_shipfont'];
        else
            $this->fontSizeShippingAddress = $packingsheetConfig['pickpack_shipfont'];

        if($packingsheetConfig['pickpack_bottom_shipping_address_yn_xtra'] == 2){
            $this->addressFooterXY_xtra = explode(",", $this->_getConfig('pickpack_shipaddress_xtra_2',  $pageConfig['addressFooterXYDefault_xtra'], true, $wonder, $storeId));
            $this->font_size_shipaddress_xtra = $this->_getConfig('pickpack_shipfont_xtra_2', 8, false, $wonder, $storeId);
            $this->flat_address_margin_rt_xtra = $this->_getConfig('flat_address_margin_rt_xtra_2', 0, true, $wonder, $storeId);
        }
        else {
            $this->addressFooterXY_xtra = explode(",", $this->_getConfig('pickpack_shipaddress_xtra', $pageConfig['addressFooterXYDefault_xtra'], true, $wonder, $storeId));
            $this->font_size_shipaddress_xtra = $this->_getConfig('pickpack_shipfont_xtra', 8, false, $wonder, $storeId);
            $this->flat_address_margin_rt_xtra = $this->_getConfig('flat_address_margin_rt_xtra', 0, true, $wonder, $storeId);
        }

        $this->line_height_top = ($generalConfig['font_size_body'] + 2);
        $this->line_height_bottom = (1.05 * $this->fontSizeShippingAddress);
    }

    public function prepareOrderCustomAttributeFilter() {
        $order_custom_attribute_filter = $this->_getConfig('order_custom_attribute_filter', '', false, $this->getWonder(), $this->getStoreId());
        $filter_custom_attributes_array = explode("\n", $order_custom_attribute_filter);
        foreach ($filter_custom_attributes_array as $key => $value) {
            $this->filter_custom_attributes_array[$key] = trim($value);
        }
    }

    public function showAddressDetails() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $helper = Mage::helper('pickpack');
        $generalConfig = Mage::helper('pickpack/config')->getGeneralConfigArray($storeId);

        $date_format = $this->_getConfig('date_format', 'M. j, Y', false, 'general', $storeId);
        $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($storeId, $date_format);

        $shipping_details_yn = $this->_getConfig('shipping_details_yn', 1, false, $wonder, $storeId);
        $billing_details_yn = $this->_getConfig('billing_details_yn', 0, false, $wonder, $storeId);
        $address_pad = explode(",", $this->_getConfig('address_pad', '0,0,0', false, $wonder, $storeId));
		
		if(count($address_pad) < 3)
			$address_pad = explode(',', '0,0,0');
		
        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
        $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;

        $shipping_billing_title_position = $this->_getConfig('shipping_billing_title_position', 'above', false, $wonder, $storeId);
        $customer_phone_yn = $this->_getConfig('customer_phone_yn', 0, false, 'general', $storeId);
        $show_mageworx_multifees = $this->_getConfig('show_mageworx_multifees', 0, false, $wonder, $storeId);
        if(Mage::helper('pickpack')->isInstalled('Magalter_Customshipping'))
            $shipment_details_shipping_options_filter = explode(',',$this->_getConfig('shipment_details_shipping_options_filter','', false, $wonder, $storeId));

        $customer_custom_attribute_yn = $this->_getConfig('customer_custom_attribute_yn', 0, false, $wonder, $storeId);
        $customer_custom_attribute = '';
        if ($customer_custom_attribute_yn == 1)
            $customer_custom_attribute = $this->_getConfig('customer_custom_attribute', '', false, $wonder, $storeId);

        $customer_group_filter = $this->_getConfig('shipment_details_custgroup_filter', 0, false, $wonder, $storeId);
        $customer_group_filter = trim(strtolower($customer_group_filter));

        $show_aitoc_checkout_field_yn = $this->_getConfig('show_aitoc_checkout_field_yn', 0, false, $wonder, $storeId);
        $show_aitoc_checkout_field = $this->_getConfig('show_aitoc_checkout_field', '', false, $wonder, $storeId);

        $billing_details_position = $this->_getConfig('billing_details_position', 0, false, $wonder, $storeId);
        $billing_title = trim($this->_getConfig('billing_title', '', false, $wonder, $storeId));

        if ($shipping_details_yn == 0)
            $shipping_title = null;
        else
            $shipping_title = trim($this->_getConfig('shipping_title', '', false, $wonder, $storeId));

        if ($this->hasBillingAddress() === false)
            $billing_details_yn = 0;

        if ($billing_details_yn == 0) {
            $billing_details_position = 0;
            $billing_title = '';
        }

        if (($billing_details_yn == 1) && ($shipping_details_yn == 0))
            $billing_details_position = 1;
        if (($billing_details_yn == 0) && ($shipping_details_yn == 1))
            $billing_details_position = 0;

        $shipment_details_yn = $this->_getConfig('shipment_details_yn', 0, false, $wonder, $storeId);
        $shipment_details_nudge = explode(",", $this->_getConfig('shipment_details_nudge', '0,0', true, $wonder, $storeId));
        $shipment_details_boxes_yn = $this->_getConfig('shipment_details_boxes_yn', 0, false, $wonder, $storeId);
        $shipment_details_custgroup = $this->_getConfig('shipment_details_custgroup', 0, false, $wonder, $storeId);
        $shipment_details_customer_id = $this->_getConfig('shipment_details_customer_id', 0, false, $wonder, $storeId);
        $shipment_details_customer_email = $this->_getConfig('shipment_details_customer_email', 0, false, $wonder, $storeId);
        $shipment_details_customer_vat = $this->_getConfig('shipment_details_customer_vat', 0, false, $wonder, $storeId);
        $shipment_details_order_id = $this->_getConfig('shipment_details_order_id', 0, false, $wonder, $storeId);
        $shipment_details_invoice_id = $this->_getConfig('shipment_details_invoice_id', 0, false, $wonder, $storeId);
        $shipment_details_order_date = $this->_getConfig('shipment_details_order_date', 0, false, $wonder, $storeId);
        if ($generalConfig['shipment_details_bold_label_yn'] == 1)
            $shipment_details_label_separator = '';
        else
            $shipment_details_label_separator=':';
        $shipment_details_ship_date = $this->_getConfig('shipment_details_ship_date', 0, false, $wonder, $storeId);
        $shipment_details_paid_date = $this->_getConfig('shipment_details_paid_date', 0, false, $wonder, $storeId);
        $shipment_details_order_source = $this->_getConfig('shipment_details_order_source', 0, false, $wonder, $storeId);
        $shipment_details_fixed_text = $this->_getConfig('shipment_details_fixed_text', 0, false, $wonder, $storeId);
        $shipment_details_fixed_title = $this->_getConfig('shipment_details_fixed_title', 0, false, $wonder, $storeId);
        $shipment_details_fixed_value = $this->_getConfig('shipment_details_fixed_value', 0, false, $wonder, $storeId);
        $shipment_details_count = $this->_getConfig('shipment_details_count', 0, false, $wonder, $storeId);
        $shipment_details_weight = $this->_getConfig('shipment_details_weight', 0, false, $wonder, $storeId);
        $shipment_details_weight_unit = $this->_getConfig('shipment_details_weight_unit', 'kg', false, $wonder, $storeId);
        $shipment_details_pickup_time_yn = $this->_getConfig('shipment_details_pickup_time_yn', 0, false, $wonder, $storeId);
        $shipment_details_tracking_number = $this->_getConfig('shipment_details_tracking_number',0, false, $wonder, $storeId);
        $shipment_details_deadline_yn = $this->_getConfig('shipment_details_deadline_yn', 0, false, $wonder, $storeId);

        $show_wsa_storepickup = $this->_getConfig('show_wsa_storepickup', 1, false, $wonder, $storeId);
        if(!Mage::helper('pickpack')->isInstalled('Webshopapps_Wsacommon'))
            $show_wsa_storepickup = 0;
        if($show_wsa_storepickup != 0)
            $store_pickup_hide_shipping_yn = $this->_getConfig('store_pickup_hide_shipping_yn', 1, false, $wonder, $storeId);
        else
            $store_pickup_hide_shipping_yn = 0;

        $shipment_details_carrier = $this->_getConfig('shipment_details_carrier', 0, false, $wonder, $storeId);
        if($store_pickup_hide_shipping_yn == 1)
            $shipment_details_carrier = 0;

        if ($shipment_details_carrier == 'filtered_by_pallet')
            $shipment_details_pallet_weight = trim($this->_getConfig('shipment_details_pallet_weight', 500, false, $wonder, $storeId));
        else
            $shipment_details_pallet_weight = 0;

        $shipment_details_custom_attribute_yn = $this->_getConfig('shipment_details_custom_attribute_yn', 0, false, $wonder, $storeId);
        $shipment_details_custom_attribute = trim($this->_getConfig('shipment_details_custom_attribute', '', false, $wonder, $storeId));
        if ($shipment_details_custom_attribute == '')
            $shipment_details_custom_attribute_yn = 0;

        $shipment_details_custom_attribute_2_yn = $this->_getConfig('shipment_details_custom_attribute_2_yn', 0, false, $wonder, $storeId);
        $shipment_details_custom_attribute_2 = trim($this->_getConfig('shipment_details_custom_attribute_2', '', false, $wonder, $storeId));
        if ($shipment_details_custom_attribute_2 == '')
            $shipment_details_custom_attribute_2_yn = 0;

        if ($shipment_details_custom_attribute_yn == 0) {
            $shipment_details_custom_attribute = '';
            $shipment_details_custom_attribute_2_yn = 0;
            $shipment_details_custom_attribute_2 = '';
        }
        if ($shipment_details_custom_attribute_2_yn == 0)
            $shipment_details_custom_attribute_2 = '';

        $shipment_temando_comment_yn = $this->_getConfig('shipment_temando_comment_yn', 1, false, $wonder, $storeId);
        $shipment_details_payment = $this->_getConfig('shipment_details_payment', 0, false, $wonder, $storeId);
        $shipment_details_cardinfo = $this->_getConfig('shipment_details_cardinfo', 0, false, $wonder, $storeId);
        $shipment_details_purchase_order = $this->_getConfig('shipment_details_purchase_order', 0, false, $wonder, $storeId);
        if ($shipment_details_payment == 0) {
            $shipment_details_purchase_order = 0;
            $shipment_details_cardinfo = 0;
        }

        $shipping_detail_pad = explode(",", $this->_getConfig('shipping_detail_pad', '0,0,0', false, $wonder, $storeId));

        /*************************** PRINTING TOP EMAIL,PHONE,FAX *******************************/
        $tel_email_y = ceil($this->address_top_y - ($this->line_height_top * ($this->i_space + 1))) + $address_pad[0];
        if($shipping_details_yn == 0 && $billing_details_yn == 0)
            $this->i_space = -1;
        $this->subheader_start = ($this->address_top_y - ($this->line_height_top * ($this->i_space + 1)) - $this->line_addon + $address_pad[1]);
        $billing_shipping_details_y = null;

        $top_phone_Y = ceil($pageConfig['addressY'] - ($this->line_height_top * ($this->i_space + 1)) - $this->line_addon);
        $subheader_start_left = $this->subheader_start;
        if ($this->hasShippingAddress() !== false && (($shipment_details_yn == 1)))
            $this->shipping_method = $order->getShippingDescription();

        if ($shipment_details_yn == 1) {
            $this->orderdetailsX = $this->orderdetailsX + $shipping_detail_pad[2];
            if ($this->float_top_address_yn == 1) {
                $invoice_title = $this->_getConfig('pickpack_title_pattern', 0, false, $wonder, $storeId, false);
                if ($invoice_title != '')
                    $shipment_details_y = $this->headerBarXY[1];
                else
                    $shipment_details_y = ($this->subheader_start - ($generalConfig['font_size_body'] * 2));
                $this->orderdetailsX += 85;
            } elseif (($billing_details_yn == 1 && $billing_details_position != 2) && ($this->hasBillingAddress() === true) && ($shipping_details_yn == 1)) {
                if (($tel_email_y > $top_phone_Y) && ($customer_phone_yn != 'no') && ($this->customer_phone != '') && (strlen($this->customer_phone) > 5))
                    $shipment_details_y = $top_phone_Y;
                else
                    $shipment_details_y = $tel_email_y;
            } else {
                $shipment_details_y = ($this->address_top_y - $this->line_height_top);
                if (($shipping_billing_title_position != 'beside') && ((($billing_details_yn == 1 && $billing_details_position != 2) && ($this->hasBillingAddress() === true) && ($billing_title != '')) || (($shipping_details_yn == 1) && ($shipping_title != ''))))
                    $shipment_details_y += $this->line_height_top;
            }

            $shipment_details_y -= $shipping_detail_pad[0];


            $payment_test = '';
            if ($shipment_details_payment == 1 || $shipment_details_payment == 2) {
                $payment_order = $this->getPaymentOrder($order);
                if ($payment_order) {
                    $paymentInfo = Mage::helper('payment')->getInfoBlock($payment_order)
                        ->setIsSecureMode(true)
                        ->toPdf();
                } else
                    $paymentInfo = '';

                if ($shipment_details_payment == 1)
                    $payment_test = clean_method($paymentInfo, 'payment-full');
                elseif ($shipment_details_payment == 2) {
                    $payment_test = clean_method($paymentInfo, 'payment-full');
                    $currencyCode = '';
                    $currency = $order->getOrderCurrency();
                    if (is_object($currency))
                        $currencyCode = $currency->getCurrencyCode();
                }
                if(strpos($payment_test, 'BillSAFE') !== false)
                    $payment_test = '';
            }

            $customer_group = Mage::helper('pickpack')->getCustomerGroupCode((int)$order->getCustomerGroupId());
            $customer_id = trim($order->getCustomerId());
            $this->line_height = 4;

            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

            // Nudge up shipment details block if it's big
            $shipment_details_dispatch_date = $this->_getConfig('shipment_details_dispatch_date', 0, false, $wonder, $storeId);
            if(
                ($customer_custom_attribute_yn + ($customer_group != '') + ($customer_id != '')
                    + $shipment_details_boxes_yn + $shipment_details_carrier + $shipment_details_count + $shipment_details_custgroup
                    + $shipment_details_customer_email + $shipment_details_customer_id + $shipment_details_customer_vat + $shipment_details_deadline_yn
                    + $shipment_details_dispatch_date + $shipment_details_fixed_text + $shipment_details_invoice_id + $shipment_details_order_date + $shipment_details_order_id
                    + $shipment_details_order_source + $shipment_details_paid_date + $shipment_details_payment + $shipment_details_pickup_time_yn + $shipment_details_purchase_order
                    + $shipment_details_ship_date + $shipment_details_weight
                    + ($shipping_title != '')
                ) > 5
            ) {
                // left out for now to save seeing if extension is active
                // + $shipment_temando_comment_yn + $show_aitoc_checkout_field_yn + $show_mageworx_multifees + $show_wsa_storepickup
                $shipment_details_y += (1.15 * 1.5 * $generalConfig['font_size_body']);
            }
            $shipment_details_y += $shipment_details_nudge[1];
            $this->orderdetailsX += $shipment_details_nudge[0];
            $total_shipping_weight = 0;

            /**PRINTING WEIGHT**/
            if ($shipment_details_weight == 1) {
                /*foreach ($order->getAllItems() as $item) {
                    if(Mage::helper('pickpack')->getProduct($item->getProductId())->getTypeID() != "configurable")
                        $total_shipping_weight += (Mage::helper('pickpack')->getProduct($item->getProductId())->getWeight() * $item->getQtyOrdered());
                }*/
				$total_shipping_weight = $order->getWeight();
				
		        if($this->_getConfig('shipment_details_weight_addon_yn', 0, false, $wonder, $storeId) == 1)
		        	$total_shipping_weight += preg_replace("/[^0-9,.]/", "", $this->_getConfig('shipment_details_weight_addon', 0, false, $wonder, $storeId));
		        
				$total_shipping_weight = round($total_shipping_weight, 2); 

                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                
                $this->_drawText(Mage::helper('pickpack')->__('Weight').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($total_shipping_weight . ' ' . $shipment_details_weight_unit, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
            }
            /**END PRINTING WEIGHT**/

            /**PRINTING BOXES**/
            if ($shipment_details_boxes_yn == 1) {
                $rounded_weight = ($total_shipping_weight / 30);
                $rounded_weight = ceil($rounded_weight);
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                $this->_drawText(Mage::helper('pickpack')->__('Boxes').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($rounded_weight, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
            }
            /**END PRINTING BOXES**/

            $maxWidthPage = ($pageConfig['padded_right'] - ($this->orderdetailsX + 95));
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $font_size_compare = ($generalConfig['font_size_body']);
            $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
            $char_width = $line_width / 10;
            $this->max_chars = round($maxWidthPage / $char_width);
            $invert_X_plus = 0;

            /**PRINTING SHIPMENT PICKUP DEADLINE **/
            if ($shipment_details_pickup_time_yn == 1) {
                $pickup_date = date('m/d/Y',strtotime(trim($order->getData('pickup_date'))));//$order->getData('pickup_date');
                $time_slot = trim($order->getData('time_slot'));
                if(isset($this->shipping_method))
                {
                    $this->shipping_method = str_replace($time_slot,'',$this->shipping_method);
                    $this->shipping_method = str_replace($pickup_date,'',$this->shipping_method);
                    $this->shipping_method = trim($this->shipping_method);
                }
                if($generalConfig['shipment_details_bold_label_yn'] == 1){
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                }
                $this->_drawText($helper->__('Pickup Time').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($time_slot.' '.$pickup_date, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->subheader_start -= $generalConfig['font_size_body'];
            }

            /**PRINTING SHIPPING METHOD**/
            if(isset($this->shipping_method))
                $this->shipping_method = Mage::helper('pickpack/functions')->clean_method($this->shipping_method, 'shipping');

            if (($shipment_details_carrier != '0') && ($this->shipping_method != '')) {
                $show_storepickup_shipmethod = false;
                // storedelivery storepickup wsa check
                if ( ($show_wsa_storepickup != 1) || ( ($show_wsa_storepickup == 1) && ( (strpos($order->getData('shipping_method'),'storepickup') === false) && (strpos($order->getData('shipping_method'),'storedelivery') === false) ) ) ) {
                    $show_storepickup_shipmethod = true;
                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($helper->__('Shipping Type').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                }
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                if (($shipment_details_carrier == 'filtered_by_pallet') && ($shipment_details_pallet_weight < $total_shipping_weight)) {
                    $this->_drawText($helper->__('SHIP BY PALLET'), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                } elseif ( ($this->shipping_method != '') && ($show_storepickup_shipmethod !== false) ) {
                    $shipment_details_carrier_trim_yn = $this->_getConfig('shipment_details_carrier_trim_yn', 0, false, $wonder, $storeId);
                    if ( (strlen($this->shipping_method) > $this->max_chars) && ($show_wsa_storepickup !== 1) ) {
                        if($shipment_details_carrier_trim_yn == 1)
                        {
                            $shipping_display = str_trim($this->shipping_method, 'WORDS', $this->max_chars - 3, '...');
                            $this->_drawText($shipping_display, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                            $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                        }
                        else
                        {
                            // quick
                            $shipping_display = mb_wordwrap_array($this->shipping_method, $this->max_chars);
                            foreach ($shipping_display as $value) {
                                $this->_drawText(trim($value), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height += $generalConfig['font_size_body'];
                            }
                            unset($value);
                        }
                    } else {
                        // storedelivery storepickup
                        $this->_drawText($this->shipping_method, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                        $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    }
                }
                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                $this->shipping_method_static_display = '';
                if ($this->_getConfig('shipment_details_carrier_static_text_yn', 0, false, $wonder, $storeId) == 1) {
                    $this->shipping_method_static_text = $this->_getConfig('shipment_details_carrier_static_text', 0, false, $wonder, $storeId);
                    if (strlen($this->shipping_method_static_text) > $this->max_chars) {
                        $this->shipping_method_static_display = mb_wordwrap_array($this->shipping_method_static_text, $this->max_chars);

                        $token = strtok($this->shipping_method_static_text, "\n");
                        $multiline_shipping_top_array = array();

                        if ($token != false) {
                            while ($token != false) {
                                $multiline_shipping_top_array[] = $token;
                                $token = strtok("\n");
                            }

                            foreach ($multiline_shipping_top_array as $value) {
                                $this->_drawText(trim($value), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height += $generalConfig['font_size_body'];
                            }
                        }
                    } else {
                        $this->_drawText($this->shipping_method_static_display, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                        $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    }
                    $this->top_y_right_column = $shipment_details_y - $this->line_height;
                }
            }
            /**END PRINTING SHIPPING METHOD**/

            /* Magalter Order Options */
            if(isset($shipment_details_shipping_options) && $shipment_details_shipping_options == 1)
            {
                $shippingOptions = Mage::getModel('magalter_customshipping/order_option')->getCollection()->addFieldToFilter('order_id', $order->getId());
                foreach ($shippingOptions as $shippingOption)
                {
                    if(in_array($shippingOption->getData('name'),$shipment_details_shipping_options_filter))
                    {
                        $this->_drawText(ucfirst($shippingOption->getData('name')) . ': ', $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                        $this->_drawText(ucfirst(trim($shippingOption->getData('value'))), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                        $this->line_height += $generalConfig['font_size_body'];
                    }
                }
                unset($shipment_details_shipping_options);
                unset($shipment_details_shipping_options_filter);
                unset($shippingOptions);
                $this->line_height += $generalConfig['font_size_body']*2;
            }
            /* END Magalter Order Options */

            if ($shipment_details_tracking_number != '0')
            {
                $tracking_number = $this->getTrackingNumber($order);
                if(strlen($tracking_number) > 0)
                {
                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($helper->__('Tracking Number').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                    if (strlen($tracking_number) > $this->max_chars) {
                        $shipping_display = mb_wordwrap_array($tracking_number, $this->max_chars);
                        foreach ($shipping_display as $value) {
                            $this->_drawText(trim($value), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                            $this->line_height += $generalConfig['font_size_body'];
                        }
                        unset($value);
                    } else {
                        $this->_drawText($tracking_number, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                        $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    }
                    $this->top_y_right_column = $shipment_details_y - $this->line_height;
                }
            }

            /** aitcheckoutfields **/
            if($show_aitoc_checkout_field_yn == 1 && Mage::helper('pickpack')->isInstalled("Aitoc_Aitcheckoutfields")){
                $codes = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getInvoiceCustomData($order->getId(), null, true);
                $code_fields = explode(',', $show_aitoc_checkout_field);
                $code_lable = '';
                $code_value = '';
                foreach ($codes as $key => $code) {
                    if($code["code"] != '' && in_array($code["code"], $code_fields)){
                        $code_lable = $code_lable . ' ' . $code["label"];
                        $code_value = $code_value . ' ' . $code["value"];
                    }
                }
                $code_lable = trim($code_lable);
                $code_lable_arr = explode(' ', $code_lable);
                $arr_count_va = array_count_values($code_lable_arr);
                $label = '';
                foreach ($arr_count_va as $key => $value) {
                    if($value > 1)
                        $label = $label . ' ' . $key;
                }
                $label = trim($label);
                $code_value = trim($code_value);
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__($label).$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($code_value, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
            }
            $purchase_test = 0;

            /**PRINTING CUSTOMER COMMENT WITH TEMANDO**/
            if ($shipment_temando_comment_yn == 1) {
                if(Mage::helper('pickpack')->isInstalled('Temando_Temando') && Mage::helper('pickpack')->isInstalled('Idev_OneStepCheckout')){
                    $customer_comment = '';
                    $temando_model = Mage::getModel("temando/shipment");
                    $temando_model->load($order->getId(), "order_id");
                    $customer_comment = $temando_model->getData("customer_comment");
                    if($customer_comment != ''){
                        if($generalConfig['shipment_details_bold_label_yn'] == 1)
                            $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $this->_drawText($helper->__('Customer Comment').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        if(strlen($customer_comment) > $this->max_chars){
                            $customer_comment_display = mb_wordwrap_array($customer_comment, $this->max_chars);
                            foreach ($customer_comment_display as $value){
                                $this->_drawText($value, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                                $this->line_height += $generalConfig['font_size_body'];
                                unset($value);
                            }
                        }
                        else{
                            $this->_drawText($customer_comment, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                            $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                        }
                    }
                }

            }
            /**END PRINTING CUSTOMER COMMENT WITH TEMANDO**/

            /**PRINTING WHAT?**/
            if ($shipment_details_purchase_order == 1) {
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Purchase Order').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                if (strlen($purchase_test) > $this->max_chars) {
                    $payment_display = mb_wordwrap_array($purchase_test, $this->max_chars);
                    foreach ($payment_display as $value) {
                        $this->_drawText(trim(str_replace(array('#', ' '), '', $value)), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                        $this->line_height += $generalConfig['font_size_body'];
                    }
                    unset($value);
                    unset($purchase_test);
                    unset($payment_display);
                } else {
                    $this->_drawText(trim(str_replace(array('#', ' '), '', $purchase_test)), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                }
            }

            if ($shipment_details_custgroup == 1 && $customer_group != '') {
                $customer_group_filtered = $customer_group;
                $customer_group_filter_array = array();
                $customer_group_filter_array = explode(',', $customer_group_filter);
                foreach ($customer_group_filter_array as $customer_group_filter_single) {
                    $customer_group_filtered = trim(str_ireplace(trim($customer_group_filter_single), '', $customer_group_filtered));
                }

                if ($customer_group_filtered != '') {
                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                    $this->_drawText($helper->__('Customer Group').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($customer_group, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    $this->top_y_right_column -= $generalConfig['font_size_body'];
                }
            }
            /**PRINTING FIXED TEXT**/
            if ($shipment_details_fixed_text == 1 && $shipment_details_fixed_title != '' && $shipment_details_fixed_value!= '') {
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__($shipment_details_fixed_title).$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($shipment_details_fixed_value, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];
            }
            /**PRINTING CUSTOMER ID**/
            if ($shipment_details_customer_id == 1 && $customer_id != '') {
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Customer ID').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($customer_id, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];
            }
            /**END PRINTING CUSTOMER ID**/

            /**PRINTING Mageworx Multifees**/
            if ($show_mageworx_multifees == 1) {
                $details_multifees = array();
                if(Mage::helper('pickpack')->isInstalled('MageWorx_MultiFees') && $order->getData('multifees_amount') > 0){
                    $details_multifees = unserialize($order->getData("details_multifees"));
                    $this->_setFont($page, 'bold', $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    foreach ($details_multifees as $key => $fee) {
                        $this->_drawText(Mage::helper('multifees')->__($fee["title"]), $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                        $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                        $this->top_y_right_column -= $generalConfig['font_size_body'];
                    }
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                }
            }

            /**PRINTING WSA Storepickup**/
            if ( ($show_wsa_storepickup == 1) && (Mage::helper('pickpack')->isInstalled('Webshopapps_Wsacommon')) ) {
                $wsa_storepickup_options = $this->_getConfig('wsa_storepickup_options', 0, false, $wonder, $storeId);
                $wsa_storepickup_options_arr = explode(",", $wsa_storepickup_options);
                $wsa_pickup_location_model_default = $this->_getConfig('wsa_pickup_location_model_default', '', false, $wonder, $storeId);
                if (Mage::helper('core')->isModuleEnabled($wsa_pickup_location_model_default))
                    $pickupStore = Mage::getModel($wsa_pickup_location_model_default.'/location')->load($order->getData('fulfillment_location'));

                if ( (strpos($order->getData('shipping_method'),'storepickup') !== false) || (strpos($order->getData('shipping_method'),'storedelivery') !== false) ){
                    if ($this->_getConfig('non_store_pickup_showdatetime', 1, false, $wonder, $storeId) == 1){
                        $wsa_storepickup_options_arr = array_replace($wsa_storepickup_options_arr,
                            array_fill_keys(
                                array_keys($wsa_storepickup_options_arr, 'pickup_date'),
                                'pickup_date_time'
                            )
                        );
                        if(($key = array_search('pickup_time', $wsa_storepickup_options_arr)) !== false)
                            unset($wsa_storepickup_options_arr[$key]);
                    }

                    $shown_store_pickup_address_title = false;

                    foreach ($wsa_storepickup_options_arr as $wsa_storepickup_options_arr_value) {
                        switch ($wsa_storepickup_options_arr_value) {
                            case 'pickup_location':
                                /*
                                        Add in shipping method here so we can include the store ID in the same line
                                    */
                                if(!$pickupStore->getData('title'))
                                    continue;

                                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $this->_drawText($helper->__('Shipping Type').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $show_wsa_shipping_display = '';
                                if(isset($this->shipping_method))
                                    $show_wsa_shipping_display = preg_replace('~ \- (.*)$~','',$this->shipping_method). ' - ';
                                $this->_drawText($show_wsa_shipping_display . $pickupStore->getData('title')." (#".$pickupStore->getData('identifier').')', ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                                break;

                            case 'pickup_date':
                                if(!$order->getData('fulfillment_date'))
                                    continue;
                                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $this->_drawText($helper->__('Pickup Date').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $this->_drawText($order->getData('fulfillment_date'), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                                break;
                            case 'pickup_time':
                                if(!$order->getData('fulfillment_slot'))
                                    continue;
                                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $this->_drawText($helper->__('Pickup Time').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $pickUpTime = trim($order->getData('fulfillment_slot'));
                                $pickUpTime = explode('|', $pickUpTime);
                                $pickUpTimeFrom =  date('g:i a',strtotime(substr($pickUpTime[0],0,19)));
                                $pickUpTimeTo =  date('g:i a',strtotime(substr($pickUpTime[1],0,19)));
                                $this->_drawText( $pickUpTimeFrom." - ".$pickUpTimeTo  , ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                                break;

                            case 'pickup_store_address':
                                if(!$pickupStore->getData('street') && !$pickupStore->getData('city') && !$pickupStore->getData('region'))
                                    continue;
                                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $this->_drawText($helper->__('Store Address').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));

                                $shown_store_pickup_address_title = true;
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                if($pickupStore->getData('street'))
                                    $this->_drawText($pickupStore->getData('street'), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                                $pickup_store_address = '';

                                if($pickupStore->getData('city'))
                                    $pickup_store_address .= $pickupStore->getData('city');
                                if($pickupStore->getData('region'))
                                    $pickup_store_address .= ', ' . Mage::helper('pickpack/state')->convertState($pickupStore->getData('region'));
                                if($pickupStore->getData('postal_code'))
                                    $pickup_store_address .= ' '.$pickupStore->getData('postal_code');
                                if($pickup_store_address != '')
                                    $this->_drawText($pickup_store_address, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));

                                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                                break;

                            case 'pickup_store_phone':
                                if(!$pickupStore->getData('phone'))
                                    continue;

                                if($shown_store_pickup_address_title === false) {
                                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    $this->_drawText($helper->__('Store Address').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                                    $shown_store_pickup_address_title = true;
                                }
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $this->_drawText($pickupStore->getData('phone'), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                                break;
                            case 'pickup_store_email':
                                if(!$pickupStore->getData('email'))
                                    continue;
                                if($shown_store_pickup_address_title === false)
                                    if($generalConfig['shipment_details_bold_label_yn'] == 1){
                                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                        $this->_drawText($helper->__('Store Address').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                                        $shown_store_pickup_address_title = true;
                                    }
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $this->_drawText($pickupStore->getData('email'), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                                break;
                            case 'pickup_date_time':
                                if(!$order->getData('fulfillment_slot') && !$order->getData('fulfillment_date'))
                                    continue;
                                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $this->_drawText($helper->__('Date/Time').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                $pickUpTime = trim($order->getData('fulfillment_slot'));
                                $pickUpTime = explode('|', $pickUpTime);
                                // may need to check against timezone of each store
                                $pickUpTimeFrom =  date('g:i a',strtotime(substr($pickUpTime[0],0,19)));
                                $pickUpTimeTo =  date('g:i a',strtotime(substr($pickUpTime[1],0,19)));
                                if($pickUpTimeFrom == $pickUpTimeTo)
                                    $this->_drawText($order->getData('fulfillment_date').' '.$pickUpTimeFrom , ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                else
                                    $this->_drawText($order->getData('fulfillment_date').' '.$pickUpTimeFrom." - ".$pickUpTimeTo  , ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                $this->top_y_right_column = $shipment_details_y - $this->line_height;
                                break;
                        }
                    }
                }
            }

            /**PRINTING PAYMENT METHOD**/
            if(($shipment_details_payment == 1 || $shipment_details_payment == 2) && (strlen($payment_test) > 0))
            {
                if ($shipment_details_purchase_order == 1) {
                    if (stripos($payment_test, 'purchase order') !== false) {
                        $purchase_test = trim(str_ireplace(array('Purchase order', ':', '  '), array('', '', ' '), $payment_test));
                        $payment_test = 'Purchase Order';
                        if (strlen($purchase_test) < 1)
                            $shipment_details_purchase_order = 0;
                    } else $shipment_details_purchase_order = 0;
                }
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Payment').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $shipment_details_payment_trim_yn = $this->_getConfig('shipment_details_payment_trim_yn', 0, false, $wonder, $storeId);
                if ((strlen($payment_test) > $this->max_chars) || (strpos($payment_test, '|') !== false))
                {
                    if($shipment_details_payment_trim_yn == 1)
                    {
                        $payment_display = str_trim($payment_test, 'WORDS', $this->max_chars - 3, '...');
                        $this->_drawText($payment_display, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                        $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    }
                    else
                    {
                        if (strpos($payment_test, '|') !== false)
                            $payment_display = explode('|', $payment_test);
                        else
                            $payment_display = mb_wordwrap_array($payment_test, $this->max_chars);

                        foreach ($payment_display as $value) {
                            $this->_drawText(Mage::helper('pickpack/functions')->clean_method(trim($value),'payment'), ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                            $this->line_height += $generalConfig['font_size_body'];
                        }
                        unset($value);
                        unset($payment_display);
                    }
                } else {
                    $this->_drawText($payment_test, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                }

                if ($shipment_details_payment == 2 && isset($currencyCode))
                    $this->_drawText("Order was placed using " . $currencyCode, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                $this->top_y_right_column = $shipment_details_y - $this->line_height;
            }
            /**END PRINTING PAYMENT METHOD**/

            /**PRINTING ITEM COUNT**/
            if ($shipment_details_count == 1) {
                $items_count = 0;
                $items_count = ceil($order->getTotalQtyOrdered());
                if (!$items_count || ($items_count == 0))
                    $items_count = count(Mage::helper('pickpack/order')->getItemsToProcess($order));
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                $this->_drawText($helper->__('Item Count').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                /**draw item count after**/
                $this->_drawText($items_count, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column = $shipment_details_y - $this->line_height;
            }
            /**END PRINTING ITEM COUNT**/

            /** PRINTING CUSTOMER SERVICE DETAILS **/
            if ($show_wsa_storepickup == 1) {
                if ( (Mage::helper('pickpack')->isInstalled('Shipperhq_Pickup'))
                    && (strpos($order->getData('shipping_method'),'storepickup') === false)
                    && (strpos($order->getData('shipping_method'),'storedelivery') === false)
                    && ($this->_getConfig('non_store_pickup_yn', 0, false, $wonder, $storeId) == 1) ){

                    $non_store_pickup_label = $this->_getConfig('non_store_pickup_label', '', false, $wonder, $storeId);
                    $non_store_pickup_value = $this->_getConfig('non_store_pickup_value', '', false, $wonder, $storeId);
                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($helper->__($non_store_pickup_label).$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($non_store_pickup_value, ($this->orderdetailsX + 95 + $invert_X_plus), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    $this->top_y_right_column = $shipment_details_y - $this->line_height;
                }
            }
            /** END PRINTING CUSTOMER SERVICE DETAILS **/

            /** PRINTING CUSTOMER EMAIL **/
            if ($order->getCustomerEmail()) $customer_email = trim($order->getCustomerEmail());
            if ($shipment_details_customer_email == 1 && $customer_email != '') {
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Customer Email').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($customer_email, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];
            }
            /** END PRINTING CUSTOMER EMAIL **/
            /** PRINTING CUSTOMER VAT **/
            if ($shipment_details_customer_vat == 1) {
                $billingaddress_2 = $order->getBillingAddress();
                if ($billingaddress_2->getData('vat_id')) {
                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($helper->__('Customer VAT').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText(trim($billingaddress_2->getData('vat_id')), ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    $this->top_y_right_column -= $generalConfig['font_size_body'];
                }
            }
            /**END PRINTING CUSTOMER VAT**/

            /**PRINTING ORDER ID**/
            if ($shipment_details_order_id == 1) {
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Order Number').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($order->getRealOrderId(), ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];
            }

            /**PRINTING ORDER Date**/
            if ($shipment_details_order_date == 1) {
                $order_date_title = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $date_format);
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Order Date').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                //TODO Moo update 2
                $this->_drawText($order_date_title, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];
            }

            /**PRINTING INVOICE ID**/
            $invoice_number_display = '';
            foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
                if ($_tmpInvoice->getIncrementId()) {
                    if ($invoice_number_display != '')
                        $invoice_number_display .= ',';
                    $invoice_number_display .= $_tmpInvoice->getIncrementId();
                }
                break;
            }
            if ($shipment_details_invoice_id == 1 && $invoice_number_display != '') {
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Invoice Number').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($invoice_number_display, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];

            }
            unset($invoice_number_display);
            /**PRINTING PAID Date**/
            if ($shipment_details_paid_date == 1 && $order->getCreatedAtStoreDate()) {
                $invoice_date_title = Mage::helper('pickpack/functions')->createInvoiceDateByFormat($order, $date_format_strftime, $date_format);
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Paid Date').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($invoice_date_title, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];
            }
            /**PRINTING SHIPP Date**/
            if ($shipment_details_ship_date == 1 && count($order->getShipmentsCollection()) > 0) {
                $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($storeId, $date_format);
                $shipment_date_title = Mage::helper('pickpack/functions')->createShipmentDateByFormat($order, $date_format_strftime, $date_format);
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Ship Date').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($shipment_date_title, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];
            }
            /**PRINTING Order Source**/
            if ($shipment_details_order_source == 1) {
                $store = Mage::getModel('core/store')->load($order->getStoreId());
                $source_website         = $store->getName();
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($helper->__('Order Source').$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($source_website, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                $this->top_y_right_column -= $generalConfig['font_size_body'];
            }


            /**PRINTING CUSTOMER CUSTOM ATTRIBUTE**/
            if ( ($customer_custom_attribute_yn == 1) && ($customer_custom_attribute != '') ) {
                // && (isset($customer_attribute_array[$customer_custom_attribute]))
                $customer_attribute_array = array();
                $customer_attribute_label_array = array();
                $customer_attribute = '';
                $customer_attribute_array = Mage::getModel('customer/customer')->load($order->getCustomerId())->getData();
                $customer_attribute = $customer_attribute_array[$customer_custom_attribute];
                $customer_attribute_label_array = Mage::getSingleton('eav/config')->getAttribute('customer', $customer_custom_attribute);

                if (strlen(trim($customer_attribute)) > 0) {
                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($helper->__($customer_attribute_label_array['frontend_label']).$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($customer_attribute, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    $this->top_y_right_column -= $generalConfig['font_size_body'];
                }

                unset($customer_attribute_array);
                unset($customer_attribute_label_array);
                unset($customer_attribute);
            }
            /**END PRINTING CUSTOMER CUSTOM ATTRIBUTE**/

            /**PRINTING ORDER CUSTOM ATTRIBUTES **/
            if (Mage::helper('pickpack')->isInstalled('Amasty_Orderattr')) {
                if ($shipment_details_custom_attribute_yn == 1 && $shipment_details_custom_attribute != '' && class_exists(get_class(Mage::getModel('amorderattr/attribute')))) {
                    unset($shipment_custom_attribute_label);
                    unset($shipment_custom_attribute);
                    $shipment_custom_attribute_label = array();
                    $shipment_custom_attribute = array();
                    $collection = Mage::getModel('eav/entity_attribute')->getCollection();
                    $collection->addFieldToFilter('is_visible_on_front', 1);
                    $collection->addFieldToFilter('entity_type_id', Mage::getModel('eav/entity')->setType('order')->getTypeId());
                    $attributes = $collection->load();

                    if ($attributes->getSize()) {
                        $list =  $this->getValueOrderAttribute($attributes, $this->filter_custom_attributes_array, $order);
                        foreach ($list as $label => $value) {
                            if (is_array($value) && !(empty($value))) {
                                $this->_drawText($label . ': ', $this->email_X, $this->subheader_start);
                                foreach ($value as $str) {
                                    foreach (explode("%BREAK%", $str) as $s) {
                                        $this->_drawText($s, $this->email_X + 10, $this->subheader_start);
                                        $this->line_height -= $generalConfig['font_size_body'];
                                    }
                                }
                                $this->line_height -= $generalConfig['font_size_body'];
                            } else {
                                if (strlen(trim($value)) > 0) {
                                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    $this->_drawText($label.$shipment_details_label_separator, $this->email_X, ($shipment_details_y - $this->line_height));
                                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                                    $this->_drawText($value, ($this->email_X + 85), ($shipment_details_y - $this->line_height));
                                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                                }
                            }
                        }
                    }
                }
            }

            if ($shipment_details_custom_attribute_2_yn == 1 && $shipment_details_custom_attribute_2 != '') {
                if (isset($shipment_custom_attribute_label[$shipment_details_custom_attribute_2]) && isset($shipment_custom_attribute[$shipment_details_custom_attribute_2]) && ($shipment_custom_attribute[$shipment_details_custom_attribute_2] != '')) {
                    $display_cust_attr_label_2 = $shipment_custom_attribute_label[$shipment_details_custom_attribute_2];
                    $display_cust_attr_2 = $shipment_custom_attribute[$shipment_details_custom_attribute_2];
                    if($generalConfig['shipment_details_bold_label_yn'] == 1)
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($display_cust_attr_label_2.$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($display_cust_attr_2, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                }
            }
            /**PRINTING ORDER CUSTOM ATTRIBUTES **/

            /**PRINTING SHIPEASY CUSTOM ATTRIBUTES **/
            if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy')) {
                $shipeasy_flag_yn = $this->_getConfig('shipeasy_flag_yn', 0, false, $wonder, $storeId);
                $shipeasy_flag_column = trim($this->_getConfig('shipeasyflagselect', '', false, $wonder, $storeId));
                if ($shipeasy_flag_yn && $shipeasy_flag_column) {
                    $orderGrid = Mage::getResourceModel('sales/order_grid_collection')->addFieldToFilter('entity_id', $order->getId())->getFirstItem();
                    $flagColumn = 'szy_custom_attribute' . ($shipeasy_flag_column > 1 ? $shipeasy_flag_column : '');
                    $shipeasy_flag_value = $orderGrid->getData($flagColumn) ;
                    $shipeasy_flag_label = Mage::getStoreConfig('moogento_shipeasy/grid/' . $flagColumn . '_header');
                    if ($generalConfig['shipment_details_bold_label_yn'] == 1)
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($shipeasy_flag_label . $shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                    $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($shipeasy_flag_value, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                }
            }
            /**PRINTING SHIPEASY CUSTOM ATTRIBUTES **/

            /**PRINTING courierRules Method **/
            if (Mage::helper('pickpack')->isInstalled('Moogento_CourierRules')) {
                $courierrules_method_yn = $this->_getConfig('courierrules_method_yn', 0, false, $wonder, $storeId);
                if ($courierrules_method_yn) {
                    $courierrules_value = $order->getCourierrulesDescription();
                    if ($courierrules_value) {
                        if ($generalConfig['shipment_details_bold_label_yn'] == 1)
                            $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $this->_drawText($helper->__('courierRules Method') . $shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                        $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $this->_drawText($courierrules_value, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                        $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                    }
                }
            }
            /**PRINTING courierRules Method **/

            /**PRINTING SHIPMENT DETAILS DISPATCH DATE**/
            $shipment_details_dispatch_date = $this->_getConfig('shipment_details_dispatch_date', 0, false, $wonder, $storeId);
            if ($shipment_details_dispatch_date) {
                $dispatch_date = '';
                /**  get delivery date from Magestore_Onestepcheckout **/
                if ($shipment_details_dispatch_date == 2 && Mage::helper('pickpack')->isInstalled("Magestore_Onestepcheckout")){
                    $magestore_delivery_model = Mage::getModel('onestepcheckout/delivery')->load($order->getId(), 'order_id');
                    if (isset($magestore_delivery_model) && $magestore_delivery_model->getData('delivery_time_date')!="")
                        $dispatch_date = $magestore_delivery_model->getData('delivery_time_date');
                }elseif ($shipment_details_dispatch_date == 1)
                    $dispatch_date = date($date_format,Mage::getModel('core/date')->timestamp($order->getData('shipping_dispatch_date')));
                if ($dispatch_date != ''){
                    if ($generalConfig['shipment_details_bold_label_yn'] == 1) {
                        $this->_setFont($page, "bold", $generalConfig['font_size_body'],
                            $generalConfig['font_family_body'],
                            $generalConfig['non_standard_characters'],
                            $generalConfig['font_color_body']);
                    }
                    $this->_drawText($helper->__('Dispatch Date').$shipment_details_label_separator, $this->orderdetailsX,
                        ($shipment_details_y - $this->line_height));
                    $this->_setFont($page, $generalConfig['font_style_body'],
                        $generalConfig['font_size_body'], $generalConfig['font_family_body'],
                        $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($dispatch_date, ($this->orderdetailsX + 95),
                        ($shipment_details_y - $this->line_height));
                    $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
                }
            }
            /**END PRINTING SHIPMENT DETAILS DISPATCH DATE**/

            /**PRINTING SHIPMENT DETAILS DEADLINE **/
            if ($shipment_details_deadline_yn == 1) {
		        $shipment_details_deadline_text = trim($this->_getConfig('shipment_details_deadline_text', '', false, $wonder, $storeId));
		        $shipment_details_deadline_date_type = trim($this->_getConfig('shipment_details_deadline_date_type', 'printing', false, $wonder, $storeId));
		        $shipment_details_deadline_days = trim($this->_getConfig('shipment_details_deadline_days', 0, false, $wonder, $storeId));
		        if ($shipment_details_deadline_yn == 0) {
		            $shipment_details_deadline_text = '';
		            $shipment_details_deadline_days = 0;
		        }
				
				$start_date = '';
				$deadline_date = false;
				switch ($shipment_details_deadline_date_type) {
					case 'invoice':
						$start_date = Mage::helper('pickpack/functions')->createInvoiceDateByFormat($order, $date_format_strftime, 'timestamp');
						if($start_date !== false)
							$deadline_date = date($date_format, Mage::getModel('core/date')->timestamp(($start_date + (60 * 60 * 24 * $shipment_details_deadline_days))));
						break;
					
					case 'order':
						$start_date = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, 'timestamp');
						//strtotime($order->getCreatedAt());
						if($start_date !== false)
							$deadline_date = date($date_format, Mage::getModel('core/date')->timestamp(($start_date + (60 * 60 * 24 * $shipment_details_deadline_days))));
						break;

					case 'printing':
					default:
						$deadline_date = date($date_format, Mage::getModel('core/date')->timestamp((time() + (60 * 60 * 24 * $shipment_details_deadline_days))));
						break;
				}
				unset($start_date);
                
				if($deadline_date == false)
					$deadline_date = 'n/a';
                if($generalConfig['shipment_details_bold_label_yn'] == 1)
                    $this->_setFont($page, "bold", $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($shipment_details_deadline_text.$shipment_details_label_separator, $this->orderdetailsX, ($shipment_details_y - $this->line_height));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText($deadline_date, ($this->orderdetailsX + 95), ($shipment_details_y - $this->line_height));
                $this->line_height = ($this->line_height + (1.15 * $generalConfig['font_size_body']));
            }

            /**END PRINTING SHIPMENT DETAILS DEADLINE **/
            if ($address_pad[1] < 0)
                $this->subheader_start = $this->top_y_left_column + $address_pad[1];

            if ($shipment_details_yn == 1) {
                $this->line_height = ($this->line_height - (2 * $generalConfig['font_size_body']));
                if ($this->subheader_start > ($shipment_details_y - $this->line_height))
                    $this->subheader_start = ceil($shipment_details_y - ($this->line_height));
                if ($this->subheader_start > $this->top_y_right_column)
                    $this->subheader_start = $this->top_y_right_column;
            }
        }

        if ( ($billing_details_yn == 0) && ($shipping_details_yn == 0) && ($shipment_details_yn == 0) ) {
            if ($this->subheader_start > ($this->headerBarXY[1] + $generalConfig['font_size_subtitles']))
                $this->subheader_start = ($this->headerBarXY[1] + $generalConfig['font_size_subtitles']);
            else
                $this->subheader_start = ($this->headerBarXY[1]);
        } elseif($this->subheader_start > $subheader_start_left)
            $this->subheader_start = $subheader_start_left;

        // Move down start of titlebar if shipping details has a base-nudge
        $this->subheader_start -= $shipping_detail_pad[1];
        $this->subheader_start -= 10;
        $this->subheader_start -= $address_pad[1];
        $this->y = $this->subheader_start + $generalConfig['font_size_body'];
    }

    public function showBottomExtraAddress() {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $page = $this->getPage();

        if($this->caseRotate > 0)
            $this->rotateLabel($this->caseRotate, $page, $pageConfig['page_top'], $pageConfig['padded_right']);

        $this->minY[] = $this->addressFooterXY_xtra[1];
        $return_to_this_y = $this->y;
        $this->y = $this->addressFooterXY_xtra[1];
        $this->shipping_address_flat = trim(str_replace(',,', ',', $this->shipping_address_flat));
        $max_flat_shipping_address_width = ($pageConfig['padded_right'] - $this->flat_address_margin_rt_xtra - $this->addressFooterXY_xtra[0]);
        $max_flat_shipping_address_characters = stringBreak($this->shipping_address_flat, $max_flat_shipping_address_width, $this->font_size_shipaddress_xtra);
        $shipping_address_flat_wrapped = wordwrap($this->shipping_address_flat, $max_flat_shipping_address_characters, "\n", false);
        $this->y -= ($this->font_size_shipaddress_xtra + 2);
        $this->line_count = 0;
        $token = strtok($shipping_address_flat_wrapped, "\n");
        $this->_setFont($page, 'regular', ($this->font_size_shipaddress_xtra - 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], '#666666');
        $this->_drawText('#' . trim( $this->getOrder()->getRealOrderId()), $this->addressFooterXY_xtra[0], $this->y);
        $this->y -= ($this->font_size_shipaddress_xtra * 1.2);

        $this->_setFont($page, 'regular', ($this->font_size_shipaddress_xtra), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        while ($token != false) {
            $this->_drawText(trim($token), $this->addressFooterXY_xtra[0], $this->y);
            $this->y -= $this->font_size_shipaddress_xtra;
            $token = strtok("\n");
            $this->line_count++;
        }
        unset($token);

        if($this->caseRotate > 0)
            $this->reRotateLabel($this->caseRotate, $page, $pageConfig['page_top'], $pageConfig['padded_right']);

        return $return_to_this_y;
    }

    public function showMultiShippingAddress() {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $bottom_2nd_shipping_address_yn = $this->_getConfig('pickpack_second_bottom_shipping_address_yn', 0, false, $wonder, $storeId);
        $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $wonder, $storeId));
        $address2ndFooterXY = explode(",", $this->_getConfig('pickpack_second_shipaddress', $pageConfig['addressFooterXYDefault'], true, $wonder, $storeId));
        $bottom_shipping_address_id_yn = $this->_getConfig('pickpack_bottom_shipping_address_id_yn', 0, false, $wonder, $storeId);
        $font_size_adjust = ($this->packingsheetConfig['capitalize_label2_yn']) ? 2 : 0;
        $this->line_height_bottom = (1.05 * $this->fontSizeShippingAddress);

        /**BOTTOM 2nd SHIPPING ADDRESS**/
        if ($this->packingsheetConfig['pickpack_bottom_shipping_address_yn'] == 1 && $bottom_2nd_shipping_address_yn == 1) {
            $this->bottom_ispace = 0;
            $bottom_2nd_shipping_address_pos = array();
            $bottom_2nd_shipping_address_pos['x'] = $address2ndFooterXY[0];
            $bottom_2nd_shipping_address_pos['y'] = $address2ndFooterXY[1];
            $this->string_2nd_shipping_address = trim($this->string_2nd_shipping_address,",");
            $this->multiline_shipping_bottom = wordwrap($this->string_2nd_shipping_address, $this->max_chars_shipping_bottom, "\n");
            $token = strtok($this->multiline_shipping_bottom, "\n");
            $multiline_shipping_bottom_array = array();
            if ($bottom_shipping_address_id_yn == 1) {
                $this->_setFont($this->getPage(), $generalConfig['font_style_body'], ($generalConfig['font_size_body'] + 0.5), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                $this->_drawText('#' . $this->getOrder()->getRealOrderId(), $address2ndFooterXY[0], $address2ndFooterXY[1] + $this->fontSizeShippingAddress - $this->line_addon - 30);
                $this->minY[] = ($address2ndFooterXY[1] + ($this->fontSizeShippingAddress)) - 7;
            }
            if ($token != false) {
                while ($token != false) {
                    $multiline_shipping_bottom_array[] = $token;
                    $token = strtok("\n");
                }

                foreach ($multiline_shipping_bottom_array as $shipping_in_line) {
                    if ($this->bottom_ispace == 0)
                        $this->bottom_ispace++;
                    $this->bottom_ispace++;
                    $this->_setFont($this->getPage(), $generalConfig['font_style_body'], ($this->fontSizeShippingAddress - $font_size_adjust), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $bottom_2nd_shipping_address_pos['y'] = ($address2ndFooterXY[1] - ($this->line_height_bottom * $this->bottom_ispace) - $this->line_addon);
                    $this->_drawText($shipping_in_line, $bottom_2nd_shipping_address_pos['x'], $bottom_2nd_shipping_address_pos['y']);
                }
            }
        }
    }

    public function showShippingMethodBottom() {
        $page = $this->getPage();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        if (!isset($this->bottom_shipping_address_pos['x']) || !isset($this->bottom_shipping_address_pos['y'])){
            $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
            $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
            $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;
            $this->bottom_shipping_address_pos['x'] = $addressFooterXY[0];
            $this->bottom_shipping_address_pos['y'] = $addressFooterXY[1];
        }

        $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $this->getWonder(), $this->getStoreId()));
        $show_shipping_method_bottom_nugde = explode(",", $this->_getConfig('show_shipping_method_bottom_nugde', '0,0', true, $this->getWonder(), $this->getStoreId()));

        if($this->caseRotate > 0)
            $this->rotateLabel($this->caseRotate, $page, $pageConfig['page_top'], $pageConfig['padded_right']);

        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] + 0.5), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        $shipping_method_raw = $this->getOrder()->getShippingDescription();
        $shipping_method = Mage::helper('pickpack/functions')->clean_method($shipping_method_raw, 'shipping');
        $this->_drawText(Mage::helper('pickpack')->__('Shipping Type') . ' : ', $this->bottom_shipping_address_pos['x'] + $show_shipping_method_bottom_nugde[0], ( $this->bottom_shipping_address_pos['y'] - $generalConfig['font_size_body'] - 18 + $show_shipping_method_bottom_nugde[1]));
        $this->_drawText($shipping_method, $this->bottom_shipping_address_pos['x'] + $show_shipping_method_bottom_nugde[0] + 50, ( $this->bottom_shipping_address_pos['y'] - $generalConfig['font_size_body'] - 18 + $show_shipping_method_bottom_nugde[1]));

        if($this->caseRotate > 0)
            $this->reRotateLabel($this->caseRotate, $page, $pageConfig['page_top'], $pageConfig['padded_right']);
    }

    public function getMaxAddressLineCount() {
        $shippingLineCount = count($this->shippingAddressArray);
        $billingLineCount = count($this->billingAddressArray);

        return ($shippingLineCount >= $billingLineCount) ? $shippingLineCount : $billingLineCount;
    }

    public function showBottomShippingAddressId2() {
        $page = $this->getPage();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $this->getWonder(), $this->getStoreId()));
        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
        $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;

        if($this->caseRotate > 0)
            $this->rotateLabel($this->caseRotate, $page, $pageConfig['page_top'], $pageConfig['padded_right']);

        $bottom_order_id_nudge = explode(",", $this->_getConfig('pickpack_nudge_bottom_movable_order_id', '0, 0', true, $this->getWonder(), $this->getStoreId()));
        if (!isset($bottom_order_id_nudge[1])) $bottom_order_id_nudge[1] = 0;

        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] + 0.5), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

        $this->_drawText('#' . $this->getOrder()->getRealOrderId(), $addressFooterXY[0] + $bottom_order_id_nudge[0], $bottom_order_id_nudge[1] + $this->bottomOrderIdY);
        $this->minY[] = ($addressFooterXY[1] + ($this->fontSizeShippingAddress)) - 7;
        if($this->caseRotate > 0)
            $this->reRotateLabel($this->caseRotate, $page, $pageConfig['page_top'], $pageConfig['padded_right']);
    }

    public function showShippingAddressBarcode() {
        $order = $this->getOrder();
        $realOrderId = $order->getRealOrderId();
        $page = $this->getPage();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $white_color = Mage::helper('pickpack/config_color')->getPdfColor('white_color');
        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
        $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;

        $bottom_barcode_nudge = explode(",", $this->_getConfig('bottom_barcode_nudge', '0,0', true, $this->getWonder(), $storeId));
        $bottom_barcode_nudge[0] += self::BOTTOM_BARCODE_DEFAULT_NUDGE_X;
        $bottom_barcode_nudge[1] += self::BOTTOM_BARCODE_DEFAULT_NUDGE_Y;

        $barcode_font_size = $generalConfig['font_size_barcode_order'];//16;
        $left_down = 0;

        if ($generalConfig['barcode_type'] !== 'code128') {
            $barcode_font_size += 12;
            $left_down = 12;
        }

        $bottom_barcode_nudge[0] = trim((int)$bottom_barcode_nudge[0]);
        $bottom_barcode_nudge[1] = trim((int)$bottom_barcode_nudge[1]);
        $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($realOrderId, $generalConfig['barcode_type']);
        $barcodeWidth = 1.35 * $this->parseString($realOrderId, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);

        $x1 = ($addressFooterXY[0] + $left_down + $bottom_barcode_nudge[0]);
        $x2 = ($addressFooterXY[0] + $barcodeWidth + 5 + $bottom_barcode_nudge[0]);
        $yy1 = ($addressFooterXY[1] + $barcode_font_size - 12 - $left_down + $bottom_barcode_nudge[1] + 5 );
        $y1 = ($yy1 + ($barcode_font_size * 0.7)); // how far up the barcode the blanker starts
        $y2 = ($y1 + ($barcode_font_size * 0.6)); // how high the blanker goes

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
        $this->_drawText($barcodeString, $x1, $yy1, 'CP1252');

        $page->setFillColor($white_color);
        $page->setLineColor($white_color);

        $page->drawRectangle($x1, $y1, $x2, $y2);
        $this->minY[] = $addressFooterXY[1] + ($barcode_font_size) - ($left_down / 4) + $bottom_barcode_nudge[1];
    }

    public function showShippingAddressBarcode2() {
        $order = $this->getOrder();
        $realOrderId = $order->getRealOrderId();
        $page = $this->getPage();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $white_color = Mage::helper('pickpack/config_color')->getPdfColor('white_color');

        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
        $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;

        $bottom_barcode2_nudge = explode(",", $this->_getConfig('bottom_barcode2_nudge', '0,0', true, $this->getWonder(), $storeId));
        $bottom_barcode2_nudge[0] += self::BOTTOM_BARCODE_DEFAULT_NUDGE_X;
        $bottom_barcode2_nudge[1] += self::BOTTOM_BARCODE_DEFAULT_NUDGE_Y;

        $barcode_font_size = $generalConfig['font_size_barcode_order']; //16
        $left_down = 0;

        if ($generalConfig['barcode_type'] !== 'code128') {
            $barcode_font_size += 12;
            $left_down = 12;
        }

        $bottom_barcode_nudge[0] = trim((int)$bottom_barcode2_nudge[0]);
        $bottom_barcode_nudge[1] = trim((int)$bottom_barcode2_nudge[1]);

        $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($realOrderId, $generalConfig['barcode_type']);
        $barcodeWidth = 1.35 * $this->parseString($realOrderId, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
        $page->setFillColor($white_color);
        $page->setLineColor($white_color);
        $page->drawRectangle(($addressFooterXY[0] - 5 + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size) - 5 + $bottom_barcode_nudge[1]), ($addressFooterXY[0] + $barcodeWidth + 5 + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + ($barcode_font_size * 2.4) + $bottom_barcode_nudge[1] + 5));
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
        $this->_drawText($barcodeString, ($addressFooterXY[0] - $left_down + $bottom_barcode_nudge[0]), ($addressFooterXY[1] + $barcode_font_size - 12 - $left_down + $bottom_barcode_nudge[1] + 5), 'CP1252');
        $this->minY[] = $addressFooterXY[1] + ($barcode_font_size) - ($left_down / 4) + $bottom_barcode_nudge[1];
    }

    public function showAddress() {
        $this->line_height = 0;

        $page = $this->getPage();
        $wonder = $this->getWonder();
        $order = $this->getOrder();
        $helper = Mage::helper('pickpack');
        $realOrderId = $order->getRealOrderId();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();
        $packingsheetConfig = $this->getPackingsheetConfig($wonder, $storeId);
		$helper = Mage::helper('pickpack');
        $page_top = $pageConfig['page_top'];
        $padded_right = $pageConfig['padded_right'];
        $padded_left = $pageConfig['padded_left'];

        $white_color = Mage::helper('pickpack/config_color')->getPdfColor('white_color');
        $shipping_title = trim($this->_getConfig('shipping_title', '', false, $wonder, $storeId));
        $bottom_barcode_nudge = explode(",", $this->_getConfig('bottom_barcode_nudge', '0,0', true, $wonder, $storeId));
        $shipaddress_packbarcode2_yn = $this->_getConfig('shipaddress_packbarcode2_yn', 0, false, $wonder, $storeId);
        $bottom_barcode2_nudge = explode(",", $this->_getConfig('bottom_barcode2_nudge', '0,0', true, $wonder, $storeId));
        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
        $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;
        $billing_details_yn = $this->_getConfig('billing_details_yn', 0, false, $wonder, $storeId);
        $logo_position = $this->_getConfig('pickpack_logo_position', 'left', false, $wonder, $storeId);
        $address_countryskip = trim(strtolower($this->_getConfig('address_countryskip', 0, false, 'general', $storeId)));
        $billing_phone_yn = $this->_getConfig('billing_phone_yn', 0, false, $wonder, $storeId);
        $address_pad = explode(",", $this->_getConfig('address_pad', '0,0,0', true, $wonder, $storeId));
        $capitalize_label_yn = $this->_getConfig('capitalize_label_yn', 0, false, $wonder, $storeId);
        $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $wonder, $storeId));
        $billing_phone_yn_in_shipping_details = $this->_getConfig('billing_phone_yn_in_shipping_details', 0, false, $wonder, $storeId);
        $customer_phone_yn = $this->_getConfig('customer_phone_yn', 0, false, 'general', $storeId);
        $billing_tax_details_yn = $this->_getConfig('billing_tax_details_yn', '', false, $wonder, $storeId);
        $shipping_billing_title_position = $this->_getConfig('shipping_billing_title_position', 'above', false, $wonder, $storeId);
        $customer_email_yn = $this->_getConfig('customer_email_yn', 0, false, 'general', $storeId);
        $shipping_details_yn = $this->_getConfig('shipping_details_yn', 1, false, $wonder, $storeId);
        $page_template = $this->_getConfig('page_template', 0, false, $wonder, $storeId);
        $pickpack_headerbar_yn = trim($this->_getConfig('pickpack_headerbar_yn', '1', false, $wonder, $storeId));
        $address_pad_billing = explode(",", $this->_getConfig('address_pad_billing', '0,0,0', true, $wonder, $storeId));
        $font_style_shipping_billing_title = $this->_getConfig('font_style_shipping_billing_title', 'bold', false, 'general', $storeId);
        if($this->packingsheetConfig['pickpack_bottom_shipping_address_yn'] ==1)
            $tracking_number_yn = $this->_getConfig('tracking_number_yn', 1, false, $wonder, $storeId);
        $shipaddress_packbarcode_yn = $this->_getConfig('shipaddress_packbarcode_yn', 0, false, $wonder, $storeId);

        if (($shipping_billing_title_position == 'beside') && ($this->title_date_xpos < 100) && ($billing_details_yn == 1))
            $this->title_date_xpos = 350;

        $billing_details_position = $this->_getConfig('billing_details_position', 0, false, $wonder, $storeId);
        $billing_title = trim($this->_getConfig('billing_title', '', false, $wonder, $storeId));

        $this->float_top_address_yn = 0;
        if ($logo_position !== 'right')
            $this->float_top_address_yn = 0;

        if ($shipping_details_yn == 0)
            $shipping_title = null;
        if ($this->hasBillingAddress() === false)
            $billing_details_yn = 0;
        if ($billing_details_yn == 0) {
            $billing_details_position = 0;
            $billing_title = '';
        }
        // if billing address set to yes, shipping set to no, and billing address set to be right-side, show on left
        if (($billing_details_yn == 1) && ($shipping_details_yn == 0))
            $billing_details_position = 1;
        if (($billing_details_yn == 0) && ($shipping_details_yn == 1))
            $billing_details_position = 0;
        if (($shipping_billing_title_position == 'beside') && ($this->title_date_xpos < 100) && ($billing_details_yn == 1))
            $this->title_date_xpos = 350;

        $this->orderdetailsX = ($shipping_billing_title_position == 'beside' && $this->title_date_xpos != 'auto') ? $this->title_date_xpos : 304;

        if(isset($address_pad[0]))
			$address_pad[0] = ($address_pad[0] * -1);
		else
			$address_pad[0] = 0;
		
        if(isset($address_pad[1]))
			$address_pad[1] = ($address_pad[1] * -1);
		else
			$address_pad[1] = 0;
		
		if(!isset($address_pad[2]))
			$address_pad[2] = 0;

        if ($this->hasBillingAddress() === false) {
            $billing_details_yn = 0;
            $billing_tax_details_yn = 0;
            $billing_details_position = 0;
            $billing_title = '';
        }

        /*************************** PRINTING SHIPPING AND BILLING ADDRESS *******************************/
        $customer_email = '';
        $customer_company = '';
        $customer_firstname = '';
        $customer_lastname = '';
        $customer_name = '';
        $customer_city = '';
        $customer_postcode = '';
        $customer_region = '';
        $customer_region_code = '';
        $customer_prefix = '';
        $customer_suffix = '';
        $customer_country = '';
        $customer_street1 = '';
        $customer_street2 = '';
        $customer_street3 = '';
        $customer_street4 = '';
        $customer_street5 = '';
        $customer_street6 = '';
        $customer_street7 = '';
        $customer_street8 = '';
        $billing_taxvat = '';

        if ($this->hasShippingAddress() !== false) {
            if ($order->getShippingAddress()->getFax())
                $customer_fax = trim($order->getShippingAddress()->getFax());
            if($billing_phone_yn_in_shipping_details && !$billing_details_yn)
                $this->customer_phone = trim($order->getBillingAddress()->getTelephone());
            elseif ($order->getShippingAddress()->getTelephone())
                $this->customer_phone = trim($order->getShippingAddress()->getTelephone());

            if ($order->getShippingAddress()->getCompany()) $customer_company = trim($order->getShippingAddress()->getCompany());
            if ($order->getShippingAddress()->getName()) $customer_name = trim($order->getShippingAddress()->getName());
            if ($order->getShippingAddress()->getFirstname()) $customer_firstname = trim($order->getShippingAddress()->getFirstname());
            if ($order->getShippingAddress()->getMiddlename()) $customer_middlename = trim($order->getShippingAddress()->getMiddlename());
            if ($order->getShippingAddress()->getLastname()) $customer_lastname = trim($order->getShippingAddress()->getLastname());
            if ($order->getShippingAddress()->getCity()) $customer_city = trim($order->getShippingAddress()->getCity());
            if ($order->getShippingAddress()->getPostcode()) $customer_postcode = trim(strtoupper($order->getShippingAddress()->getPostcode()));
            if ($order->getShippingAddress()->getRegion()) $customer_region = trim($order->getShippingAddress()->getRegion());
            if ($order->getShippingAddress()->getRegionCode()) $customer_region_code = trim($order->getShippingAddress()->getRegionCode());
            if ($order->getShippingAddress()->getPrefix()) $customer_prefix = trim($order->getShippingAddress()->getPrefix());
            if ($order->getShippingAddress()->getSuffix()) $customer_suffix = trim($order->getShippingAddress()->getSuffix());
            if ($order->getShippingAddress()->getStreet1()) $customer_street1 = trim($order->getShippingAddress()->getStreet1());
            if ($order->getShippingAddress()->getStreet2()) $customer_street2 = trim($order->getShippingAddress()->getStreet2());
            if ($order->getShippingAddress()->getStreet3()) $customer_street3 = trim($order->getShippingAddress()->getStreet3());
            if ($order->getShippingAddress()->getStreet4()) $customer_street4 = trim($order->getShippingAddress()->getStreet4());
            if ($order->getShippingAddress()->getStreet5()) $customer_street5 = trim($order->getShippingAddress()->getStreet5());
            if ($order->getShippingAddress()->getStreet5()) $customer_street6 = trim($order->getShippingAddress()->getStreet6());
            if ($order->getShippingAddress()->getStreet5()) $customer_street7 = trim($order->getShippingAddress()->getStreet7());
            if ($order->getShippingAddress()->getStreet5()) $customer_street8 = trim($order->getShippingAddress()->getStreet8());

            if (Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()))
                $customer_country = trim(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()));
        }
        if ($order->getCustomerEmail())
            $customer_email = trim($order->getCustomerEmail());

        $billing_email = '';
        $billing_phone = '';
        $billing_company = '';
        $billing_name = '';
        $billing_firstname = '';
        $billing_lastname = '';
        $billing_city = '';
        $billing_postcode = '';
        $billing_region = '';
        $billing_region_code = '';
        $billing_prefix = '';
        $billing_suffix = '';
        $billing_country = '';
        $billing_street1 = '';
        $billing_street2 = '';
        $billing_street3 = '';
        $billing_street4 = '';
        $billing_street5 = '';

        if ($billing_details_yn == 1) {
            $billingaddress = $order->getBillingAddress();
            if ($billing_tax_details_yn == 1) {
                if ($billingaddress->getData('vat_id')) {
                    $billing_tax_details_title = $this->_getConfig('billing_tax_details_title', '', false, $wonder, $storeId); //no trim so can be positioned
                    $billing_taxvat = $billing_tax_details_title . ' ' . trim($billingaddress->getData('vat_id'));
                }
            }

            $billing_middlename = '';
            if ($billingaddress->getTelephone()) $billing_phone = trim($billingaddress->getTelephone());
            if ($billingaddress->getCompany()) $billing_company = trim($billingaddress->getCompany());
            if ($billingaddress->getName()) $billing_name = trim($billingaddress->getName());
            if ($billingaddress->getFirstname()) $billing_firstname = trim($billingaddress->getFirstname());
            if ($billingaddress->getMiddlename()) $billing_middlename = trim($billingaddress->getMiddlename());
            if ($billingaddress->getLastname()) $billing_lastname = trim($billingaddress->getLastname());
            if ($billingaddress->getCity()) $billing_city = trim($billingaddress->getCity());
            if ($billingaddress->getPostcode()) $billing_postcode = trim(strtoupper($billingaddress->getPostcode()));
            if ($billingaddress->getRegion()) $billing_region = trim($billingaddress->getRegion());
            if ($billingaddress->getRegionCode()) $billing_region_code = trim($billingaddress->getRegionCode());
            if ($billingaddress->getPrefix()) $billing_prefix = trim($billingaddress->getPrefix());
            if ($billingaddress->getSuffix()) $billing_suffix = trim($billingaddress->getSuffix());
            if ($billingaddress->getStreet1()) $billing_street1 = trim($billingaddress->getStreet1());
            if ($billingaddress->getStreet2()) $billing_street2 = trim($billingaddress->getStreet2());
            if ($billingaddress->getStreet3()) $billing_street3 = trim($billingaddress->getStreet3());
            if ($billingaddress->getStreet4()) $billing_street4 = trim($billingaddress->getStreet4());
            if ($billingaddress->getStreet5()) $billing_street5 = trim($billingaddress->getStreet5());
            if ($countryTranslation = Mage::app()->getLocale()->getCountryTranslation($billingaddress->getCountryId()))
                $billing_country = trim($countryTranslation);

            $billing_address = array();
            $if_contents = array();
            $billing_address['street'] = '';
            $billing_address['street1'] = $billing_street1;
            $billing_address['street2'] = $billing_street2;
            $billing_address['street3'] = $billing_street3;
            $billing_address['street4'] = $billing_street4;
            $billing_address['street5'] = $billing_street5;
            $billing_address['company'] = $billing_company;
            $billing_address['name'] = $billing_name;
            $billing_address['firstname'] = $billing_firstname;
            $billing_address['middlename'] = $billing_middlename;
            $billing_address['lastname'] = $billing_lastname;
            $billing_address['name'] = $billing_name;
            $billing_address['name'] = trim(preg_replace('~^' . $billing_address['company'] . '~i', '', $billing_address['name']));
            $billing_address['city'] = $billing_city;
            $billing_address['postcode'] = $billing_postcode;
            $billing_address['region_full'] = $billing_region;
            $billing_address['region_code'] = $billing_region_code;

            if ($billing_region_code != '')
                $billing_address['region'] = $billing_region_code;
            else
                $billing_address['region'] = $billing_region;

            $billing_address['prefix'] = $billing_prefix;
            $billing_address['suffix'] = $billing_suffix;
            $billing_address['country'] = $billing_country;
            if ($address_countryskip != '') {
                $address_billing_countryskip = array();
                foreach( explode(',',$address_countryskip) as $skip_country ){
                    if ($skip_country == 'usa' || $skip_country == 'united states' || $skip_country == 'united states of america') {
                        $address_billing_countryskip = array('usa', 'united states of america', 'united states');
                        break;
                    }
                    if( strtolower($skip_country) == strtolower($billing_address['country']) ){
                        $address_billing_countryskip = array($skip_country);
                        break;
                    }
                    /*TODO filter city if country = singapore or monaco*/
                    if (!is_array($skip_country) && (strtolower($skip_country) == "singapore" || strtolower($skip_country) == "monaco"))
                        $billing_address['city'] = str_ireplace($skip_country, '', $billing_address['city']);
                }
                $billing_address['country'] = str_ireplace($address_billing_countryskip, '', $billing_address['country']);
            }
            $i = 0;
            while ($i < 10) {
                if ($order->getBillingAddress()->getStreet($i) && !is_array($order->getBillingAddress()->getStreet($i))) {
                    $value = trim($order->getBillingAddress()->getStreet($i));
                    $this->max_chars = 20;
                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $font_size_compare = ($generalConfig['font_size_body'] * 0.8);
                    $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                    $char_width = $line_width / 10;
                    $this->max_chars = round(($this->orderdetailsX - 160) / $char_width);
                    // wordwrap characters
                    $value = wordwrap($value, $this->max_chars, "\n", false);
                    $token = strtok($value, "\n");
                    while ($token != false) {
                        if (trim(str_replace(',', '', $token)) != '')
                            $billing_address['street'] .= trim($token) . "\n";
                        $token = strtok("\n");
                    }
                }
                $i++;
            }

            $address_format_set = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $this->getAddressFormat());
            $address_format_set = $this->getArrayShippingAddress($billing_address, $capitalize_label_yn, $address_format_set);

            if ($billing_tax_details_yn == 1 && $billing_taxvat != '') $address_format_set .= '|||||' . $billing_taxvat;
            $address_format_set = trim(str_replace(array('||', '|'), "\n", trim($address_format_set)));
            $address_format_set = str_replace("\n\n", "\n", $address_format_set);

            $this->billingAddressArray = explode("\n", $address_format_set);

            if($billing_phone_yn == 1)
                array_push($this->billingAddressArray, ($helper->__('T: ') . $billing_phone));

            $billing_line_count = (count($this->billingAddressArray) - 1);
        }

        $shipping_address = array();
        $if_contents = array();
        $shipping_address['company'] = $customer_company;
        $shipping_address['firstname'] = $customer_firstname;
        if (isset($customer_middlename) && (strlen($customer_middlename) > 0))
            $shipping_address['middlename'] = $customer_middlename;
        else
            $shipping_address['middlename'] = '';
        $shipping_address['lastname'] = $customer_lastname;
        $shipping_address['name'] = $customer_name;
        $shipping_address['name'] = trim(preg_replace('~^' . $shipping_address['company'] . '~i', '', $shipping_address['name']));
        $shipping_address['city'] = $customer_city;
        $shipping_address['postcode'] = $customer_postcode;
        $shipping_address['region_full'] = $customer_region;
        $shipping_address['region_code'] = $customer_region_code;
        if ($customer_region_code != '')
            $shipping_address['region'] = $customer_region_code;
        else
            $shipping_address['region'] = $customer_region;
        $shipping_address['prefix'] = $customer_prefix;
        $shipping_address['suffix'] = $customer_suffix;
        $shipping_address['country'] = $customer_country;
        $shipping_address['street'] = '';
        $shipping_address['street1'] = $customer_street1;
        $shipping_address['street2'] = $customer_street2;
        $shipping_address['street3'] = $customer_street3;
        $shipping_address['street4'] = $customer_street4;
        $shipping_address['street5'] = $customer_street5;
        $shipping_address['street6'] = $customer_street6;
        $shipping_address['street7'] = $customer_street7;
        $shipping_address['street8'] = $customer_street8;


        if ($address_countryskip != '') {
            $address_shipping_countryskip = array();
            foreach( explode(',',$address_countryskip) as $skip_country ){
                if ($skip_country == 'usa' || $skip_country == 'united states' || $skip_country == 'united states of america') {
                    $address_shipping_countryskip = array('usa', 'united states of america', 'united states');
                    break;
                }

                if( strtolower($skip_country) == strtolower($shipping_address['country']) ){
                    $address_shipping_countryskip = array($skip_country);
                    break;
                }
                /*TODO filter city if country = singapore or monaco*/
                if ($skip_country == "singapore" || $skip_country == "monaco") {
                    $shipping_address['city'] = str_ireplace($skip_country, '', $shipping_address['city']);
                    break;
                }
            }
            $shipping_address['country'] = str_ireplace($address_shipping_countryskip, '', $shipping_address['country']);
        }

        if ($this->hasShippingAddress() !== false) {
            $i = 0;
            while ($i < 10) {
                if ($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i))) {
                    $value = trim($order->getShippingAddress()->getStreet($i));

                    $this->max_chars = 20;
                    $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                    $font_size_compare = ($generalConfig['font_size_body'] * 0.8);
                    $line_width = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                    $char_width = $line_width / 10;
                    $this->max_chars = round(($this->orderdetailsX - 160) / $char_width);
                    // wordwrap characters
                    $value = wordwrap($value, $this->max_chars, "\n", false);
                    $token = strtok($value, "\n");
                    while ($token !== false) {
                        if (trim(str_replace(',', '', $token)) != '')
                            $shipping_address['street'] .= trim($token) . "\n";
                        $token = strtok("\n");
                    }
                }
                $i++;
            }
        }

        $address_format_set = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $this->getAddressFormat());
        $address_format_set_2 = str_replace(array("\n", '<br />', '<br/>', "\r"), '', $this->getAddressFormat());
        if (strpos($order->getData('shipping_method'),'storepickup') !== false){
            $address_format_set = '{if name}{name},|{/if}';
            $address_format_set_2 = '{if name}{name},|{/if}';
        }
        $address_format_set = $this->getArrayShippingAddress($shipping_address, $capitalize_label_yn, $address_format_set);
        $address_format_set_2 = $this->getArrayShippingAddress($shipping_address, isset($this->packingsheetConfig['capitalize_label2_yn']) ? $this->packingsheetConfig['capitalize_label2_yn'] : false, $address_format_set_2);//fro bottom shipping address

        $this->shippingAddressArray = explode("\n", $address_format_set);
        $this->shippingAddressArrayBottom = explode("\n", $address_format_set_2);
        $last_line_index = count($this->shippingAddressArrayBottom);
        $last_line_index_top = count($this->shippingAddressArray);

        if (($customer_phone_yn != 'no') && ($this->customer_phone != '') && (strlen($this->customer_phone) > 5))
        {
            if($customer_phone_yn == 'yes' || $customer_phone_yn == 'yesdetails')
                array_push($this->shippingAddressArray, ($helper->__('T: ') . $this->customer_phone));
            if($customer_phone_yn == 'yes' || $customer_phone_yn == 'yeslabel')
                array_push($this->shippingAddressArrayBottom, ($helper->__('T: ') . $this->customer_phone));
        }

        if ($customer_email_yn != 'no' && $customer_email != '') {
            if($customer_phone_yn == 'yes' || $customer_phone_yn == 'yesdetails')
                array_push($this->shippingAddressArray, ($helper->__('E: ') . $customer_email));
            if (($customer_email_yn == 'yes' || $customer_email_yn == 'yeslabel') && ($shipping_details_yn == 1)) {
                array_push($this->shippingAddressArrayBottom, ($helper->__('E: ') . $customer_email));
            }

        }

        $count = (count($this->shippingAddressArray));
        $shipping_line_count = $count;
        if (isset($billing_line_count) && ($billing_line_count > $shipping_line_count) && ($shipping_line_count > 1))
            $shipping_line_count = $billing_line_count;

        $address_left_x = $pageConfig['addressX'];
        if ($this->float_top_address_yn == 0)
            $address_right_x = $this->orderdetailsX;
        $this->email_X = $address_left_x + $address_pad[2];

        if ($billing_details_position == 1 || $billing_details_position == 2) {
            if($billing_details_position != 2)
                $address_left_x = $this->orderdetailsX;
            $address_right_x = $pageConfig['addressX'];
        }
        if ($pickpack_headerbar_yn == 1)
            $this->address_top_y = ($this->headerBarXY[1] - $generalConfig['font_size_subtitles']/2 - $pageConfig['vertical_spacing'] + 3);
        else
            $this->address_top_y = $this->headerBarXY[1];

        //Dont need to move more for Top billing and shipping title.
        if ($shipping_title == '' && ($billing_details_yn == 0 || $billing_title == ''))
            $this->address_top_y -= 10;

        $this->top_y_left_column = $this->address_top_y;
        $this->top_y_right_column = $this->address_top_y;
        $this->address_title_left_x = $address_left_x;
        $this->address_title_right_x = $address_right_x;

        if ($shipping_billing_title_position == 'beside') {
            $address_left_x = ($padded_left + ((strlen($shipping_title)) * $generalConfig['font_size_subtitles'] * 0.5));
            $this->address_title_left_x = $padded_left;
            if ($page_template == '0')
                $this->address_title_left_x = $this->headerBarXY[0];

            $address_right_x = $this->orderdetailsX;
            $this->address_title_right_x = ($address_right_x - ((strlen($billing_title)) * $generalConfig['font_size_subtitles'] * 0.5));

            $address_left_x += 10;
            $this->email_X = $address_left_x;

            if ($billing_details_position == 1 || $billing_details_position == 2) {
                if($billing_details_position != 2)
                    $address_left_x = $this->orderdetailsX;
                $this->address_title_left_x = ($address_left_x - ((strlen($shipping_title)) * $generalConfig['font_size_subtitles'] * 0.5));

                if ($this->float_top_address_yn == 0)
                    $address_right_x = ($padded_left + ((strlen($billing_title)) * $generalConfig['font_size_subtitles'] * 0.5));
                $this->address_title_right_x = $padded_left;
                if ($page_template == '0')
                    $this->address_title_right_x = $this->headerBarXY[0];
            }
        }

        $this->_setFont($page, $font_style_shipping_billing_title, ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

        if ($shipping_title == '' && ($billing_details_yn == 0 || $billing_title == '') && ($this->float_top_address_yn == 0))
            $this->address_top_y = ($this->address_top_y + ($generalConfig['font_size_body'] + 2));
        elseif ($this->float_top_address_yn == 1) {
            $float_top_address_y = ($this->y - ($this->line_height - ($generalConfig['font_size_body'] * 2.5)));
            $this->address_top_y = $float_top_address_y;
            $this->address_title_right_x = $padded_left;
            $address_right_x = $padded_left;
        } else
            $this->address_top_y -= $generalConfig['font_size_body'];

        if($this->caseRotate > 0)
            $this->rotateLabel($this->caseRotate,$page,$page_top,$padded_right);

        /*************************** PRINTING TOP SHIPPING BACKGROUND BEHIND *******************************/

        $top_shipping_address_background_yn = $this->_getConfig('top_shipping_address_background_yn', 0, false, $wonder, $storeId);
        if ($top_shipping_address_background_yn){
            $scale = $this->_getConfig('top_shipping_address_background_yn_scale', 0, false, $wonder, $storeId);
            $this->showShippingAddresBackground($order, ($this->address_top_y + $address_pad[0]), $wonder, $storeId, $page, ($this->address_title_left_x + $address_pad[2]), $scale);
        }

        /*************************** END PRINTING TOP SHIPPING BACKGROUND BEHIND *******************************/

        $addon_billing_y_updown_title = 0;
        if($billing_details_position == 2)
            $addon_billing_y_updown_title = $shipping_line_count * $generalConfig['font_size_body'] + 80;

        if (($shipping_title != '') && ($shipping_line_count > 1))
            $this->_drawText($shipping_title, $this->address_title_left_x + $address_pad[2], $this->address_top_y  + $address_pad[0]);

        if (($billing_details_yn == 1) && ($billing_title != '') && ($this->hasBillingAddress() === true) && ($billing_line_count > 1))
            $this->_drawText($billing_title, $this->address_title_right_x + $address_pad_billing[2], $this->address_top_y - $addon_billing_y_updown_title + $address_pad_billing[0]);

        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] + 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

        $this->line_height = 0;
        $addressLine = '';
        $this->i_space = -0.5;
        if ($shipping_billing_title_position == 'beside')
            $this->i_space = -1;

        $show_this_billing_line = array();
        $show_this_shipping_line = array();
        $show_this_shipping_line_bottom = array();
        $skip = 0;
        $line_bold = 0;

        $show_this_shipping_line = $this->getAddressLines($this->shippingAddressArray, $show_this_shipping_line);
        $show_this_shipping_line_bottom = $this->getAddressLines($this->shippingAddressArrayBottom, $show_this_shipping_line_bottom);
        if (($billing_details_yn == 1) && ($this->hasBillingAddress() === true))
            $show_this_billing_line = $this->getAddressLines($this->billingAddressArray, $show_this_billing_line);
        $count_ship = (count($show_this_shipping_line));
        $count_bill = (count($show_this_billing_line));
        $shipping_line_count = $count_ship;
        $billing_line_count = $count_bill;
        if (isset($billing_line_count) && ($billing_line_count > $shipping_line_count) && ($shipping_line_count > 1))
            $shipping_line_count = $billing_line_count;
        /*************************** END SHIPPING AND BILLING ADDRESS *******************************/

        /***************************PRINTING BOTTOM SHIPPING ADDRESS BARCODE *******************************/
        if ($shipaddress_packbarcode_yn == 1 && $packingsheetConfig['pickpack_bottom_shipping_address_yn'] == 1)
            $this->showShippingAddressBarcode();

        if ($shipaddress_packbarcode2_yn == 1 && $packingsheetConfig['pickpack_bottom_shipping_address_yn'] == 1)
            $this->showShippingAddressBarcode2();

        /***************************PRINTING BOTTOM TRACKING NUMBER *******************************/
        if(isset($tracking_number_yn) && ($tracking_number_yn == 1)){
            $tracking_number_fontsize = $this->_getConfig('tracking_number_fontsize', 15, false, $wonder, $storeId);
            $tracking_number_nudge = explode(",", $this->_getConfig('tracking_number_nudge', '0,0', true, $wonder, $storeId));
            $this->_setFont($page, $generalConfig['font_style_body'], $tracking_number_fontsize, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            if(!isset($tracking_number_barcode_nudge))
                $tracking_number_barcode_nudge = array(0,0);
            $this->drawTrackingNumber($page,$order, $tracking_number_fontsize, $white_color, $addressFooterXY, $tracking_number_nudge, $tracking_number_barcode_nudge);
        }

        $this->shipping_address_flat = '';

        /***************************END PRINTING BOTTOM RETURN ADDRESS IMAGE ***************************/
        $this->_setFont($page, $generalConfig['font_style_subtitles'], ($this->fontSizeShippingAddress - 3), $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], '#444444');

        if($this->caseRotate > 0)
            $this->reRotateLabel($this->caseRotate,$page,$page_top,$padded_right);
        /***************************PRINTING START BILLING ADDRESS, SHIPPING ADDRESS TOP *******************************/
        $cycle_address_array = array();
        $cycle_address_array = $show_this_shipping_line;

        if (count($show_this_shipping_line) < count($show_this_billing_line)) $cycle_address_array = $show_this_billing_line;
        $line_bold_billing = 0;
        $line_bold = 0;
        $line_bold_bottom = 0;
        $bold_last_line_yn = $this->_getConfig('bold_address_format_yn', 0, false, "general", $storeId);
        $bold_last_line_yn_top = $this->_getConfig('bold_topaddress_format_yn', 0, false, "general", $storeId);
        //$addressFooterXY[1] += $generalConfig['font_size_body'];
        $this->i_space = -1;
        $this->address_top_y -= ($this->line_height_top + 2);

        foreach ($cycle_address_array as $i => $value) {
            if (isset($show_this_shipping_line[$i]))
                $value_shipping = trim($show_this_shipping_line[$i]);
            else
                $value_shipping = '';
            $value_shipping = ltrim($value_shipping, ",");
            $value_shipping = ltrim($value_shipping, ".");
            $value_shipping = trim($value_shipping);
            //New TODO update 1
            if($generalConfig['non_standard_characters'] == 0)
                $value_shipping = trim(Mage::helper('pickpack/functions')->clean_method($value_shipping, 'pdf'));
            $value_shipping = preg_replace('~, ,~', '', $value_shipping);
            if ($capitalize_label_yn == 1) {
                if (strtolower($customer_country) == 'united states')
                    $value_shipping = preg_replace('~,$~', '', $value_shipping);
                $value_shipping = ucfirst($value_shipping);
            } elseif ($capitalize_label_yn == 2)
                if (strtolower($customer_country) == 'united states')
                    $value_shipping = preg_replace('~,$~', '', $value_shipping);
            $value_billing = '';
            if (isset($show_this_billing_line[$i])) {
                $value_billing = trim($show_this_billing_line[$i]);
                $value_billing = ltrim($value_billing, ",");
                $value_billing = ltrim($value_billing, ".");
                $value_billing = trim($value_billing);
                //New TODO 2
                $value_billing = trim(Mage::helper('pickpack/functions')->clean_method($value_billing, 'pdf'));
                $value_billing = preg_replace('~, ,~', '', $value_billing);
                if ($capitalize_label_yn == 1) {
                    if (strtolower($customer_country) == 'united states')
                        $value_billing = preg_replace('~,$~', '', $value_billing);
                    $value_billing = ucfirst($value_billing);
                } elseif ($capitalize_label_yn == 2) {
                    if (strtolower($customer_country) == 'united states')
                        $value_billing = preg_replace('~,$~', '', $value_billing);
                }
            }

            if ($bold_last_line_yn == 1 && $i == ($last_line_index - 1) && ($address_countryskip != $value_shipping))
                $line_bold_bottom = 1;

            if ($bold_last_line_yn_top == 1) {
                if ($i == ($last_line_index_top - 1) && ($address_countryskip != $value_shipping)) {
                    $line_bold = 1;
                    $value_shipping = preg_replace('~,$~', '', $value_shipping);
                }
                if ($i == ($billing_line_count - 1) && ($address_countryskip != $value_billing)) {
                    $line_bold_billing = 1;
                    $value_billing = preg_replace('~,$~', '', $value_billing);
                }
            }

            if ( (($this->packingsheetConfig['pickpack_bottom_shipping_address_yn_xtra'] == 1) && ($show_this_shipping_line[$i] != ''))
                || ( ($this->packingsheetConfig['pickpack_bottom_shipping_address_yn_xtra'] == 2) && ($show_this_shipping_line[$i] != '') && (strpos($show_this_shipping_line[$i],'@')==false) )) {
                if ($this->shipping_address_flat != '')
                    $this->shipping_address_flat .= ', ';

                $this->shipping_address_flat .= $show_this_shipping_line[$i];
            }

            $this->i_space = ($this->i_space + 1);
            if($value == $helper->__('T: ') . $this->customer_phone || $value == $helper->__('T: ') . $billing_phone)
                $this->i_space = $this->i_space + 0.5;

            /**** PRINTING TOP BILLING AND SHIPPING ****/
            if ($shipping_billing_title_position != 'beside') {
                if (($shipping_details_yn == 1) && isset($show_this_shipping_line[$i])) {
                    if ($line_bold == 1) {
                        $this->_setFont($page, "bold", ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $line_bold = 0;
                    } else
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    //Print shipping and billing. (check when have titles and without titles)
                    if ($shipping_title != '')
                        $this->_drawText($value_shipping, ($address_left_x + $address_pad[2]), ($this->address_top_y - ($this->line_height_top * $this->i_space) - $this->line_addon + $address_pad[0]));
                    else
                        $this->_drawText($value_shipping, ($address_left_x + $address_pad[2]), (($this->address_top_y) - ($this->line_height_top * $this->i_space) - $this->line_addon + $address_pad[0]));
                }
                if (($billing_details_yn == 1) && isset($show_this_billing_line[$i])) {

                    if ($line_bold_billing == 1) {
                        $this->_setFont($page, "bold", ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $line_bold_billing = 0;
                    } else
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    if ($billing_title != '') {
                        $this->_drawText($value_billing, ($address_right_x + $address_pad_billing[2]), ($this->address_top_y - $addon_billing_y_updown_title - ($this->line_height_top * $this->i_space) - $this->line_addon + $address_pad_billing[0]));
                    } else
                        $this->_drawText($value_billing, ($address_right_x + $address_pad_billing[2]), (($this->address_top_y) - $addon_billing_y_updown_title - ($this->line_height_top * $this->i_space) - $this->line_addon + $address_pad_billing[0]));
                }
            }
            else {
                if (($shipping_details_yn == 1) && isset($value_shipping)) {
                    if ($line_bold == 1) {
                        $this->_setFont($page, "bold", ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $line_bold = 0;
                    } else
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($value_shipping, ($address_left_x + $address_pad[2]), ($this->address_top_y - ($this->line_height_top * $this->i_space) - $this->line_addon + $address_pad[0]));
                }

                if (($billing_details_yn == 1) && isset($show_this_billing_line[$i])) {
                    if ($line_bold_billing == 1) {
                        $this->_setFont($page, "bold", ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                        $line_bold_billing = 0;
                    } else
                        $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body']), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->_drawText($value_billing, ($address_right_x + $address_pad_billing[2]), ($this->address_top_y - $addon_billing_y_updown_title - ($this->line_height_top * $this->i_space) - $this->line_addon + $address_pad_billing[0]));
                }
            }
            /**** END PRINTING TOP BILLING AND SHIPPING ****/
        }

        /*************************** PRINTING BOTTOM SHIPPING BACKGROUND BEHIND *******************************/
        $shipping_address_background_yn = $this->_getConfig('shipping_address_background_yn', 0, false, $wonder, $storeId);
        if ($shipping_address_background_yn){
            $shipping_address_background_nudge = explode(',',$this->_getConfig('shipping_address_background_nudge', '0,0', false, $wonder, $storeId));
            $this->showShippingAddresBackground($order, $addressFooterXY[1]+$shipping_address_background_nudge[1]+self::BOTTOM_LABEL_IMAGE_DEFAULT_NUDGE_Y, $wonder, $storeId, $page, $addressFooterXY[0]+$shipping_address_background_nudge[0]+self::BOTTOM_LABEL_IMAGE_DEFAULT_NUDGE_X, 100);
            unset($shipping_address_background_nudge);
        }
        /*************************** END PRINTING BOTTOM SHIPPING BACKGROUND BEHIND *******************************/

        /***************************PRINTING BOTTOM ORDER ID ABOVE BOTTOM SHIPPING ADDRESS *******************************/
        $this->printBottomOrderId($order,$page,$page_top,$padded_right,$generalConfig['font_style_body'],$generalConfig['font_size_body'],$generalConfig['font_family_body'],$generalConfig['font_color_body'], $storeId);
        /***************************END PRINTING BOTTOM ORDER ID ABOVE BOTTOM SHIPPING ADDRESS *******************************/

        /***PRINTING BOTTOM SHIPPING ADDRESS***/
        if ($packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup')
            $font_size_shipaddress = isset($this->packingsheetConfig['pickpack_shipfont_group']) ? $this->packingsheetConfig['pickpack_shipfont_group'] : 14;
        else
            $font_size_shipaddress = isset($this->packingsheetConfig['pickpack_shipfont']) ? $this->packingsheetConfig['pickpack_shipfont'] : 14;

        foreach ($cycle_address_array as $i => $value) {
            $font_size_adjust = 0;
            if (($this->packingsheetConfig['pickpack_bottom_shipping_address_yn'] == 1) && isset($show_this_shipping_line_bottom[$i])) {
                $this->bottomOrderIdY = 0;
                if ($this->caseRotate > 0)
                    $this->rotateLabel($this->caseRotate, $page, $page_top, $padded_right);
                $value = trim($show_this_shipping_line_bottom[$i]);
                $value = ltrim($value, ",");
                $value = ltrim($value, ".");
                $value = trim($value);
                $value = trim(Mage::helper('pickpack/functions')->clean_method($value, 'pdf'));
                $value = preg_replace('~, ,~', '', $value);
                if ($this->packingsheetConfig['capitalize_label2_yn'] == 1) {
                    $value = mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');//ucfirst($value);
                    if (strtolower($customer_country) == 'united states')
						$value = preg_replace('~,$~', '', $value);
                    $font_size_adjust = 2;
                } elseif ($this->packingsheetConfig['capitalize_label2_yn'] == 2) {
                    $value = mb_convert_case($value, MB_CASE_UPPER, 'UTF-8');
                    if (strtolower($customer_country) == 'united states')
						$value = preg_replace('~,$~', '', $value);
                    $font_size_adjust = 2;
                }

                if ($line_bold_bottom == 1 && $i == ($last_line_index - 1) && ($address_countryskip != $value_shipping)) {
                    $this->_setFont($page, 'bold', ($font_size_shipaddress + 2 - $font_size_adjust), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $this->line_addon = ($font_size_shipaddress * 0.1);
                    $line_bold_bottom = 0;
                } else
                    $this->_setFont($page, $generalConfig['font_style_body'], ($font_size_shipaddress - $font_size_adjust), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                $this->string_2nd_shipping_address .= trim($value, ",") . ",";

                $this->bottom_shipping_address_pos = array();
                $this->bottom_shipping_address_pos['x'] = $addressFooterXY[0];
                $this->bottom_shipping_address_pos['y'] = $addressFooterXY[1];
                $this->bottom_shipping_address_pos = preg_replace('~[^.0-9]~', '', $this->bottom_shipping_address_pos);
                if (trim($this->bottom_shipping_address_pos['x']) == '')
                    $this->bottom_shipping_address_pos['x'] = 0;
                if (trim($this->bottom_shipping_address_pos['y']) == '')
                    $this->bottom_shipping_address_pos['y'] = 0;

                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $font_size_temp = $font_size_shipaddress - $font_size_adjust;
                $line_width = $this->parseString('1234567890', $font_temp, $font_size_temp);
                $bottom_shipping_address_max_points = $this->_getConfig('pickpack_shipaddress_maxpoints', 250, false, $this->_wonder, $storeId);
                $char_width_shipping_bottom = $line_width / 11;
                $max_chars_shipping_bottom = round($bottom_shipping_address_max_points / $char_width_shipping_bottom);
                $multiline_shipping_bottom = wordwrap($value, $max_chars_shipping_bottom, "\n");

                $token = strtok($multiline_shipping_bottom, "\n");
                $multiline_shipping_bottom_array = array();

                if ($token != false) {
                    while ($token != false) {
                        $multiline_shipping_bottom_array[] = $token;
                        $token = strtok("\n");
                    }

                    foreach ($multiline_shipping_bottom_array as $shipping_in_line) {
                        if ($this->bottom_ispace == 0)
                            $this->bottom_ispace++;
                        $this->bottom_ispace++;
                        $this->bottom_shipping_address_pos['y'] = ($addressFooterXY[1] - ($this->line_height_bottom * $this->bottom_ispace) - $this->line_addon);
                        $this->_drawText($shipping_in_line, $this->bottom_shipping_address_pos['x'], $this->bottom_shipping_address_pos['y']);
                    }
                } else {
                    if ($this->bottom_ispace == 0)
                        $this->bottom_ispace++;
                    $this->bottom_shipping_address_pos['y'] = ($addressFooterXY[1] - ($this->line_height_bottom * $this->bottom_ispace) - $this->line_addon);
                    $this->_drawText($value, $this->bottom_shipping_address_pos['x'], $this->bottom_shipping_address_pos['y']);
                }
                $this->bottomOrderIdY = ($addressFooterXY[1] - ($this->line_height_bottom * ($this->bottom_ispace + 1)) - $this->line_addon);
                if ($this->caseRotate > 0)
                    $this->reRotateLabel($this->caseRotate, $page, $page_top, $padded_right);
            }
        }
        /***END PRINTING BOTTOM SHIPPING ADDRESS***/
        return $this->address_top_y;
    }

    protected function showShippingAddresBackground($order, $page_top, $wonder = "", $storeId, $page, $padded_left, $scale = 100, $label_width = 250, $nudge_shipping_addressX = 0, $resolution = null) {
        $shipping_address_background = $this->_getConfig('shipping_address_background_shippingmethod', '', false, 'image_background', $storeId);
        if(strlen(trim($shipping_address_background)) == 0)
            return;
        try {
            $shipping_address_background = unserialize($shipping_address_background);
            if($shipping_address_background == false)
                $shipping_address_background = $this->getConfigValue2('shipping_address_background_shippingmethod');

            $shipping_address_background = $this->checkCourrierrulesAndM2epro($shipping_address_background);
        } catch (Exception $e) {
            return;
        }
        $top_or_bottom = $page_top;
        $this->printShippingAddressBackground($order, $scale, $shipping_address_background, $top_or_bottom, $page, $padded_left, $label_width = 250, $nudge_shipping_addressX = 0, $resolution);
    }

    protected function printShippingAddressBackground($order, $scale, $shipping_address_background, $page_top_or_bottom, $page, $padded_left, $label_width = 0, $nudge_shipping_addressX = 0,$resolution,$image_zebra=null) {
        require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';
        $image_simple = new SimpleImage();
        $print_row = $this->getShippingAddressMaxPriority($order, $shipping_address_background);
        if ((($print_row != -1))) {
            $image_file_name = Mage::getBaseDir('media') . '/moogento/pickpack/image_background/' . $shipping_address_background[$print_row]['file'];
            if ($image_file_name) {
                $image_part                  = explode('.', $image_file_name);
                $image_ext                   = array_pop($image_part);
                $shipping_background_nudge_x = $shipping_address_background[$print_row]['xnudge'];
                $shipping_background_nudge_y = $shipping_address_background[$print_row]['ynudge'];


                if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($image_file_name))) {
                    $logo_shipping_maxdimensions[0] = $label_width - $nudge_shipping_addressX;
                    $logo_shipping_maxdimensions[1] = 300;

                    $imageObj        = Mage::helper('pickpack')->getImageObj($image_file_name);

                    $orig_img_width  = $imageObj->getOriginalWidth();
                    $orig_img_height = $imageObj->getOriginalHeight();

                    $img_height = $imageObj->getOriginalHeight();
                    $img_width  = $imageObj->getOriginalWidth();
                    if ($orig_img_width > ($logo_shipping_maxdimensions[0])) {
                        $img_height = ceil(($logo_shipping_maxdimensions[0] / $orig_img_width) * $orig_img_height);
                        $img_width  = $logo_shipping_maxdimensions[0];
                    }
                    if(isset($image_simple))
                    {
                        //Create new temp image
                        $final_image_path2 = $image_file_name;//$media_path . '/' . $image_url_after_media_path;
                        $image_source = $final_image_path2;
                        $io = new Varien_Io_File();
                        $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');

                        $img_width1 = intval($img_width*300/72);
                        $img_height1 = intval($img_height*300/72);

                        $filename = pathinfo($image_source, PATHINFO_FILENAME)."_".$img_width1."X".$img_height1.".jpeg";
                        $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$filename;

                        if(!(file_exists($image_target))){
                            $image_simple->load($image_source);
                            $image_simple->resize($img_width1,$img_height1);
                            $image_simple->save($image_target, IMAGETYPE_JPEG, 100);
                        }
                        $image_file_name = $image_target;
                    }
                    $image = Zend_Pdf_Image::imageWithPath($image_file_name);
                    $x1 = $padded_left + $shipping_background_nudge_x + $nudge_shipping_addressX;
                    $y1 = $page_top_or_bottom - $img_height + $shipping_background_nudge_y;
                    $x2 = $padded_left + $img_width + $shipping_background_nudge_x + $nudge_shipping_addressX;
                    $y2 = $page_top_or_bottom + $shipping_background_nudge_y;
                    if($scale && is_numeric($scale) && $scale!= 100){
                        if($scale < 100){
                            $y1 =  $y1+(($y2-$y1)*$scale/100);
                            $x2 =  $x2-(($x2-$x1)*$scale/100);
                        }
                        else{
                            $y1 =  $y1-(($y2-$y1)*($scale-100)/100);
                            $x2 =  $x2+(($x2-$x1)*($scale-100)/100);
                        }
                    }
                    $page->drawImage($image, $x1 ,$y1 , $x2, $y2);
                }
            }
        }
        unset($image_zebra);
    }

    public function parseString($string, $font = null, $fontsize = null) {
        return Mage::helper('pickpack/font')->parseString($string, $font, $fontsize);
    }

    public function printBottomOrderId($order, $page, $page_top, $padded_right, $font_style_body, $font_size_body, $font_family_body, $font_color_body, $storeId) {
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();

        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
        $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;
        $non_standard_characters = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $wonder, $storeId));
        $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $wonder, $storeId));
        $bottom_shipping_address_id_yn = $this->_getConfig('pickpack_bottom_shipping_address_id_yn', 0, false, $wonder, $storeId);

        if (($this->packingsheetConfig['pickpack_bottom_shipping_address_yn'] == 1) && ($bottom_shipping_address_id_yn == 1)) {
            if($this->caseRotate > 0)
                $this->rotateLabel($this->caseRotate,$page,$page_top,$padded_right);
            $this->_setFont($page, $font_style_body, ($font_size_body + 0.5), $font_family_body, $non_standard_characters, $font_color_body);
            $bottom_order_id_nudge = explode(",", $this->_getConfig('pickpack_nudge_id_bottom_shipping_address', '0, 0', true, $wonder, $storeId));
            if (!isset($bottom_order_id_nudge[1]))
                $bottom_order_id_nudge[1] = 0;
            $bottom_order_id_nudge[0] += self::BOTTOM_LABEL_ORDERID_DEFAULT_NUDGE_X;
            $bottom_order_id_nudge[1] += self::BOTTOM_LABEL_ORDERID_DEFAULT_NUDGE_Y;
            $this->_drawText('#' . $order->getRealOrderId(), $addressFooterXY[0] + $bottom_order_id_nudge[0], $bottom_order_id_nudge[1] + $addressFooterXY[1]);
            $this->minY[] = ($addressFooterXY[1] + ($this->fontSizeShippingAddress)) - 7;
            unset($bottom_order_id_nudge);
            if($this->caseRotate > 0)
                $this->reRotateLabel($this->caseRotate,$page,$page_top,$padded_right);
        }
    }

    protected function getArrayShippingAddress($shipping_address, $capitalize_label_yn, $address_format_set) {
        $if_contents = array();
        foreach ($shipping_address as $key => $value) {
            $value = trim($value);
            if (($capitalize_label_yn == 1) && ($key != 'postcode') && ($key != 'region_code') && ($key != 'region'))
                $value = $this->reformatAddress($value,'uppercase');
            elseif ($capitalize_label_yn == 2)
                $value = $this->reformatAddress($value,'capitals');

            $value = str_replace(array(',,', ', ,', ', ,'), ',', $value);
            $value = str_replace(array('N/a', 'n/a', 'N/A'), '', $value);
            $value = trim(preg_replace('~\-$~', '', $value));

            //applied all-capitals in format address string only for COUNTRY and REGION
            if ($key == "region" || $key == "country"){
                $str = preg_replace("/{\/if}(.*)/", "", $address_format_set);
                $str = preg_replace("/({if ".$key."}|\|)/", "", $str);
                $str = preg_replace("/((.*)\{|\}(.*))/", "", $str);
                if ($this->reformatAddress($key,'capitals') == $str){
                    $value = $this->reformatAddress($value,'capitals');
                }
            }


            //check key in format address string
            $string_key_check = '{if ' . $key . '}';
            $key_flag = strpos($address_format_set, $string_key_check);
            $search = array($string_key_check, '{/if}');
            $replace = array('', '');
            if ($key_flag !== FALSE)
                $address_format_set = str_replace($search, $replace, $address_format_set);
            // end check key in format address string

            if ($value != '' && !is_array($value)) {
                $pre_value = '';
                preg_match('~\{if ' . $key . '\}(.*)\{\/if ' . $key . '\}~ims', $address_format_set, $if_contents);

                if (isset($if_contents[1]))
                    $if_contents[1] = str_replace('{' . $key . '}', $value, $if_contents[1]);
                else $if_contents[1] = '';

                $address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~ims', $if_contents[1], $address_format_set);
                $address_format_set = str_ireplace('{' . $key . '}', $pre_value . $value, $address_format_set);
                $address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
                $address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
                $address_format_set = str_ireplace('{/if ' . '}', '', $address_format_set);
            } else {
                $pre_value = '';
                $address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~i', '', $address_format_set);
                $address_format_set = str_replace('{' . $key . '}', '', $address_format_set);
                $address_format_set = str_ireplace('{' . $key . '}', $pre_value . $value, $address_format_set);
                $address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
                $address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
                $address_format_set = str_ireplace('{/if ' . '}', '', $address_format_set);
                //$address_format_set = str_ireplace(', ', '', $address_format_set);
            }

            $from_date = "{if telephone}";
            $end_date = "{telephone}";
            $from_date_pos = strpos($address_format_set, $from_date);
            if ($from_date_pos !== false) {
                $end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
                $date_length = $end_date_pos - $from_date_pos;
                $date_str = substr($address_format_set, $from_date_pos, $date_length);
                $address_format_set = str_replace($date_str, '', $address_format_set);
            }

            $from_date = "{if fax}";
            $end_date = "{fax}";
            $from_date_pos = strpos($address_format_set, $from_date);
            if ($from_date_pos !== false) {
                $end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
                $date_length = $end_date_pos - $from_date_pos;
                $date_str = substr($address_format_set, $from_date_pos, $date_length);
                $address_format_set = str_replace($date_str, '', $address_format_set);
            }

            $from_date = "{if vat_id}";
            $end_date = "{vat_id}";
            $from_date_pos = strpos($address_format_set, $from_date);
            if ($from_date_pos !== false) {
                $end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
                $date_length = $end_date_pos - $from_date_pos;
                $date_str = substr($address_format_set, $from_date_pos, $date_length);
                $address_format_set = str_replace($date_str, '', $address_format_set);
            }
        }
        $address_format_set = trim(str_replace(array('||', '|'), "\n", trim($address_format_set)));
        $address_format_set = str_replace("\n\n", "\n", $address_format_set);
        $address_format_set = str_replace("  ", " ", $address_format_set);
        $address_format_set = trim(ltrim($address_format_set,','));
        return $address_format_set;
    }

    private function drawTrackingNumber($page,$order, $tracking_number_fontsize, $white_color, $addressFooterXY, $tracking_number_nudge, $tracking_number_barcode_nudge) {
        $tracking_number = $this->getTrackingNumber($order);
        if($tracking_number != '')
            $this->_drawText($tracking_number, ($addressFooterXY[0] + $tracking_number_nudge[0] + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] + $tracking_number_nudge[1]+ $tracking_number_barcode_nudge[1] - $tracking_number_fontsize), 'CP1252');
    }

    protected function getConfigValue2($path) {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName4 = $resource->getTableName('core_config_data');
        $query = 'SELECT * FROM '.$tableName4.' WHERE path like "%'.$path.'%"'.' LIMIT 1';
        $data  = $readConnection->fetchAll($query);
        $config_value = $data[0]['value'];
        try {
            $shipping_address_background = unserialize($config_value);
            return $shipping_address_background;
        }
        catch (Exception $e) {
            return '';
        }
    }

    private function checkPayment($paymentInfo) {
        $is_payment_code = false;
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        $payment_test = implode(',', $payment);
        $payment_test = strtolower($payment_test);
        $payments = array('Credit Card', 'American Express', 'Master Card', 'Cash on Delivery', 'Purchase Order Purchase Order', 'Payment Visa', 'Payment Mastercard', 'Mastercard#', 'MasterCard', 'Pay with Paypal');
        foreach ($payments as $value) {
            if (strpos($payment_test, strtolower($value)) !== false) {
                $is_payment_code = true;
                return $is_payment_code;
            }
        }
        return $is_payment_code;
    }

    private function cleanPaymentFull($paymentInfo) {
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        $payment_test = implode(',', $payment);
        $payment_test = trim(str_ireplace(
            array('Credit or Debit Card'),
            array('Card'), $payment_test));
        $payment_test = preg_replace('~^\s*~', '', $payment_test);
        $payment_test = trim(preg_replace('~^:~', '', $payment_test));
        $payment_test = preg_replace('~Paypal(.*)$~i', 'Paypal', $payment_test);
        $payment_test = preg_replace('~Account(.*)$~i', 'Account', $payment_test);
        $payment_test = preg_replace('~Processed Amount(.*)$~i', '', $payment_test);
        $payment_test = preg_replace('~Payer Email(.*)$~i', '', $payment_test);
        $payment_test = preg_replace('~Charge:$~i', '', $payment_test);
        $payment_test = str_ireplace('Expiration', '|Expiration', $payment_test);
        $payment_test = str_ireplace('Name on the Card', '|Name on the Card', $payment_test);
        $payment_test = preg_replace('~^\-~', '', $payment_test);
        $payment_test = preg_replace('~Check / Money order(.*)$~i', 'Check / Money order', $payment_test);
        $payment_test = preg_replace('~Cheque / Money order(.*)$~i', 'Cheque / Money order', $payment_test);
        $payment_test = preg_replace('~Make cheque payable(.*)$~i', '', $payment_test);
        $payment_test = str_ireplace(
            array('CardCC', 'CC Type', 'MasterCardCC', 'MasterCC', ': MC', ': Visa', 'Payment Visa', 'Payment MC', 'CCAmex', 'AmexCC', 'Type: Amex', 'CC Exp.', 'CC (Sage Pay)CC'),
            array('CC', 'CC, Type', 'MC', 'MC', ' MC', ' Visa', 'Visa', 'MC', 'Amex', 'Amex', 'Amex', 'Exp.', '(Sage Pay)'), $payment_test);
        $payment_test = preg_replace('~:$~', '', $payment_test);

        preg_match('~\b(?:\d[ -]*?){13,16}\b~', $payment_test, $cc_matches);
        if (isset($cc_matches[0])) {
            $replacement_cc = str_pad(substr($cc_matches[0], -4), 8, '*', STR_PAD_LEFT);
            $payment_test = str_replace($cc_matches[0], $replacement_cc, $payment_test);
        }

        $payment_test = trim($payment_test);
        return $payment_test;
    }

    public function  getPageFooterHeight(){
        $footerY = 0;
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
        $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;


        if($this->_getConfig('pickpack_bottom_shipping_address_yn',0, false, $wonder, $storeId)) {
            $footerXY = explode(",", $this->_getConfig('pickpack_shipaddress', $pageConfig['addressFooterXYDefault'], true, $wonder, $storeId));
            $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
            $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;
            $footerY = $footerXY[1];


            if($this->_getConfig('pickpack_second_bottom_shipping_address_yn',0, false, $wonder, $storeId)) {
                $footerXY = explode(",", $this->_getConfig('pickpack_second_shipaddress', $pageConfig['addressFooterXYDefault'], true, $wonder, $storeId));
                if($footerXY[1] > $footerY){
                    $footerY = $footerXY[1];
                }
            }

            if($this->_getConfig('pickpack_bottom_shipping_address_id_yn',0, false, $wonder, $storeId)) {
                $footerXY = explode(",", $this->_getConfig('pickpack_nudge_id_bottom_shipping_address', '0, 0', true, $wonder, $storeId));
                $footerXY[1] = $addressFooterXY[1] + $footerXY[1];
                if($footerXY[1] > $footerY){
                    $footerY = $footerXY[1];
                }
            }

            if($this->_getConfig('shipaddress_packbarcode_yn',0, false, $wonder, $storeId)) {
                $footerXY = explode(",", $this->_getConfig('bottom_barcode_nudge', '0,0', true, $this->getWonder(), $storeId));
                $footerXY[1] = $addressFooterXY[1] +  $footerXY[1];
                if($footerXY[1] > $footerY){
                    $footerY = $footerXY[1];
                }
            }
        }


        if($this->_getConfig('pickpack_return_address_yn',0, false, $wonder, $storeId)) {
            $footerXY = explode(",", $this->_getConfig('pickpack_returnaddress', $pageConfig['addressFooterXYDefault'], true, $wonder, $storeId));
            if($footerXY[1] > $footerY){
                $footerY = $footerXY[1];
            }

            if($this->_getConfig('pickpack_returnlogo',0, false, $wonder, $storeId)) {
                $footerXY = explode(",", $this->_getConfig('pickpack_nudgelogo', $pageConfig['return_logo_XYDefault'], true, $wonder, $storeId));
                $footerXY[1] = $addressFooterXY[1] +  $footerXY[1];
                if($footerXY[1] > $footerY){
                    $footerY = $footerXY[1];
                }
            }

        }

        if($this->_getConfig('pickpack_bottom_shipping_address_yn_xtra',0, false, $wonder, $storeId)) {
            $footerXY = explode(",", $this->_getConfig('pickpack_shipaddress_xtra', $pageConfig['addressFooterXYDefault_xtra'], true, $wonder, $storeId));
            $addressFooterXY[0] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_X;
            $addressFooterXY[1] += self::BOTTOM_LABEL_ADDRESS_DEFAULT_NUDGE_Y;
            if($footerXY[1] > $footerY){
                $footerY = $footerXY[1];
            }
        }

        if($this->_getConfig('show_customs_declaration',0, false, $wonder, $storeId)) {
            $footerXY = explode(',',$this->_getConfig('show_customs_declaration_nudge','340,250', true, $wonder, $storeId));
            if($footerXY[1] > $footerY){
                $footerY = $footerXY[1];
            }
        }


        return $footerY;

    }

    public function checkCourrierrulesAndM2epro($shipping_address_background) {
        if (Mage::helper('pickpack')->isInstalled("Moogento_CourierRules"))
            return $shipping_address_background;

        if (Mage::helper('pickpack')->isInstalled("Ess_M2ePro")){
            $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
            $_allShippingMethodDescription = array();
            foreach($methods as $_ccode => $_carrier)
            {
                if($_methods = $_carrier->getAllowedMethods())
                {
                    if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                        $_title = $_ccode;

                    foreach($_methods as $_mcode => $_method)
                    {
                        if ($_mcode == "m2eproshipping")
                            continue;
                        $_allShippingMethodDescription[] = $_title." - ".$_method;
                    }
                }
            }

            foreach ($shipping_address_background as $key => $item){
                if (trim($item['pattern'])=="")
                    continue;
                if (!in_array($item['pattern'] , $_allShippingMethodDescription))
                    unset($shipping_address_background[$key]);
            }
        }
        return $shipping_address_background;
    }

    protected function getShippingAddressMaxPriority($order, $shipping_address_background)  {
        $print_row                                = -1;
        $max_priority_row                         = 9999;
        $shipping_background_type                 = '';
        $find_shipping_pattern_in_shipping_detail = 0;
        $shipping_description                     = $order->getShippingDescription();

        if (is_array($shipping_address_background)) {
            foreach ($shipping_address_background as $rowId => $row_value) {
                $row_type = $row_value['type'][0];
                if (($row_type == 'shipping_method') && ($shipping_description != '')) {
                    $shipping_description   = strtolower($shipping_description);
                    $list_carriers_name_row = explode(",", strtolower($row_value['pattern']));

                    foreach ($list_carriers_name_row as $k => $v) {
                        $v = strtolower($v);
                        if (!empty($v))
                            $pos = strpos($shipping_description, $v);
                        else
                            $pos = false;

                        if (($pos !== false) || ($v == '')) {
                            if ($row_value['priority'] == '')
                                $row_value['priority'] = 999;

                            if ($row_value['priority'] < $max_priority_row) {
                                $print_row                = $rowId;
                                $max_priority_row         = $row_value['priority'];
                                $shipping_background_type = $row_type;
                            }
                            $find_shipping_pattern_in_shipping_detail = 1;
                        }
                    }
                    unset($list_carriers_name_row);
                } else if ($row_type == 'courier_rules') {
                    if(Mage::helper('pickpack')->isInstalled('Moogento_CourierRules')) {
                        $courierrules_description = $order->getData('courierrules_description');
                        if(strlen(trim($courierrules_description)) > 0)
                            $shipping_description = $courierrules_description;

                        $shipping_description   = strtolower($shipping_description);
                        $list_carriers_name_row = explode(",", strtolower($row_value['pattern']));

                        foreach ($list_carriers_name_row as $k => $v) {
                            $v = strtolower($v);
                            if (!empty($v))
                                $pos = strpos($shipping_description, $v);
                            else
                                $pos = false;

                            if (($pos !== false) || ($v == '')) {
                                if ($row_value['priority'] == '')
                                    $row_value['priority'] = 999;

                                if ($row_value['priority'] < $max_priority_row) {
                                    $print_row                = $rowId;
                                    $max_priority_row         = $row_value['priority'];
                                    $shipping_background_type = $row_type;
                                }

                                $find_shipping_pattern_in_shipping_detail = 1;
                            }
                        }
                        unset($list_carriers_name_row);
                    }
                } elseif ($row_type == 'shipping_zone') {
                    $customer_country_id  = $order->getShippingAddress()->getCountryId();
                    $zone_collection = mage::getModel("moogento_courierrules/zone")->getCollection();
                    foreach ($zone_collection as $item){
                        $item_data = $item->getData();
                        if ( in_array($customer_country_id,$item_data['countries']) ) {
                            if ($row_value['priority'] == '')
                                $row_value['priority'] = 999;

                            if ($row_value['priority'] < $max_priority_row) {
                                $print_row                = $rowId;
                                $max_priority_row         = $row_value['priority'];
                                $shipping_background_type = $row_type;
                            }
                        }

                    }

                } elseif ($row_type == 'country_group') {
                    $country_in_group     = 0;
                    $image_position_nudge = array();
                    $customer_country_id  = $order->getShippingAddress()->getCountryId();
                    if ((Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy'))) {
                        $countryGroups                = Mage::getStoreConfig('moogento_shipeasy/country_groups');
                        $country_label_group          = $row_value['country_group'][0];
                        $country_group_list_key       = str_replace('label', 'countries', $country_label_group);
                        $country_group_list_value     = $countryGroups[$country_group_list_key];
                        $country_group_list_value_arr = explode(",", $country_group_list_value);

                        foreach ($country_group_list_value_arr as $k => $v) {
                            $pos = strpos($v, $customer_country_id);

                            if ($pos !== false) {
                                $country_in_group = 1;

                                if ($row_value['priority'] == '')
                                    $row_value['priority'] = 999;

                                if ($row_value['priority'] < $max_priority_row) {
                                    $print_row                = $rowId;
                                    $max_priority_row         = $row_value['priority'];
                                    $shipping_background_type = $row_type;
                                }
                            }
                        }

                    }
                }
            }
        }
        return $print_row;
    }
}