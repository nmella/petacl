<?php

class FME_Percentagepricing_Model_Mysql4_Percentagepricing extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the percentagepricing_id refers to the key field in your database table.
        $this->_init('percentagepricing/percentagepricing', 'percentagepricing_id');
    }
}