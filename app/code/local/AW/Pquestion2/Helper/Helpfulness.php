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


class AW_Pquestion2_Helper_Helpfulness extends Mage_Core_Helper_Abstract
{
    /**
     * @param array $questionIdList
     *
     * @return array
     */
    public function getVoteMap($questionIdList)
    {
        $voteMap = array(
            'question_vote_map' => array(),
            'answer_vote_map'   => array(),
        );
        if (count($questionIdList) <= 0) {
            return $voteMap;
        }
        /* @var $collection AW_Pquestion2_Model_Resource_Summary_Question_Collection*/
        $collection = Mage::getModel('aw_pq2/summary_question')->getCollection();
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array('question_id', 'helpful'));
        $collection->addFilterByQuestionIds($questionIdList);

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $collection->addFieldToFilter('customer_id', $customer->getId());
        } else {
            $visitor = Mage::getSingleton('log/visitor');
            $collection->addFieldToFilter('visitor_id', $visitor->getId());
        }

        $voteMap['question_vote_map'] = $collection->toOptionHash();
        $voteMap['answer_vote_map'] = $this->_getAnswerVoteMap($questionIdList);

        return $voteMap;
    }

    protected function _getAnswerVoteMap($questionIdList)
    {

        $answerCollection = Mage::getModel('aw_pq2/answer')->getCollection();
        $answerCollection->getSelect()->where('question_id in(?)', $questionIdList);

        $answerIdList = $answerCollection->getAllIds();


        /* @var $collection AW_Pquestion2_Model_Resource_Summary_Question_Collection*/
        $collection = Mage::getModel('aw_pq2/summary_answer')->getCollection();
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array('answer_id', 'helpful'));
        $collection->addFilterByAnswerIds($answerIdList);

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $collection->addFieldToFilter('customer_id', $customer->getId());
        } else {
            $visitor = Mage::getSingleton('log/visitor');
            $collection->addFieldToFilter('visitor_id', $visitor->getId());
        }
        return $collection->toOptionHash();
    }
}