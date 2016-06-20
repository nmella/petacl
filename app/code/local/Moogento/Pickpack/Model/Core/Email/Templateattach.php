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
 * File        Templateattach.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */

$flag = 0;
if (Mage::helper('pickpack')->isInstalled('Aschroder_SMTPPro')) {
    if (Mage::helper('core')->isModuleEnabled('smtppro')) {
        $flag = 1;
    }
    if (Mage::helper('core')->isModuleEnabled('Aschroder_SMTPPro')) {
        $flag = 1;
    }
    if (Mage::getStoreConfig('system/smtppro/option') != "disabled") {
        $flag = 1;
    }
}

//TODO new email
if (Mage::helper('pickpack')->isInstalled('Aschroder_Email')) {
    if (Mage::helper('core')->isModuleEnabled('aschroder_email')) {
        $flag = 5;
    }
    if (Mage::helper('core')->isModuleEnabled('Aschroder_Email')) {
        $flag = 5;
    }
    if (Mage::getStoreConfig('system/aschroder_email/option') != "disabled") {
        $flag = 5;
    }
}

if (Mage::helper('pickpack')->isInstalled('Aschroder_SMTPPro')) {
    if (Mage::helper('core')->isModuleEnabled('smtppro')) {
        $flag = 1;
    }
    if (Mage::helper('core')->isModuleEnabled('Aschroder_SMTPPro')) {
        $flag = 1;
    }
    if (Mage::getStoreConfig('system/smtppro/option') != "disabled") {
        $flag = 1;
    }
}

if (Mage::helper('pickpack')->isInstalled('Ebizmarts_Mandrill')) {
    if(class_exists('Ebizmarts_Mandrill_Model_System_Config')){
        $storeId = Mage::app()->getStore()->getId();
        if (Mage::helper('core')->isModuleEnabled('mandrill')) {
            $flag = 2;
        }
        else
            if (Mage::helper('core')->isModuleEnabled('Ebizmarts_Mandrill') && Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE,$storeId)) {
                    $flag = 2;
            }
    }
}

if (Mage::helper('pickpack')->isInstalled('AW_Customsmtp')) {
    if (Mage::helper('core')->isModuleEnabled('AW_Customsmtp')) {
        $aw_version = Mage::getConfig()->getNode()->modules->AW_Customsmtp->version;
        $flag = 3;
    }
    
}

if (Mage::helper('pickpack')->isInstalled('Aitoc_Aitemails')) {
    if (Mage::helper('core')->isModuleEnabled('Aitoc_Aitemails')) {
        $at_version = Mage::getConfig()->getNode()->modules->Aitoc_Aitemails->version;
        $flag = 4;
    }
    
}

if (Mage::helper('pickpack')->isInstalled('Junaidbhura_Mandrill')) {
    if (Mage::helper('core')->isModuleEnabled('Junaidbhura_Mandrill')) {
        $flag = 6;
    }
    
}

if($flag == 6)
{
    class Moogento_Pickpack_Model_Core_Email_Templateattach extends Junaidbhura_Mandrill_Model_Email_Template
    {
        public function send($email, $name = null, array $variables = array()) {
            try{    
                if ( Mage::getStoreConfig( 'mandrill/mandrill/active' ) && Mage::getStoreConfig( 'mandrill/mandrill/api_key' ) != '' ) 
                {

                if ( ! $this->isValidForSend() ) {
                    Mage::logException( new Exception( 'This letter cannot be sent.' ) );
                    return false;
                }

                // Set up names and email addresses
                $emails = array_values( (array)$email );
                $names = is_array( $name ) ? $name : (array)$name;
                $names = array_values( $names );
                foreach ( $emails as $key => $email ) {
                    if ( ! isset( $names[$key] ) ) {
                        $names[ $key ] = substr( $email, 0, strpos( $email, '@' ) );
                    }
                }

                // Get message
                $this->setUseAbsoluteLinks( true );
                $variables['email'] = reset( $emails );
                $variables['name'] = reset( $names );
                $text = $message_pre = $this->getProcessedTemplate( $variables, true );
                // Prepare email
                $email = array( 'subject' => $this->getProcessedTemplateSubject( $variables ), 'to' => array() );
                $mail = $this->getMail();

                $this->setUseAbsoluteLinks(true);
                $text = $this->getProcessedTemplate($variables, true);
            
                $needle1 = "attach_invoice";
                $needle2 = "attach_packingsheet";
            
                if (strpos($text, $needle1) !== FALSE) {
                    $invoice_pos            = strpos($text, $needle1);
                    $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                    $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                    $right_invoice_str      = strstr($text, $needle1);
                    $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                    $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    $remove_invoice_text    = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                    $text                   = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace("<p>" . $remove_invoice_text, '', $text);
                    $text                   = str_replace($remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace($remove_invoice_text, '', $text);
                    $searchArray            = array(
                        "$needle1",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                    $replaceArray           = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $invoice_order_id       = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    //Get PDF invoice
                    $orderIds               = array();
                    $orders                 = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($invoice_order_id)
                    ))->setPageSize(200);
            
                    foreach ($orders as $key => $value) {
                        $orderIds[] = $value['entity_id'];
                    }
                    $from_shipment = 'order';
                    if (!empty($orderIds)) {
                        $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                        //Attach PDF invoice into email
                        //Option 1      
                        // $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                        $email->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                    }
                }
                if (strpos($text, $needle2) !== FALSE) {
                    $pick_pos            = strpos($text, $needle2);
                    $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                    $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                    $right_pick_str      = strstr($text, $needle2);
                    $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                    $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                    $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
            
                    $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                    $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                    $text        = str_replace($remove_pick_text . "</p>", '', $text);
                    $text        = str_replace($remove_pick_text, '', $text);
                    $searchArray = array(
                        "$needle2",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
            
                    $replaceArray  = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                
                    //Get PDF Packing sheet
                    $pack_orderIds = array();
            
                    $orderIds    = array();
                    $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($pick_order_id)
                    ))->setPageSize(2);
                
                
            
                    foreach ($pack_orders as $key => $value) {
                        $pack_orderIds[] = $value['entity_id'];
                    }
            
                    if (!empty($pack_orderIds)) {
                        $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
    //                     $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                        $email->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                    }
            
                } 
            
            
                // Initialize Mandrill
                $mandrill = new Junaidbhura_Mandrill( Mage::getStoreConfig( 'mandrill/mandrill/api_key' ) );

                // Prepare email
    //          $email = array( 'subject' => $this->getProcessedTemplateSubject( $variables ), 'to' => array() );

                for ( $i = 0; $i < count( $emails ); $i++ ) {
                    if ( isset( $names[ $i ] ) ) {
                        $email['to'][] = array(
                            'email' => $emails[ $i ],
                            'name' => $names[ $i ]
                        );
                    }
                    else {
                        $email['to'][] = array(
                            'email' => $emails[ $i ],
                            'name' => ''
                        );
                    }
                }

                for ( $i = 0; $i < count( $this->_bcc_array ); $i++ ) {
                    $email['to'][] = array(
                        'email' => $this->_bcc_array[ $i ],
                        'name' => ''
                    );
                }

                if ( Mage::getStoreConfig( 'mandrill/mandrill/from_name' ) != '' )
                    $email['from_name'] = Mage::getStoreConfig( 'mandrill/mandrill/from_name' );
                else
                    $email['from_name'] = $this->getSenderName();

                if ( Mage::getStoreConfig( 'mandrill/mandrill/from_email' ) != '' )
                    $email['from_email'] = Mage::getStoreConfig( 'mandrill/mandrill/from_email' );
                else
                    $email['from_email'] = $this->getSenderEmail();

                if( $this->isPlain() )
                    $email['text'] = $message;
                else
                    $email['html'] = $message;

                // Send the email!
                try {
                    $result = $mandrill->messages->send( $email );
                }
                catch( Exception $e ) {
                    // Oops, some error in sending the email!
                    Mage::logException( $e );
                    return false;
                }

                // Woo hoo! Email sent!
                return true;

            }
                else {
                    // Extension is not enabled, use parent's function
                    return parent::send( $email, $name, $variables );
                }
            
            }
            catch(Exception $e)
            {
                Mage::logException($e);
                return parent::send($email, $name, $variables);
            }
        }
    }
    
}
else
if($flag == 4)
{
    class Moogento_Pickpack_Model_Core_Email_Templateattach extends Aitoc_Aitemails_Model_Rewrite_CoreEmailTemplate
    {
        public function send($email, $name = null, array $variables = array()) {

            $helper = Mage::helper('mandrill');

            //Check if should use Mandrill Transactional Email Service
            if(FALSE === $helper->useTransactionalService()){
                return parent::send($email, $name, $variables);
            }

            if (!$this->isValidForSend()) {
                Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                return false;
            }

            $emails = array_values((array)$email);

            if(count($this->_bcc) > 0){
    //          $bccEmail = $this->_bcc[0];
                $bccEmail = $this->_bcc;
            }else{
                $bccEmail = '';
            }

            $names = is_array($name) ? $name : (array)$name;
            $names = array_values($names);
            foreach ($emails as $key => $email) {
                if (!isset($names[$key])) {
                    $names[$key] = substr($email, 0, strpos($email, '@'));
                }
            }

            $variables['email'] = reset($emails);
            $variables['name'] = reset($names);

            $mail = $this->getMail();

            $this->setUseAbsoluteLinks(true);
            $text = $this->getProcessedTemplate($variables, true);
            
            $needle1 = "attach_invoice";
            $needle2 = "attach_packingsheet";
            
            if (strpos($text, $needle1) !== FALSE) {
                $invoice_pos            = strpos($text, $needle1);
                $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                $right_invoice_str      = strstr($text, $needle1);
                $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                $remove_invoice_text    = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                $text                   = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                $text                   = str_replace("<p>" . $remove_invoice_text, '', $text);
                $text                   = str_replace($remove_invoice_text . "</p>", '', $text);
                $text                   = str_replace($remove_invoice_text, '', $text);
                $searchArray            = array(
                    "$needle1",
                    "{{",
                    "}}",
                    "(",
                    ")"
                );
                $replaceArray           = array(
                    "",
                    "",
                    "",
                    "",
                    ""
                );
                $invoice_order_id       = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                $text        = str_replace($pickpack_email_str, '', $text);
                //Get PDF invoice
                $orderIds               = array();
                $orders                 = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                    'in' => trim($invoice_order_id)
                ))->setPageSize(200);
            
                foreach ($orders as $key => $value) {
                    $orderIds[] = $value['entity_id'];
                }
                $from_shipment = 'order';
                if (!empty($orderIds)) {
                    $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                    //Attach PDF invoice into email
                    //Option 1      
                    $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                }
            }
            if (strpos($text, $needle2) !== FALSE) {
                $pick_pos            = strpos($text, $needle2);
                $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                $right_pick_str      = strstr($text, $needle2);
                $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
            
                $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                $text        = str_replace($remove_pick_text . "</p>", '', $text);
                $text        = str_replace($remove_pick_text, '', $text);
                $searchArray = array(
                    "$needle2",
                    "{{",
                    "}}",
                    "(",
                    ")"
                );
            
                $replaceArray  = array(
                    "",
                    "",
                    "",
                    "",
                    ""
                );
                $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                
                //Get PDF Packing sheet
                $pack_orderIds = array();
            
                $orderIds    = array();
                $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                    'in' => trim($pick_order_id)
                ))->setPageSize(2);
                
                
            
                foreach ($pack_orders as $key => $value) {
                    $pack_orderIds[] = $value['entity_id'];
                }
            
                if (!empty($pack_orderIds)) {
                    $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                    $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                }
            
            }    
            
//New TODO
            try {

                $message = array (
                                'subject'     => $this->getProcessedTemplateSubject($variables),
                                'from_name'   => $this->getSenderName(),
                                'from_email'  => $this->getSenderEmail(),
                                'to_email'    => $emails,
                                'to_name'     => $names,
                                'bcc_address' => $bccEmail,
                                'headers'     => array('Reply-To' => $this->replyTo)
                            );

                if($this->isPlain()) {
                    $message['text'] = $text;
                } else {
                    if (false === strpos($text, '<html>'))
                    {
                        $text = '<html>' . $text;
                    }
                    if (false === strpos($text, '</html>'))
                    {
                        $text = $text . '</html>';
                    }
                    $message['html'] = $text;
                }
                if(isset($variables['tags']) && count($variables['tags'])) {
                    $message ['tags'] = $variables['tags'];
                }
                else {
                    $templateId = (string)$this->getId();
                    $templates = parent::getDefaultTemplates();
                    if (isset($templates[$templateId])) {
                        $message ['tags'] =  array(substr($templates[$templateId]['label'], 0, 50));
                    } else {
                            if($this->getTemplateCode()){
                                $message ['tags'] = array(substr($this->getTemplateCode(), 0, 50));
                            } else {
                                $message ['tags'] = array(substr($templateId, 0, 50));
                            }
                    }
                }

                // adding attachments
                $attachmentCollection = Mage::getModel('aitemails/aitattachment')->getTemplateAttachments($this->getId());
                if (count($attachmentCollection) > 0)
                {
                    foreach ($attachmentCollection as $attachment)
                    {
                        if ($attachment->getAttachmentFile())
                        {
                            $sFileExt = substr($attachment->getAttachmentFile(), strrpos($attachment->getAttachmentFile(), '.'));
                            if ($attachment->getData('store_title'))
                            {
                                $sFileName = $this->normalizeFilename($attachment->getData('store_title')) . $sFileExt;
                            } else
                            {
                                $sFileName = substr($attachment->getAttachmentFile(), 1 + strrpos($attachment->getAttachmentFile(), '/'));
                            }
                            $att = $mail->createAttachment(file_get_contents(Aitoc_Aitemails_Model_Aitattachment::getBasePath() . $attachment->getAttachmentFile()));
                            $att->filename = $sFileName;
                        }
                    }
                }


                $sent = $mail->sendEmail($message);
                if($mail->errorCode){
                    return false;
                }

            }catch (Exception $e) {
                Mage::logException($e);
                return false;
            }

            return true;
        }

    }
}
else
if($flag == 3)
{   
    class Moogento_Pickpack_Model_Core_Email_Templateattach extends AW_Customsmtp_Model_Email_Template
    {
        public function sendMail(AW_Customsmtp_Model_Mail $mailDataModel, $storeId)
        {
            $config = array(
                'port'     => Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_PORT, $storeId),
                'auth'     => Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_AUTH, $storeId),
                'username' => Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_LOGIN, $storeId),
                'password' => Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_PASSWORD, $storeId),
            );

            $needSSL = Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_SSL, $storeId);
            if (!empty($needSSL)) {
                $config['ssl'] = Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_SSL, $storeId);
            }

            $transport = new Zend_Mail_Transport_Smtp(
                Mage::getStoreConfig(AW_Customsmtp_Helper_Config::XML_PATH_SMTP_HOST, $storeId), $config
            );
            ini_set('SMTP', Mage::getStoreConfig('system/smtp/host', $storeId));
            ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port', $storeId));

            $mail = $this->getMail();

            $mail->setSubject('=?utf-8?B?' . base64_encode($mailDataModel->getSubject()) . '?=');
        
            /* Starts from 1.10.1.1 version "TO" holds array values */
            if (!empty($this->_saveRange)) {
                foreach ($this->_saveRange as $range) {
                    $mail->addTo($range['email'], '=?utf-8?B?' . base64_encode($range['name']) . '?=');
                }
            } else {
                $mail->addTo(
                    $mailDataModel->getToEmail(), '=?utf-8?B?' . base64_encode($mailDataModel->getToName()) . '?='
                );
            }

            if (!array_key_exists('Reply-To', $mail->getHeaders())) {
                $mail->setReplyTo($mailDataModel->getFromEmail(), $mailDataModel->getFromName());
            }

            $mail->setFrom($mailDataModel->getFromEmail(), $mailDataModel->getFromName());
            
            $text =  $mailDataModel->getBody();
            
            $needle1 = "attach_invoice";
                $needle2 = "attach_packingsheet";
                
                if (strpos($text, $needle1) !== FALSE) {
                    $invoice_pos            = strpos($text, $needle1);
                    $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                    $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                    $right_invoice_str      = strstr($text, $needle1);
                    $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                    $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    $remove_invoice_text    = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                    $text                   = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace("<p>" . $remove_invoice_text, '', $text);
                    $text                   = str_replace($remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace($remove_invoice_text, '', $text);
                    $searchArray            = array(
                        "$needle1",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                    $replaceArray           = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $invoice_order_id       = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    //Get PDF invoice
                    $orderIds               = array();
                    $orders                 = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($invoice_order_id)
                    ))->setPageSize(200);
                
                    foreach ($orders as $key => $value) {
                        $orderIds[] = $value['entity_id'];
                    }
                    $from_shipment = 'order';
                    if (!empty($orderIds)) {
                        $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                        //Attach PDF invoice into email
                        //Option 1      
                        $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                    }
                }
                if (strpos($text, $needle2) !== FALSE) {
                    $pick_pos            = strpos($text, $needle2);
                    $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                    $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                    $right_pick_str      = strstr($text, $needle2);
                    $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                    $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                    $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
                
                    $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                    $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                    $text        = str_replace($remove_pick_text . "</p>", '', $text);
                    $text        = str_replace($remove_pick_text, '', $text);
                    $searchArray = array(
                        "$needle2",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                
                    $replaceArray  = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    
                    //Get PDF Packing sheet
                    $pack_orderIds = array();
                
                    $orderIds    = array();
                    $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($pick_order_id)
                    ))->setPageSize(2);
                    
                    
                
                    foreach ($pack_orders as $key => $value) {
                        $pack_orderIds[] = $value['entity_id'];
                    }
                
                    if (!empty($pack_orderIds)) {
                        $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                        $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                    }
                
                }    
                
            if ($mailDataModel->getIsPlain()) {
                $mail->setBodyText($text);
            } else {
                $mail->setBodyHTML($text);
            }

            $this->setUseAbsoluteLinks(true);

            try {
                
                $mail->send($transport); //add $transport object as parameter
                $this->_mail = null;
            } catch (Exception $e) {
                throw($e);
                return false;
            }
            return true;
        }
    }
}
else
if ($flag == 2) {
    $mandrill_version = (int)Mage::getConfig()->getNode()->modules->Ebizmarts_Mandrill->version;
    if($mandrill_version <2) {
        class Moogento_Pickpack_Model_Core_Email_Templateattach extends Ebizmarts_Mandrill_Model_Email_Template
        {
            public function send2($email, $name = null, array $variables = array())
            {
                $helper = Mage::helper('mandrill');
            
                //Check if should use Mandrill Transactional Email Service
                if (FALSE === $helper->useTransactionalService()) {
                    return parent::send($email, $name, $variables);
                }
            
                if (!$this->isValidForSend()) {
                    Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                    return false;
                }
            
                $emails = array_values((array) $email);
            
                if (count($this->_bcc) > 0) {
                    $bccEmail = $this->_bcc;
                } else {
                    $bccEmail = '';
                }
            
                $names = is_array($name) ? $name : (array) $name;
                $names = array_values($names);
                foreach ($emails as $key => $email) {
                    if (!isset($names[$key])) {
                        $names[$key] = substr($email, 0, strpos($email, '@'));
                    }
                }
            
                $variables['email'] = reset($emails);
                $variables['name']  = reset($names);
            
                $mail = $this->getMail();
            
                $this->setUseAbsoluteLinks(true);
                $text = $this->getProcessedTemplate($variables, true);
            
                $needle1 = "attach_invoice";
                $needle2 = "attach_packingsheet";
                // if ((strpos($text, $needle1) === FALSE) && (strpos($text, $needle2) === FALSE))

                if (strpos($text, $needle1) !== FALSE) {
                    $invoice_pos            = strpos($text, $needle1);
                    $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                    $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                    $right_invoice_str      = strstr($text, $needle1);
                    $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                    $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    $remove_invoice_text    = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                    $text                   = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace("<p>" . $remove_invoice_text, '', $text);
                    $text                   = str_replace($remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace($remove_invoice_text, '', $text);
                    $searchArray            = array(
                        "$needle1",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                    $replaceArray           = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $invoice_order_id       = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    //Get PDF invoice
                    $orderIds               = array();
                    $orders                 = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($invoice_order_id)
                    ))->setPageSize(200);
                
                    foreach ($orders as $key => $value) {
                        $orderIds[] = $value['entity_id'];
                    }
                    $from_shipment = 'order';
                    if (!empty($orderIds)) {
                        $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                        //Attach PDF invoice into email
                        //Option 1      
                        $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                    }
                }
                if (strpos($text, $needle2) !== FALSE) {
                    $pick_pos            = strpos($text, $needle2);
                    $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                    $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                    $right_pick_str      = strstr($text, $needle2);
                    $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                    $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                    $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
                
                    $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                    $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                    $text        = str_replace($remove_pick_text . "</p>", '', $text);
                    $text        = str_replace($remove_pick_text, '', $text);
                    $searchArray = array(
                        "$needle2",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                
                    $replaceArray  = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                
                    //Get PDF Packing sheet
                    $pack_orderIds = array();
                
                    $orderIds    = array();
                    $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($pick_order_id)
                    ))->setPageSize(2);
                
                    foreach ($pack_orders as $key => $value) {
                        $pack_orderIds[] = $value['entity_id'];
                    }
                
                    if (!empty($pack_orderIds)) {
                        $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                    
                        $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                    }
                
                }
            
                try {
                
                    $message = array(
                        'subject' => $this->getProcessedTemplateSubject($variables),
                        'from_name' => $this->getSenderName(),
                        'from_email' => $this->getSenderEmail(),
                        'to_email' => $emails,
                        'to_name' => $names,
                        'bcc_address' => $bccEmail,
                        'headers' => array(
                            'Reply-To' => $this->replyTo
                        )
                    );
                
                    if ($this->isPlain()) {
                        $message['text'] = $text;
                    } else {
                        $message['html'] = $text;
                    }
                    if (isset($variables['tags']) && count($variables['tags'])) {
                        $message['tags'] = $variables['tags'];
                    } else {
                        $templateId = (string) $this->getId();
                        $templates  = parent::getDefaultTemplates();
                        if (isset($templates[$templateId])) {
                            $message['tags'] = array(
                                substr($templates[$templateId]['label'], 0, 50)
                            );
                        } else {
                            if ($this->getTemplateCode()) {
                                $message['tags'] = array(
                                    substr($this->getTemplateCode(), 0, 50)
                                );
                            } else {
                                $message['tags'] = array(
                                    substr($templateId, 0, 50)
                                );
                            }
                        }
                    }
                
                
                
                    $sent = $mail->sendEmail($message);
                    if ($mail->errorCode) {
                        return false;
                    }
                
                }
                catch (Exception $e) {
                    Mage::logException($e);
                    return false;
                }
            
                return true;
            }
        
            public function send3($email, $name = null, array $variables = array())
            {
                $helper = Mage::helper('mandrill');
            
                //Check if should use Mandrill Transactional Email Service
                if (FALSE === $helper->useTransactionalService()) {
                    return parent::send($email, $name, $variables);
                }
            
                if (!$this->isValidForSend()) {
                    Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                    return false;
                }
            
                $emails = array_values((array) $email);
            
                if (count($this->_bcc) > 0) {
                    $bccEmail = $this->_bcc;
                } else {
                    $bccEmail = '';
                }
            
                $names = is_array($name) ? $name : (array) $name;
                $names = array_values($names);
                foreach ($emails as $key => $email) {
                    if (!isset($names[$key])) {
                        $names[$key] = substr($email, 0, strpos($email, '@'));
                    }
                }
            
                $variables['email'] = reset($emails);
                $variables['name']  = reset($names);
            
                $mail = $this->getMail();
            
                $this->setUseAbsoluteLinks(true);
                $text    = $this->getProcessedTemplate($variables, true);
                $needle1 = "attach_invoice";
                $needle2 = "attach_packingsheet";
                // if ((strpos($text, $needle1) === FALSE) && (strpos($text, $needle2) === FALSE))

                if (strpos($text, $needle1) !== FALSE) {
                    $invoice_pos            = strpos($text, $needle1);
                    $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                    $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                    $right_invoice_str      = strstr($text, $needle1);
                    $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                    $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    $remove_invoice_text    = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                    $text                   = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace("<p>" . $remove_invoice_text, '', $text);
                    $text                   = str_replace($remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace($remove_invoice_text, '', $text);
                    $searchArray            = array(
                        "$needle1",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );

                    $replaceArray     = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $invoice_order_id = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    //Get PDF invoice
                    $orderIds         = array();
                    $orders           = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($invoice_order_id)
                    ))->setPageSize(200);
                
                    foreach ($orders as $key => $value) {
                        $orderIds[] = $value['entity_id'];
                    }
                    $from_shipment = 'order';
                    if (!empty($orderIds)) {
                        $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                        //Attach PDF invoice into email
                        //Option 1      
                        $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                    }
                }
                if (strpos($text, $needle2) !== FALSE) {
                    $pick_pos            = strpos($text, $needle2);
                    $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                    $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                    $right_pick_str      = strstr($text, $needle2);
                    $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                    $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                    $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
                
                    $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                    $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                    $text        = str_replace($remove_pick_text . "</p>", '', $text);
                    $text        = str_replace($remove_pick_text, '', $text);
                    $searchArray = array(
                        "$needle2",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                
                    $replaceArray  = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                
                    //Get PDF Packing sheet
                    $pack_orderIds = array();
                
                    $orderIds    = array();
                    $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($pick_order_id)
                    ))->setPageSize(2);
                
                    foreach ($pack_orders as $key => $value) {
                        $pack_orderIds[] = $value['entity_id'];
                    }
                
                    if (!empty($pack_orderIds)) {
                        $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                    
                        $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                    }
                
                }
                try {
                
                    $message = array(
                        'subject' => $this->getProcessedTemplateSubject($variables),
                        'from_name' => $this->getSenderName(),
                        'from_email' => $this->getSenderEmail(),
                        'to_email' => $emails,
                        'to_name' => $names,
                        'bcc_address' => $bccEmail,
                        'headers' => array(
                            'Reply-To' => $this->replyTo
                        )
                    );
                
                    if ($this->isPlain()) {
                        $message['text'] = $text;
                    } else {
                        $message['html'] = $text;
                    }
                    if (isset($variables['tags']) && count($variables['tags'])) {
                        $message['tags'] = $variables['tags'];
                    } else {
                        $templateId = (string) $this->getId();
                        $templates  = parent::getDefaultTemplates();
                        if (isset($templates[$templateId])) {
                            $message['tags'] = array(
                                substr($templates[$templateId]['label'], 0, 50)
                            );
                        } else {
                            if ($this->getTemplateCode()) {
                                $message['tags'] = array(
                                    substr($this->getTemplateCode(), 0, 50)
                                );
                            } else {
                                $message['tags'] = array(
                                    substr($templateId, 0, 50)
                                );
                            }
                        }
                    }
                
                    $sent = $mail->sendEmail($message);
                    if ($mail->errorCode) {
                        return false;
                    }
                
                }
                catch (Exception $e) {
                    Mage::logException($e);
                    return false;
                }
            
                return true;
            }
        
            public function send($email, $name = null, array $variables = array())
            {
                $helper = Mage::helper('mandrill');
            
                //Check if should use Mandrill Transactional Email Service
                if (FALSE === $helper->useTransactionalService()) {
                    return parent::send($email, $name, $variables);
                }
            
                if (!$this->isValidForSend()) {
                    Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                    return false;
                }
            
                $emails = array_values((array) $email);
            
                if (count($this->_bcc) > 0) {
                    $bccEmail = $this->_bcc[0];
                } else {
                    $bccEmail = '';
                }
            
                $names = is_array($name) ? $name : (array) $name;
                $names = array_values($names);
                foreach ($emails as $key => $email) {
                    if (!isset($names[$key])) {
                        $names[$key] = substr($email, 0, strpos($email, '@'));
                    }
                }
            
                $variables['email'] = reset($emails);
                $variables['name']  = reset($names);
            
                $mail = $this->getMail();
            
                $this->setUseAbsoluteLinks(true);
                $text    = $this->getProcessedTemplate($variables, true);
                $needle1 = "attach_invoice";
                $needle2 = "attach_packingsheet";
            
                // if ((strpos($text, $needle1) === FALSE) && (strpos($text, $needle2) === FALSE))

                
                if (strpos($text, $needle1) !== FALSE) {
                    $invoice_pos            = strpos($text, $needle1);
                    $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                    $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                    $right_invoice_str      = strstr($text, $needle1);
                    $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                    $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    $remove_invoice_text    = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                    $text                   = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace("<p>" . $remove_invoice_text, '', $text);
                    $text                   = str_replace($remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace($remove_invoice_text, '', $text);
                    $searchArray            = array(
                        "$needle1",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );

                    $replaceArray     = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $invoice_order_id = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    //Get PDF invoice
                    $orderIds         = array();
                    $orders           = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($invoice_order_id)
                    ))->setPageSize(200);
                
                    foreach ($orders as $key => $value) {
                        $orderIds[] = $value['entity_id'];
                    }
                    $from_shipment = 'order';
                    if (!empty($orderIds)) {
                        $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                        //Attach PDF invoice into email
                        //Option 1      
                        $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                    }
                }
            
            
                if (strpos($text, $needle2) !== FALSE) {
                    $pick_pos            = strpos($text, $needle2);
                    $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                    $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                    $right_pick_str      = strstr($text, $needle2);
                    $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                    $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                    $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
                
                    $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                    $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                    $text        = str_replace($remove_pick_text . "</p>", '', $text);
                    $text        = str_replace($remove_pick_text, '', $text);
                    $searchArray = array(
                        "$needle2",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                
                    $replaceArray  = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                
                    //Get PDF Packing sheet
                    $pack_orderIds = array();
                
                    $orderIds    = array();
                    $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($pick_order_id)
                    ))->setPageSize(2);
                
                    foreach ($pack_orders as $key => $value) {
                        $pack_orderIds[] = $value['entity_id'];
                    }
                
                    if (!empty($pack_orderIds)) {
                        $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                        $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                    }
                
                }
                try {
                
                    $message = array(
                        'subject' => $this->getProcessedTemplateSubject($variables),
                        'from_name' => $this->getSenderName(),
                        'from_email' => $this->getSenderEmail(),
                        'to_email' => $emails,
                        'to_name' => $names,
                        'bcc_address' => $bccEmail,
                        'headers' => array(
                            'Reply-To' => $this->replyTo
                        )
                    );
                
                    if ($this->isPlain()) {
                        $message['text'] = $text;
                    } else {
                        $message['html'] = $text;
                    }
                
                    $tTags = $this->_getTemplateTags();
                    if (!empty($tTags)) {
                        $message['tags'] = $tTags;
                    }
                
                    $sent = $mail->sendEmail($message);
                    if ($mail->errorCode) {
                        return false;
                    }
                
                }
                catch (Exception $e) {
                
                    try {
                        $this->send3($email, $name, $variables);
                    }
                    catch (Exception $e) {
                        try {
                            parent::send($email, $name, $variables);
                        }
                        catch (Exception $e) {
                            Mage::logException($e);
                            return false;
                        }
                    }
                }
            
                return true;
            }
        
        }
    }
    else
    {
        class Moogento_Pickpack_Model_Core_Email_Templateattach extends Ebizmarts_Mandrill_Model_Email_Template
        {
            public function send($email, $name = null, array $variables = array())
            {
                $storeId = Mage::app()->getStore()->getId();
                if(!Mage::getStoreConfig(Ebizmarts_Mandrill_Model_System_Config::ENABLE,$storeId)) {
                   return parent::send($email, $name,$variables);
                }
                if (!$this->isValidForSend()) {
                    Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                    return false;
                }
                $emails = array_values( (array)$email );
                $names = is_array( $name ) ? $name : (array)$name;
                $names = array_values( $names );
                foreach ( $emails as $key => $email ) {
                    if ( ! isset( $names[$key] ) ) {
                        $names[ $key ] = substr( $email, 0, strpos( $email, '@' ) );
                    }
                }

                // Get message
                $this->setUseAbsoluteLinks( true );
                $variables['email'] = reset( $emails );
                $variables['name'] = reset( $names );
                $message = $this->getProcessedTemplate( $variables, true );

                $email = array( 'subject' => $this->getProcessedTemplateSubject( $variables ), 'to' => array() );

                $mail = $this->getMail();
                try{
                $text    = $message;
                $needle1 = "attach_invoice";
                $needle2 = "attach_packingsheet";
                
                if (strpos($text, $needle1) !== FALSE) {
                $invoice_pos            = strpos($text, $needle1);
                $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                $right_invoice_str      = strstr($text, $needle1);
                $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                $remove_invoice_text    = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                $text                   = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                $text                   = str_replace("<p>" . $remove_invoice_text, '', $text);
                $text                   = str_replace($remove_invoice_text . "</p>", '', $text);
                $text                   = str_replace($remove_invoice_text, '', $text);
                $searchArray            = array(
                    "$needle1",
                    "{{",
                    "}}",
                    "(",
                    ")"
                );

                $replaceArray     = array(
                    "",
                    "",
                    "",
                    "",
                    ""
                );
                $invoice_order_id = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                $text        = str_replace($pickpack_email_str, '', $text);
                //Get PDF invoice
                $orderIds         = array();
                $orders           = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                    'in' => trim($invoice_order_id)
                ))->setPageSize(200);
            
                foreach ($orders as $key => $value) {
                    $orderIds[] = $value['entity_id'];
                }
                $from_shipment = 'order';
                if (!empty($orderIds)) {
                    $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                    //Attach PDF invoice into email
                    //Option 1      
                    $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                }
            }
                if (strpos($text, $needle2) !== FALSE) {
                $pick_pos            = strpos($text, $needle2);
                $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                $right_pick_str      = strstr($text, $needle2);
                $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
            
                $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                $text        = str_replace($remove_pick_text . "</p>", '', $text);
                $text        = str_replace($remove_pick_text, '', $text);
                $searchArray = array(
                    "$needle2",
                    "{{",
                    "}}",
                    "(",
                    ")"
                );
            
                $replaceArray  = array(
                    "",
                    "",
                    "",
                    "",
                    ""
                );
                $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
            
                //Get PDF Packing sheet
                $pack_orderIds = array();
            
                $orderIds    = array();
                $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                    'in' => trim($pick_order_id)
                ))->setPageSize(2);
            
                foreach ($pack_orders as $key => $value) {
                    $pack_orderIds[] = $value['entity_id'];
                }
            
                if (!empty($pack_orderIds)) {
                    $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                    $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                }
            
            }
                }
                catch(Exception $e)
                {
                    Mage::log($e->getMessage(), null, 'moogento_pickpack_mail.log');
                }
                
                for ( $i = 0; $i < count( $emails ); $i++ ) {
                    if ( isset( $names[ $i ] ) ) {
                        $email['to'][] = array(
                            'email' => $emails[ $i ],
                            'name' => $names[ $i ]
                        );
                    }
                    else {
                        $email['to'][] = array(
                            'email' => $emails[ $i ],
                            'name' => ''
                        );
                    }
                }
                foreach($mail->getBcc() as $bcc)
                {
                    $email['to'][] = array(
                        'email' => $bcc,
                        'type' => 'bcc'
                    );
                }

                $email['from_name'] = $this->getSenderName();
                $email['from_email'] = $this->getSenderEmail();
                $email['headers'] = $mail->getHeaders();
                
                if(isset($variables['tags']) && count($variables['tags'])) {
                    $email ['tags'] = $variables['tags'];
                }

                if(isset($variables['tags']) && count($variables['tags'])) {
                    $email ['tags'] = $variables['tags'];
                }
                else {
                    $templateId = (string)$this->getId();
                    $templates = parent::getDefaultTemplates();
                    if (isset($templates[$templateId])) {
                        $email ['tags'] =  array(substr($templates[$templateId]['label'], 0, 50));
                    } else {
                        if($this->getTemplateCode()){
                            $email ['tags'] = array(substr($this->getTemplateCode(), 0, 50));
                        } else {
                            $email ['tags'] = array(substr($templateId, 0, 50));
                        }
                    }
                }

                if($att = $mail->getAttachments()) {
                    $email['attachments'] = $att;
                }
                if( $this->isPlain() )
                    $email['text'] = $message;
                else
                    $email['html'] = $message;

                try {
                    $result = $mail->messages->send( $email );
                }
                catch( Exception $e ) {
                    Mage::logException( $e );
                    return false;
                }
                return true;

            }
        }
    }
        
} 
else 
if(!(class_exists("Moogento_Pickpack_Model_Core_Email_Templateattach")))
{
    class Moogento_Pickpack_Model_Core_Email_Templateattach extends Mage_Core_Model_Email_Template
    {
        /**
         * Configuration path for default email templates
         */
        const XML_PATH_TEMPLATE_EMAIL = 'global/template/email';
        const XML_PATH_SENDING_SET_RETURN_PATH = 'system/smtp/set_return_path';
        const XML_PATH_SENDING_RETURN_PATH_EMAIL = 'system/smtp/return_path_email';
        const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';
        const XML_PATH_DESIGN_EMAIL_LOGO_ALT = 'design/email/logo_alt';
        
        protected $_templateFilter;
        protected $_preprocessFlag = false;
        protected $_mail;
        
        static protected $_defaultTemplates;
        
        /**
         * Initialize email template model
         *
         */
        public function send($email, $name = null, array $variables = array())
        {
             $flag = 0;
            if (Mage::helper('pickpack')->isInstalled('Aschroder_SMTPPro')) {
                if (Mage::helper('core')->isModuleEnabled('smtppro')) {
                    $flag = 1;
                }
                if (Mage::helper('core')->isModuleEnabled('Aschroder_SMTPPro')) {
                    $flag = 1;
                }
                if (Mage::getStoreConfig('system/smtppro/option') != "disabled") {
                    $flag = 1;
                }
            }
            //
            if (Mage::helper('pickpack')->isInstalled('Aschroder_Email')) {
                if (Mage::helper('core')->isModuleEnabled('aschroder_email')) {
                    $flag = 5;
                }
                if (Mage::helper('core')->isModuleEnabled('Aschroder_Email')) {
                    $flag = 5;
                }
                if (Mage::getStoreConfig('system/aschroder_email/option') != "disabled") {
                    $flag = 5;
                }
            }        

            if ($flag == 5)
            {
                $_helper = Mage::helper('aschroder_email');
                // If it's not enabled, just return the parent result.
                if (!$_helper->isEnabled()) {
                    $_helper->log('MageSend is not enabled, fall back to parent class');
                    return parent::send($email, $name, $variables);
                }


                // As per parent class - except addition of before and after send events

                if (!$this->isValidForSend()) {
                    $_helper->log('Email is not valid for sending, this is a core error that often means there\'s a problem with your email templates.');
                    Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                    return false;
                }

                $emails = array_values((array)$email);
                $names = is_array($name) ? $name : (array)$name;
                $names = array_values($names);
                foreach ($emails as $key => $email) {
                    if (!isset($names[$key])) {
                        $names[$key] = substr($email, 0, strpos($email, '@'));
                    }
                }

                $variables['email'] = reset($emails);
                $variables['name'] = reset($names);

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
                    $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
                    Zend_Mail::setDefaultTransport($mailTransport);
                }

                foreach ($emails as $key => $email) {
                    $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
                }

                $this->setUseAbsoluteLinks(true);
                $text = $this->getProcessedTemplate($variables, true);

                /*****MOOGENTO EMAIL ATTACHMENT******/
                    $needle1 = "attach_invoice";
                    $needle2 = "attach_packingsheet";
                    
                   
                    if (strpos($text, $needle1) !== FALSE) {
                        $invoice_pos            = strpos($text, $needle1);
                        $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                        $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                        $right_invoice_str      = strstr($text, $needle1);
                        $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                        $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    
                        $remove_invoice_text = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                        $text                = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                        $text                = str_replace("<p>" . $remove_invoice_text, '', $text);
                        $text                = str_replace($remove_invoice_text . "</p>", '', $text);
                        $text                = str_replace($remove_invoice_text, '', $text);
                        $searchArray         = array(
                            "$needle1",
                            "{{",
                            "}}",
                            "(",
                            ")"
                        );
                        $replaceArray        = array(
                            "",
                            "",
                            "",
                            "",
                            ""
                        );
                        $invoice_order_id    = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                        $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                        $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                        $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                        $text        = str_replace($pickpack_email_str, '', $text);
                        //Get PDF invoice
                        $orderIds            = array();
                        $orders              = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                            'in' => trim($invoice_order_id)
                        ))->setPageSize(200);
                    
                        foreach ($orders as $key => $value) {
                            $orderIds[] = $value['entity_id'];
                        }
                        $from_shipment = 'order';
                        if (!empty($orderIds)) {
                            $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                            $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                        }
                    }
                    if (strpos($text, $needle2) !== FALSE) {
                        $pick_pos            = strpos($text, $needle2);
                        $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                        $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                        $right_pick_str      = strstr($text, $needle2);
                        $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                        $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                        $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
                    
                        $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                        $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                        $text        = str_replace($remove_pick_text . "</p>", '', $text);
                        $text        = str_replace($remove_pick_text, '', $text);
                        $searchArray = array(
                            "$needle2",
                            "{{",
                            "}}",
                            "(",
                            ")"
                        );
                    
                        $replaceArray  = array(
                            "",
                            "",
                            "",
                            "",
                            ""
                        );
                        $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    
                        //Get PDF Packing sheet
                        $pack_orderIds = array();
                    
                        $orderIds    = array();
                        $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                            'in' => trim($pick_order_id)
                        ))->setPageSize(2);
                    
                        foreach ($pack_orders as $key => $value) {
                            $pack_orderIds[] = $value['entity_id'];
                        }
                    
                        if (!empty($pack_orderIds)) {
                            $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                            $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                        }
                    
                    }
                    /*****************END EMAIL ATTACHMENT**********/
                    
                if($this->isPlain()) {
                    $mail->setBodyText($text);
                } else {
                    $mail->setBodyHTML($text);
                }

                $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
                $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

                try {

                    $transport = new Varien_Object();
                    Mage::dispatchEvent('aschroder_email_template_before_send', array(
                        'mail' => $mail,
                        'template' => $this,
                        'variables' => $variables,
                        'transport' => $transport
                    ));

                    if ($transport->getTransport()) { // if set by an observer, use it
                        $mail->send($transport->getTransport());
                    } else {
                        $mail->send();
                    }

                    foreach ($emails as $key => $email) {
                        Mage::dispatchEvent('aschroder_email_after_send', array(
                            'to' => $email,
                            'template' => $this->getTemplateId(),
                            'subject' => $this->getProcessedTemplateSubject($variables),
                            'html' => !$this->isPlain(),
                            'email_body' => $text));
                    }

                    $this->_mail = null;
                }
                catch (Exception $e) {
                    $this->_mail = null;
                    Mage::logException($e);
                    return false;
                }

                return true;
            }
            else
            //Override SMPT Pro send mail function
            if  ($flag == 1) {
                try
                {
                    Mage::log('SMTPPro is enabled, sending email in Aschroder_SMTPPro_Model_Email_Template');
                    if (!$this->isValidForSend()) {
                        Mage::log('SMTPPro: Email not valid for sending - check template, and smtp enabled/disabled setting');
                        Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                        return false;
                    }
                
                    $emails = array_values((array) $email);
                    $names  = is_array($name) ? $name : (array) $name;
                    $names  = array_values($names);
                    foreach ($emails as $key => $email) {
                        if (!isset($names[$key])) {
                            $names[$key] = substr($email, 0, strpos($email, '@'));
                        }
                    }
                
                    $variables['email'] = reset($emails);
                    $variables['name']  = reset($names);
                
                    $mail = $this->getMail();
                    $smtp_helper = Mage::helper('smtppro');
                    if(method_exists($smtp_helper,'getDevelopmentMode'))
                    {
                        $dev = $smtp_helper->getDevelopmentMode();
                
                        if ($dev == "contact") {
                    
                            $email = Mage::getStoreConfig('contacts/email/recipient_email', $this->getDesignConfig()->getStore());
                            Mage::log("Development mode set to send all emails to contact form recipient: " . $email);
                    
                        } elseif ($dev == "supress") {
                    
                            Mage::log("Development mode set to supress all emails.");
                            # we bail out, but report success
                            return true;
                        }
                    }
                
                    // In Magento core they set the Return-Path here, for the sendmail command.
                    // we assume our outbound SMTP server (or Gmail) will set that.
                
                    foreach ($emails as $key => $email) {
                        $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
                    }
                
                
                    $this->setUseAbsoluteLinks(true);
                    $text = $this->getProcessedTemplate($variables, true);
                    /*****MOOGENTO EMAIL ATTACHMENT******/
                    $needle1 = "attach_invoice";
                    $needle2 = "attach_packingsheet";
                    
                   
                    if (strpos($text, $needle1) !== FALSE) {
                        $invoice_pos            = strpos($text, $needle1);
                        $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                        $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                        $right_invoice_str      = strstr($text, $needle1);
                        $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                        $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    
                        $remove_invoice_text = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                        $text                = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                        $text                = str_replace("<p>" . $remove_invoice_text, '', $text);
                        $text                = str_replace($remove_invoice_text . "</p>", '', $text);
                        $text                = str_replace($remove_invoice_text, '', $text);
                        $searchArray         = array(
                            "$needle1",
                            "{{",
                            "}}",
                            "(",
                            ")"
                        );
                        $replaceArray        = array(
                            "",
                            "",
                            "",
                            "",
                            ""
                        );
                        $invoice_order_id    = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                        $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                        $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                        $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                        $text        = str_replace($pickpack_email_str, '', $text);
                        //Get PDF invoice
                        $orderIds            = array();
                        $orders              = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                            'in' => trim($invoice_order_id)
                        ))->setPageSize(200);
                    
                        foreach ($orders as $key => $value) {
                            $orderIds[] = $value['entity_id'];
                        }
                        $from_shipment = 'order';
                        if (!empty($orderIds)) {
                            $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                            //Attach PDF invoice into email
                            //Option 1      
                            $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                        }
                        // //Option 2
                        //             createAttachment(
                        //              file_get_contents('temp/temp.csv'),
                        //              Zend_Mime::TYPE_OCTETSTREAM,
                        //              Zend_Mime::DISPOSITION_ATTACHMENT,
                        //              Zend_Mime::ENCODING_BASE64,
                        //              'file.csv'
                        //          );
                    
                    
                    }
                    if (strpos($text, $needle2) !== FALSE) {
                        $pick_pos            = strpos($text, $needle2);
                        $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                        $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                        $right_pick_str      = strstr($text, $needle2);
                        $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                        $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                        $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
                    
                        $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                        $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                        $text        = str_replace($remove_pick_text . "</p>", '', $text);
                        $text        = str_replace($remove_pick_text, '', $text);
                        $searchArray = array(
                            "$needle2",
                            "{{",
                            "}}",
                            "(",
                            ")"
                        );
                    
                        $replaceArray  = array(
                            "",
                            "",
                            "",
                            "",
                            ""
                        );
                        $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    
                        //Get PDF Packing sheet
                        $pack_orderIds = array();
                    
                        $orderIds    = array();
                        $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                            'in' => trim($pick_order_id)
                        ))->setPageSize(2);
                    
                        foreach ($pack_orders as $key => $value) {
                            $pack_orderIds[] = $value['entity_id'];
                        }
                    
                        if (!empty($pack_orderIds)) {
                            $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                            $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                        }
                    
                    }
                    /*****************END EMAIL ATTACHMENT**********/
                
                    if ($this->isPlain()) {
                        $mail->setBodyText($text);
                    } else {
                        $mail->setBodyHTML($text);
                    }
                
                    $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
                    $mail->setFrom($this->getSenderEmail(), $this->getSenderName());
                    
                    if(method_exists($smtp_helper,'isReplyToStoreEmail'))
                    {
                        if ($smtp_helper->isReplyToStoreEmail() && !array_key_exists('Reply-To', $mail->getHeaders())) {
                    
                            // Patch for Zend upgrade
                            // Later versions of Zend have a method for this, and disallow direct header setting...
                            if (method_exists($mail, "setReplyTo")) {
                                $mail->setReplyTo($this->getSenderEmail(), $this->getSenderName());
                            } else {
                                $mail->addHeader('Reply-To', $this->getSenderEmail());
                            }
                            Mage::log('ReplyToStoreEmail is enabled, just set Reply-To header: ' . $this->getSenderEmail());
                        }
                    
                    }
                
                    $transport = Mage::helper('smtppro')->getTransport($this->getDesignConfig()->getStore());
                
                    try {
                    
                        Mage::log('About to send email');
                        $mail->send($transport); // Zend_Mail warning..
                        Mage::log('Finished sending email');
                    
                        // Record one email for each receipient
                        foreach ($emails as $key => $email) {
                            $smtp_helper = Mage::helper('smtppro');
                            // Mage::dispatchEvent('smtppro_email_after_send', array(
                            if(method_exists($smtp_helper,'logEmailSent'))
                                Mage::helper('smtppro')->logEmailSent($email,$this->getTemplateId(),$this->getProcessedTemplateSubject($variables),$text,!$this->isPlain());
                            else
                                if(method_exists($smtp_helper,'log'))
                                    Mage::helper('smtppro')->log($email,$this->getTemplateId(),$this->getProcessedTemplateSubject($variables),$text,!$this->isPlain());
                            //Mage::helper('smtppro')->logEmailSent($email,$this->getTemplateId(),$this->getProcessedTemplateSubject($variables),$text,!$this->isPlain());
                        }
                    
                        $this->_mail = null;
                    }
                    catch (Exception $e) {
                        Mage::logException($e);
                        return false;
                    }
                
                    return true;
                }
                catch(Exception $e)
                {
                    try{
                        $_helper = Mage::helper('smtppro');
                        // If it's not enabled, just return the parent result.
                        if (!$_helper->isEnabled()) {
                            $_helper->log('SMTP Pro is not enabled, fall back to parent class');
                            return parent::send($email, $name, $variables);
                        }


                        // As per parent class - except addition of before and after send events

                        if (!$this->isValidForSend()) {
                            $_helper->log('Email is not valid for sending, this is a core error that often means there\'s a problem with your email templates.');
                            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                            return false;
                        }

                        $emails = array_values((array)$email);
                        $names = is_array($name) ? $name : (array)$name;
                        $names = array_values($names);
                        foreach ($emails as $key => $email) {
                            if (!isset($names[$key])) {
                                $names[$key] = substr($email, 0, strpos($email, '@'));
                            }
                        }

                        $variables['email'] = reset($emails);
                        $variables['name'] = reset($names);

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
                            $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
                            Zend_Mail::setDefaultTransport($mailTransport);
                        }

                        foreach ($emails as $key => $email) {
                            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
                        }

                        $this->setUseAbsoluteLinks(true);
                        $text = $this->getProcessedTemplate($variables, true);
                        /*****MOOGENTO EMAIL ATTACHMENT******/
                    $needle1 = "attach_invoice";
                    $needle2 = "attach_packingsheet";
                    
                    if ((strpos($text, $needle1) === FALSE) && (strpos($text, $needle2) === FALSE))
                    {   
                        return parent::send($email, $name, $variables);
                    }
                    if (strpos($text, $needle1) !== FALSE) {
                        $invoice_pos            = strpos($text, $needle1);
                        $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                        $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                        $right_invoice_str      = strstr($text, $needle1);
                        $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                        $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    
                        $remove_invoice_text = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                        $text                = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                        $text                = str_replace("<p>" . $remove_invoice_text, '', $text);
                        $text                = str_replace($remove_invoice_text . "</p>", '', $text);
                        $text                = str_replace($remove_invoice_text, '', $text);
                        $searchArray         = array(
                            "$needle1",
                            "{{",
                            "}}",
                            "(",
                            ")"
                        );
                        $replaceArray        = array(
                            "",
                            "",
                            "",
                            "",
                            ""
                        );
                        $invoice_order_id    = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                        $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                        $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                        $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                        $text        = str_replace($pickpack_email_str, '', $text);
                        //Get PDF invoice
                        $orderIds            = array();
                        $orders              = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                            'in' => trim($invoice_order_id)
                        ))->setPageSize(200);
                    
                        foreach ($orders as $key => $value) {
                            $orderIds[] = $value['entity_id'];
                        }
                        $from_shipment = 'order';
                        if (!empty($orderIds)) {
                            $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                            $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                        }
                    }
                    if (strpos($text, $needle2) !== FALSE) {
                        $pick_pos            = strpos($text, $needle2);
                        $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                        $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                        $right_pick_str      = strstr($text, $needle2);
                        $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                        $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                        $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
                    
                        $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                        $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                        $text        = str_replace($remove_pick_text . "</p>", '', $text);
                        $text        = str_replace($remove_pick_text, '', $text);
                        $searchArray = array(
                            "$needle2",
                            "{{",
                            "}}",
                            "(",
                            ")"
                        );
                    
                        $replaceArray  = array(
                            "",
                            "",
                            "",
                            "",
                            ""
                        );
                        $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    
                        //Get PDF Packing sheet
                        $pack_orderIds = array();
                    
                        $orderIds    = array();
                        $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                            'in' => trim($pick_order_id)
                        ))->setPageSize(2);
                    
                        foreach ($pack_orders as $key => $value) {
                            $pack_orderIds[] = $value['entity_id'];
                        }
                    
                        if (!empty($pack_orderIds)) {
                            $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                            $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                        }
                    
                    }
                    /*****************END EMAIL ATTACHMENT**********/
                        if($this->isPlain()) {
                            $mail->setBodyText($text);
                        } else {
                            $mail->setBodyHTML($text);
                        }

                        $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
                        $mail->setFrom($this->getSenderEmail(), $this->getSenderName());

                        try {

                            $transport = new Varien_Object();
                            Mage::dispatchEvent('aschroder_smtppro_template_before_send', array(
                                'mail' => $mail,
                                'template' => $this,
                                'variables' => $variables,
                                'transport' => $transport
                            ));

                            if ($transport->getTransport()) { // if set by an observer, use it
                                $mail->send($transport->getTransport());
                            } else {
                                $mail->send();
                            }

                            foreach ($emails as $key => $email) {
                                $smtp_helper = Mage::helper('smtppro');
                                // Mage::dispatchEvent('smtppro_email_after_send', array(
                                if(method_exists($smtp_helper,'logEmailSent'))
                                    Mage::helper('smtppro')->logEmailSent($email,$this->getTemplateId(),$this->getProcessedTemplateSubject($variables),$text,!$this->isPlain());
                                else
                                    if(method_exists($smtp_helper,'log'))
                                        Mage::helper('smtppro')->log($email,$this->getTemplateId(),$this->getProcessedTemplateSubject($variables),$text,!$this->isPlain());
                                //Mage::helper('smtppro')->logEmailSent($email,$this->getTemplateId(),$this->getProcessedTemplateSubject($variables),$text,!$this->isPlain());
                            }
 
                            $this->_mail = null;
                        }
                        catch (Exception $e) {
                            $this->_mail = null;
                            Mage::logException($e);
                            return false;
                        }

                        return true;
                    }
                    catch(Exception $e)
                    {   
                        Mage::logException($e);
                    }
                }
            }
            //end override SMPT Pro send mail function
            else if ($flag == 0) {
                $emails = array_values((array) $email);
                $names  = is_array($name) ? $name : (array) $name;
                $names  = array_values($names);
                foreach ($emails as $key => $email) {
                    if (!isset($names[$key])) {
                        $names[$key] = substr($email, 0, strpos($email, '@'));
                    }
                }
                
                $variables['email'] = reset($emails);
                $variables['name']  = reset($names);
                
                @ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
                @ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));
                
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
                    $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $returnPathEmail);
                    Zend_Mail::setDefaultTransport($mailTransport);
                }
                
                foreach ($emails as $key => $email) {
                    $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
                }
                
                $this->setUseAbsoluteLinks(true);
                $text = $this->getProcessedTemplate($variables, true);
                
                $needle1 = "attach_invoice";
                $needle2 = "attach_packingsheet";
                
                if ((strpos($text, $needle1) === FALSE) && (strpos($text, $needle2) === FALSE))
                {
                    return parent::send($email, $name, $variables);
                }
                
                if (strpos($text, $needle1) !== FALSE) {
                    $invoice_pos            = strpos($text, $needle1);
                    $left_invoice_str       = substr($text, 0, $invoice_pos); //strstr($text, $needle1,true);
                    $invoice_left_pos       = strrpos($left_invoice_str, '{{'); //get the last {{ pos from left substring
                    $right_invoice_str      = strstr($text, $needle1);
                    $right_invoice_pos      = strpos($right_invoice_str, '}}'); //Get the first }} from right substring
                    $real_right_invoice_pos = strlen($left_invoice_str) + $right_invoice_pos + 1;
                    $remove_invoice_text    = substr($text, $invoice_left_pos, $real_right_invoice_pos - $invoice_left_pos + 1);
                    $text                   = str_replace("<p>" . $remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace("<p>" . $remove_invoice_text, '', $text);
                    $text                   = str_replace($remove_invoice_text . "</p>", '', $text);
                    $text                   = str_replace($remove_invoice_text, '', $text);
                    $searchArray            = array(
                        "$needle1",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                    $replaceArray           = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );
                    $invoice_order_id       = str_replace($searchArray, $replaceArray, $remove_invoice_text);
                    $invoice_order_id        = str_replace('pickpack_', '', $invoice_order_id);                    
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    //Get PDF invoice
                    $orderIds               = array();
                    $orders                 = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($invoice_order_id)
                    ))->setPageSize(200);
                    
                    foreach ($orders as $key => $value) {
                        $orderIds[] = $value['entity_id'];
                    }
                    $from_shipment = 'order';
                    if (!empty($orderIds)) {
                        $pdf_invoice = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($orderIds, $from_shipment, 'invoice');
                        $mail->createAttachment($pdf_invoice->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Invoice_' . $invoice_order_id . '.pdf');
                    }
                }
                
                if (strpos($text, $needle2) !== FALSE) {
                    $pick_pos            = strpos($text, $needle2);
                    $left_pick_str       = substr($text, 0, $pick_pos); //strstr($text, $needle1,true);
                    $pick_left_pos       = strrpos($left_pick_str, '{{'); //get the last {{ pos from left substring
                    $right_pick_str      = strstr($text, $needle2);
                    $right_pick_pos      = strpos($right_pick_str, '}}'); //Get the first }} from right substring
                    $real_right_pick_pos = strlen($left_pick_str) + $right_pick_pos + 1;
                    $remove_pick_text    = substr($text, $pick_left_pos, $real_right_pick_pos - $pick_left_pos + 2);
                    
                    $text        = str_replace("<p>" . $remove_pick_text . "</p>", '', $text);
                    $text        = str_replace("<p>" . $remove_pick_text, '', $text);
                    $text        = str_replace($remove_pick_text . "</p>", '', $text);
                    $text        = str_replace($remove_pick_text, '', $text);
                    $searchArray = array(
                        "$needle2",
                        "{{",
                        "}}",
                        "(",
                        ")"
                    );
                    
                    $replaceArray  = array(
                        "",
                        "",
                        "",
                        "",
                        ""
                    );

                    $pick_order_id = str_replace($searchArray, $replaceArray, $remove_pick_text);
                    $pick_order_id        = str_replace('pickpack_', '', $pick_order_id);                    
                    $pickpack_email_left_pos       = strrpos($text, '<pickpack_email>');
                    $pickpack_email_right_pos       = strrpos($text, '</pickpack_email>');
                    $pickpack_email_str = substr($text,$pickpack_email_left_pos,$pickpack_email_right_pos);
                    $text        = str_replace($pickpack_email_str, '', $text);
                    
                    //Get PDF Packing sheet
                    $pack_orderIds = array();
                    
                    $orderIds    = array();
                    $pack_orders = Mage::getModel('sales/order')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('increment_id', array(
                        'in' => trim($pick_order_id)
                    ))->setPageSize(2);
                    
                    foreach ($pack_orders as $key => $value) {
                        $pack_orderIds[] = $value['entity_id'];
                    }
                    
                    if (!empty($pack_orderIds)) {
                        $pdf_pack   = Mage::getModel('pickpack/sales_order_pdf_invoices_default')->getPdfDefault($pack_orderIds, 'order', 'pack');
                        $mail->createAttachment($pdf_pack->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'Packingsheet_' . $pick_order_id . '.pdf');
                    }
                    
                }
                
                if ($this->isPlain()) {
                    $mail->setBodyText($text);
                } else {
                    $mail->setBodyHTML($text);
                }
                
                $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
                $mail->setFrom($this->getSenderEmail(), $this->getSenderName());
                try {
                    if (!$this->isValidForSend()) {
                        Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
                        return false;
                    }
                    $mail->send();
                    $this->_mail = null;
                }
                catch (Exception $e) {
                    $this->_mail = null;
                    Mage::logException($e);
                    return false;
                }
                
                return true;
            }
            
            /*
            TODO check for version 1.6
            else
            {
            //                        
            if (!$this->isValidForSend()) {
            Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted
            return false;
            }
            $email[0]='david@moogento.com';
            $emails = array_values((array)$email);
            $names = is_array($name) ? $name : (array)$name;
            $names = array_values($names);
            foreach ($emails as $key => $email) {
            if (!isset($names[$key])) {
            $names[$key] = substr($email, 0, strpos($email, '@'));
            }
            }
            
            $variables['email'] = reset($emails);
            $variables['name'] = reset($names);
            
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
            $mailTransport = new Zend_Mail_Transport_Sendmail("-f".$returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
            }
            
            foreach ($emails as $key => $email) {
            $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
            }
            
            $this->setUseAbsoluteLinks(true);
            $text = $this->getProcessedTemplate($variables, true);
            
            if($this->isPlain()) {
            $mail->setBodyText($text);
            } else {
            $mail->setBodyHTML($text);
            }
            
            $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=');
            $mail->setFrom($this->getSenderEmail(), $this->getSenderName());
            
            try {
            $mail->send();
            $this->_mail = null;
            }
            catch (Exception $e) {
            $this->_mail = null;
            Mage::logException($e);
            return false;
            }
            
            return true;
            }
            */
            
        }
    }
}

?>