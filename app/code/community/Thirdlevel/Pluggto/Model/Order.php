<?php

class Thirdlevel_Pluggto_Model_Order extends Mage_Core_Model_Abstract
{

    public $weight;
    public $totalqtd;
    public $configs;

    protected function _construct(){

       $this->_init("pluggto/order");
    }

    public function getConfig(){

        if(empty($this->configs)){
            $this->configs = Mage::helper('pluggto')->config();
        }

        return $this->configs;
    }


    // create item
    private function importItem($unitem)
    {


        $items = new Mage_Sales_Model_Order_Item();

        if(isset($unitem['variation']['sku'])){
            $sku = $unitem['variation']['sku'];
        } elseif(isset($unitem['sku'])) {
            $sku = $unitem['sku'];
        }



        if(isset($sku)){
            $product = Mage::getModel('pluggto/product')->findProduct($sku);
        }	


        if(!$product && isset($unitem['sku'])){
        	$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$unitem['sku']);
        }






        if(isset($product) && is_object($product) && $product->getEntityId() != null){
            $items->setProductId($product->getEntityId());
            $items->setProductType($product->getTypeId());
            $items->setProductWeight($product->getWeight());
            $this->weight += $product->getWeight();
        }




        $items->setBaseWeeeTaxAppliedAmount(0);
        $items->setBaseWeeeTaxAppliedRowAmnt(0);
        $items->setWeeeTaxAppliedAmount(0);
        $items->setWeeeTaxAppliedRowAmount(0);
        $items->setWeeeTaxApplied(serialize(array()));
        $items->setWeeeTaxDisposition(0);
        $items->setWeeeTaxRowDisposition(0);
        $items->setBaseWeeeTaxDisposition(0);
        $items->setBaseWeeeTaxRowDisposition(0);


        if(isset($unitem['name'])) $items->setName($unitem['name']);
        if(isset($unitem['price'])) $items->setBasePrice($unitem['price']);
        if(isset($unitem['sku']) && isset($unitem['quantity'])) $items->setRowTotal($unitem['price']*$unitem['quantity']);
        if(isset($unitem['price'])) $items->setOriginalPrice($unitem['price']);
        if(isset($unitem['price'])) $items->setPrice($unitem['price']);
        if(isset($unitem['quantity'])) $items->setQtyOrdered($unitem['quantity']);
        if(isset($unitem['quantity'])) $this->totalqtd += $unitem['quantity'];
        if(isset($unitem['total']))  $items->setRowTotal($unitem['total']);
        if(isset($unitem['total']))  $items->setSubtotal($unitem['total']);

        if(isset($unitem['variation']['sku'])){
            $items->setSku($unitem['variation']['sku']);
        } elseif (isset($unitem['sku'])) {
            $items->setSku($unitem['sku']);
        }

        $attributes = array();

        if(isset($unitem['variation']['attributes']) && is_array($unitem['variation']['attributes'])){

            foreach($unitem['variation']['attributes'] as $att){
                if(isset($att['label']) && isset($att['value']['label'])) $attributes[] =  $att['label'] . ':' . $att['value']['label'] . ' ';
            }

            $items->setAdditionalData(implode(',',$attributes));

        }

        return $items;
    }


	// create at store
	public function create($data){


            if(!Mage::getStoreConfig('pluggto/orders/allowcreate')){
                return;
            }

            try {
                $customer = Mage::getModel('pluggto/customer')->getCustomer($data);
            } catch (exception $e){
                $customer = false;
            }


            $order = new Mage_Sales_Model_Order();
            $col = $order->getCollection();

            $order = $col->addFieldToFilter('plugg_id',$data['id'])->getFirstItem();

            $new = false;

            if ($order->getEntityId() == null) {
                $new = true;
                $order = new Mage_Sales_Model_Order();
            }

            if($order->getCanalId() == null){
                if(isset($data['external'][$data['created_by']])){
                    $order->setCanalId($data['external'][$data['created_by']]);
                    $order->setExtOrderId($data['external'][$data['created_by']]);
                }
             }

            if($order->getCanal() == null){
                 if(isset($data['created_by'])){
                    $api = Mage::getSingleton('pluggto/api')->load(1);
                    $canalReturn = $api->get('clientInfo/'.$data['created_by'],null,null,true);

                    if(isset($canalReturn['Body']['name'])){
                        $order->setCanal($canalReturn['Body']['name']);
                        $order->setExtOrderId($canalReturn['Body']['name'].'-'.$data['external'][$data['created_by']]);
                    }

                }
            }

            if(!empty($data['payer_email'])){
                $order->setCustomerEmail($data['payer_email']);
            } elseif(!empty($data['receiver_email'])){
                $order->setCustomerEmail($data['receiver_email']);
            } else {
                $order->setCustomerEmail('customer@email.com');
            }

            $order->setCustomerFirstname($data['payer_name']);
            $order->setCustomerLastname($data['payer_lastname']);
            $order->setPluggId($data['id']);




            if(isset($customer) && is_array($customer) && isset($customer['id'])){
                $order->setCustomerId($customer['id']);
            } else {
                $order->setCustomerIsGuest(1);
            }


            if (isset($data['payer_cpf']) && !empty($data['payer_cpf'])) {
                $order->setCustomerTaxvat($data['payer_cpf']);
            }

            if (isset($data['payer_cnpj']) && !empty($data['payer_cnpj'])) {
                $order->setCustomerTaxvat($data['payer_cnpj']);
            }

            if (isset($orderDataFromPluggto['payer_cpf']) && !empty($orderDataFromPluggto['payer_cpf'])) $document = $orderDataFromPluggto['payer_cpf'];
            if (isset($orderDataFromPluggto['payer_cnpj']) && !empty($orderDataFromPluggto['payer_cnpj'])) $document = $orderDataFromPluggto['payer_cnpj'];


            $customFieldToStoreCFPorCNPJ = Mage::getStoreConfig('pluggto/configs/custom_document_field');

            if (isset($document) && $customFieldToStoreCFPorCNPJ != '' && $customFieldToStoreCFPorCNPJ != null) {
                $order->addData(array(trim($customFieldToStoreCFPorCNPJ) => $document));
            }

            $store = Mage::getStoreConfig('pluggto/configs/default_store');

            if(!empty($store)){
                $currencies_array = Mage::app()->getStore($store)->getDefaultCurrency();
            } else {
                $currencies_array = Mage::app()->getStore()->getDefaultCurrency();
            }

            $currencycode = $currencies_array->getCurrencyCode();

            if(empty($data['subtotal']) || $data['subtotal'] == 0){
                $data['subtotal'] = $data['total'] - $data['shipping'];
            }

            // total amount informatiom
            $order->setTotalDue($data['total']);
            $order->setSubtotal($data['subtotal']);
            $order->setGrandTotal($data['total']);
            $order->setTotalDue($data['total']);
            $order->setBaseTaxAmount(0.00);
            $order->setBaseGrandTotal($data['total']);
            $order->setStoreCurrencyCode($currencycode);
            $order->setShippingAmount($data['shipping']);
            $order->setBaseShippingAmount($data['shipping']);

            $order->setBaseSubtotalInclTax($data['subtotal']);
            $order->setSubtotalInclTax($data['subtotal']);
            $order->setShippingDiscount(0);
            $order->setStoreId(Mage::getStoreConfig('pluggto/configs/default_store'));
            $order->setCurrenyCode($currencycode);
            $order->setOrderCurrencyCode($currencycode);
            $order->setGlobalCurrencyCode($currencycode);
            $order->setBaseCurrencyCode($currencycode);
            $order->setBaseSubtotal($data['subtotal']);
            $order->setBaseToOrderRate(1);
            $order->setBaseToGlobalRate(1);
            $order->setBaseTaxAmount(0);


            // billing information

           if ($order->getIncrementId()) {
            $billing = Mage::getModel('sales/order_address')->load($order->getBillingAddress()->getId());
            } else {
            $billing = new Mage_Sales_Model_Order_Address;
            }

            $billing->setFirstname($data['payer_name']);
            $billing->setLastname($data['payer_lastname']);

            $PayerAddressLine = array();

        // receiver address line
            if(!empty($data['payer_address'])) {
               $PayerAddressLine[]  = $data['payer_address'];
             } else {
              $PayerAddressLine[] = '';
             }

             if(!empty($data['payer_address_number'])){
              $PayerAddressLine[]  = $data['payer_address_number'];
            } else {
              $PayerAddressLine[]  = '';
            }

            if(!empty($data['payer_address_complement'])){

                if(!empty($data['payer_additional_info'])){
                    $PayerAddressLine[]  = $data['payer_address_complement'] . '-'. $data['payer_additional_info'];
                } else {
                    $PayerAddressLine[]  = $data['payer_address_complement'];
                }

            } else {

                if(!empty($data['payer_additional_info'])){
                    $PayerAddressLine[]  = $data['payer_additional_info'];
                } else {
                    $PayerAddressLine[]  = '';
                }
            }

            if(!empty($data['payer_neighborhood'])){
                $PayerAddressLine[]  = $data['payer_neighborhood'];
            } else {
                $PayerAddressLine[]  = '';
            }


            if(!empty($PayerAddressLine)){
                $billing->setStreet($PayerAddressLine);
            }

            if (isset($data['payer_zipcode'])) {
                $billing->setPostcode($data['payer_zipcode']);
            }
            if (isset($data['payer_city'])) {
                $billing->setCity($data['payer_city']);
            }
            if (isset($data['payer_state'])) {
                $billing->setRegion($data['payer_state']);
            }

            if(isset($data['payer_country'])) {
               $billing->setCountry($data['payer_country']);
            }

            if (isset($data['payer_phone']) && isset($data['payer_phone_area'])) {
                $billing->setTelephone($data['payer_phone_area'] . $data['payer_phone']);
            }
            if (isset($data['payer_email'])) {
                $billing->setEmail($data['payer_email']);
            }
            if (isset($data['payer_cpf'])) {
                $billing->setVatId($data['payer_cpf']);
            }

            if (isset($data['payer_cnpj'])) {
            $billing->setVatId($data['payer_cnpj']);
            }





         $billing->setCountryId($data['payer_country']);

            $regionModel = Mage::getModel('directory/region')->loadByCode($data['payer_state'],$data['payer_country']);
            $regionId = $regionModel->getId();


            $billing->setRegionId($regionId);

            if (!$order->getIncrementId()) {
                $order->setBillingAddress($billing);
            }

            // shipping information
            if ($order->getIncrementId()) {
                $shipping = Mage::getModel('sales/order_address')->load($order->getShippingAddress()->getId());
            } else {
                $shipping = new Mage_Sales_Model_Order_Address;
            }



            $shipping->setFirstname($data['receiver_name']);
            $shipping->setLastname($data['receiver_lastname']);

            $ReceiverAddressLine = array();
            // receiver address line

            if(!empty($data['receiver_address'])){
                $ReceiverAddressLine[]  = $data['receiver_address'];
            } else {
                $ReceiverAddressLine[]  = '';
            }

            if($data['receiver_address_number'] != null && $data['receiver_address_number'] != '' ){
                $ReceiverAddressLine[]  = $data['receiver_address_number'];
            } else {
                $ReceiverAddressLine[]  = '';
            }

            if(!empty($data['receiver_address_complement'])){

                if(!empty($data['receiver_additional_info'])){

                    if(!empty($data['receiver_address_reference'])){
                        $ReceiverAddressLine[]  = $data['receiver_address_complement'] . '-'. $data['receiver_additional_info'] . '-' . $data['receiver_address_reference'];
                    } else {
                        $ReceiverAddressLine[]  = $data['receiver_address_complement'] . '-'. $data['receiver_additional_info'];
                    }

                } else {

                    if (!empty($data['receiver_address_reference'])) {
                        $ReceiverAddressLine[] = $data['receiver_address_complement'] . '-' . $data['receiver_address_reference'];
                    } else {
                        $ReceiverAddressLine[] = $data['receiver_address_complement'];
                    }

                }

            } else {

                if(!empty($data['receiver_additional_info'])){

                    if(!empty($data['receiver_address_reference'])){
                        $ReceiverAddressLine[]  = $data['receiver_additional_info'] . '-' . $data['receiver_address_reference'];
                    } else {
                        $ReceiverAddressLine[]  = $data['receiver_additional_info'];
                    }

                } else {

                    if(!empty($data['receiver_address_reference'])){
                        $ReceiverAddressLine[]  = $data['receiver_address_reference'];
                    } else {
                        $ReceiverAddressLine[]  = '';
                    }
                }
            }

            if(!empty($data['receiver_neighborhood'])){
                $ReceiverAddressLine[]  = $data['receiver_neighborhood'];
            } else {
                $ReceiverAddressLine[]  = '';
            }

            if(!empty($ReceiverAddressLine)){
                $shipping->setStreet($ReceiverAddressLine);
            }

            if (isset($data['receiver_zipcode'])) {
                $shipping->setPostcode($data['receiver_zipcode']);
            }

            if (isset($data['receiver_city'])) {
                $shipping->setCity($data['receiver_city']);
            }
            if (isset($data['receiver_state'])) {
                $shipping->setRegion($data['receiver_state']);
            }
            if (isset($data['receiver_phone'])) {
                $shipping->setTelephone($data['receiver_phone']);
            }
            if (isset($data['receiver_email'])) {
                $shipping->setEmail($data['receiver_email']);
            }

            if(is_null($data['receiver_country'])){
                $data['receiver_country'] = 'BR';
            }

            $shipping->setCountryId($data['receiver_country']);


            $regionModel = Mage::getModel('directory/region')->loadByCode($data['receiver_state'],$data['receiver_country']);
            $regionId = $regionModel->getId();

            $shipping->setRegionId($regionId);

            if (!$order->getIncrementId()) {
                 $order->setShippingAddress($shipping);
            }





        $items = $order->getAllVisibleItems();


        if (!$order->getIncrementId() || count($items) == 0) {

                if (count($data['items']) == 0) {

                    $items = new Mage_Sales_Model_Order_Item();
                    $items->setBasePrice($data['subtotal']);
                    $items->setProductId(0);
                    $items->setRowTotal($data['subtotal']);
                    $items->setOriginalPrice($data['subtotal']);
                    $items->setPrice($data['subtotal']);
                    $items->setQtyOrdered();
                    $items->setProductWeight(0.1);
                    $items->setSku('pluggto');
                    $items->setName(Mage::helper('pluggto')->__('Produto do PluggTo'));
                    $order->addItem($items);

                } else {
                    foreach ($data['items'] as $unitem) {

                        if(isset($unitem['sku'])){
                        $items = $this->importItem($unitem);
                        try{
                            $order->addItem($items);
                        } catch (exception $e){
                            Mage::helper('pluggto')->WriteLogForModule('Error','Erro ao adicionar item ao pedido: '.print_r($e->getMessage(),1));
                        }

                        }
                    }
                }
             
				
                $order->setTotalQtyOrdered($this->totalqtd);
                $order->setWeight($this->weight);

            }
            

                if(!empty($data['delivery_type'])){


                    switch ($data['delivery_type']){

                        case 'standard':
                            $method = $defaultMethod = Mage::getStoreConfig('pluggto/shipping/standard');
                            break;
                        case 'express':
                            $method = $defaultMethod = Mage::getStoreConfig('pluggto/shipping/express');
                            break;
                        case 'onehour':
                            $method = $defaultMethod = Mage::getStoreConfig('pluggto/shipping/onehour');
                            break;
                        case 'pickup':
                            $method = $defaultMethod = Mage::getStoreConfig('pluggto/shipping/pickup');
                            break;
                        case 'economy':
                            $method = $defaultMethod = Mage::getStoreConfig('pluggto/shipping/economy');
                            break;
                        case 'guaranteed':
                            $method = $defaultMethod = Mage::getStoreConfig('pluggto/shipping/guaranteed');
                            break;
                        case 'scheduled':
                            $method = $defaultMethod = Mage::getStoreConfig('pluggto/shipping/scheduled');
                            break;
                        default:
                            $method = $defaultMethod = Mage::getStoreConfig('pluggto/shipping/standard');

                    }

                }

                if(empty($method)){
                    $method = Mage::getStoreConfig('pluggto/shipping/standard');
                }

                 $description = Mage::getStoreConfig("carriers/".$method."/title");

                 if(empty($description)){
                     $description = $data['delivery_type'];
                 } else {
                     $description = $description . '('.$data['delivery_type'].')';
                 }

                 $order->setShippingMethod($method);
                 $order->setShippingDescription($description);



            if(isset($data['shipments'][0]['id'])){
                $order->setShipmentId($data['shipments'][0]['id']);
            }


            // payment info
            $payment = new Mage_Sales_Model_Order_Payment();

            $storemmethod = Mage::getStoreConfig('pluggto/configs/paymentdefault');

            if(!empty($storemmethod)){
                $payment->setMethod($storemmethod);
            } else {
                $payment->setMethod('pluggto');
            }

          if(isset($data['payment_method'])){

            if ($data['payment_method']) {
                // caso pagamento tenha sido realizado por MercadoPago

                $payment->setAdditionalData($data['payment_method']);
            }
        }
        
   

        Mage::getSingleton('core/session')->setPluggToNotSave(1);
        $payment->setOrder($order);
        Mage::getSingleton('core/session')->setPluggToNotSave();



        if($new){
            $order->addPayment($payment->place());
            $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        }




		
	
        Mage::getSingleton('core/session')->setPluggToNotSave(1);
        $order->save();
        Mage::getSingleton('core/session')->setPluggToNotSave();


		

	
        
        $shipping->save();
        $billing->save();
        $invoice = false;
        switch ($data['status']) {

            case 'approved':
            case 'paid':

            $status = Mage::getStoreConfig('pluggto/orderstatus/approved');
            $state =  Mage_Sales_Model_Order::STATE_PROCESSING;
            $invoice = true;
                if (Mage::getStoreConfig('pluggto/orders/invoice') == 1)
                {
                    $notifyCustomerOrderUpdate = false;
                    Mage::getSingleton('core/session')->setPluggToNotSave(1);
                    // Cria invoice (fatura) para o pedido se já não houver alguma criada.


                    try{
                        if (!$order->hasInvoices() )
                        {

                            foreach ($order->getAllItems() as $item) {
                                $Allitems[$item->getId()] = $item->getQtyOrdered();
                            }

                            $invoice = $order->prepareInvoice();
                            $invoice->register()->pay();

                            Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();
                        }
                    } catch (exception $e){
                    }
                    Mage::getSingleton('core/session')->setPluggToNotSave();
                }




                break;
            case 'refunded':
                $state =  Mage_Sales_Model_Order::STATE_CANCELED;
                $status = Mage::getStoreConfig('pluggto/orderstatus/canceled');
                break;
            case 'pending':
                $status = Mage::getStoreConfig('pluggto/orderstatus/pending');
                $state =  Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                break;
            case 'invoiced':
                $status = Mage::getStoreConfig('pluggto/orderstatus/invoiced');
                $state =  Mage_Sales_Model_Order::STATE_PROCESSING;
                $invoice = true;
                break;
            case 'under_review':
                $status = Mage::getStoreConfig('pluggto/orderstatus/under_review');
                $state =  Mage_Sales_Model_Order::STATE_HOLDED;
                break;
            case 'canceled':
                $status = Mage::getStoreConfig('pluggto/orderstatus/canceled');
                $state =  Mage_Sales_Model_Order::STATE_CANCELED;
                break;
            case 'delivered':
                $status = Mage::getStoreConfig('pluggto/orderstatus/delivered');
                $invoice = true;
                break;
            case 'shipped':
            case 'shipping_informed':
            case 'shipping_error':
               $status = Mage::getStoreConfig('pluggto/orderstatus/shipped');
            $invoice = true;
               break;
            default:
                $status = '';
                $state =  Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
            break;
        }

        if($invoice){
            if (Mage::getStoreConfig('pluggto/orders/invoice') == 1)
            {
                $notifyCustomerOrderUpdate = false;
                Mage::getSingleton('core/session')->setPluggToNotSave(1);
                // Cria invoice (fatura) para o pedido se já não houver alguma criada.


                try{
                    if (!$order->hasInvoices() )
                    {

                        foreach ($order->getAllItems() as $item) {
                            $Allitems[$item->getId()] = $item->getQtyOrdered();
                        }

                        $invoice = $order->prepareInvoice();
                        //  $invoice->register()->pay();

                        Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();
                    }
                } catch (exception $e){
                }
                Mage::getSingleton('core/session')->setPluggToNotSave();
            }
        }

        $orderHistory = Mage::getModel('sales/order_status_history')->getCollection()
            ->addFieldToFilter('parent_id', $order->getId());

        $orderHistory  = $orderHistory->getData();
        $statusHistory = array();

        if(is_array($orderHistory)){
            foreach($orderHistory as $history) {
            $statusHistory[] = $history['status'];
            }
        }

        try{

            if(!in_array($status,$statusHistory)){

                if(isset($state)){
                    $order->setState($state);
                }

                if($order->getStatus() != $status) $order->addStatusToHistory($status,'',false);
            }


        } catch (exception $e){

        }


        Mage::getSingleton('core/session')->setPluggToNotSave(1);
        $order->save();

        Mage::dispatchEvent('sales_order_place_after', array('order'=>$order));
        Mage::getSingleton('core/session')->setPluggToNotSave();


	}

    public function getStatusId(){


    }

    public function savePluggToid($OrderFromPluggto){

        $order = Mage::getModel('sales/order')->load($OrderFromPluggto['external'],'increment_id');
        $order->setPluggId($OrderFromPluggto['id']);
        $order->save();

    }
	
	// send to pluggtoTo
	public function update($order,$new=false,$status=false){





        if($new){
            $store = Mage::app()->getStore();
            $name = $store->getName();
            $toPlugg['channel'] = $name;
            $toPlugg['original_id'] = $order->getIncrementId();
        }

        $toPlugg['external'] = $order->getIncrementId();


        if($status){
            $status = $status;
        } else {
            $status = $order->getState();
        }

        /// Set Status
        switch ($status){
            case Mage_Sales_Model_Order::STATE_PROCESSING:
                $toPlugg['status'] = 'approved';
                break;
            case Mage_Sales_Model_Order::STATE_CANCELED:
                $toPlugg['status'] = 'canceled';
                break;
            case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
                $toPlugg['status'] = 'pending';
                break;
            case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
            case Mage_Sales_Model_Order::STATE_HOLDED:
                $toPlugg['status'] = 'pending';
                break;
            case Mage_Sales_Model_Order::STATE_NEW:
                $toPlugg['status'] = 'pending';
                break;
            case Mage_Sales_Model_Order::STATE_COMPLETE:
                $toPlugg['status'] = 'shipping_informed';
                break;
            }

        // check to see if not delivered, if is, overwrite
        $DeliveryStatus = Mage::getStoreConfig('pluggto/orders/status_delivery');

        if(!empty($DeliveryStatus) && $status == $DeliveryStatus){
            $toPlugg['status'] = 'delivered';
        }

        $toPlugg['receiver_name'] = $order->getCustomerFirstname();
        $toPlugg['receiver_lastname'] = $order->getCustomerLastname();

        // shipping address

        $shiping = $order->getShippingAddress();

        $DelStree = $shiping->getStreet();


        $toPlugg['receiver_address'] = $DelStree[0];

        if(isset($DelStree[1])) $toPlugg['receiver_address_number'] = $DelStree[1];
        $toPlugg['receiver_city'] = $shiping->getCity();
        $toPlugg['receiver_state'] = $shiping->getRegion();
        $toPlugg['receiver_country'] = $shiping->getCountryId();
        $toPlugg['receiver_zipcode'] = $shiping->getPostcode();
        $toPlugg['receiver_phone'] = $shiping->getTelephone();
        $toPlugg['receiver_phone_area'] = '';
        $toPlugg['receiver_email'] = $shiping->getEmail();

        $billing = $order->getBillingAddress();

        $customer = Mage::getModel('customer/customer');
        $customerid = $billing->getCustomerId();

        if(!empty($customerid)){
            $customer->load($customerid);
            $toPlugg['payer_name'] = $customer->getName();
            $toPlugg['payer_lastname'] = $customer->getLastname();
            $toPlugg['payer_email'] =  $customer->getEmail();
        } else {
            $toPlugg['payer_name'] = $order->getCustomerFirstname();
            $toPlugg['payer_lastname'] = $order->getCustomerLastname();
            $toPlugg['payer_email'] =  $order->getCustomerEmail();
        }

        $Billstreet = $billing->getStreet();

        if(isset($Billstreet[1])) $toPlugg['payer_address_number'] = $Billstreet[1];
        $toPlugg['payer_address'] = $Billstreet[0];

        $toPlugg['payer_city'] = $billing->getCity();
        $toPlugg['payer_state'] = $billing->getRegion();
        $toPlugg['payer_country'] = $billing->getCountryId();
        $toPlugg['payer_zipcode'] = $billing->getPostcode();
        $toPlugg['payer_phone'] = $billing->getTelephone();
        $toPlugg['payer_phone_area'] = '';
        $toPlugg['payer_cpf'] = $order->getVatId();
        $toPlugg['total'] = $order->getGrandTotal();
        $toPlugg['shipping'] = $order->getShippingAmount();
        $toPlugg['subtotal'] = $order->getGrandTotal() - $order->getShippingAmount();

        $payment = $order->getPayment();
        $method = $payment->getMethood();
        $addicional = $payment->getAdditionalData();

        if(($method == 'pluggto' || empty($method)) && !empty($addicional)){
            $method = $addicional;
        }

        $toPlugg['payment_method'] = $method;

        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($order)
            ->load();

        $tracknum = array();

        $toshipmentArray = array();

        foreach ($shipmentCollection as $shipment){

                foreach($shipment->getAllTracks() as $trackns)
                {
                    if(!is_null($trackns->getDescription())) {
                        $shipping['shipping_method'] = $trackns->getDescription();
                    }
                    else
                    {
                         $shipping['shipping_method'] = $order->getShippingDescription();
                    }
                    if(!is_null($trackns->getTitle()))       $shipping['shipping_company'] = $trackns->getTitle();
                    if(!is_null($trackns->getTrackNumber())) $shipping['track_code'] = $trackns->getTrackNumber();
                    break;
                }

                    if(mageFindClassFile('Thirdlevel_Pluggto_Model_Nfe') != false){

                        $nefClass = Mage::getModel('pluggto/nfe');
                        $nef = $nefClass->getNfe($order,$shipment);
                        if(isset($nef['nfe_key']) && !empty($nef['nfe_key']))      $shipping['nfe_key']     = $nef['nfe_key'];
                        if(isset($nef['nfe_number'])&& !empty($nef['nfe_number']))  $shipping['nfe_number'] = $nef['nfe_number'];
                        if(isset($nef['nfe_serie']) && !empty($nef['nfe_serie']))   $shipping['nfe_serie']  = $nef['nfe_serie'];
                        if(isset($nef['nfe_date']) && !empty($nef['nfe_date']))     $shipping['nfe_date']   = $nef['nfe_date'];
                        if(isset($nef['nfe_key']) &&  !empty($nef['nfe_key']))      $shipping['nfe_key']    = $nef['nfe_key'];
                        if(isset($nef['nfe_link']) && !empty($nef['nfe_link']))     $shipping['nfe_link']   = $nef['nfe_link'];
                    }




                if(!is_null($shipment->getIncrementId())) $shipping['external'] = $shipment->getIncrementId();
                if(!is_null($shipment->getCreatedAt()))   $shipping['date_shipped'] = $shipment->getCreatedAt();

                 $toshipmentArray[] = $shipping;

        }


        $toPlugg['shipments'] = $toshipmentArray;


        $pluggtoId = $order->getPluggId();

        if(!empty($toPlugg['shipments'])){

            $old = Mage::getModel('pluggto/api')->get('orders/'.$pluggtoId,null,null,true);


            if(isset($old['Body']['Order']['shipments'][0]['id'])){
                $toPlugg['shipments'][0]['id'] = $old['Body']['Order']['shipments'][0]['id'];
            }

        }

        $toPlugg['purchased'] = $order->getCreatedAt();
        if($new) $toPlugg['created'] = $order->getCreatedAt();
        $toPlugg['modified'] = $order->getUpdatedAt();

        $items = $order->getAllVisibleItems();

        $i = 0;

        if($new):
        foreach($items as $item):


            $product = Mage::getModel('catalog/product')->load($item->getProductId());

            $toPlugg['items'][$i]['name']       = $item->getName();
            $toPlugg['items'][$i]['price']      = $item->getPrice();
            $toPlugg['items'][$i]['quantity']   = $item->getQtyOrdered();
            $toPlugg['items'][$i]['total']      = $item->getQtyOrdered() * $item->getPrice();
            $toPlugg['items'][$i]['sku']        = $product->getSku();
            $toPlugg['items'][$i]['external']   = $product->getId();



            if($product->getStockItem()->getProductTypeId() == 'configurable'){

                $options = $item->getProductOptions();

                try{
                    $frompluggto = Mage::getSingleton('pluggto/api')->get('products/'.$product->getPluggtoId(),null,null,true);
                } catch (exception $e){
                    Mage::helper('pluggto')->WriteLogForModule('Error', 'Item não encontrado no plugg.to');
                }



                $vari = array();
                if(isset($frompluggto['Product']['variations']) && is_array($frompluggto['Product']['variations'])){


                    foreach($frompluggto['Product']['variations'] as $varis){
                        $vari[$varis['id']] = $varis;
                    }

                }

                $subproduct = Mage::getSingleton('catalog/product')->load($product->getIdBySku($options['simple_sku']));

                $toPlugg['items'][$i]['variation']['id'] = $subproduct->getPluggtoId();
                $toPlugg['items'][$i]['variation']['sku'] =  $subproduct->getSku();
                $toPlugg['items'][$i]['variation']['name'] =  $subproduct->getName();


                if(isset($vari[$subproduct->getPluggtoId()])){

                    if(isset($vari[$subproduct->getPluggtoId()]['attributes']) && is_array($vari[$subproduct->getPluggtoId()]['attributes'])){
                        $j = 0;
                        foreach($vari[$subproduct->getPluggtoId()]['attributes'] as $attribute){
                            if(isset($attribute['code']))  $toPlugg['items'][$i]['variation']['attributes'][$j]['code'] = $attribute['code'];
                            if(isset($attribute['label'])) $toPlugg['items'][$i]['variation']['attributes'][$j]['label'] = $attribute['label'];
                            if(isset($attribute['value']['code'])) $toPlugg['items'][$i]['variation']['attributes'][$j]['value']['code'] = $attribute['value']['code'];
                            if(isset($attribute['value']['label'])) $toPlugg['items'][$i]['variation']['attributes'][$j]['value']['label'] = $attribute['value']['label'];
                        $j++;
                        }

                    }
                }
            }

            $i++;

        endforeach;
            endif;// if new

        return $toPlugg;

	}

    public function forceSyncOrders(){

        $api = Mage::getSingleton('pluggto/api');
        $post['order'] = 'desc';
        $post['limit'] = 100;
        $orders = $api->get('orders',$post,'field',true);


        foreach($orders['Body']['result'] as $order){
            $this->create($order['Order']);
        }
    }



}
