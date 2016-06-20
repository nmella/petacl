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


class AW_Pquestion2_Block_Customer_Question_Grid extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->_prepareCollection();
    }

    protected function _prepareCollection()
    {
        $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
        /** @var AW_Pquestion2_Model_Resource_Question_Collection $collection */
        $collection = Mage::getModel('aw_pq2/question')->getCollection();
        $collection->addFilterByCustomer($currentCustomer);
        $this->setCollection($collection);
        return $this;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales.order.history.pager')
            ->setCollection($this->getCollection())
        ;
        $this->setChild('pager', $pager);
        $this->getCollection()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return string
     */
    public function getIsAnsweredLabelForQuestion(AW_Pquestion2_Model_Question $question)
    {
        $answerCount = $question->getApprovedAnswerCollection()->getSize();
        if ($answerCount < 1) {
            return $this->__('Not yet');
        }
        if ($answerCount === 1) {
            return $this->__('Yes (1 answer)');
        }
        return $this->__('Yes (%s answers)', $answerCount);
    }

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return string
     */
    public function getStatusLabelByQuestion(AW_Pquestion2_Model_Question $question)
    {
        return Mage::getModel('aw_pq2/source_question_status')->getOptionByValue($question->getStatus());
    }

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return string
     */
    public function getProductUrlByQuestion(AW_Pquestion2_Model_Question $question)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $question->getProduct();
        if (null === $product->getId()) {
            return '';
        }
        return $product->getProductUrl();
    }

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return string
     */
    public function getProductNameByQuestion(AW_Pquestion2_Model_Question $question)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $question->getProduct();
        if (null === $product->getId()) {
            return '';
        }
        return $product->getName();
    }
}