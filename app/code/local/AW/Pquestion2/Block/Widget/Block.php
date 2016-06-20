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


class AW_Pquestion2_Block_Widget_Block extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    protected $_collection = null;
    protected $_pageSize = 5;

    /**
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function getCollection()
    {
        if ($this->_collection == null) {
            $collection = Mage::getResourceModel('aw_pq2/question_collection');
            $collection
                ->addShowInStoresFilter(Mage::app()->getStore()->getId())
                ->addPublicFilter()
                ->addApprovedStatusFilter()
                ->addCreatedAtLessThanNowFilter()
                ->sortByHelpfull()
                ->setPageSize($this->_pageSize)
            ;
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    /**
     * @return bool
     */
    public function canShow()
    {
        return Mage::helper('aw_pq2/config')->getIsEnabled() && ($this->getCollection()->getSize() > 0);
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

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return string
     */
    public function getDate(AW_Pquestion2_Model_Question $question)
    {
        return $this->formatDate($question->getCreatedAt(), Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, true);
    }

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return string
     */
    public function getProductName(AW_Pquestion2_Model_Question $question)
    {
        $product = $question->getProduct();
        return $product ? $product->getName() : '';
    }

    /**
     * @param AW_Pquestion2_Model_Question $question
     *
     * @return string
     */
    public function getProductUrl(AW_Pquestion2_Model_Question $question)
    {
        $product = $question->getProduct();
        return $product ? $product->getProductUrl() : '#';
    }

    protected function _beforeToHtml()
    {
        $this->_pageSize = $this->getData('num_questions');
        $this->setTemplate('aw_pq2/widget/block.phtml');
    }
}