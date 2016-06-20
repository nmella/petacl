<?php

class FME_Percentagepricing_Model_Mysql4_Percentagepricing_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('percentagepricing/percentagepricing');
    }
}