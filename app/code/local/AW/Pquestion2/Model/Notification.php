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


class AW_Pquestion2_Model_Notification extends Mage_Core_Model_Email_Template
{
    /**
     * @param string $recipientName
     * @param string $recipientEmail
     * @param string $notificationType
     * @param array $variables
     * @param int|null|Mage_Core_Model_Store $store
     *
     * @return Mage_Core_Model_Abstract | false
     * @throws Exception
     */
    public function addToQueue($recipientName, $recipientEmail, $notificationType, $variables, $store)
    {
        $notificationTypeList = Mage::getModel('aw_pq2/source_notification_type')->getAllTypesDataAsArray($store);
        if (!array_key_exists($notificationType, $notificationTypeList)) {
            throw new Exception('Unknown notification type');
        }
        $notificationTypeData = $notificationTypeList[$notificationType];
        if (empty($notificationTypeData['template']) || empty($recipientEmail)) {
            return false;
        }
        if (is_numeric($notificationTypeData['template'])) {
            $this->load($notificationTypeData['template']);
        } else {
            $localeCode = Mage::getStoreConfig('general/locale/code', $store);
            $this->loadDefault($notificationTypeData['template'], $localeCode);
        }
        if (!$this->getId()) {
            throw new Exception('Invalid transactional email code.');
        }
        $_sender = Mage::getModel('aw_pq2/source_notification_type')->getSender();
        if (empty($_sender)) {
            throw new Exception('Sender not specified for this notification type.');
        }

        $this->setDesignConfig(array('area'=>'frontend', 'store' => $store));

        $currentDate = new Zend_Date();
        $queueModel = Mage::getModel('aw_pq2/notification_queue');
        $queueModel
            ->setNotificationType($notificationType)
            ->setRecipientName($recipientName)
            ->setRecipientEmail($recipientEmail)
            ->setSubject('')
            ->setBody('')
            ->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $_sender . '/name', $store))
            ->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $_sender . '/email', $store))
            ->setCreatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
            ->save()
        ;
        $variables['unsubscribe_link'] = Mage::helper('aw_pq2/notification')->getUnsubscribeUrl(
            $recipientEmail, $store
        );
        $variables['web_version_link'] = Mage::helper('aw_pq2/notification')->getViewWebVersionUrl(
            $recipientEmail, $queueModel->getId(), $store
        );
        $queueModel
            ->setSubject($this->getProcessedTemplateSubject($variables))
            ->setBody($this->getProcessedTemplate($variables, true))
            ->save()
        ;
        if ($notificationTypeData['send_now']) {
            $queueModel->send();
        }
        return $queueModel;
    }

    /**
     * @param int|string|Mage_Customer_Model_Customer $customer
     * @param string $notificationType
     * @param id|null $websiteId
     *
     * @return $this
     */
    public function unsubscribe($customer, $notificationType, $websiteId = null)
    {
        $this->_updateSubscriber($customer, $notificationType, 0, $websiteId);
        return $this;
    }

    /**
     * @param int|string|Mage_Customer_Model_Customer $customer
     * @param string $notificationType
     * @param id|null $websiteId
     *
     * @return $this
     */
    public function subscribe($customer, $notificationType, $websiteId = null)
    {
        $this->_updateSubscriber($customer, $notificationType, 1, $websiteId);
        return $this;
    }

    /**
     * @param int|string|Mage_Customer_Model_Customer $customer
     * @param int $notificationType
     * @param int $value
     * @param int $websiteId
     *
     * @return $this
     */
    protected function _updateSubscriber($customer, $notificationType, $value, $websiteId)
    {
        if (null === $websiteId) {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }
        if (is_numeric($customer)) {
            $customer = Mage::getModel('customer/customer')->load($customer);
        }
        if (is_string($customer)) {
            $_email = $customer;
            $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($_email);
            $customer->setEmail($_email);
        }

        if (!$customer instanceof Mage_Customer_Model_Customer) {
            return $this;
        }
        $subscriberModel = $this->_getSubscriber($customer, $notificationType, $websiteId);
        $subscriberModel
            ->setWebsiteId($websiteId)
            ->setNotificationType($notificationType)
            ->setValue($value)
            ->setCustomerId((int)$customer->getId())
            ->setCustomerEmail($customer->getEmail())
            ->save()
        ;
        return $this;
    }

    /**
     * @param int|string|Mage_Customer_Model_Customer $customer
     * @param string $notificationType
     * @param id|null $websiteId
     *
     * @return AW_Pquestion2_Model_Notification_Subscriber
     */
    protected function _getSubscriber($customer, $notificationType, $websiteId = null)
    {
        $subscriberCollection = Mage::getModel('aw_pq2/notification_subscriber')->getCollection();
        $subscriberCollection
            ->addFilterByCustomer($customer)
            ->addFilterByNotificationType($notificationType)
            ->addFilterByWebsiteId($websiteId)
        ;
        return $subscriberCollection->getFirstItem();
    }
}