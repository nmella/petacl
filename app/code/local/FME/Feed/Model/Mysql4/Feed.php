<?php

class FME_Feed_Model_Mysql4_Feed extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the feed_id refers to the key field in your database table.
        $this->_init('feed/feed', 'feed_id');
    }
}