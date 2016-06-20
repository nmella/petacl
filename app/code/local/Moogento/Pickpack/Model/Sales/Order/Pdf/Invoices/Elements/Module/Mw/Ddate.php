<?php
/**
 * 
 * Date: 20.12.15
 * Time: 16:27
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Mw_Ddate extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Abstract
{
    public $customer_comments;
    public $subheader_start;
    public $y;

    public function showDeliveryDate() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $generalConfig = $this->getGeneralConfig();

        $background_color_subtitles = trim($this->_getConfig('background_color_subtitles', '#5BA638', false, 'general', $storeId));
        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($background_color_subtitles);

        $ddate = Mage::getResourceModel('ddate/ddate')->getDdateByOrder($order->getIncrementId());
        if($ddate) {
            if(!empty($ddate['dtime'])){$Tm=$ddate['dtime'];}else{$Tm=$ddate['dtimetext'];}
            $fields = array('date'=>$ddate['ddate'],'time'=>$Tm);
            if (is_array($fields) && (!empty($fields))) {
                $list = array();
                foreach ($fields as $key=>$field) {
                    $value = $field;
                    if ('date' == $key) {
                        $label = 'Delivery Date';
                        if ('0000-00-00' != $value) {
                            $value = Mage::helper('ddate')->format_ddate($value);
                        } else {
                            $value = '';
                        }
                    } elseif ('time' == $key) {
                        $label = 'Delivery Time';
                    }
                    if (is_array($value)) {
                        $list[$label] = $value;
                    } elseif ($value) {
                        $list[$label] = $value;
                    }
                }
                if (!strlen($this->customer_comments) > 0) {
                    $this->y -= ($generalConfig['font_size_body'] - 80);
                }
                $this->y -= ($generalConfig['font_size_body'] + 10);

                $page->setFillColor($background_color_subtitles_zend);
                $page->setLineColor($background_color_subtitles_zend);
                $page->setLineWidth(0.5);
                $page->drawRectangle($pageConfig['padded_left'], ($this->y - ($generalConfig['font_size_subtitles'] / 2)), $pageConfig['padded_right'], ($this->y + $generalConfig['font_size_subtitles'] + 2));

                $this->_setFont($page, $generalConfig['font_style_subtitles'], $generalConfig['font_size_subtitles'], $generalConfig['font_family_subtitles'], $generalConfig['non_standard_characters'], $generalConfig['font_color_subtitles']);
                $page->drawText('Delivery Date', ($this->addressXY[0]), $this->y);
                $this->y -= ($generalConfig['font_size_body'] + 15);
                $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] + 2), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

                $line_count = 0;
                $this->subheader_start -= (($generalConfig['font_size_body']) * ($line_count + 2));
                if (!empty($list)) {
                    foreach ($list as $label => $value) {
                        if (is_array($value)) {
                            $page->drawText($label . ': ', $this->addressXY[0], $this->y, 'UTF-8');
                            foreach ($value as $str) {
                                $page->drawText($str, $this->addressXY[0] + 160, $this->y, 'UTF-8');
                                $this->y -= 12;
                                $line_count++;
                            }
                        } else {
                            $page->drawText($label . ': ', $this->addressXY[0], $this->y, 'UTF-8');
                            $page->drawText($value, $this->addressXY[0] + 160, $this->y, 'UTF-8');
                            $this->y -= 10;
                            $line_count++;
                        }
                    }
                }
                $this->subheader_start -= (($generalConfig['font_size_body']) * ($line_count + 2));
            }
        }
    }
}