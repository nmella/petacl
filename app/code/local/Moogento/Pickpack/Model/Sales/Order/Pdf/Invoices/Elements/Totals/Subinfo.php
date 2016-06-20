<?php

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals_Subinfo extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Totals
{
    public function getSubInfo($total_data){
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $helper = Mage::helper('pickpack');
        $storeId = $this->getStoreId();

        $result = array();

        if ($this->packingsheetConfig['total_paid_yn'] == 1)
            $result[] = $this->getTotalPaid();

        if ($this->packingsheetConfig['total_due_yn'] == 1)
            $result[] = $this->getTotalDue();

        return $result;
    }

    // this will use to get total due, it is total_sub_info
    private function getTotalDue(){
        $total_due = array();
        $order = $this->getOrder();
        $helper = Mage::helper('pickpack');

        if ($this->packingsheetConfig['total_paid_yn'] == 1) {
            $total_due['key'] = 'sub_info';
            $total_due['text'] = $helper->__('Total Due');
            $total_due['value'] = $order->getTotalDue();
        }

        return $total_due;
    }

    // this will use to get total paid, it is total_sub_info
    private function getTotalPaid(){
        $total_paid = array();
        $order = $this->getOrder();
        $helper = Mage::helper('pickpack');

        if ($this->packingsheetConfig['total_due_yn'] == 1) {
            $total_paid['key'] = 'sub_info';
            $total_paid['text'] = $helper->__('Total Paid');
            $total_paid['value'] = $order->getTotalPaid();
        }

        return $total_paid;
    }
}