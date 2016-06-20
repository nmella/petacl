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

class Mgt_ReviewReminder_Model_Resource_Reminder_Item_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    const CATALOG_PRODUCT_IMAGE_ATTRIBUTE_CODE = 'thumbnail';
    
    protected function _construct()
    {
        parent::_construct();
        $this->_init('mgt_review_reminder/reminder_item');
    }
    
    protected function _initSelect()
    {
        parent::_initSelect();

        $select = $this->getSelect();
        
        $orderItemAlias = 'order_item_o_i';
        $joinTable = $this->getTable('sales/order_item');

        $catalogProductEntityVarcharAlias = 'cpev';
        $catalogProductEntityVarcharTable = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_varchar');
        
        $select->joinLeft(
            array($orderItemAlias => $joinTable),
                "(main_table.order_item_id = {$orderItemAlias}.item_id)",
                array('name')
        );

        $entityType = Mage::getModel('eav/entity_type')->load('catalog_product', 'entity_type_code');
        
        $attribute = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setEntityTypeFilter($entityType->getId())
                ->setCodeFilter(self::CATALOG_PRODUCT_IMAGE_ATTRIBUTE_CODE)
                ->getFirstItem();
        
        $select->joinLeft(
            array($catalogProductEntityVarcharAlias => $catalogProductEntityVarcharTable),
            sprintf("(main_table.product_id = %s.entity_id) and (%s.attribute_id) = %d", $catalogProductEntityVarcharAlias, $catalogProductEntityVarcharAlias, 
            $attribute->getAttributeId()),
            array('thumbnail' => 'value')
        );
        
        $select->group('main_table.reminder_item_id');
    }
    
    public function addReminderFilter(array $reminderIds)
    {
        $select = $this->getSelect();
        $select->where('main_table.reminder_id in (?)', $reminderIds);
    }
}