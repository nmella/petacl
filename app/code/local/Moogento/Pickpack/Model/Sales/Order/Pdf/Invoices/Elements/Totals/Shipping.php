<?php
/**
 * Date: 4/7/2016
 * Time: 11:28 AM
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals_Shipping extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals
{
    public function caculateShipping(&$total_data){
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $helper = Mage::helper('pickpack');
        $storeId = $this->getStoreId();
        $result = array();

        if ($this->packingsheetConfig['total_shipping_yn'] != 0){
            $value = $order->getShippingAmount();
            $shipping_tax = floatval($order->getData('shipping_tax_amount'));
     
	        if ($this->packingsheetConfig['total_shipping_with_tax_yn'] == 1)
                $value += $shipping_tax;

            if( ($this->packingsheetConfig['hide_zero_shipping_value'] == 0) || ($value != 0) ){
                $result[0]['key'] = 'shipping';
                $result[0]['text'] = $helper->__('Shipping');
                $result[0]['value'] = $value;
            }
        }

        //add value to grand total
        if (count($result)){
            if (isset($total_data['grand_total']))
				$total_data['grand_total'] += floatval($result[0]['value']);
            else
				$total_data['grand_total'] = floatval($result[0]['value']);
        }

        return $result;
    }
}