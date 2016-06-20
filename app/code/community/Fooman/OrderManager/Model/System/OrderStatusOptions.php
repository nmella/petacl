<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Model_System_OrderStatusOptions
{

    /**
     * Get order status options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $returnArray = array();
        $returnArray[] = array('value' => '', 'label' => Mage::helper('fooman_ordermanager')->__('Default'));
        foreach (Mage::getModel('sales/order_config')->getStatuses() as $status => $statusLabel) {
            $returnArray[] = array('value' => $status, 'label' => Mage::helper('sales')->__($statusLabel));
        }
        return $returnArray;
    }

}
