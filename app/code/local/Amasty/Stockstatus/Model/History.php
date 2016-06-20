<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Model_History extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amstockstatus/history');
    }

    public function loadByOrderAndProduct($orderId, $productId)
    {
        $this->_getResource()->loadByOrderAndProduct($this, $orderId, $productId);

        return $this;
    }
}