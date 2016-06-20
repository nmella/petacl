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


class AW_Pquestion2_Test_Model_Question extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function getAnswerCollection($questionId, $expectation)
    {
        $answerCollectionList = array();
        $answerCollectionList[] = Mage::getModel('aw_pq2/question')->load($questionId)->getAnswerCollection();
        $answerCollectionList[] = Mage::getModel('aw_pq2/question')->setId($questionId)->getAnswerCollection();

        foreach ($answerCollectionList as $answerCollection) {
            $this->assertInstanceOf(
                'AW_Pquestion2_Model_Resource_Answer_Collection', $answerCollection,
                'Question must return collection object always!'
            );
            $itemsCount = count($answerCollection->getItems());
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
    public function getHelpfulCollection($questionId, $expectation)
    {
        $helpfulCollectionList = array();
        $helpfulCollectionList[] = Mage::getModel('aw_pq2/question')->load($questionId)->getHelpfulCollection();
        $helpfulCollectionList[] = Mage::getModel('aw_pq2/question')->setId($questionId)->getHelpfulCollection();

        foreach ($helpfulCollectionList as $helpfulCollection) {
            $this->assertInstanceOf(
                'AW_Pquestion2_Model_Resource_Summary_Question_Collection', $helpfulCollection,
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
    public function addHelpful($questionId, $customerId, $visitorId, $visitorData, $value, $expectation)
    {
        $customer = null;
        if (null !== $customerId) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
        } else {
            $customer = Mage::getModel('log/visitor')->load($visitorId);
            $customer->addData($visitorData);
        }
        $question = Mage::getModel('aw_pq2/question')->load($questionId);

        $isException = false;
        try {
            $question->addHelpful($customer, $value);
        } catch (Exception $e) {
            $isException = true;
        }
        $this->assertEquals($expectation['exception'], $isException);

        $this->assertEquals(
            $expectation['question_helpfulness'],
            $question->getHelpfulness()
        );

        $summaryQuestionCollection = $question->getHelpfulCollection();
        if (null !== $customerId || null !== $customer->getCustomerId()) {
            $summaryQuestionCollection->addFieldToFilter(
                'customer_id',
                (null !== $customerId) ? $customerId : $customer->getCustomerId()
            );
        } else {
            $summaryQuestionCollection->addFieldToFilter('visitor_id', $visitorId);
        }

        $this->assertLessThanOrEqual(
            1,
            count($summaryQuestionCollection->getItems()),
            'Row must be single in keys (answer_id, customer_id, visitor_id)'
        );

        $summaryQuestion = $summaryQuestionCollection->getFirstItem();
        $this->assertEquals(
            $summaryQuestion->getHelpful(),
            $expectation['summary_question']
        );
    }

    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function addAnswer($questionId, $answerId, $answerData, $expectation)
    {
        $question = Mage::getModel('aw_pq2/question')->load($questionId);
        $answer = Mage::getModel('aw_pq2/answer')->load($answerId);
        $answer->addData($answerData);
        $currentAnswerQuestionModel = Mage::getModel('aw_pq2/question')->load($answer->getQuestionId());

        $isException = false;
        try {
            $question->addAnswer($answer);
        } catch (Exception $e) {
            $isException = true;
        }
        $this->assertEquals($expectation['exception'], $isException);

        $this->assertEquals(
            count($question->getAnswerCollection()->getItems()),
            $expectation['answer_count'],
            'Expected and actual answer count is not equal'
        );

        //check answers count in previous question
        if (null !== $currentAnswerQuestionModel->getId()) {
            $this->assertEquals(
                count($currentAnswerQuestionModel->getAnswerCollection()->getItems()),
                $expectation['answer_count_in_previous_answer_question'],
                'Expected and actual answer count is not equal'
            );
        }
    }

    /**
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function saveAndLoad($questionId, $questionData, $expectation)
    {
        $this->_registerNotificationObserverMock();
        $model = Mage::getModel('aw_pq2/question');
        if (null !== $questionId) {
            $model->load($questionId);
        }
        $model->addData($questionData);

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
        $model = Mage::getModel('aw_pq2/question')->load($model->getId());
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
            ->method('questionSaveAfter')
            ->will($this->returnValue($user))
        ;
        $this->replaceByMock('model', 'aw_pq2/observer', $user);
        return $this;
    }


}