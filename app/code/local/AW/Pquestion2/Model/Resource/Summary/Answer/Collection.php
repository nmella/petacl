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


class AW_Pquestion2_Model_Resource_Summary_Answer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('aw_pq2/summary_answer');
    }

    /**
     * @param int $answerId
     *
     * @return AW_Pquestion2_Model_Resource_Summary_Answer_Collection
     */
    public function addFilterByAnswerId($answerId)
    {
        return $this->addFieldToFilter('answer_id', $answerId);
    }

    /**
     * @param int $customerId
     *
     * @return AW_Pquestion2_Model_Resource_Summary_Answer_Collection
     */
    public function addFilterByCustomerId($customerId)
    {
        return $this->addFieldToFilter('customer_id', $customerId);
    }

    /**
     * @param int $visitorId
     *
     * @return AW_Pquestion2_Model_Resource_Summary_Answer_Collection
     */
    public function addFilterByVisitorId($visitorId)
    {
        return $this->addFieldToFilter('visitor_id', $visitorId);
    }

    /**
     * @param array $answerIds
     *
     * @return AW_Pquestion2_Model_Resource_Summary_Answer_Collection
     */
    public function addFilterByAnswerIds($answerIds)
    {
        return $this->addFieldToFilter('answer_id', array('in' => $answerIds));
    }

    /**
     * Convert items array to hash for select options
     *
     * return items hash
     * array($value => $label)
     *
     * @param   string $valueField
     * @param   string $labelField
     * @return  array
     */
    protected function _toOptionHash($valueField = 'answer_id', $labelField = 'helpful')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }
}