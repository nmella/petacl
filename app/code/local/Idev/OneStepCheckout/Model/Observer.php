<?php
class Idev_OneStepCheckout_Model_Observer
{
    public function initialize_checkout($observer)
    {
        $helper = Mage::helper('onestepcheckout/checkout');
    }

    public function salesEventSaveAdditionQuoteData($observer)
    {
        $request = Mage::getSingleton('core/app')->getRequest();
        $quote = $observer->getEvent()->getQuote();
      
        if ($request->getParam('checkbox_field', false)){
            $field_1 = $request->getParam('field_1', '');
            $field_1 = preg_replace("|[^0-9kK]|i", "", $field_1);
            if ($field_1){
              $length = strlen($field_1);
              $field_1 = substr($field_1, 0, $length - 1) . '-' . substr($field_1, $length - 1, $length);
            }

            $quote->setData('field_1', $field_1);
            $quote->setData('field_2', $request->getParam('field_2', ''));
            $quote->setData('field_3', $request->getParam('field_3', ''));
            $quote->setData('field_4', $request->getParam('field_4', ''));
            $quote->setData('field_5', $request->getParam('field_5', ''));
            $quote->setData('field_6', $request->getParam('field_6', ''));
            $quote->setData('field_7', $request->getParam('field_7', ''));
            $quote->setData('field_8', $request->getParam('field_8', ''));
        }
        return $this;
    }

    public function salesEventSaveAdditionOrderData($observer)
    { 
	       $request = Mage::getSingleton('core/app')->getRequest();
           $order =  $observer->getEvent()->getQuote();
		    

      
        if ($observer->getRequestModel()->getPost('checkbox_field')){
            $field_1 = $request->getParam('field_1', '');
            $field_1 = preg_replace("|[^0-9kK]|i", "", $field_1);
            if ($field_1){
                $length = strlen($field_1);
                $field_1 = substr($field_1, 0, $length - 1) . '-' . substr($field_1, $length - 1, $length);
            }
            $order->setData('field_1', $observer->getRequestModel()->getPost('field_1'));
            $order->setData('field_2', $observer->getRequestModel()->getPost('field_2'));
            $order->setData('field_3', $observer->getRequestModel()->getPost('field_3'));
            $order->setData('field_4', $observer->getRequestModel()->getPost('field_4'));
            $order->setData('field_5', $observer->getRequestModel()->getPost('field_5'));
            $order->setData('field_6', $observer->getRequestModel()->getPost('field_6'));
            $order->setData('field_7', $observer->getRequestModel()->getPost('field_7'));
            $order->setData('field_8', $observer->getRequestModel()->getPost('field_8'));
			
			
			
        }
        return $this;
    }

    public function salesEventConvertQuoteToOrder($observer)
    {
        $observer->getEvent()->getOrder()->setData('field_1', $observer->getEvent()->getQuote()->getData('field_1'));
        $observer->getEvent()->getOrder()->setData('field_2', $observer->getEvent()->getQuote()->getData('field_2'));
        $observer->getEvent()->getOrder()->setData('field_3', $observer->getEvent()->getQuote()->getData('field_3'));
        $observer->getEvent()->getOrder()->setData('field_4', $observer->getEvent()->getQuote()->getData('field_4'));
        $observer->getEvent()->getOrder()->setData('field_5', $observer->getEvent()->getQuote()->getData('field_5'));
        $observer->getEvent()->getOrder()->setData('field_6', $observer->getEvent()->getQuote()->getData('field_6'));
        //$observer->getEvent()->getOrder()->setData('field_7', $observer->getEvent()->getQuote()->getData('field_7'));
        //$observer->getEvent()->getOrder()->setData('field_8', $observer->getEvent()->getQuote()->getData('field_8'));
        return $this;
    }
	
	public function salesEventSaveAdditionOrderDataBackend($observer)
    { 
	    $request = Mage::getSingleton('core/app')->getRequest();
	    $order =  $observer->getEvent()->getOrder();		
		$_order_data = Mage::app()->getRequest()->getParam('order');		

        if ($_order_data['custom_field']['checkbox_field']!=''){
            $order->setData('field_1', $_order_data['custom_field']['field_1']);
            $order->setData('field_2', $_order_data['custom_field']['field_2']);
            $order->setData('field_3', $_order_data['custom_field']['field_3']);
            $order->setData('field_4', $_order_data['custom_field']['field_4']);
            $order->setData('field_5', $_order_data['custom_field']['field_5']);
            $order->setData('field_6', $_order_data['custom_field']['field_6']);
            $order->setData('field_7', $_order_data['custom_field']['field_7']);
            $order->setData('field_8', $_order_data['custom_field']['field_8']);			
        }
        return $this;
    }

}