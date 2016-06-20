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


class AW_Pquestion2_Block_Notification_Manage extends Mage_Core_Block_Template
{
    public function getFormAction()
    {
        return Mage::getUrl(
            "aw_pq2/notification/unsubscribePost",
            array('key' => $this->getRequest()->getParam('key', ''))
        );
    }

    /**
     * @return array;
     */
    public function getNotificationTypes()
    {
        return Mage::helper('aw_pq2/notification')->getNotificationListForCustomer(
            $this->getEmail()
        );
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return Mage::registry('current_email');
    }

}