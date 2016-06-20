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


class AW_Pquestion2_Block_Question_List extends Mage_Core_Block_Template
{
    /**
     * @return mixed
     */
    public function canShow()
    {
        return Mage::helper('aw_pq2/config')->getIsEnabled();
    }

    /**
     * @return bool
     */
    public function canAskQuestion()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return true;
        }
        return Mage::helper('aw_pq2/config')->getAllowGuestToAskQuestion();
    }

    /**
     * @return bool
     */
    public function canAnswerQuestion()
    {
        switch (Mage::helper('aw_pq2/config')->getAllowCustomerToAddAnswer()) {
            case AW_Pquestion2_Model_Source_ProductPageCustomerAllowOptions::DENIED_VALUE:
                return false;
                break;
            case AW_Pquestion2_Model_Source_ProductPageCustomerAllowOptions::REGISTERED_CUSTOMERS_BOUGHT_PRODUCT_VALUE:
                if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                    return false;
                }
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if (!Mage::helper('aw_pq2')->isCustomerBoughtProduct($customer, $this->getProduct())) {
                    return false;
                }
                return true;
            case AW_Pquestion2_Model_Source_ProductPageCustomerAllowOptions::REGISTERED_CUSTOMERS_VALUE:
                if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                    return true;
                }
                return false;
            case AW_Pquestion2_Model_Source_ProductPageCustomerAllowOptions::ALL_CUSTOMERS_VALUE:
                return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getVoteMap()
    {
        if (!$this->hasData('vote_map')) {
            $questionIdList = $this->getQuestionCollection()->getAllIds();
            $voteMap = Mage::helper('aw_pq2/helpfulness')->getVoteMap($questionIdList);
            $this->setData('vote_map', $voteMap);
        }
        return $this->getData('vote_map');
    }

    /**
     * @return bool
     */
    public function isCustomerCanVoteQuestion()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::helper('aw_pq2/config')->isAllowGuestRateHelpfulness()
        ;
    }

    /**
     * @return bool
     */
    public function isCustomerCanVoteAnswer()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::helper('aw_pq2/config')->isAllowGuestRateHelpfulness()
        ;
    }

    /**
     * @return string
     */
    public function getTitleForQuestionVote()
    {
        if (!$this->isCustomerCanVoteQuestion()) {
            return $this->__("Only registered customers can rate helpfulness");
        }
        return "";
    }

    /**
     * @return string
     */
    public function getTitleForAnswerVote()
    {
        if (!$this->isCustomerCanVoteAnswer()) {
            return $this->__("Only registered customers can rate helpfulness");
        }
        return "";
    }

    /**
     * @param int $questionId
     *
     * @return bool
     */
    public function isCustomerLikeQuestion($questionId)
    {
        $voteMap = $this->getVoteMap();
        $voteMap = $voteMap['question_vote_map'];
        if (array_key_exists($questionId, $voteMap) && $voteMap[$questionId] == 1) {
            return true;
        }
        return false;
    }

    /**
     * @param int $questionId
     *
     * @return bool
     */
    public function isCustomerDislikeQuestion($questionId)
    {
        $voteMap = $this->getVoteMap();
        $voteMap = $voteMap['question_vote_map'];
        if (array_key_exists($questionId, $voteMap) && $voteMap[$questionId] == -1) {
            return true;
        }
        return false;
    }

    /**
     * @param int $answerId
     *
     * @return bool
     */
    public function isCustomerLikeAnswer($answerId)
    {
        $voteMap = $this->getVoteMap();
        $voteMap = $voteMap['answer_vote_map'];
        if (array_key_exists($answerId, $voteMap) && $voteMap[$answerId] == 1) {
            return true;
        }
        return false;
    }

    /**
     * @param int $answerId
     *
     * @return bool
     */
    public function isCustomerDislikeAnswer($answerId)
    {
        $voteMap = $this->getVoteMap();
        $voteMap = $voteMap['answer_vote_map'];
        if (array_key_exists($answerId, $voteMap) && $voteMap[$answerId] == -1) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getAnswerMessage()
    {
        switch (Mage::helper('aw_pq2/config')->getAllowCustomerToAddAnswer()) {
            case AW_Pquestion2_Model_Source_ProductPageCustomerAllowOptions::REGISTERED_CUSTOMERS_BOUGHT_PRODUCT_VALUE:
                if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                    return $this->__(
                        'You must be <a href="%s">logged in</a> to answer questions.', $this->getLoginUrl()
                    );
                }
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if (!Mage::helper('aw_pq2')->isCustomerBoughtProduct($customer, $this->getProduct())) {
                    return $this->__('Only customers who bought the product can answer questions.');
                }
                break;
            case AW_Pquestion2_Model_Source_ProductPageCustomerAllowOptions::REGISTERED_CUSTOMERS_VALUE:
                return $this->__('You must be <a href="%s">logged in</a> to answer questions.', $this->getLoginUrl());
        }
        return '';
    }

    /**
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function getQuestionCollection()
    {
        $collection = Mage::getResourceModel('aw_pq2/question_collection');
        $collection
            ->addFilterByProduct($this->getProduct())
            ->addShowInStoresFilter(Mage::app()->getStore()->getId())
            ->addPublicFilter()
            ->addApprovedStatusFilter()
            ->addCreatedAtLessThanNowFilter()
        ;
        if ($this->_getCurrentSortBy() == AW_Pquestion2_Model_Source_Question_Sorting::DATE_VALUE) {
            $collection->sortByDate($this->_getCurrentSortDir());
        } else {
            $collection->sortByHelpfull($this->_getCurrentSortDir());
        }
        return $collection;
    }

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return AW_Pquestion2_Model_Resource_Answer_Collection
     */
    public function getAnswerCollectionForQuestion(AW_Pquestion2_Model_Question $question)
    {
        return $question->getApprovedAnswerCollection()
            ->addCreatedAtLessThanNowFilter()
        ;
    }

    /**
     * @return string
     */
    public function getQuestionPageSize()
    {
        return Zend_Json::encode(Mage::helper('aw_pq2/config')->getNumberQuestionsToDisplay());
    }

    /**
     * @return string
     */
    public function getAnswerPageSize()
    {
        return Zend_Json::encode(Mage::helper('aw_pq2/config')->getNumberAnswersToDisplay());
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return Mage::getUrl(
            'customer/account/index', array('_secure' => Mage::app()->getFrontController()->getRequest()->isSecure())
        );
    }

    /**
     * @param AW_Pquestion2_Model_Question $questionModel
     *
     * @return string
     */
    public function getLikeQuestionUrl(AW_Pquestion2_Model_Question $questionModel)
    {
        return Mage::getUrl(
            'aw_pq2/question/like',
            array(
                '_secure'     => Mage::app()->getFrontController()->getRequest()->isSecure(),
                'question_id' => $questionModel->getId(),
            )
        );
    }

    /**
     * @param AW_Pquestion2_Model_Question $questionModel
     *
     * @return string
     */
    public function getDislikeQuestionUrl(AW_Pquestion2_Model_Question $questionModel)
    {
        return Mage::getUrl(
            'aw_pq2/question/dislike',
            array(
                '_secure'     => Mage::app()->getFrontController()->getRequest()->isSecure(),
                'question_id' => $questionModel->getId(),
            )
        );
    }

    /**
     * @param AW_Pquestion2_Model_Answer $answerModel
     *
     * @return string
     */
    public function getLikeAnswerUrl(AW_Pquestion2_Model_Answer $answerModel)
    {
        return Mage::getUrl(
            'aw_pq2/answer/like',
            array(
                '_secure'   => Mage::app()->getFrontController()->getRequest()->isSecure(),
                'answer_id' => $answerModel->getId(),
            )
        );
    }

    /**
     * @param AW_Pquestion2_Model_Answer $answerModel
     *
     * @return string
     */
    public function getDislikeAnswerUrl(AW_Pquestion2_Model_Answer $answerModel)
    {
        return Mage::getUrl(
            'aw_pq2/answer/dislike',
            array(
                '_secure'   => Mage::app()->getFrontController()->getRequest()->isSecure(),
                'answer_id' => $answerModel->getId(),
            )
        );
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (Mage::registry('current_product')) {
            return Mage::registry('current_product');
        }
        return Mage::getModel('catalog/product')->load(Mage::helper('aw_pq2/request')->getRewriteProductId());
    }

    /**
     * @param AW_Pquestion2_Model_Answer $answer
     *
     * @return string
     */
    public function getAnswerContent(AW_Pquestion2_Model_Answer $answer)
    {
        $content = $this->escapeHtml($answer->getContent());
        if (Mage::helper('aw_pq2/config')->isAllowDisplayUrlAsLink()) {
            $content = Mage::helper('aw_pq2')->parseContentUrls($content);
        }
        return nl2br($content);
    }

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return string
     */
    public function getQuestionContent(AW_Pquestion2_Model_Question $question)
    {
        $content = $this->escapeHtml($question->getContent());
        if (Mage::helper('aw_pq2/config')->isAllowDisplayUrlAsLink()) {
            $content = Mage::helper('aw_pq2')->parseContentUrls($content);
        }
        return nl2br($content);
    }

    protected function _getCurrentSortBy()
    {
        return $this->getRequest()->getParam(
            'orderby',
            Mage::helper('aw_pq2/config')->getDefaultQuestionsSortBy()
        );
    }

    protected function _getCurrentSortDir()
    {
        return Mage::getModel('aw_pq2/source_question_sorting_dir')->getStorageValue(
            $this->getRequest()->getParam(
                'dir',
                Mage::helper('aw_pq2/config')->getDefaultSortOrder()
            )
        );
    }
}