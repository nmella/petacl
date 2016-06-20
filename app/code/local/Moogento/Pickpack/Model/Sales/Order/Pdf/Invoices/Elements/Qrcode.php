<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Qrcode extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public function showQRCode() {
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $orderId = $order->getRealOrderId();

        $show_1st_qrcode =  $this->_getConfig('pickpack_show_first_qrcode', 0, false, $wonder, $storeId);
        $qrcode_pattern = $this->_getConfig('pickpack_show_qrcode_pattern','{{order_id}}', false, $wonder, $storeId);
        $qrcode_1st_nudge = explode(",", $this->_getConfig('qrcode_1st_nudge', '0,0', false, $wonder, $storeId));
        $show_2nd_qrcode =  $this->_getConfig('show_2nd_qrcode', 0, false, $wonder, $storeId);
        $qrcode_2nd_nudge = explode(",", $this->_getConfig('qrcode_2nd_nudge', '0,0', false, $wonder, $storeId));

        $errorCorrectionLevel = 'H';
        $matrixPointSize = 6;
        $filename = $this->getPngTmpDir().'orderId'.$orderId.'.png';
        //Order id, invoice id, shipping details.
        $qrcode_string = $this->getQrcodeText($qrcode_pattern,$order);

        if (!file_exists($filename)) {
            QRcode::png($qrcode_string, $filename, $errorCorrectionLevel, $matrixPointSize, 3);
        }

        if ($show_1st_qrcode == 1) {
            $image = Zend_Pdf_Image::imageWithPath($filename);
            $qr_x1 = $qrcode_1st_nudge[0];
            $qr_x2 = $qrcode_1st_nudge[0] + 50;
            $qr_y1= $qrcode_1st_nudge[1];
            $qr_y2 = $qrcode_1st_nudge[1] +50;
            $this->getPage()->drawImage($image, $qr_x1 , $qr_y1, $qr_x2, $qr_y2);

            if ($show_2nd_qrcode == 1) {
                $qr_x1 = $qrcode_2nd_nudge[0];
                $qr_x2 = $qrcode_2nd_nudge[0] + 50;
                $qr_y1= $qrcode_2nd_nudge[1];
                $qr_y2 = $qrcode_2nd_nudge[1] +50;
                $this->getPage()->drawImage($image, $qr_x1 , $qr_y1, $qr_x2, $qr_y2);
            }
        }
    }

    private function getQrcodeText($pattern,$order) {
        $date_format = 'd/m/Y';
        $invoice_title = $pattern;
        $storeId = $order->getStore()->getId();
        $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($storeId, $date_format);
        if ($invoice_title != '') {
            ////Order date. n/a if empty
            $order_date_title = 'n/a';
            $dated_title = $order->getCreatedAt();
            $dated_timestamp = strtotime($dated_title);

            if ($dated_title != '') {
                $order_date_title = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $date_format);
                $invoice_title = str_replace("{{if order_date}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif order_date}}", '', $invoice_title);

            } else {
                //This field is empty.
                $from_date = "{{if order_date}}";
                $end_date = "{{endif order_date}}";
                $from_date_pos = strpos($invoice_title, $from_date);
                if ($from_date_pos !== false) {
                    $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                    $date_length = $end_date_pos - $from_date_pos;
                    $date_str = substr($invoice_title, $from_date_pos, $date_length);
                    $invoice_title = str_replace($date_str, '', $invoice_title);
                }

                unset($from_date);
                unset($end_date);
                unset($from_date_pos);
                unset($end_date_pos);
                unset($date_length);
                unset($date_str);

            }
            //////////// Invoice date  n/a if empty
            if ($order->getCreatedAtStoreDate()) {
                $invoice_date_title = Mage::helper('pickpack/functions')->createInvoiceDateByFormat($order, $date_format_strftime, $date_format);
                $invoice_title = str_replace("{{if invoice_date}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif invoice_date}}", '', $invoice_title);
            } else {
                //This field is empty.
                $from_date = "{{if invoice_date}}";
                $end_date = "{{endif invoice_date}}";
                $from_date_pos = strpos($invoice_title, $from_date);
                if ($from_date_pos !== false) {
                    $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                    $date_length = $end_date_pos - $from_date_pos;
                    $date_str = substr($invoice_title, $from_date_pos, $date_length);
                    $invoice_title = str_replace($date_str, '', $invoice_title);
                }
                $invoice_title = str_replace("{{if order_date}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif order_date}}", '', $invoice_title);
                unset($from_date);
                unset($end_date);
                unset($from_date_pos);
                unset($end_date_pos);
                unset($date_length);
                unset($date_str);
            }

            $invoice_number_display = '';

            foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
                if ($_tmpInvoice->getIncrementId()) {
                    if ($invoice_number_display != '') $invoice_number_display .= ',';
                    $invoice_number_display .= $_tmpInvoice->getIncrementId();
                }
                break;
            }

            if ($invoice_number_display == '') {
                //This field is empty.
                $from_date = "{{if invoice_id}}";
                $end_date = "{{endif invoice_id}}";
                $from_date_pos = strpos($invoice_title, $from_date);
                if ($from_date_pos !== false) {
                    $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                    $date_length = $end_date_pos - $from_date_pos;
                    $date_str = substr($invoice_title, $from_date_pos, $date_length);
                    $invoice_title = str_replace($date_str, '', $invoice_title);
                }
                $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
                unset($from_date);
                unset($end_date);
                unset($from_date_pos);
                unset($end_date_pos);
                unset($date_length);
                unset($date_str);
            }
            else {
                $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
            }

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

            $from_date = "{{if warehouse}}";
            $end_date = "{{endif warehouse}}";
            $from_date_pos = strpos($invoice_title, $from_date);
            if ($from_date_pos !== false) {
                $end_date_pos = strpos($invoice_title, $end_date) + strlen($end_date);
                $date_length = $end_date_pos - $from_date_pos;
                $date_str = substr($invoice_title, $from_date_pos, $date_length);
                $invoice_title = str_replace($date_str, '', $invoice_title);
            } else {
                $invoice_title = str_replace("{{if warehouse}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif warehouse}}", '', $invoice_title);
            }
            unset($from_date);
            unset($end_date);
            unset($from_date_pos);
            unset($end_date_pos);
            unset($date_length);
            unset($date_str);
            /*****  Get Warehouse information ****/
            if ($date_format_strftime !== true) $printing_date_title = date($date_format, Mage::getModel('core/date')->timestamp(time()));
            else $printing_date_title = strftime($date_format, Mage::getModel('core/date')->timestamp(time()));
            if ($printing_date_title != '') {
                $invoice_title = str_replace("{{if printing_date}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif printing_date}}", '', $invoice_title);
            }

            $order_number_display_title = $order->getRealOrderId();
            if ($order_number_display_title != '') {
                $invoice_title = str_replace("{{if order_id}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif order_id}}", '', $invoice_title);
            }

            //market place order ID
            $marketPlaceOrderId = $this->getMarketPlaceId($order);
            if($marketPlaceOrderId != ''){
                $invoice_title = str_replace("{{if marketplace_order_id}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif marketplace_order_id}}", '', $invoice_title);
            }
            //ebay sale number
            $ebay_sale_number = $this->getEbaySaleNumber($order);
            if($ebay_sale_number != ''){
                $invoice_title = str_replace("{{if ebay_sales_number}}", '', $invoice_title);
                $invoice_title = str_replace("{{endif ebay_sales_number}}", '', $invoice_title);
            }

            //[fixed text] [%customer_id%] [%order_id%] [%invoice_id%] [%order_date%] [%invoice_date%] [%printed_date%] [%postcode%] [%shipping_lastname%] [%shipping_name%]
            //
            $customer_id = trim($order->getCustomerId());
            $printed_date = date('d/m/Y', Mage::getModel('core/date')->timestamp(time()));

            $shipping_address = $order->getShippingAddress();
            if(is_object($shipping_address))
            {
                if ($shipping_address->getPostcode())
                    $postcode = Mage::helper('pickpack/functions')->clean_method(strtoupper($shipping_address->getPostcode()),'pdf');
                else
                    $postcode ='';
                if($shipping_address->getLastname())
                    $shipping_lastname = Mage::helper('pickpack/functions')->clean_method($shipping_address->getLastname(),'pdf');
                else
                    $shipping_lastname = '';
                if($shipping_address->getPrefix() && $shipping_address->getFirstname() && $shipping_address->getLastname())
                    $shipping_name = Mage::helper('pickpack/functions')->clean_method($shipping_address->getPrefix() . ' ' . $shipping_address->getFirstname() . ' ' . $shipping_address->getLastname(),'pdf');
                else
                    $shipping_name = '';
            }
            else
            {
                $postcode ='';
                $shipping_lastname = '';
                $shipping_name = '';
            }


            $arr_1 = array('{{order_date}}', '{{invoice_date}}', '{{printing_date}}', '{{order_id}}', '{{invoice_id}}', '{{marketplace_order_id}}', '{{ebay_sales_number}}','{{customer_id}}','{{printed_date}}','{{postcode}}','{{shipping_lastname}}','{{shipping_name}}');

            $arr_2 = array($order_date_title, $invoice_date_title, $printing_date_title, $order_number_display_title, $invoice_number_display, $marketPlaceOrderId, $ebay_sale_number,$customer_id,$printed_date,$postcode,$shipping_lastname,$shipping_name);

            $invoice_title_print = str_replace($arr_1, $arr_2, $invoice_title);
            return $invoice_title_print;
        }
        return '';
    }
}