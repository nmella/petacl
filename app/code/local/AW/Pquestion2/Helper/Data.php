<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Pquestion2
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Pquestion2_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getStoreLabel($storeId)
    {
        $store = Mage::getModel('core/store')->load($storeId);
        if (null === $store->getId()) {
            return $this->__('[DELETED]');
        }
        return $store->getWebsite()->getName()
            . '/' . $store->getGroup()->getName()
            . '/' . $store->getName()
        ;
    }

    /**
     * @param int | Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     *
     * @return array
     */
    public function getCustomerEmailListWhoBoughtProductFewDaysAgo($product, $store = null)
    {
        $salesCollection = $this->getCustomerCollectionWhoBoughtProductFewDaysAgo($product, $store);
        return array_unique($salesCollection->getColumnValues('customer_email'));
    }

    /**
     * @param int | Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     *
     * @return array
     */
    public function getCustomerCollectionWhoBoughtProductFewDaysAgo($product, $store = null)
    {
        if (!$store instanceof Mage_Core_Model_Store) {
            try {
                $store = Mage::app()->getStore($store);
            } catch (Mage_Core_Model_Store_Exception $e) {
                $store = Mage::app()->getStore();
            }
        }
        $store = $store->getId();
        $days = Mage::helper('aw_pq2/config')->getBoughtProductDaysAgo($store);
        $salesCollection = $this->getCustomerWhoBoughtProductCollection($product);
        if (null !== $days) {
            $date = new Zend_Date();
            $date->subDay($days);
            $salesCollection->addFieldToFilter(
                'main_table.created_at',
                array('date' => array('from' => $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)))
            );
        }
        $salesCollection->addFieldToFilter('main_table.store_id', $store);
        return $salesCollection;
    }

    public function getCustomerWhoBoughtProductCollection($product)
    {
        $salesCollection = Mage::getModel('sales/order')->getCollection();
        $select = $salesCollection->getSelect();
        $itemTableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
        $quoteTableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_quote');
        $select
            ->joinLeft(
                array('item_table' => $itemTableName),
                "item_table.order_id = main_table.entity_id",
                array('product_id' => 'item_table.product_id')
            )
            ->joinLeft(
                array('quote_table' => $quoteTableName),
                "quote_table.entity_id = main_table.quote_id",
                array('customer_email' => 'quote_table.customer_email')
            )
        ;
        if (!$product instanceof Mage_Catalog_Model_Product) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        $_productId = $product->getId();
        if (null === $_productId) {
            $_productId = 0;
        }
        $salesCollection->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        $salesCollection->addFieldToFilter('product_id', $_productId);
        $salesCollection->getSelect()->group('quote_table.customer_email');
        return $salesCollection;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isCustomerBoughtProduct($customer, $product)
    {
        $_customerEmails = $this->getCustomerWhoBoughtProductCollection($product)->getColumnValues('customer_email');
        return in_array($customer->getEmail(), $_customerEmails);
    }

    /**
     * @param $content
     *
     * @return mixed
     */
    public function parseContentUrls($content)
    {
        return preg_replace(
            '/\b(?:(http(s?):\/\/)|(?=www\.))(\S+)/is',
            '<a href="http$2://$3" target="_blank">$1$3</a>',
            $content
        );
    }

    public function getPointsEmailVariables()
    {
        $_pointsVariables = array(
            'points_enabled' => false,
            'points_registration_amount' => 0,
            'points_amount' => 0
        );
        if ($this->isModuleEnabled('AW_Points')) {
            $_pointsVariables = array(
                'points_enabled'             => true,
                'points_registration_amount' => Mage::helper('points/config')->getPointsForRegistration(),
                'points_amount'              => Mage::helper('points/config')->getPointsForAnsweringProductQuestion()
            );
        }
        return $_pointsVariables;
    }

    public function checkMageVersion($version = '1.5.0.0', $expression = '<')
    {
        return version_compare(Mage::getVersion(), $version, $expression);
    }
}