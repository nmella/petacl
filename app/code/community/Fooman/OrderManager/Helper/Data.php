<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ORDERMANAGER_SETTINGS = 'ordermanager/';

    protected $carriers = array();

    protected $carrierTitles = array();

    /**
     * Return order manager store config value for key
     *
     * @param     $key
     * @param int $storeId
     *
     * @return mixed
     */
    public function getStoreConfig($key, $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {
        $path = self::XML_PATH_ORDERMANAGER_SETTINGS . $key;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * retrieve carrier title
     *
     * @param     $carrierCode
     * @param int $storeId
     *
     * @return mixed
     */
    public function getCarrierTitle($carrierCode, $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
    {

        if (!isset($this->carrierTitles[$storeId][$carrierCode])) {
            if ($carrierCode == 'custom') {
                $this->carrierTitles[$storeId][$carrierCode] = $this->escapeHtml(
                    Mage::helper('fooman_ordermanager')->getStoreConfig(
                        'settings/customtitle', $storeId
                    )
                );
            } else {
                $this->carrierTitles[$storeId][$carrierCode] = $this->escapeHtml(
                    Mage::getStoreConfig(
                        'carriers/' . $carrierCode . '/title', $storeId
                    )
                );
            }
            //add workaround for xtento custom trackers
            if (empty($this->carrierTitles[$storeId][$carrierCode])) {
                $this->carrierTitles[$storeId][$carrierCode] = $this->escapeHtml(
                    Mage::getStoreConfig(
                        'customtrackers/' . $carrierCode . '/title', $storeId
                    )
                );
            }
        }
        return $this->carrierTitles[$storeId][$carrierCode];
    }

    /**
     * @param $storeId
     *
     * @return array
     */
    public function getCarriersForStore($storeId)
    {
        if (!isset($this->carriers[$storeId])) {
            $carrierInstances = Mage::getSingleton('shipping/config')->getAllCarriers(
                $storeId
            );
            $this->carriers[$storeId]['custom'] = $this->getCarrierTitle('custom', $storeId);
            foreach ($carrierInstances as $code => $carrier) {
                if ($carrier->isTrackingAvailable()) {
                    $this->carriers[$storeId][$code] = $this->getCarrierTitle($code, $storeId);
                }
            }
        }
        return $this->carriers[$storeId];
    }
}
