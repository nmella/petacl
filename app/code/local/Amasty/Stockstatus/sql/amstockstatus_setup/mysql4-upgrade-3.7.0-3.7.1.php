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
    $this->run("
        UPDATE `{$this->getTable('catalog_eav_attribute')}` set used_in_product_listing = 1 
        WHERE attribute_id IN 
            (SELECT attribute_id FROM `{$this->getTable('eav_attribute')}` where attribute_code 
                IN ('custom_stock_status', 'hide_default_stock_status', 'custom_stock_status_qty_based', 'custom_stock_status_qty_rule'))
    ");
}
catch(Exception $exc){} 
$installer->endSetup();