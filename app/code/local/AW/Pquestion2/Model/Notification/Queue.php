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


class AW_Pquestion2_Model_Notification_Queue extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_pq2/notification_queue');
    }

    public function send()
    {
        if (Mage::getStoreConfigFlag('system/smtp/disable')) {
            return $this;
        }
        $email = Mage::getModel('aschroder_email/email_template');
        // $mail->setBodyHtml($this->getBody());
        // $mail
        //     ->setFrom($this->getSenderEmail(), $this->getSenderName())
        //     ->addTo($this->getRecipientEmail(), $this->getRecipientName())
        //     ->setSubject($this->getSubject())
        // ;

        $subject = $this->getSubject();
        $message = $this->getBody();
        $sender = array(
            'name'  => $this->getSenderName(),
            'email' => $this->getSenderEmail()
        );
        $name = array(
            'name'  => $this->getRecipientName(),
            'email' => $this->getRecipientEmail()
        );
        
        $email->setSenderName($sender['name']);
        $email->setSenderEmail($sender['email']);

        $email->setTemplateSubject($subject);
        $email->setTemplateText($message);

        $recipients = array($name['email']);



        try {
            $email->send(
                $recipients,
                $name['name'],
                array(
                     'name'    => $name['name'],
                     'email'   => $name['email'],
                     'subject' => $subject,
                     'message' => $message
                )
            );
            $_today = new Zend_Date();
            $this->setSentAt($_today->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))->save();
        } catch (Exception $e){
            Mage::logException($e);
        }
        return $this;
    }
}