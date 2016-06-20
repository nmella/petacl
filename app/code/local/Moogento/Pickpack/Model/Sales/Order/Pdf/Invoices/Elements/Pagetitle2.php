<?php
/**
 * 
 * Date: 04.12.15
 * Time: 10:56
 */


class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Pagetitle2 extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public function showTitle() {
        $pageConfig = $this->getPageConfig();
        $invoice_title_2 = trim($this->_getConfig('pickpack_title_2', '', false, $this->getWonder(), $this->getStoreId()));
        if(strlen($invoice_title_2)) {
            $generalConfig = $this->getGeneralConfig();
            list($x,$y) = explode(',', $pageConfig['title2XYDefault']);
            $title2XY = explode(",", $this->_getConfig('pickpack_nudge_title', $x.','.$y, true, $this->getWonder(), $this->getStoreId()));
            $this->_setFont($this->getPage(), $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
            $this->getPage()->drawText($invoice_title_2, $title2XY[0], $title2XY[1], 'UTF-8');
        }
    }
}