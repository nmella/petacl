<?php

$installer = $this;

$installer->startSetup();

$tables = array(
    'mgt_review_reminder', 
    'mgt_review_reminder_history',
    'mgt_review_reminder_item'
);

foreach ($tables as $table) {
    $this->run(sprintf('DROP TABLE IF EXISTS %s', $table));
}

/* Add reminder table  -------------------------------------------------------*/

$tableName = $installer->getTable('mgt_review_reminder');

$this->run("CREATE TABLE `{$tableName}` (
  `reminder_id` int UNSIGNED NOT NULL auto_increment COMMENT 'Reminder Id',
  `code` varchar(16) NULL COMMENT 'Code',
  `customer_name` varchar(128) NULL COMMENT 'Customer Name',
  `customer_email` varchar(128) NULL COMMENT 'Customer E-Mail',
  `reminders_sent` smallint UNSIGNED NOT NULL default '0' COMMENT 'Reminders Sent',
  `store_id` smallint UNSIGNED NOT NULL COMMENT 'Store Id',
  `order_id` int UNSIGNED NOT NULL COMMENT 'Order Id',
  `purchased_at` timestamp NULL COMMENT 'Purchased At Time',
  `created_at` timestamp NULL COMMENT 'Created At Time',
  PRIMARY KEY (`reminder_id`)
) COMMENT='mgt_review_reminder' ENGINE=INNODB charset=utf8 COLLATE=utf8_general_ci");

$this->run("ALTER TABLE {$tableName} ADD INDEX (`store_id`)");
$this->run("ALTER TABLE {$tableName} ADD INDEX (`order_id`)");

$this->run("ALTER TABLE {$tableName} ADD FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
$this->run("ALTER TABLE {$tableName} ADD FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_flat_order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
$this->run("ALTER TABLE {$tableName} CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'Creation Time'");


/* Add reminder item table  --------------------------------------------------*/

$tableName = $installer->getTable('mgt_review_reminder_item');

$this->run("CREATE TABLE `{$tableName}` (
  `reminder_item_id` int UNSIGNED NOT NULL auto_increment COMMENT 'Reminder Item Id',
  `reminder_id` int UNSIGNED NOT NULL default '0' COMMENT 'Reminder Id',
  `order_item_id` int UNSIGNED NOT NULL default '0' COMMENT 'Order Item Id',
  `product_id` int UNSIGNED NOT NULL default '0' COMMENT 'Product Id',
  `is_reviewed` smallint UNSIGNED NOT NULL default '0' COMMENT 'Is Reviewed',
  PRIMARY KEY (`reminder_item_id`)
) COMMENT='mgt_review_reminder_item' ENGINE=INNODB charset=utf8 COLLATE=utf8_general_ci");

$this->run("ALTER TABLE {$tableName} ADD INDEX (`reminder_id`)");
$this->run("ALTER TABLE {$tableName} ADD INDEX (`product_id`)");
$this->run("ALTER TABLE {$tableName} ADD INDEX (`order_item_id`)");

$this->run("ALTER TABLE {$tableName} ADD FOREIGN KEY (`reminder_id`) REFERENCES `{$installer->getTable('mgt_review_reminder')}` (`reminder_id`) ON DELETE CASCADE ON UPDATE CASCADE ;");
$this->run("ALTER TABLE {$tableName} ADD FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE ;");
$this->run("ALTER TABLE {$tableName} ADD FOREIGN KEY (`order_item_id`) REFERENCES `{$installer->getTable('sales_flat_order_item')}` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE ;");

/* Add reminder history table  -----------------------------------------------*/

$tableName = $installer->getTable('mgt_review_reminder_history');

$this->run("CREATE TABLE `{$tableName}` (
  `reminder_history_id` int UNSIGNED NOT NULL auto_increment COMMENT 'Reminder History Id',
  `reminder_id` int UNSIGNED NOT NULL COMMENT 'Reminder Id',
  `store_id` smallint UNSIGNED NOT NULL COMMENT 'Store Id',
  `customer_name` varchar(128) NULL COMMENT 'Customer Name',
  `customer_email` varchar(128) NULL COMMENT 'Customer E-Mail',
  `reminder_sent` smallint UNSIGNED NOT NULL default '0' COMMENT 'Reminder Sent',
  `order_id` int UNSIGNED NOT NULL COMMENT 'Order Id',
  `created_at` timestamp NULL COMMENT 'Created At Time',
  PRIMARY KEY (`reminder_history_id`)
) COMMENT='mgt_review_reminder_history' ENGINE=INNODB charset=utf8 COLLATE=utf8_general_ci");

$this->run("ALTER TABLE {$tableName} ADD INDEX (`reminder_id`)");
$this->run("ALTER TABLE {$tableName} ADD INDEX (`store_id`)");
$this->run("ALTER TABLE {$tableName} ADD INDEX (`order_id`)");

$this->run("ALTER TABLE {$tableName} ADD FOREIGN KEY (`reminder_id`) REFERENCES `{$installer->getTable('mgt_review_reminder')}` (`reminder_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
$this->run("ALTER TABLE {$tableName} ADD FOREIGN KEY (`store_id`) REFERENCES `{$installer->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
$this->run("ALTER TABLE {$tableName} ADD FOREIGN KEY (`order_id`) REFERENCES `{$installer->getTable('sales_flat_order')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE;");
$this->run("ALTER TABLE {$tableName} CHANGE `created_at` `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'Creation Time'");

$installer->endSetup();