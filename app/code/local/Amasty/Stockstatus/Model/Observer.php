<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Model_Observer
{
    public function salesOrderLoadBefore($observer)
    {
        if (!Mage::getStoreConfig('amstockstatus/general/display_order'))
        {
            return;
        }
        try {
            $order = $observer->getOrder();
            $orderId = $order->getIncrementId();

            $products = array();
            foreach ($order->getItemsCollection() as $item) {
                $productId = $item->getProductId();
                $product = Mage::getModel('catalog/product')->setStore($order->getStoreId())->load($productId);
                $status = Mage::helper('amstockstatus')->getCustomStockStatusText($product, $item->getQtyOrdered());
                if ($status) {
                    $collection = Mage::getResourceModel('amstockstatus/history_collection')
                        ->addFieldToFilter('product_id', $productId)
                        ->addFieldToFilter('order_id', $orderId);

                    if ($collection->getSize() > 0)
                        continue;

                    $model = Mage::getModel('amstockstatus/history');
                    $data = array(
                        'product_id' => $productId,
                        'order_id' => $orderId,
                        'status' => $status,
                    );
                    $model->setData($data);
                    $model->save();
                }
            }
        }
        catch(Exception $ex){
            Mage::log($ex->getMessage());
        }
    }

    public function coreBlockAbstractToHtmlBefore($observer)
    {
        if (Mage::getStoreConfig('amstockstatus/general/display_order'))
        {
            if (($observer->getBlock() instanceof Mage_Sales_Block_Order_Item_Renderer_Default)) {
                if($observer->getBlock()->getTemplate() == "sales/order/items/renderer/default.phtml"){
                    $observer->getBlock()->setTemplate('amasty/amstockstatus/sales/order/items/renderer/default.phtml');
                }
                if($observer->getBlock()->getTemplate() == "bundle/sales/order/items/renderer.phtml"){
                    $observer->getBlock()->setTemplate('amasty/amstockstatus/bundle/sales/order/items/renderer.phtml');
                }

            }
        }

        if (Mage::getStoreConfig('amstockstatus/general/displayinemail')) {
            if (($observer->getBlock() instanceof Mage_Sales_Block_Order_Email_Items_Order_Default)) {
                if ($observer->getBlock()->getTemplate() == "email/order/items/order/default.phtml") {
                    $observer->getBlock()->setTemplate('amasty/amstockstatus/email/order/items/order/default.phtml');
                }
            }
        }
    }
    
    public function onModelSaveBefore($observer)
    {
        $model = $observer->getObject();
        if ($model instanceof Mage_Catalog_Model_Resource_Eav_Attribute)
        {
            if ('custom_stock_status' == $model->getAttributeCode())
            {
                Mage::getModel('amstockstatus/range')->clear(); // deleting all old values
                $ranges = Mage::app()->getRequest()->getPost('amstockstatus_range');
                // saving quantity ranges
                if ($ranges && is_array($ranges) && !empty($ranges))
                {
                    foreach ($ranges as $range)
                    {
                        $data = array(
                            'qty_from'   => $range['from'],
                            'qty_to'     => $range['to'],
                            'status_id'  => $range['status'],
                        );
                        if(Mage::getStoreConfig('amstockstatus/general/use_range_rules')) {
                            $data['rule'] = $range['rule'];
                        }
                        $rangeModel = Mage::getModel('amstockstatus/range');
                        $rangeModel->setData($data);
                        $rangeModel->save();
                    }
                }
            }
        }
    }

    /**
    * Used to show configurable product attributes in case when all elements are out-of-stock
    *
    * "$_product->isSaleable() &&" should be commented out at line #100 (where "container2" block is outputted) in catalog/product/view.phtml
    * to make this work
    *
    * @see Mage_Catalog_Model_Product::isSalable
    * @param object $observer
    */
    public function onCatalogProductIsSalableAfter($observer)
    {
        if (Mage::getStoreConfig('amstockstatus/general/outofstock'))
        {
            $salable = $observer->getSalable();
            $stack = debug_backtrace();
            foreach ($stack as $object)
            {
                if (isset($object['file']))
                {
                    if ($object['file'])
                    {
                        if ( isset($object['file']) && false !== strpos($object['file'], 'options' . DIRECTORY_SEPARATOR . 'configurable'))
                        {
                            $salable->setData('is_salable', true);
                        }
                    }
                }
            }
        }
    }

    public function onProductBlockHtmlBefore($observer)
    {
        if (($observer->getBlock() instanceof Mage_Catalog_Block_Product_View)) {
            $template = $observer->getBlock()->getTemplate();
            if(strpos($template, "view.phtml")){
                $html = $observer->getTransport()->getHtml();

                $product = Mage::registry('product');
                if($product){
                    $html = Mage::helper('amstockstatus')->processViewStockStatus($product, $html);
                }

                $observer->getTransport()->setHtml($html);
            }
       }
    }

    public function salesOrderItemCollectionLoadAfter($observer)
    {
        $collection = $observer->getOrderItemCollection();
        $tmp = array();
        foreach ($collection as $_item){
            if ($_item->getParentItemId()){
                $tmp[$_item->getParentItemId()] = $_item->getProductId();
            }
        }
        foreach ($collection as $_item){
            if (array_key_exists($_item->getItemId(), $tmp)){
                $_item->setData('child_product_id', $tmp[$_item->getItemId()]);
            }
        }
        $observer->setOrderItemCollection($collection);
    }

    /*display stock status at category page*/
    public function onListBlockHtmlBefore($observer)//core_block_abstract_to_html_after    
    {
        if (($observer->getBlock() instanceof Mage_Catalog_Block_Product_List) && Mage::getStoreConfig('amstockstatus/general/display_at_categoty')) {
            $html = $observer->getTransport()->getHtml();
            preg_match_all("/product.*?-price-([0-9]+)/", $html, $productsId);
            preg_match_all("/price-including-tax-([0-9]+)/", $html, $productsTaxId);

            $ids = array_merge($productsId[1], $productsTaxId[1]);
            $ids = array_unique($ids);
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection ->addFieldToFilter('entity_id', array('in'=>$ids));
            $collection ->addStoreFilter(Mage::app()->getStore()->getId())
                        ->addAttributeToSelect('*');

            foreach ($collection as $_product){
                $productId = $_product->getId();
                $template = '@(product.*?-price-'.$productId.'">(.*?)div>)@s';
                preg_match_all($template, $html, $res);
                if(!$res[0]){
                    $template = '@(price-including-tax-'.$productId.'">(.*?)div>)@s';
                     preg_match_all($template, $html, $res);
                     if(!$res[0]){
                         $template = '@(price-excluding-tax-'.$productId.'">(.*?)div>)@s';
                         preg_match_all($template, $html, $res);
                    }
                }
                if($res[0]){
                   $replace = $res[1][0] . Mage::helper('amstockstatus')->showStockStatus($_product, false, true);
                   $html= str_replace($res[0][0], $replace, $html);
                }
            }
            $observer->getTransport()->setHtml($html);
        }
    }
    /*add our tabs for custom stock status attribute*/
    public function onCoreBlockAbstractToHtmlBefore($observer)
    {
        if (($observer->getBlock() instanceof Mage_Adminhtml_Block_Sales_Items_Column_Name)) {
            $observer->getBlock()->setTemplate('amasty/amstockstatus/sales/items/column/name.phtml');
        }
        if (($observer->getBlock() instanceof Mage_Bundle_Block_Adminhtml_Sales_Order_View_Items_Renderer)) {
            $observer->getBlock()->setTemplate('amasty/amstockstatus/bundle/sales/order/view/items/renderer.phtml');
        }

        $block = $observer->getBlock();
        $catalogProductEditTabsClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_attribute_edit_tabs');
        if ($catalogProductEditTabsClass == get_class($block) && 'custom_stock_status' == Mage::registry('entity_attribute')->getData('attribute_code')) {
            if(method_exists ($block, 'addTabAfter' )){
                $block->addTabAfter('icons', array(
                    'label'     => Mage::helper('amstockstatus')->__('Manage Icons'),
                    'title'     => Mage::helper('amstockstatus')->__('Manage Icons'),
                    'content'   => $block->getLayout()->createBlock('amstockstatus/icons')->toHtml(),
                ), "labels");
                $block->addTabAfter('ranges', array(
                    'label'     => Mage::helper('amstockstatus')->__('Quantity Range Statuses'),
                    'title'     => Mage::helper('amstockstatus')->__('Quantity Range Statuses'),
                    'content'   => $block->getLayout()->createBlock('amstockstatus/ranges')->toHtml(),
                ), "labels");
            }
            else{
                $block->addTab('icons', array(
                    'label'     => Mage::helper('amstockstatus')->__('Manage Icons'),
                    'title'     => Mage::helper('amstockstatus')->__('Manage Icons'),
                    'content'   => $block->getLayout()->createBlock('amstockstatus/icons')->toHtml(),
                ));
                $block->addTab('ranges', array(
                    'label'     => Mage::helper('amstockstatus')->__('Quantity Range Statuses'),
                    'title'     => Mage::helper('amstockstatus')->__('Quantity Range Statuses'),
                    'content'   => $block->getLayout()->createBlock('amstockstatus/ranges')->toHtml(),
                ));
            }
        }

        return $this;
    }
}