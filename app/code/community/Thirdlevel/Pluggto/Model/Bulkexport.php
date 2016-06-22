<?php

class Thirdlevel_Pluggto_Model_Bulkexport extends Mage_Core_Model_Abstract
{
    protected function _construct(){
       $this->_init("pluggto/bulkexport");
    }

    public function write($id){
         $this->setProductId($id);
         $this->save();
    }

    public function runBulkExport(){

        $bulkstocks = $products = Mage::getModel('pluggto/bulkexport')->getCollection();

        foreach($bulkstocks as $bulk){
            $product = Mage::getModel('catalog/product')->load($bulk->getProductId());
            Mage::getSingleton('pluggto/export')->exportProductToQueue($product);
            $bulk->delete();
        }


    }


}
	 