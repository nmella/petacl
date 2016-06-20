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

class Mgt_ReviewReminder_Adminhtml_ReminderController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('newsletter');
        $layout = $this->getLayout();
        $gridContainer = $layout->createBlock('mgt_review_reminder_adminhtml/reminder');
        $this->_addContent($gridContainer);
        $this->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $layout = $this->getLayout();
        $grid = $layout->createBlock('mgt_review_reminder_adminhtml/reminder_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }
    
    public function massSendAction()
    {
        $reminderIds = $this->getRequest()->getParam('reminders', null);
        if ($reminderIds) {
            
            $collection = Mage::getResourceModel('mgt_review_reminder_resource/reminder_collection');
            $collection->filterbyIds($reminderIds);
            
            $reminderSent = 0;
            foreach ($collection as $reminder) {
                if ($reminder->send()) {
                    $reminderSent++;
                }
            }
            
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('Total of %d record(s) has been sent', $reminderSent)
            );
        }
        $this->_redirect('*/*/');
    }
    
    public function massDeleteAction()
    {
        $reminderIds = $this->getRequest()->getParam('reminders', null);
        
        if ($reminderIds) {
            $deletedReminders = 0;
            foreach ($reminderIds as $reminderId) {
                $reminder = Mage::getModel('mgt_review_reminder/reminder')->load($reminderId);
                if ($reminder->getId()) {
                    $deletedReminders++;
                    $reminder->delete();
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('adminhtml')->__('Total of %d record(s) has been deleted', $deletedReminders)
            );
        }
        
        $this->_redirect('*/*/');
    }
}