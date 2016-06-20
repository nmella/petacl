<?php
/**
 * 
 * Date: 20.11.15
 * Time: 15:22
 */
class Moogento_Pickpack_Helper_Config_Supplier extends Moogento_Pickpack_Helper_Config
{
    public function isSplitSupplier($wonder, $storeId = null) {
        $supplierKey = ($wonder == 'wonder_invoice') ? 'invoice' : 'pack';
        if($this->getConfig('pickpack_split_supplier_yn', 'supplier', false, 'general', $storeId)) {
            $options = $this->getConfig('pickpack_split_supplier_options', 'no', false, 'general', $storeId);
            if(in_array($supplierKey, explode(',',$options))) {
                return true;
            }
        }
        return false;
    }

    public function getSplitSupplierOptions($storeId = null) {
        return $this->getConfig('pickpack_split_supplier_options', 'supplier', false, 'general', $storeId) ? 'pickpack' : 'no';
    }

    public function getSupplierAttribute($storeId = null) {
        return $this->getConfig('pickpack_supplier_attribute', 'supplier', false, 'general', $storeId);
    }

    public function getFilterSupplierOptions($storeId = null) {
        return $this->getConfig('pickpack_supplier_options', 'filter', false, 'general', $storeId);
    }

    public function getSupplierLogin() {
        $userId = Mage::getSingleton('admin/session')->getUser() ? Mage::getSingleton('admin/session')->getUser()->getId() : 0;
        $user = ($userId !== 0) ? Mage::getModel('admin/user')->load($userId) : '';
        $username = (!empty($user['username'])) ? $user['username'] : '';

        if(is_null($username)) {
            $userId = Mage::getSingleton('admin/session')->getUser() ? Mage::getSingleton('admin/session')->getUser()->getId() : 0;
            $user = ($userId !== 0) ? Mage::getModel('admin/user')->load($userId) : '';
            $username = (!empty($user['username'])) ? $user['username'] : '';
        }

        $supplier_login_pre = $this->getConfig('pickpack_supplier_login', '', false, 'general', 0);
        $supplier_login_pre = str_replace(array("\n", ','), ';', $supplier_login_pre);
        $supplier_login_pre = explode(';', $supplier_login_pre);

        foreach ($supplier_login_pre as $key => $value) {
            $supplier_login_single = explode(':', $value);
            $supplier_login = '';
            if (preg_match('~' . $username . '~i', $supplier_login_single[0])) {
                if (isset($supplier_login_single[1]) && $supplier_login_single[1] != 'all') $supplier_login = trim($supplier_login_single[1]);
            }
        }

        return $supplier_login;
    }

    public function getAllSupplier($order, $supplier_attribute) {
        $is_warehouse_supplier = 0;
        if((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
        {
            if($supplier_attribute == 'warehouse')
            {
                $is_warehouse_supplier = 1;
            }
        }
        $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
        $supplier_array = array();
        foreach ($itemsCollection as $item) {
            if($is_warehouse_supplier == 1)
            {
                $warehouse_title = $item->getWarehouseTitle();
                $warehouse = $item->getWarehouse();
                $warehouse_code = $warehouse->getData('code');
                $supplier = $warehouse_code;
                $warehouse_code = trim(strtoupper($supplier));
                $this->warehouse_title[$warehouse_code] = $warehouse_title;
            }
            else
            {
                $product = Mage::helper('pickpack/product')->getProductFromItem($item);
                $supplier = Mage::helper('pickpack')->getProductAttributeValue($product, $supplier_attribute);
            }
            if (is_array($supplier)) $supplier = implode(',', $supplier);
            if (!$supplier) $supplier = '~Not Set~';
            $supplier_array[] = trim(strtoupper($supplier));
        }
        return array_unique($supplier_array);
    }
}