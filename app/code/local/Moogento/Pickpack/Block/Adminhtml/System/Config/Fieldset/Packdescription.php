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
* File        Packdescription.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Block_Adminhtml_System_Config_Fieldset_Packdescription
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface {    
//     Mage_Adminhtml_Block_System_Config_Form_Fieldset
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        if(Mage::helper('pickpack')->isInstalled('Moogento_Email'))
        {
        	$html = '<div class="moo_config_info"><em></em>Attach this PDF to your <a href="http://www.magentocommerce.com/knowledge-base/entry/customizing-transactional-emails">transactional emails</a> by adding this code : <span class="comment_code">&lt;pickpack_email&gt;{{attach_packingsheet({{var order.increment_id}})}}&lt;/pickpack_email&gt;</span></div>';
        }
        else
        {
            $html = '<div class="moo_config_info"><em></em>To attach this PDF to your emails, first <a href="https://moogento.com/guides/pickPack_Advanced_Setup#Attach_pickPack_PDFs_to_Magento_Emails">install the <b>"Moogento Email"<b> extension</a>.</div>';
        }
        return $html;
    }
}



