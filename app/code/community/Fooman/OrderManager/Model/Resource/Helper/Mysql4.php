<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Model_Resource_Helper_Mysql4 extends Mage_Core_Model_Resource_Helper_Mysql4
{

    protected $_fieldsNeedingMapping
        = array('increment_id', 'created_at', 'grand_total', 'base_grand_total', 'store_id', 'status');

    protected $_gridCollection = null;

    protected $_trackingCollection = null;
    protected $_trackingCollectionLoaded = false;


    /**
     * add billing and shipping information to collection
     *
     * @param $collection
     */
    public function addAddressFields($collection)
    {
        $displayBilling = Mage::getStoreConfigFlag('ordermanager/settings/displaybilling');
        $displayShipping = Mage::getStoreConfigFlag('ordermanager/settings/displayshipping');

        if ($displayBilling || $displayShipping) {
            $joinTable = $collection->getTable('sales/order_address');
            $select = $collection->getSelect();

            if ($displayBilling) {
                $billingAliasName = 'billing_o_a';
                $select->joinLeft(
                    array($billingAliasName => $joinTable),
                    "(main_table.entity_id = {$billingAliasName}.parent_id"
                    . " AND {$billingAliasName}.address_type = 'billing')",
                    array(
                        $billingAliasName . '.firstname AS billing_firstname',
                        $billingAliasName . '.lastname AS billing_lastname',
                        $billingAliasName . '.region_id AS billing_region_id',
                        $billingAliasName . '.region AS billing_region',
                        $billingAliasName . '.postcode AS billing_postcode',
                        $billingAliasName . '.street AS billing_street',
                        $billingAliasName . '.city AS billing_city',
                        $billingAliasName . '.telephone AS billing_telephone',
                        $billingAliasName . '.country_id AS billing_country_id',
                    )
                );
            }

            if ($displayShipping) {
                $shippingAliasName = 'shipping_o_a';

                $select->joinLeft(
                    array($shippingAliasName => $joinTable),
                    "(main_table.entity_id = {$shippingAliasName}.parent_id"
                    . " AND {$shippingAliasName}.address_type = 'shipping')",
                    array(
                        $shippingAliasName . '.firstname AS shipping_firstname',
                        $shippingAliasName . '.lastname AS shipping_lastname',
                        $shippingAliasName . '.region_id AS shipping_region_id',
                        $shippingAliasName . '.region AS shipping_region',
                        $shippingAliasName . '.postcode AS shipping_postcode',
                        $shippingAliasName . '.street AS shipping_street',
                        $shippingAliasName . '.city AS shipping_city',
                        $shippingAliasName . '.telephone AS shipping_telephone',
                        $shippingAliasName . '.country_id AS shipping_country_id',
                    )
                );
            }
        }

    }

    /**
     * add tracking numbers and detailed shipping info to collection
     *
     * @param $collection
     */
    public function addShippingInformation($collection)
    {
        $displayShippingInfo = Mage::getStoreConfigFlag('ordermanager/settings/displayshippinginfo');
        if ($displayShippingInfo) {

            $this->_addFilterMaps($collection);
            $orderJoinTable = $collection->getTable('sales/order');

            $orderAliasName = 'order_t';
            $collection->getSelect()->joinLeft(
                array($orderAliasName => $orderJoinTable),
                "(main_table.entity_id = {$orderAliasName}.entity_id)",
                array(
                    'shipping_incl_tax',
                    'base_shipping_incl_tax',
                    'shipping_amount',
                    'base_shipping_amount',
                    'shipping_tax_amount',
                    'base_shipping_tax_amount',
                    'shipping_description',
                    'protect_code'
                )
            );
        }
    }


    /**
     * when joining the order table we need to differentiate
     * to avoid ambiguity in the where clause
     *
     * @param $collection
     */
    public function _addFilterMaps($collection)
    {
        foreach ($this->_fieldsNeedingMapping as $field) {
            $collection->addFilterToMap($field, 'main_table.' . $field);
            $this->_fixLatenessOfAddFilterMap($collection->getSelect(), $field);
        }
    }

    /**
     * existing where clauses do not reflect the filter map
     * as there is no suitable event adjust for it here
     *
     * @param $select
     * @param $field
     */
    public function _fixLatenessOfAddFilterMap($select, $field)
    {
        $where = $select->getPart(Zend_Db_Select::WHERE);
        if ($where) {
            foreach ($where as $key => $condition) {
                if (preg_filter('/[^a-z_]/', '', $this->_stringBeforeOperator($condition)) == $field) {
                    if (false !== strpos($condition, '`')) {
                        $new_condition = str_replace($field, 'main_table`.`' . $field, $condition);
                    } else {
                        $new_condition = str_replace($field, 'main_table.' . $field, $condition);
                    }
                    $where[$key] = $new_condition;
                }
            }
            $select->setPart(Zend_Db_Select::WHERE, $where);
        }
    }

    /**
     * @param $condition
     *
     * @return string
     */
    protected function _stringBeforeOperator($condition)
    {
        $operators = array('LIKE', '>=', '<=', '=');
        foreach ($operators as $operator) {
            if (false !== strpos($condition, $operator)) {
                $parts = explode($operator, $condition);
                return trim($parts[0]);
            }
        }
        return $condition;
    }

    /**
     * retrieve tracking numbers for given order
     *
     * @param $row
     * @param $order
     *
     * @return bool
     */
    public function getFoomanTrackingNumbers($row, $order)
    {
        $this->_prepareTrackingCollection($row);
        if (isset($this->_trackingCollection[$order->getEntityId()]['tracking_numbers'])) {
            return $this->_trackingCollection[$order->getEntityId()]['tracking_numbers'];
        }
        return false;

    }

    /**
     * retrieve carrier titles for given order
     *
     * @param $row
     * @param $order
     *
     * @return bool
     */
    public function getFoomanCarrierTitles($row, $order)
    {
        $this->_prepareTrackingCollection($row);
        if (isset($this->_trackingCollection[$order->getEntityId()]['titles'])) {
            return $this->_trackingCollection[$order->getEntityId()]['titles'];
        }
        return false;

    }

    /**
     * get grid collection from given row
     *
     * @param $row
     *
     * @return null
     */
    protected function _getGridCollection($row)
    {
        if (is_null($this->_gridCollection)) {
            $this->_gridCollection = $row->getColumn()->getGrid()->getCollection();
        }
        return $this->_gridCollection;
    }

    /**
     * use the grid's collection to prepare tracking data
     *
     * @param $row
     */
    protected function _prepareTrackingCollection($row)
    {
        if (!$this->_trackingCollectionLoaded) {
            $orderIds = array();
            foreach ($this->_getGridCollection($row) as $order) {
                $orderIds[] = $order->getId();
            }

            $trackingCollection = Mage::getResourceModel('sales/order_shipment_track_collection');
            $trackingCollection->addFieldToFilter('order_id', array('IN' => $orderIds))
                ->addFieldToSelect('title')
                ->addFieldToSelect('track_number')
                ->addFieldToSelect('order_id');
            if ($trackingCollection) {
                foreach ($trackingCollection as $track) {
                    $this->_trackingCollection[$track->getOrderId()]['tracking_numbers'][] = $track->getTrackNumber();
                    $this->_trackingCollection[$track->getOrderId()]['titles'][] = $track->getTitle();
                }
            }
            $this->_trackingCollectionLoaded = true;
        }
    }
}
