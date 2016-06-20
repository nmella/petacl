<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Order.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Resource_Sales_Order extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct() {
        $this->_init('sales/order', 'entity_id');
    }

    public function getOrderStoreId($orderId) {
        if ($orderId instanceof Mage_Sales_Model_Order) {
            $orderId = $orderId->getId();
        }

        $select = $this->_getReadAdapter()->select();
        $select->from(
            $this->getMainTable(),
            array('store_id')
        )->where('entity_id = ' . $orderId);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    public function updateOrderField($orderIds, $fieldName, $value) {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }

        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                $fieldName => $value
            ),
            $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $orderIds)
        );

        return $this;
    }

    public function getOrderFields() {
        $this->_getReadAdapter()->resetDdlCache($this->getMainTable());
        return $this->_getReadAdapter()->describeTable($this->getMainTable());
    }

    public function getOrderColumnValue($field, $orderId) {
        $select = $this->_getReadAdapter()->select();
        $select->from(
            $this->getMainTable(),
            array($field)
        )->where('entity_id = ' . $orderId);

        return $this->_getReadAdapter()->fetchOne($select);
    }

    public function getOrderGridColumnValue($field, $orderId) {
        $select = $this->_getReadAdapter()->select();
        $select->from(
            $this->getTable('sales/order_grid'),
            array($field)
        )->where('entity_id = ' . $orderId);

        return $this->_getReadAdapter()->fetchOne($select);
    }
}
