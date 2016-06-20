<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Helper_Order extends Mage_Core_Helper_Abstract
{
    protected $order = null;
    protected $address = null;
    protected $countryModels = array();

    /**
     * @param $row
     *
     * @return bool
     */
    public function canShip($row)
    {
        return $this->getOrder($row)->canShip();
    }

    /**
     * @param $row
     *
     * @return Mage_Sales_Model_Order|null
     */
    public function getOrder($row)
    {
        if (null === $this->order) {
            $this->order = Mage::getModel('sales/order');
        }
        $this->order->unsetData();
        $this->order->setData($row->getData());
        return $this->order;
    }

    /**
     * @param $data
     *
     * @return Mage_Sales_Model_Order_Address|null
     */
    public function getOrderAddress($data)
    {
        if (null === $this->address) {
            $this->address = Mage::getModel('sales/order_address');
        }
        $this->address->unsetData();
        $this->address->setData($data);
        return $this->address;
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public function getCountryName($code)
    {
        if (!isset($this->countryModels[$code])) {
            $country = Mage::getModel('directory/country')->loadByCode($code);
            if ($country) {
                $this->countryModels[$code] = $country->getName();
            }
        }
        return $this->countryModels[$code];
    }
}
