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
* File        Abstract.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Sales_Order_Processor_Abstract extends Varien_Object
{
    protected $_configGroupPrefix = '';

    protected $_hiddenOrderFlag = '';
    
    protected $_flagColumn = '';

    protected $_fieldPrefix  = 'auto_processing';
    
    protected $_filePath = 'auto_PickPack';
    
    protected $auto_save_folder = 'autoProcessing';

    protected $_orderCollection = array();
    protected $_itemsCollection = array();
    protected $_productsCollection = array();

    protected $_totalItemsPerOrder = array();   

    protected $store_list = array();
    protected $product_list = '';   
    protected $product_list_arr = array();

    //Total ordered of each product
    protected $product_ordered = array();
    //Number of ordered of each product per order
    protected $product_ordered_per_order = array();
    
    protected $order_list_of_product = array();
    protected $order_list_of_product_per_option = array();

    protected $order_model_list = array();

    protected $product_model_list_arr = array();
    protected $product_order_list_arr = array();    
    protected $item_model_list_arr = array();


    public function getPdf($orderIds, $storeId = 0) {
        return $this;
    }

    protected function _getFileName($orderIds) {
        return $this;
    }

    protected function _getConfig($key, $storeId = 0) {
        return Mage::getStoreConfig("pickpack_options/{$this->_configGroupPrefix}/{$key}", $storeId);
    }

    protected function _getConfigFlag($key, $storeId = 0) {
        return Mage::getStoreConfigFlag("pickpack_options/{$this->_configGroupPrefix}/{$key}", $storeId);
    }
    
    protected function _sendAnEmail_old($pdf, $fileName, $storeId = 0) {
        $sender = $this->_getConfig("auto_processing_sender", $storeId);
        $sendTo = $this->_getConfig("auto_processing_send_to", $storeId);
        $subject = $this->_getConfig("auto_processing_subject", $storeId);
        $body = $this->_getConfig("auto_processing_body", $storeId);

        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setTemplateSubject($subject);
        $mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $sender . '/name'));
        $mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $sender . '/email'));
        $mailTemplate->setTemplateText($body);
        $mailTemplate->getTemplateType(Mage_Core_Model_Email_Template::TYPE_TEXT);

        $at = new Zend_Mime_Part($pdf->render());
        $at->type = 'application/pdf';
        $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $at->encoding = Zend_Mime::ENCODING_BASE64;
        $at->filename = $fileName;
        $mailTemplate->getMail()->addAttachment($at);

        $mailTemplate->send($sendTo);

        return $this;
    }

    
    public function _sendAnEmail_work_version1($pdf, $fileName, $storeId = 0) {
    	try{
            $mail = new Zend_Mail();
        $sender = $this->_getConfig("auto_processing_sender", $storeId);
            $senderName = Mage::getStoreConfig('trans_email/ident_' . $sender . '/name');
			$senderEmail= Mage::getStoreConfig('trans_email/ident_' . $sender . '/email');
			
        $sendTo = $this->_getConfig("auto_processing_send_to", $storeId);
        $subject = $this->_getConfig("auto_processing_subject", $storeId);
        $body = $this->_getConfig("auto_processing_body", $storeId);
						
            $mail->setFrom($senderEmail,$senderName);
            $mail->addTo($sendTo,"Receiver");
            $mail->setSubject($subject);
            $mail->setBodyHtml($body); // here u also use setBodyText options.
 
            // this is for to set the file format
            $at = new Zend_Mime_Part($pdf->render());
 
            $at->type        = 'application/pdf'; // if u have PDF then it would like -> 'application/pdf'
            $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $at->encoding    =  Zend_Mime::ENCODING_BASE64;
            $at->filename    = $fileName;
            $mail->addAttachment($at);
            $mail->send();
            Mage::log('Sent mail to '.$sendTo, null, 'moogento_pickpack.log');
 
        }catch(Exception $e)
        {
            Mage::log($e->getMessage(),null, 'moogento_pickpack.log');
 
        }
    }
    
	public function _sendAnEmail_with_template($pdf, $fileName, $storeId = 0) {
    	try{
            $sender = Mage::getStoreConfig("pickpack_options/product_separated/auto_processing_sender",$storeId);
            $senderName = Mage::getStoreConfig('trans_email/ident_' . $sender . '/name');
			$senderEmail= Mage::getStoreConfig('trans_email/ident_' . $sender . '/email');
			
			$sendTo = $this->_getConfig("auto_processing_send_to", $storeId);
			$subject = Mage::getStoreConfig("pickpack_options/product_separated/auto_processing_subject",$storeId);
			$body = Mage::getStoreConfig("pickpack_options/product_separated/auto_processing_body",$storeId);
			$translate  = Mage::getSingleton('core/translate');
			
			$emailTemplate = Mage::getModel('core/email_template')->loadDefault('moogento_pickpack_cron_product_separated_email_template');
			$emailTemplateVariables = array(); 
			$emailTemplateVariables['body'] = $body;
			$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
			$emailTemplate->setSenderName($senderName);
			$emailTemplate->setSenderEmail($senderEmail);
			$emailTemplate->setTemplateSubject($subject);
			
			// this is for to set the file format
            $at = new Zend_Mime_Part($pdf->render());
 
            $at->type        = 'application/pdf'; // if u have PDF then it would like -> 'application/pdf'
            $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
            $at->encoding    =  Zend_Mime::ENCODING_BASE64;
            $now = Mage::getModel('core/date')->timestamp(time());  
			$time =  date('Y-m-d-H-i', $now); 
            $at->filename    = 'product_separated_'.$time.'.pdf';
            $emailTemplate->getMail()->addAttachment($at);
            
			$emailTemplate->send($sendTo,'Admin',$emailTemplateVariables);
            Mage::log('Sent mail to '.$sendTo, null, 'moogento_pickpack.log');
 
        }catch(Exception $e)
        {
            Mage::log($e->getMessage(),null, 'moogento_pickpack.log'); 
        }
    	
    }
    
    public function _sendMail($pdf, $fileName, $storeId = 0) {
    	try{
			$sender = $this->_getConfig("auto_processing_sender", $storeId);
            $senderName = Mage::getStoreConfig('trans_email/ident_' . $sender . '/name');
			$senderEmail= Mage::getStoreConfig('trans_email/ident_' . $sender . '/email');
			$sender = $this->_getConfig("auto_processing_sender", $storeId);
			$sendTo = $this->_getConfig("auto_processing_send_to", $storeId);
			$subject = $this->_getConfig("auto_processing_subject", $storeId);
			$body = $this->_getConfig("auto_processing_body", $storeId);
			$translate  = Mage::getSingleton('core/translate');
			$emailTemplate = Mage::getModel('core/email_template')->loadDefault('moogento_pickpack_auto_processing');
			$emailTemplateVariables = array(); 
			$emailTemplateVariables['body'] = $body;
			$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
			$emailTemplate->setSenderName($senderName);
			$emailTemplate->setSenderEmail($senderEmail);
			$emailTemplate->setTemplateSubject($subject);
			$emailTemplate->getMail()->createAttachment($pdf->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $fileName);
			$emailTemplate->send($sendTo);
            Mage::log('Sent mail to '.$sendTo, null, 'moogento_pickpack.log');
 
        }catch(Exception $e)
        {
            Mage::log($e->getMessage(),null, 'moogento_pickpack.log'); 
        }
    	
    }

    protected function _sendAnEmail($pdf, $fileName, $storeId = 0) {
        $sender = $this->_getConfig("auto_processing_sender", $storeId);
        $sendTo = $this->_getConfig("auto_processing_send_to", $storeId);
        $subject = $this->_getConfig("auto_processing_subject", $storeId);
        $body = $this->_getConfig("auto_processing_body", $storeId);
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setTemplateSubject($subject);
        $mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_' . $sender . '/name'));
        $mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_' . $sender . '/email'));
        $mailTemplate->setTemplateText($body);
        $mailTemplate->getTemplateType(Mage_Core_Model_Email_Template::TYPE_TEXT);
        $mailTemplate->getMail()->createAttachment($pdf->render(), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, $fileName);
        $mailTemplate->send($sendTo);
        return $this;
    }

  protected function _saveAsFile($pdf, $fileName, $storeId = 0) {
        try {
            $io = new Varien_Io_File();
            $io->setAllowCreateFolders(true);
            $path = Mage::getBaseDir() . DS . str_replace('/', DS, $this->_getConfig("auto_processing_export_path", $storeId));
            Mage::log('Save file '.$fileName.' to '.$path, null, 'moogento_pickpack.log');
            $io->open(array('path' => $path));
            $io->write($fileName, $pdf->render());
            $io->close();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::log('Error when save file '.$fileName.' to '.$path.' : '.$e->getMessage(), null, 'moogento_pickpack.log');
        }

        return $this;
    }

    protected function _sendToFtp($pdf, $fileName, $storeId = 0) {
    	$baseDir = Mage::getBaseDir();
		$varDir = $baseDir.DS.$this->auto_save_folder;		
		$timeOfImport = date('jmY_his');
		$pickPackDir = $varDir.DS.$this->_filePath.DS;
		$io_file = new Varien_Io_File();
		$io_file->checkAndCreateFolder(Mage::getBaseDir('var').DS.'auto_pickPack');
		$io_file->checkAndCreateFolder(Mage::getBaseDir().DS.$this->auto_save_folder.DS.$this->_filePath);
		$port_ftp = $this->_getConfig("auto_processing_ftp_port", $storeId);
		$port_sftp = $port_ftp;
		if($port_ftp =='')
			$port_ftp = '21';
		if($port_sftp =='')
			$port_sftp = '22';
			
			$remote_path = $this->_getConfig("auto_processing_ftp_remote_path", $storeId);
        try { 
            $io = new Varien_Io_Ftp();
            $io->open(array(
                'host' => $this->_getConfig("auto_processing_ftp_host", $storeId),
                'user' => $this->_getConfig("auto_processing_ftp_user", $storeId),
                'password' => $this->_getConfig("auto_processing_ftp_password", $storeId),
                'port' => $port_ftp,
                'path' =>$remote_path,
                'timeout'   => '12'
            ));
			$io->write($fileName, $pdf->render());
            $io->close();
        } catch (Exception $e) 
        {
        	try {
        		
				$io = new Varien_Io_Sftp();
				$io->open(array(
					'host' => $this->_getConfig("auto_processing_ftp_host", $storeId),
					'username' => $this->_getConfig("auto_processing_ftp_user", $storeId),
					'password' => $this->_getConfig("auto_processing_ftp_password", $storeId),
					'port' => $port_sftp,
					'path' =>$remote_path,
					'timeout'   => '20'
				));
				// 			$io->write($pickPackDir.$fileName, $pdf->render());
				$io->write($fileName, $pdf->render());
				$io->close();
			} 
			catch (Exception $e) {
				//Should add message into session
				Mage::logException($e);
			}
            
        }
    }

    protected function _processReadyPdf($pdf, $fileName, $storeId = 0) {
        switch ($this->_getConfig("auto_processing", $storeId)) {
            case 1:
                //send as email
                $this->_sendMail($pdf, $fileName, $storeId);
                break;
            case 2:
                //save as file
                $this->_saveAsFile($pdf, $fileName, $storeId);
                break;
            case 3:
                //send to ftp
                $this->_sendToFtp($pdf, $fileName, $storeId);
                break;
            default:
                //don't do anything
                break;
        }

        return $this;
    }
    
    public function filter_by_status($order,$storeId=0) {
            $triggerStatus = $this->_getConfig("auto_processing_condition_status", $storeId);
            if (strpos($triggerStatus, ',') !== false) {
                $triggerStatus = explode(',', $triggerStatus);
            } else {
                $triggerStatus = array($triggerStatus);
            }

            if (in_array($order->getStatus(), $triggerStatus)) {
                return true;
            }
            return false;
    }
    
    public function check_trigger_status($order,$storeId=0) {
            $triggerStatus = $this->_getConfig("auto_processing_main_condition_status", $storeId);
            if (strpos($triggerStatus, ',') !== false) {
                $triggerStatus = explode(',', $triggerStatus);
            } else {
                $triggerStatus = array($triggerStatus);
            }

            if (in_array($order->getStatus(), $triggerStatus)) {
                return true;
            }
            return false;
    }
    
    public function filter_by_shipping_method($order,$storeId=0,$filter_pattern='') {
        $shipping_method_temp = $order->getShippingDescription();
		$shipping_method_temp = strtolower($shipping_method_temp);	
		$filter_pattern = strtolower($filter_pattern);
		$filter_pattern = trim($filter_pattern);		
		if(strlen($filter_pattern) == 0)
			return true;			
		$filter_pattern_arr = explode(',',$filter_pattern);
		foreach($filter_pattern_arr as $filter_element)
		{
			if(strpos($shipping_method_temp,$filter_element)!==false)
				return true;
		}
		return false;
    }

    public function filter_by_order_attribute($order,$code,$value) {
        try
        {
            $code = strtolower($code);
            $value = strtolower($value);
            $order_att_data = $order->getData($code);
            if(strpos($order_att_data,$value) !== false)
            {
                return true;
            }
            else
            {
                return false;
            }

        }
        catch(Exception $e)
        {
            return fasle;
        }
        return true;
    }

    public function filter_by_stock($order,$storeId=0) {
        $checkStock = $this->_getConfigFlag("auto_processing_required_stock", $storeId);
        if (
            ($checkStock) ||
            ($checkStock && Mage::helper('pickpack')->checkStock($order))
        ) {
            return true;
        }
        return false;
    }
    
    public function filter_by_custom_attribute($order,$storeId,&$canProcessOrder) {                   
        if ($this->_getConfigFlag("auto_processing_check", $storeId)) {
            $canProcessOrder -= 1;                            
            $checkField = $this->_getConfig("auto_processing_check_attribute", $storeId);                   
            
            if($checkField == 'szy_custom_attribute')
                $postfix =  'szy_check_custom_value_1';
            else
                if($checkField == 'szy_custom_attribute2')
                    $postfix =  'szy_check_custom_value_2';
                else
                    $postfix =  'szy_check_custom_value_3';
                    
            $checkValue = $this->_getConfig($postfix,$storeId);
            if ($checkValue == '_custom_') {
                
                if($checkField == 'szy_custom_attribute')
                    $postfix =  'szy_check_own_value1';
                else
                    if($checkField == 'szy_custom_attribute2')
                        $postfix =  'szy_check_own_value2';
                    else
                        $postfix =  'szy_check_own_value3';
                $checkValue = $this->_getConfig($postfix,$storeId);

            }
            
            $orderValue = null;
            if ($order->hasData($checkField)) {
                $orderValue = $order->getData($checkField);
            } else {
                $orderValue = $this->_getOrderTargetValue($checkField, $order->getId());
            }

            if ($this->_ifValueMatched($orderValue, $checkValue)) {
                $canProcessOrder += 1;
                return true;
            }
            
            return false;
        }
        return true;
    }
    
    public function filter_by_run_one($order,$storeId = 0) {
        if ($this->_getConfigFlag("auto_processing_print_flag", $storeId)) {
            return !Mage::helper('pickpack/print')->isPrinted($order->getId(), $this->_flagColumn);
        }
        return true;
    }

    public function processOrderStatusUpdate($order) {
        $storeId = $order->getStoreId();
        $flag_continue = false;
     
        if (!$storeId) {
            $storeId = 0;
        }
		
		if ($this->_getConfig("enable_auto_processing", $storeId))
			if($this->check_trigger_status($order,$storeId))
				if ($this->_getConfig("auto_processing", $storeId)) 
				{
		
					if ($this->_getConfig("auto_processing_condition_type", $storeId) == 'status') {

						//Get order filter
						// If status 
						//Filter 1: Check status
						$order_filter_type = $this->_getConfig("auto_processing_condition_order_attribute", $storeId);

						//TODO continue here
						if($order_filter_type == 'status')
							$flag_continue = $this->filter_by_status($order,$storeId);
						else
							if($order_filter_type == 'shipping_method')
							{
								$order_filter_pattern = $this->_getConfig("auto_processing_condition_order_attribute_value", $storeId);
								$flag_continue = $this->filter_by_shipping_method($order,$storeId,$order_filter_pattern);
							}
							else
								if($order_filter_type == 'attribute')
								{
									$order_filter_code = $this->_getConfig("auto_processing_condition_order_attribute_code", $storeId);

									$order_filter_pattern = $this->_getConfig("auto_processing_condition_order_attribute_value", $storeId);
									$flag_continue = $this->filter_by_order_attribute($order,$order_filter_code,$order_filter_pattern);
								}
								else
									if($order_filter_type == 'no')
										$flag_continue = true;


						if($flag_continue == false)
							return $this;
						//Else if order attribute
						//Filter 2 : CHECK STOCK
						$flag_continue = $this->filter_by_stock($order,$storeId);

                        if($flag_continue == false)
                            return $this;
						//Filter 3: CHECK CUSTOM ATTRIBUTE VALUE    
						$canProcessOrder = 1;
						$flag_continue = $this->filter_by_custom_attribute($order,$storeId,$canProcessOrder);  

						if($flag_continue == false)
								return $this;        
						//Filter 4: CHECK RUN ONE. 
						$flag_continue = $this->filter_by_run_one($order,$storeId);
						if($flag_continue == false)
							return $this;
				
						$pdf = $this->getPdf($order->getId(), $storeId);

						if(empty($pdf))
                        {
							return $this;
                        }
						else
                        {
							$this->_processReadyPdf($pdf, $this->_getFileName($order->getIncrementId()), $storeId);
                        }
				
						//Update 1 Order Attribute
						$this->_updateAttribute($this->_configGroupPrefix,$order->getId(),$this->_fieldPrefix);
				
						//Update 2 Flagautoaction table
                        Mage::helper('pickpack/print')->processPrint($order->getId(), $this->_flagColumn);
					} 
				}
        return $this;
    }
   
    protected function _getProcessingConfigHash($storeId) {
        $hashArray['type'] = $this->_getConfig("auto_processing", $storeId);
        switch ($hashArray['type']) {
            case 1:
                $hashArray['send_to'] = $this->_getConfig("auto_processing_send_to", $storeId);
                $hashArray['subject'] = $this->_getConfig("auto_processing_subject", $storeId);
                $hashArray['body'] = $this->_getConfig("auto_processing_body", $storeId);
                $hashArray['sender'] = $this->_getConfig("auto_processing_sender", $storeId);
                break;

            case 2:
                $hashArray['path'] = $this->_getConfig("auto_processing_export_path", $storeId);
                break;

            case 3:
                $hashArray['host'] = $this->_getConfig("auto_processing_ftp_host", $storeId);
                $hashArray['user'] = $this->_getConfig("auto_processing_ftp_user", $storeId);
                $hashArray['password'] = $this->_getConfig("auto_processing_ftp_password", $storeId);
                $hashArray['port'] = $this->_getConfig("auto_processing_ftp_port", $storeId);
                break;

            default:
                break;
        }
        return md5(implode('||', $hashArray));
    }

    protected function _updateAttribute($configPrefix, $orderIds, $fieldPrefix = '') {
        if (!is_array($orderIds)) {
            $orderIds = array($orderIds);
        }

        if (!empty($fieldPrefix)) {
            $fieldPrefix .= '_';
        }

        $resource = Mage::getResourceModel('moogento_shipeasy/sales_order');

        foreach ($orderIds as $orderId) {

            $storeId = Mage::getResourceSingleton('pickpack/sales_order')->getOrderStoreId($orderId);
            if (!$storeId) {
                $storeId = 0;
            }

            if (Mage::getStoreConfigFlag("pickpack_options/{$configPrefix}/{$fieldPrefix}additional_action", $storeId)) {

                $attributeToUpdate = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}szy_attribute_to_update", $storeId);
                if($attributeToUpdate == 'custom_attribute')
                    $postfix =  'szy_custom_value1';
                else
                    if($attributeToUpdate == 'custom_attribute2')
                        $postfix =  'szy_custom_value2';
                    else
                        $postfix =  'szy_custom_value3';
                    
               

                $value = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}{$postfix}", $storeId);

                if($value =='{{date}}')
                {               
                    $value = date("d-m-Y", Mage::getModel('core/date')->timestamp(time()));
                }
                else
                    if ($value == '_custom_') {
                        if($attributeToUpdate == 'custom_attribute')
                            $postfix =  'szy_own_value1';
                        else
                            if($attributeToUpdate == 'custom_attribute2')
                                $postfix =  'szy_own_value2';
                            else
                                $postfix =  'szy_own_value3';
                        $value = Mage::getStoreConfig("pickpack_options/{$configPrefix}/{$fieldPrefix}{$postfix}", $storeId);
                    }

                try {
                    $pickpack_version = (int)Mage::getConfig()->getNode()->modules->Moogento_Pickpack->version;
                    $shipeasy_version = (int)Mage::getConfig()->getNode()->modules->Moogento_ShipEasy->version;
                    if($shipeasy_version < 3){
                        $resource->updateGridRow($orderId, $attributeToUpdate, $value);
                    }
                    else
                    {
                        $resource->updateGridRow($orderId, 'szy_'.$attributeToUpdate, $value);
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        return $this;
    }

    protected function _getOrderTargetValue($field, $orderId) {
        if (in_array($field, array('custom_attribute', 'custom_attribute2', 'custom_attribute3','szy_custom_attribute', 'szy_custom_attribute2', 'szy_custom_attribute3'))) {
            return Mage::getResourceSingleton('pickpack/sales_order')->getOrderGridColumnValue($field, $orderId);
        }
        return Mage::getResourceSingleton('pickpack/sales_order')->getOrderColumnValue($field, $orderId);
    }

    protected function _ifValueMatched($orderValue, $neededValue) {
        $result = false;

        if ($neededValue === '0') {
            if ((float)$orderValue == (float)$neededValue) {
                $result = true;
            }
        } else if (empty($neededValue)) {
            if (!$orderValue || empty($orderValue)) {
                $result = true;
            }
        } else {
            if ($orderValue == $neededValue) {
                $result = true;
            }
        }

        return $result;
    }
    
     protected function _processBulkPdfs($storePdfs) {
        $groups = array();

        foreach ($storePdfs as $storeId => $storePdfInfo) {

            $storePdf = $storePdfInfo['pdf'];
            $orderIds = $storePdfInfo['order_ids'];

            if ($this->_getConfigFlag("auto_processing_groupping", $storeId)) {

                $storeConfigHash = $this->_getProcessingConfigHash($storeId);
                
                if (!isset($groups[$storeConfigHash])) {
                    $groups[$storeConfigHash] = array(
                        'store_id' => $storeId,
                        'pdf' => $storePdf,
                        'order_ids' => $orderIds
                    );
                } else {
                    $groupedPdf = $groups[$storeConfigHash]['pdf'];
                    foreach ($storePdf->pages as $page) {
                        $groupedPdf->pages[] = $page;
                    }

                    $groups[$storeConfigHash]['order_ids'] = array_unique(array_merge($groups[$storeConfigHash]['order_ids'], $orderIds));
                }
            } else {
                $storeConfigHash = md5('store_' . $storeId);
                $groups[$storeConfigHash] = array(
                    'store_id' => $storeId,
                    'pdf' => $storePdf,
                    'order_ids' => $orderIds
                );
            }
        }

        foreach ($groups as $groupedData) {
            $storeId = $groupedData['store_id'];
            $pdf = $groupedData['pdf'];
            $orderIds = $groupedData['order_ids'];
            $this->_processReadyPdf($pdf, $this->_getFileName($orderIds), $storeId);
        }
    }

    public function processBulk($digitDay) { 
        $stores = Mage::app()->getStores(true);
        $ordersByStores = array();
        //Change max numbers of order print in the same time.
        $avalable_orders_processing = 20;
        foreach ($stores as $store) {
        	$tmpOrderIds = array();
            $storeId = $store->getId();
            if ($this->_getConfig("enable_auto_processing", $storeId))
				if ($this->_getConfig("auto_processing", $storeId)) {
					if ($this->_getConfig("auto_processing_condition_type", $storeId) == 'day') {
						$daysToRun = $this->_getConfig("auto_processing_condition_specific_day", $storeId);                    
						if (strpos($daysToRun, $digitDay) !== false) {
							//Filter 1: Order status
						
							$orders = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('store_id', $storeId);
							if (count($orders)) {
								$orderIds = array();
								foreach ($orders as $order) {
									//Main filter:
									$flag_continue = $this->check_trigger_status($order,$storeId);
									if($flag_continue == false)
										continue;
																			
									 $canProcessOrder = 1;
									 $order_filter_type = $this->_getConfig("auto_processing_condition_order_attribute", $storeId);
									 //Filter 1: CHECK Status | Shipping method | Order attribute
									if($order_filter_type == 'status')
									{
										$flag_continue = $this->filter_by_status($order,$storeId);
									}
									else
										if($order_filter_type == 'shipping_method')
										{
											$order_filter_pattern = $this->_getConfig("auto_processing_condition_order_attribute_value", $storeId);
											$flag_continue = $this->filter_by_shipping_method($order,$storeId,$order_filter_pattern);
										}
										else
											if($order_filter_type == 'attribute')
											{
												$order_filter_code = $this->_getConfig("auto_processing_condition_order_attribute_code", $storeId);

												$order_filter_pattern = $this->_getConfig("auto_processing_condition_order_attribute_value", $storeId);
												$flag_continue = $this->filter_by_order_attribute($order,$order_filter_code,$order_filter_pattern);
											}
											else
												if($order_filter_type == 'no')
													$flag_continue = true;
								
									if($flag_continue == false)
										continue;
									
									//Filter 2 : CHECK STOCK
									$flag_continue = $this->filter_by_stock($order,$storeId);
									if($flag_continue == false)
										continue;
								
									//Filter 3 : CHECK CUSTOM ATTRIBUTE
									$flag_continue = $this->filter_by_custom_attribute($order,$storeId,$canProcessOrder);  
									if($flag_continue == false)
										continue;
								
									//Filter 4: CHECK RUN ONE. 
									$flag_continue = $this->filter_by_run_one($order,$storeId,$canProcessOrder); 
									if($flag_continue == false)
										continue;						
									
									$tmpOrderIds[] = $order->getId();
								
								}
							
								$orderIds = $tmpOrderIds;
								unset($tmpOrderIds);
								if (!count($orderIds)) {
									continue;
								} 
							
								$new_orderIds = count($orderIds);
								$output = array();
								if($new_orderIds > $avalable_orders_processing)
								{
									$output = array_slice($orderIds, 0, $avalable_orders_processing);

								}
								else
								{
									$output = $orderIds; 

								}
							
								if(count($output) > 0 )
									$ordersByStores[$storeId] = $output;
								
								$avalable_orders_processing -= count($output);

								if($avalable_orders_processing <= 0)
								{
									continue; 
								}
								unset($output);
							
							}
						}
					}
				}
        }
        
        //Get not printed orderId in trigger status array from all stores. Change flag for each column
        foreach ($ordersByStores as $storeId => $storeOrderIds) {
        	$pdf = $this->getPdf($storeOrderIds, $storeId);
            Mage::helper('pickpack/print')->processPrint($storeOrderIds, $this->_flagColumn);
            $ordersByStores[$storeId] = array(
                'order_ids' => $storeOrderIds,
                'pdf' => $pdf
            );
        }
        $this->_processBulkPdfs($ordersByStores);
        return $this;
    }
    
    public function processProductSeparated($digitDay) { 
        $stores = Mage::app()->getStores(true);
        $ordersByStores = array();
        $productFilterByStores = array();
        //Change max numbers of order print in the same time.
        $avalable_orders_processing = 100;
      	Mage::log('cron run', null, 'moogento_pickpack.log');
        foreach ($stores as $store) {
        	$tmpOrderIds = array();
            $storeId = $store->getId();
			if($this->checkCronTime($storeId) != true)
            continue;
            
			Mage::log('Run. From store: '.$storeId, null, 'moogento_pickpack.log');
            $product_filter_type = $this->_getConfig("auto_processing_condition_product_attribute", $storeId);
			$product_filter_code = $this->_getConfig("auto_processing_condition_product_attribute_code", $storeId);
			$product_filter_value = $this->_getConfig("auto_processing_condition_product_attribute_value", $storeId);
			
			$product_filter = array();	
				
			$product_filter['type'] = $product_filter_type;
			$product_filter['code'] = $product_filter_code;
			$product_filter['value'] = $product_filter_value;
			
			$productFilterByStores[$storeId] = 	$product_filter;
			
			unset($product_filter);					 
            if ($this->_getConfig("enable_auto_processing", $storeId))
				if ($this->_getConfig("auto_processing", $storeId)) {
					if ($this->_getConfig("auto_processing_condition_type", $storeId) == 'day') {
						$daysToRun = $this->_getConfig("auto_processing_condition_specific_day", $storeId);
						//Filter by date                    
						if (strpos($daysToRun, $digitDay) !== false) {
							//Filter 1: Order status
							$orders = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('store_id', $storeId);
							if (count($orders)) {
								$orderIds = array();
								foreach ($orders as $order) {
									//Main filter:
									$flag_continue = $this->check_trigger_status($order,$storeId);
									if($flag_continue == false)
										continue;								
									$tmpOrderIds[] = $order->getId();
								
								}
							
								$orderIds = $tmpOrderIds;
								unset($tmpOrderIds);
								if (!count($orderIds)) {
									continue;
								} 
							
								$new_orderIds = count($orderIds);
								$output = array();
								if($new_orderIds > $avalable_orders_processing)
								{
									$output = array_slice($orderIds, 0, $avalable_orders_processing);

								}
								else
								{
									$output = $orderIds; 

								}
							
								if(count($output) > 0 )
									$ordersByStores[$storeId] = $output;
								
								$avalable_orders_processing -= count($output);

								if($avalable_orders_processing <= 0)
								{
									continue; 
								}
								unset($output);
							
							}
						}
					}
				}
        }
        
		
        $ordersByStores_update = array();//$ordersByStores;
        $ordersByStores_process = array();
        $is_empty_process = 0;
        foreach ($ordersByStores as $storeId => $storeOrderIds) {
        	
        	$product_filter = $productFilterByStores[$storeId];
        	$pdf = $this->getPdf($storeOrderIds, $storeId,$product_filter); 
        	
        	
        	if(sizeof($pdf) == 0)
        		continue;
        	
        	if(is_null($pdf))     
        		continue;
        		
        	$is_empty_process = 1;
        	unset($ordersByStores[$storeId]);
            $ordersByStores_process[$storeId] = array(
                'order_ids' => $storeOrderIds,
                'pdf' => $pdf
            );
            $ordersByStores_update = array('order_ids' => $storeOrderIds);
        }  
        if($is_empty_process ==1)
        {
	        $this->_processBulkPdfs($ordersByStores_process);
	        foreach ($ordersByStores_update as $storeId => $storeOrderIds) {
				$this->updateData($storeOrderIds,$product_filter,$storeId);
			}
	    }
        return $this;
    }
    
    public function updateData($order_arrs,$product_filter = array(),$storeId=0) {
    	$return_data = array();
        $this->_orderCollection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' =>($order_arrs)))->setPageSize(count($order_arrs))//count($orders)); 1000
            ->setCurPage(1);
			$now = Mage::getModel('core/date')->timestamp(time());  
			$time =  date('Y - m - d', $now); 		
			
        foreach ($this->_orderCollection as $order) {
        	$qtys = array();
            $_orderId = $order->getId();
            $this->order_model_list[$_orderId] = $order;
            $storeId  = $order->getStore()->getId(); 
            if(isset($this->store_list[$storeId]))
                $this->store_list[$storeId] .= ','.$_orderId;
            else
                $this->store_list[$storeId] = $_orderId;
			$order_incre_id = $order->getData('increment_id');			
			
            //Split bundle product default yes
            $this->_itemsCollection[$_orderId] = Mage::helper('moogento_pickpack')->getItemsToProcess($order, $this->_printing_format['split_bundle_product'] == 1);
            $total_item_count = 0;
            $product_list_order = '';
			$has_qty =0;
            foreach ($this->_itemsCollection[$_orderId] as $item) 
            {
				
				if(Mage::getStoreConfig('pickpack_options/product_separated/filter_shipped_item_yn',$storeId) == 1)
				{
					if(($item->getProductType() == 'simple') || 
					($item->getProductType() == 'bundle') || 
					($item->getProductType() == 'configurable')|| 
					($item->getProductType() == 'configurable'))
						$qty_calculate_product = $item->getQtyOrdered() - $item->getQtyShipped();				
					else
						$qty_calculate_product = $item->getQtyOrdered() - $item->getQtyInvoiced();				
				}
				else
					$qty_calculate_product = $item->getQtyOrdered();
				
				if($qty_calculate_product == 0)
					continue;
				
				
					if(isset($product_filter['type']) && isset($product_filter['type']) && isset($product_filter['type']))
					{
						if(($product_filter['type'] == 'product_attribute'))
						{
							$code = $product_filter['code'];
							$value = strtolower(trim($product_filter['value']));
							$item_attribute_value = strtolower(trim($item->getData($code)));
							if(strpos($item_attribute_value,$value) === FALSE)
							{
								continue;
							}
						}
						else
							if(($product_filter['type'] == 'yes_current_date'))
							{
								
								$code = $product_filter['code'];
								$item_date = $item->getData($code); 
								if(strtotime($item_date) === strtotime('today'))
								{
									$qtys[$item->getId()] = $qty_calculate_product;
									$has_qty ++;
								}
								else
									$qtys[$item->getId()] = 0;
							}
					}
			}

//TODO invoice
			if($has_qty > 0)
			{
				$this->autoInvoice($order,$qtys);								
			}
        }
    }
    
    public function autoInvoice($order ,$qtys = array()) {
    	try{
			$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtys);
			$invoice->setEmailSent(true);
			$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);// 
	//  		$invoice->register();
			$invoice->register()->pay();
			Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder())
			->save();
			$invoice->save();
			try {
					// $invoice->sendEmail(true, '');
                    $invoice->sendEmail();
					} catch (Exception $e) {
						Mage::log($e->getMessage(), null, 'moogento_pickpack.log');
					}
		}
		catch(Exception $e)
		{
			Mage::log($e->getMessage(), null, 'moogento_pickpack.log');
		}
    }
    
    public function autoShip($order,$qtys= array()) {
    	$shipment = $order->prepareShipment($qtys);
		$shipment->register();
		//$order->setIsInProcess(true);
		$order->addStatusHistoryComment('Automatically SHIPPED by Inchoo_Invoicer.', false);
		$transactionSave = Mage::getModel('core/resource_transaction')
			->addObject($shipment)
			->addObject($shipment->getOrder())
			->save();
    }
    
    public function checkCronTime($storeId) {
    	$mage_time = Mage::getModel('core/date')->timestamp(time());  
    	
		$current_time =  date('H:i:s', $mage_time);	
    	$cron_config_time = Mage::getStoreConfig('pickpack_options/product_separated/auto_processing_condition_specific_time',$storeId);
    	$cron_period = Mage::getStoreConfig('pickpack_options/product_separated/cron_period');
    	if(is_numeric($cron_period))
    		$cron_period = 60*$cron_period;
    	else
    		$cron_period = 300;
    	$cron_config_time = str_replace(',',':',$cron_config_time);
    	$time_cron =  strtotime($current_time);
		$time_config = strtotime($cron_config_time);	
		Mage::log('storeId: '.$storeId.' current time: '.$current_time.' config_time: '.$cron_config_time.' cron period '.$cron_period, null, 'moogento_pickpack.log');
		if((($time_cron -30) <= $time_config) && ($time_config <= ($time_cron + $cron_period -30)))
    	{
			return true;
    	}
    	return false;
    }
}
