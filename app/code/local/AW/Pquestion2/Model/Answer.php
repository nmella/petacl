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


class AW_Pquestion2_Model_Answer extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'aw_pq2_answer';
    protected $_eventObject = 'answer';

    protected $_question = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_pq2/answer');
    }

    /**
     * @return AW_Pquestion2_Model_Resource_Summary_Question_Collection
     */
    public function getHelpfulCollection()
    {
        return Mage::getModel('aw_pq2/summary_answer')->getCollection()->addFilterByAnswerId($this->getId());
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

        $helpfulCollection = Mage::getModel('aw_pq2/summary_answer')->getCollection();
        $helpfulCollection->addFilterByAnswerId($this->getId());
        $helpfulModel = Mage::getModel('aw_pq2/summary_answer');
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
            ->setAnswerId($this->getId())
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
     * @return AW_Pquestion2_Model_Question
     */
    public function getQuestion()
    {
        if (null === $this->_question) {
            $this->_question = Mage::getModel('aw_pq2/question')->load($this->getQuestionId());
        }
        return $this->_question;
    }
}