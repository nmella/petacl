<?php
class GoMage_Puntopagos_Model_Observer
{     

    public function changeorderStatus($observer)
    {                         
	$order = $order_id = $observer->getData('order_ids');
    $orderdata = Mage::getModel('sales/order')->load($order[0]);
	$paymentMethod = $orderdata->getPayment()->getMethodInstance()->getCode();
	if($paymentMethod == 'bancoestado' || $paymentMethod =='tbanc' || $paymentMethod =='bci'|| $paymentMethod =='banco' || $paymentMethod =='presto' || $paymentMethod =='santander'|| $paymentMethod =='Webpay_standard' || $paymentMethod =='ripley' ){
	$orderdata->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
	}
	
    }
   
}
