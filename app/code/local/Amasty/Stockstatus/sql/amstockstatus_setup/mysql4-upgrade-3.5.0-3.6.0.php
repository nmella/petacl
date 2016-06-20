<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
$installer = $this;
$installer->startSetup();
$tableName = $this->getTable('amasty_stockstatus_quantityranges');
$fieldsSql = 'SHOW COLUMNS FROM ' . $tableName;
$cols = $this->getConnection()->fetchCol($fieldsSql);

if (!in_array('rule', $cols))
{
    $this->run("
        ALTER TABLE `{$this->getTable('amasty_stockstatus_quantityranges')}`  ADD `rule` TEXT NULL
    ");
}  


$installer->addAttribute('catalog_product', 'custom_stock_status_qty_rule', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Custom Stock Status Qty Rule',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_table',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => '',
    'is_configurable'   => false,
    'used_in_product_listing'   => true
));
$attributeId = $installer->getAttributeId('catalog_product', 'custom_stock_status_qty_rule');

foreach ($installer->getAllAttributeSetIds('catalog_product') as $attributeSetId) 
{
    try {
        $attributeGroupId = $installer->getAttributeGroupId('catalog_product', $attributeSetId, 'General');
    } catch (Exception $e) {
        $attributeGroupId = $installer->getDefaultAttributeGroupId('catalog_product', $attributeSetId);
    }
    $installer->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
}

$installer->endSetup();