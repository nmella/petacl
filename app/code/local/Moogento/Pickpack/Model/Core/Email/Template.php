<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Template.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Core_Email_Template
    extends Mage_Core_Model_Email_Template
{
    public function sendWithAttachment($email, $subject, $body, $sender, $attachment = null) {
        $name = substr($email, 0, strpos($email, '@'));

        ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
        ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

        $mail = $this->getMail();

        $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $this->getSenderEmail();
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($returnPathEmail !== null) {
            $mail->setReturnPath($returnPathEmail);
        }

        $mail->addTo($email, '=?utf-8?B?' . base64_encode($name) . '?=');
        $mail->setBodyHTML($body);
        $mail->setSubject('=?utf-8?B?' . base64_encode($subject) . '?=');

        $this->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $sender . '/name'));
        $this->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $sender . '/email'));

        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

        if (is_array($attachment)) {
            $at = new Zend_Mime_Part($attachment['content']);
            $at->type = $attachment['type'];
            $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $at->encoding = Zend_Mime::ENCODING_BASE64;
            $at->filename = $attachment['file_name'];
            $mail->addAttachment($at);
        }

        try {
            $mail->send();
            $this->_mail = null;
        } catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
        }

        return $this;
    }
}
