<?php
class Thirdlevel_Pluggto_Adminhtml_SyncController extends Mage_Adminhtml_Controller_Action {	
	
	private $productData;


	public function _construct() {

         parent::_construct();
    }

    public function getTableData(){

        if(empty($this->productData)){
            $api = Mage::getSingleton('pluggto/api');
            $this->productData = $api->get('products/tabledata',null,null,true);
            $this->productData = $this->productData['Body'];
        }

        return $this->productData;
    }


    public function forceExportAction(){

        $product_model = Mage::getModel('pluggto/product');

        $product_model->forceExport();

        Mage::getSingleton('core/session')->addSuccess(Mage::helper('pluggto')->__('A exportação foi agendada.'));
        $this->_redirect('adminhtml/system_config/edit/section/pluggto');


    }

    public function stockPriceSyncAction(){

        $product_model = Mage::getModel('pluggto/product');

        $product_model->syncPriceStock();

        Mage::getSingleton('core/session')->addSuccess(Mage::helper('pluggto')->__('A sincronização foi agendada.'));
        $this->_redirect('adminhtml/system_config/edit/section/pluggto');

    }

    public function unlinkAllAction(){

        $product_model = Mage::getModel('pluggto/product');

        $product_model->unLinkAll();

        Mage::getSingleton('core/session')->addSuccess(Mage::helper('pluggto')->__('Todos produtos foram desvinculados do Plugg.To'));
        $this->_redirect('adminhtml/system_config/edit/section/pluggto');

    }

	public function  importAllAction(){


        $product_model = Mage::getModel('pluggto/product');
        $product_model->import();


         Mage::getSingleton('core/session')->addSuccess(Mage::helper('pluggto')->__('A importação foi agendada.'));
         $this->_redirect('adminhtml/system_config/edit/section/pluggto');

    }

    public function runLineAction(){
        $line = Mage::getSingleton('pluggto/line');
        $line->playline(true);
        Mage::getSingleton('core/session')->addSuccess(Mage::helper('pluggto')->__('A fila foi executada'));
        $this->_redirect('adminhtml/system_config/edit/section/pluggto');
        return;
    }

    public function importOrdersAction(){

        Mage::getSingleton('pluggto/order')->forceSyncOrders();
        Mage::getSingleton('core/session')->addSuccess(Mage::helper('pluggto')->__('Os pedidos foram capturados'));
        $this->_redirect('adminhtml/system_config/edit/section/pluggto');
        return;

    }



    public function manualAction(){

        $produts = $this->getRequest()->getParam('product');
        $export = Mage::getSingleton('pluggto/export');
        foreach($produts as $prodId){
            $product = Mage::getModel('catalog/product')->load($prodId);
            $export->exportProductToQueue($product);
        }
        Mage::getSingleton('core/session')->addSuccess(Mage::helper('pluggto')->__('Produtos agendados para exportação'));
        $this->_redirect('adminhtml/catalog_product');

    }



    public function testAction(){

            $call = Mage::getSingleton('pluggto/call');
            $result = $call->Autenticate(true);
            if($result){
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('pluggto')->__('O módulo pode se autenticar no PluggTo com sucesso.'));

            } else {
                Mage::getSingleton('core/session')->addError(Mage::helper('pluggto')->__('Não foi possível a autenticação no PluggTo, por favor, verifique as credenciais cadastradas'));

            }
            $this->_redirect('adminhtml/system_config/edit/section/pluggto');

    }


	 	
	 
	 
}


?>