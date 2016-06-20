<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Packedby extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();
    public $packedByXY = array(0,0);

    public function __construct($arguments) {
        parent::__construct($arguments);
		
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        
		if ($this->_getConfig('packed_by_yn', 0, false, $wonder, $storeId) == 1)
            $this->packedByXY = explode(",", $this->_getConfig('packed_by_nudge', $pageConfig['packedByXYDefault'], true, $wonder, $storeId));
    }

    public function showPackedBy($firstPage) {
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $generalConfig = $this->getGeneralConfig();

        $packed_by_text = trim($this->_getConfig('packed_by_text', '', false, $wonder, $storeId));
        $this->_setFont($firstPage, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
        $firstPage->drawText(Mage::helper('pickpack')->__($packed_by_text), $this->packedByXY[0], $this->packedByXY[1], 'UTF-8');
    }
}