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
* File        Blank.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Block_Hidedisableextensionconfig extends Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $html = '<script type="text/javascript">';
        $html .= 'document.observe("dom:loaded", function() {';

        //Show Aitoc Checkout Fields?
        if(!Mage::helper('pickpack')->isInstalled("Aitoc_Aitcheckoutfields")){
            $html .= "document.getElementById('row_pickpack_options_wonder_show_aitoc_checkout_field_yn').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_invoice_show_aitoc_checkout_field_yn').style.display = 'none';";
        }

        //Show Mageworx Multifees?
        if(!Mage::helper('pickpack')->isInstalled('MageWorx_MultiFees')){
            $html .= "document.getElementById('row_pickpack_options_wonder_show_mageworx_multifees').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_invoice_show_mageworx_multifees').style.display = 'none';";
        }

        //Show WSA Store-Pickup details?
        if(!Mage::helper('pickpack')->isInstalled('Webshopapps_Shippingoverride2')){
            //PDF Packing Sheet
            $html .= "document.getElementById('row_pickpack_options_wonder_show_wsa_storepickup').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_wsa_storepickup_options').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_non_store_pickup_yn').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_non_store_pickup_showdatetime').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_store_pickup_hide_shipping_yn').style.display = 'none';";

            //PDF Invoice
            $html .= "document.getElementById('row_pickpack_options_wonder_invoice_show_wsa_storepickup').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_invoice_wsa_storepickup_options').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_invoice_non_store_pickup_yn').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_invoice_non_store_pickup_showdatetime').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_invoice_store_pickup_hide_shipping_yn').style.display = 'none';";
        }

        //Show shipping warehouse?
        if(!Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')){
            $html .= "document.getElementById('row_pickpack_options_picks2_pickpack_warehouse').style.display = 'none';";
        }

        //Show Product Gift-Wrap Column?
        if(!Mage::helper('pickpack')->isMageEnterprise()){
            $html .= "document.getElementById('row_pickpack_options_wonder_show_gift_wrap').style.display = 'none';";
            $html .= "document.getElementById('row_pickpack_options_wonder_invoice_show_gift_wrap').style.display = 'none';";
        }

        $html .= "document.getElementById('row_pickpack_options_wonder_hide_disable_extension_config').style.display = 'none';";

        $html .= '});';
        $html .= '</script>';

        return $html;
    }
}