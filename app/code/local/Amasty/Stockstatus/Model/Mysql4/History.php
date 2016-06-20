<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('amstockstatus/history', 'entity_id');
    }
    
    public function deleteAll()
    {
        $this->_getWriteAdapter()->delete($this->getMainTable());
    }
    
    public function loadByOrderAndProduct(Mage_Core_Model_Abstract $object, $orderId, $productId)
    {
        $read = $this->_getReadAdapter();
        
        if ($read && !is_null($orderId)) {
            $select = $this->_getReadAdapter()->select()
                           ->from($this->getMainTable())
                           ->where($this->getMainTable().'.'.'order_id'.'= ?', $orderId)
                           ->where($this->getMainTable().'.'.'product_id'.'= ?', $productId);
            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }
    }
}