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

class AW_Pquestion2_CustomerController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
        if (!Mage::helper('aw_pq2/config')->getIsEnabled()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Product Questions'));
        $this->renderLayout();
    }

    public function subscribePostAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectReferer();
        }
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $subscribeTo = $this->getRequest()->getParam('aw_pq2_customer_subscribe_to', array());
        $subscribeTo = array_map('intval', $subscribeTo);
        //subscribe to
        foreach ($subscribeTo as $type) {
            foreach (AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer[$type] as $notificationType) {
                Mage::getModel('aw_pq2/notification')->subscribe($customer, $notificationType);
            }
        }

        //unsubscribe from
        $unsubscribeFrom = array_diff(
            array_keys(AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer), $subscribeTo
        );
        foreach ($unsubscribeFrom as $type) {
            foreach (AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer[$type] as $notificationType) {
                Mage::getModel('aw_pq2/notification')->unsubscribe($customer, $notificationType);
            }
        }
        Mage::getSingleton('core/session')->addSuccess(
            $this->__("Subscription settings have been successfully saved.")
        );
        return $this->_redirectReferer();
    }
}