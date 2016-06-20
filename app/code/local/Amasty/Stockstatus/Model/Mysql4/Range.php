<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Model_Mysql4_Range extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('amstockstatus/range', 'entity_id');
    }
    
    public function deleteAll()
    {
        $this->_getWriteAdapter()->delete($this->getMainTable());
    }
    
    public function loadByQty(Mage_Core_Model_Abstract $object, $qty)
    {
        $read = $this->_getReadAdapter();
        
        if ($read && !is_null($qty)) {
            $select = $this->_getReadAdapter()->select()
                           ->from($this->getMainTable())
                           ->where($this->getMainTable().'.'.'qty_from'.'<= ?', $qty)
                           ->where($this->getMainTable().'.'.'qty_to'.'>= ?', $qty);
            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }
    }
    
    public function loadByQtyAndRule(Mage_Core_Model_Abstract $object, $qty, $rule)
    {
        $read = $this->_getReadAdapter();
        
        if ($read && !is_null($qty)) {
            $select = $this->_getReadAdapter()->select()
                           ->from($this->getMainTable())
                           ->where($this->getMainTable().'.'.'qty_from'.'<= ?', $qty)
                           ->where($this->getMainTable().'.'.'qty_to'.'>= ?', $qty)
                           ->where($this->getMainTable().'.'.'rule'.'= ?', $rule);
                           
            $data = $read->fetchRow($select);
            if ($data) {
                $object->setData($data);
            }
        }
    }

}