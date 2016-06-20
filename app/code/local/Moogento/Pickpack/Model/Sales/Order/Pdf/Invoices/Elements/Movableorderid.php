<?php
/**
 * 
 * Date: 09.12.15
 * Time: 12:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Movableorderid extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

	const ORDERID_NUDGE_X = -93;
	const ORDERID_NUDGE_Y = 27;
	
    public function showOrderId() {
        $order = $this->getOrder();
        $generalConfig = $this->getGeneralConfig();
        $page = $this->getPage();
        $pageConfig = $this->getPageConfig();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $orderId = $order->getRealOrderId();

        $orderId_font_size = $this->_getConfig('font_size_orderid', 14, false, $wonder, $storeId);
        $order_id_nudge = explode(",", $this->_getConfig('order_id_nudge', '0,0', true, $wonder, $storeId));
		$order_id_nudge[0] += self::ORDERID_NUDGE_X;
		$order_id_nudge[1] += self::ORDERID_NUDGE_Y;
		
        $this->_setFont($page, $generalConfig['font_style_body'], $orderId_font_size, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        if(isset($barcodeWidth))
            $order_id_X = $pageConfig['padded_right'] - $barcodeWidth + $order_id_nudge[0];
        else
            $order_id_X = $pageConfig['padded_right'] + $order_id_nudge[0];
        $order_id_Y = $this->getHeaderLogo()->getY2() - 20 - $orderId_font_size + $order_id_nudge[1];
        $order_Id = $order->getRealOrderId();
        $page->drawText($order_Id, $order_id_X, $order_id_Y, 'UTF-8');
    }

    public function getFontForType() {

    }
}