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


class AW_Pquestion2_Model_Observer_Adminhtml
{
    public function adminhtmlCustomerSaveAfter($observer)
    {
        $customer = $observer->getCustomer();
        $request = $observer->getRequest();
        $subscribeTo = $request->getParam('subscribe_to', array());
        $subscribeTo = array_map('intval', $subscribeTo);
        //subscribe to
        foreach ($subscribeTo as $type) {
            foreach (AW_Pquestion2_Model_Source_Notification_Type::$groupMapForCustomer[$type] as $notificationType) {
                try {
                    Mage::getModel('aw_pq2/notification')->subscribe(
                        $customer, $notificationType, $customer->getWebsiteId()
                    );
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
                    Mage::getModel('aw_pq2/notification')->unsubscribe(
                        $customer, $notificationType, $customer->getWebsiteId()
                    );
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
    }
}