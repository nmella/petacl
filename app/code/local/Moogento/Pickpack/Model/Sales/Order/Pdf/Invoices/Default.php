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
 * File        Default.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */
/*
Print PDF Default for PDF invoice, PDF Packing Sheet and both.
*/
require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Default extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    protected $items_header_top_firstpage;

    protected $_configObject;
    protected $_configPdfObject;
    protected $_configPageObject;

    public $sku_supplier_item_action = array();
    public $sku_supplier_item_action_master = array();

    protected $_suppliers = array();
    protected $_warehouseTitle = array();
    protected $_wonder = '';
    protected $_currentPageConfig;
    protected $_currentPage;
    protected $_shippedItemsQty;

    protected $columns_xpos_array = array(); //this value use to save xpos to caculate columns at mid page

    protected $supplierOrderIds = array();

    public function __construct() {
        $this->_configObject = Mage::getModel('pickpack/sales_order_pdf_config_config');
        $this->_configPdfObject = Mage::getModel('pickpack/sales_order_pdf_config_pdf');
        $this->_configPageObject = Mage::getModel('pickpack/sales_order_pdf_config_page');

        parent::__construct();

        //TODO РЈР±СЂР°С‚СЊ РёР· С„СѓРЅРєС†РёРё
        $PNG_TEMP_DIR = $this->getPngTmpDir();
        if (!file_exists($PNG_TEMP_DIR)) mkdir($PNG_TEMP_DIR,0777,true);

        $storeId = Mage::app()->getStore()->getId();

        //TODO @NAMDG check effect of transper config from value to object
        /*************************** BEGIN PDF GENERAL CONFIG *******************************/
        //$this->setGeneralConfig($storeId);
        /*************************** END PDF GLOBAL PAGE CONFIG *******************************/
    }

    public function getCurrentPageConfig() {
        return $this->_currentPageConfig;
    }

    public function getWonder() {
        return $this->_wonder;
    }

    public function getPngTmpDir() {
        return Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'qrcode'.DS;
    }

    public function getWonderConfig($field, $store = null) {
        $this->_getConfig($field, $default = '', $add_default = true, $this->getWonder(), $store = null, $trim = true, $section = 'pickpack_options');
    }

    public function getGeneralConfig($wonder) {
        return $this->_configObject()->getGeneralConfig($wonder, $store = null);
    }

    public function getPackingsheetConfig($wonder, $storeId) {
        return Mage::helper('pickpack/config')->getPackingsheetConfigArray($wonder, $storeId);
    }

    public function getPdfDefault($orders = array(), $from_shipment = 'order', $invoice_or_pack = 'pack', $order_invoice_id = '', $shipment_ids = '',$order_items_arr = array()) {
        /** @var Moogento_Pickpack_Helper_Data $helper */
        $helper = Mage::helper('pickpack');

        $shipments = explode('|', $from_shipment);
        if ($shipments[0] == 'shipment') {
            unset($from_shipment);
            $from_shipment = 'shipment';
            unset($orders);
            $orders = explode(',', $shipments[1]);
        }

        $minY = array();
        $wonder = $this->_wonder = ($invoice_or_pack == 'invoice') ? 'wonder_invoice' : 'wonder';

        // РџСЂРѕРІРµСЂРєР° РЅР° Р·Р°Р»РѕРіРёРЅРµРЅРѕРіРѕ СЃР°РїРїР»Р°РµСЂР°.
        $storeId = Mage::app()->getStore()->getId();
        /*************************** BEGIN PDF PACKING-SHEET/INVOICE PAGE CONFIG *******************************/
        $this->setPickPackInvoiceConfig($storeId);
        /*************************** END PDF PACKING-SHEET/INVOICE PAGE CONFIG *******************************/

        /*********** OPTIMIZE SUGGESTION 1: Re-use orderCollection ***********************/
        foreach ($orders as $orderSingle) {
            $this->_currentPageCount = 1;
            //Check shipment_ids or order_id here
            if ($shipments[0] == 'shipment')
                $order = $helper->getOrderByShipment($orderSingle);
            else
                $order = $helper->getOrder($orderSingle);

            $storeId = $order->getStore()->getId();

            /***********************************************************
             * CONFIGURATIONS
             ***********************************************************/
            Mage::helper('pickpack/config')->getPackingsheetConfigArray($wonder,$storeId);

            $isSplitSupplierItem =  Mage::helper("pickpack/config_supplier")->isSplitSupplier($wonder, $storeId);
            if ($isSplitSupplierItem)
                $this->prepareSupplierArrays($order);
        }

        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);

        if (Mage::helper("pickpack/config_supplier")->isSplitSupplier($wonder)) {
            foreach($this->_suppliers as $supplier) {
                foreach ($orders as $orderSingle) {
                    $this->getPdfOrder($orderSingle, $supplier, $from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids,$order_items_arr);
                }
            }
        }
        else {
            foreach ($orders as $orderSingle) {
                $this->getPdfOrder($orderSingle, null, $from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids,$order_items_arr);
            }
        }

        $this->_afterGetPdf();
        return $pdf;
    }

    public function getPdfOrder($orderSingle, $supplier = null, $from_shipment = 'order', $invoice_or_pack = 'pack', $order_invoice_id = '', $shipment_ids = '',$order_items_arr = array()) {
        Varien_Profiler::start('PickPack PDF getPdfOrder');

        $pdf = $this->_getPdf();
        $helper = Mage::helper('pickpack');

        $wonder = $this->_wonder = ($invoice_or_pack == 'invoice') ? 'wonder_invoice' : 'wonder';
        $isShipment = ($from_shipment == 'order') ? false : true;
        $subheader_start = 0;
        $min_product_y = 0;

        //Check shipment_ids or order_id here
        $this->_shippedItemsQty = array();
        if ($isShipment) {
            $shipmentModel = Mage::getModel('sales/order_shipment')->load($orderSingle);
            $order = $helper->getOrder($shipmentModel->getOrderId());
            foreach ($shipmentModel->getItemsCollection() as $shipedItem) {
                $this->_shippedItemsQty[$shipedItem->getData('product_id')] = $shipedItem->getData('qty');
            }
        } else
            $order = $helper->getOrder($orderSingle);

        $storeId = $order->getStore()->getId();
        $order_id = $order->getRealOrderId();

        $generalConfig = Mage::helper('pickpack/config')->getGeneralConfigArray($storeId);
        $packingsheetConfig = Mage::helper('pickpack/config')->getPackingsheetConfigArray($wonder, $storeId);
        $pageConfig = Mage::helper('pickpack/config')->getPageConfigArray($wonder, $storeId);

        $isSplitSupplier =  Mage::helper("pickpack/config_supplier")->isSplitSupplier($wonder, $storeId);

        /*************************** PDF PAGE CONFIG *******************************/
        //TODO Moo Image turn off
        $show_top_logo_yn = $this->_getConfig('pickpack_packlogo', 0, false, $wonder, $storeId);

        /*************************** GIFTWRAP MESSAGE*******************************/

        $product_gift_message_yn = $this->_getConfig('product_gift_message_yn', 'no', false, $wonder, $storeId);

        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($generalConfig['background_color_subtitles']);

        $product_images_maxdimensions = explode(',', str_ireplace('null', '', $this->_getConfig('product_images_maxdimensions', '50,50', false, $wonder, $storeId)));
        if ($product_images_maxdimensions[0] == '' || $product_images_maxdimensions[1] == '') {
            if ($product_images_maxdimensions[0] == '')
                $product_images_maxdimensions[0] = NULL;
            if ($product_images_maxdimensions[1] == '')
                $product_images_maxdimensions[1] = NULL;
            if ($product_images_maxdimensions[0] == NULL && $product_images_maxdimensions[1] == NULL)
            {
                $product_images_maxdimensions[0] = 50;
                $product_images_maxdimensions[1] = 50;
            }
        }

        $show_aitoc_checkout_field_bottom_yn = $this->_getConfig('show_aitoc_checkout_field_bottom_yn', 0, false, $wonder, $storeId);
        $show_aitoc_checkout_field_bottom = $this->_getConfig('show_aitoc_checkout_field_bottom', '', false, $wonder, $storeId);

        $headerBarXY = array($pageConfig['orderIdX'], $pageConfig['orderIdY']);

        if($packingsheetConfig['pickpack_bottom_shipping_address_yn'] ==1){
            $tracking_number_barcode_yn = $this->_getConfig('tracking_number_barcode_yn', 0, false, $wonder, $storeId);
            $tracking_number_yn = $this->_getConfig('tracking_number_yn', 1, false, $wonder, $storeId);
        }
        $bottom_movable_order_id_yn = $this->_getConfig('pickpack_bottom_movable_order_id_yn', 0, false, $wonder, $storeId);

        $float_top_address_yn = 0;
        /*************************** END PDF PAGE CONFIG *******************************/

        $supplierLogin = Mage::helper('pickpack/config_supplier')->getSupplierLogin($storeId);

        /*************************** New PDF PER Item *******************************/
        $count_item = 0;
        do {
            /*************************** BEGIN TO PRINT ************************************/
            $keep_supplier_order = false;
            $keep_supplier_login = true;
            if ($supplier && array_search($supplier, $this->supplierOrderIds[$order_id]) !== false)
                $keep_supplier_order = true;
            if (isset($supplierLogin) && ($supplierLogin != "") && $supplierLogin != strtolower($supplier))
                $keep_supplier_login = false;

            if ((isset($this->sku_supplier_item_action_master[$order_id]) && $this->sku_supplier_item_action_master[$order_id] == 'keep' && $keep_supplier_order && $keep_supplier_login) || !$isSplitSupplier) {
                $page = $this->nooPage($generalConfig['page_size']);

                /***************PRINTING BACKGROUND PAGE****************/
                $page_background_image_yn = $this->_getConfig('page_background_image_yn', 1, false, $wonder, $storeId);
                if ($page_background_image_yn == 1) {
                    $suffix_group = 'page_background_image';
                    $this->printBackGroundImage($page, $storeId, $suffix_group, $pageConfig['backgroundImageX'], $pageConfig['backgroundImageY']);
                }

                /******Set language*******/
                $choose_language_display = $this->_getConfig('choose_language_display', 'l_login', false, "general", $storeId);
                if($choose_language_display == "l_store"){
                    $locale = Mage::getStoreConfig('general/locale/code', $storeId);
                    Mage::app()->getLocale()->setLocaleCode($locale);
                    Mage::getSingleton('core/translate')->setLocale($locale)->init('adminhtml', true);
                }

                $invoiceTitle2YN = $this->_getConfig('pickpack_title_2_yn', 0, false, $wonder, $storeId);
                if ($invoiceTitle2YN == 1) {
                    $invoiceTitleElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_pagetitle2', array($this, $order));
                    $invoiceTitleElement->generalConfig = $generalConfig;
                    $invoiceTitleElement->packingsheetConfig = $packingsheetConfig;
                    $invoiceTitleElement->showTitle();
                }

                //this value use to get index for header page to print gift warp icon
                $currentHeaderPage = $page;
                /***************************PRINTING 1 HEADER LOGO *******************************/
                $start_page_for_order = count($pdf->pages) -1;
                $headerLogoElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_headerlogo', array($this, $order));
                $headerLogoElement->generalConfig = $generalConfig;
                $headerLogoElement->packingsheetConfig = $packingsheetConfig;
                if ($show_top_logo_yn == 1) {
                    /*************************** PRINT HEADER LOGO *******************************/
                    $headerLogoElement->showLogo();
                    $headerBarXY[1] += 5;
                    /*************************** END PRINT HEADER LOGO ***************************/
                }
                else
                    $headerBarXY[1] = $pageConfig['page_top'];

                $logo_y1 = $headerLogoElement->getY1();

                /**PRINTING ORDER ID BARCODE**/
                if ($packingsheetConfig['pickpack_packbarcode'] == 1 && ($packingsheetConfig['pickpack_logo_position'] != 'right' || $packingsheetConfig['pickpack_packlogo'] == 0)){
                    $barcodeElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_barcode', array($this, $order));
                    $barcodeElement->generalConfig = $generalConfig;
                    $barcodeElement->packingsheetConfig = $packingsheetConfig;
                    $barcodeElement->showTopBarcode($pageConfig['page_top'] - 5);
                }

                /**PRINTING MOVABLE ORDER ID**/
                $showOrderId = $this->_getConfig('show_order_id', 0, false, $wonder, $storeId);
                if ($showOrderId == 1) {
                    $movableOrderIDElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_movableorderid', array($this, $order));
                    $movableOrderIDElement->generalConfig = $generalConfig;
                    $movableOrderIDElement->packingsheetConfig = $packingsheetConfig;
                    $movableOrderIDElement->setHeaderLogo($headerLogoElement);
                    $movableOrderIDElement->showOrderId();
                }

                /*************************** END HEADER LOGO *******************************/
                /******Set language*******/
                $choose_language_display = $this->_getConfig('choose_language_display', 'l_login', false, "general", $storeId);
                if($choose_language_display == "l_store"){
                    $locale = Mage::getStoreConfig('general/locale/code', $order->getStore()->getId());
                    Mage::app()->getLocale()->setLocaleCode($locale);
                    Mage::getSingleton('core/translate')->setLocale($locale)->init('adminhtml', true);
                }

                /*************************** PRINTING 2 HEADER STORE ADDRESS *******************************/
                if ($packingsheetConfig['pickpack_logo_position'] != 'fullwidth'){
                    $company_address_yn = $packingsheetConfig['pickpack_company_address_yn'];
                }else $company_address_yn = 0;

                $storeAddressElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_storeaddress', array($this, $order));
                $storeAddressElement->generalConfig = $generalConfig;
                $storeAddressElement->packingsheetConfig = $packingsheetConfig;
                if($company_address_yn != 0) {
                    $storeAddressElement->setHeaderLogo($headerLogoElement);
                    $x = $pageConfig['padded_left'];
                    $storeAddressElement->x = $x;
                    $storeAddressElement->y = $this->y;
                    $storeAddressElement->showAddress();
                    if(empty($logo_y1)){
                        $logo_y1  = $storeAddressElement->y;
                    }
                    else if($logo_y1 > $storeAddressElement->y){
                        $logo_y1  = $storeAddressElement->y;
                    }
                }
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                /*************************** END HEADER STORE ADDRESS *******************************/

                /*************************** GET NUMBER ORDER TO PRINT IN PDF *******************************/
                $order_or_invoice = $this->_getConfig('orderorinvoice', 'order', false, $wonder, $storeId);
                $invoice_number_display = '';
                foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
                    if ($_tmpInvoice->getIncrementId()) {
                        if ($invoice_number_display != '')
                            $invoice_number_display .= ',';
                        $invoice_number_display .= $_tmpInvoice->getIncrementId();
                    }
                    break;
                }
                if ($order_or_invoice == 'order')
                    $order_number_display = $order->getRealOrderId();
                elseif ( ($order_or_invoice == 'invoice') && ($invoice_number_display != '') )
                    $order_number_display = $invoice_number_display;

                unset($order_or_invoice);
                unset($invoice_number_display);
                /*************************** END GET NUMBER ORDER TO PRINT IN PDF *******************************/

                /****************************PRINTNG 3 HEADER TITLEBAR BEFORE SHIPPING ADDRESS*****************************/
                $items_header_top_firstpage = $headerBarXY[1];
                /** @var Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Headerbar $headerBarElement */
                $pickpack_headerbar_yn = trim($this->_getConfig('pickpack_headerbar_yn', '1', false, $wonder, $storeId));
                $headerBarElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_headerbar', array($this, $order));
                if ($pickpack_headerbar_yn == 1) {
                    $headerBarElement->generalConfig = $generalConfig;
                    $headerBarElement->packingsheetConfig = $packingsheetConfig;
                    $headerBarElement->setSupplier($supplier);
                    $headerBarElement->headerLogoY = $logo_y1;
                    $headerBarElement->logoPosition = $headerLogoElement->getLogoPosition();
                    $headerBarElement->companyVertLineY1 = $storeAddressElement->getVertLineY1();
                    $headerBarElement->isShipment = $isShipment;
                    $headerBarElement->showHeaderBar();
                    $headerBarXY = $headerBarElement->headerBarXY;
                    $order_number_display = $headerBarElement->order_number_display;
                }
                /*************************** END HEADER TITLEBAR BEFORE SHIPPING ADDRESS *****************************/

                /******************************************* QR Code **************************************************/
                $QRCodeElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_qrcode', array($this, $order));
                $QRCodeElement->generalConfig = $generalConfig;
                $QRCodeElement->packingsheetConfig = $packingsheetConfig;
                $QRCodeElement->showQRCode();
                /***************************************** END QR Code **************************************************/

                /***************************** SHIPPING AND BILLING ADDRESS *********************************/
                /** @var Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Address $addressesElement */
                $addressesElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_address', array($this, $order));
                $addressesElement->generalConfig = $generalConfig;
                $addressesElement->packingsheetConfig = $packingsheetConfig;

                $pageFooterHeight = $addressesElement->getPageFooterHeight();

                $addressesElement->headerBarXY = $headerBarElement->headerBarXY;
                $addressesElement->subheader_start = $subheader_start;
                $addressesElement->showAddress();
                /*************************** END SHIPPING AND BILLING ADDRESS *******************************/

                /****************************** PRINTING Trolley Title  ********************************/
                $trolleyElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_trolley', array($this, $order));
                $trolleyElement->generalConfig = $generalConfig;
                $trolleyElement->packingsheetConfig = $packingsheetConfig;
                $trolleyElement->showTrolley($order_items_arr);
                /*************************** END PRINTING Trolley Title  *******************************/

                /***************************PRINTING BOTTOM TRACKING NUMBER BARCODE *******************************/
                $trackingNumberBarcodeElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_trackingnumberbarcode', array($this, $order));
                if(isset($tracking_number_barcode_yn)  && ($tracking_number_barcode_yn == 1)){
                    $trackingNumberBarcodeElement->generalConfig = $generalConfig;
                    $trackingNumberBarcodeElement->packingsheetConfig = $packingsheetConfig;
                    $trackingNumberBarcodeElement->showBarcode();
                }

                /***************************PRINTING BOTTOM TRACKING NUMBER *******************************/
                if(isset($tracking_number_yn) && ($tracking_number_yn == 1)){
                    $trackingNumberElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_trackingnumber', array($this, $order));
                    $trackingNumberElement->generalConfig = $generalConfig;
                    $trackingNumberElement->packingsheetConfig = $packingsheetConfig;
                    $trackingNumberElement->showTrackingNumber();
                }

                /***************************PRINTING BOTTOM SHIPPING ADDRESS TITLE *******************************/
                $shipping_address_flat = '';

                if ($packingsheetConfig['pickpack_return_address_yn'] != 0) {
                    $returnAddressElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_returnaddress', array($this, $order));
                    $returnAddressElement->generalConfig = $generalConfig;
                    $returnAddressElement->packingsheetConfig = $packingsheetConfig;

                    /***************************PRINTING BOTTOM RETURN ADDRESS IMAGE *******************************/
                    if($packingsheetConfig['show_return_logo_yn'] == 1)
                        $returnAddressElement->showLogo1();

                    if($packingsheetConfig['show_return_logo2_yn'] == 1)
                        $returnAddressElement->showLogo2();
                    /***************************END PRINTING BOTTOM RETURN ADDRESS IMAGE ***************************/

                    /***************************PRINTING BOTTOM RETURN ADDRESS *******************************/
                    $returnAddressElement->showReturnAddress();
                }

                /***************************PRINTING BOTTOM POSITIONABLE ORDER ID BELOW BOTTOM SHIPPING ADDRESS*******************************/
                if ($bottom_movable_order_id_yn == 1)
                    $addressesElement->showBottomShippingAddressId2();
                /***************************END PRINTING BOTTOM POSITIONABLE ORDER ID BELOW BOTTOM SHIPPING ADDRESS*******************************/

                if($show_aitoc_checkout_field_bottom_yn == 1 && Mage::helper('pickpack')->isInstalled("Aitoc_Aitcheckoutfields")){
                    $codes = Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getInvoiceCustomData($order->getId(), null, true);
                    $code_fields = explode(',', $show_aitoc_checkout_field_bottom);
                    $addon_code_x = 0;
                    $addressFooterXY = $packingsheetConfig['pickpack_shipaddress'];
                    foreach ($codes as $key => $code) {
                        if($code["code"] != '' && in_array($code["code"], $code_fields)){
                            $page->drawText($code["value"], ($addressesElement->bottom_shipping_address_pos['x'] + $addon_code_x), ($addressFooterXY[1] - 5), 'UTF-8');
                            $addon_code_x += 40;
                        }
                    }
                }

                if($packingsheetConfig['show_shipping_method_bottom_yn'] == 1)
                    $addressesElement->showShippingMethodBottom();

                /**BOTTOM 2nd SHIPPING ADDRESS**/
                $bottom_2nd_shipping_address_yn = $this->_getConfig('pickpack_second_bottom_shipping_address_yn', 0, false, $wonder, $storeId);
                if(($packingsheetConfig['pickpack_bottom_shipping_address_yn'] == 1) && ($bottom_2nd_shipping_address_yn == 1))
                    $addressesElement->showMultiShippingAddress();

                /***************************PRINTING BOTTOM EXTRA ADDRESS *******************************/

                if (($packingsheetConfig['pickpack_bottom_shipping_address_yn_xtra'] == 1)||($packingsheetConfig['pickpack_bottom_shipping_address_yn_xtra'] == 2)){
                    if (isset($addressesElement) && $addressesElement->shipping_address_flat != '')
                        $this->y = $addressesElement->showBottomExtraAddress();
                }
                /***************************END PRINTING BOTTOM EXTRA ADDRESS ***************************/

                $addressesElement->showAddressDetails();
                $this->y = $addressesElement->y;
                $this->y -= $generalConfig['font_size_body'] * 0.5;
                $subheader_start = $addressesElement->subheader_start;
                $top_y_left_column = $addressesElement->top_y_left_column;
                $top_y_right_column = $addressesElement->top_y_right_column;

                /***************************PRINTING ORDER NOTE UNDER SHIPPING ADDRESS *******************************/
                $notes_yn = $this->_getConfig('notes_yn', 0, false, $wonder, $storeId);
                $notes_position = $this->_getConfig('notes_position', 'yesshipping', false, $wonder, $storeId);
                if ($notes_yn == 0)
                    $notes_position = 'no';
                $orderNoteElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_ordernote', array($this, $order));
                $orderNoteElement->generalConfig = $generalConfig;
                $orderNoteElement->packingsheetConfig = $packingsheetConfig;
                $orderNoteElement->subheader_start = $subheader_start;
                $orderNoteElement->items_header_top_firstpage = $items_header_top_firstpage;
                $orderNoteElement->y = $this->y;
                if ($notes_position == 'yesshipping') {
                    /** @var Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Ordernote $orderNoteElement */
                    $orderNoteElement->pageFooterHeight = $pageFooterHeight;
                    $orderNoteElement->showBottomNote($subheader_start);
                    $this->y = $orderNoteElement->y;
                    $this->y -= $generalConfig['font_size_body'] * 0.2;
                }
                $flag_message_after_shipping_address = $orderNoteElement->flag_message_after_shipping_address;
                $subheader_start = $orderNoteElement->subheader_start;
                /******************************END PRINTING ORDER NOTE UNDER SHIPPING ADDRESS ********************************/

                $giftMessageElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_giftmessage', array($this, $order));
                $giftMessageElement->generalConfig = $generalConfig;
                $giftMessageElement->packingsheetConfig = $packingsheetConfig;
                $giftMessageElement->flag_message_after_shipping_address = $flag_message_after_shipping_address;
                $giftMessageElement->subheader_start = $subheader_start;
                $giftMessageElement->headerBarXY = $headerBarXY;
                $giftMessageElement->email_X = $addressesElement->email_X;
                $giftMessageElement->y = $this->y;

                /***************************PRINTING ORDER GIFT MESSAGE UNDER SHIPPING ADDRESS *******************************/
                $gift_message_yn = $this->_getConfig('gift_message_yn', 'yesunder', false, $wonder, $storeId);
                if ($gift_message_yn == 'yesundership')
                    $giftMessageElement->showTopOrderGiftMessage();

                /***************************PRINTING PRODUCT GIFT MESSAGE UNDER SHIPPING ADDRESS *******************************/
                if ($product_gift_message_yn == 'yesundership')
                    $giftMessageElement->showTopProductGiftMessage();

                $giftwrap_style_yn = $this->_getConfig('gift_wrap_style_yn', 'yesshipping', false, $wonder, $storeId);
                if($giftwrap_style_yn == 'yesshipping')
                    $giftMessageElement->showGiftWrapUnderShippingAddress();

                $this->y = $giftMessageElement->y;
                $subheader_start = $giftMessageElement->subheader_start;

                /***************************PRINTING CUSTOMER COMMENTS UNDER SHIPPING ADDRESS *******************************/
                if(($notes_yn != 0) && ($notes_position == 'yesshipping')) {
                    $customerCommentsElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_customercomments', array($this, $order));
                    $customerCommentsElement->generalConfig = $generalConfig;
                    $customerCommentsElement->packingsheetConfig = $packingsheetConfig;
                    $customerCommentsElement->y = $this->y;
                    $customerCommentsElement->subheader_start = $this->y;
                    $customerCommentsElement->showComments();
                    $customer_comments = $customerCommentsElement->customer_comments;
                    $this->y = $customerCommentsElement->subheader_start;
                    $subheader_start = $customerCommentsElement->subheader_start;
                }
                /***************************END PRINTING CUSTOMER COMMENTS UNDER SHIPPING ADDRESS *******************************/

                /***************************PRINTING POSTMAN NOTICE UNDER SHIPPING ADDRESS *******************************/
                /* Add Order Postman Notice*/
                if (Mage::helper('pickpack')->isInstalled('AW_Sarp')) {
                    $awSarpElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_module_aw_sarp', array($this, $order));
                    $awSarpElement->subheader_start = $subheader_start;
                    $awSarpElement->y = $this->y;
                    $awSarpElement->headerBarXY = $headerBarXY;
                    $awSarpElement->order_notes_was_set;
                    $awSarpElement->showPostmanNotice();
                    $subheader_start = $awSarpElement->subheader_start;
                    $this->y = $awSarpElement->y;
                    $order_notes_was_set = $awSarpElement->order_notes_was_set;
                }
                /***************************END PRINTING POSTMAN NOTICE UNDER SHIPPING ADDRESS *******************************/

                $line_height = 0;
                $page->setFillColor(Mage::helper('pickpack/config_color')->getPdfColor('black_color'));
                $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                if (strtoupper($generalConfig['background_color_subtitles']) == '#FFFFFF')
                    $subheader_start -= ($generalConfig['font_size_subtitles'] * 2);

                // set the y pos of the first bar, according to height of logo image
                if($show_top_logo_yn == 1 && (($logo_y1) < $pageConfig['page_top'] + 5 - $pageConfig['logo_maxdimensions'][1])){
                    if ($subheader_start > ($logo_y1 - 5))
                        $subheader_start = $logo_y1 - 5;
                    $subheader_start = min(array($subheader_start, $top_y_left_column, $top_y_right_column));
                }

                /**PRINTING AMASTY ORDER ATTRIBUTE**/
                if (Mage::helper('pickpack')->isInstalled('Amasty_Orderattr')) {
                    $amastyOrderattrElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_module_amasty_orderattr', array($this, $order));
                    $amastyOrderattrElement->flag_message_after_shipping_address = $flag_message_after_shipping_address;
                    $amastyOrderattrElement->subheader_start = $subheader_start;
                    $amastyOrderattrElement->showAttribute();
                    $flag_message_after_shipping_address = $amastyOrderattrElement->flag_message_after_shipping_address;
                    $subheader_start = $amastyOrderattrElement->subheader_start;
                }
                /**END PRINTING AMASTY ORDER ATTRIBUTE**/

                /**PRINTING AMASTY DELIVERY DATE **/
                $order_custom_delivery_date_yn = $this->_getConfig('order_custom_delivery_date_yn', 0, false, $wonder, $storeId);
                if (($order_custom_delivery_date_yn == 1) && Mage::helper('pickpack')->isInstalled('Amasty_Deliverydate')) {
                    $amastyDeliverydateElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_module_amasty_deliverydate', array($this, $order));
                    $amastyDeliverydateElement->subheader_start = $subheader_start;
                    $amastyDeliverydateElement->y = $this->y;
                    $amastyDeliverydateElement->showDeliveryDate();
                    $this->y = $amastyDeliverydateElement->y;
                    $subheader_start = $amastyDeliverydateElement->subheader_start;
                }
                if (strtoupper($generalConfig['background_color_subtitles']) != '#FFFFFF') {
                    $page->setFillColor($background_color_subtitles_zend);
                    $page->setLineColor($background_color_subtitles_zend);
                    $page->setLineWidth(0.5);
                }

                /**PRINTING MW DELIVERY DATE **/
                $order_mw_custom_delivery_date_yn = $this->_getConfig('order_mw_custom_delivery_date_yn', 0, false, $wonder, $storeId);
                if ( ($order_mw_custom_delivery_date_yn == 1) && Mage::helper('pickpack')->isInstalled('MW_Ddate') ) {
                    $mwDeliverydateElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_module_mw_ddate', array($this, $order));
                    $mwDeliverydateElement->customer_comments = $customer_comments;
                    $mwDeliverydateElement->subheader_start = $subheader_start;
                    $mwDeliverydateElement->y = $this->y;
                    $mwDeliverydateElement->showDeliveryDate();
                    $customer_comments = $mwDeliverydateElement->customer_comments;
                    $subheader_start = $mwDeliverydateElement->subheader_start;
                    $this->y = $mwDeliverydateElement->y;
                }
                /**PRINTING MW DELIVERY DATE **/
                if (strtoupper($generalConfig['background_color_subtitles']) != '#FFFFFF') {
                    $page->setFillColor($background_color_subtitles_zend);
                    $page->setLineColor($background_color_subtitles_zend);
                    $page->setLineWidth(0.5);
                }

                /***************************PRINTING HEADER TITLEBAR UNDER SHIPPING ADDRESS*****************************/
                if ($pickpack_headerbar_yn == 2) {
                    $headerBarElement->generalConfig = $generalConfig;
                    $headerBarElement->packingsheetConfig = $packingsheetConfig;
                    $headerBarElement->setHeaderLogo($headerLogoElement);
                    $headerBarElement->setStoreAddress($storeAddressElement);
                    $headerBarElement->setSupplier($supplier);
                    $headerBarElement->subheader_start = $subheader_start;
                    $headerBarElement->showHeaderBar2();
                    $subheader_start = $headerBarElement->subheader_start;
                }
                /***************************PRINTING HEADER TITLEBAR UNDER SHIPPING ADDRESS*****************************/

                /***********PRINTING QC MESSAGE***********/
                $packedByElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_packedby', array($this, $order));
                $packedByElement->generalConfig = $generalConfig;
                $packedByElement->packingsheetConfig = $packingsheetConfig;
                if ($this->_getConfig('packed_by_yn', 0, false, $wonder, $storeId) == 1)
                    $packedByElement->showPackedBy($this->getPage()); //getFirstPage

                /*************************** START PRINT PRODUCTS *******************************/
                $subheader_start += $generalConfig['font_size_body'];
                if ($flag_message_after_shipping_address == 1)
                    $subheader_start -= $generalConfig['font_size_body'] * 1.3;

                /** @var Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Products $productsElement */
                $productsElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_products', array($this, $order));
                $productsElement->generalConfig = $generalConfig;
                $productsElement->packingsheetConfig = $packingsheetConfig;
                $productsElement->setSupplier($supplier);
                $productsElement->y = $this->y;
                $productsElement->gift_message_array = $orderNoteElement->gift_message_array;
                $productsElement->subheader_start = $subheader_start;
                $productsElement->isShipment = $isShipment;
                $productsElement->sku_supplier_item_action = $this->sku_supplier_item_action;
                $productsElement->items_header_top_firstpage = $items_header_top_firstpage;
                $productsElement->order_number_display = $order_number_display;
                $productsElement->count_item = $count_item;
                $productsElement->min_product_y = $min_product_y;
                $productsElement->line_height = $line_height;
                $productsElement->caculateDefaultValue();
                $productsElement->showItemsGrid($from_shipment, $invoice_or_pack, $order_invoice_id, $shipment_ids, $order_items_arr);
                $this->y = $productsElement->y;
                $min_product_y = $productsElement->min_product_y;
                $subheader_start = $productsElement->subheader_start;
                /***************************PRINTING TOTALS********************************/

                /** @var Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals $totalsBlock */
                $totalsBlock = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_totals', array($this, $order));
                $totalsBlock->generalConfig = $generalConfig;
                $totalsBlock->packingsheetConfig = $packingsheetConfig;
                $totalsBlock->y = $this->y;
                $totalsBlock->discount_line_or_subtotal = $productsElement->discount_line_or_subtotal;
                $totalsBlock->tax_percents = $productsElement->tax_percents;
                $totalsBlock->tax_percents_total = $productsElement->tax_percents_total;
                $totalsBlock->tax_rate_code = $productsElement->tax_rate_code;
                $totalsBlock->min_product_y = $min_product_y;
                $totalsBlock->items_header_top_firstpage = $items_header_top_firstpage;
                $totalsBlock->packedByXY = $packedByElement->packedByXY;
                $totalsBlock->order_number_display = $order_number_display;
                $use_magento_subtotal = $this->_getConfig('use_magento_subtotal', 0, false, $wonder, $storeId);


                $totalsBlock = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_totals', array($this, $order));
                $totalsBlock->generalConfig = $generalConfig;
                $totalsBlock->packingsheetConfig = $packingsheetConfig;
                $totalsBlock->y = $this->y;
                $totalsBlock->total_data = $productsElement->subtotal_data;
                $totalsBlock->showTotals();

                $this->y = $totalsBlock->y;

                /******************************************FULL PAYMENT**************************************************/
                $pickpack_show_full_payment_yn = $this->_getConfig('pickpack_show_full_payment_yn', 0, false, $wonder, $storeId);
                if($pickpack_show_full_payment_yn == 1) {
                    $fullPaymentElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_fullpayment', array($this, $order));
                    $fullPaymentElement->generalConfig = $generalConfig;
                    $fullPaymentElement->packingsheetConfig = $packingsheetConfig;
                    $fullPaymentElement->showFullPayment();
                }

                /*************************** PRINTING CUSTOM MESSAGE *******************************/
                $customMessageElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_custommessage', array($this, $order));
                $customMessageElement->generalConfig = $generalConfig;
                $customMessageElement->packingsheetConfig = $packingsheetConfig;
                $customMessageElement->y = $this->y;
                $customMessageElement->start_page_for_order = $start_page_for_order;
                $customMessageElement->order_number_display = $order_number_display;
                $customMessageElement->items_header_top_firstpage = $items_header_top_firstpage;
                $customMessageElement->min_product_y = $min_product_y;
                $customMessageElement->img_height = $productsElement->img_height;
                $customMessageElement->has_shown_product_image = $productsElement->has_shown_product_image;
                $customMessageElement->pageFooterHeight = $pageFooterHeight;
                $customMessageElement->showCustomMessage();
                $this->y = $customMessageElement->y;

                /***********PRINTING ORDER NOTES***********/
                if (($notes_position != 'no') && ($notes_position != 'yesshipping')) {
                    $orderNoteElement->order_number_display = $order_number_display;
                    $orderNoteElement->y = $this->y;
                    $orderNoteElement->pageFooterHeight = $pageFooterHeight;
                    $orderNoteElement->showBottomNote();
                    $this->y = $orderNoteElement->y;
                    $order_number_display = $orderNoteElement->order_number_display;
                }

                if (($notes_position != 'no') && ($notes_position != 'yesshipping')) {
                    $customerCommentsElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_customercomments', array($this, $order));
                    $customerCommentsElement->generalConfig = $generalConfig;
                    $customerCommentsElement->packingsheetConfig = $packingsheetConfig;
                    $customerCommentsElement->y = $this->y;
                    $customerCommentsElement->subheader_start = $this->y;
                    $customerCommentsElement->showComments();
                    $customer_comments = $customerCommentsElement->customer_comments;
                    $this->y = $customerCommentsElement->subheader_start;
                    $subheader_start = $customerCommentsElement->subheader_start;
                }

                /***********PRINTING SUPPLIER ATTRIBUTE***********/
                if($isSplitSupplier) {
                    list($x,$y) = explode(',', $pageConfig['supplierXYDefault']);
                    $supplierBlock = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_supplier', array($this, $order));
                    $supplierBlock->generalConfig = $generalConfig;
                    $supplierBlock->packingsheetConfig = $packingsheetConfig;
                    $supplierBlock->drawSupplierBlock($supplier, $x, $y);
                }

                /**********************************GIFT MESSAGE, CUSTOM MESSAGE AND NOTES***********************************/

                /***********PRINTING ORDER GIFT MESSAGE***********/
                if (($gift_message_yn == 'yesunder' || $gift_message_yn == 'yesbox' || $gift_message_yn == 'yesnewpage')) {
                    $giftMessageElement->y = $this->y;
                    $giftMessageElement->gift_message_array = $productsElement->gift_message_array;
                    $giftMessageElement->comments_y =  $orderNoteElement->comments_y;
                    $giftMessageElement->order_number_display = $order_number_display;
                    $giftMessageElement->items_header_top_firstpage = $items_header_top_firstpage;
                    $giftMessageElement->custom_message_yn = $customMessageElement->message_yn;
                    $giftMessageElement->bottom_message_pos = $customMessageElement->bottom_message_pos;
                    $giftMessageElement->showBottomOrderGiftMessage();
                }

                /***********PRINTING PRODUCT GIFT MESSAGE***********/
                if (($product_gift_message_yn == 'yesnewpage' || $product_gift_message_yn == 'yesbox' || $product_gift_message_yn == 'yesunder'))
                    $giftMessageElement->showBottomProductGiftMessage();

                /*************************REPEAT GIFT MESSAGE **************************/
                $repeat_gift_message_yn = $this->_getConfig('repeat_gift_message_yn', 'no', false, $wonder, $storeId);
                if($repeat_gift_message_yn == 1)
                    $giftMessageElement->showRepeatGiftMessage();

                /*************************PRINT AUTOCN22 LABEL **************************/
                if (Mage::helper('pickpack')->isInstalled('Moogento_Cn22')) {
                    if($this->_getConfig('show_customs_declaration',0, false, $wonder, $storeId) == 1) {
                        $moogentoCn22Element = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_module_moogento_cn22', array($this, $order));
                        $moogentoCn22Element->itemQtyArray = $productsElement->itemQtyArray;
                        $moogentoCn22Element->showCustomsDeclaration();
                    }
                }

                /*************************** PRINT GIFT WRAP ICON AT TOP RIGHT *******************************/
                $show_gift_wrap_yn = (Mage::helper('pickpack')->isMageEnterprise()) ? $this->_getConfig('show_gift_wrap', 0, false, $wonder, $storeId) : 0;
                if($show_gift_wrap_yn && $productsElement->show_top_right_gift_icon)
                    $giftMessageElement->showTopGiftWrapIcon($currentHeaderPage);

                /*************************** PRINT CourierRules LABEL AT BOTTOM LEFT *******************************/
                if (Mage::helper('pickpack')->isInstalled('Moogento_CourierRules') && ($this->_getConfig('show_courierrules_shipping_label', 0, false, $wonder, $storeId) == 1)) {
                    $courierRulesElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_module_moogento_courierrules', array($this, $order));
                    $courierRulesElement->showCourierRulesLabel();
                }
            }
            $count_item = $count_item -1;
        } while($count_item > 0);
        Varien_Profiler::stop('PickPack PDF getPdfOrder');
    }

    public function prepareSupplierArrays($order) {
        $storeId = $order->getStore()->getId();
        $orderId = $order->getRealOrderId();

        $supplierAttribute = Mage::helper("pickpack/config_supplier")->getSupplierAttribute($storeId);
        $supplierFilterOptions = Mage::helper("pickpack/config_supplier")->getFilterSupplierOptions($storeId);

        $isWarehouseSupplier = 0;
        if (Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse') && ($supplierAttribute == 'warehouse'))
            $isWarehouseSupplier = 1;

        $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
        $supplierArray = array();
        foreach ($itemsCollection as $item) {
            if ($isWarehouseSupplier == 1) {
                $warehouse_title = $item->getWarehouseTitle();
                $warehouse = $item->getWarehouse();
                $warehouse_code = $warehouse->getData('code');
                $supplier = $warehouse_code;
                $warehouse_code = trim(strtoupper($supplier));
                $this->_warehouseTitle[$warehouse_code] = $warehouse_title;
            } else {
                $product = Mage::helper('pickpack/product')->getProductFromItem($item);
                $supplier = Mage::helper('pickpack')->getProductAttributeValue($product, $supplierAttribute);
            }
            if (is_array($supplier))
                $supplier = implode(',', $supplier);
            if (!$supplier)
                $supplier = '~Not Set~';
            $supplierArray[] = trim(strtoupper($supplier));
        }

        $this->supplierOrderIds[$orderId] = $supplierArray;

        foreach ($itemsCollection as $item) {
            $product = Mage::helper('pickpack/product')->getProductFromItem($item);
            $sku = $product->getSku();
            $this->sku_supplier_item_action_master[$orderId] = 'keep';

            if($supplierAttribute == 'warehouse')
            {
                $warehouse_title = $item->getWarehouseTitle();
                $warehouse = $item->getWarehouse();
                $warehouse_code = $warehouse->getData('code');
                $supplier = $warehouse_code;
            }
            else
                $supplier = Mage::helper('pickpack')->getProductAttributeValue($product, $supplierAttribute);

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

            if (!isset($supplier_master[$supplier])) {
                $supplier_master[$supplier] = $supplier;
                if (array_search($supplier, $this->_suppliers) === false)
                    $this->_suppliers[] = $supplier;
            }

            $this->sku_supplier_item_action[$supplier][$sku] = 'keep';
            // if set to filter and a name and this is the name, then print
            foreach ($supplierArray as $supplier) {
                if ($supplierFilterOptions == 'filter' && isset($supplierLogin) && ($sku_supplier[$sku] == strtoupper($supplierLogin)) && ($sku_supplier[$sku] == strtoupper($supplier)))
                    $this->sku_supplier_item_action[$supplier][$sku] = 'keep';
                elseif ($supplierFilterOptions == 'filter' && isset($supplierLogin) && ($supplierLogin != '') && ($sku_supplier[$sku] != strtoupper($supplierLogin)))
                    $this->sku_supplier_item_action[$supplier][$sku] = 'hide';
                elseif ($supplierFilterOptions == 'grey' && isset($supplierLogin) && ($sku_supplier[$sku] == strtoupper($supplierLogin)))
                    $this->sku_supplier_item_action[$supplier][$sku] = 'keep';
                elseif ($supplierFilterOptions == 'grey' && isset($supplierLogin) && $supplierLogin != '' && ($sku_supplier[$sku] != strtoupper($supplierLogin)))
                    $this->sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
                elseif ($supplierFilterOptions == 'grey' && (!isset($supplierLogin) || $supplierLogin == '') && ($sku_supplier[$sku] != strtoupper($supplier)))
                    $this->sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
                elseif ($supplierFilterOptions == 'filter' && (!isset($supplierLogin) || $supplierLogin == '') && ($sku_supplier[$sku] != strtoupper($supplier))) {
                    $this->sku_supplier_item_action[$supplier][$sku] = 'hide';
                    if(strpos($sku_supplier[$sku], ',')) {
                        $temp_arr = explode(',',$sku_supplier[$sku]);
                        if (in_array(strtoupper($supplier), $temp_arr))
                            $this->sku_supplier_item_action[$supplier][$sku] = 'keep';
                        unset($temp_arr);
                    }
                } elseif ($supplierFilterOptions == 'grey' && (!isset($supplierLogin) || $supplierLogin == '') && ($sku_supplier[$sku] == strtoupper($supplier)))
                    $this->sku_supplier_item_action[$supplier][$sku] = 'keep';
                elseif ($supplierFilterOptions == 'filter' && (!isset($supplierLogin) || $supplierLogin == '') && ($sku_supplier[$sku] == strtoupper($supplier)))
                    $this->sku_supplier_item_action[$supplier][$sku] = 'keep';
                elseif ($supplierFilterOptions == 'grey')
                    $this->sku_supplier_item_action[$supplier][$sku] = 'keepGrey';
                elseif ($supplierFilterOptions == 'filter')
                    $this->sku_supplier_item_action[$supplier][$sku] = 'hide';

                $this->sku_supplier_item_action[$supplier][$sku] = 'keep';
            }
        }
    }

    public function nooPage($page_size = '') {
        $page = parent::nooPage($page_size);
        $this->_currentPageConfig = Mage::helper('pickpack/config')->getPageConfigArray($this->_wonder);
        return $page;
    }

    protected function printBackGroundImage($page, $storeId, $suffix_group, $x1, $y2) {
        $wonder = $this->getWonder();
        $pageConfig = $this->getCurrentPageConfig();
        $page_top = $pageConfig['page_top'];
        $full_page_width = $pageConfig['full_page_width'];

        if ($wonder != 'wonder')
            $sub_folder = 'background_invoice';
        else
            $sub_folder = 'background_pack';

        /*****************Config for background image***************/
        $page_background_image_yn = $this->_getConfig('page_background_image_yn', 1, false, $wonder, $storeId);
        $page_background_position = $this->_getConfig('page_background_position', 1, false, $wonder, $storeId);
        $page_background_resize = $this->_getConfig('page_background_resize', 1, false, $wonder, $storeId);
        $page_background_nudge = explode(',', $this->_getConfig('page_background_nudge', '0,0', false, $wonder, $storeId));

        $image_simple = new SimpleImage();

        if ($page_background_image_yn == 1) {
            $filename = Mage::getStoreConfig('pickpack_options/' . $wonder . '/' . $suffix_group, $storeId);
            $helper = Mage::helper('pickpack');
            if ($filename) {
                $image_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $filename;
                if (is_file($image_path)) {
                    $image_file_name = $image_path;
                    $imageObj = $helper->getImageObj($image_path);
                    $orig_img_width = $imageObj->getOriginalWidth();
                    $orig_img_height = $imageObj->getOriginalHeight();

                    $img_width = $orig_img_width;
                    $img_height = $orig_img_height;
                    /*************************** RESIZE IMAGE BY "AUTO-RESIZE" VALUE *******************************/
                    if ($orig_img_width > $full_page_width) {
                        $img_height = ceil(($full_page_width / $orig_img_width) * $orig_img_height);
                        $img_width = $full_page_width;
                    }
                    elseif ($orig_img_height > $page_top) {
                        $temp_var = $page_top / $orig_img_height;
                        $img_height = $page_top;
                        $img_width = $temp_var * $orig_img_width;
                    }

                    if($page_background_resize == 'low'){
                        $img_width = $img_width * 72/300;
                        $img_height = $img_height * 72/300;
                    }

                    if($page_background_position == 'topleft')
                        $y2 += 10;
                    elseif($page_background_position == 'center_page'){
                        $x1 = ($full_page_width - $img_width) / 2;
                        $y2 = ($page_top + 10 - $img_height) / 2 + $img_height;
                    } else {
                        $x1 = ($full_page_width - $img_width) / 2;
                        if($page_background_resize == 'high')
                            $y2 = ($page_top - 200);
                        else
                            $y2 = ($page_top - 350);
                    }
                    $x1 = $x1 + $page_background_nudge[0] - 20;
                    $y2 = $y2 + $page_background_nudge[1] + 17;
                    $y1 = ($y2 - $img_height);
                    $x2 = ($x1 + $img_width);
                    $image_ext = '';
                    $temp_array_image = explode('.', $image_path);
                    $option_group_folder = str_replace('/','',$wonder);
                    $suffix_group_folder = str_replace('/','',$suffix_group);

                    $image_ext = array_pop($temp_array_image);
                    if (($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) {
                        if(isset($image_simple)) {
                            //Create new temp image
                            $final_image_path2 = $image_file_name;//$media_path . '/' . $image_url_after_media_path;
                            $image_source = $final_image_path2;
                            $io = new Varien_Io_File();
                            $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
                            $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage'.DS.$option_group_folder.DS.$suffix_group_folder.DS.'default');
                            $ext = substr($image_source, strrpos($image_source, '.') + 1);
                            $filename = str_replace($ext,'jpeg', $filename);
                            $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$option_group_folder.'/'. $suffix_group_folder.'/'.$filename;
                            if(($orig_img_width > $img_width*300/72) || ($orig_img_height > $img_height*300/72)) {
                                if(!(file_exists($image_target))) {
                                    $size_1 = $img_width*300/72;
                                    $size_2 = $img_height*300/72;
                                    $image_simple->load($image_source);
                                    $image_simple->resize($size_1,$size_2);
                                    $image_simple->save($image_target);
                                }
                                $image_path = $image_target;
                            }
                        }

                        $background = Zend_Pdf_Image::imageWithPath($image_path);
                        $page->drawImage($background, $x1, $y1, $x2, $y2);
                        unset($background);
                        unset($filename);
                        unset($image_path);
                    }
                }
            }
        }
        unset($image_zebra);
    }
}