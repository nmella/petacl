<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Trackingnumberbarcode extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public function showBarcode() {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();

        $addressFooterXY = $this->packingsheetConfig['pickpack_shipaddress'];
        $x = $addressFooterXY[0];
        $y = $addressFooterXY[1];
        $page = $this->getPage();
        $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $this->getStoreId());
        $font_family_barcode = Mage::helper('pickpack/barcode')->getFontForType($barcode_type);
        $whiteColor = Mage::helper('pickpack/config_color')->getPdfColor('white_color');

        $barcode_font_size = $this->_getConfig('tracking_number_barcode_fontsize', 15, false, $this->getWonder(), $this->getStoreId());
        $tracking_number_barcode_nudge = explode(",", $this->_getConfig('tracking_number_barcode_nudge', '0,0', true, $this->getWonder(), $this->getStoreId()));

        $tracking_number = $this->getTrackingNumber($this->getOrder());
        if($tracking_number != ''){
            $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($tracking_number, $barcode_type);
            $barcode_font_size_action = $barcode_font_size;
            if($barcode_font_size > 18) $barcode_font_size = 15;
            $barcodeWidth = 1.35 * Mage::helper('pickpack/font')->parseString($tracking_number, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
            $page->setFillColor($whiteColor);
            $page->setLineColor($whiteColor);
            $page->drawRectangle(($x - 5 + $tracking_number_barcode_nudge[0]), ($y - 5 + $tracking_number_barcode_nudge[1] ), ($x + $barcodeWidth + 5 + $tracking_number_barcode_nudge[0]), ($y + ($barcode_font_size * 1.4) + $tracking_number_barcode_nudge[1]));
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
            $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
            $page->drawText($barcodeString, ($x + $tracking_number_barcode_nudge[0]), ($y + $tracking_number_barcode_nudge[1]), 'CP1252');
            if($barcode_font_size_action > 18)
            {
                if($barcode_font_size_action > 18 && $barcode_font_size_action <= 24) $page->drawText($barcodeString, ($x + $tracking_number_barcode_nudge[0]), ($y + $tracking_number_barcode_nudge[1] + 19), 'CP1252');
                if($barcode_font_size_action >24 && $barcode_font_size_action <= 36){
                    $page->drawText($barcodeString, ($x + $tracking_number_barcode_nudge[0]), ($y + $tracking_number_barcode_nudge[1] + 19), 'CP1252');
                    $page->drawText($barcodeString, ($x + $tracking_number_barcode_nudge[0]), ($y + $tracking_number_barcode_nudge[1] + 38), 'CP1252');
                }
            }
        }
    }
}