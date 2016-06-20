<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Pquestion2
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_pq2/question')} (
      `entity_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
      `author_name` VARCHAR(255) NOT NULL,
      `author_email` VARCHAR(255) NOT NULL,
      `customer_id` INT(10) unsigned NOT NULL DEFAULT 0,
      `created_at` DATETIME NOT NULL,
      `content` TEXT NOT NULL,
      `product_id` INT(10) unsigned NOT NULL,
      `store_id` SMALLINT(5) unsigned NOT NULL,
      `show_in_store_ids` VARCHAR(255) NOT NULL DEFAULT 0,
      `status` SMALLINT(5) unsigned NOT NULL,
      `visibility` SMALLINT(5) unsigned NOT NULL,
      `sharing_type` SMALLINT(5) unsigned NOT NULL,
      `sharing_value` TEXT NULL,
      `helpfulness` INT(11) NOT NULL DEFAULT 0,
      PRIMARY KEY (`entity_id`))
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_pq2/answer')} (
      `entity_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
      `question_id` INT(10) unsigned NOT NULL,
      `author_name` VARCHAR(255) NOT NULL,
      `author_email` VARCHAR(255) NOT NULL,
      `customer_id` INT(10) unsigned NULL DEFAULT 0,
      `status` SMALLINT(5) unsigned NOT NULL,
      `created_at` DATETIME NOT NULL,
      `content` TEXT NOT NULL,
      `helpfulness` INT(11) NOT NULL DEFAULT 0,
      `is_admin` SMALLINT(5) unsigned NOT NULL DEFAULT 0,
      PRIMARY KEY (`entity_id`),
      INDEX `fk_aw_productquestion_answers_aw_productquestion_idx` (`question_id` ASC),
      CONSTRAINT `fk_aw_productquestion_answers_aw_productquestion`
        FOREIGN KEY (`question_id`)
        REFERENCES {$this->getTable('aw_pq2/question')} (`entity_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_pq2/summary_question')} (
      `entity_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
      `question_id` INT(10) unsigned  NOT NULL,
      `customer_id` INT(10) unsigned  NOT NULL DEFAULT 0,
      `visitor_id` INT(10) unsigned NULL,
      `helpful` TINYINT(2) NOT NULL,
      PRIMARY KEY (`entity_id`),
      INDEX `fk_aw_productquestion_summary_question_aw_productquestion1_idx` (`question_id` ASC),
      CONSTRAINT `fk_aw_productquestion_summary_question_aw_productquestion1`
        FOREIGN KEY (`question_id`)
        REFERENCES {$this->getTable('aw_pq2/question')} (`entity_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_pq2/notification_subscriber')} (
      `entity_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
      `customer_id` INT(10) unsigned  NOT NULL DEFAULT 0,
      `customer_email` VARCHAR(255) NOT NULL,
      `website_id` SMALLINT(5) unsigned NOT NULL,
      `notification_type` VARCHAR(255) NOT NULL,
      `value` TINYINT(2) unsigned NOT NULL DEFAULT 1,
      PRIMARY KEY (`entity_id`),
      UNIQUE ( `customer_email` , `website_id` , `notification_type` ))
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_pq2/notification_queue')} (
      `entity_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
      `notification_type` VARCHAR(255) NOT NULL,
      `recipient_email` VARCHAR(255) NOT NULL,
      `recipient_name` VARCHAR(255) NOT NULL,
      `sender_email` VARCHAR(255) NOT NULL,
      `sender_name` VARCHAR(255) NOT NULL,
      `subject` VARCHAR(255) NOT NULL,
      `body` TEXT NOT NULL,
      `created_at` DATETIME NOT NULL,
      `sent_at` DATETIME NULL,
      PRIMARY KEY (`entity_id`))
    ENGINE = InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS {$this->getTable('aw_pq2/summary_answer')} (
      `entity_id` INT(10) unsigned NOT NULL AUTO_INCREMENT,
      `answer_id` INT(10) unsigned NOT NULL,
      `customer_id` INT(10) unsigned NULL DEFAULT 0,
      `visitor_id` INT(10) unsigned NULL,
      `helpful` TINYINT(2) NOT NULL,
      PRIMARY KEY (`entity_id`),
      INDEX `fk_aw_productquestion_summary_answer_aw_productquestion_ans_idx` (`answer_id` ASC),
      CONSTRAINT `fk_aw_productquestion_summary_answer_aw_productquestion_answe1`
        FOREIGN KEY (`answer_id`)
        REFERENCES {$this->getTable('aw_pq2/answer')} (`entity_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE)
    ENGINE = InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();