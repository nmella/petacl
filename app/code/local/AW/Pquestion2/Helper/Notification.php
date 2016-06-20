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


class AW_Pquestion2_Helper_Notification extends Mage_Core_Helper_Abstract
{
    /**
     * @param int|string|Mage_Customer_Model_Customer $customer
     * @param int|null $websiteId
     * @param string $notificationType
     *
     * @return bool
     */
    public function isCanNotifyCustomer($customer, $notificationType, $websiteId = null)
    {
        $subscriberCollection = Mage::getModel('aw_pq2/notification_subscriber')->getCollection();
        $subscriberCollection
            ->addFilterByCustomer($customer)
            ->addFilterByNotificationType($notificationType)
            ->addFilterByWebsiteId($websiteId)
        ;
        $subscriberModel = $subscriberCollection->getFirstItem();
        if (null !== $subscriberModel->getId()) {
            return (bool)$subscriberModel->getValue();
        } elseif (!Mage::getModel('aw_pq2/source_notification_type')->isCustomerSubscribedByDefault($websiteId)) {
            return false;
        }
        return true;
    }

    /**
     * @param int|string|Mage_Customer_Model_Customer $customer
     * @param int $websiteId = null
     *
     * @return array
     */
    public function getNotificationListForCustomer($customer, $websiteId = null)
    {
        $isSubscribed = array_combine(
            array_keys(AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer),
            array_fill(0, count(AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer), false)
        );

        foreach ($isSubscribed as $key => $value) {
            foreach (AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer[$key] as $notificationType) {
                $isCanNotify = $this->isCanNotifyCustomer($customer, $notificationType, $websiteId);
                if ($isCanNotify) {
                    $isSubscribed[$key] = true;
                    break;
                }
            }
        }
        $groupList = Mage::getModel('aw_pq2/source_notification_type')->getAllGroupForCustomerDataAsArray();
        $data = array();
        foreach ($groupList as $groupKey => $groupData) {
            $data[$groupKey] = array(
                'value'     => $groupKey,
                'label'     => $groupData['label'],
                'checked' => $isSubscribed[$groupKey],
            );
        }
        return $data;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function encrypt($value)
    {
        return $this->urlEncode(Mage::helper('core')->encrypt($value));
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function decrypt($value)
    {
        return Mage::helper('core')->decrypt($this->urlDecode($value));
    }

    /**
     * @param string $customerEmail
     * @param int $queueId
     * @param mixed $store
     *
     * @return string
     */
    public function getViewWebVersionUrl($customerEmail, $queueId, $store)
    {
        return Mage::getUrl(
            "aw_pq2/notification/viewWebVersion",
            array(
                'key'    => $this->encrypt($customerEmail . ',' . $queueId),
                '_store' => $store
            )
        );
    }

    /**
     * @param string $customerEmail
     * @param mixed $store
     *
     * @return string
     */
    public function getUnsubscribeUrl($customerEmail, $store)
    {
        $store = Mage::app()->getStore($store);
        $websiteId = $store->getWebsiteId();
        return Mage::getUrl(
            "aw_pq2/notification/unsubscribe",
            array(
                'key'    => $this->encrypt($customerEmail . ',' . $websiteId),
                '_store' => $store
            )
        );
    }

    public function getAutoLoginUrl($customerEmail, $redirectUrl, $store)
    {
        $store = Mage::app()->getStore($store);
        return Mage::getUrl(
            "aw_pq2/notification/login",
            array(
                'key'    => $this->encrypt($customerEmail . '|' . $redirectUrl . '|' . $store->getId()),
                '_store' => $store
            )
        );
    }
}