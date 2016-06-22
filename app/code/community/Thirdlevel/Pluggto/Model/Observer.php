<?php
class Thirdlevel_Pluggto_Model_Observer
{

            // update item order when a order is placed (ok)
            public function placeOrder(Varien_Event_Observer $observer)
            {


                $notsave = Mage::getSingleton('core/session')->getPluggToNotSave();

                // not quee when was saved by pluggto
                if(!empty($notsave)){
                    return;
                }

                try{

                $order = $observer->getOrder();
                $items  = $order->getAllVisibleItems();

                    if(is_array($items)):
                    foreach ($items as $item)
                    {

                        $product = Mage::getModel('catalog/product')->load($item->getProductId());

                        if($product->getStockItem()->getProductTypeId() == 'configurable'){
                            // preciso saber o id da variação

                            $variacao = Mage::getModel('catalog/product')->getCollection()
                                ->addAttributeToFilter('sku',$item->getSku())
                                ->addAttributeToSelect('*')
                                ->getFirstItem();

                                Mage::getModel('pluggto/export')->decreaseProductStock($product,$item->getQtyOrdered(),$variacao);


                        } else {
                            Mage::getModel('pluggto/export')->decreaseProductStock($product,$item->getQtyOrdered());
                        }

                 }
                endif;

                return;
                } catch (exception $e){
                    Mage::helper('pluggto')->WriteLogForModule('Error','Erro sincronizar estoque: '.print_r($e->getMessage(),1));
                }

            }


             // Chamado quando um pedido é criado/alterado (ok)
			public function saveorder(Varien_Event_Observer $observer)
			{


                // not quee when was saved by pluggto
                if(!empty($notsave)){
                    return;
                }


                try{

                    $notsave = Mage::getSingleton('core/session')->getPluggToNotSave();

                    // not quee when was saved by pluggto
                    if(!empty($notsave)){
                        return;
                    }

                    $order = $observer->getOrder();
                    $orderid = $order->getId();

                    if(!empty($orderid)){
                        Mage::getModel('pluggto/export')->exportOrderToQueue($orderid);
                    }

                return;

                } catch (exception $e){

                    Mage::helper('pluggto')->WriteLogForModule('Error',print_r($e->getMessage(),1));
                }


			}
		
            // Event Fired where (ok)
			public function cancelorder(Varien_Event_Observer $observer)
			{

                try{
                    $order = $observer->getEvent()->getItem();
                    // exporta pedido
                    $orderModel = Mage::getModel('pluggto/export');
                    $orderModel->exportOrderToQueue($order->getOrderId());


                        $item = $observer->getEvent()->getItem();

                        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

                        if ($item->getId() && ($productId = $item->getProductId()) && $qty) {

                            $Product = Mage::getModel('catalog/product')->load($item->getProductId());

                            if($Product->getTypeId() == 'simple'){
                                Mage::getModel('pluggto/export')->decreaseProductStock($Product,$item->getQtyOrdered(),null,'increase');
                            }

                        }





                } catch (exception $e){
                    Mage::helper('pluggto')->WriteLogForModule('Error','Erro cancelar pedido: '.print_r($e->getMessage(),1));
                }

			}



            // Usado apenas para deletar variações do PluggTo, não produtos
            // TODO não funcionando versão 1.5, testar nas demais
            public function productDelete(Varien_Event_Observer $observer){

                $product = $observer->getProduct();

                $productids = Mage::getResourceModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

                $deletePluggto = Mage::getStoreConfig('pluggto/products/delete_pluggto');

                if(!empty($productids)){
                    try{
                        $export = Mage::getModel('pluggto/export');
                        $export->exportProductToQueue($product);
                    } catch (exception $e){
                        Mage::helper('pluggto')->WriteLogForModule('Error','Erro Produto Excluido: '.print_r($e->getMessage(),1));
                    }
                } elseif($deletePluggto){
                    try{
                        $export = Mage::getModel('pluggto/export');
                            $export->exportProductToQueue($product,false,'DEL');
                    } catch (exception $e){
                        Mage::helper('pluggto')->WriteLogForModule('Error','Erro Produto Excluido: '.print_r($e->getMessage(),1));
                    }
                }

            }


            // SINCRONIZAÇÃO MANUAL DE ESTOQUE (ok) NOT MORE IN USE
            public function stockChange(Varien_Event_Observer $observer){

                $notsave = Mage::getSingleton('core/session')->getPluggToNotSave();

                // not quee when was saved by pluggto
                if(!empty($notsave)){
                    return;
                }


                $newstock =$observer->getItem()->getData();
                $oldstock = $observer->getItem()->getOrigData();

                if(isset($oldstock['qty']) && isset($newstock['qty']) && !empty($newstock['qty']) && (int)$newstock['qty'] != (int)$oldstock['qty']){
                   $product = Mage::getModel('catalog/product')->load($newstock['product_id']);
                   Mage::getSingleton('pluggto/export')->stockUpdate($product,$newstock['qty']);
               }

            }



            // Quando clicadl em alterar atributos de produto em massa
            public function afterSaveAttribute(Varien_Event_Observer $observer){


                $notsave = Mage::getSingleton('core/session')->getPluggToNotSave();

                // not quee when was saved by pluggto
                if(!empty($notsave)){
                    return;
                }

                $productIds = $observer->getEvent()->getProductIds();

                foreach ($productIds as $id) {


                    try{
                    $product = Mage::getSingleton('catalog/product')->load($id);

                    Mage::getSingleton('pluggto/export')->exportProductToQueue($product);
                    } catch (exception $e){
                        Mage::helper('pluggto')->WriteLogForModule('Error','beforesaveAttribute: '.print_r($e->getMessage(),1));
                    }
                }

            }


            public function addMassAction($observer)
            {

                $block = $observer->getEvent()->getBlock();

                if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
                    && $block->getRequest()->getControllerName() == 'catalog_product')
                {

                    $block->addItem('pluggto', array(
                        'label' => 'Exportar para o PluggTo',
                        'url' => Mage::helper("adminhtml")->getUrl("pluggto/adminhtml_sync/manual")
                    ));
                }
            }


            // send to pluggto to actualizate
            public function aftersaveproduct(Varien_Event_Observer $observer){

                $notsave = Mage::getSingleton('core/session')->getPluggToNotSave();

                // not quee when was saved by pluggto
                if(!empty($notsave)){
                    Mage::getSingleton('core/session')->setPluggToNotSave(0);
                   return;
                }


                try{
                    $product = $observer->getProduct();
                    Mage::getSingleton('pluggto/export')->exportProductToQueue($product);
                } catch (exception $e){
                    Mage::helper('pluggto')->WriteLogForModule('Error','Aftersaveproduct: '.print_r($e->getMessage(),1));
                }
            }


            // salvar código de rastreioi do pedido (ok)
			public function shippingtrack(Varien_Event_Observer $observer)
			{

                try{
                    $track        = $observer->getEvent()->getTrack();
                    $orderModel = Mage::getModel('pluggto/export');
                    $orderModel->exportOrderToQueue($track->getOrderId());
                } catch (exception $e){
                    Mage::helper('pluggto')->WriteLogForModule(print_r($e->getMessage(),1));
                }

            }
		
			

		
}
