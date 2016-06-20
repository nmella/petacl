<?php

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Labelzebra_OrderNotes extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    protected $generalConfig = 0;
    protected $zebralabelConfig = 0;

    protected $notesArray = array();
    protected $line_count_note = 0;

    const PADDING_NOTE_LEFT = 10;
    const PADDING_NOTE_RIGHT = 10;

    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->generalConfig = $arguments[2];
        $this->zebralabelConfig = $arguments[3];
    }

    public function showOrderNotes(){
        $this->notesArray = $this->getOrderNotesData();
        if ($this->line_count_note){
            $this->checkForNewZebraPage();
            $this->printOrderNotes($this->notesArray);
        }
    }

    private function getOrderNotesData(){
        $order = $this->getOrder();
        $storeId= $this->getStoreId();
        $wonder = $this->getWonder();

        $note_line = array();

        $font_size_message = $this->generalConfig['font_size_message'];
        $strip_comment_line_break = $this->_getConfig('strip_comment_line_break', 0, false, 'general', $storeId);

        $notes_filter_options = $this->zebralabelConfig['order_notes_filter_options'];

        if ($notes_filter_options != 'yestext') $notes_filter = '';
        else{
            $notes_filter = trim(strtolower($this->_getConfig('notes_filter', '', false, $wonder, $storeId)));
            $notes_filter = preg_replace('/^([\'"])(.*)\\1$/', '\\2', $notes_filter);
        }

        if ($order->getStatusHistoryCollection(true)) {
            $notes = $order->getStatusHistoryCollection(true);
            $note_line = array();
            $note_comment_count = 0;
            $test_name = 'abcdefghij'; //10
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
            $test_name_length = round($this->parseString($test_name, $font_temp, ($this->generalConfig['font_size_message'])));
            $pt_per_char = ($test_name_length / 10);
            $max_name_length = $this->page_padding['right'] + self::PADDING_NOTE_RIGHT - $this->page_padding['left'] + self::PADDING_NOTE_LEFT;
            $character_breakpoint = round(($max_name_length / $pt_per_char));
            $i = 0;
            $this->line_count_note = 0;

            foreach ($notes as $_item) {
                if ($notes_filter_options == 'yestext' && ($this->checkFilterNotes($_item['comment'], $notes_filter))) {
                    $_item['comment'] = '';
                }

                if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
                    $check_comments_for_gift_message_filter = $this->_getConfig('check_comments_for_gift_message_filter', 'Checkout Message', false, $this->getWonder(), $this->getStoreId());
                    $pos = strpos($_item['comment'], 'M2E Pro Notes');
                    $pos2 = strpos($_item['comment'], $check_comments_for_gift_message_filter);
                    if (($pos !== false) && ($pos2 !== false)) {
                        $start_pos1 = strlen('M2E Pro Notes') + 1;
                        $start_pos2 = strlen('Checkout Message From Buyer:') + 1;
                        $str_1 = trim(substr($_item['comment'], $start_pos1));
                        $str_2 = trim(substr($str_1, $start_pos2));
                        $this->gift_message_array['notes'][] = $str_2;
                        $_item['comment'] = '';
                    } else
                        if ($pos !== false) {
                            $_item['comment'] = str_replace('M2E Pro Notes:', '', $_item['comment']);
                        }
                }

                if(Mage::helper('pickpack')->isInstalled('Brainvire_OrderComment')){
                    if($_item['is_customer_notified'] != 0)
                        $_item['is_visible_on_front'] = 1;
                }

                if ($_item['comment'] != '' && (($notes_filter_options == 'yesfrontend' && $_item['is_visible_on_front'] == 1)
                        || ($notes_filter_options == 'no'
                            || $notes_filter_options == 'yestext'))
                ) {
                    $_item['created_at'] = date('m/d/y', strtotime($_item['created_at']));
                    if($strip_comment_line_break == 1){
                        $_item['comment'] = preg_replace('/\s+/', ' ', $_item['comment']);
                    }

                    //if order currency is different with store currency then
                    //change current to store currency
                    $str = Mage::helper('pickpack')->__('Because the Order currency is different from the Store currency, the conversion from');
                    $str_to = Mage::helper('pickpack')->__('Prices converted from');
                    $_item['comment'] = str_replace($str,$str_to,$_item['comment']);
                    $order_currency_code = $order->getOrderCurrencyCode();
                    $store_currency_code = $order->getStore()->getCurrentCurrencyCode();
                    $str = Mage::helper('pickpack')->__('"'.$order_currency_code.'" to "'.$store_currency_code.'"');
                    $str_to = Mage::helper('pickpack')->__('"'.$store_currency_code.'" to "'.$order_currency_code.'"');
                    $_item['comment'] = str_replace($str,$str_to,$_item['comment']);

                    preg_match_all('/\d+\.\d+/',  $_item['comment'], $matches);
                    $num = $matches[0];
                    if(isset($num[0]) && ($num[0] > 0)) {
                        $str = Mage::helper('pickpack')->__('was performed using '. (float)$num[0] .' as a rate');
                        $str_to = Mage::helper('pickpack')->__('@ '.(float)$num[0]);
                        $_item['comment'] = str_replace($str,$str_to,$_item['comment']);
                    }

                    //filter note by visible on fontend option
                    if ($this->zebralabelConfig['order_notes_filter_options']== 'yesfrontend' && $_item['is_visible_on_front'] == 0)
                        continue;

                    $note_line[$i]['date'] = $_item['created_at'];
                    $note_line[$i]['comment'] = $this->wordWrapNoteComments($_item['created_at'] . ' : ' . $_item['comment'],$character_breakpoint);
                    $note_line[$i]['is_visible_on_front'] = $_item['is_visible_on_front'];

                    $i++;
                }
            }
        }

        return $note_line;
    }

    private function checkForNewZebraPage(){
        $pdf = $this->getPdf();
        $page = $this->getPage();

        $page_width = $page->getWidth();
        $page_height = $page->getHeight();

        $height_of_block = $this->caculateHeightOfCommentBlock();
        if ($this->y - $height_of_block < $this->page_padding['bottom']){
            $page_demension        = $page_width . ':' . $page_height;
            $settings['page_size'] = $page_demension;
            $pdf->newPageZebra($settings);
            $this->y = $this->page_padding['top'];
        }
    }

    private function caculateHeightOfCommentBlock(){
        $top_padding = $this->generalConfig['font_size_message'] * 0.2;
        $botom_padding = $this->generalConfig['font_size_message'] * 0.5;
        $height = $this->line_count_note * $this->generalConfig['font_size_message'] + $top_padding + $botom_padding;
        if ($this->zebralabelConfig['order_notes_title'] != '')
            $height += $this->generalConfig['font_size_message'];

        return $height;
    }

    private function wordWrapNoteComments($note_line,$character_breakpoint){
        $note_array = array();
        $note_line_break = preg_split ('/$\R?^/m', $note_line);
        foreach ($note_line_break as $note_line_each){
            if ($note_line_each != "") {
                $note_line_each = trim($note_line_each);
                $note_line_wr = wordwrap($note_line_each, $character_breakpoint, "\n", false);
                $comment_array = explode("\n", $note_line_wr);
                $note_array = array_merge($note_array, $comment_array);
            }
        }
        $this->line_count_note += count($note_array);
        return $note_array;
    }

    private function printOrderNotes($note_line = array()){
        $page = $this->getPage();
        $storeId= $this->getStoreId();
        $helper = Mage::helper('pickpack');

        $bkg_color_message = trim($this->_getConfig('bkg_color_message', '#5BA638', false, 'general', $storeId));
        $bkg_color_message_zend = new Zend_Pdf_Color_Html($bkg_color_message);

        if ($this->line_count_note) {

            //print background block
            if (($bkg_color_message_zend != '') && ($bkg_color_message_zend != '#FFFFFF')) {
                $page->setFillColor($bkg_color_message_zend);
                $page->setLineColor($bkg_color_message_zend);
                $page->setLineWidth(0.5);
                $x1 = $this->page_padding['left'];
                $y1 = $this->y;
                $x2 = $this->page_padding['right'];
                $y2 = $this->y - $this->caculateHeightOfCommentBlock();
                $page->drawRectangle($x1, $y1, $x2, $y2);
                $this->y -= $this->generalConfig['font_size_message'] * 1.2;
            }

            //print order note title first
            if ($this->zebralabelConfig['order_notes_title'] != ''){
                $this->_setFont($page, 'bold', ($this->generalConfig['font_size_message']), $this->generalConfig['font_family_message'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_message']);
                $page->drawText($helper->__($this->zebralabelConfig['order_notes_title']), ($this->page_padding['left'] + 10), $this->y, 'UTF-8');
                $this->y -= ($this->generalConfig['font_size_message']);
            }

            //print order comment
            sksort($note_line, 'date', true);
            $this->_setFont($page, $this->generalConfig['font_style_message'], $this->generalConfig['font_size_message'], $this->generalConfig['font_family_comments'], $this->_general['non_standard_characters'], $this->generalConfig['font_color_message']);
            foreach ($note_line as $note){
                foreach ($note['comment'] as $comment_line){
                    $page->drawText($comment_line, ($this->page_padding['left'] + 10), $this->y, 'UTF-8');
                    $this->y -= $this->generalConfig['font_size_message'];
                }
            }
            //add space for next item
            $this->y -= $this->generalConfig['font_size_message'] * 0.5;
        }
    }
}