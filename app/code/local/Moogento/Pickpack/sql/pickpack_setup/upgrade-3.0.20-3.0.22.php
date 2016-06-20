<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('moogento_pickpack_flagautoaction')};
CREATE TABLE {$this->getTable('moogento_pickpack_flagautoaction')} (
  			`id` int(11) unsigned NOT NULL auto_increment,
			`orderid` varchar(255) NOT NULL default '',
			`invoice_printed` int(2) NULL,
			`pack_printed` int(2) NULL,
			`separate_printed` int(2) NULL,
			`combined_printed` int(2) NULL,
			`manual_invoice_printed` int(2) NULL,
			`manual_pack_printed` int(2) NULL,
			`manual_separate_printed` int(2) NULL,
			`manual_combined_printed` int(2) NULL,
			PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 