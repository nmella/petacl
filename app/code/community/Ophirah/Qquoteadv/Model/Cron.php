<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2016] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2016 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

class Ophirah_Qquoteadv_Model_Cron
{

    /**
     * Cron job route to the qqadvcustomer sendReminderEmail
     */
    public function sendReminderEmail()
    {
        // Check for a valid Enterprise license
        if (Mage::helper('qquoteadv/license')->validLicense('qquoteadv_qquoteadv_reminder_email', null, true)) {
            Mage::getModel('qquoteadv/qqadvcustomer')->sendReminderEmail();
        } else {
            //This function is called by a cron job, so a notice won't do anything.
            //$message = Mage::helper('qquoteadv')->__("This function is only available in the Enterprise edition. <a href='https://www.cart2quote.com/magento-quotation-module-pricing.html'>Upgrade</a>");
            //Mage::getSingleton('adminhtml/session')->addNotice($message);
        }
    }

    /**
     * Cron job route to the qqadvcustomer sendExpireEmail
     */
    public function sendExpireEmail()
    {
        // Check for a valid license
        if (Mage::helper('qquoteadv/license')->validLicense('qquoteadv_qquoteadv_expire_email', null, true)) {
            Mage::getModel('qquoteadv/qqadvcustomer')->sendExpireEmail();
        } else {
            //This function is called by a cron job, so a notice won't do anything.
            //$message = Mage::helper('qquoteadv')->__("This function is only available from the Starter and above editions. <a href='https://www.cart2quote.com/magento-quotation-module-pricing.html'>Upgrade</a>");
            //Mage::getSingleton('adminhtml/session')->addNotice($message);
        }
    }

}
