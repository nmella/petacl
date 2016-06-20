<?php
/**
 * 
 * Date: 20.12.15
 * Time: 14:53
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Moogento_Courierrules extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Abstract
{
    public function showCourierRulesLabel() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();

        try{
            $show_courierrules_label_nudge = explode(',',$this->_getConfig('show_courierrules_shipping_label_nudge', '50,50', false, $wonder, $storeId));
            $show_courierrules_label_dimension = explode(',',$this->_getConfig('show_courierrules_shipping_label_dimension', '0,0', false, $wonder, $storeId));
            $x1 = $pageConfig['padded_left'] + $show_courierrules_label_nudge[0];
            $y1 = $pageConfig['page_bottom'] + $show_courierrules_label_nudge[1];
            $x2 = $x1 + $show_courierrules_label_dimension[0];
            $y2 = $y1 + $show_courierrules_label_dimension[1];

            $labels = Mage::helper('moogento_courierrules/connector')->getConnectorLabels($order);
            $i = 0;
            foreach($labels as $label) {
                if($i > 0) {
                    $page = $this->newPage();
                }
                $tmpFile = Mage::helper('pickpack')->getConnectorLabelTmpFile($label);
                $imageObj = Zend_Pdf_Image::imageWithPath($tmpFile);
                $page->drawImage($imageObj, $x1 , $y1,  $x2, $y2);
                unset($tmpFile);
                $i++;
            }
        }
        catch(Exception $e) {
        }
    }
}