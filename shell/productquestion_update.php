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

require_once '../lib/Zend/Exception.php';
require_once '../lib/Zend/Config.php';
require_once '../lib/Zend/Config/Xml.php';
require_once '../lib/Zend/Db.php';
require_once '../lib/Zend/Db/Exception.php';
require_once '../lib/Zend/Db/Statement/Interface.php';
require_once '../lib/Zend/Db/Statement/Exception.php';
require_once '../lib/Zend/Db/Statement.php';
require_once '../lib/Zend/Db/Statement/Pdo.php';
require_once '../lib/Zend/Db/Profiler.php';
require_once '../lib/Zend/Db/Adapter/Abstract.php';
require_once '../lib/Zend/Db/Adapter/Pdo/Abstract.php';
require_once '../lib/Zend/Db/Adapter/Pdo/Mysql.php';
require_once '../lib/Zend/Loader.php';

$__config = new Zend_Config_Xml('../app/etc/local.xml');

$__dbNode = $__config->global->resources->db;
$__dbPrefix = $__config->global->resources->db->table_prefix;
$__connectionNode = $__config->global->resources->default_setup->connection;
$__db = Zend_Db::factory(
    'Pdo_Mysql',
    array(
        'host'     => $__connectionNode->host,
        'username' => $__connectionNode->username,
        'password' => $__connectionNode->password,
        'dbname'   => $__connectionNode->dbname
    )
);
$questionIds = $__db->query("SELECT question_id FROM `" . $__dbPrefix . "aw_productquestions`")->fetchAll();
foreach ($questionIds as $questionId) {

    //insert data into aw_pquestion2_question table from aw_productquestions table
    //helpfulness = (vote_sum - (vote_count - vote_sum))
    //customer_id = (SELECT entity_id FROM `" . $__dbPrefix . "customer_entity` AS ce WHERE ce.email = old_pq.question_author_email); default = 0
    //status = Approved
    //sharing_type = Product(s)
    $__db->query("
        ALTER TABLE `" . $__dbPrefix . "aw_productquestions_helpfulness` MODIFY vote_count INT(10) NOT NULL DEFAULT 0;
		ALTER TABLE `" . $__dbPrefix . "aw_productquestions_helpfulness` MODIFY vote_sum INT(10) NOT NULL DEFAULT 0;
        INSERT INTO `" . $__dbPrefix . "aw_pquestion2_question` (author_name, author_email, customer_id, created_at,
            content, product_id, store_id, show_in_store_ids, status, visibility, sharing_type,
            sharing_value, helpfulness
        )
        SELECT old_pq.question_author_name, old_pq.question_author_email,
        IF(
            (SELECT entity_id FROM `" . $__dbPrefix . "customer_entity` AS ce WHERE ce.email = old_pq.question_author_email),
            (SELECT entity_id FROM `" . $__dbPrefix . "customer_entity` AS ce WHERE ce.email = old_pq.question_author_email),
            0
        ), old_pq.question_date, old_pq.question_text, old_pq.question_product_id, old_pq.question_store_id,
        old_pq.question_store_ids, 2, old_pq.question_status, 1, old_pq.question_product_id,
        (
            SELECT (vote_sum - (vote_count - vote_sum))
            FROM `" . $__dbPrefix . "aw_productquestions_helpfulness`
            WHERE question_id = old_pq.question_id
        )
        FROM `" . $__dbPrefix . "aw_productquestions` as old_pq WHERE question_id =" . $questionId['question_id']
    );
    $newQuestionId = $__db->lastInsertId();

    //insert data into aw_pquestion2_answer table from aw_productquestions table
    //customer_id = (SELECT entity_id FROM `" . $__dbPrefix . "customer_entity` AS ce WHERE ce.email = old_pq.question_author_email); default = 0
    //status = Approved
    //is_admin = 1
    //helpfulness = 0
    //author_name = first name from table admin_user
    //author_email = first email from table admin_user
    $__db->query("
        INSERT INTO `" . $__dbPrefix . "aw_pquestion2_answer` (question_id, author_name, author_email,
            customer_id, status, created_at, content, helpfulness, is_admin
        )
        SELECT " . $newQuestionId . ",
            (SELECT CONCAT(firstname, ' ', lastname) FROM `" . $__dbPrefix . "admin_user` limit 1),
            (SELECT email FROM `" . $__dbPrefix . "admin_user` limit 1),
        0, 2, old_pq.question_date, old_pq.question_reply_text, 0, 1
        FROM `" . $__dbPrefix . "aw_productquestions` as old_pq
        WHERE question_id = " . $questionId['question_id'] . " AND old_pq.question_reply_text != ''"
    );
}
echo 'Complete!';
