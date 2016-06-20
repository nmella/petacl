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


class AW_Pquestion2_Model_Resource_Answer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('aw_pq2/answer');
    }

    /**
     * @param int $questionId
     *
     * @return AW_Pquestion2_Model_Resource_Answer_Collection
     */
    public function addFilterByQuestionId($questionId)
    {
        return $this->addFieldToFilter('question_id', $questionId);
    }

    /**
     * @param int|string|Mage_Customer_Model_Customer
     *
     * @return $this
     */
    public function addFilterByCustomer($customer)
    {
        $customerValue = $this->_getCustomerFilteredValue($customer);
        if (is_string($customerValue)) {
            return $this->addFieldToFilter('author_email', $customerValue);
        }
        return $this->addFieldToFilter('customer_id', $customerValue);
    }

    /**
     * int customerId | string customerEmail
     * @param int |string|Mage_Customer_Model_Customer $customer
     *
     * @return int|string
     */
    protected function _getCustomerFilteredValue($customer)
    {
        if (is_string($customer)) {
            return $customer;
        }

        $customerId = $customer;
        $customerEmail = '';
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customerEmail = $customer->getEmail();
            $customerId    = (int)$customer->getId();
            if (!$customerId && empty($customerEmail)) {
                $customerId = -1;//empty collection should be returned
            }
        }

        if ($customerId) {
            return $customerId;
        }
        return $customerEmail;
    }

    /**
     * @param int $status
     *
     * @return AW_Pquestion2_Model_Resource_Answer_Collection
     */
    public function addFilterByStatus($status)
    {
        return $this->addFieldToFilter('status', $status);
    }

    /**
     * @return $this
     */
    public function addCreatedAtLessThanNowFilter()
    {
        $now = new Zend_Date();
        return $this->addFieldToFilter(
            'created_at', array('lteq' => $now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
        );
    }

    /**
     * @param string $sort
     *
     * @return AW_Pquestion2_Model_Resource_Answer_Collection
     */
    public function sortByHelpfull($sort = Zend_Db_Select::SQL_DESC)
    {
        return $this->setOrder('helpfulness', $sort);
    }
}