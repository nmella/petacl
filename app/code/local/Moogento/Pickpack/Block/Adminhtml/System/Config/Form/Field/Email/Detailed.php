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
* File        Detailed.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Field_Email_Detailed
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $html = $element->getElementHtml();


        $websiteScope = $this->getRequest()->getParam('website');
        $storeScope = $this->getRequest()->getParam('store');

        $senders = array();

        $possibleSenders = Mage::getSingleton('adminhtml/system_config_source_email_identity')->toOptionArray();
        foreach ($possibleSenders as $senderInfo) {
            $senderCode = $senderInfo['value'];
            $namePath = "trans_email/ident_{$senderCode}/name";
            $emailPath = "trans_email/ident_{$senderCode}/email";

            $senderName = $senderEmail = '';

            if ($websiteScope) {
                $senderName = Mage::app()->getWebsite($websiteScope)->getConfig($namePath);
                $senderEmail = Mage::app()->getWebsite($websiteScope)->getConfig($emailPath);
            } else if ($storeScope) {
                $senderName = Mage::app()->getStore($storeScope)->getConfig($namePath);
                $senderEmail = Mage::app()->getStore($storeScope)->getConfig($emailPath);
            } else {
                $senderName = Mage::getStoreConfig($namePath, 0);
                $senderEmail = Mage::getStoreConfig($emailPath, 0);
            }

            $senders[$senderCode] = array(
                'name' => $senderName,
                'email' => $senderEmail
            );
        }

        $currentValue = (string)$element->getValue();

        $html .= '<br>';
        if(isset($senders[$currentValue]['name']))
	        $name = $senders[$currentValue]['name'];
	    else
	    	$name ='';
	    	
	    if(isset($senders[$currentValue]['email']))
	        $email = $senders[$currentValue]['email'];
	    else
	    	$email ='';
	    	
        $html .= 'Name : <span id="' . $element->getId() . '_descr_name">' . $name . '</span><br>';
        $html .= 'Email: <span id="' . $element->getId() . '_descr_email">' . $email . '</span>';
        $html .= '
<script type="text/javascript">
    var ' . $element->getId() . '_possible_senders = ' . Mage::helper('core')->jsonEncode($senders) . ';
    $("' . $element->getId() . '").observe("change", function(event){
        var _element = Event.element(event);
        var _value   = $(_element).getValue();

        $("' . $element->getId() . '_descr_name").update(
            ' . $element->getId() . '_possible_senders[_value].name
        );
        $("' . $element->getId() . '_descr_email").update(
            ' . $element->getId() . '_possible_senders[_value].email
        );
    });
</script>
';
        return $html;
    }

}
