<?php
/**
 * MGT-Commerce GmbH
 * http://www.mgt-commerce.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@mgt-commerce.com so we can send you a copy immediately.
 *
 * @category    Mgt
 * @package     Mgt_ReviewReminder
 * @author      Stefan Wieczorek <stefan.wieczorek@mgt-commerce.com>
 * @copyright   Copyright (c) 2012 (http://www.mgt-commerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mgt_ReviewReminder_Model_Observer 
{
    public function addReminderForShipment(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        
        if ($shipment) {
            $items = array();
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getQty()) {
                    $items[] = $item->getOrderItem();
                }
            }
            Mage::helper('mgt_review_reminder')->createReminderFromOrder($shipment->getOrder(), $items);
        }
    }

    public function sendReminders()
    {
        $isEnabled = (int)Mage::getStoreConfig('mgt-commerce_mgt_review_reminder/mgt_review_reminder_general_settings/enabled');
        if (!$isEnabled) {
            return $this;
        }
        
        $reminders = array(
            0 => 'first',
            1 => 'second',
            2 => 'third'
        );
        
        foreach ($reminders as $reminderNr => $reminder) {
            $notificationAfterDays = (int)Mage::getStoreConfig(sprintf('mgt-commerce_mgt_review_reminder/mgt_review_reminder_time_settings/%s_notification', $reminder));
            if ($notificationAfterDays) {
                $collection = Mage::getResourceModel('mgt_review_reminder_resource/reminder_collection');
                $collection->addDaysAndReminderNrFilter($notificationAfterDays, $reminderNr);
                foreach ($collection as $reminder) {
                    $reminder->send();
                }
            }
        }
    }
}