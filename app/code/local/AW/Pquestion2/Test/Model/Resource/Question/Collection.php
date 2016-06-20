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


class AW_Pquestion2_Test_Model_Resource_Question_Collection extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function addFilterByProduct($product, $isLoadAsModel, $expectation)
    {
        if ($isLoadAsModel) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        $collection = Mage::getModel('aw_pq2/question')->getCollection();
        $collection->addFilterByProduct($product);
        $this->assertEquals(
            $expectation['collection_item_count'],
            count($collection->getItems()),
            'Check count of collection items'
        );
    }

    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function addFilterByCustomer($customer, $isLoadAsModel, $expectation)
    {
        if ($isLoadAsModel) {
            $customer = Mage::getModel('customer/customer')->load($customer);
        }
        $collection = Mage::getModel('aw_pq2/question')->getCollection();
        $collection->addFilterByCustomer($customer);
        $this->assertEquals(
            $expectation['collection_item_count'],
            count($collection->getItems()),
            'Check count of collection items'
        );
    }

    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function addShowInStoresFilter($storeId, $expectation)
    {
        $collection = Mage::getModel('aw_pq2/question')->getCollection();
        $collection->addShowInStoresFilter($storeId);
        $this->assertEquals(
            $expectation['collection_item_count'],
            count($collection->getItems()),
            'Check count of collection items'
        );
    }

    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function addPublicFilter($expectation)
    {
        $collection = Mage::getModel('aw_pq2/question')->getCollection();
        $collection->addPublicFilter();
        $this->assertEquals(
            $expectation['collection_item_count'],
            count($collection->getItems()),
            'Check count of collection items'
        );
    }

    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function addPrivateFilter($expectation)
    {
        $collection = Mage::getModel('aw_pq2/question')->getCollection();
        $collection->addPrivateFilter();
        $this->assertEquals(
            $expectation['collection_item_count'],
            count($collection->getItems()),
            'Check count of collection items'
        );
    }

}