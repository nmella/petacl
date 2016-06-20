<?php
/**
 * 
 * Date: 14.12.15
 * Time: 18:15
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Trolley extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public function showTrolley($orderItemsArr) {
        $black_color = Mage::helper('pickpack/config_color')->getPdfColor('black_color');
        $order = $this->getOrder();
        $page = $this->getPage();
        $generalConfig = $this->getGeneralConfig();

        $orderId_font_size = $this->_getConfig('font_size_orderid', 14, false, $this->getWonder(), $this->getStoreId());

        foreach($orderItemsArr as $trolley_item_data) {
            if($trolley_item_data['db_order_id'] == $order->getId()) {
                $order_trolley_data =$trolley_item_data;
                break;
            }
        }

        if(isset($order_trolley_data)) {
            $storeID_trolley = $order_trolley_data['store_id'];
            $trolley_text_nudge = explode(",", $this->_getConfigTrolley('pickpack_title_position', '30,810', false, 'trolleybox_picklist', $storeID_trolley));
            $showTrolleyText = 1;

            if ($showTrolleyText == 1) {
                $trolley_color = new Zend_Pdf_Color_GrayScale(1.0);
                $page->setFillColor($trolley_color);
                $page->setLineColor($black_color);
                $page->setLineWidth(1.2);
                $trolley_text_nudge[0] = trim((int)$trolley_text_nudge[0]);
                $trolley_text_nudge[1] = trim((int)$trolley_text_nudge[1]);
                $trolley_id = $order_trolley_data['trolleybox_trolley_id'];
                $extra_space = 45;

                if($trolley_id >= 100) {
                    $page->drawRectangle($trolley_text_nudge[0], $trolley_text_nudge[1], $trolley_text_nudge[0] + 60, $trolley_text_nudge[1]+40);
                    $page->drawRectangle($trolley_text_nudge[0]+3, $trolley_text_nudge[1]+3, $trolley_text_nudge[0] + 57, $trolley_text_nudge[1]+37);
                    $this->_setFont($page, $generalConfig['font_style_body'], $orderId_font_size*2, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $page->drawText($trolley_id, $trolley_text_nudge[0]+6, $trolley_text_nudge[1]+10, 'UTF-8');
                    $extra_space = 70;
                }
                elseif($trolley_id >= 10) {
                    $page->drawRectangle($trolley_text_nudge[0], $trolley_text_nudge[1], $trolley_text_nudge[0] + 45, $trolley_text_nudge[1]+40);
                    $page->drawRectangle($trolley_text_nudge[0]+3, $trolley_text_nudge[1]+3, $trolley_text_nudge[0] + 42, $trolley_text_nudge[1]+37);
                    $this->_setFont($page, $generalConfig['font_style_body'], $orderId_font_size*2, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $page->drawText($trolley_id, $trolley_text_nudge[0]+6, $trolley_text_nudge[1]+10, 'UTF-8');
                    $extra_space = 55;
                }
                else {
                    $page->drawRectangle($trolley_text_nudge[0], $trolley_text_nudge[1], $trolley_text_nudge[0] + 40, $trolley_text_nudge[1]+40);
                    $page->drawRectangle($trolley_text_nudge[0]+3, $trolley_text_nudge[1]+3, $trolley_text_nudge[0] + 37, $trolley_text_nudge[1]+37);
                    $this->_setFont($page, $generalConfig['font_style_body'], $orderId_font_size*2, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                    $page->drawText($trolley_id, $trolley_text_nudge[0]+12, $trolley_text_nudge[1]+10, 'UTF-8');
                    $extra_space = 55;
                }
                $page->setLineWidth(2.5);
                $page->setFillColor($trolley_color);
                $page->drawRectangle($trolley_text_nudge[0]+$extra_space, $trolley_text_nudge[1], $trolley_text_nudge[0] + $extra_space + 38, $trolley_text_nudge[1]+25);
                $page->setLineColor($trolley_color);
                $page->drawRectangle($trolley_text_nudge[0]+$extra_space-3, $trolley_text_nudge[1]+8, $trolley_text_nudge[0] + $extra_space + 38 +3, $trolley_text_nudge[1]+28);
                $box_id = $order_trolley_data['trolleybox_box_id'];
                $this->_setFont($page, $generalConfig['font_style_body'], $orderId_font_size*1.7, $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
                if($box_id < 10)
                    $page->drawText($box_id, $trolley_text_nudge[0] +$extra_space +12, $trolley_text_nudge[1]+8, 'UTF-8');
                else
                    $page->drawText($box_id, $trolley_text_nudge[0] +$extra_space +7, $trolley_text_nudge[1]+8, 'UTF-8');
            }
        }
        /**END PRINTING Trolley Title**/
    }

    protected function _getConfigTrolley($field, $default = '', $add_default = true, $group = 'trolleybox_picklist', $store = null, $trim = true,$section = 'trolleybox_options') {
        if($group=='general')
        {
            return parent::_getConfig($field,$default,$add_default,$group,$store);
        }
        if ($trim)
            $value = trim(Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store));
        else
            $value = Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store);
        if (strstr($field, '_color') !== FALSE) {
            if ($value != 0 && $value != 1) {
                $value = checkColor($value);
            }
        }

        if ($value == '') {
            return $default;
        } else {
            if ($field == 'csv_field_separator' && $value == ',')
                return $value;
            if (($value !== '') && (strpos($value, ',') !== false) && (strpos($default, ',') !== false))
            {
                $values   = explode(",", $value);
                $defaults = explode(",", $default);

                if ($add_default === true) {
                    $value         = '';
                    $count         = 0;
                    $default_count = count($defaults);
                    foreach ($defaults as $i => $v) {
                        if (($count != ($default_count)) && ($count != 0))
                            $value .= ',';
                        if (isset($values[$i]) && $values[$i] != '')
                            $value .= ($values[$i] + $defaults[$i]);
                        else
                            $value .= $v;
                        $count++;
                    }
                } else {
                    $value         = '';
                    $count         = 0;
                    $default_count = count($defaults);
                    foreach ($defaults as $i => $v) {
                        if (($count != ($default_count)) && ($count != 0))
                            $value .= ',';
                        if (isset($values[$i]) && $values[$i] != '')
                            $value .= $values[$i];
                        else
                            $value .= $v;
                        $count++;
                    }
                }
            } else {
                $value = ($add_default) ? ($value + $default) : $value;
            }
            return $value;
        }
    }
}