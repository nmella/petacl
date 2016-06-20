<?php

class Moogento_Pickpack_Helper_Order extends Mage_Core_Helper_Abstract
{
    protected $_itemsCache = array();

    public function getItemsToProcess($order, $allItems = false) {
        if (isset($this->_itemsCache[$order->getId()]) && !$allItems) {
            return $this->_itemsCache[$order->getId()];
        }

        $items = array();
        $generalConfig = Mage::helper('pickpack/config')->getGeneralConfigArray($order->getStoreId());
        $itemCollection = $allItems ? $order->getAllItems() : $order->getAllVisibleItems();
        foreach ($itemCollection as $item) {
            if($generalConfig['filter_virtual_products_yn'] == 1
                && ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL
                    || $item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE)) {
                continue;
            }
            $items[] = $item;
        }

        $this->_itemsCache[$order->getId()] = $items;

        return $items;
    }
} 