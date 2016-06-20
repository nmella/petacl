<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Supplier extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();
    
    public function drawSupplierBlock($supplier, $x, $y) {
        $storeId = $this->getStoreId();
        if($this->_getConfig('supplier_attribute_show_option', 0, false, "general", $storeId)) {
            $pageConfig = $this->getPdf()->getCurrentPageConfig();
            $page = $this->getPage();
            $generalConfig = $this->getGeneralConfig();
            $supplier_attributeXY = explode(",", $this->_getConfig('supplier_attribute_xpos', $x.','.$y, true, "general", $storeId));
            $fontSize = $this->_getConfig('supplier_font_size_options', 22, false, "general", $storeId);

            $supplier_attribute_text = $supplier;
            if((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse'))) {
                $supplierAttribute = Mage::helper("pickpack/config_supplier")->getSupplierAttribute($storeId);
                if($supplierAttribute == 'warehouse') {
                    if(isset($this->getPdf()->_warehouseTitle[$supplier]))
                        $supplier_attribute_text = trim(strtoupper($this->getPdf()->_warehouseTitle[$supplier]));
                }
            }
            $font = Mage::helper('pickpack/font')->getFont($generalConfig['font_style_body'], $fontSize, $generalConfig['font_family_body'], $generalConfig['non_standard_characters']);
            $page->setFillColor(new Zend_Pdf_Color_Html($generalConfig['font_color_body']));
            $page->setFont($font, $fontSize);
            $page->drawText(Mage::helper('sales')->__($supplier_attribute_text), $supplier_attributeXY[0], $supplier_attributeXY[1], 'UTF-8');
        }
    }
}