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


class AW_Pquestion2_Block_Answer_Popup extends Mage_Core_Block_Template
{
    public function canShow()
    {
        if (!Mage::helper('aw_pq2/config')->getIsEnabled()) {
            return false;
        }
        $popupData = Mage::helper('aw_pq2/request')->getPopupData();
        if (!is_array($popupData)) {
            return false;
        }
        if (count($popupData) == 0) {
            return false;
        }
        $this->setData($popupData);

        if ((int)$this->getCustomerId() !== (int)Mage::getSingleton('customer/session')->getCustomerId()) {
            return false;
        }

        if ($this->getCustomerEmail() === null) {
            return false;
        }
        if ($this->getQuestion()->getId() === null) {
            return false;
        }
        return true;
    }

    public function getAnswerFormHtml()
    {
        $block = $this->getChild('aw_pq2_add_answer_form')
            ->setQuestionId($this->getQuestionId())
            ->setCustomerName($this->getCustomerName())
            ->setCustomerEmail($this->getCustomerEmail())
        ;
        return $block->toHtml();
    }

    public function getSalutation()
    {
        $salutation = '';
        if ($this->getCustomerName()) {
            $salutation .= $this->__('Dear %s!', $this->getCustomerName()) . ' ';
        }
        $salutation .= $this->__('Could you please answer the question?');
        return $salutation;
    }

    public function getQuestion()
    {
        return Mage::getModel('aw_pq2/question')->load($this->getQuestionId());
    }

    public function getQuestionContent()
    {
        return $this->getQuestion()->getContent();
    }
}