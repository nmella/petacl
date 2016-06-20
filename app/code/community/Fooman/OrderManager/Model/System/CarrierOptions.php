<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Model_System_CarrierOptions
{

    /**
     * Get Carrier options
     *
     * @return array
     */
    public function toOptionArray()
    {

        $returnArray = array();
        $shipConfig = Mage::getSingleton('shipping/config');
        $returnArray[] = array('value' => 'custom',
                               'label' => Mage::helper('fooman_ordermanager')->__('Custom Carrier'));
        foreach ($shipConfig->getAllCarriers() as $code => $carrier) {
            $returnArray[] = array('value' => $code, 'label' => $this->_getCarrierTitle($code));
        }
        return $returnArray;
    }

    /**
     * Get Title for carrier, falls back to carrier code
     *
     * @param $code
     *
     * @return mixed
     */
    protected  function _getCarrierTitle($code)
    {
        $title = Mage::helper('fooman_ordermanager')->getCarrierTitle($code);
        return (empty($title)) ? $code : $title;
    }

}
