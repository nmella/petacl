<?php
/**
 * 
 * Date: 22.12.15
 * Time: 12:22
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Magik_Magikfees extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Abstract
{
    public $magik_product_str = array();

    public function getSubtotalAddon($item) {
        $farr = unserialize($item->getPaymentFee());
        $Magikfee = 0;
        foreach ($farr as $fval) {
            $Magikfee += $fval[0];
        }
        if ($Magikfee != 0) {
            if ($Magikfee != '') {
                $this->magik_product_str[$item->getId()] = implode("\n", array_values(array_filter(unserialize($item->getPaymentStr()))));
            }
        }

        return $Magikfee;
    }
}