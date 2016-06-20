<?php
/**
 * MGT-Commerce GmbH
 * http://www.mgt-commerce.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@mgt-commerce.com so we can send you a copy immediately.
 *
 * @category    Mgt
 * @package     Mgt_ReviewReminder
 * @author      Stefan Wieczorek <stefan.wieczorek@mgt-commerce.com>
 * @copyright   Copyright (c) 2012 (http://www.mgt-commerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mgt_ReviewReminder_Model_Resource_Reminder_Item extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct() 
    {
        $this->_init('mgt_review_reminder/mgt_review_reminder_item', 'reminder_item_id');
    }
    
    public function doesItemExist(Mage_Sales_Model_Order_Item $orderItem)
    {
        $orderItemId = $orderItem->getId();
        $productId = $orderItem->getProductId();

        $field = $this->getIdFieldName();
        $mainTable = $this->getMainTable();
        $readAdapter = $this->_getReadAdapter();
        $field  = $readAdapter->quoteIdentifier(sprintf('%s.%s', $mainTable, $field));
        $select = $readAdapter->select();
        $select->from($this->getMainTable());
        $select->where(sprintf('%s.%s =?', $mainTable, 'order_item_id'), $orderItemId);
        $select->where(sprintf('%s.%s =?', $mainTable, 'product_id'), $productId);
        
        $data = $readAdapter->fetchRow($select);
        
        return (false === $data ? false : true);
    }
}