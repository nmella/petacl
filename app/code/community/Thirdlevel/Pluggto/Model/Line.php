<?php

class Thirdlevel_Pluggto_Model_Line extends Mage_Core_Model_Abstract
{
    protected function _construct(){

        $this->_init("pluggto/line");

    }


    public function processNotification($id){


        $api = Mage::getSingleton('pluggto/api')->load(1);
        $data = $this->load($id);
        $error = false;


        switch ($data->getOpt()):

            case 'GET':

                if($data->getReason() == 'deleted' && $data->getWhat() == 'products'){
                    $data->setResult('Deleted')->setCode('200')->setStatus(1)->save();
                    return;
                }



                if($data->getWhat() == 'orders'){
                    $body = array('showExternal'=>'true');
                    $result = $api->get($data->getUrl(),$body,'field',true);
                } else {
                    $result = $api->get($data->getUrl(),null,null,true);
                }



                if($result['code'] == 200){

                    if($data->getWhat() == 'products'){

                        try{
                            Mage::getSingleton('pluggto/product')->saveProduct($result['Body']['Product']);
                        } catch (exception $e){
                            $error = $e->getMessage();
                            Mage::helper('pluggto')->WriteLogForModule('Error','Error saving product: '.print_r($e->getMessage(),1));
                        }


                    } else {
                        try{
                            Mage::getSingleton('pluggto/order')->create($result['Body']['Order']);
                        } catch (exception $e){
                            $error = $e->getMessage();

                        }
                    }

                }

                if($result['code'] != 200 || $error){
                    if($error){
                        $data->setResult(json_encode(print_r($error,1)))->setCode($result['code'])->setStatus(2)->save();
                    } else {
                        $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(2)->save();
                    }

                } else {
                    $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(1)->save();
                }

                break;
            case 'POST':

                $body = $data->getBody();



                if(empty($body) &&  $data->getWhat() == 'products'){

                    $product = Mage::getModel('catalog/product')->load($data->getStoreid());

                    if($product->getEntityId() != null){

                        $old = Mage::getSingleton('pluggto/product')->getProductInPluggto($product->getSku());



                        if(isset($old['id']) && !empty($old['id'])){
                            $data->setUrl('products/' . $old['id']);
                            $data->setOpt('PUT');
                        }

                        $body = Mage::getSingleton('pluggto/product')->formateToPluggto($product,$old);

                        $data->setBody(json_encode($body))->save();

                    } else {
                        $data->setResult(json_encode('Product not find in Store'))->setCode(500)->setStatus(2)->save();
                        continue;
                    }

                }


                // check to see if the operation didn' changed
                if($data->getOpt() == 'PUT'){
                    // If not found in Plugg.To, create
                    $result = $api->put($data->getUrl(),$data->getBody());
                } else {
                    // If not found in Plugg.To, create
                    $result = $api->post($data->getUrl(),$data->getBody());
                }

                if($result['code'] == 201 || $result['code'] == 200) {

                    if ($data->getWhat() == 'orders') {
                        Mage::getSingleton('pluggto/order')->savePluggToid($result['Body']['Order']);
                    }
                }

                // if return sucess, save pluggtoattributes
                if($result['code'] == 201 || $result['code'] == 200){

                    $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(1)->save();
                    break;

                } else {

                        // if authentication issue, mark to try again
                        if($result['code'] == 500 && $result['Body'] == 'Authentication Fail, was not possible to authenticate to Plugg.To'){
                            $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(0)->save();
                        // if return code 0 or empty, maybe a API is out or has a firewall
                        } else if (empty($result['code']) || $result['code'] == 0) {
                            $data->setResult('API NOT REACHED')->setCode($result['code'])->setStatus(0)->save();
                        } else {
                            $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(2)->save();
                        }
                    break;

                }


            case 'PUT':

                $body = $data->getBody();


                // the body is empty because was putted by force sync
                if(empty($body)){

                    $product = Mage::getModel('catalog/product')->load($data->getStoreid());

                        $old = Mage::getSingleton('pluggto/product')->getProductInPluggto($product->getSku());
                        $body = Mage::getSingleton('pluggto/product')->formateToPluggto($product,$old);
                        $body = json_encode($body);
                        $data->setBody($body)->save();

                }



                $result = $api->put($data->getUrl(),$body);


                // sucess!
                if($result['code'] == 200) {

                    if ($data->getWhat() == 'orders') {
                        Mage::getSingleton('pluggto/order')->savePluggToid($result['Body']['Order']);
                    }
                    // product with sku with mistake, try to find correct product
                } elseif ($result['code'] == 400 && $data->getWhat() == 'products') {

                    if(isset($result['Body']['details']) &&  $result['Body']['details'] == 'Changes not found in the document'){

                        $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(1)->save();
                        break;
                    } else {

                    $product = json_decode($data->getBody(), 1);
                    $old = Mage::getSingleton('pluggto/product')->getProductInPluggto($product['sku']);
                    // product was found, update the correct product

                    if(isset($old['id'])){
                        $result = $api->put('skus/'.$old['sku'],$data->getBody());
                    }

                    }



                    /// product not found, try to find the correct product
                } elseif ($result['code'] == 404 && $data->getWhat() == 'products'){

                            $result = $api->post('products/',$body);

                            if($result['code'] == 201){
                                $data->setResult(json_encode($result['Body']))->setOpt('POST')->setCode($result['code'])->setStatus(1)->save();
                            } else {
                                $data->setResult(json_encode($result['Body']))->setOpt('POST')->setCode($result['code'])->setStatus(2)->save();
                            }

                            break;
                }

                if($result['code'] != 200){

                    if($result['code'] == 500 && $result['Body'] == 'Authentication Fail, was not possible to authenticate to Plugg.To'){
                        $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(0)->save();
                    } else {
                        $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(2)->save();
                    }

                } else {
                    $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(1)->save();
                }

                break;
            case 'DEL':

                $result = $api->del($data->getUrl());

                if($result['code'] != 200){

                    if($result['code'] == 500 && $result['Body'] == 'Authentication Fail, was not possible to authenticate to Plugg.To'){
                        $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(0)->save();
                    } else {
                        $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(2)->save();
                    }

                } else {
                    $data->setResult(json_encode($result['Body']))->setCode($result['code'])->setStatus(1)->save();
                }


                break;

        endswitch;


    }

    public function playline($force = false,$id=null){


        try{

            ini_set('max_execution_time',0);
            ini_set("memory_limit","256M");

            $collection = $this->getCollection();
            $datas = $collection->addFieldToFilter('status',0)->setPageSize(100)->setOrder('id', 'DESC');
            $api = Mage::getSingleton('pluggto/api')->load(1);
            $start = time();
            $allowtime = ini_get('max_input_time');
            $maximput = ini_get('max_execution_time');

            if($maximput == 0){
                $maximput = 9999999999999;
            }

            $memory_limit = (int) ini_get('memory_limit');


            if($allowtime < $maximput){
                $allowtime = $maximput;
            }

            // set allow time
            if(empty($allowtime)){
                $allowtime = 30;
            } else {
                $allowtime = $allowtime - 10;
            }

            // check if line is running, break if has less than 60 secounds last line
            $lastimestamp = $api->getLine();


            if(!empty($lastimestamp) && $force == false){


                $now = new DateTime('now');
                $diff =  $now->getTimestamp() - $lastimestamp;
                if($diff < 60){
                    return;
                }
            }


            foreach($datas as $data):

                $api->setLine(time())->save();

                try{
                    $this->processNotification($data->getId());
                } catch (exception $e){
                    Mage::helper('pluggto')->WriteLogForModule('Error','FAIL REQUEST: '.print_r($e->getMessage(),1));
                }

                $now = new DateTime('now');
                $passou =  $now->getTimestamp() - $start;
                $mem = round(memory_get_usage()/1048576,2);


                if(strtotime($passou) > $allowtime ){
                    break;
                } elseif ($mem > $memory_limit){
                    break;
                }

            endforeach;
            return;

        }catch (Exception $e){
            return;
        }
    }

    public function clearQueue(){

        $daystoclear = Mage::getStoreConfig('pluggto/configs/clear_queue');

        if($daystoclear == 0){
            return;
        }

        $sdate = date('Y-m-d', strtotime("-".$daystoclear." days")).' 00:00:00';
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $tableName = $resource->getTableName('pluggto/line');
        $query = "DELETE FROM " . $tableName . " WHERE status = 1 AND created < '".$sdate."'";
        $writeConnection->query($query);

    }

    public function checkCron(){

        $api =  Mage::getSingleton('pluggto/api')->load(1);
        $lastimestamp = $api->getLine();

        if(empty($lastimestamp)){
            Mage::getSingleton('core/session')->addError('PluggTo queue is not running, check if cron is configured correctly');
            return;
        }

        $now = new DateTime('now');
        $diff =  $now->getTimestamp() - $lastimestamp;

        if($diff > 300){
            Mage::getSingleton('core/session')->addNotice(sprintf('Last time that PluggTo Queue run was more than %s minutes ago.', round($diff/60)));
        } elseif($diff > 3600){
            Mage::getSingleton('core/session')->addError('Last time that PluggTo Queue run was more than 1 hour ago, check if cron is configured correctly');
        }

    }


}