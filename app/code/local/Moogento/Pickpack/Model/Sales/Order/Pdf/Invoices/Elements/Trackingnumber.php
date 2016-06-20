<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Trackingnumber extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public function showTrackingNumber() {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $tracking_number_fontsize = $this->_getConfig('tracking_number_fontsize', 15, false, $this->getWonder(), $this->getStoreId());
        $tracking_number_nudge = explode(",", $this->_getConfig('tracking_number_nudge', '0,0', true, $this->getWonder(), $this->getStoreId()));

        $this->_setFont($this->getPage(), $generalConfig['font_style_body'], $tracking_number_fontsize, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        if(!isset($tracking_number_barcode_nudge))
            $tracking_number_barcode_nudge = array(0,0);
        $tracking_number = $this->getTrackingNumber($this->getOrder());

        if($tracking_number != '')
            $this->getPage()->drawText($tracking_number, ($addressFooterXY[0] + $tracking_number_nudge[0] + $tracking_number_barcode_nudge[0]), ($addressFooterXY[1] + $tracking_number_nudge[1]+ $tracking_number_barcode_nudge[1] - $tracking_number_fontsize), 'CP1252');
    }
}