<?php
/**
 * 
 * Date: 20.12.15
 * Time: 14:53
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Aw_Sarp extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Module_Abstract
{
    public $y;
    public $subheader_start;
    public $order_notes_was_set;

    public function showPostmanNotice() {
        $page = $this->getPage();
        $order = $this->getOrder();
        $generalConfig = $this->getGeneralConfig();

        $notice_obj = Mage::getModel('sarp/notice')->load($order->getId(), 'order_id');
        $notice = nl2br($notice_obj->getNotice());
        if (strlen($notice) > 0) {
            $notice_line = array();
            $notice_line_count = 0;
            $notice_line = wordwrap($notice, 114, "\n", false);
            $i = 0;
            $this->y -= ($generalConfig['font_size_body'] + 4);
            $this->_setFont($page, 'bold', ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);
            $page->drawText('Postman Notice', ($this->addressXY[0]), $this->y);
            $this->y -= ($generalConfig['font_size_body'] + 3);
            $this->_setFont($page, $generalConfig['font_style_body'], ($generalConfig['font_size_body'] - 1), $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], $generalConfig['font_color_body']);

            $i = 0;
            $line_count = 0;
            $token = strtok($notice_line, "\n");
            while ($token != false) {
                $page->drawText(trim($token), ($this->addressXY[0]), $this->y);
                $this->y -= 10;
                $token = strtok("\n");
                $line_count++;
            }
            $this->order_notes_was_set = true;
            $i++;

            $this->subheader_start -= (($generalConfig['font_size_body']) * ($line_count + 2));
            unset($notice_line);
            unset($notice_obj);
            unset($notice);
        }
    }
}