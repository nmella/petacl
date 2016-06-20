<?php

$installer = $this;
$installer->startSetup();

$command  = "
DROP TABLE IF EXISTS `ewpagecache_clean_job`;
CREATE TABLE `ewpagecache_clean_job` (
  `clean_job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mode` enum('matchingAnyTag','matchingTag') NOT NULL DEFAULT 'matchingAnyTag',
  `tags` text NOT NULL,
  `lock_key` text,
  `locked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`clean_job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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