<?php
/**
 * 
 * Date: 04.12.15
 * Time: 10:56
 */


class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Headerbar extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public $headerBarXY;
    public $subheader_start;
    public $companyVertLineY1;
    public $logoPosition;
    public $headerLogoY;
    public $title_date_xpos;
    public $isShipment;

    private $invoice_number_display = '';
    private $order_number_display = '';

    public function __construct($arguments) {
        parent::__construct($arguments);
        $pageConfig = $this->getPageConfig();
        $this->headerBarXY = array($pageConfig['orderIdX'], $pageConfig['orderIdY']);
    }

    public function showHeaderBar() {
        $pageConfig = $this->getPageConfig();
        $page = $this->getPage();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $order = $this->getOrder();

		if($this->packingsheetConfig['letterhead'] == 0){
			$show_top_logo_yn = 0;
		} else {
			$show_top_logo_yn = $this->packingsheetConfig['pickpack_packlogo'];
		}
        $page_title_nuge = explode(',', trim($this->_getConfig('title_pattern_nudge', '0,0', false, $wonder, $storeId)));

        $float_top_address_yn = 0;
        if ($this->logoPosition !== 'right') {
            $float_top_address_yn = 0;
        }

        if ($this->headerLogoY) {
            if(!is_null($this->companyVertLineY1) && ($this->headerBarXY[1] > $this->companyVertLineY1))
                $this->headerBarXY[1] = $this->headerLogoY - ($this->generalConfig['font_size_company'] * 4);
            else
                $this->headerBarXY[1] = $this->headerLogoY - ($this->generalConfig['font_size_company'] * 1.5);
        }
        else
            $this->headerBarXY[1] = ( $pageConfig['page_top'] - ($this->generalConfig['font_size_company'] * 7) ); 
		//@TODO set padding here based on company address height

		// shifts up the titlebar if we're not printing borders
		if ($this->generalConfig['background_color_subtitles'] == '#FFFFFF') {
		   $this->headerBarXY[0] -= 11;
		   $this->headerBarXY[1] += 11;
		}

        /*******PRINT TOP BREAK LINE TITLE BAR*******/
        if (strtoupper($this->generalConfig['background_color_subtitles']) != '#FFFFFF') {
            if ($this->generalConfig['fill_bars_subtitles'] == 1){
                $this->printTopLineTitleBar($page);
                $this->printBottomLineTitleBar($page);
            } elseif ($this->generalConfig['fill_bars_subtitles'] == 0)
                $this->printTitleBarBackGroundColor($page);
        }
        /*******END PRINT TOP BREAK LINE TITLE BAR*******/

        /**DATE    */
        $this->invoice_number_display = '';

        foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
            if ($_tmpInvoice->getIncrementId()) {
                if ($this->invoice_number_display != '')
					$this->invoice_number_display .= ',';
                $this->invoice_number_display .= $_tmpInvoice->getIncrementId();
            }
            break;
        }

        if ($wonder == 'wonder')
			$this->order_number_display = $order->getRealOrderId();
        elseif ($wonder == 'wonder_invoice' && $this->invoice_number_display != '')
            $this->order_number_display = $this->invoice_number_display;

        $this->_setFont($page, $this->generalConfig['font_style_subtitles'], $this->generalConfig['font_size_subtitles'], $this->generalConfig['font_family_subtitles'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_subtitles']);

        if ($this->packingsheetConfig['pickpack_title_pattern'] != '') {
            // If small logo, make sure Invoice Title/Date start below height of raised address (if address has been brought up)
            if ($float_top_address_yn == 1 && (($this->hasBillingAddress() == 1) || ($this->hasShippingAddress() == 1)) && ($this->headerBarXY[1] > ($pageConfig['page_top'] - ($this->generalConfig['font_size_body'] * 15))))
                $this->headerBarXY[1] = ($pageConfig['page_top'] - ($this->generalConfig['font_size_body'] * 15));

            $title_start_X = $this->headerBarXY[0] + $page_title_nuge[0];
            $title_start_Y = $this->headerBarXY[1] + $page_title_nuge[1];

            $this->prepareTitleBarText($order);

            $invoice_title_temp = $this->order_number_display;
            $invoice_title_temp = explode("\n", $invoice_title_temp);
            $title_line_count = 0;
            foreach ($invoice_title_temp as $title_line) {
                $page->drawText(trim($title_line), $title_start_X, ($title_start_Y - ($this->generalConfig['font_size_subtitles'] * $title_line_count)), 'UTF-8');
                $title_line_count++;
            }
            
        }
        else
            $page->drawText($this->order_number_display, $this->headerBarXY[0], $this->headerBarXY[1], 'UTF-8');

        $this->setX1($this->headerBarXY[0]);
        $this->setY1($this->headerBarXY[1]);
    }

    function showHeaderBar2() {
        $page = $this->getPage();
        $pageConfig = $this->getPageConfig();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $order = $this->getOrder();

        $this->generalConfig['date_format'] = $this->_getConfig('date_format', 'M. j, Y', false, 'general', $storeId);
        $font_size_company = $this->_getConfig('font_size_company', 8, false, 'general', $storeId);

        $invoice_title = $this->_getConfig('pickpack_title_pattern', 0, false, $wonder, $storeId, false);
        $order_or_invoice = $this->_getConfig('orderorinvoice', 'order', false, $wonder, $storeId);
        $order_or_invoice_date = $this->_getConfig('orderorinvoicedate', 'order', false, $wonder, $storeId);

        $invoice_title_temp = $invoice_title;
        $invoice_title_temp = explode("\n", $invoice_title_temp);
        $invoice_title_linebreak = count($invoice_title_temp);

        $float_top_address_yn = 0;
        $fill_product_header_yn = 1;

        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($this->generalConfig['background_color_subtitles']);

        $fillbar_padding = explode(",", $this->generalConfig['fillbar_padding']);
        $line_widths = explode(",", $this->generalConfig['bottom_line_width']);

        $this->subheader_start -= ($this->generalConfig['font_size_body'] + 2);
        $this->subheader_start -= ($pageConfig['vertical_spacing'] + 3);
		$this->titlebar_base_y = null;
        if (strtoupper($this->generalConfig['background_color_subtitles']) != '#FFFFFF') {
            $page->setFillColor($background_color_subtitles_zend);
            $page->setLineColor($background_color_subtitles_zend);
            $page->setLineWidth(0.5);

            switch ($this->generalConfig['fill_bars_subtitles']) {
                case 0:
                    $page->drawRectangle($pageConfig['padded_left'], ceil($this->subheader_start - ($this->generalConfig['font_size_subtitles'] / 2)), $pageConfig['padded_right'], ceil($this->subheader_start + $this->generalConfig['font_size_subtitles'] + 2));
                    break;
                case 1:
                    if ($invoice_title_linebreak <= 1 && ($line_widths[0] > 0 || $line_widths[1] > 0)) {
                        $bottom_fillbar = ceil($this->subheader_start - ($this->generalConfig['font_size_subtitles'] / 2)) - $fillbar_padding[1];
                        $this->titlebar_base_y = $bottom_fillbar;
                        $top_fillbar = ceil($this->subheader_start + $this->generalConfig['font_size_subtitles'] + 2) + $fillbar_padding[0];
						
                        if($line_widths[0] > 0){
                            $page->setLineWidth($line_widths[0]-0.5);
                            $page->drawLine($pageConfig['padded_left'], $top_fillbar, $pageConfig['padded_right'], $top_fillbar);
                        }
                        if($line_widths[1] > 0){
                            $page->setLineWidth($line_widths[1]-0.5);
                            $page->drawLine($pageConfig['padded_left'], $bottom_fillbar, $pageConfig['padded_right'], $bottom_fillbar);
                        }
                    }
                    break;
                case 2:
                    break;
            }
        }

        $order_date = '';
        if ($order_or_invoice_date == 'order') {
            if ($this->isShipment && ($order->getCreatedAtStoreDate())) {
                $order_date = 'n/a';
                $dated = $order->getCreatedAt();
                $dated_timestamp = strtotime($dated);

                if ($dated != '') {
                    $dated_timestamp = strtotime($dated);
                    if ($dated_timestamp != false)
                        $order_date = Mage::getModel('core/date')->date($this->generalConfig['date_format'], $dated_timestamp);
					else {
                        $locale_timestamp = Mage::getModel('core/date')->timestamp(strtotime($order->getCreatedAt()));
                        if ($locale_timestamp != false) 
							$order_date = Mage::getModel('core/date')->date($this->generalConfig['date_format'], $locale_timestamp);
                    }
                }
            }
        } elseif ($order_or_invoice_date == 'invoice') {
            if ($order->getCreatedAtStoreDate()) {
                $_invoices = $order->getInvoiceCollection();
                foreach ($_invoices as $_invoice) {
                    $invoiceIncrementId = $_invoice->getIncrementId();
                    $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceIncrementId);
                    $dated = $invoice->getCreatedAt();
                    if ($dated != '') {
                        $dated_timestamp = strtotime($dated);
                        $order_date = date($this->generalConfig['date_format'], $dated_timestamp);
                    }
                    break;
                }

            }
        } elseif ($order_or_invoice_date == 'today')
			$order_date = date($this->generalConfig['date_format'], Mage::getModel('core/date')->timestamp(time()));

        $this->invoice_number_display = '';
        $this->order_number_display = '';

        foreach ($order->getInvoiceCollection() as $_tmpInvoice) {
            if ($_tmpInvoice->getIncrementId()) {
                if ($this->invoice_number_display != '')
					$this->invoice_number_display .= ',';
                $this->invoice_number_display .= $_tmpInvoice->getIncrementId();
            }
            break;
        }

        if ($order_or_invoice == 'order') 
			$this->order_number_display = $order->getRealOrderId();
        elseif ($order_or_invoice == 'invoice' && $this->invoice_number_display != '')
            $this->order_number_display = $this->invoice_number_display;

        $this->_setFont($page, $this->generalConfig['font_style_subtitles'], $this->generalConfig['font_size_subtitles'], $this->generalConfig['font_family_subtitles'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_subtitles']);

        if ($this->packingsheetConfig['pickpack_title_pattern'] != '') {
            // If small logo, make sure Invoice Title/Date start below height of raised address (if address has been brought up)
            if ($float_top_address_yn == 1 && (($this->hasBillingAddress() == 1) || ($this->hasShippingAddress() == 1)) && ($this->headerBarXY[1] > ($pageConfig['page_top'] - ($this->generalConfig['font_size_body'] * 15))))
                $this->headerBarXY[1] = ($pageConfig['page_top'] - ($this->generalConfig['font_size_body'] * 15));

            $title_start_X = $this->headerBarXY[0];
            $this->prepareTitleBarText($order);
            $page->drawText($this->order_number_display, $title_start_X, $this->subheader_start, 'UTF-8');
            
        } else
			$page->drawText($this->order_number_display, $this->headerBarXY[0], $this->subheader_start, 'UTF-8');

        $this->subheader_start -= ($this->generalConfig['font_size_subtitles'] / 2);
    }

    private function printTopLineTitleBar($page){
        $pageConfig = $this->getPageConfig();
        $fillbar_padding = explode(",", $this->generalConfig['fillbar_padding']);
        $line_widths = explode(",", $this->generalConfig['bottom_line_width']);

        $top_fillbar = ceil($this->headerBarXY[1] + $this->generalConfig['font_size_subtitles'] + 2) + $fillbar_padding[0];
        if($line_widths[0] > 0){
            $background_color_subtitles_zend = new Zend_Pdf_Color_Html($this->generalConfig['background_color_subtitles']);
            $page->setFillColor($background_color_subtitles_zend);
            $page->setLineColor($background_color_subtitles_zend);
            $page->setLineWidth($line_widths[0]-0.5);
            $page->drawLine($pageConfig['padded_left'], $top_fillbar, $pageConfig['padded_right'], $top_fillbar);
        }
    }

    private function printBottomLineTitleBar($page){
        $pageConfig = $this->getPageConfig();
        $fillbar_padding = explode(",", $this->generalConfig['fillbar_padding']);
        $line_widths = explode(",", $this->generalConfig['bottom_line_width']);

        $bottom_fillbar = ceil($this->headerBarXY[1] - ($this->generalConfig['font_size_subtitles'] / 2)) - $fillbar_padding[1];

        if($line_widths[1] > 0){
            $background_color_subtitles_zend = new Zend_Pdf_Color_Html($this->generalConfig['background_color_subtitles']);
            $page->setFillColor($background_color_subtitles_zend);
            $page->setLineColor($background_color_subtitles_zend);
            $page->setLineWidth($line_widths[1]-0.5);
            $page->drawLine($pageConfig['padded_left'], $bottom_fillbar, $pageConfig['padded_right'], $bottom_fillbar);
        }
    }

    private function printTitleBarBackGroundColor($page){
        $pageConfig = $this->getPageConfig();
        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($this->generalConfig['background_color_subtitles']);
        $page->setFillColor($background_color_subtitles_zend);
        $page->setLineColor($background_color_subtitles_zend);
        $page->setLineWidth(0.5);
        $page->drawRectangle($pageConfig['padded_left'], ceil($this->headerBarXY[1] - ($this->generalConfig['font_size_subtitles'] / 2)), $pageConfig['padded_right'], ceil($this->headerBarXY[1] + $this->generalConfig['font_size_subtitles'] + 2));
    }

    private function prepareTitleBarText($order){
        $storeId = $this->getStoreId();

        $invoice_title = $this->packingsheetConfig['pickpack_title_pattern'];
        $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($storeId, $this->generalConfig['date_format']);
        $order_date = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $this->generalConfig['date_format']);

        /*****GET ORDER DATE*****/
        ////Order date. n/a if empty
        $order_date_title = 'n/a';
        $dated_title = $order->getCreatedAt();
        if ($dated_title != '') {
            $order_date_title = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $this->generalConfig['date_format']);
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
        /*****END GET ORDER DATE*****/

        /*****GET INVOICE DATE*****/
        //////////// Invoice date  n/a if empty
        if ($order->getCreatedAtStoreDate()) {
            $invoice_date_title = Mage::helper('pickpack/functions')->createInvoiceDateByFormat($order, $date_format_strftime, $this->generalConfig['date_format']);
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
        /*****END GET INVOICE DATE*****/

        /*****GET INVOICE ID*****/
        if ($this->invoice_number_display == '') {
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
        } else {
            $invoice_title = str_replace("{{if invoice_id}}", '', $invoice_title);
            $invoice_title = str_replace("{{endif invoice_id}}", '', $invoice_title);
        }
        /*****END GET INVOICE ID*****/

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
        } else
            $warehouse_title = '';
        /*****  Get Warehouse information ****/

        if ($date_format_strftime !== true)
            $printing_date_title = date($this->generalConfig['date_format'], Mage::getModel('core/date')->timestamp(time()));
        else
            $printing_date_title = strftime($this->generalConfig['date_format'], Mage::getModel('core/date')->timestamp(time()));
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
        $arr_1 = array('{{order_date}}', '{{invoice_date}}', '{{printing_date}}', '{{order_id}}', '{{invoice_id}}', '{{marketplace_order_id}}', '{{ebay_sales_number}}');

        $arr_2 = array($order_date_title, $invoice_date_title, $printing_date_title, $order_number_display_title, $this->invoice_number_display, $marketPlaceOrderId, $ebay_sale_number);

        $invoice_title_print = str_replace($arr_1, $arr_2, $invoice_title);

        $this->order_number_display = $invoice_title_print;
    }
}