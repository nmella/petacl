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


class AW_Pquestion2_Model_Question extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'aw_pq2_question';
    protected $_eventObject = 'question';
    protected $_product     = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_pq2/question');
    }

    /**
     * @return AW_Pquestion2_Model_Question
     */
    protected function _beforeSave()
    {
        if (is_array($this->getShowInStoreIds())) {
            $this->setShowInStoreIds(implode(',', $this->getShowInStoreIds()));
        }
        if (is_array($this->getSharingValue())) {
            $this->setSharingValue(implode(',', $this->getSharingValue()));
        }
        return parent::_beforeSave();
    }

    /**
     * @return AW_Pquestion2_Model_Question
     */
    protected function _afterSave()
    {
        if (strlen($this->getShowInStoreIds()) > 0) {
            $this->setShowInStoreIds(array_map('intval', explode(',', $this->getShowInStoreIds())));
        } else {
            $this->setShowInStoreIds(array());
        }
        if (strlen($this->getSharingValue()) > 0) {
            $this->setSharingValue(array_map('intval', explode(',', $this->getSharingValue())));
        } else {
            $this->setSharingValue(array());
        }
        return parent::_afterSave();
    }

    /**
     * @return AW_Pquestion2_Model_Question
     */
    protected function _afterLoad()
    {
        if (strlen($this->getShowInStoreIds()) > 0) {
            $this->setShowInStoreIds(array_map('intval', explode(',', $this->getShowInStoreIds())));
        } else {
            $this->setShowInStoreIds(array());
        }
        if (strlen($this->getSharingValue()) > 0) {
            $this->setSharingValue(array_map('intval', explode(',', $this->getSharingValue())));
        } else {
            $this->setSharingValue(array());
        }
        return parent::_afterLoad();
    }

    /**
     * @return AW_Pquestion2_Model_Resource_Answer_Collection
     */
    public function getAnswerCollection()
    {
        return Mage::getModel('aw_pq2/answer')->getCollection()->addFilterByQuestionId($this->getId());
    }

    /**
     * @return AW_Pquestion2_Model_Resource_Summary_Question_Collection
     */
    public function getHelpfulCollection()
    {
        return Mage::getModel('aw_pq2/summary_question')->getCollection()->addFilterByQuestionId($this->getId());
    }

    /**
     * @param AW_Pquestion2_Model_Answer $answer
     *
     * @return $this
     * @throws Exception
     */
    public function addAnswer(AW_Pquestion2_Model_Answer $answer)
    {
        if (null === $this->getId()) {
            throw new Exception('Question ID can not be NULL');
        }
        $answer
            ->setQuestionId($this->getId())
            ->save();
        return $this;
    }

    /**
     * @param Mage_Customer_Model_Customer | Mage_Log_Model_Visitor $customer
     * @param int $value
     *
     * @return $this
     * @throws Exception
     */
    public function addHelpful($customer, $value)
    {
        if (!$customer instanceof Mage_Customer_Model_Customer && !$customer instanceof Mage_Log_Model_Visitor) {
            throw new Exception('Not supported customer object instance.');
        }

        $helpfulCollection = Mage::getModel('aw_pq2/summary_question')->getCollection();
        $helpfulCollection->addFilterByQuestionId($this->getId());
        $helpfulModel = Mage::getModel('aw_pq2/summary_question');
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $helpfulCollection->addFilterByCustomerId($customer->getId());
            $helpfulModel = $helpfulCollection->getFirstItem();
            $helpfulModel->setCustomerId($customer->getId());
        }
        if ($customer instanceof Mage_Log_Model_Visitor) {
            if ($customer->getCustomerId()) {
                $helpfulCollection->addFilterByCustomerId($customer->getCustomerId());
            } else {
                $helpfulCollection->addFilterByVisitorId($customer->getId());
            }
            $helpfulModel = $helpfulCollection->getFirstItem();
            $helpfulModel
                ->setCustomerId($customer->getCustomerId())
                ->setVisitorId($customer->getId())
            ;
        }

        if (null === $helpfulModel->getId()) {
            $helpfulModel->setHelpful(0);
        }

        $helpfulModel
            ->setQuestionId($this->getId())
            ->setHelpful($helpfulModel->getHelpful() + $value)
        ;
        if (null !== $helpfulModel->getHelpful()
            && !in_array($helpfulModel->getHelpful(), array(-1, 0, 1))
        ) {
            throw new Exception('Not allowed value for this customer.');
        }
        $helpfulModel->save();
        $this
            ->setHelpfulness($this->getHelpfulness() + $value)
            ->save()
        ;
        return $this;
    }

    /**
     * @return AW_Pquestion2_Model_Resource_Answer_Collection
     */
    public function getApprovedAnswerCollection()
    {
        return $this->getAnswerCollection()
            ->addFilterByStatus(AW_Pquestion2_Model_Source_Question_Status::APPROVED_VALUE)
            ->sortByHelpfull()
        ;
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (null === $this->_product) {
            $this->_product = Mage::getModel('catalog/product')
                ->setStoreId($this->getStoreId())
                ->load($this->getProductId())
            ;
        }
        return $this->_product;
    }
}