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


class AW_Pquestion2_Test_Model_Answer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function getHelpfulCollection($answerId, $expectation)
    {
        $helpfulCollectionList = array();
        $helpfulCollectionList[] = Mage::getModel('aw_pq2/answer')->load($answerId)->getHelpfulCollection();
        $helpfulCollectionList[] = Mage::getModel('aw_pq2/answer')->setId($answerId)->getHelpfulCollection();

        foreach ($helpfulCollectionList as $helpfulCollection) {
            $this->assertInstanceOf(
                'AW_Pquestion2_Model_Resource_Summary_Answer_Collection', $helpfulCollection,
                'Question must return collection object always!'
            );
            $itemsCount = count($helpfulCollection->getItems());
            $this->assertEquals(
                $expectation['collection_item_count'],
                $itemsCount
            );
        }
    }

    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function addHelpful($answerId, $customerId, $visitorId, $visitorData, $value, $expectation)
    {
        $customer = null;
        if (null !== $customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
        } else {
            $customer = Mage::getModel('log/visitor')->load($visitorId);
            $customer->addData($visitorData);
        }
        $answer = Mage::getModel('aw_pq2/answer')->load($answerId);

        $isException = false;
        try {
            $answer->addHelpful($customer, $value);
        } catch (Exception $e) {
            $isException = true;
        }
        $this->assertEquals($expectation['exception'], $isException);

        $this->assertEquals(
            $expectation['answer_helpfulness'],
            $answer->getHelpfulness()
        );

        $summaryAnswerCollection = $answer->getHelpfulCollection();
        if (null !== $customerId || null !== $customer->getCustomerId()) {
            $summaryAnswerCollection->addFieldToFilter(
                'customer_id',
                (null !== $customerId) ? $customerId : $customer->getCustomerId()
            );
        } else {
            $summaryAnswerCollection->addFieldToFilter('visitor_id', $visitorId);
        }

        $this->assertLessThanOrEqual(
            1,
            count($summaryAnswerCollection->getItems()),
            'Row must be single in keys (answer_id, customer_id, visitor_id)'
        );

        $summaryAnswer = $summaryAnswerCollection->getFirstItem();
        $this->assertEquals(
            $expectation['summary_answer'],
            $summaryAnswer->getHelpful(),
            'Check value in summary entity'
        );
    }

    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function saveAndLoad($answerId, $answerData, $expectation)
    {
        $this->_registerNotificationObserverMock();
        $model = Mage::getModel('aw_pq2/answer');
        if (null !== $answerId) {
            $model->load($answerId);
        }
        $model->addData($answerData);

        //check on save
        $originalData = new Varien_Object($model->getData());
        $isException = false;
        try {
            $model->save();
        } catch (Exception $e) {
            $isException = true;
        }
        $this->assertEquals($expectation['exception_on_save'], $isException);

        if (null === $model->getId()) {
            return;
        }
        $this->_compareTwoVarienObjectData($originalData, $model);

        //check on load
        $currentData = new Varien_Object($model->getData());
        $model = Mage::getModel('aw_pq2/answer')->load($model->getId());
        $this->_compareTwoVarienObjectData($currentData, $model);

    }

    protected function _compareTwoVarienObjectData(Varien_Object $first, Varien_Object $second, $skipFields = array())
    {
        foreach ($first->getData() as $field => $value) {
            $modelValue = $second->getData($field);
            if (is_object($value) || is_object($modelValue)) {
                continue;
            }
            if (is_array($value)) {
                $value = json_encode($value);
            }
            if (is_array($modelValue)) {
                $modelValue = json_encode($modelValue);
            }
            if (in_array($field, $skipFields)) {
                continue;
            }
            $this->assertEquals($modelValue, $value);
        }
        return $this;
    }

    protected function _registerNotificationObserverMock()
    {
        $user = $this->getModelMock('aw_pq2/observer');
        $user->expects($this->any())
            ->method('answerSaveAfter')
            ->will($this->returnValue($user))
        ;
        $this->replaceByMock('model', 'aw_pq2/observer', $user);
        return $this;
    }
}