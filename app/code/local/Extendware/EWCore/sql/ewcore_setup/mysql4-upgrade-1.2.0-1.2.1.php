<?php

$installer = $this;
$installer->startSetup();

$command  = "
DROP TABLE IF EXISTS `ewcore_system_message`;
CREATE TABLE `ewcore_system_message`(
	`system_message_id` int(10) unsigned NOT NULL  auto_increment , 
	`extension` varchar(255) COLLATE utf8_general_ci NOT NULL  , 
	`category` varchar(255) COLLATE utf8_general_ci NOT NULL  , 
	`subject` varchar(255) COLLATE utf8_general_ci NOT NULL  , 
	`body` text COLLATE utf8_general_ci NOT NULL  , 
	`updated_at` datetime NOT NULL  , 
	`created_at` datetime NOT NULL  , 
	PRIMARY KEY (`system_message_id`) 
) ENGINE=InnoDB DEFAULT CHARSET='utf8';
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