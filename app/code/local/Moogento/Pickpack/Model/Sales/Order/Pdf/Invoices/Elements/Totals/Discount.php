<?php

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals_Discount extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals
{
    public function caculateDiscount(&$total_data){
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $helper = Mage::helper('pickpack');
        $storeId = $this->getStoreId();

        $result = array();

        if ($this->packingsheetConfig['total_discount_yn'] == 1){
            $value = $order->getDiscountAmount();
            if ($this->packingsheetConfig['hide_zero_discount_value'] == 0 || $value != 0){
                $result[0]['key'] = 'discount';
                $result[0]['text'] = $helper->__('Discount');
                $result[0]['value'] = $value;
				$result[0]['incl'] = false;
				if($this->packingsheetConfig['total_subtotal_with_discount_yn'] == 1)
					$result[0]['incl'] = true;
            }
        }elseif ($this->packingsheetConfig['total_discount_yn'] == 2){
            $value = $total_data['subtotal_discount'];
            if ($this->packingsheetConfig['hide_zero_discount_value'] == 0 || $value != 0){
                $result[0]['key'] = 'discount';
                $result[0]['text'] = $helper->__('Discount');
                $result[0]['value'] = $value;
				$result[0]['incl'] = false;
				if($this->packingsheetConfig['total_subtotal_with_discount_yn'] == 1)
					$result[0]['incl'] = true;
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