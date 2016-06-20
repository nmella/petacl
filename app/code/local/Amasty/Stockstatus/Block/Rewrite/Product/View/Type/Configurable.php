<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Block_Rewrite_Product_View_Type_Configurable extends Amasty_Stockstatus_Block_Rewrite_Product_View_Type_Configurable_Pure
{
    protected $_options;

    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);
        if ('product.info.options.configurable' == $this->getNameInLayout()  && !Mage::app()->getRequest()->isAjax())
        {
            $aStockStatus = array();
            Mage::register('main_product', $this->getProduct());
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());

            $_attributes = $this->getProduct()->getTypeInstance(true)->getConfigurableAttributes($this->getProduct());
            foreach ($allProducts as $product)
            {

                $key = array();
                foreach ($_attributes as $attribute)
                {
                    $key[] = $product->getData($attribute->getData('product_attribute')->getData('attribute_code'));
                }
                
                $stockStatus = '';
                if ( !Mage::getStoreConfig('amstockstatus/general/displayforoutonly') || !$product->isSaleable())
                {
                    $stockStatus = Mage::helper('amstockstatus')->getCustomStockStatusText($product);
                }
                if ($key)
                {
                    $strKey = implode(',', $key);
                    if('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Xnotif/active')){
                        $stockalert = Mage::helper('amxnotif')->getStockAlert($product, $this->getProduct()->getId());
                    }
                    else{
                        $stockalert = Mage::helper('amstockstatus')->getStockAlert($product);
                    }
                    $aStockStatus[$strKey] = array(
                        'is_in_stock'   => $product->isSaleable(),
                        'custom_status' => $stockStatus,
                        'custom_status_icon' =>  Mage::helper('amstockstatus')->getStatusIconImage($product),
                        'custom_status_icon_only' => Mage::getStoreConfig('amstockstatus/general/icononly'),
                        'is_qnt_0'      => (int)($product->isInStock()),
                        'product_id'    => $product->getId(),
                        'stockalert'	=> $stockalert,
                    );

                    if (! $aStockStatus[$strKey]['is_in_stock'] && ! $aStockStatus[$strKey]['custom_status']){
                        $aStockStatus[$strKey]['custom_status'] = Mage::helper('amstockstatus')->__('Out of Stock');
                    }
                    $pos = strrpos($strKey, ",");

                    if($pos){
                        $newKey = substr($strKey, 0, $pos);
                        if(array_key_exists($newKey, $aStockStatus)){
                            if($aStockStatus[$newKey]['custom_status'] !=  $aStockStatus[$strKey]['custom_status']){
                                $aStockStatus[$newKey] = null;
                            }
                        }
                        else{
                            $aStockStatus[$newKey] =  $aStockStatus[$strKey];
                        }
                    }
                }
            }

            if (Mage::getStoreConfig('amstockstatus/general/change_custom_configurable_status')) {
                $tmpHtml = '<script type="text/javascript"> var changeConfigurableStatus = true;';
            } else {
                $tmpHtml = '<script type="text/javascript"> var changeConfigurableStatus = false;';
            }
            $tmpHtml .=  'var amStAutoSelectAttribute = ' . intval(Mage::getStoreConfig('amstockstatus/general/auto_select_attribute')) . '; ';

            $html = $tmpHtml . ' var stStatus = new StockStatus(' . Zend_Json::encode($aStockStatus) . ');</script>' . $html;
        }

        return $html;
    }
    
    public function getAllowProducts()
    {
        if (!$this->hasAllowProducts()) {
            $products = array();
            $allProducts = $this->getProduct()->getTypeInstance(true)
                ->getUsedProducts(null, $this->getProduct());
            foreach ($allProducts as $product) {
                /**
                * Should show all products (if setting set to Yes), but not allow "out of stock" to be added to cart
                */
                 if ($product->isSaleable() ||(Mage::getStoreConfig('amstockstatus/general/outofstock') &&  !('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Conf/active') && Mage::registry('isList')))) {
                    if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                    {
                        $products[] = $product;
                    }
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }
}