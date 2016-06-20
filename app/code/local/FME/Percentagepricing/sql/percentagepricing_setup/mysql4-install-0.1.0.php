<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('percentagepricing')};
CREATE TABLE {$this->getTable('percentagepricing')} (
  `percentagepricing_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `priorty` int(11) NOT NULL DEFAULT '0',
  `action` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '0',
  `apply` smallint(6) NOT NULL DEFAULT '0',
  `amount` int(11) NOT NULL DEFAULT '0',
  `website_ids` varchar(255) DEFAULT NULL,
  `customer_group_ids` varchar(255) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `percentagepricing_rule` text,
  PRIMARY KEY (`percentagepricing_id`),
  UNIQUE KEY `priorty` (`priorty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
