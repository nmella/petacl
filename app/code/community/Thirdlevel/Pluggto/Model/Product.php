<?php

class Thirdlevel_Pluggto_Model_Product extends Mage_Core_Model_Abstract
{

    public $products;
    protected $productData = null;
    protected $attributeSetId;
    protected $attributesIds;
    protected $simpleProduts;
    protected $price;
    protected $attributeCode;
    protected $values;
    protected $new = true;
    protected $setdata;
    protected $tovar;
    protected $configs;
    protected $StartTime;
    protected $weight;
    protected $categoryArray;


    public function getConfig()
    {

        if (empty($this->configs)) {
            $this->configs = Mage::helper('pluggto')->config();
        }

        return $this->configs;
    }


    // sera desativada
    public function exportAll()
    {

        $this->getProducts();

        $this->export();
        return;
    }


    public function forceExport()
    {

        $this->getProducts();

        foreach ($this->products as $product) {
            Mage::getModel('pluggto/bulkexport')->write($product->getId());
        }

        Mage::getSingleton('pluggto/bulkexport')->runBulkExport();

        return;
    }


    // sera desativada
    public function process()
    {

        ini_set('max_execution_time', 0);

        $this->getTableData();

        // exporta para o pluggto
        $this->export();

        // sincroniza
        $this->sync();

        //  Mage::getSingleton('pluggto/line')->playline();

        return;

    }


    public function getProducts()
    {

        if (empty($this->products)) {

            $this->products = $this->getProductsCollection();
        }

        return $this->products;

    }

    public function getProductsCollection()
    {

        $allowed = array();

        $StoreId = Mage::getStoreConfig('pluggto/products/product_store_id');

        if (!empty($StoreId)) {
            $modelProduct = Mage::getModel('catalog/product')->setStoreId($StoreId);
        } else {
            $modelProduct = Mage::getModel('catalog/product');
        }


        $products = $modelProduct->getCollection()
            ->addStoreFilter()
            ->addAttributeToFilter('visibility', 4)
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('pluggto_time')
            ->addAttributeToSelect('pluggto_id');

        foreach ($products as $product) {
            $allowed[] = $product;
        }


        return $allowed;

    }

    public function getOneTableDataFromPluggTo($page,$limit){

        try{
            $api = Mage::getSingleton('pluggto/api');
            $data = array('page'=>$page,'limit'=>$limit);
            $result = $api->get('products/tabledata',$data, 'field', true);
        } catch (exception $e){

            // resposta vazia, api fora do ar, tentar novamente
            if($e->getMessage() == ''){
                $result = false;
            } else {
                return false;
            }

        }

        if(!$result){
            return $this->getOneTableDataFromPluggTo($page,$limit);
        }

        if(isset($result['Body']['Products'])){
            return $result['Body']['Products'];
        } else {
            return false;
        }
    }

    public function gellTableDataFromPluggTo(){


        $products = $this->getOneTableDataFromPluggTo(1,100);


        if (!$products){
            $products = array();
        }

        $page = 1;
        $_limit = 100;

        if (count($products) == 100) {

            $lastResult = $products;

            while (count($lastResult) == $_limit) {

                $page += 1;
                $_limit = 100;

                // try to get more
                $result = $this->getOneTableDataFromPluggTo($page, $_limit);

                $lastResult = $result;

                if (is_array($lastResult)) {
                    $products = array_merge($lastResult,$products);
                }


            }
        }

        return $products;

    }


    public function getTableData()
    {

        if (empty($this->productData)) {
            $this->productData = $this->gellTableDataFromPluggTo();
        }

        return $this->productData;
    }

    public function import()
    {

        $this->getTableData();

        // se tiver vazio retorna por que não tem nada para importar
        if (empty($this->productData)) {
            return;
        }

        foreach ($this->productData as $key => $value) {


            $this->carregaProduto($value);

            // check if dont't have external in pluggto
            if ($this->new) {

                // lock first to see if already is not in database
                $alline = Mage::getModel('pluggto/line')->getCollection();
                $alline->addFieldToFilter('pluggtoid', $key);
                $id = $alline->getFirstItem()->getId();

                $line = Mage::getModel('pluggto/line');

                if ($id != null) {
                    $line->load($id);
                }

                $line->setWhat('products');
                $line->setUrl('skus' . '/' . $key);
                $line->setPluggtoid($key);
                $line->setOpt('GET');
                $line->setDirection('from');
                $line->setCreated(date("Y-m-d H:i:s"));
                $line->save();
            }
        }

        return;
    }


    public function syncPriceStock(){

        ini_set('max_execution_time',0);
        ini_set("memory_limit","256M");

        $this->getTableData();
        $configs = $this->getConfig();

        $chanceDisable = Mage::getStoreConfig('pluggto/products/disable_product');

        foreach ($this->productData as $key => $value) {

            $needSync = false;

            $product  = $this->carregaProduto($value);


            if (!empty($product) && $product->getEntityId() == null){
                continue;
            }

            // configurable product
            if($product->getTypeId() == 'configurable'){


                $price = $this->numberFormat($this->getPriceWithTax($product->getPrice(),$product));
                $specialPrice = $this->numberFormat($this->getPriceWithTax($this->getProductPriceRule($product),$product));

                if($price != $value['price']){
                    $needSync = true;
                }

                if($specialPrice != $value['special_price']){
                    $needSync = true;
                }


                $existedVarisArray = array();

                if(isset($value['variations'])){

                        foreach($value['variations'] as $varis){
                            if(!empty($varis['sku'])){
                                $existedVarisArray[$varis['sku']] = $varis;
                            }
                        }
                }

                $childProducts = $product->getTypeInstance()->getUsedProducts();

                $configs = $this->getConfig();
                $storeView = $configs['products']['product_store_default'];

                foreach($childProducts as $Msubproduct){

                    $subproduct = Mage::getModel('catalog/product');

                    if(!empty($storeView)){
                        $subproduct->setStoreId($storeView);
                    }

                    $subproduct->load($Msubproduct->getEntityId());


                    if(isset($existedVarisArray[$subproduct->getSku()])){

                        $thisvari = $existedVarisArray[$subproduct->getSku()];

                        $vprice = $this->numberFormat($this->getPriceWithTax($subproduct->getPrice(),$subproduct));
                        $vspecialPrice = $this->numberFormat($this->getPriceWithTax($this->getProductPriceRule($subproduct),$subproduct));

                        if(isset($thisvari['special_price']) && $vspecialPrice != $thisvari['special_price']){
                            $needSync = true;
                        }

                        if(isset($thisvari['price']) && $vprice != $thisvari['price']){
                            $needSync = true;
                        }

                        $stock = $this->getProducQtd($subproduct);

                        if($chanceDisable){

                            if($subproduct->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                               // do nothing
                            } else {
                                // change for zero
                                $stock['quantity'] = 0;
                            }

                        } else {
                            $data['quantity'] = $stock['quantity'];
                        }

                        if(isset($thisvari['quantity']) && $stock['quantity'] != $thisvari['quantity']){
                            $needSync = true;
                        }

                    } else {
                        // do not exists in Plugg.To, need to sent
                        $needSync = true;
                    }


                }

            //simple product
            } else {

                $price = $this->numberFormat($this->getPriceWithTax($product->getPrice(),$product));
                $specialPrice = $this->numberFormat($this->getPriceWithTax($this->getProductPriceRule($product),$product));


                if(isset($value['price']) && $price != $value['price']){
                    $needSync = true;
                }

                if(isset($value['special_price']) && $specialPrice != $value['special_price']){
                    $needSync = true;
                }

                $stock = $this->getProducQtd($product);

                if($chanceDisable){

                    if($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                        // do nothing
                    } else {
                        // change for zero
                        $stock['quantity'] = 0;
                    }

                } else {
                    $data['quantity'] = $stock['quantity'];
                }


                if(isset($value['quantity']) && $stock['quantity'] != $value['quantity']){
                    $needSync = true;
                }
            }


            // check if dont't have external in pluggto
            if ($needSync) {

                if($configs['configuration']['base']){


                    $line = Mage::getModel('pluggto/line');
                    $line->setWhat('products');
                    $line->setUrl('skus' . '/' . $value['sku']);
                    $line->setPluggtoid($key);
                    $line->setStoreid($product->getEntityId());
                    $line->setOpt('PUT');
                    $line->setDirection('to');
                    $line->setCreated(date("Y-m-d H:i:s"));
                    $line->save();

                } else {

                // lock first to see if already is not in database
                $line = Mage::getModel('pluggto/line');
                $line->setWhat('products');
                $line->setUrl('skus' . '/' . $value['sku']);
                $line->setPluggtoid($key);
                $line->setOpt('GET');
                $line->setDirection('from');
                $line->setCreated(date("Y-m-d H:i:s"));
                $line->save();
                }
            }


        }

    }

    public function sync()
    {


        $pluggids = array();

        // nada para sincronizar
        if (empty($this->productData)) {
            return;
        }

        if (is_array($this->productData)) {
            // pluggto ids that have ids in the store
            foreach ($this->productData as $key => $value) {

                $product = $this->getOneByPluggtoId($key);

                if ($product->getEntityId() != null) {
                    $pluggids[$value['timestamp']] = $key;
                }

            }
        }

        if (is_array($this->products)) {
            foreach ($this->products as $product) {

                $plugtime = null;
                $plugtime = array_search($product->getPluggtoId(), $pluggids);

                if ($plugtime != null) {

                    // se o timestamp na loja estiver maior, envia para o pluggto
                    if ((int)$product->getPluggtoTime() > $plugtime) {

                        Mage::getSingleton('pluggto/export')->exportProductToQueue($product);

                        // se na loja estiver menor, recebe do pluggto
                    } elseif ((int)$product->getPluggtoTime() < $plugtime) {


                        $alline = Mage::getModel('pluggto/line')->getCollection();
                        $alline->addFieldToFilter('pluggtoid', $product->getPluggtoId());
                        $alline->addFieldToFilter('storeid', $product->getEntityId());
                        $id = $alline->getFirstItem()->getId();

                        $line = Mage::getModel('pluggto/line');

                        if ($id != null) {
                            $line->load($id);
                        }

                        $line->setWhat('products');
                        $line->setStoreid($product->getEntityId());
                        $line->setPluggtoid($product->getPluggtoId());
                        $line->setDirection('from');
                        $line->setOpt('GET');
                        $line->setUrl('products' . '/' . $product->getPluggtoId());
                        $line->setCreated(date("Y-m-d H:i:s"));
                        $line->save();


                    }

                }

            }
        }

        return;

    }


    public function export($force = false)
    {

        $this->getProducts();

        // nada para exportar
        if (empty($this->products)) {
            return;
        }

        $configs = $this->getConfig();

        $storeView = $configs['products']['product_store_default'];


        foreach ($this->products as $product) {


            if (($product['pluggto_id'] == null || $product['pluggto_id'] == '') || $force) {
                $product = Mage::getModel('catalog/product');

                if(!empty($storeview)){
                    $product->setStoreId($storeview);
                }
                $product->load($product['entity_id']);



                Mage::getSingleton('pluggto/export')->exportProductToQueue($product);
            }

        }

        return;

    }


    // return a array with pluggtoid and timestamp
    public function getProductsIndexData()
    {


        $products = $this->getProductsCollection();

        $return = array();

        foreach ($products as $product) {
            $return[$product->getEntityId()]['pluggtoid'] = $product->getPluggtoId();
            $return[$product->getEntityId()]['timestamp'] = $product->getPluggtoTime();
        }

        return $return;
    }


    /*
     * Salva ou atualiza o Produto
     *
     */


    public function getAttributeSetId($array_product, $var = false)
    {

        if ($var) {
            if (!empty($this->attributeSetId)) {
                return $this->attributeSetId;
            }
        }

        $attribute_api = new Mage_Catalog_Model_Product_Attribute_Set_Api();
        $attribute_sets = $attribute_api->items();

        // retorna o primeiro attribute set id da loja
        if (!isset($array_product['raw']['attribute_set_id'])) {
            $this->attributeSetId = $attribute_sets[0]['set_id'];
            return $this->attributeSetId;
        } else {
            // já tem atribute set id
            $found = false;

            // verifica se exsite na loja
            foreach ($attribute_sets as $sets) {
                if ($sets['set_id'] == $array_product['raw']['attribute_set_id']) {
                    $found = true;
                }
            }

            // se existir retorna ele
            if ($found) {
                $this->attributeSetId = $array_product['raw']['attribute_set_id'];
                return $this->attributeSetId;
                // caso contrário retorna um padrão da loja
            } else {
                $this->attributeSetId = $attribute_sets[0]['set_id'];
                return $this->attributeSetId;
            }

        }

    }


    function findValueId($attribute, $label)
    {

        $attribute_options_model = Mage::getModel('eav/entity_attribute_source_table');
        $attribute_options_model->setAttribute($attribute);
        $options = $attribute_options_model->getAllOptions(false);
        $optionId = null;

        foreach ($options as $option) {
            if ($option['label'] == $label) {
                $optionId = $option['value'];

                break;
            }
        }

        return $optionId;
    }

    function getOptionId($attribute_code, $label)
    {
        $attribute_model = Mage::getModel('eav/entity_attribute');
        $attribute_id = $attribute_model->getIdByCode('catalog_product', $attribute_code);

        $attribute = $attribute_model->load($attribute_id);


        if ($attribute->getAttributeId() == null) {



            $attribute_api = new Mage_Catalog_Model_Product_Attribute_Set_Api();
            $attribute_sets = $attribute_api->items();

            $group = Mage::getModel('eav/entity_attribute_set');
            $groupId = $group->getDefaultGroupId($attribute_sets[0]['set_id']);
            $attribute->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
            $attribute->setAttributeCode($attribute_code);
            $attribute->setFrontendInput('select');
            $attribute->setFrontendLabel($attribute_code);
            $attribute->setIsGlobal(1);
            $attribute->setIsVisible(1);
            $attribute->setIsConfigurable(1);
            $attribute->setIsUserDefined(1);
            $attribute->setIsRequired(0);
            $attribute->setBackEndType('int');
            $attribute->setApplyTo('simple', 'bundle', 'grouped', 'configurable');
            $attribute->setApplyTo('simple', 'bundle', 'grouped', 'configurable');
            $attribute->setAttributeSetId($attribute_sets[0]['set_id']);
            $attribute->setAttributeGroupId($groupId);

            $attribute->save();

        } else {

            if ($attribute->getAttributeSetId() == null) {
                $attribute_api = new Mage_Catalog_Model_Product_Attribute_Set_Api();
                $attribute_sets = $attribute_api->items();
                $group = Mage::getModel('eav/entity_attribute_set');
                $groupId = $group->getDefaultGroupId($attribute_sets[0]['set_id']);
                $attribute->setAttributeSetId($attribute_sets[0]['set_id']);
                $attribute->setAttributeGroupId($groupId);
                $attribute->save();
            }
        }



        $optionId = $this->findValueId($attribute, $label);

        if (!is_null($optionId)) {

            $this->attributeCode = $attribute->getAttributeId();
            $this->attributeId = $attribute->getAttributeId();

            if (is_array($this->attributesIds)) {

                if (!in_array($attribute->getAttributeId(), $this->attributesIds)) {
                    $this->attributesIds[] = $attribute->getAttributeId();
                }

            } else {
                $this->attributesIds[] = $attribute->getAttributeId();
            }
            return $optionId;

        } else {
            $this->addAttributeOptions($attribute_code,array($label));
            $option = $this->findValueId($attribute, $label);
        }

        if (!empty($option)) {
            if (is_array($this->attributesIds)) {

                if (!in_array($attribute->getAttributeId(), $this->attributesIds)) {
                    $this->attributesIds[] = $attribute->getAttributeId();
                }

            } else {
                $this->attributesIds[] = $attribute->getAttributeId();
            }
            $this->attributeCode = $attribute->getAttributeId();
            $this->attributeId = $attribute->getAttributeId();
            return $option;
        } else { // wil be null
            return $option;
        }
    }

    public function addAttributeOptions($attribute_code, array $optionsArray)
    {


        $setup = new Mage_Sales_Model_Mysql4_Setup('core_setup');
        $tableOptions = $setup->getTable('eav_attribute_option');
        $tableOptionValues = $setup->getTable('eav_attribute_option_value');
        $attributeId = (int) $setup->getAttribute('catalog_product', $attribute_code, 'attribute_id');


        foreach ($optionsArray as $sortOrder => $label) {
            // add option
            $data = array(
                'attribute_id' => $attributeId,
                'sort_order' => $sortOrder,
            );


            $setup->getConnection()->insert($tableOptions, $data);

            // add option label
            $optionId = (int) $setup->getConnection()->lastInsertId($tableOptions, 'option_id');
            $configs = $this->getConfig();

            $storeId = $configs['products']['product_store_default'];
            if(empty($storeId)){
                $storeId = 0;
            }

            $data = array(
                'option_id' => $optionId,
                'store_id' => $storeId,
                'value' => $label,
            );
            $setup->getConnection()->insert($tableOptionValues, $data);
        }
    }

    // isola o nome do arquivo para tentar ver se não falta colocar no external array
    public function getImageFileNameFromMagento($imageUrl)
    {

        $imagepces = explode('_', $imageUrl);
        $filenamearray = explode('/', $imagepces[0]);
        $withOutExtension = explode('.', end($filenamearray));
        return $withOutExtension[0];
    }

    public function getImagePluggtoFileName($imageUrl)
    {

        $imagepces = explode('-', $imageUrl);
        $img = explode('.', end($imagepces));
        return $img[0];

    }


    public function updatePhotoExternal($PluggtoUrl, $item, $product, $array_product, $parent)
    {

        $api = Mage::getSingleton('pluggto/api')->load(1);


        if ($parent) {
            $toPlugg['id'] = $parent['id'];
            $toPlugg['variations'][0]['id'] = $array_product['id'];
            $toPlugg['variations'][0]['photos'][0]['url'] = $PluggtoUrl['url'];

            if (isset($item['url'])) $toPlugg['variations'][0]['photos'][0]['external'] = $item['url'];
            if (isset($item['disabled'])) $toPlugg['variations'][0]['photos'][0]['disabled'] = $item['disabled'];
            if (isset($item['position'])) $toPlugg['variations'][0]['photos'][0]['order'] = $item['position'];
            if (isset($item['label'])) $toPlugg['variations'][0]['photos'][0]['name'] = $item['label'];

            if ($item['file'] == $product->getThumbnail()) {
                $toPlugg['variations'][0]['photos'][0]['thumb'] = (bool)true;
            } else {
                $toPlugg['variations'][0]['photos'][0]['thumb'] = (bool)false;
            }

        } else {
            $toPlugg['id'] = $array_product['id'];
            if (isset($item['url'])) $toPlugg['photos'][0]['url'] = $PluggtoUrl['url'];
            if (isset($item['url'])) $toPlugg['photos'][0]['external'] = $item['url'];
            if (isset($item['disabled'])) $toPlugg['photos'][0]['disabled'] = $item['disabled'];
            if (isset($item['position'])) $toPlugg['photos'][0]['order'] = $item['position'];
            if (isset($item['label'])) $toPlugg['photos'][0]['name'] = $item['label'];

            if ($item['file'] == $product->getThumbnail()) {
                $toPlugg['photos'][0]['thumb'] = (bool)true;
            } else {
                $toPlugg['photos'][0]['thumb'] = (bool)false;
            }
        }

        Mage::getModel('pluggto/call')->doCall('products/' . $toPlugg['id'], $toPlugg, 'json', 'PUT', true);

    }


    public function saveImage($product, $array_product, $parent = false)
    {

        // check to see if have pictures
        if (isset($array_product['photos'])) {


            $mediaApi = Mage::getModel("catalog/product_attribute_media_api");


            $mediaApiItems = $mediaApi->items($product->getId());
            // delete first old pictures
            $arrayphotos = array();
            $arrayphotosUrl = array();


            foreach ($array_product['photos'] as $pluggphotos) {

                if (isset($pluggphotos['external'])) {
                    $arrayphotos[$pluggphotos['external']]['url'] = $pluggphotos['url'];
                    if (isset($pluggphotos['thumb'])) $arrayphotos[$pluggphotos['external']]['thumb'] = $pluggphotos['thumb'];
                } else {
                    $tophoto['url'] = $pluggphotos['url'];
                    if (isset($pluggphotos['thumb'])) $tophoto['thumb'] = $pluggphotos['thumb'];
                    $arrayphotos[] = $tophoto;
                }
            }

            // primeiro tento excluir pelo external
            $externalArray = array();

            foreach ($mediaApiItems as $item) {

                // se possui external code não precisa fazer nada já pula para o próximo
                if (isset($arrayphotos[$item['url']])) {
                    unset($arrayphotos[$item['url']]);
                    continue;
                }

                // tenta busca pelo nome do arquivo
                $file = $this->getImageFileNameFromMagento($item['url']);
                $finded = false;
                foreach ($arrayphotos as $photo) {

                    $finded = strpos($photo['url'], $file);

                    if ($finded) {
                        unset($arrayphotos[array_search($photo, $arrayphotos)]);
                        $this->updatePhotoExternal($photo, $item, $product, $array_product, $parent);
                        break;
                    }

                }


                // esta na loja, mas não no pluggto, deve ser apagado.
                if (!$finded) {
                    $this->lockSave();
                    $mediaApi->remove($product->getId(), $item['file']);
                    $product->save();
                    $this->unlockSave();

                    $configs = $this->getConfig();
                    $storeView = $configs['products']['product_store_default'];

                    $product = Mage::getModel('catalog/product');

                    if(!$storeView){
                        $product->setStoreId($storeView);
                    }


                    $product->load($product->getId());
                }

            }


            if (count($arrayphotos) > 0) {
                // salvar novas images

                $dir = Mage::getBaseDir('base') . '/media/pluggto';

                if (!is_dir($dir)) {
                    mkdir($dir, 0700);
                }

                $total = count($arrayphotos);
                foreach ($arrayphotos as $picture) {


                    $parts = explode('/', $picture['url']);
        
                    foreach ($parts as $part) {
                        $name = $part;    
                    }

                    $files = explode('.', $name);

                    if (!isset($files[1])) {
                        $ext = '.jpg';
                    } else {
                        $ext = '.' . $files[1];
                    }

                    $img = $dir . '/' . $files[0] . $ext;

                    $arrContextOptions=array(
                        "ssl"=>array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        ),
                    );

                    try{
                        file_put_contents($img, file_get_contents($picture['url'],false,stream_context_create($arrContextOptions)));
                    } catch (exception $e){
                        continue;
                    }

                    if (isset($picture['disabled'])) {
                        $disable = $picture['disabled'];
                    } else {
                        $disable = false;
                    }

                    if (isset($picture['thumb']) && ($picture['thumb'] == true || $total == 1)) {
                        $scope = array('image', 'small_image', 'thumbnail');
                    } else {
                        $scope = array('image', 'small_image');
                    }

                    $product->addImageToMediaGallery($img, $scope, false, $disable);

                    $product->getMediaGalleryImages();

                }

            }

            $this->lockSave();
            $product->save();
            $this->unlockSave();
        }

    }

    // create a single category
    public function checkCategory($arraycat)
    {

        $category = Mage::getModel('catalog/category');

        if (isset($arraycat['external']) && !empty($arraycat['external'])) {
            $category->load($arraycat['external']);
        }


        if ($category->getEntityId() != null) {

            if ($category->getName() != $arraycat['name']) {
                $category = Mage::getModel('catalog/category');

                $col = $category->getCollection();
                $col->addFieldToFilter('name', $arraycat['name']);
                $id = $col->getFirstItem()->getEntityId();

                if (!empty($id)) {
                    return $id;
                }

            } else {
                return $category->getEntityId();
            }

        } else {

            $col = $category->getCollection();
            $col->addFieldToFilter('name', $arraycat['name']);
            $id = $col->getFirstItem()->getEntityId();

            if (!empty($id)) {
                return $id;
            }
        }

        $category->setStoreId(Mage::app()->getStore()->getId());


        $cat['name'] = $arraycat['name'];
        $cat['path'] = "1"; //parent relationship..
        $cat['description'] = $arraycat['name'];
        $cat['is_active'] = 1;
        $cat['is_anchor'] = 0; //for layered navigation

        $category->addData($cat);
        $category->save();

        return $category->getEntityId();


    }
    public function getPriceWithTax($price,$product){

        $insertTax = Mage::getStoreConfig('pluggto/products/calculate_tax');

        if(!empty($insertTax) && $insertTax){

            $store = Mage::app()->getStore('default');
            $taxCalculation = Mage::getModel('tax/calculation');
            $request = $taxCalculation->getRateRequest(null, null, null, $store);
            $taxClassId = $product->getTaxClassId();

            $percent = $taxCalculation->getRate($request->setProductClassId($taxClassId));


            if(!empty($percent)){
                $price = $price + ($price * ($percent/100));
                return $price;
            } else {
                return $price;
            }

        } else{
            return $price;
        }
    }




    public function getProductPriceRule($prod)
    {
        // Verifica se há preços especiais (ofertas) para o produto
        if ($prod->getSpecialPrice() != null && $prod->getSpecialPrice() != '') {
            // Verifica se o preço especial está em um determinado range de datas
            if ($prod->getSpecialFromDate() != null && $prod->getSpecialFromDate() != '' && $prod->getSpecialToDate() != null && $prod->getSpecialToDate() != '') {
                $now = strtotime(date('c'));
                if (strtotime($prod->getSpecialFromDate()) < $now && $now < strtotime($prod->getSpecialToDate())) {
                    // is special price
                    $price = $prod->getSpecialPrice();
                } else {
                    // if  have special price but is out of date, return price
                    $price = $prod->getPrice();
                }
            } else {
                // Há preço especial, porém sem range de datas, logo, retorna o preço especial
                $price = $prod->getSpecialPrice();
            }
        } else {
            // Não há ofertas para o produto, logo, retorna o preço do produto
            $price = $prod->getPrice();
        }


        return $price;
    }


    public function findProduct($sku)
    {

        $product = Mage::getModel('catalog/product');
        $collection = $product->getCollection();
        $collection->addFieldToFilter('sku',$sku);

        $id = $collection->getFirstItem()->getEntityId();

        if ($id != null) {


            $configs = $this->getConfig();
            $storeView = $configs['products']['product_store_default'];

            $product =  Mage::getModel('catalog/product');

            if(!$storeView){
                $product->setStoreId($storeView);
            }

            $product->load($product->getId());

            return $product->load($id);
        }

        return false;

    }

    // retorna a colocação de produtos com um id do pluggto
    public function getAllByPluggtoId($id)
    {

        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addFieldToFilter('pluggto_id', $id);
        return $collection;
    }

    // retorna o primeiro produto com o id do pluggto
    public function getOneByPluggtoId($id)
    {
        return $this->getAllByPluggtoId($id)->getFirstItem();
    }


    public static function numberFormat($number)
    {
        return (float) number_format($number,2,'.','');
    }

    public function carregaProduto($array_product)
    {


        // carrega o model
        $product = Mage::getModel('catalog/product');

        $configs = $this->getConfig();

        $storeView = $configs['products']['product_store_default'];


                // first try to load by sku
                if (isset($array_product['sku']) && !empty($array_product['sku'])) {

                    $collection = $product->getCollection();
                    $collection->addFieldToFilter('sku',$array_product['sku']);

                    $id = $collection->getFirstItem();


                    if ($id->getEntityId() != null) {
                        $entityId = $id->getEntityId();
                        $product = Mage::getModel('catalog/product');

                            if(!empty($storeView)){
                            $product->setStoreId($storeView);
                            }

                            $product = $product->load($entityId);
                    }

                } else {
                    return $product;
                }

        return $product;


    }

    public function getWebSites()
    {

        $websites = Mage::app()->getWebsites();
        $websiteIds = array();
        foreach ($websites as $website) {
            $websiteIds[] = $website->getWebsiteId();
            break;
        }

        return $websiteIds;
    }


    public function saveProductAttributes($array_product)
    {

        $i = 0;

        if (!isset($array_product['attributes'])) {
            return;
        }



        foreach ($array_product['attributes'] as $attribute) {

            if (isset($attribute['code'])) {

                if (isset($attribute['value']['code'])) {
                    $optionId = $this->getOptionId($attribute['code'], $attribute['value']['code']);
                } else {
                    $optionId = $this->getOptionId($attribute['code'], $attribute['value']['label']);
                }




                if (!empty($optionId)) {

                    $this->setdata[$attribute['code']] = (int)$optionId;

                    $this->tovar[$i] = array(
                        'label' => $attribute['value']['label'], //attribute label
                        'attribute_id' => $this->attributeId, //attribute ID of attribute 'color' in my store
                        'value_index' => $optionId, //value of 'Green' index of the attribute 'color'
                        'is_percent' => '0', //fixed/percent price for this option
                        'pricing_value' => '0.00'
                    );

                    $i++;
                }

            }
        }

    }

    public function getProductCategoriesId($array_product)
    {

        $category = array();


        if (isset($array_product['categories']) && is_array($array_product['categories'])) {

            foreach ($array_product['categories'] as $categories) {
                $ids = Mage::getModel('pluggto/category')->getCategoryByNameOrNew($categories);

                if(is_array($ids)){
                    foreach($ids as $id){
                        $category[] = $id;
                    }
                }


            }

            if (!empty($category)) {
                return $category;
            }
        }
    }

    public function setProductStock($product, $array_product, $simple = false)
    {

        $qtd = null;

        // seta a quantidade
        if (isset($array_product['quantity'])) {
            $qtd = $array_product['quantity'];
        }

        // if quantity is equal a zero, should return
        if ($qtd == null) {
            $qtd = 0;
        }



        if ($product->getEntityId() != null) {

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

            $stockItemData = $stockItem->getData();



            if (empty($stockItemData) || (isset($stockItemData[0]) && empty($stockItemData[0]))) {

                // Create the initial stock item object
                $stockItem->setManageStock(1);


                if ($qtd > 0 || $product->getTypeId() == 'configurable') {
                    $stockItem->setIsInStock(1);
                } else {
                    $stockItem->setIsInStock(0);
                }

                if ($stockItem->getUseConfigManageStock() == null) {
                    $stockItem->setUseConfigManageStock(0);
                }


                $stockItem->setStockId(1);


                if ($stockItem->getProductId() == null) {
                    $stockItem->setProductId($product->getEntityId());
                }

                if($product->getTypeId() == 'simple'){

                    $stockItem->setQty($qtd);
                }




                $this->lockSave();

                $stockItem->save();


                $this->unlockSave();
                Mage::getSingleton('core/session')->setPluggToNotSave(0);
                Mage::getSingleton('core/session')->setPluggToNotSaveStock(0);

                // Init the object again after it has been saved so we get the full object
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            }

            // Set the quantity

            if($qtd > 0 ||  $product->getTypeId() == 'configurable'){
                $stockItem->setIsInStock(1);
            } else {
                $stockItem->setIsInStock(0);
            }

            $stockItem->setQty($qtd);


            $this->lockSave();
            $stockItem->save();
            $this->unlockSave();


        }


    }

    public function saveSimpleProduct($array_product, $parent = false)
    {


        if (Mage::getStoreConfig('pluggto/products/no_update')) {

            return;
        }


        $save = false;
        $this->setdata = array();

        $product = $this->carregaProduto($array_product);




        if (!$product) {

            $product =  Mage::getModel('catalog/product');
            $configs = $this->getConfig();
            $storeView = $configs['products']['product_store_default'];
            if(!$storeView){
                $product->setStoreId($storeView);
            }

        }

        if (Mage::getStoreConfig('pluggto/products/only_qtd')) {



            if ($product->getEntityId() != null && $product->getEntityId() != '') {
                $this->setProductStock($product, $array_product);
            }

            return;
        }

        if ($product->getPluggtoId() != $array_product['id']) {
            $this->setdata['pluggto_id'] = $array_product['id'];
            $save = true;
        }

        if (isset($array_product['timestamp']) && $product->getPluggtoTime() != $array_product['timestamp']) {
            $this->setdata['pluggto_time'] = $array_product['timestamp'];
            $save = true;
        }


        if (isset($array_product['sku']) && $product->getSku() != $array_product['sku']) {
            $this->setdata['sku'] = $array_product['sku'];
            $save = true;
        }

        if (isset($array_product['name']) && $product->getName() != $array_product['name']) {
            $this->setdata['name'] = $array_product['name'];
            $save = true;
        }

        $descriptionField = Mage::getStoreConfig('pluggto/fields/description');

        if(empty($descriptionField)){
            $descriptionField = 'description';
        }

        $productData = $product->getData();

        if (isset($array_product['description']) && $productData[$descriptionField] != $array_product['description']) {
            $this->setdata[$descriptionField] = $array_product['description'];
            $save = true;
        } else {
            $this->setdata[$descriptionField] = $array_product['name'];
        }

        if (isset($array_product['price']) && $product->getPrice() != $array_product['price']) {
            $this->setdata['price'] = $array_product['price'];
            $save = true;
        } elseif (($product->getPrice() == '' || $product->getPrice() == null)) {
            $this->setdata['price'] = $this->price;
            $save = true;
        }


        if ($product->getEntityId() == null || $product->getEntityId() == '') {


            // informações só do Magento
            $this->setdata['status'] = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;


            $this->setdata['type_id'] = Mage_Catalog_Model_Product_Type::TYPE_SIMPLE;
            $this->setdata['tax_class_id'] = 0;
            $this->setdata['attribute_set_id'] = $this->getAttributeSetId($array_product, $parent);

            // assign product to the default website
            $this->setdata['website_ids'] = $this->getWebSites();

            if(isset($array_product['categories']) && !empty($array_product['categories'])){
                // categorias do produto
                $this->setdata['category_ids'] = $this->getProductCategoriesId($array_product);
            }


            // informações de produtos variaveis
            if ($parent) {
                $this->setdata['visibility'] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
                $this->setdata['tax_class_id'] = 0;
                $this->saveProductAttributes($array_product);
            }


            $configs = $this->getConfig();

            foreach ($configs['fields'] as $key => $value) {

                if (!empty($value)) {

                    if ($key != 'width' && $key != 'height' && $key != 'length' && $key != 'weight') {
                        if (isset($array_product[$key])) {
                            $this->setdata[$value] = $array_product[$key];
                        }

                    } else {
                        if (isset($array_product['dimension'][$key])) {
                            $this->setdata[$value] = $array_product['dimension'][$key];
                        }
                    }
                }
            }
        }


        if ($save) {

            $product->addData($this->setdata);


            $this->lockSave();
            $product->save();
            $this->unlockSave();
        }

        if ($parent) {
            foreach ($array_product['attributes'] as $attribute) {
                if (isset($attribute['code'])) {
                    $this->simpleProduts[$product->getEntityId()] = $this->tovar;
                }
            }
        }

        $this->setProductStock($product, $array_product);

        $this->saveImage($product, $array_product, $parent);

    }


    public function saveConfigurableProduct($array_product)
    {


        $save = false;

        if (Mage::getStoreConfig('pluggto/products/no_update')) {
            return;
        }

        if (isset($array_product['price'])) {
            $this->price = $array_product['price'];
        }

        // salva primeiro os produtos filhos
        foreach ($array_product['variations'] as $variation) {
            $this->saveSimpleProduct($variation, $array_product);
        }


        $product = $this->carregaProduto($array_product);


        if (Mage::getStoreConfig('pluggto/products/only_qtd')) {

            if ($product->getEntityId() != null && $product->getEntityId() != '') {
                $this->setProductStock($product, $array_product);
            }
            return;

        };


        if ($product == null) {
            // Serve para ZERAR objeto Product, sem isso não funciona
            $product = Mage::getModel('catalog/product');
        }

        $this->setdata = array();


        // informações que devem ser sincronizadas
        if (isset($array_product['id']) && $product->getPluggtoId() != $array_product['id']) {
            $product->setPluggtoId($array_product['id']);
            $save = true;
        }

        // informações que devem ser sincronizadas
        if (isset($array_product['timestamp']) && $product->getPluggtoTime() != $array_product['timestamp']) {
            $product->setPluggtoTime($array_product['timestamp']);
            $save = true;
        }

        // informações que devem ser sincronizadas
        if (isset($array_product['sku']) && $product->getSku() != $array_product['sku']) {
            $product->setSku($array_product['sku']);
            $save = true;
        }

        // informações que devem ser sincronizadas
        if (isset($array_product['name']) && $product->getName() != $array_product['name']) {
            $product->setName($array_product['name']);
            $save = true;
        }

        $descriptionField = Mage::getStoreConfig('pluggto/fields/description');
        $productData = $product->getData();

        if (empty($descriptionField)) {
            $descriptionField = 'description';
        }

            // informações que devem ser sincronizadas
            if (isset($array_product['description']) && isset($productData[$descriptionField]) && $productData[$descriptionField] != $array_product['description']) {
                $product->addData(array($descriptionField => $array_product['description']));
                ///     $product->setDescription($array_product['description']);
                $save = true;
            } elseif(!isset($productData[$descriptionField])) {
                $product->addData(array($descriptionField => $array_product['description']));
            }



        // informações que devem ser sincronizadas
        if (isset($array_product['dimension']['weight']) && $product->getWeight() != $array_product['dimension']['weight']) {
            $product->setWeight($array_product['dimension']['weight']);
            $save = true;
        }

        // informações que devem ser sincronizadas
        if (isset($array_product['price']) && $product->getPrice() != $array_product['price']) {
            $product->setPrice($array_product['price']);
            $save = true;
        } elseif ($product->getPrice() == null) {
            $product->setPrice(0.00);
            $save = true;
        }

        $new = false;
        $entityId = $product->getEntityId();

        if (empty($entityId)) {

            $save = true;
            $new = true;
            // assign product to the default website

            $catsIds = $this->getProductCategoriesId($array_product);

            // categorias do produto
            if (!empty($catsIds)){
                $product->setCategoryIds($catsIds);
            }

            $product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
            $product->setCreatedAt(strtotime('now'));
            $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
            $product->setAttributeSetId($this->getAttributeSetId($array_product, false));
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
            $product->setTaxClassId(0);
            $product->setMsrpEnabled(1);
            $product->setWebsiteIds($this->getWebSites());


            // teste
            if (!empty($this->attributesIds)) {


                foreach($this->attributesIds as $attrCode){

                    $super_attribute= Mage::getModel('eav/entity_attribute')->load($attrCode);
                    $configurableAtt = Mage::getModel('catalog/product_type_configurable_attribute')->setProductAttribute($super_attribute);

                    $newAttributes[] = array(
                        'id'             => $configurableAtt->getId(),
                        'label'          => $configurableAtt->getLabel(),
                        'position'       => $super_attribute->getPosition(),
                        'values'         => $configurableAtt->getPrices() ? $product->getPrices() : array(),
                        'attribute_id'   => $super_attribute->getId(),
                        'attribute_code' => $super_attribute->getAttributeCode(),
                        'frontend_label' => $super_attribute->getFrontend()->getLabel(),
                    );
                }

                $product->getTypeInstance()->setUsedProductAttributeIds($this->attributesIds);
                $product->setCanSaveConfigurableAttributes(true);
                $attributes = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
                $product->setConfigurableAttributesData($attributes);
            }


            $product->setCanSaveConfigurableAttributes(true);

            $product->setConfigurableProductsData($this->simpleProduts);

            $configs = $this->getConfig();

            foreach ($configs['fields'] as $key => $value) {

                if (!empty($value)) {
                    if ($key != 'width' && $key != 'height' && $key != 'length' && $key != 'weight') {
                        if (isset($array_product[$key])) $varProduct[$value] = $array_product[$key];
                    } else {
                        if (isset($array_product[$key])) $varProduct[$value] = $array_product['dimension'][$key];
                    }
                }
            }

            if (isset($varProduct)) {
                $product->addData($varProduct);
            }
        }


        if ($save) {
            $this->lockSave();
            $product->save();
            $this->unlockSave();
        }


        // salvar imagem
        $this->saveImage($product, $array_product);


        // salvar estoque
        if ($new) {
            $this->setProductStock($product, $array_product);
        }

    }


    public function saveProduct($array_product)
    {

        // check if is simple or configurable
        if (isset($array_product['variations']) && count($array_product['variations']) > 0) {
            // é configurabel
            $this->saveConfigurableProduct($array_product);
        } else {
            // é simples
            $this->saveSimpleProduct($array_product);
        }

    }


    /* Just save PLuggTo ID and PluggTo Time

    public function savePluggtoAttributes($fromPluggTo)
    {


        if (isset($fromPluggTo['Body']['Product'])) {
            $array_product = $fromPluggTo['Body']['Product'];
        } else {
            $array_product = $fromPluggTo;
        }

        $product = $this->carregaProduto($array_product);

        if (isset($product['timestamp'])) $product->setPluggtoTime($array_product['timestamp']);
        $product->setPluggtoId($array_product['id']);
        $product->getResource()->saveAttribute($product, 'pluggto_time')->saveAttribute($product, 'pluggto_id');

        if (isset($array_product['variations']) && !empty($array_product['variations']) && count($array_product['variations'] > 0)) {

            foreach ($array_product['variations'] as $variation) {

                $varproduct = $this->carregaProduto($variation);

                if ($varproduct->getId() != null) {
                    $varproduct->setPluggtoId($variation['id']);
                    $varproduct->getResource()->saveAttribute($varproduct, 'pluggto_id');
                }


            }
        }

    }
    */

    public function formateToPluggto($product,$old = null)
    {


        $product = $this->setProductStore($product);



        $descriptionField = Mage::getStoreConfig('pluggto/fields/description');
        $productData = $product->getData();



        if (empty($descriptionField)) {
            $descriptionField = 'description';
        }



        $data['name'] = trim($product->getName());
        $data['description'] = Mage::helper('cms')->getBlockTemplateProcessor()->filter(trim($productData[$descriptionField]));
        $data['external'] = $product->getEntityId();

        if($product->getSku() != null){
            $data['sku'] = trim($product->getSku());
        }


        $data['price'] = $this->numberFormat($this->getPriceWithTax($product->getPrice(),$product));
        $data['special_price'] = $this->numberFormat($this->getPriceWithTax($this->getProductPriceRule($product),$product));

        $productUrl = $product->getProductUrl();
        $data['link'] = trim($productUrl);

        if (isset($old['photos'])) {
            $fotos = $this->getProducImages($product, $old['photos']);
        } else {
            $fotos = $this->getProducImages($product);
        }

        // images
        if (!empty($fotos)) {
            $data['photos'] = $fotos;
        }

        //  stock
        $stock = $this->getProducQtd($product);

        // check if should sent 0 when product has no quantity
        $chanceDisable = Mage::getStoreConfig('pluggto/products/disable_product');

        if($chanceDisable){

            if($productData['status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                $data['quantity'] = $stock['quantity'];
            } else {
                $data['quantity'] = 0;
            }

        } else {
            $data['quantity'] = $stock['quantity'];
        }






        // get categories
        $categories = $product->getCategoryCollection();
        $categoModel = Mage::getSingleton('pluggto/category');
        $j = 0;


        foreach ($categories as $category) {

            if(is_object($category) && $category->getEntityId() != null && $category->getEntityId() != 1){

                $CategoryTree = array();

                $category = Mage::getModel('catalog/category')->load($category->getEntityId());

                $paths = explode('/', $category->getPath());

                if(is_array($paths)){

                    foreach ($paths as $categ){

                        if($categ != 1){

                            if(isset($this->categoryArray[$categ])){
                                $CategoryTree[] =  $this->categoryArray[$categ]->getName();
                            } else {
                                $category = Mage::getModel('catalog/category')->load($categ);
                                if($category->getEntityId() != null){
                                    $this->categoryArray[$category->getEntityId()] = $category;
                                    $CategoryTree[] = $category->getName();
                                }
                            }
                        }


                    }
                }

                $fullcategory = implode(' > ',$CategoryTree);


                if(!empty($fullcategory)){
                    $data['categories'][$j]['name'] = $fullcategory;
                    $j++;
                }



            }



        }

        $configs = $this->getConfig();
        $allowedAttributes = explode(',', $configs['fields']['allowed_attributes']);


        // variations
        if ($product->getTypeId() == 'configurable') {


            $allaVaris = array();

            $childProducts = $product->getTypeInstance()->getUsedProducts();

            $vararray = array();


            if (isset($old['variations'])) {
                $variacoesBySku = array();
                $variacoesByKey = array();

                foreach ($old['variations'] as $oldvar) {

                    $allaVaris[] = trim($oldvar['sku']);
                    $variacoesBySku[trim($oldvar['sku'])] = $oldvar;


                    if (isset($oldvar['attributes']) && is_array($oldvar['attributes'])) {
                        foreach ($oldvar['attributes'] as $attributes) {

                            if (!in_array($attributes['code'], $vararray)) {
                                $vararray[trim($oldvar['sku'])][] = $attributes['code'];
                            }

                        }
                    }
                }

            }


            $k = 0;

            $configs = $this->getConfig();
            $storeView = $configs['products']['product_store_default'];



            foreach ($childProducts as $Msubproduct) {

                $subproduct = Mage::getModel('catalog/product');

                if(!empty($storeView)){
                    $subproduct->setStoreId($storeView);
                }

                $subproduct->load($Msubproduct->getEntityId());



                if (!isset($variacoesBySku[trim($subproduct->getSku())])) {


                    $productInPluggTo = $this->getProductInPluggto($subproduct->getSku());

                    // check if sku is not in another product, if yes, skip this one
                    if ($productInPluggTo && $productInPluggTo['sku'] != $product->getSku()) {
                        continue;
                    } else {
                        $data['variations'][$k]['sku'] = $subproduct->getSku();
                    }

                } else {
                    $data['variations'][$k]['sku'] = $subproduct->getSku();
                }

                $stock = $this->getProducQtd($subproduct);


                // first try to find by sky that is already setuped in the product
                if (isset($variacoesBySku[trim($subproduct->getSku())])) {
                    $data['variations'][$k]['sku'] = trim($subproduct->getSku());
                    $sku = $subproduct->getSku();
                    unset($allaVaris[array_search(trim($sku), $allaVaris)]);
                    // then try to find by sku not setup
                }

                $data['variations'][$k]['name'] = $subproduct->getName();
                $data['variations'][$k]['external'] = $subproduct->getEntityId();


                // check if should sent 0 when product has no quantity
                $chanceDisable = Mage::getStoreConfig('pluggto/products/disable_product');

                if($chanceDisable){

                    if($productData['status'] == Mage_Catalog_Model_Product_Status::STATUS_ENABLED){
                        $data['variations'][$k]['quantity'] = $stock['quantity'];
                    } else {
                        $data['variations'][$k]['quantity'] = 0;
                    }

                } else {
                    $data['variations'][$k]['quantity'] = $stock['quantity'];
                }


                $varprice = $this->numberFormat($this->getPriceWithTax($subproduct->getPrice(),$subproduct));
                $varspecialprice = $this->numberFormat($this->getPriceWithTax($this->getProductPriceRule($subproduct),$subproduct));

                $data['variations'][$k]['price'] = $varprice;
                $data['variations'][$k]['special_price'] = $varspecialprice;


                if (isset($variacoesByKey[$subproduct->getPluggtoId()]['photos'])) {
                    $data['variations'][$k]['photos'] = $this->getProducImages($subproduct, $variacoesByKey[$subproduct->getPluggtoId()]['photos']);
                } else {
                    $data['variations'][$k]['photos'] = $this->getProducImages($subproduct);
                }

                $variacao = array();
                $this->insertPluggAttributes($subproduct, $variacao);

                $data['variations'][$k] = array_merge($data['variations'][$k], $variacao);


                // changes statys
                $attributes = $subproduct->getAttributes();

                $attributeArray = array();


                foreach ($attributes as $attribute) {

                    $toattribute = array();


                    $code = $attribute->getAttributeCode();

                    if (in_array($code, $allowedAttributes)) {

                        $value = $attribute->getFrontend()->getValue($subproduct);
                        $name = $attribute->getFrontend()->getLabel($subproduct);

                        // do something with $value here



                        if (!empty($value) && !empty($name) && !empty($code)):
                            $toattribute['label'] = $name;
                            $toattribute['code'] = $code;
                            $toattribute['value']['code'] = $value;
                            $toattribute['value']['label'] = $value;
                            $attributeArray[] = $toattribute;
                        endif;

                    }

                    $data['variations'][$k]['attributes'] = $attributeArray;
                }
                $k++;

            }


            // variações para excluir

            foreach ($allaVaris as $all) {

                $data['variations'][$k]['sku'] = trim($all);
                $data['variations'][$k]['quantity'] = 0;
                $k++;
            }

        } else {

            // case is not a configurable product
            if(isset($old['variations']) && is_array($old['variations'])){
                $ki = 0;
                foreach($old['variations'] as $vari){
                    $data['variations'][$ki]['sku'] = $vari['sku'];
                    $data['variations'][$ki]['remove'] = true;
                    $ki++;
                }
            }


        }



        // get attributes por parent
        $attributes = $product->getAttributes();
        $attributeArray = array();

        foreach ($attributes as $attribute) {

            $code = '';
            $toattribute = array();

            $code = $attribute->getAttributeCode();


            if (in_array($code, $allowedAttributes)) {

                $value = $attribute->getFrontend()->getValue($product);
                $name = $attribute->getFrontend()->getLabel($product);

                // do something with $value here
                if (!empty($value) && !empty($name) && !empty($code)):
                    $toattribute['label'] = $name;
                    $toattribute['code'] = $code;
                    $toattribute['value']['code'] = $value;
                    $toattribute['value']['label'] = $value;
                    $attributeArray[] = $toattribute;
                endif;

            }

            $data['attributes'] = $attributeArray;
        }


        $this->insertPluggAttributes($product, $data);

        if (!isset($data['dimension']['weight']) || empty($data['dimension']['weight']) || $data['dimension']['weight'] == 0) {
            $data['dimension']['weight'] = round($this->weight, 2);
        }

        $data['description'] = $data['description'];


        return $data;

    }


    public function insertPluggAttributes($product, &$data)
    {


        $configs = $this->getConfig();
        $pweigh = $product->getWeight();

        if ($pweigh != 0 && !empty($pweigh)) {
            $this->weight = $product->getWeight();
        }


        $magentoProductData = $product->getData();

        if(isset($configs['pricetable']) && !empty($configs['pricetable'])){

        $tableData = array();

            foreach ($configs['pricetable'] as $code => $tabledata){

                $thiscode = array();

                $thiscode['code'] = $code;




                        foreach($tabledata as $key => $attribute){

                            if(isset($magentoProductData[$attribute])){

                                    if($key == 'action'){

                                        $attr = $product->getResource()->getAttribute($attribute);


                                        if (is_object($attr)) {
                                            $attrValue = $attr->getSource()->getOptionText($magentoProductData[$attribute]);
                                        }

                                        $thiscode[$key] = $attrValue;

                                    } else {

                                        $thiscode[$key] = $magentoProductData[$attribute];


                                    }

                            }

                    }

                $tableData[] = $thiscode;

            }


            $data['price_table'] = $tableData;
        }

        foreach ($configs['fields'] as $key => $value) {

            if (!empty($value)) {

                try {

                    $attr = $product->getResource()->getAttribute($value);

                    if (is_object($attr)) {
                        $attrValue = $attr->getSource()->getOptionText($magentoProductData[$value]);
                    }

                    if ($attrValue) {
                        $magentoProductData[$value] = $attrValue;
                    }
                } catch (exception $e) {

                }

                if ($key == 'origin') {

                    if (isset($magentoProductData[$value])) {
                        $op = explode('-', $magentoProductData[$value]);
                        $or = trim($op[0]);
                        if ($or = '0' || $or = '1' || $or = '2') {
                            $data['origin'] = (int)$or;
                        }
                    }
                } elseif ($key != 'width' && $key != 'height' && $key != 'length' && $key != 'weight') {
                    if (isset($magentoProductData[$value])) $data[$key] = $magentoProductData[$value];
                } else {
                    if (isset($magentoProductData[$value])) $data['dimension'][$key] = round($magentoProductData[$value], 2);
                }
            }
        }


    }


    // insert product entity id
    // return product ready to pluggto
    public function getProductDimensions($product)
    {


        $lengthfield = Mage::getStoreConfig('pluggto/fields/length');
        $widthfield = Mage::getStoreConfig('pluggto/fields/width');
        $heightfield = Mage::getStoreConfig('pluggto/fields/height');
        $weightfield = Mage::getStoreConfig('pluggto/fields/weight');
        $productdata = $product->getData();


        foreach ($productdata as $key => $value) {

            $length = $width = $height = $weight = null;


            if ($key == $lengthfield) {
                $length = $value;
            } elseif ($key == $widthfield) {
                $width = $value;
            } elseif ($key == $heightfield) {
                $height = $value;
            } elseif ($key == $weightfield) {
                $weight = $value;
            }

        }

        $dimension['weight'] = $weight;
        $dimension['height'] = $height;
        $dimension['lenght'] = $length;
        $dimension['width'] = $width;


        return $dimension;

    } // end formaToPluggto

    // format raw data to pluggto to

    public function getProducImages($product, $olds = false)
    {


        $media = $product->getData('media_gallery');

        $count = 0;

        $arrayphotos = array();
        $images = null;

        $configs = $this->getConfig();

        if(isset($configs['configs']['send_disable_imagem'])){
            $senddisable = $configs['configs']['send_disable_imagem'];
        } else {
            $senddisable = 1;
        }



        if ($olds) {
            foreach ($olds as $pluggphotos) {

                if (isset($pluggphotos['external'])) {
                    $arrayphotos[$pluggphotos['external']] = $pluggphotos;
                } else {
                    $arrayphotos[] = $pluggphotos;
                }
            }
        }

        if (count($media) > 0) {

            if(isset($configs['products']['product_store_default']) && !empty($configs['products']['product_store_default'])){
                $storeView = $configs['products']['product_store_default'];
                Mage::app()->setCurrentStore($storeView);
            }

            foreach ($media['images'] as $image) {

                // 1) se possui external code não precisa fazer nada já pula para o próximo
                if (isset($arrayphotos[$product->getMediaConfig()->getMediaUrl($image['file'])])) {

                    // se as imagens estiveram em ordens diferentes, necessário reenviar de novo
                    if ($arrayphotos[$product->getMediaConfig()->getMediaUrl($image['file'])]['order'] != $image['position']) {
                        $images[$count]['url'] = (string)$product->getMediaConfig()->getMediaUrl($image['file']);
                        $images[$count]['external'] = (string)$product->getMediaConfig()->getMediaUrl($image['file']);
                        $images[$count]['order'] = $image['position'];
                        $count++;
                    }


                    if ((isset($image['removed']) && $image['removed'] == 1) || ((!$senddisable) && isset($image['disabled']) && $image['disabled'] == 1 )) {
                        // do nothing
                    } else {
                        unset($arrayphotos[$product->getMediaConfig()->getMediaUrl($image['file'])]);
                    }

                    continue;
                }

                /* 2) tenta busca pelo nome do arquivo
                $file = $this->getImageFileNameFromMagento($product->getMediaConfig()->getMediaUrl($image['file']));

                $finded = false;

                foreach ($arrayphotos as $photo) {


                   // achou no pluggto
                    if ($finded) {
                        unset($arrayphotos[array_search($photo['url'], $arrayphotos)]);
                        break;
                    }

                }



                if ($finded) {
                    continue;
                }
                */

                // 3) Não tem no PluggTo, tem que enviar

                // tenta buscar pelo nome do arquivo

                if($senddisable || $image['disabled'] == 0){


                    // deve ser adicionada
                    $images[$count]['url'] = (string)$product->getMediaConfig()->getMediaUrl($image['file']);
                    $images[$count]['external'] = (string)$product->getMediaConfig()->getMediaUrl($image['file']);
                    $images[$count]['order'] = (int)$image['position'];
                    $images[$count]['name'] = (string)$image['label'];
                    $images[$count]['title'] = (string)$image['label'];
                    $images[$count]['disabled'] = (bool)$image['disabled'];

                    if ($image['file'] == $product->getThumbnail()) {
                        $images[$count]['thumb'] = (bool)true;
                    } else {
                        $images[$count]['thumb'] = (bool)false;
                    }


                    $count++;
                }

            }

        }




        if(!isset($configs['products']['allow_remove_images']) || $configs['products']['allow_remove_images']){

            // Imagens que não tem na loja, tem que excluir no PluggTo
            foreach ($arrayphotos as $odimage) {
                $images[$count]['url'] = $odimage['url'];
                $images[$count]['remove'] = true;
                $count++;
            }

        }


        return $images;

    }


    public function getMediaUrl($image = '')
    {
        $image = trim($image);
        $result = Mage::getBaseUrl('media') . 'xmlconnect';

        if ($image) {
            if (strpos($image, '/') !== 0) {
                $image = '/' . $image;
            }
            $result .= $image;
        }
        return $result;
    }



    // in: a product objecto
    // out:: images array

    public function getProducQtd($product, $old = false)
    {

        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getEntityId())->getData();

        if (isset($stock['qty'])) {
            $stock['quantity'] = (int)$stock['qty'];
        } else {
            $stock['quantity'] = null;
        }
        if (isset($stock['min_qty'])) {
            $stock['min_qty'] = (int)$stock['min_qty'];
        } else {
            $stock['min_qty'] = null;
        }
        return $stock;

    } // end getImages

    // get product attributes

    public function formatRawData($datas)
    {

        $raw = array();
        if (is_array($datas)) {

            foreach ($datas as $key => $value) {

                if (is_array($datas[$key])) {

                    if (is_array($value) && !empty($value)) {
                        //	$raw[$key] = $this->formatRawData($value);
                    } else {
                        $raw[$key] = $value;
                    }

                } elseif (!is_object($datas[$key])) {
                    $raw[$key] = $value;
                }
            }
        }

        return $raw;

    }

    public function unLinkAll(){

        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $query = "UPDATE ".$resource->getTableName('catalog_product_entity_varchar')." SET value = '' where attribute_id = (SELECT attribute_id FROM ".$resource->getTableName('eav_attribute')." where attribute_code = 'pluggto_id')";
        $writeConnection->query($query);
    }

    public function disconnectByPluggToId($pluggtoId)
    {

    }


    // get store attributes

    protected function _construct()
    {
        $this->_init("pluggto/product");
    }

    public function lockSave()
    {
        Mage::getSingleton('core/session')->setPluggToNotSave(1);
    }

    public function unlockSave()
    {
        Mage::getSingleton('core/session')->setPluggToNotSave();
    }

    public function getProductInPluggto($sku){


        try {

            $body = array();
            $body['bysku'] = trim($sku);
            $result = Mage::getSingleton('pluggto/api')->load(1)->get('products',$body,'field',true);

            if(isset($result['Body']['result'][0]['Product'])){
                $old = $result['Body']['result'][0]['Product'];
            } else {
                $old = false;
            }

            return $old;


        } catch (exception $e) {
            Mage::helper('pluggto')->WriteLogForModule('Error', print_r($e->getMessage(), 1));
        }

    }

    public function setProductStore($product){

        $configs = $this->getConfig();

        if(isset($configs['products']['product_store_default'])){
            $storeView = $configs['products']['product_store_default'];
        } else {
            $storeView = null;
        }


        if(!empty($storeView)) {
            $nproduct = Mage::getModel('catalog/product')->setStoreId($storeView);
            $nproduct->load($product->getEntityId());
        } else {
            $nproduct = $product;
        }

        return $nproduct;

    }




}