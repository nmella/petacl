<?php

class Thirdlevel_Pluggto_Model_Export extends Mage_Core_Model_Abstract
{

    // COMMON
    protected function writeToQueue($what,$resource,$body,$opt,$rewrite=true,$pluggtoid=null,$storeid=null)
    {

        $newversion = Mage::getStoreConfig('pluggto/configs/magento_old_version');

        // caso possa fazer apenas uma chamada
        if ($rewrite && !$newversion):

            $alline = Mage::getModel('pluggto/line')->getCollection();
            $alline->addFieldToFilter('url', $resource)
                    ->addFieldToFilter('what', $what)
                    ->addFieldToFilter('status',0);

            if (!is_null($storeid)) {
                $alline->addFieldToFilter('storeid', $storeid);
            }

            $id = $alline->getFirstItem()->getId();

        endif;

        $line = Mage::getModel('pluggto/line');

        if (isset($id) && $id != null) {
            $line->load($id);
        }

        $line->setWhat($what);
        $line->setUrl($resource);
        $line->setStoreid($storeid);
        $line->setPluggtoid($pluggtoid);
        $line->setOpt($opt);
        $line->setDirection('to');
        $line->setCode('');
        $line->setStatus(0);
        $line->setResult('');
        $line->setCreated(date("Y-m-d H:i:s"));
        if(!empty($body)){
            $line->setBody(json_encode($body));
        }

        $line->save();

    }

    /* STOCK UPDATE (Not more in use
    public function stockUpdate($product,$qtd){


        // check website before send product
        $StoreId = Mage::getStoreConfig('pluggto/products/product_store_id');

        // if empety, should not be send
        if(!empty($StoreId)){
            $store = Mage::getModel('Core/store')->load($StoreId);
            if(!in_array($store->getWebsiteId(),$product->getWebsiteIds())){
                return;
            }
        }



        // verifica se possui pai
        $productids = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($product->getEntityId());



        // caso possua, envia cada pai, envia anuncio com variacao
        if(!empty($productids)){

            foreach($productids as $productid){

                $parentProduct = Mage::getModel('catalog/product')->load($productid);

                $old = Mage::getSingleton('pluggto/product')->getProductInPluggto($parentProduct);


                if(!isset($old['id']) &&  $parentProduct->getPluggtoId() != null && $product->getPluggtoId() != null){

                    $body = array (
                        'action' => 'update',
                        'quantity' => $qtd
                    );


                    $url = 'products/'.$parentProduct->getPluggtoId().'/variation/'.$product->getPluggtoId().'/stock';

                    $this->writeToQueue('stock/update',$url,$body,'PUT',false,$parentProduct->getPluggtoId(),$productid);
                }

            }
            // é um produto simples
        } else {


            if($product->getPluggtoId() != null){

                $body = array (
                    'action' => 'update',
                    'quantity' => $qtd
                );

                $url = 'products/'.$product->getPluggtoId().'/stock';

                $this->writeToQueue('stock/update',$url,$body,'PUT',false,$product->getPluggtoId(),$product->getEntityId());

            }

        }
    }
    */

    // STOCK DECREASE
    public function decreaseProductStock($product,$qtd,$variation=null,$type='decrease'){



        // se não tiver um produto retorna.
        if($product->getEntityId() == null){
            return;
        }


        // check website before send product
        $StoreId = Mage::getStoreConfig('pluggto/products/product_store_id');

        // if empety, should not be send
        if(!empty($StoreId)){
            $store = Mage::getModel('Core/store')->load($StoreId);
            if(!in_array($store->getWebsiteId(),$product->getWebsiteIds())){
                return;
            }
        }



        if($variation != null){

            $url = 'skus/'.trim($variation->getSku()).'/stock';

            $body = array (
                'action' => $type,
                'quantity' => $qtd
            );

            $this->writeToQueue('stock/update',$url,$body,'PUT',false,$product->getPluggtoId(),$product->getEntityId());

        } else {

            $url = 'skus/'.trim($product->getSku()).'/stock';
            $body = array (
                'action' => $type,
                'quantity' => $qtd
            );


            $this->writeToQueue('stock/update',$url,$body,'PUT',false,$product->getPluggtoId(),$product->getEntityId());
        }

    }

    // PRODUCT

    public function exportProductToQueue($product,$forceSimple=false,$type='PUT'){

        if($product->getEntityId() == null){
            return;
        }

        // check website before send product
        $StoreId = Mage::getStoreConfig('pluggto/products/product_store_id');


        // if empety, should not be send
        if(!empty($StoreId)){

            $store = Mage::getModel('Core/store')->load($StoreId);

            if(!in_array($store->getWebsiteId(),$product->getWebsiteIds())){
                return;
            }
        }

        $exportToPluggTo = $product->getExportPluggto();


        if($exportToPluggTo == false){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pluggto')->__('O Produto ') . $product->getSku() . Mage::helper('pluggto')->__(' está configurado para não ser exportado para o Pluggto.'));
            return;
        }

        $productids = Mage::getResourceSingleton('catalog/product_type_configurable')
            ->getParentIdsByChild($product->getEntityId());

          // é um produto configuravel
        if(!empty($productids) && !$forceSimple){

            foreach($productids as $opid){

                $productParent = Mage::getModel('catalog/product')->load($opid);

                if($productParent->getEntityId() != null){

                    // avoid to sent to pluggto a configurable product that is not really a configurable product
                    if($productParent->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE){
                        $idfound = $opid;
                        break;
                    }

                }

            }



                if(isset($idfound)){
                    $url = 'skus/'.rawurlencode(trim($productParent->getSku()));
                    $this->writeToQueue('products',$url,null,$type,true,$productParent->getEntityId(),$idfound);
                } else {

                    $this->exportProductToQueue($product,true,$type);
                }

        // é um produto simples
        } else {

            $exportVisibles = Mage::getStoreConfig('pluggto/products/export_not_visible');

            if($product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE && !$exportVisibles){
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('pluggto')->__('O Produto ') . $product->getSku() . Mage::helper('pluggto')->__(' não será exportado para o Pluggto pois está configurado para não ser exibido individualmente'));
                return;
            }



                // Alwayras try to put, if not find will be a post after
                $url = 'skus/'.rawurlencode(trim($product->getSku()));
                $this->writeToQueue('products',$url,null,$type,true,null,$product->getEntityId());
        }


    }

    public function exportOrderToQueue($orderid,$status = false){

        $order = Mage::getModel('sales/order');
        $order->load($orderid);
        $new = false;

        // verifica se pedido existe
        if ($order->getEntityId() == null) {
            Mage::helper('pluggto')->WriteLogForModule('Error', 'Pedido não encontrado');
            return;
        }


        // verifica se pedido é novo, caso positivo, verifica se pode ser enviado
        if($order->getExtOrderId() == null && $order->getPluggId() == null){

            if(!Mage::getStoreConfig('pluggto/orders/allowsend')){
                return;
            }

            $new = true;
        }

        $body = Mage::getSingleton('pluggto/order')->update($order,$new,$status);


        if($order->getPluggId() != null){

            if($new){
                $resource = 'orders';
                $opt = 'POST';
                $pluggId = null;
            } else {
                $resource = 'orders/'.$order->getPluggId();
                $opt = 'PUT';
                $pluggId = $order->getPluggId();
            }

        } else {

            if($new){
                $resource = 'orders';
                $opt = 'POST';
                $pluggId = null;
            } else {
                $resource = 'orders/'.$order->getExtOrderId();
                $opt = 'PUT';
                $pluggId = $order->getExtOrderId();
            }
        }


        $this->writeToQueue('orders',$resource,$body,$opt,true,$pluggId,$orderid);

    }


}