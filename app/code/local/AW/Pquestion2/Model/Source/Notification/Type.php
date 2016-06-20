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


class AW_Pquestion2_Model_Source_Notification_Type
{
    const ANSWER_STATUS_CHANGE_TO_CUSTOMER    = 'aw_pq2_answer_status_change_to_customer';
    const ASK_CUSTOMER                        = 'aw_pq2_ask_customer';
    const QUESTION_AUTO_RESPONDER             = 'aw_pq2_question_auto_responder';
    const ANSWER_AUTO_RESPONDER               = 'aw_pq2_answer_auto_responder';
    const NEW_ANSWER_TO_ADMIN                 = 'aw_pq2_new_answer_to_admin';
    const NEW_QUESTION_TO_ADMIN               = 'aw_pq2_new_question_to_admin';
    const NEW_REPLY_ON_QUESTION_TO_CUSTOMER   = 'aw_pq2_new_reply_on_question_to_customer';
    const QUESTION_STATUS_CHANGE_TO_CUSTOMER  = 'aw_pq2_question_status_change_to_customer';

    const GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_QUESTIONS_UPDATES_VALUE = 1;
    const GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_ANSWERS_UPDATES_VALUE = 2;
    const GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_ANSWER_SUGGESTIONS_VALUE = 3;

    public static $groupMapForCustomer = array(
        self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_QUESTIONS_UPDATES_VALUE => array(
            self::QUESTION_STATUS_CHANGE_TO_CUSTOMER,
            self::QUESTION_AUTO_RESPONDER,
            self::NEW_REPLY_ON_QUESTION_TO_CUSTOMER,
        ),
        self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_ANSWERS_UPDATES_VALUE => array(
            self::ANSWER_STATUS_CHANGE_TO_CUSTOMER,
            self::ANSWER_AUTO_RESPONDER
        ),
        self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_ANSWER_SUGGESTIONS_VALUE => array(
            self::ASK_CUSTOMER
        )
    );

    public function getAllTypesDataAsArray($store = null)
    {
        return array(
            self::ANSWER_STATUS_CHANGE_TO_CUSTOMER   => array(
                'template' => Mage::helper('aw_pq2/config')->getStatusChangeToCustomerTemplate($store),
                'send_now' => true
            ),
            self::ASK_CUSTOMER                       => array(
                'template' => Mage::helper('aw_pq2/config')->getAskCustomerTemplateToCustomer($store),
                'send_now' => false
            ),
            self::QUESTION_AUTO_RESPONDER            => array(
                'template' => Mage::helper('aw_pq2/config')->getAutoResponderQuestionTemplate($store),
                'send_now' => true
            ),
            self::ANSWER_AUTO_RESPONDER              => array(
                'template' => Mage::helper('aw_pq2/config')->getAutoResponderAnswerTemplate($store),
                'send_now' => true
            ),
            self::NEW_ANSWER_TO_ADMIN                => array(
                'template' => Mage::helper('aw_pq2/config')->getNewAnswerToAdminTemplate($store),
                'send_now' => true
            ),
            self::NEW_QUESTION_TO_ADMIN              => array(
                'template' => Mage::helper('aw_pq2/config')->getNewQuestionToAdminTemplate($store),
                'send_now' => true
            ),
            self::NEW_REPLY_ON_QUESTION_TO_CUSTOMER  => array(
                'template' => Mage::helper('aw_pq2/config')->getNewReplyOnQuestionToCustomerTemplate($store),
                'send_now' => true
            ),
            self::QUESTION_STATUS_CHANGE_TO_CUSTOMER => array(
                'template' => Mage::helper('aw_pq2/config')->getQuestionStatusChangeToCustomerTemplate($store),
                'send_now' => true
            ),
        );
    }

    public function getAllGroupForCustomerDataAsArray()
    {
        return array(
            self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_QUESTIONS_UPDATES_VALUE => array(
                'label' => Mage::helper('aw_pq2')->__('Notifications about my questions updates'),
            ),
            self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_MY_ANSWERS_UPDATES_VALUE => array(
                'label' => Mage::helper('aw_pq2')->__('Notifications about my answers updates'),
            ),
            self::GROUP_FOR_CUSTOMER_NOTIFICATION_ABOUT_ANSWER_SUGGESTIONS_VALUE => array(
                'label' => Mage::helper('aw_pq2')->__('Notifications about answer suggestions'),
            ),
        );
    }

    public function getSender($store = null)
    {
        return Mage::helper('aw_pq2/config')->getEmailSender($store);
    }

    public function getStoredEmailsLifetime($store = null)
    {
        return Mage::helper('aw_pq2/config')->getStoredEmailsLifetime($store);
    }

    /**
     * @param int|null $websiteId = null
     *
     * @return bool
     */
    public function isCustomerSubscribedByDefault($websiteId = null)
    {
        return Mage::helper('aw_pq2/config')->getAllowSubscribeToNotificationAutomatically(
            Mage::app()->getWebsite($websiteId)->getDefaultStore()
        );
    }
}