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


class AW_Pquestion2_Model_Resource_Question_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('aw_pq2/question');
    }

    /**
     * @return $this
     */
    public function joinPendingAnswerCount()
    {
        if (!$this->getFlag('answer_count_joined')) {
            $pendingStatus = AW_Pquestion2_Model_Source_Question_Status::PENDING_VALUE;
            $this->getSelect()->joinLeft(
                new Zend_Db_Expr(
                    "(SELECT COUNT(entity_id) as pending_answers, question_id"
                    . " FROM {$this->getTable('aw_pq2/answer')}"
                    . " WHERE  status={$pendingStatus}"
                    . " GROUP BY question_id)"
                ),
                "main_table.entity_id = t.question_id",
                array('pending_answers' => "IFNULL(t.pending_answers, 0)")
            );
            $this->setFlag('answer_count_joined', true);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function joinTotalAnswerCount()
    {
        if (!$this->getFlag('answer_total_count_joined')) {
            $this->getSelect()->joinLeft(
                new Zend_Db_Expr(
                    "(SELECT COUNT(entity_id) as total_answers, question_id"
                    . " FROM {$this->getTable('aw_pq2/answer')}"
                    . " GROUP BY question_id)"
                ),
                "main_table.entity_id = t_2.question_id",
                array('total_answers' => "IFNULL(t_2.total_answers, 0)")
            );
            $this->setFlag('answer_total_count_joined', true);
        }
        return $this;
    }

    /**
     * @param int|Mage_Catalog_Model_Product $product
     *
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function addFilterByProduct($product)
    {
        if (!$product instanceof Mage_Catalog_Model_Product) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        $_productId = $product->getId();
        if (null === $_productId) {
            $_productId = 0;
        }
        $_productAttributeSetId = $product->getAttributeSetId();
        if (null === $_productAttributeSetId) {
            $_productAttributeSetId = 0;
        }
        $_productWebsiteIds = $product->getWebsiteIds();
        if (empty($_productWebsiteIds)) {
            $_productWebsiteIds = array(0);
        }
        $this
            ->getSelect()
            ->where(
                'sharing_type = ' . AW_Pquestion2_Model_Source_Question_Sharing_Type::PRODUCTS_VALUE
                . ' AND find_in_set(' . $_productId . ', sharing_value)'
                . ' OR (sharing_type = ' . AW_Pquestion2_Model_Source_Question_Sharing_Type::ATTRIBUTE_SET_VALUE
                . ' AND sharing_value = ' . $_productAttributeSetId . ')'
                . ' OR (sharing_type = ' . AW_Pquestion2_Model_Source_Question_Sharing_Type::WEBSITE_VALUE
                . ' AND sharing_value IN(' . implode(',', $_productWebsiteIds) . '))'
                . ' OR (sharing_type = ' . AW_Pquestion2_Model_Source_Question_Sharing_Type::GLOBAL_VALUE . ')'
            )
        ;
        return $this;
    }

    /**
     * @param int|string|Mage_Customer_Model_Customer
     *
     * @return AW_Pquestion2_Model_Resource_Question_Collection
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
                $customerId = -1; //empty collection should be returned
            }
        }

        if ($customerId) {
            return $customerId;
        }
        return $customerEmail;
    }

    /**
     * @param int $storeId
     *
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function addShowInStoresFilter($storeId)
    {
        $this
            ->getSelect()
            ->where("FIND_IN_SET(0, show_in_store_ids) OR FIND_IN_SET({$storeId}, show_in_store_ids)")
        ;
        return $this;
    }

    /**
     * @param int $visibility
     *
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function addVisibilityFilter($visibility)
    {
        $this->addFieldToFilter('visibility', $visibility);
        return $this;
    }

    /**
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function addPublicFilter()
    {
        return $this->addVisibilityFilter(AW_Pquestion2_Model_Source_Question_Visibility::PUBLIC_VALUE);
    }

    /**
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function addPrivateFilter()
    {
        return $this->addVisibilityFilter(AW_Pquestion2_Model_Source_Question_Visibility::PRIVATE_VALUE);
    }

    /**
     * @param $status
     *
     * @return $this
     */
    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }

    /**
     * @return $this
     */
    public function addApprovedStatusFilter()
    {
        return $this->addStatusFilter(AW_Pquestion2_Model_Source_Question_Status::APPROVED_VALUE);
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
     * @param number|null $from
     * @param number|null $to
     *
     * @return $this
     */
    public function addPendingAnswerFilter($from, $to)
    {
        $this->joinPendingAnswerCount();
        if (null !== $from) {
            $this->getSelect()->where("IFNULL(t.pending_answers, 0) >= ?", $from);
        }
        if (null !== $to) {
            $this->getSelect()->where("IFNULL(t.pending_answers, 0) <= ?", $to);
        }
        return $this;
    }

    /**
     * @param number|null $from
     * @param number|null $to
     *
     * @return $this
     */
    public function addTotalAnswerFilter($from, $to)
    {
        $this->joinTotalAnswerCount();
        if (null !== $from) {
            $this->getSelect()->where("IFNULL(t_2.total_answers, 0) >= ?", $from);
        }
        if (null !== $to) {
            $this->getSelect()->where("IFNULL(t_2.total_answers, 0) <= ?", $to);
        }
        return $this;
    }


    /**
     * @param string $sort
     *
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function sortByHelpfull($sort = Zend_Db_Select::SQL_DESC)
    {
        return $this->setOrder('helpfulness', $sort);
    }

    /**
     * @param string $sort
     *
     * @return AW_Pquestion2_Model_Resource_Question_Collection
     */
    public function sortByDate($sort = Zend_Db_Select::SQL_DESC)
    {
        return $this->setOrder('created_at', $sort);
    }

    /**
     * @return $this
     */
    public function joinProductTitle()
    {
        if (!$this->getFlag('product_title_joined')) {
            $titleAttribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'name');
            $titleAttributeId = $titleAttribute->getId();
            $this->getSelect()
                ->joinLeft(
                    array(
                        'cpev' => Mage::getResourceModel('aw_pq2/question')->getValueTable('catalog/product', 'varchar')
                    ),
                    'main_table.product_id = cpev.entity_id'
                    . ' AND cpev.store_id = 0 AND cpev.attribute_id = ' . $titleAttributeId,
                    array('question_product_name' => 'cpev.value')
                )
            ;
            $this->setFlag('product_title_joined', true);
        }
        return $this;
    }
}