<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Model_Range extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amstockstatus/range');
    }
    
    public function clear()
    {
        $this->getResource()->deleteAll();
    }
    
    public function loadByQty($qty)
    {
        $this->_getResource()->loadByQty($this, $qty);
    }
     
    public function loadByQtyAndRule($qty, $rule)
    {
        $this->_getResource()->loadByQtyAndRule($this, $qty, $rule);
    }
}