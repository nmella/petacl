<?php

$installer = $this;
$installer->startSetup();

$command  = "
DROP TABLE IF EXISTS `ewcore_config_data`;
CREATE TABLE `ewcore_config_data` (
  `config_data_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `scope` enum('default','websites','stores','config') NOT NULL DEFAULT 'default',
  `scope_id` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL DEFAULT 'general',
  `value` text NOT NULL,
  PRIMARY KEY (`config_data_id`),
  UNIQUE KEY `config_scope` (`scope`,`scope_id`,`path`)
) ENGINE=InnoDB CHARSET=utf8;
";


$command = preg_replace_callback('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/i',
                                  function ($m) {
                                      return $m[1] . $this->getTable($m[2]) . $m[3];
                                  }, $command);
$command = preg_replace_callback('/(ON\s+`)([a-z0-9\_]+?)(`)/i',
                                  function ($m) {
                                      return $m[1] . $this->getTable($m[2]) . $m[3];
                                  }, $command);
$command = preg_replace_callback('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/i',
                                  function ($m) {
                                      return $m[1] . $this->getTable($m[2]) . $m[3];
                                  }, $command);
$command = preg_replace_callback('/(TABLE\s+`)([a-z0-9\_]+?)(`)/i',
                                  function ($m) {
                                      return $m[1] . $this->getTable($m[2]) . $m[3];
                                  }, $command);


$installer->run($command);

$installer->endSetup();