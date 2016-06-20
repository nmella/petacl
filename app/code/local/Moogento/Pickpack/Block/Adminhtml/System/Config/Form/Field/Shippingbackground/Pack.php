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
* File        Pack.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Field_Shippingbackground_Pack
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    public function _getTableHtml($element) {
        $html = '';
        if( !(Mage::helper('pickpack')->isInstalled("Moogento_CourierRules")) && Mage::helper('pickpack')->isInstalled("Ess_M2ePro")){
            $html .='<p><span>You need <b style="color:red;">courierRules</b> to correctly match m2epro-generated shipping methods.</span></p>';
        }

        $html .= '<table class="status-group border" cellpadding="0" cellspacing="0">';
        /**
         * Table Head
         */
        $html .= '<thead>';
            $html .= '<tr class="headings">';
                $html .= '<th>Name</th>';
                $html .= '<th>Type <span class="required">*</span></th>';
                $html .= '<th>Pattern <span></th>';
                $html .= '<th>X-Nudge</th>';
                $html .= '<th>Y-Nudge</th>';
                $html .= '<th>Priority</th>';
                $html .= '<th>Image</th>';
                $html .= '<th style="width: 30px">&nbsp;</th>';
            $html .= '</tr>';
        $html .= '</thead>';


        /**
         * Table Body
         */
        $html .= '<tbody>';
        $html .= '</tbody>';


        /**
         * Table Foot
         */
        $html .= '<tfoot>';
            $html .= '<tr>';
                $html .= '<td colspan="5" class="a-right">';
                    $html .= '<button id="pack-add-new-shipping-method-group" type="button" class="scalable add add-select-row"><span><span><span>Add New Shipping Method Group</span></span></span></button>';
                $html .= '</td>';
            $html .= '</tr>';
        $html .= '</tfoot>';

        $html .= '</table>';
        $statusesHtml = '';
        if ((Mage::helper('pickpack')->isInstalled('Moogento_CourierRules')))
        {
            $statusesHtml .= '<option value=\'shipping_method\'>Shipping Method (default)</option>';
            $statusesHtml .= '<option value=\'courier_rules\'>courierRules Method</option>';
            $statusesHtml .= '<option value=\'shipping_zone\'>Shipping Zone</option>';
        }else{
            $statusesHtml .= '<option value=\'shipping_method\'>Shipping Method</option>';
            $statusesHtml .= '<option value=\'country_group\'>Country Group</option>';
        }


        $countryGroups = Mage::getStoreConfig('moogento_shipeasy/country_groups');
        $countryGroup = '';
        if ((Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy')))
        {
            $countryGroups = Mage::getStoreConfig('moogento_shipeasy/country_groups');
            if(is_array($countryGroups)){
                foreach($countryGroups as $code => $label){
                    $pos = strpos($code, 'label');
                    if ($pos !== false) {
                        $countryGroup.="<option value='".$code."'>".$label."</option>";
                    }

                }
            }
        }
        $html .= '<script type="text/javascript">var shippingMethodGrouperPack = new moogenthoShippingMethodGroupPack("'.$element->getHtmlId().'_value", "'.$statusesHtml.'","'.$countryGroup.'", "'.$element->getName().'");';

        if ($element->getValue()) {
            try {
                $value = unserialize($element->getValue());
            } catch (Exception $e) {
            }

            if (is_array($value)) {
                foreach($value as $rowId => $row_value) {
                    if (isset($row_value['file']) && $row_value['file']) {
                        $value[$rowId]['image'] = "Current Image: <img width='100px'src='".Mage::getBaseUrl('media').'moogento/pickpack/image_background/'.$row_value['file']."'><br>";
                    } else {
                        $value[$rowId]['image'] = '';
                    }
                }
                $html .= 'shippingMethodGrouperPack.initValues('.Mage::helper('core')->jsonEncode($value).');';
            }
        }
        $html .= '</script>';
        return $html;
    }

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $id = $element->getHtmlId();
        $useContainerId = $element->getData('use_container_id');
        $html = '<tr id="pack_row_' . $id . '">'
              . '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';
        $isMultiple = $element->getExtType()==='multiple';
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $options = $element->getValues();

        $addInheritCheckbox = false;
        if ($element->getCanUseWebsiteValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        }
        elseif ($element->getCanUseDefaultValue()) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        $html.= '<td class="value" id="'.$id.'_value">';
        $html.= $this->_getElementHtml($element) . $this->_getTableHtml($element);
        if ($element->getComment()) {
            $html.= '<p class="note"><span>'.$element->getComment().'</span></p>';
        }
        $html.= '</td>';

        if ($addInheritCheckbox) {

            $defText = $element->getDefaultValue();
            if ($options) {
                $defTextArr = array();
                foreach ($options as $k=>$v) {
                    if ($isMultiple) {
                        if (is_array($v['value']) && in_array($k, $v['value'])) {
                            $defTextArr[] = $v['label'];
                        }
                    } elseif ($v['value']==$defText) {
                        $defTextArr[] = $v['label'];
                        break;
                    }
                }
                $defText = join(', ', $defTextArr);
            }
            $html.= '<td class="use-default">';
            $html.= '<input id="'.$id.'_inherit" name="'.$namePrefix.'[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '.$inherit.' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
            $html.= '<label for="'.$id.'_inherit" class="inherit" title="'.htmlspecialchars($defText).'">'.$checkboxLabel.'</label>';
            $html.= '</td>';
        }

        $html.= '<td class="scope-label">';
        if ($element->getScope()) {
            $html .= $element->getScopeLabel();
        }
        $html.= '</td>';

        $html.= '<td class="">';
        if ($element->getHint()) {
            $html.= '<div class="hint" >';
            $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
            $html.= '</div>';
        }
        $html.= '</td>';

        $html.= '</tr>';
        return $html;
    }
}
