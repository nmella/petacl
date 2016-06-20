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


class AW_Pquestion2_NotificationController extends Mage_Core_Controller_Front_Action
{
    public function viewWebVersionAction()
    {
        $key = $this->getRequest()->getParam('key', null);
        if (null !== $key) {
            $key = Mage::helper('aw_pq2/notification')->decrypt($key);
            list($email, $queueId) = @explode(',', $key);
            if (!empty($email) && !empty($queueId)) {
                $queueModel = Mage::getResourceModel('aw_pq2/notification_queue')->getStoredEmail($email, $queueId);
                if (null !== $queueModel->getId()) {
                    $this->loadLayout();
                    $this->getLayout()->getBlock('content')->setBody($queueModel->getBody());
                    $this->renderLayout();
                    return $this;
                }
            }
        }
        $this->_forward('noRoute');
        return $this;
    }

    public function unsubscribeAction()
    {
        $key = $this->getRequest()->getParam('key', null);
        if (null === $key) {
            $this->_forward('noRoute');
            return $this;
        }
        $key = Mage::helper('aw_pq2/notification')->decrypt($key);
        list($email, $websiteId) = @explode(',', $key);
        if (empty($email) || empty($websiteId)) {
            $this->_forward('noRoute');
            return $this;
        }
        Mage::register('current_email', $email);
        $this->loadLayout();
        $this->renderLayout();
        return $this;
    }

    public function unsubscribePostAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }
        $key = $this->getRequest()->getParam('key', null);
        if (null === $key) {
            return $this->_redirectReferer();
        }
        $key = Mage::helper('aw_pq2/notification')->decrypt($key);
        list($email, $websiteId) = @explode(',', $key);
        if (empty($email) || empty($websiteId)) {
            return $this->_redirectReferer();
        }
        $subscribeTo = $this->getRequest()->getParam('aw_pq2_notification_subscribe_to', array());
        $subscribeTo = array_map('intval', $subscribeTo);
        //subscribe to
        foreach ($subscribeTo as $type) {
            foreach (AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer[$type] as $notificationType) {
                try {
                    Mage::getModel('aw_pq2/notification')->subscribe($email, $notificationType, $websiteId);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        //unsubscribe from
        $unsubscribeFrom = array_diff(
            array_keys(AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer), $subscribeTo
        );
        foreach ($unsubscribeFrom as $type) {
            foreach (AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer[$type] as $notificationType) {
                try {
                    Mage::getModel('aw_pq2/notification')->unsubscribe($email, $notificationType, $websiteId);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
        Mage::getSingleton('core/session')->addSuccess(
            $this->__("Subscription settings have been successfully saved.")
        );
        return $this->_redirectUrl(Mage::helper('core/url')->getHomeUrl());
    }

    public function loginAction()
    {
        $key = $this->getRequest()->getParam('key', null);
        if (null === $key) {
            $this->_forward('noRoute');
            return $this;
        }
        $key = Mage::helper('aw_pq2/notification')->decrypt($key);
        list($email, $redirectUrl, $storeId) = @explode('|', $key);
        if (empty($email) || empty($redirectUrl) || empty($storeId)) {
            $this->_forward('noRoute');
            return $this;
        }
        $customerSession = Mage::getSingleton('customer/session');
        if (!$customerSession->isLoggedIn()) {
            $store = Mage::app()->getStore($storeId);
            $customer = Mage::getModel('customer/customer')->setWebsiteId($store->getWebsiteId())->loadByEmail($email);
            if ($customer->getId()) {
                $customerSession->setCustomerAsLoggedIn($customer);
            }
        }
        $this->_redirectUrl($redirectUrl);
        return $this;
    }
}