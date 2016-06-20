<?php

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals_Customfee extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals
{
    public function caculateCustomFee(&$total_data){
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $helper = Mage::helper('pickpack');
        $storeId = $this->getStoreId();

        $result = array();

        /****CUSTOM CODE TO ADD FEE OF Magesty_AddFees EXTENSION****/
        if (Mage::helper('pickpack')->isinstalled('Magesty_AddFees')){
            if ($order->getData('addfees')){
                $fee = array();
                $fee['key'] = 'custom_fee';
                $fee['text'] = Mage::helper('addfees')->getTotalsLabel();
                $fee['value'] = floatval($order->getData('addfees'));
                $result[] = $fee;
                unset($fee);
            }
        }
        /****END CUSTOM CODE TO ADD FEE OF Magesty_AddFees EXTENSION****/

        //add value to grand total
        if (count($result)){
            foreach ($result as $customfee){
                if (isset($total_data['grand_total']))
                    $total_data['grand_total'] += floatval($customfee['value']);
                else 
					$total_data['grand_total'] = floatval($customfee['value']);
            }
        }

        return $result;
    }
}