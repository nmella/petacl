<?php

class Moogento_Pickpack_Helper_Print extends Mage_Core_Helper_Abstract
{
    public function processPrint($orderIds, $types)
    {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }
        if (!is_array($types)) {
            $types = array($types);
        }

        foreach ($orderIds as $orderId) {
            $flag = Mage::getModel('pickpack/flagautoaction')->load($orderId, 'orderid');
            if (!$flag->getId()) {
                $flag->setData('orderid', $orderId);
            }
            foreach ($types as $type) {
                $fieldName = $type . '_printed';
                $flag->setData($fieldName, 1);
            }
            $flag->save();
        }
    }

    public function filterPrinted($orderIds, $types) {
        $collection = Mage::getResourceModel('pickpack/flagautoaction_collection');
        $fields = array();
        if (!is_array($types)) {
            $types = array($types);
        }
        foreach ($types as $type) {
            $fields[] = 'ifnull(' . $type . '_printed, 0)';
        }
        $collection->getSelect()->where('(' . implode(' + ' , $fields) . ') > 0');
        $collection->addFieldToFilter('orderid', array('in' => $orderIds));
        $printed = array();
        foreach ($collection as $one) {
            $printed[] = $one->getData('orderid');
        }

        return array_diff($orderIds, $printed);
    }

    public function isPrinted($orderId, $types)
    {
        $flag = Mage::getModel('pickpack/flagautoaction')->load($orderId, 'orderid');
        if (!$flag->getId()) {
            return false;
        }
        $result = 0;
        foreach ($types as $type) {
            $result += $flag->getData($type . '_printed');
        }

        return $result > 0;
    }
} 