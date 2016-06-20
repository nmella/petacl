<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Courierrules extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public function showTopBarcode($y) {
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $page = $this->getPage();
        $wonder = $this->getWonder();
        $generalConfig = $this->getGeneralConfig();

        $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $storeId);
        $bottom_barcode_nudge = explode(",", $this->_getConfig('bottom_barcode_nudge', '0,0', true, $wonder, $storeId));
        $barcode_nudge = explode(",", $this->_getConfig('barcode_nudge', '0,0', true, $wonder, $storeId));
        $black_color = Mage::helper('pickpack/config_color')->getPdfColor('black_color');

        switch ($barcode_type) {
            case 'code128':
                $font_family_barcode = 'Code128bWin.ttf';
                break;

            case 'code39':
                $font_family_barcode = 'CODE39.ttf';
                break;

            case 'code39x':
                $font_family_barcode = 'CODE39X.ttf';
                break;

            default:
                $font_family_barcode = 'Code128bWin.ttf';
                break;
        }


        $showBarCode = $this->_getConfig('pickpack_packbarcode', 0, false, $wonder, $storeId);
        if ($showBarCode) {

            $config_values['barcode_type'] = $barcode_type;
            $config_values['font_family_barcode'] = $font_family_barcode;
            $config_values['barcode_nudge'] = $barcode_nudge;
            $config_values['black_color'] = $black_color;
            $config_values['padded_right'] = $pageConfig['padded_right'];
            $config_values['font_size_body'] = $generalConfig['font_size_body'];
            $barcode_text = '';
            if($showBarCode == 1)
                $barcode_text = $order->getRealOrderId();
            else
                if($showBarCode == 2)
                {
                    if ($order->hasInvoices()) {
                        $invIncrementIDs = array();
                        foreach ($order->getInvoiceCollection() as $inv) {
                            $invIncrementIDs[] = $inv->getIncrementId();
                        }
                        $barcode_text = implode(',',$invIncrementIDs);
                    }
                }
                else
                    if($showBarCode == 3)
                        $barcode_text  = $this->getMarketPlaceId($order);
            if($barcode_text != '')
                $this->_showTopBarcode($page,$barcode_text,$config_values,$y, $pageConfig['padded_right']);
            $page->setFillColor($black_color);
            Mage::helper('pickpack/font')->setFontRegular($page, $generalConfig['font_size_body']);
        }
    }
}