<?php

class FME_Feed_Model_Mysql4_Feed_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('feed/feed');
    }
}