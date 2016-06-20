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


class AW_Pquestion2_Helper_Config extends Mage_Core_Helper_Abstract
{
    const GENERAL_IS_ENABLED
        = 'aw_pq2/general/is_enabled';
    const GENERAL_ALLOW_GUEST_TO_ASK_QUESTION
        = 'aw_pq2/general/allow_guest_to_ask_question';
    const GENERAL_ALLOW_CUSTOMER_TO_ADD_ANSWER_FROM_PRODUCT_PAGE
        = 'aw_pq2/general/allow_customer_to_add_answer_from_product_page';
    const GENERAL_REQUIRE_MODERATE_CUSTOMER_ANSWER
        = 'aw_pq2/general/require_moderate_customer_answer';
    const GENERAL_BOUGHT_PRODUCT_DAYS_AGO
        = 'aw_pq2/general/bought_product_days_ago';
    const GENERAL_ALLOW_GUEST_RATE_HELPFULNESS
        = 'aw_pq2/general/allow_guest_rate_helpfulness';
    const GENERAL_ALLOW_SUBSCRIBE_TO_NOTIFICATION_AUTOMATICALLY
        = 'aw_pq2/general/allow_subscribe_to_notification_automatically';
    const GENERAL_ALLOW_CUSTOMER_DEFINED_QUESTION_VISIBILITY
        = 'aw_pq2/general/allow_customer_defined_question_visibility';

    const INTERFACE_NUMBER_QUESTIONS_TO_DISPLAY
        = 'aw_pq2/interface/number_questions_to_display';
    const INTERFACE_NUMBER_ANSWERS_TO_DISPLAY
        = 'aw_pq2/interface/number_answers_to_display';
    const INTERFACE_ALLOW_DISPLAY_URL_AS_LINK
        = 'aw_pq2/interface/allow_display_url_as_link';
    const INTERFACE_DEFAULT_QUESTIONS_SORT_BY
        = 'aw_pq2/interface/default_questions_sort_by';
    const INTERFACE_DEFAULT_SORT_ORDER
        = 'aw_pq2/interface/default_sort_order';

    const NOTIFICATION_SEND_NOTIFICATION_NEW_QUESTION_TO
        = 'aw_pq2/notification/send_notification_new_question_to';
    const NOTIFICATION_EMAIL_SENDER
        = 'aw_pq2/notification/email_sender';
    const NOTIFICATION_NEW_QUESTION_TO_ADMIN_TEMPLATE
        = 'aw_pq2/notification/new_question_to_admin_template';
    const NOTIFICATION_NEW_ANSWER_TO_ADMIN_TEMPLATE
        = 'aw_pq2/notification/new_answer_to_admin_template';
    const NOTIFICATION_NEW_REPLY_ON_QUESTION_TO_CUSTOMER_TEMPLATE
        = 'aw_pq2/notification/new_reply_on_question_to_customer_template';
    const NOTIFICATION_QUESTION_STATUS_CHANGE_TO_CUSTOMER_TEMPLATE
        = 'aw_pq2/notification/question_status_change_to_customer_template';
    const NOTIFICATION_ANSWER_STATUS_CHANGE_TO_CUSTOMER_TEMPLATE
        = 'aw_pq2/notification/answer_status_change_to_customer_template';
    const NOTIFICATION_ASK_CUSTOMER_TEMPLATE_TO_CUSTOMER
        = 'aw_pq2/notification/ask_customer_template_to_customer';
    const NOTIFICATION_AUTO_RESPONDER_QUESTION_TEMPLATE
        = 'aw_pq2/notification/auto_responder_question_template';
    const NOTIFICATION_AUTO_RESPONDER_ANSWER_TEMPLATE
        = 'aw_pq2/notification/auto_responder_answer_template';
    const NOTIFICATION_STORED_EMAILS_LIFETIME
        = 'aw_pq2/notification/stored_emails_lifetime';

    public function getIsEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_IS_ENABLED, $store);
    }

    public function getAllowGuestToAskQuestion($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_ALLOW_GUEST_TO_ASK_QUESTION, $store);
    }

    public function getAllowCustomerToAddAnswer($store = null)
    {
        return Mage::getStoreConfig(self::GENERAL_ALLOW_CUSTOMER_TO_ADD_ANSWER_FROM_PRODUCT_PAGE, $store);
    }

    public function getRequireModerateCustomerAnswer($store = null)
    {
        return !(bool)Mage::getStoreConfig(self::GENERAL_REQUIRE_MODERATE_CUSTOMER_ANSWER, $store);
    }

    /**
     * @param Mage_Core_Model_Store|null $store
     *
     * @return int|null
     */
    public function getBoughtProductDaysAgo($store = null)
    {
        $value = (int)Mage::getStoreConfig(self::GENERAL_BOUGHT_PRODUCT_DAYS_AGO, $store);
        if ($value < 1) {
            return null;
        }
        return $value;
    }

    public function isAllowGuestRateHelpfulness($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_ALLOW_GUEST_RATE_HELPFULNESS, $store);
    }

    public function getAllowSubscribeToNotificationAutomatically($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_ALLOW_SUBSCRIBE_TO_NOTIFICATION_AUTOMATICALLY, $store);
    }

    public function getAllowCustomerDefinedQuestionVisibility($store = null)
    {
        return (bool)Mage::getStoreConfig(self::GENERAL_ALLOW_CUSTOMER_DEFINED_QUESTION_VISIBILITY, $store);
    }

    public function getNumberQuestionsToDisplay($store = null)
    {
        return (int)Mage::getStoreConfig(self::INTERFACE_NUMBER_QUESTIONS_TO_DISPLAY, $store);
    }

    public function getNumberAnswersToDisplay($store = null)
    {
        return (int)Mage::getStoreConfig(self::INTERFACE_NUMBER_ANSWERS_TO_DISPLAY, $store);
    }

    public function isAllowDisplayUrlAsLink($store = null)
    {
        return (bool)Mage::getStoreConfig(self::INTERFACE_ALLOW_DISPLAY_URL_AS_LINK, $store);
    }

    public function getDefaultQuestionsSortBy($store = null)
    {
        return Mage::getStoreConfig(self::INTERFACE_DEFAULT_QUESTIONS_SORT_BY, $store);
    }

    public function getDefaultSortOrder($store = null)
    {
        return Mage::getStoreConfig(self::INTERFACE_DEFAULT_SORT_ORDER, $store);
    }

    public function getSendNewQuestionTo($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_SEND_NOTIFICATION_NEW_QUESTION_TO, $store);
    }

    public function getEmailSender($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_EMAIL_SENDER, $store);
    }

    public function getNewQuestionToAdminTemplate($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_NEW_QUESTION_TO_ADMIN_TEMPLATE, $store);
    }

    public function getNewAnswerToAdminTemplate($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_NEW_ANSWER_TO_ADMIN_TEMPLATE, $store);
    }

    public function getNewReplyOnQuestionToCustomerTemplate($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_NEW_REPLY_ON_QUESTION_TO_CUSTOMER_TEMPLATE, $store);
    }

    public function getQuestionStatusChangeToCustomerTemplate($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_QUESTION_STATUS_CHANGE_TO_CUSTOMER_TEMPLATE, $store);
    }

    public function getStatusChangeToCustomerTemplate($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_ANSWER_STATUS_CHANGE_TO_CUSTOMER_TEMPLATE, $store);
    }

    public function getAskCustomerTemplateToCustomer($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_ASK_CUSTOMER_TEMPLATE_TO_CUSTOMER, $store);
    }

    public function getAutoResponderQuestionTemplate($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_AUTO_RESPONDER_QUESTION_TEMPLATE, $store);
    }

    public function getAutoResponderAnswerTemplate($store = null)
    {
        return Mage::getStoreConfig(self::NOTIFICATION_AUTO_RESPONDER_ANSWER_TEMPLATE, $store);
    }

    public function getStoredEmailsLifetime($store = null)
    {
        $value = (int)Mage::getStoreConfig(self::NOTIFICATION_STORED_EMAILS_LIFETIME, $store);
        if ($value < 1) {
            return null;
        }
        return $value;
    }
}