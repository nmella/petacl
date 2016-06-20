<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('amasty_stockstatus_history')}`  (
    `entity_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `order_id` TINYTEXT NOT NULL ,
    `product_id` INT NOT NULL ,
    `status` TEXT NOT NULL
) ENGINE = InnoDB ;
");
$installer->endSetup();