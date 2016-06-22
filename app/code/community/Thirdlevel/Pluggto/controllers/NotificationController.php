<?php


class Thirdlevel_Pluggto_NotificationController extends Mage_Core_Controller_Front_Action {
	
	private $productData;


	public function _construct() {

         parent::_construct();
    }

    public function indexAction(){

        $request = file_get_contents('php://input');
        $content = json_decode($request);

        if(is_object($content)){

            $alline = Mage::getModel('pluggto/line')->getCollection();
            $alline->addFieldToFilter('pluggtoid',$content->id);
            $alline->addFieldToFilter('status',0);
            $id = $alline->getFirstItem()->getId();

            $line = Mage::getModel('pluggto/line');

            if($id != null){
                $line->load($id);
            }

            $line->setWhat($content->type);
            $line->setPluggtoid($content->id);
            $line->setDirection('from');
            if($content->type == 'orders'){
                $line->setUrl($content->type.'/'.$content->id);
            } else {
                $line->setUrl($content->type.'/'.$content->id);
            }

            $line->setOpt('GET');
            $line->setReason($content->action);
            $line->setCreated(date("Y-m-d H:i:s"));
            $line->save();

        }
    }

    public function playAction(){
        Mage::getSingleton('pluggto/bulkexport')->runBulkExport();
        Mage::getSingleton('pluggto/line')->playline();
        Mage::getSingleton('pluggto/line')->clearQueue();
    }

    public function clearLineAction(){
        Mage::getSingleton('pluggto/line')->clearQueue();
    }

    public function versionAction(){
       echo Mage::getConfig()->getNode()->modules->Thirdlevel_Pluggto->version;
    }

    public function forceSyncProductsAction(){
        Mage::getSingleton('pluggto/product')->syncPriceStock();
    }

    public function forceSyncOrdersAction(){
        Mage::getSingleton('pluggto/order')->forceSyncOrders();
    }

    public function resetAction(){

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->query("DELETE FROM core_resource WHERE code = 'pluggto_setup'");


    }


}

?>