<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
$installer = $this;
$installer->startSetup();

try
{
    $tableName = Mage::getSingleton('core/resource')->getTableName('core/config_data');
    $fieldsSql = 'SELECT * FROM ' . $tableName . " WHERE `path` like 'cataloginventory/options/display_product_stock_status'";
    $cols = $this->getConnection()->fetchCol($fieldsSql);
    if ($cols)
    {
        $this->run("
            UPDATE `{$tableName}` SET `value` = '1' WHERE `path` = 'cataloginventory/options/display_product_stock_status';
        ");
    }
    else{
        $this->run("
            INSERT into `{$tableName}`(`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, 'cataloginventory/options/display_product_stock_status', '1');
        ");
    }
}
catch(Exception $exc){} 
$installer->endSetup();