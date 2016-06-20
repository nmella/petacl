<?php
$installer = $this;
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('moogento_pickpack_printedtime')};
CREATE TABLE {$this->getTable('moogento_pickpack_printedtime')} (
  			`id` int(11) unsigned NOT NULL auto_increment,
			`order_id` varchar(255) NOT NULL default '',
			`order_increment_id` varchar(255) NOT NULL default '',
			`type` varchar(255) NOT NULL default '',
			`date` datetime default NULL,
     		`timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup(); 