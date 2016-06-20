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
* File        Actions.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Actions
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{


    protected function _getFieldsContainerHeaderWithClass($title,$class) {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        $html = '<tr class="column_config '.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getFieldsContainerHeaderWithClassNoneborder($title,$class) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        $html = '<tr class="auto-processing '.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getFieldsContainerHeaderWithClassAndStatus($title,$class,$status) {
		$default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
		$colspan = (!$default) ? 5 : 4;

        if($status == 1)
            $html = '<tr style="display:none"><td colspan="' . $colspan . '">';
        else
            $html = '<tr class="column_config '.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';

	    if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getGroupContainerHeaderWithClass($class) {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        $html = '<tr class="'.$class.'"><td colspan="' . $colspan . '">';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';

        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _isShipEasyInstalled() {
        return Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy','0.1.14');
    }

    protected function _isAutomationInstalled() {
        return Mage::helper('pickpack')->isInstalled('Moogento_Automation');
    }

    protected function _getInstallShipEasyMessage() {
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;
        $html = '<tr><td colspan="' . $colspan . '" ><b>Advanced Features</b><br/> <span style="color:#ff0000" >To enable advanced features, please install </span> <b><a href="https://moogento.com/magento-order-shipping-processing?utm_source=install&utm_medium=config&utm_campaign=pickpack_connector" target="_blank">shipEasy</a></b></td></tr>';
        return $html;
    }

    protected function _getFieldsContainerHeaderManual($title) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

        if(!$isShipEasyInstalled) {
            $html = '<tr><td colspan="' . $colspan . '" ><ul class="addon_features">';
            $html .= '<li class="addon_features_title"><a href="https://moogento.com/magento-order-shipping-processing?utm_source=install&utm_medium=config&utm_campaign=pickpack_connector">Install shipEasy</a> to activate these advanced features:</li>';
            $html .= '<li class="addon_features_subtitle">&bull; &nbsp;Auto-set custom flag columns columns in Order Grid, when making PDFs</li>';
			$html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. a \'Printed\' column, to make sure you only ship orders once)</li>';
            $html .= '<li class="addon_features_subtitle">&bull; Auto-set Order Status when making PDFs</li>';
			$html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. set the status to \'Packing your order\' - keep your customers informed and reduce support requests)</li>';
            $html .= '</ul></td></tr>';
            $html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        } else
            $html = '<tr class="pack_invoice_group manual-printing"><td colspan="' . $colspan . '">';
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';

	    if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getFieldsContainerHeaderAuto($title) {
        $isAutomationInstalled = $this->_isAutomationInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

        if(!$isAutomationInstalled) {
            $html = '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else if($title == 'Main control'){
            $html = '<tr class="pack_invoice_group manual-printing auto-processing-printing-main-control"><td colspan="' . $colspan . '">';
        }
        else {
            $html = '<tr class="pack_invoice_group manual-printing auto-processing-printing"><td colspan="' . $colspan . '">';
        }
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';

        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getFieldsContainerHeaderManualWithID($title,$id) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

        if(!$isShipEasyInstalled) {
            $html = '<tr><td colspan="' . $colspan . '" ><b>Advanced Features</b><br/> <span style="color:#ff0000" >&nbsp;&nbsp;&nbsp; To activate our advanced features, please install </span> <b><a href="https://moogento.com/magento-order-shipping-processing.html" target="_blank">shipEasy</a></b></td></tr>';
            $html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
			$html = '<tr class="pack_invoice_group manual-printing"><td colspan="' . $colspan . '">';
        $html .= '<fieldset id="'.$id.'" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getFieldsContainerHeaderAutoWithID($title,$id) {
        $isAutomationInstalled = $this->_isAutomationInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

        if(!$isAutomationInstalled) {
            $html = '<tr><td colspan="' . $colspan . '" ><ul class="addon_features">';
            $html .= '<li class="addon_features_title"><a href="https://moogento.com/automation?utm_source=install&utm_medium=config&utm_campaign=pickpack_connector">Install Automation</a> to activate these advanced features:</li>';
            $html .= '<li class="addon_features_subtitle">&bull; &nbsp;Auto-create PDFs based on custom rules</li>';
            $html .= "<li>- - &nbsp;Send the PDFs to email, local filesystem, or remote FTP</li>";
            $html .= "<li>- - &nbsp;Set a 'print once' filter</li>";
            $html .= "<li>- - &nbsp;Set a filter to not auto-print orders with out-of-stock items</li>";
            $html .= "<li>- - &nbsp;Set a filter to only process orders that match a custom shipEasy attribute</li>";
            $html .= "<li>- - &nbsp;Set option to only auto-print on certain days</li>";
            $html .= "<li>- - &nbsp;Add additional filters based on Order Status, Shipping Method, or Product Attribute</li>";
            $html .= "<li>- - &nbsp;Set up to 3 actions - eg. print express shipments immediatey to a specific printer</li>";
			$html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. reduce training : new orders just print out)</li>';

            $html .= '<li class="addon_features_subtitle">&bull; &nbsp;Auto-change order statuses</li>';
            $html .= "<li>- - &nbsp;Apply at a specific time of day</li>";
            $html .= "<li>- - &nbsp;Apply to specific order statuses</li>";
            $html .= "<li>- - &nbsp;Apply to specific shipEasy flags</li>";
			$html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. auto-mark orders as Shipped when they\'ve been flagged as QA\'d and Packed)</li>';
			$html .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(eg. auto-mark Shipped orders as Archived at the end of the day)</li>';
            $html .= '</ul></td></tr>';
            $html .= '<tr style="display:none"><td colspan="' . $colspan . '">';
        }
        else
            $html = '<tr class="pack_invoice_group manual-printing"><td colspan="' . $colspan . '">';
        $html .= '<fieldset id="'.$id.'" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getFieldsContainerHeader($title) {
        $isShipEasyInstalled = $this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

	    if(!$isShipEasyInstalled)
            $html = '<tr style="display:none"><td colspan="' . $colspan . '">';
        else
            $html = '<tr class="auto-processing"><td colspan="' . $colspan . '">';
        $html .= '<fieldset style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';

        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getFieldsContainerHeaderNoneborder($title) {
        $isShipEasyInstalled = 1;//$this->_isShipEasyInstalled();
        $default = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $colspan = (!$default) ? 5 : 4;

        if(!$isShipEasyInstalled)
        {
            $html = '<tr ><td colspan="' . $colspan . '">';
        }
        else
			$html = '<tr  class="auto-processing-none-boder"><td colspan="' . $colspan . '">';
        $html .= '<fieldset class = "none-border" style="text-align:left; margin-top: 20px"><legend style="display: inline; font-weight: bold">&nbsp;' . $title . '&nbsp;</legend>';
        $html .= '<table cellspacing="0" class="form-list"><colgroup class="label" /><colgroup class="value" />';
        if (!$default)
            $html .= '<colgroup class="use-default" />';
        $html .= '<colgroup class="scope-label" /><colgroup class="" /><tbody>';
        return $html;
    }

    protected function _getFieldsContainerFooter() {
        $html = '</tbody></table></fieldset></td></tr>';
        return $html;
    }

    protected function _getFieldsContainerFooter4() {
        $html = '<tr class="text_padding" style="height:15px"><td></td></tr></tbody></table></fieldset></td></tr>';
        return $html;
    }

    protected function _getFieldsContainerFooter3() {
        $html = '<tr class="text_padding" style="height:32px"><td></td></tr></tbody></table></fieldset></td></tr>';
        return $html;
    }

     protected function _getFieldsContainerFooter2() {
        $html = '</div>';
        return $html;
    }

    protected function _getTextFieldsContainerFooter() {
        $html = '</div>';
        return $html;
    }

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $dependingFields = array(
            'pickpack_options_wonder_invoice_additional_action',
            'pickpack_options_wonder_invoice_auto_processing_additional_action',
            'pickpack_options_wonder_additional_action',
        );
        $html = $this->_getHeaderHtml($element);

        foreach ($element->getSortedElements() as $field) {

            /**
             * Additional Action Config
             */

            //Begin top group
            if (
                ($field->getId() == 'pickpack_options_wonder_heading_top')  ||
                ($field->getId() == 'pickpack_options_wonder_invoice_heading_top')
            ) {
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_top');
            }

            //Begin bottom group
            if (
                ($field->getId() == 'pickpack_options_wonder_heading_bottom')  ||
                ($field->getId() == 'pickpack_options_wonder_invoice_heading_bottom')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_bottom');
            }

            //Begin middle group
            if (
                ($field->getId() == 'pickpack_options_wonder_heading_middle')  ||
                ($field->getId() == 'pickpack_options_wonder_invoice_heading_middle')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_middle');
            }

            // Begin message group
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_heading_message')  ||
                ($field->getId() == 'pickpack_options_wonder_heading_message')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_message');
            }
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_heading_manual_action') ||
                ($field->getId() == 'pickpack_options_wonder_heading_manual_action')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_manual_printing');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_heading_auto_processing_action') ||
                ($field->getId() == 'pickpack_options_wonder_heading_auto_processing_action')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getGroupContainerHeaderWithClass('pack_invoice_group pack_invoice_group_auto_processing_printing');
            }




            // Close Additional Action Config 3
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_szy_own_value3_3rd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_szy_own_value3_3rd')
            ) {
                //close for shipeasy connection
                $html .= $this->_getFieldsContainerFooter();
                //close for advanced processing
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerFooter();
            }
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_manual_description') ||
                ($field->getId() == 'pickpack_options_wonder_manual_description')
            ) {
                $html .= $this->_getFieldsContainerHeaderManual('Manual printing');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_manual_processing_print_flag') ||
                ($field->getId() == 'pickpack_options_wonder_manual_processing_print_flag')
            ) {

            }

            if (
            ($field->getId() == 'pickpack_options_wonder_invoice_autoprocess_description')
            ) {
                $html .=$this->_getFieldsContainerHeaderAutoWithID('Automated processing','invoice_description');
            }

            if (
            ($field->getId() == 'pickpack_options_wonder_autoprocess_description')
            ) {
                $html .=$this->_getFieldsContainerHeaderAutoWithID('Automated processing','pack_description');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_enable_auto_processing') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_enable_auto_processing')
            ) {
                $html .=$this->_getFieldsContainerHeaderAuto('Main control');
            }

            //Automated processing 0

            //Automated processing 1
            if (
                ($field->getId() == 'pickpack_options_wonder_auto_processing') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing')
            ) {
                //Turn this line on when need
                $html .= $this->_getFieldsContainerFooter();
                $html .=$this->_getFieldsContainerHeaderAuto('#1');
            }

            //Automated processing 2
            if (
                ($field->getId() == 'pickpack_options_wonder_auto_processing_2nd') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_2nd')
            ) {
                //Turn this line on when need
                $html .= $this->_getFieldsContainerFooter();
                $html .=$this->_getFieldsContainerHeaderAuto('#2');
            }


            //Automated processing 3
            if (
                ($field->getId() == 'pickpack_options_wonder_auto_processing_3rd') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_3rd')
            ) {
                //Turn this line on when need
                $html .= $this->_getFieldsContainerFooter();
                $html .=$this->_getFieldsContainerHeaderAuto('#3');
            }

            //Autoprocessing order filter
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_print_flag') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_print_flag')
            ) {
                $html .= $this->_getFieldsContainerHeaderNoneborder('Processing filters');
            }

            //Autoprocessing order filter
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_print_flag_2nd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_print_flag_2nd')
            ) {
                $html .= $this->_getFieldsContainerHeaderNoneborder('Processing filters');
            }

            //Autoprocessing order filter
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_print_flag_3rd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_print_flag_3rd')
            ) {
                $html .= $this->_getFieldsContainerHeaderNoneborder('Processing filters');
            }


            //Automated order processing 1
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_condition_type') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_condition_type')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerHeaderNoneborder('Automated order processing');
            }


            //Automated order processing 2
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_condition_type_2nd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_condition_type_2nd')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerHeaderNoneborder('Automated order processing');
            }

            //Automated order processing 3
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_condition_type_3rd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_condition_type_3rd')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerHeaderNoneborder('Automated order processing');
            }

            if (!$this->_isShipEasyInstalled() && in_array($field->getId(), $dependingFields)) {
                $field->setValue(0);
                $field->setReadonly(true, true);
            }

            // Additional Action Config 1
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_additional_action') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_additional_action')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerHeaderNoneborder('Additional action');
            }

            // Additional Action Config 2
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_additional_action_2nd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_additional_action_2nd')
            ) {
                $html .= $this->_getFieldsContainerFooter();
                $html .= $this->_getFieldsContainerHeaderNoneborder('Additional action');
            }

            // Additional Action Config 3
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_additional_action_3rd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_additional_action_3rd')
            ) {
                $html .= $this->_getFieldsContainerHeaderNoneborder('Additional action');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shipment_details_shipping_options_yn') ||
                ($field->getId() == 'pickpack_options_wonder_shipment_details_shipping_options_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Magalter_Customshipping')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Shipping options','shipping_options',1);
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_product_warehouse_yn') ||
                ($field->getId() == 'pickpack_options_wonder_product_warehouse_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Warehouse','warehouse_column',1);
                else
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Warehouse','warehouse_column',0);
            }
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shipment_temando_comment_yn') ||
                ($field->getId() == 'pickpack_options_wonder_shipment_temando_comment_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Temando_Temando')) || !(Mage::helper('pickpack')->isInstalled('Idev_OneStepCheckout')))

                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('customer comment','customer_comment',1);
            }
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_gift_wrap_style_yn') ||
                ($field->getId() == 'pickpack_options_wonder_gift_wrap_style_yn')
            )
            {
                if((!(Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap'))) && !(Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Giftwrap','giftwrap_column',1);
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_order_custom_attribute_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_order_custom_attribute_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Amasty_Orderattr')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Order attribute','orderattributes',1);
            }


            if (
                ($field->getId() == 'pickpack_options_wonder_order_custom_delivery_date_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_order_custom_delivery_date_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Amasty_Deliverydate')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Order deliverydate','deliverydate',1);
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_order_mw_custom_delivery_date_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_order_mw_custom_delivery_date_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('MW_Ddate')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Order deliverydate','deliverydate',1);
            }


            //Text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_product_sku_yn') ||
                ($field->getId() == 'pickpack_options_wonder_product_sku_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Sku','sku_text_grouped');
            }

            //Text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_tickbox_yn') ||
                ($field->getId() == 'pickpack_options_wonder_tickbox_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Tickbox','tickbox_text_grouped');
            }
            //Text numberlist
            if (
                ($field->getId() == 'pickpack_options_wonder_numbered_product_list_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_numbered_product_list_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Numberlist','tickbox_text_grouped');
            }
            //Product Price Line
            if (
                ($field->getId() == 'pickpack_options_wonder_product_line_prices_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_product_line_prices_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Product Lines','product_price_line_group');
            }
            //Total Price
            if (
                ($field->getId() == 'pickpack_options_wonder_total_subtotal') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_total_subtotal')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Subtotals','total_price_group');
            }
            //Allowance Text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_show_allowance_yn') ||
                ($field->getId() == 'pickpack_options_wonder_show_allowance_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Allowance','allowance_text_grouped');
            }

            //Custom attribute Text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shelving_real_yn') ||
                ($field->getId() == 'pickpack_options_wonder_shelving_real_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Custom attribute','custom_attribute_text_grouped');
            }

            //Custom attribute 2 Text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shelving_yn') ||
                ($field->getId() == 'pickpack_options_wonder_shelving_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Custom attribute 2','custom_attribute2_text_grouped');
            }

            //Custom attribute 3 Text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shelving_2_yn') ||
                ($field->getId() == 'pickpack_options_wonder_shelving_2_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Custom attribute 3','custom_attribute2_text_grouped custom_attribute3_text_grouped');
            }

            // Custom attribute 4 Text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shelving_3_yn')
                || ($field->getId() == 'pickpack_options_wonder_shelving_3_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Custom attribute 4','custom_attribute2_text_grouped custom_attribute3_text_grouped custom_attribute4_text_grouped');
            }

            // Custom attribute combined
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_combine_custom_attribute_yn')
                || ($field->getId() == 'pickpack_options_wonder_combine_custom_attribute_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Combine custom attributes','custom_attribute5_text_grouped');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_product_sku_barcode_yn') ||
                ($field->getId() == 'pickpack_options_wonder_product_sku_barcode_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Sku barcode','sku_barcode_text_grouped');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_product_sku_barcode_2_yn') ||
                ($field->getId() == 'pickpack_options_wonder_product_sku_barcode_2_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Sku barcode 2','sku_barcode_text_grouped');
            }


            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_qty_title')  ||
                ($field->getId() == 'pickpack_options_wonder_qty_title')
            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Qty','qty_text_grouped');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_product_qty_backordered_yn') ||
                ($field->getId() == 'pickpack_options_wonder_product_qty_backordered_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Qty backordered','qty_backordered_text_grouped');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_product_stock_qty_yn') ||
                (($field->getId() == 'pickpack_options_wonder_product_stock_qty_yn'))


            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Stock qty','stock_qty_text_grouped');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_product_images_yn') ||
                ($field->getId() == 'pickpack_options_wonder_product_images_yn')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Image','image_text_grouped');
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_show_product_name') ||
                ($field->getId() == 'pickpack_options_wonder_show_product_name')

            ) {
                $html .= $this->_getFieldsContainerHeaderWithClassNoneborder('Name','name_text_grouped');
            }


            if (
                ($field->getId() == 'pickpack_options_wonder_show_customs_declaration') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_show_customs_declaration')

            ) {
                if(!(Mage::helper('pickpack')->isInstalled('Moogento_Cn22')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('Cn22',' ',1);
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_erp_pdf_replace_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_erp_pdf_replace_yn')

            ) {
                if(!(Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation')))
                    $html .= $this->_getFieldsContainerHeaderWithClassAndStatus('MDN Pdfs',' ',1);
            }

            //////////////////group for product separated//////////////////////////////

            $html .= $field->toHtml();
            // Close sku text format
            if (
                ($field->getId() == 'pickpack_options_wonder_pricesN_skuX') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_pricesN_skuX')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            // Close sku barcode text format
            if (
                ($field->getId() == 'pickpack_options_wonder_pricesN_barcodeX') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_pricesN_barcodeX')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }

            // Close sku barcode 2nd text format
            if (
                ($field->getId() == 'pickpack_options_wonder_pricesN_barcodeX_2') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_pricesN_barcodeX_2')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }

            // Close qty text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_show_qty_options') ||
                ($field->getId() == 'pickpack_options_wonder_show_qty_options')

            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            // Close text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_tickbox2_width') ||
                ($field->getId() == 'pickpack_options_wonder_tickbox2_width')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }
            // Close text Nmuberlist
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_numbered_product_list_bundle_children_X') ||
                ($field->getId() == 'pickpack_options_wonder_numbered_product_list_bundle_children_X')
            ) {
                $html .= $this->_getFieldsContainerFooter3();
            }

            // Close Product Price Line
            if (
                ($field->getId() == 'pickpack_options_wonder_product_line_total_with_discount_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_product_line_total_with_discount_yn')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }
            // Close Total Price
            if (
                ($field->getId() == 'pickpack_options_wonder_subtotal_order') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_subtotal_order')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            // Close allowance  text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_show_allowance_xpos') ||
                ($field->getId() == 'pickpack_options_wonder_show_allowance_xpos')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            //Close custom attribute  text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shelving_real_image') ||
                ($field->getId() == 'pickpack_options_wonder_shelving_real_image')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            //Close custom attribute 2 text format
            if (
                ($field->getId() == 'pickpack_options_wonder_shelving_2_image') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_shelving_2_image')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            // Close custom attribute 3 text format
            if (
                ($field->getId() == 'pickpack_options_wonder_shelving_3_image') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_shelving_3_image')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }
            // Close custom attribute 4 text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shelving_4_image') ||
                ($field->getId() == 'pickpack_options_wonder_shelving_4_image')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            //  Close combine custom attribute text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_combine_custom_attribute_Xpos') ||
                ($field->getId() == 'pickpack_options_wonder_combine_custom_attribute_Xpos')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            // Close  stock text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_pricesN_stockqtyX') ||
                ($field->getId() == 'pickpack_options_wonder_pricesN_stockqtyX')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            //Close backordered text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_prices_qtybackorderedX') ||
                ($field->getId() == 'pickpack_options_wonder_prices_qtybackorderedX')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }


            //Close warehouse text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_prices_warehouseX') ||
                ($field->getId() == 'pickpack_options_wonder_prices_warehouseX')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_show_customs_declaration') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_show_customs_declaration')

            ) {
                if(!(Mage::helper('pickpack')->isInstalled('Moogento_Cn22')))
                    $html .= $this->_getFieldsContainerFooter();
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_erp_pdf_replace_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_erp_pdf_replace_yn')

            ) {
                if(!(Mage::helper('pickpack')->isInstalled('MDN_Orderpreparation')))
                    $html .= $this->_getFieldsContainerFooter();
            }

            //Close images text format
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_pricesN_images_priceX') ||
                ($field->getId() == 'pickpack_options_wonder_pricesN_images_priceX')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }

            //Close price text format
            if (
            ($field->getId() == 'pickpack_options_wonder_invoice_pricesN_productX')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_pricesN_productX') ||
                ($field->getId() == 'pickpack_options_wonder_prices_productX')
            ) {
                $html .= $this->_getFieldsContainerFooter4();
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_gift_wrap_style_yn') ||
                ($field->getId() == 'pickpack_options_wonder_gift_wrap_style_yn')
            ) {
                if((!(Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap'))) && !(Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')))
                    $html .= $this->_getFieldsContainerFooter();
            }
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_shipment_temando_comment_yn') ||
                ($field->getId() == 'pickpack_options_wonder_shipment_temando_comment_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Temando_Temando')) || !(Mage::helper('pickpack')->isInstalled('Idev_OneStepCheckout')))
                    $html .= $this->_getFieldsContainerFooter();
            }
            if (
                ($field->getId() == 'pickpack_options_wonder_order_custom_attribute_filter') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_order_custom_attribute_filter')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Amasty_Orderattr')))
                    $html .= $this->_getFieldsContainerFooter();
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_shipment_details_shipping_options_filter') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_shipment_details_shipping_options_filter')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Magalter_Customshipping')))
                    $html .= $this->_getFieldsContainerFooter();
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_order_custom_delivery_date_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_order_custom_delivery_date_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('Amasty_Deliverydate')))
                    $html .= $this->_getFieldsContainerFooter();
            }

            if (
                ($field->getId() == 'pickpack_options_wonder_order_mw_custom_delivery_date_yn') ||
                ($field->getId() == 'pickpack_options_wonder_invoice_order_mw_custom_delivery_date_yn')
            )
            {
                if(!(Mage::helper('pickpack')->isInstalled('MW_Ddate')))
                    $html .= $this->_getFieldsContainerFooter();
            }


            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_required_stock_3rd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_required_stock_3rd')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            // Close Additional Action Config 1
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_szy_custom_value3') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_szy_custom_value3')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }

            // Close Additional Action Config 2
            if (
                ($field->getId() == 'pickpack_options_wonder_invoice_auto_processing_szy_own_value3_2nd') ||
                ($field->getId() == 'pickpack_options_wonder_auto_processing_szy_own_value3_2nd')
            ) {
                $html .= $this->_getFieldsContainerFooter();
            }



        }

        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}
