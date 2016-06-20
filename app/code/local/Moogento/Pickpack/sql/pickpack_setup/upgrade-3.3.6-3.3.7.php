<?php
$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('moogento_pickpack_printgroup')};
CREATE TABLE {$this->getTable('moogento_pickpack_printgroup')} (
  			`id` int(11) unsigned NOT NULL auto_increment,
			`order_increment_id` varchar(255) NOT NULL default '',
			`orderid` int(11)  NULL,
			`combined_list` varchar(1028) NOT NULL default '',
			`update_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
?>