<?php

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Labelzebra extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    protected $_config = array();
    protected $_base_position = array(); //this will help caculate print position of text after rotate
    protected $_label_width = 0;
    protected $_label_height = 0;
    public $_order;
    protected $generalConfig = 0;
    protected $zebralabelConfig = 0;
    protected $page_padding = array();

    protected $product_line_array_xpos = array(); //this will help store xpos of each element in product line

    protected $_charactedMap = array(
        'from' => array(
            'Š',
            'α',
			'₹',
        ),
        'to' => array(
            'S',
            '(alpha)',
	        'R',
        ),
    );
	
    public function __construct() {
        parent::__construct();
        parent::setWonder('label_zebra');
    }

    public function setCurrentOrder($order) {
        $this->_order = $order;
    }

    public function getOrder() {
        return $this->_order;
    }

    public function setStoreId($storeId) {
        parent::setStoreId($storeId);
    }

    public function getConfigValue($storeId){
        if ($storeId === null) $storeId = Mage::app()->getStore()->getStoreId();
        $this->generalConfig = Mage::helper('pickpack/config')->getGeneralConfigArray($storeId);
        $this->zebralabelConfig = Mage::helper('pickpack/config')->getZebraLabelConfigArray($storeId);

        $page_size = $this->zebralabelConfig['nudge_demension_zebra'];
        $page_padding = $this->zebralabelConfig['paper_margin_zebra'];

        $this->page_padding['top'] = $page_size[1] - $page_padding[0];
        $this->page_padding['right'] = $page_size[0] - $page_padding[1];
        $this->page_padding['bottom'] = $page_padding[2];
        $this->page_padding['left'] = $page_padding[3];

        $productsElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_labelzebra_products', array($this,$this->getOrder(),$this->generalConfig,$this->zebralabelConfig));
        $this->product_line_array_xpos = $productsElement->caculateProductElementArrayXpos();
    }

    private function sortByChild($itemCollection, $keysort, $sortorder = 'SORT_ASC') {
        $typesort = array();
        foreach ($itemCollection as $key => $row) {
            $typesort[$key] = $row[$keysort];
        }
        if ($sortorder == "ascending")
            $sortorder = SORT_ASC;
        elseif ($sortorder == "descending")
            $sortorder = SORT_DESC;
        array_multisort($typesort, $sortorder, SORT_STRING, $itemCollection);
        return $itemCollection;
    }
	
    private function sortitems($sort_packing, $shelving_real_attribute, $itemCollection, $sortorder = 'SORT_ASC') {
        if ($sort_packing == 'sku') {
            $itemCollection = $this->sortByChild($itemCollection, 'sku', $sortorder);
        } elseif ($sort_packing == 'attribute') {
            $itemCollection = $this->sortByChild($itemCollection, $shelving_real_attribute, $sortorder);
        }
        return $itemCollection;
    }
	
    private function getAttributeValueZebra($shelving_real_attribute, $item, $shelving_real_attribute_yn){
        $attributeName  = $shelving_real_attribute;
        if($shelving_real_attribute_yn == 1){
            $simple_sku       = $item->getSku();
            $_product_temp = Mage::getModel('catalog/product');
            $simpleProductId = $_product_temp->getIdBySku($simple_sku);
            $product        = Mage::helper('pickpack')->getProduct($simpleProductId);
        }else
            $product        = Mage::helper('pickpack')->getProduct($item->getProductId());

        if ($attributeName == '%category%') {
            $catCollection = $product->getCategoryCollection();

            $categsToLinks = array();
            # Get categories names
            foreach ($catCollection as $cat) {
                if ($cat->getName() != '') {
                    $categsToLinks[] = $cat->getName();
                }
            }
            $category_label = implode(', ', $categsToLinks);
            $attributeValue = $category_label;
        } else{
            $attributeValue = Mage::helper('pickpack')->getProductAttributeValue($product,$attributeName);
        }
        return $attributeValue;
    }

	// check if the address template part has been set as caps, indicating an intent to make that part caps
	private function checkCapsIntent($check_str,$value) {
		$check_caps_intent = array();
		preg_match('~\{(.*)\}~i',$check_str,$check_caps_intent);
		if ( isset($check_caps_intent[1]) && (strtoupper($check_caps_intent[1]) == $check_caps_intent[1]) ) {
			unset($check_caps_intent);
			return $this->getCapitalize($value,true);
		}
		return $this->getCapitalize($value,false);
	}
	
    private function getCapitalize($value, $override_caps = false) {
        $valueC = '';
        switch ($this->_config['capitalize_zebra_yn']) {
            case 0:
				// could put it through reformatAddress as 'none' but probably not needed
                if($override_caps)
					$valueC = $this->reformatAddress($value,'capitals');
				else
					$valueC = $value;
                break;
            case 1:
	            if($override_caps)
					$valueC = $this->reformatAddress($value,'capitals');
				else
					$valueC = $this->reformatAddress($value,'uppercase');
                break;
            case 2:
				$valueC = $this->reformatAddress($value,'capitals');
                break;
        }
        return $valueC;		
    }
	
	/*
	This function to change address to Address ('uppercase') or to ADDRESS ('capitals') or to address ('none')
	'none' will still switch problem characters depending on the font family
	*/
	protected function reformatAddress($str, $change_to = 'none') {		
		switch ($change_to) {
			case 'capitals':
				return $this->capitalAddress($str);
				break;
				
			case 'uppercase':
				return $this->uppercaseAddress($str);
				break;
			
			case 'none':
			default:
				return $this->prepareText($str);
				break;
		}
	}
	 
	private function _mb_strtoupper($str) {
		if (!function_exists('mb_strtoupper'))
			return mb_strtoupper($str);
		else
			return strtoupper($str);
	}
	
    private function capitalAddress($str) {	
		$str = $this->prepareText($str);
		//$str = $this->_mb_strtoupper($str);	
		$str = mb_convert_case($str, MB_CASE_UPPER, 'UTF-8');
        return strtr($str, array("ß" => "SS"));
    }
	
    private function uppercaseAddress($str) {
		$str = $this->prepareText($str);
        //$str = ucfirst($str);
		$str = mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
        //$str = Mage::helper('pickpack/functions')->ucwords_specific( mb_strtolower($str, 'UTF-8'), "-'");
        return $str;
    }
	
    private function removeAccentsFromChars($str) {
		// Depending on font chosen, change characters
		if($this->generalConfig['remove_accents_from_chars_yn'] == 0)
			return $str;
		else
			return Mage::helper('pickpack/functions')->normalizeChars($str);
    }
	
    private function fixProblemCharacters($str) {
		// Depending on font chosen, change characters
		if($this->generalConfig['font_family_body'] == 'noto')
			return $str;
		else 
			return str_replace($this->_charactedMap['from'], $this->_charactedMap['to'], $str);
    }
	
    private function prepareText($str) {
		$str = $this->fixProblemCharacters($str);
		$str = $this->removeAccentsFromChars($str);
		return $str;
    }
	/**
	end repeated	
	*/
	
    private function getRotateReturnAddress($rotate_return_address) {

        switch ($rotate_return_address) {
            case 0:
                $rotate = 0;
                break;
            case 1:
                $rotate = deg2rad(90);
                break;
            case 2:
                $rotate = -deg2rad(90);
                break;
            default:
                $rotate = 0;
        }
        return $rotate;
    }

    private function drawLabelSummary($summary_zebra, $page, $temp_x, $temp_y, $font_size_product, $total_orders, $label_width, $unit_weight){
        $round_weight_by = $this->_getConfig('round_weight_by', 2, false, "label_zebra");
        $greyout_color = new Zend_Pdf_Color_GrayScale(0.6);
        $column1X = $temp_x;
        $column2X = $column1X + 120;
        $column3X = $column2X + 180;
        $column4X = $column3X + 60;
        $total_qty_items = 0;
        $total_weight = 0;
        $average_order_weight = 0;
        $average_order_qty = 0;
        foreach($summary_zebra as $key=>$summary){
            $total_weight += $summary["total_weight"];
            $total_qty_items += $summary["total_items"];
            if($summary["total_items"] > 1)
                $label_item = "items";
            else
                $label_item = "item";

            if($summary["total_orders"] > 1)
                $label_order = "orders";
            else
                $label_order = "order";
            $column1text=  '[' . $summary["group"] . ']';
            $column2text = round($summary["total_weight"], $round_weight_by) . $unit_weight .' (' . round($summary["total_weight"]/$summary["total_items"], $round_weight_by) . $unit_weight .' avg)';
            $column3text = $summary["total_items"] . ' ' . $label_item;
            $column4text = $summary["total_orders"] . ' ' . $label_order;

            $this->drawText($page,$column1text, $column1X, $temp_y, 'UTF-8');
            if ($this->parseString($column1text,$page->getFont(),$font_size_product + 5)+5 >= 120){   //this code will auto create new line when first string is too long
                $temp_y -= $font_size_product + 7;
            }
            $this->drawText($page,$column2text, $column2X, $temp_y, 'UTF-8');
            $this->drawText($page,$column3text, $column3X, $temp_y, 'UTF-8');
            $this->drawText($page,$column4text, $column4X, $temp_y, 'UTF-8');
            $temp_y -= $font_size_product + 7;
        }
        $total_valueX = $temp_x + 150;
        $average_order_qty = round($total_qty_items / $total_orders, $round_weight_by);
        $average_order_qty > 1 ? $label_avr_qty="items" : $label_avr_qty="item";
        $page->setLineWidth(1);
        $page->setLineColor($greyout_color);
        $this->drawLine($page,$temp_x, $temp_y, $label_width , $temp_y);

        $temp_y -= $font_size_product + 10;
        $this->drawText($page,"Total Orders	:", $temp_x, $temp_y, 'UTF-8');
        $this->drawText($page,$total_orders, $total_valueX, $temp_y, 'UTF-8');
        $temp_y -= $font_size_product + 8;
        $this->drawText($page,"Total Qty Items	:", $temp_x, $temp_y, 'UTF-8');
        $this->drawText($page,$total_qty_items, $total_valueX, $temp_y, 'UTF-8');
        $temp_y -= $font_size_product + 8;
        $this->drawText($page,"Total Weight	:", $temp_x, $temp_y, 'UTF-8');
        $this->drawText($page,round($total_weight, $round_weight_by) . $unit_weight, $total_valueX, $temp_y, 'UTF-8');
        $temp_y -= $font_size_product + 8;
        $this->drawText($page,"Average Order Weight :", $temp_x, $temp_y, 'UTF-8');
        $this->drawText($page,round($total_weight / $total_orders, $round_weight_by) . $unit_weight, $total_valueX, $temp_y, 'UTF-8');
        $temp_y -= $font_size_product + 8;
        $this->drawText($page,"Average Order Qty :", $temp_x, $temp_y, 'UTF-8');
        $this->drawText($page,$average_order_qty . " " . $label_avr_qty, $total_valueX, $temp_y, 'UTF-8');
    }
    private function getMaxchars($font_size_return_label, $value, $label_height) {
        $font_temp      = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $max_line_width = ($label_height * 0.86);
        $value          = trim($value);
        $line_width     = $this->parseString($value, $font_temp, $font_size_return_label);
        $max_chars      = round($max_line_width / ($line_width / strlen($value)));
        return $max_chars;
    }
    // private function getShippingAddressFull($order, $font_size_label, $temp_x)
    // {
    // $address_full = '';
    // $i            = 0;
    // while ($i < 10) {
    // if ($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i))) {
    // $value             = trim($order->getShippingAddress()->getStreet($i));
    // $max_chars         = 20;
    // $font_temp         = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
    // $font_size_compare = ($font_size_label * 0.8);
    // $line_width        = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
    // $char_width        = $line_width / 10;
    // $max_chars         = 200;
    // $token             = strtok($value, "\n");
    // while ($token !== false) {
    // if (trim(str_replace(',', '', $token)) != '') {
    // $address_full .= trim($token) . ", ";
    // }
    // $token = strtok("\n");
    // }
    // }
    // $i++;
    // }

    // $address_full = trim($address_full, ',');
    // return $address_full;
    // }
    protected function _getTruncatedComment($comment,$length='trim') {
        $comment = str_replace('<br />','~',nl2br(trim($comment)));
        // Strip HTML Tags
        $comment = strip_tags($comment);
        // Clean up things like &amp;
        $comment = html_entity_decode($comment);
        // Strip out any url-encoded stuff
        $comment = urldecode($comment);
        // Replace non-AlNum characters with space
        $comment = preg_replace('/[^@A-Za-z0-9\.\,~:\-]/', ' ', $comment);

        $comment = str_ireplace(array('M2E Pro Notes:','','Checkout Message From '),'',$comment);
        $comment = preg_replace('/Because the Order currency is different (.*)$/i','',$comment);

        // uncomment for rates comments
        // $comment = str_ireplace(array('M2E Pro Notes:','Because the Order currency is different from the Store currency','the conversion from ','as a rate','Checkout Message From '),'',$comment);
        // $comment = str_replace(' was performed~  using','@',$comment);

        // Replace Multiple spaces with single space    
        $comment = preg_replace('/ +/', ' ', $comment);
        // Trim the string of leading/trailing space
        $comment = trim($comment);
        $comment = preg_replace('/[ \,@\;~]$/', '', $comment);
        $comment = preg_replace('/ \.$/', '', $comment);
        $comment = preg_replace('/^[~\s\,\.\;~]+/', '', $comment);
        $comment = str_replace(array('~~~','~~','~~','~'),'~',$comment);

        if($length == 'trim')
        {
            $truncate_at = Mage::getStoreConfig(self::XML_PATH_TRUNCATE);
            if($truncate_at < 5) $truncate_at = 5;
            if ($truncate_at < strlen($comment)) {

                $comment = trim(substr($comment, 0, $truncate_at)). '&hellip;';
                $comment = str_replace('~','<br />',$comment);
                return $comment;
            }
        }
        $comment = str_replace('~','&#10;',$comment); //&#13;
        $comment = preg_replace('/Buyer:\s?$/i','',$comment); //&#13;
        $comment = preg_replace('/&#10;\s?$/i','',$comment); //&#13;

        return trim($comment);
    }
    private function getCustomerComments($order){
        $comments = array();
        foreach ($order->getStatusHistoryCollection() as $k => $comment) {
            if (!$comment->getData('is_visible_on_front') && $comment->getComment() && !$comment->getData('is_customer_notified'))
            {
                //$commentText = $this->_getTruncatedComment($comment->getComment(),'trim');
                $commentText = $comment->getComment();

                if (strpos($commentText, '<br />') !== false) {
                    $commentStrings = explode('<br />', $commentText);
                    foreach ($commentStrings as $key => $string) {
                        $commentStrings[$key] = $this->_getTruncatedComment($string,'full');
                    }
                    $commentText = implode('<br />', $commentStrings);
                } else {
                    $commentText = $this->_getTruncatedComment($commentText,'full');
                }

                $commentTextTrim = substr(preg_replace('~[^a-zA-Z0-9]+~', '', $commentText),0,10);
                if(!isset($comments[$commentTextTrim]) && $commentTextTrim != '')
                {
                    $comments[$commentTextTrim]['time'] = strtotime($comment->getCreatedAtDate());
                    $comments[$commentTextTrim]['datetime'] = $comment->getCreatedAtDate();
                    $comments[$commentTextTrim]['text'] = Mage::helper('moogento_shipeasy/functions')->clean_method($commentText);
                    //if(strpos($comments[$commentTextTrim]['text'],'USD to GBP') !== false)
                    //$comments[$commentTextTrim]['text'] = '';
                    $comments[$commentTextTrim]['text_full'] = $this->_getTruncatedComment($comment->getComment(),'full');
                    $comments[$commentTextTrim]['count'] = 1;
                }
                else
                {
                    $thisTime = strtotime($comment->getCreatedAtDate());
                    $comments[$commentTextTrim]['count'] ++;
                    if($comments[$commentTextTrim]['time'] < $thisTime)
                    {
                        $comments[$commentTextTrim]['time'] = $thisTime;
                        $comments[$commentTextTrim]['datetime'] = $comment->getCreatedAtDate();
                    }
                }
            }
        }
        return $comments;
    }
	
    public function getLabelzebra($orders = array()) {
        $helper = Mage::helper('pickpack');

        $store_id = Mage::app()->getStore()->getId();

        //get general config object;


        $this->setLabelZebraConfig($store_id);
        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $rotate_label    = $this->_getConfig('rotate_label', 0, false, 'label_zebra');;
        $demension_zebra = explode(",", $this->_getConfig('nudge_demension_zebra', '432,288', false, 'label_zebra'));
        $label_width     = $demension_zebra[0];
        $label_height    = $demension_zebra[1];
        $page_top        = $label_height - 2;
        $padded_right    = $label_width - 10;
        $paper_width     = $label_width;
        $paper_height    = $label_height;
        if ($rotate_label == 0) {
            $page_demension        = $label_width . ':' . $label_height;
            $rotate_point[0]       = 0;
            $rotate_point[1]       = 0;
            $settings['page_size'] = $page_demension;
            $page                  = $this->newPageZebra($settings);
        } elseif ($rotate_label == 1) {
            $page_demension        = $label_height . ':' . $label_width;
            $rotate_point[0]       = 0;
            $rotate_point[1]       = 0;
            $settings['page_size'] = $page_demension;
            $page                  = $this->newPageZebra($settings);
        } else {
            $page_demension        = $label_height . ':' . $label_width;
            $rotate_point[0]       = 0;
            $rotate_point[1]       = 0;
            $settings['page_size'] = $page_demension;
            $page                  = $this->newPageZebra($settings);
        }

        $paper_margin = explode(",", $this->_getConfig('paper_margin_zebra', '13,5,5,5', false, 'label_zebra'));

        $label_padding          = explode(",", $this->_getConfig('label_padding_zebra', '13,5,5,5', false, 'label_zebra'));
        $nudge_shipping_address = explode(",", $this->_getConfig('nudge_shipping_address_zebra', '0,0', false, 'label_zebra'));

        $show_address_barcode_yn = $this->_getConfig('show_address_barcode_yn_zebra', 0, false, 'label_zebra');

        $top_left_x = ($paper_margin[3] + $label_padding[3]);
        $top_left_y = ($paper_height - ($paper_margin[0] + $label_padding[0]));

        $available_width = $label_width - $label_padding[1] - $label_padding[3];

        $resolution = $this->_getConfig('resolution', 0, false, 'label_zebra');

        $show_order_id_barcode_yn = $this->_getConfig('show_order_id_barcode_yn', 1, false, 'label_zebra');
        $show_order_id_yn         = $this->_getConfig('label_show_order_id_yn', 1, false, 'label_zebra');
        $nudge_order_id           = explode(",", $this->_getConfig('nudge_order_id', '0,0', true, 'label_zebra', $store_id));
        $font_family_label  = $this->_getConfig('font_family_label', 'helvetica', false, 'label_zebra', $store_id);
        $font_style_label   = $this->_getConfig('font_style_label', 'regular', false, 'label_zebra', $store_id);
        $font_size_order_id = $this->_getConfig('font_size_order_id', 9, false, 'label_zebra', $store_id);
        $font_size_label    = $this->_getConfig('font_size_label', 15, false, 'label_zebra', $store_id);
        $font_size_label2   = $this->_getConfig('font_size_label2', 20, false, 'label_zebra', $store_id);
        $font_color_label   = trim($this->_getConfig('font_color_label', 'Black', false, 'label_zebra', $store_id));

        $font_family_product = $this->_getConfig('font_family_product', 'helvetica', false, 'label_zebra', $store_id);
        $font_style_product  = $this->_getConfig('font_style_product', 'regular', false, 'label_zebra', $store_id);
        $font_size_product   = $this->_getConfig('font_size_product', 15, false, 'label_zebra', $store_id);
        $font_color_product  = trim($this->_getConfig('font_color_product', 'Black', false, 'label_zebra', $store_id));

        $sort_packing_order = trim($this->_getConfig('sort_packing_order', 0, false, 'label_zebra', $store_id));
        $sort_packing       = trim($this->_getConfig('sort_packing', 0, false, 'label_zebra', $store_id));

        //custom attribute
        $shelving_real_attribute_yn = $this->_getConfig('shelving_real_yn', 0, false, 'label_zebra', $store_id);
        $shelving_real_attribute    = $this->_getConfig('shelving_real', 'shelf', false, 'label_zebra', $store_id);
        $shelving_attributeX        = $this->_getConfig('pricesN_shelfX', '', false, 'label_zebra', $store_id);
        $shelving_attribute_title   = $this->_getConfig('shelving_real_title', '', false, 'label_zebra', $store_id);

        $show_product_options_yn   = $this->_getConfig('show_product_options_yn', '', false, 'label_zebra', $store_id);

        $qtyX = $this->_getConfig('pricesN_qtyX', 0, false, 'label_zebra');
        $show_product_name = 0;
        $show_product_sku = 0;
        $show_product_qty = 0;
        $separate_zebra_page_yn =0;
        $show_product_list = $this->_getConfig('show_product_list', 1, false, 'label_zebra');
        if($show_product_list == 1){
            $show_product_name = $this->_getConfig('show_product_name', 1, false, 'label_zebra');
            $show_product_sku  = $this->_getConfig('show_product_sku', 1, false, 'label_zebra');
            $separate_zebra_page_yn     = $this->_getConfig('separate_zebra_page_yn', 0, false, 'label_zebra');
            $show_address_product_label     = $this->_getConfig('show_address_product_label', 0, false, 'label_zebra');
            $show_product_qty          = $this->_getConfig('show_product_qty', 0, false, 'label_zebra');
            $show_product_price         = $this->_getConfig('show_product_price', 0, false, 'label_zebra');
            if($show_product_price == 1 || $show_product_price == 2)
                $pricesN_priceX = $this->_getConfig('pricesN_priceX', 80, false, 'label_zebra');

            $show_order_date         = $this->_getConfig('show_order_date', 0, false, 'label_zebra');
            $order_dateX = $this->_getConfig('order_dateX', 80, false, 'label_zebra');
            $order_date_title = $this->_getConfig('order_date_title', 'Order date', false, 'label_zebra');

            $subtotal_yn          = $this->_getConfig('subtotal_yn', 0, false, 'label_zebra');
            $subtotal_item_yn          = $this->_getConfig('subtotal_item_yn', 0, false, 'label_zebra');
            $show_order_shipping          = $this->_getConfig('show_order_shipping', 0, false, 'label_zebra');
            $show_order_grand          = $this->_getConfig('show_order_grand', 0, false, 'label_zebra');
            $show_order_source          = $this->_getConfig('show_order_source', 0, false, 'label_zebra');
            $show_customer_comment          = $this->_getConfig('show_customer_comment', 0, false, 'label_zebra');
        }
        if ( ($show_product_name == 1) || ($show_product_name == 'configurable') ) {
            $productX = $this->_getConfig('pricesN_productX', 80, false, 'label_zebra');
            $trim_product_name_yn = $this->_getConfig('trim_product_name_yn', 1, false, 'label_zebra');
        }
        if ( ($show_product_sku == 1) || ($show_product_sku == 'configurable') ) {
            $skuX = $this->_getConfig('pricesN_skuX', 200, false, 'label_zebra');
            $trim_product_sku_yn = $this->_getConfig('trim_product_sku_yn', 1, false, 'label_zebra');
        }
        $non_standard_characters = $this->_getConfig('non_standard_characters', 0, false, 'general', $store_id);

        if ($non_standard_characters == 'msgothic') {
            $font_family_label       = 'msgothic';
            $font_family_return_label       = 'msgothic';

            $font_family_product     = 'msgothic';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'tahoma') {
            $font_family_label       = 'tahoma';
            $font_family_return_label       = 'tahoma';
            $font_family_product     = 'tahoma';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'garuda') {
            $font_family_label       = 'garuda';
            $font_family_return_label       = 'garuda';
            $font_family_product     = 'garuda';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'sawasdee') {
            $font_family_label       = 'sawasdee';
            $font_family_return_label       = 'sawasdee';
            $font_family_product     = 'sawasdee';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'kinnari') {
            $font_family_label       = 'kinnari';
            $font_family_return_label       = 'kinnari';
            $font_family_product     = 'kinnari';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'purisa') {
            $font_family_label       = 'purisa';
            $font_family_return_label       = 'purisa';
            $font_family_product     = 'purisa';
            $non_standard_characters = 1;
        }elseif ($non_standard_characters == 'traditional_chinese') {

            $font_family_label = 'traditional_chinese';
            $font_family_return_label = 'traditional_chinese';
            $font_family_product     = 'traditional_chinese';
            $non_standard_characters = 1;
        }elseif ($non_standard_characters == 'simplified_chinese') {

            $font_family_label = 'simplified_chinese';
            $non_standard_characters = 1;
        }
        elseif ($non_standard_characters == 'hebrew') {
            $font_family_body = 'hebrew';
            $font_family_header = 'hebrew';
            $font_family_gift_message = 'hebrew';
            $font_family_comments = 'hebrew';
            $font_family_message = 'hebrew';
            $font_family_company = 'hebrew';
            $font_family_subtitles = 'hebrew';
            $non_standard_characters = 1;
        }
        elseif ($non_standard_characters == 'yes') {
            $non_standard_characters = 2;
        }

        $override_address_format_yn = 1;
        $customer_email_yn          = $this->_getConfig('label_show_email', 0, false, 'label_zebra', $store_id);
        $customer_phone_yn          = $this->_getConfig('label_show_phone_number', 0, false, 'label_zebra', $store_id);
        $address_format             = $this->_getConfig('address_format', '', false, 'label_zebra'); //col/sku
        $address_countryskip        = $this->_getConfig('address_countryskip', 0, false, 'label_zebra');
        $return_address_yn          = $this->_getConfig('label_return_address_yn', 0, false, 'label_zebra'); // 0,1,yesside		
        if ($return_address_yn == 'yesside') {
            $font_family_return_label = $this->_getConfig('font_family_return_label_side', 'helvetica', false, 'label_zebra', $store_id);
            $font_style_return_label  = $this->_getConfig('font_style_return_label_side', 'regular', false, 'label_zebra', $store_id);
            $font_size_return_label   = $this->_getConfig('font_size_return_label_side', 9, false, 'label_zebra', $store_id);
            $font_color_return_label  = trim($this->_getConfig('font_color_return_label_side', 'Black', false, 'label_zebra', $store_id));
            $rotate_return_address    = $this->_getConfig('rotate_return_label_side', 1, false, 'label_zebra', $store_id);
        } elseif ($return_address_yn == '1') {
            $font_family_return_label = $this->_getConfig('font_family_return_label', 'helvetica', false, 'label_zebra', $store_id);
            $font_style_return_label  = $this->_getConfig('font_style_return_label', 'regular', false, 'label_zebra', $store_id);
            $font_size_return_label   = $this->_getConfig('font_size_return_label', 9, false, 'label_zebra', $store_id);
            $font_color_return_label  = trim($this->_getConfig('font_color_return_label', 'Black', false, 'label_zebra', $store_id));
        } else {
            $font_size_return_label = 15;
            $rotate_return_address  = 0;
        }

        $label_logo_yn = $this->_getConfig('label_logo_yn2', 0, false, 'label_zebra', $store_id);
        $nudge_shipping_address[1] -= 35;
        $nudge_shipping_address[0] += 10;
        $label_logo_xy = explode(",", $this->_getConfig('label_nudgelogo', '0,0', false, 'label_zebra', $store_id)); //0,0
        if ($label_logo_yn == 1)
            $nudge_shipping_bg = explode(",", $this->_getConfig('nudge_label_logo_yn2', '0,0', false, 'label_zebra', $store_id)); //0,0
        $barcode_nudge = explode(",", $this->_getConfig('nudge_barcode', '0,0', true, 'label_zebra', $store_id));
        $barcode_nudge[0] += -10;
        $barcode_nudge[1] += 150;

        $black_color       = new Zend_Pdf_Color_Rgb(0, 0, 0);
        $white_color       = new Zend_Pdf_Color_GrayScale(1);

        $address_count  = 0;

        $current_x = $top_left_x;
        $current_y = $top_left_y;

        $temp_y            = $current_y;
        $temp_x            = $current_x;
        $subheader_start   = 0;
        $next_y            = $current_y;
        $rotate_label = $this->getRotateReturnAddress($rotate_label);
        $final_zebra_summary = $this->_getConfig('final_zebra_summary', '', false, 'label_zebra', $store_id);
        if($final_zebra_summary == 1){
            $priority_custom_attribute = explode("\n", $this->_getConfig('priority_custom_attribute', '', false, 'label_zebra', $store_id));
            $priority_custom_attribute = array_map("trim", $priority_custom_attribute);
            $unit_weight = $this->_getConfig('label_summary_weight_unit', 'kg', false, 'label_zebra', $store_id);
            $summary_custom_attribute    = $this->_getConfig('summary_custom_attribute', 'shelf', false, 'label_zebra', $store_id);
        }
        $summary_zebra = array();
        foreach ($orders as $orderSingle) {
            $order = $helper->getOrder($orderSingle);
            $this->setCurrentOrder($order);
            $storeId = $order->getStore()->getId();
            $this->setStoreId($storeId);

            $this->getConfigValue($storeId);

            $font_family_barcode = Mage::helper('pickpack/barcode')->getFontForType($this->generalConfig['font_family_barcode']);

            $this->setStoreId($storeId); //this use storeid for split function later
            $useGFSLabel = false;

            if (Mage::helper('pickpack')->isInstalled('Moogento_CourierRules') && ($this->_getConfig('use_courierrules_shipping_label',0, false, 'label_zebra', $storeId) == 1)) {
                try{
                    $show_courierrules_label_nudge[0] = 0;
                    $show_courierrules_label_nudge[1] = 1;
                    $show_courierrules_label_dimension[0] = $demension_zebra[0];
                    $show_courierrules_label_dimension[1] = $demension_zebra[1];

                    $labels = Mage::helper('moogento_courierrules/connector')->getConnectorLabels($orderSingle);
                    if (count($labels)) {
                        $i = 0;
                        foreach ($labels as $label) {
                            if ($i > 0) {
                                $page = $this->newPageZebra($settings);
                            }
                            $tmpFile = Mage::helper('pickpack')->getConnectorLabelTmpFile($label);
                            $imageObj = Zend_Pdf_Image::imageWithPath($tmpFile);
                            $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                            $this->drawImage($page,$imageObj, $show_courierrules_label_nudge[0], $show_courierrules_label_nudge[1], $show_courierrules_label_nudge[0] + $show_courierrules_label_dimension[0], $show_courierrules_label_nudge[1] + $show_courierrules_label_dimension[1]);
                            unset($tmpFile);
                            $i++;
                        }
                        $useGFSLabel = true;
                    }
                }
                catch(Exception $e)
                {
                    echo $e->getMessage();
                }
            }

            if(!$useGFSLabel) {
                $order_zebra = array();
                $item_zebra = array();
                $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                if ($address_count > 0) {
                    // going top left down, then across
                    // if last label bigger than 1 label, start on fresh
                    if (($current_y - $temp_y) > ($label_height - $label_padding[0] - $label_padding[3]))
                        $current_y = ($current_y - $label_height);

                    //Calculate X pos.
                    if (($temp_y - $label_height - $paper_margin[3]) < 0) {
                        $current_y = $top_left_y;
                        $next_y    = $top_left_y;
                        if (($current_x + $label_width + $paper_margin[2]) >= $paper_width) {
                            $page = $this->newPageZebra($settings);
                            $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                            $current_x = $top_left_x;
                        } else {
                            $current_x += $label_width;
                        }
                    } else {
                        $current_y -= $label_height;
                    }
                }
                $address_count++;

                if ($next_y < $current_y)
                    $current_y -= $label_height;

                if (($current_y - $paper_margin[3]) < 0) {
                    $current_y = $top_left_y;
                    $next_y    = $top_left_y;
                    if (($current_x + $label_width + $paper_margin[2]) > $paper_width) {
                        $page = $this->newPageZebra($settings);
                        $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                        $current_x = $top_left_x;

                    } else {
                        $current_x += $label_width;
                    }
                }

                $temp_y = $current_y;

                $temp_x = $current_x;
                $order                  = $helper->getOrder($orderSingle);
                $order_id               = $order->getRealOrderId();
                // store-specific options
                $store_id               = $order->getStore()->getId();
                $nudge_order_id_barcode = explode(",", $this->_getConfig('nudge_order_id_barcode', '0,0', true, 'label_zebra', $store_id));
                $nudge_order_id_barcode[0] -= 30;
                $nudge_order_id_barcode[1] -= 150;
                if ($return_address_yn == 'yesside') {
                    $return_address        = $this->_getConfig('label_return_address_side', '', false, 'label_zebra', $store_id);
                    $return_label_nudge_xy = explode(",", $this->_getConfig('nudge_return_label', '0,0', true, 'label_zebra', $store_id));
                }

                $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);


                /******************NEW SHIPPING ADDRESS DETAIL*********************/
                $has_shipping_address = false;
                foreach ($order->getAddressesCollection() as $address) {
                    if ($address->getAddressType() == 'shipping' && !$address->isDeleted()) {
                        $has_shipping_address = true;
                        break;
                    }
                }
                $customer_company= ""; // added by VJ
                if ($has_shipping_address !== false) {
                    if ($order->getShippingAddress()->getFax())
                        $customer_fax = trim($order->getShippingAddress()->getFax());
                    if ($order->getShippingAddress()->getTelephone())
                        $customer_phone = trim($order->getShippingAddress()->getTelephone());
                    else $customer_phone = '';
                    if ($order->getShippingAddress()->getCompany())
                        $customer_company = trim($order->getShippingAddress()->getCompany());
                    if ($order->getShippingAddress()->getName())
                        $customer_name = trim($order->getShippingAddress()->getName());
                    if ($order->getShippingAddress()->getFirstname())
                        $customer_firstname = trim($order->getShippingAddress()->getFirstname());
                    else
                        $customer_firstname = '';
                    if ($order->getShippingAddress()->getLastname())
                        $customer_lastname = trim($order->getShippingAddress()->getLastname());
                    else
                        $customer_lastname = '';
                    if ($order->getShippingAddress()->getCity())
                        $customer_city = trim($order->getShippingAddress()->getCity());
                    if ($order->getShippingAddress()->getPostcode())
                        $customer_postcode = trim(strtoupper($order->getShippingAddress()->getPostcode()));
                    if ($order->getShippingAddress()->getRegion())
                        $customer_region = trim($order->getShippingAddress()->getRegion());
                    else
                        $customer_region = '';
                    if ($order->getShippingAddress()->getRegionCode())
                        $customer_region_code = trim($order->getShippingAddress()->getRegionCode());
                    if ($order->getShippingAddress()->getPrefix())
                        $customer_prefix = trim($order->getShippingAddress()->getPrefix());
                    else
                        $customer_prefix = '';
                    if ($order->getShippingAddress()->getSuffix())
                        $customer_suffix = trim($order->getShippingAddress()->getSuffix());
                    else
                        $customer_suffix = '';
                    if ($order->getShippingAddress()->getStreet1())
                        $customer_street1 = trim($order->getShippingAddress()->getStreet1());
                    else
                        $customer_street1 = '';
                    if ($order->getShippingAddress()->getStreet2())
                        $customer_street2 = trim($order->getShippingAddress()->getStreet2());
                    else
                        $customer_street2 = '';
                    if ($order->getShippingAddress()->getStreet3())
                        $customer_street3 = trim($order->getShippingAddress()->getStreet3());
                    else
                        $customer_street3 = '';
                    if ($order->getShippingAddress()->getStreet4())
                        $customer_street4 = trim($order->getShippingAddress()->getStreet4());
                    else
                        $customer_street4 = '';
                    if ($order->getShippingAddress()->getStreet5())
                        $customer_street5 = trim($order->getShippingAddress()->getStreet5());
                    else
                        $customer_street5 = '';

                    if (Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId())) {
                        $customer_country = trim(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()));
                    }else{
                        $customer_country = '';
                    }
                }



                $shipping_address = array();
                $if_contents      = array();
                if ($has_shipping_address !== false){
                    if (isset($customer_company))
                        $shipping_address['company'] = $customer_company;
                    else
                        $shipping_address['company'] = '';
                    $shipping_address['firstname']   = $customer_firstname;
                    $shipping_address['lastname']    = $customer_lastname;
                    $shipping_address['name']        = $customer_name;
                    $shipping_address['name']        = trim(preg_replace('~^' . $shipping_address['company'] . '~i', '', $shipping_address['name']));
                    $shipping_address['city']        = $customer_city;
                    $shipping_address['telephone']   = $customer_phone;
                    $shipping_address['postcode']    = $customer_postcode;
                    $shipping_address['region_full'] = $customer_region;
                    $shipping_address['region_code'] = (isset($customer_region_code) ? $customer_region_code : '');
                    if (isset($customer_region_code) && $customer_region_code != '') {
                        $shipping_address['region'] = $customer_region_code;
                        unset($customer_region_code);
                    } else {
                        $shipping_address['region'] = $customer_region;
                        unset($customer_region);
                    }
                    $shipping_address['prefix']  = $customer_prefix;
                    $shipping_address['suffix']  = $customer_suffix;
                    $shipping_address['country'] = $customer_country;
                    $shipping_address['street']  = $customer_street1;
                    $shipping_address['street1'] = $customer_street1;
                    $shipping_address['street2'] = $customer_street2;
                    $shipping_address['street3'] = $customer_street3;
                    $shipping_address['street4'] = $customer_street4;
                    $shipping_address['street5'] = $customer_street5;

                    if ($address_countryskip != '') {
                        if ($address_countryskip == 'usa' || $address_countryskip == 'united states' || $address_countryskip == 'united states of america') {
                            $address_countryskip = array(
                                'usa',
                                'united states of america',
                                'united states'
                            );
                        }
                        $shipping_address['country'] = str_ireplace($address_countryskip, '', $shipping_address['country']);

                        if(!is_array($address_countryskip) && (strtolower(trim($address_countryskip)) == "singapore" || strtolower(trim($address_countryskip)) =="monaco")){
                            $shipping_address['city'] = str_ireplace($address_countryskip, '', $shipping_address['city']);
                        }
                    }
                }
                if ($has_shipping_address !== false)
                    $shipping_address['street'] = $this->getShippingAddressFull($order, $font_size_label);
                $address_format_set = str_replace(array(
                    "\n",
                    '<br />',
                    '<br/>',
                    "\r"
                ), '', $address_format);


				$address_street     = '';
                foreach ($shipping_address as $key => $value) {
                    $value = trim($value);
                    $value = str_replace(array(
                        ',,',
                        ', ,',
                        ', ,'
                    ), ',', $value);
                    $value = trim(preg_replace('~\-$~', '', $value));
                    if ($value != '' && !is_array($value)) {
						// we match one of our preset keys
                        if ($key == "street1" || $key == "street2" || $key == "street3" || $key == "street4" || $key == "street5") {
                            if ($address_street != '')
                                $address_street = $address_street . "," . $value;
                            else
                                $address_street = $value;
                        }
                        preg_match('~\{if ' . $key . '\}(.*)\{\/if\}~ims', $address_format_set, $if_contents);
						//backwards compatible
						if(!isset($if_contents[1]) || ($if_contents[1] == ''))
							preg_match('~\{if ' . $key . '\}(.*)\{\/if ' . $key . '\}~ims', $address_format_set, $if_contents);
						
                        if (isset($if_contents[1])) {
							// check if the address format is caps
                            if ($key == 'postcode'){ // this will make postcode always be full-caps
                                $if_contents[1] = str_ireplace('{' . $key . '}', $value, $if_contents[1]);
                            }else{
                                $str = preg_replace("/{\/if}(.*)/", "", $if_contents[1]);
                                $value = $this->checkCapsIntent($str, $value);
                                $if_contents[1] = str_ireplace('{' . $key . '}', $value, $if_contents[1]);
                            }
                        } else
                            $if_contents[1] = '';

						// backwards compatible
                        $address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~ims', $if_contents[1], $address_format_set);
                        $address_format_set = str_ireplace('{' . $key . '}', $value, $address_format_set);
                        $address_format_set = str_ireplace('{if ' . $key . '}', '', $address_format_set);
						
                    } else {
						// clear this value, as it's not one of our preset keys
                        // backwards compatible
						$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~i', '', $address_format_set);
						$address_format_set = preg_replace('~\{if ' . $key . '\}{' . $key . '}\,?\.?\s?~i', '', $address_format_set);
                        $address_format_set = str_ireplace('{' . $key . '}\,?\.?\s?', '', $address_format_set);
                    }
                }
                $address_format_set = str_ireplace('{/if}', '', $address_format_set);
				
                $address_format_set   = trim(str_replace(array(
                    '||',
                    '|'
                ), "\n", trim($address_format_set)));
                $address_format_set   = trim(str_replace(array(
                    ',,'
                ), ",", trim($address_format_set)));
                $address_format_set   = str_replace("\n\n", "\n", $address_format_set);
                $shippingAddressArray = array();
			
                if ($has_shipping_address !== false)
                    $shippingAddressArray = explode("\n", $address_format_set);
                /******************END SHIPPING ADDRESS DETAIL*********************/

                $count = (count($shippingAddressArray) - 2);

                $addressLine = '';
                $line_height = (1 * $font_size_label);

                $stop_address = FALSE;

                if ($label_logo_yn == 1) {
                    if ($order->getStoreId())
                        $store_id = $order->getStoreId();
                    else
                        $store_id = null;
                    $x1                   = 0;
                    $y1                   = 0;
                    $default_ship_image_x = 20;
                    $default_ship_image_y = 360;

                }

                if ($order->getStoreId())
                    $store = $order->getStoreId();
                else
                    $store = null;
                $shipping_method = clean_method($order->getShippingDescription(), 'shipping');
                $haystack        = preg_replace("/[^\-\(\)\{\}\_a-z0-9\s]/", '', strtolower($shipping_method));
                if ($label_logo_yn == 1) {
//                    Default potision of shipping background image should be at top-left
//                    This value will help get right potition after rotate
//                    $this->_base_position['X']
//                    $this->_base_position['Y']
//                    $this->_config['nudge_demension_zebra'][1]  : the height of page

                    $image_line_x = $this->_base_position['X'] + $nudge_shipping_bg[0];
                    $image_line_y = $this->_base_position['Y'] + $this->_config['nudge_demension_zebra'][1] + $nudge_shipping_bg[1];
                    $this->showShippingAddresBackground($order, $image_line_y, $wonder = "", $store_id, $page, $image_line_x, $this->_getConfig('top_shipping_address_background_yn_scale', '', false, 'label_zebra', $store_id), $label_width, 0, $resolution);
                }

                //Print order id barcode.
                if ($show_order_id_barcode_yn == 1) {
                    $barcode_font_size = 16;
                    $barcodeString     = Mage::helper('pickpack/barcode')->convertToBarcodeString($order_id, $this->generalConfig['font_family_barcode']);
                    $barcodeWidth      = 1.35 * $this->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                    $page->setFillColor($black_color);
                    $page->setLineColor($black_color);
                    $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                    $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                    $order_barcode_x = ($temp_x + ($label_width * 0.9) - $barcodeWidth + $nudge_order_id_barcode[0]);
                    $order_barcode_y = ($temp_y + $nudge_order_id_barcode[1]);
                    $this->drawText($page,$barcodeString, $order_barcode_x, $order_barcode_y, 'CP1252');
                    $page->setFillColor($white_color);
                    $page->setLineColor($white_color);
                    $this->drawRectangle($page,($order_barcode_x + 10 + $nudge_order_id[0]), ($order_barcode_y + 10 + $nudge_order_id[1]), ($order_barcode_x + $nudge_order_id[0] + ($barcodeWidth / 1.35) + 10), ($order_barcode_y + $nudge_order_id[1] - 10));
                }

                //Print order Id
                if ($show_order_id_yn == 1) {

                    $this->_setFont($page, $font_style_label, ($font_size_order_id), $font_family_label, $non_standard_characters, $font_color_label);
                    $page->setFillColor($black_color);
                    if(isset($order_barcode_x))
                        $extra_order_id_x = $order_barcode_x;
                    else
                        $extra_order_id_x = 0;

                    if(isset($order_barcode_y))
                        $extra_order_id_y = $order_barcode_y;
                    else
                        $extra_order_id_y = 0;

                    $this->drawText($page,$order_id, ($extra_order_id_x + 15 + $nudge_order_id[0]), ($extra_order_id_y + $nudge_order_id[1]), 'UTF-8');
                    $temp_y -= ($line_height * 0.4);
                }

                $this->_setFont($page, $font_style_label, $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);

                $product_qty_red = 0;
                $i                  = 0;
                $line_height_top    = (1.07 * $font_size_label);
                $line_height_bottom = (1.05 * $font_size_label);

                $i_space = 0;

                $shipping_line_count = (count($shippingAddressArray) - 2);
                $line_addon          = 0;
                $token_addon         = 25;


                $ship_address_title_yn        = $this->_getConfig('ship_address_title_yn', '', false, 'label_zebra', $store_id);
                if($ship_address_title_yn){
                    $ship_address_title        = $this->_getConfig('ship_address_title', '', false, 'label_zebra', $store_id);
                    $this->_setFont($page, 'bold', $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);
                    $this->drawText($page,$ship_address_title, $temp_x + $nudge_shipping_address[0], $temp_y + $nudge_shipping_address[1] , 'UTF-8');
                }

                /*************************** BEGIN PRINT SHIPPING ADDRESS *******************************/

                $font_temp      = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $max_line_width = ($label_width * 0.86);
                $this_address_line_y = 0;
				$address_line_x = 0;
                foreach ($shippingAddressArray as $i => $value) {
                    $value      = trim($value);
                    $value = Mage::helper('pickpack/functions')->clean_method($value,'pdf');
                    if($value != ''){
                        $line_width = $this->parseString($value, $font_temp, $font_size_label);
                        $max_chars  = round($max_line_width / ($line_width / strlen($value)));
                        $skip       = 0;
                        $line_bold  = 0;
                        $i_space    = ($i_space + 1);
                        $font_size_adjust = 0;
                        if ($i == 2 && $value != '') {
                            $i_space = ($i_space + ($font_size_label2 * 0.003));
                        }
                        if ($i < 2)
                            $this->_setFont($page, $font_style_label, ($font_size_label - $font_size_adjust), $font_family_label, $non_standard_characters, $font_color_label);
                        else
                            $this->_setFont($page, $font_style_label, ($font_size_label2 - $font_size_adjust), $font_family_label, $non_standard_characters, $font_color_label);
                        //New option here.
                        $value_arr      = wordwrap($value, $max_chars);
                        $address_line_x = $temp_x;
                        $address_line_y = $temp_y;
                        $returns_side_x = round($address_line_x + ($label_width * 0.8));
                        if (isset($rotate_return_address) && $rotate_return_address != 0)
                            $address_line_y = ($address_line_y + 15 + $font_size_product / 2);
                        else
                            $address_line_y = ($address_line_y - 5);
                        $address_line_x = $address_line_x + $nudge_shipping_address[0];

                        $address_line_y = $address_line_y + $nudge_shipping_address[1];


                        $this_address_line_y = 0;
                        $token               = strtok($value_arr, "\n");
                        $flag                = 0;
                        while ($token != false) {
                            $this_address_line_y = ($address_line_y - ($line_height_top * $i_space) - $line_addon - $token_addon);
                            if (($this_address_line_y - $paper_margin[3]) < 0) {
                                $this_address_line_y = $top_left_y;
                                if (($current_x + $label_width + $paper_margin[2]) > $paper_width) {
                                    $page = $this->newPageZebra($settings);
                                    $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                                    $current_x      = $top_left_x;
                                    $address_line_x = $top_left_x;
                                    $temp_x         = $top_left_x;
                                } else {
                                    $current_x += $label_width;
                                    $address_line_x += $label_width;
                                    $temp_x += $label_width;
                                }
                                $current_y = $top_left_y;
                                $next_y    = $top_left_y;
                                $temp_y    = $top_left_y;

                                $address_line_y = $top_left_y;
                                $line_addon     = 0;
                                $token_addon    = 0;
                                $i_space        = 0;
                                $this->_setFont($page, $font_style_label, $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);
                                $this->drawText($page,trim($token), $address_line_x, $this_address_line_y, 'UTF-8');
                                $token_addon += $font_size_label + 2;
                            } else {
                                $this->drawText($page,trim($token), $address_line_x, $this_address_line_y, 'UTF-8');
                                $token_addon += $font_size_label + 2;
                            }
                            $token = strtok("\n");
                        }
                        $i_space = ($i_space - 1);
                    }
                    $i++;
                }

                if ($has_shipping_address !== false)
                    $subheader_start = $this_address_line_y - ($font_size_label * 1);
                else
                    $subheader_start = $top_left_y;

                if($has_shipping_address === false)
                {
                    $subheader_start = $temp_y;
                    if(isset($order_barcode_y) && $order_barcode_y < $subheader_start)
                        $subheader_start = $order_barcode_y - $font_size_label;


                    if(isset($extra_order_id_y) && ($extra_order_id_y + $nudge_order_id[1]) < $subheader_start)
                        $subheader_start = $extra_order_id_y + $nudge_order_id[1]- $font_size_label ;
                }

                if ($order->getShippingAddress() && $order->getShippingAddress()->getPostcode()) {
                    $zipcode = trim(strtoupper($order->getShippingAddress()->getPostcode()));
                }
                else
                    $zipcode = '';
                if (($customer_phone_yn != 0) && ($customer_phone != '')) {
                    $value          = $helper->__('T: ') . $customer_phone;
                    $line_addon     = ($font_size_label * 0.5);
                    $address_line_x = $temp_x;
                    $address_line_y = $subheader_start;

                    $this->_setFont($page, 'regular', ($font_size_label - 3), $font_family_label, $non_standard_characters, 'Gray');

                    if ($customer_phone_yn != 'yesdetails') {
                        $this->drawText($page,$value, $address_line_x, $address_line_y, 'UTF-8');
                    }
                    $subheader_start -= ($font_size_label);
                }

                if (($customer_email_yn != 0) && ($customer_email != '')) {
                    $value          = $helper->__('E: ') . $customer_email;
                    $address_line_x = $temp_x;
                    $address_line_y = $subheader_start;
                    $this->_setFont($page, 'regular', ($font_size_label - 3), $font_family_label, $non_standard_characters, 'Gray');
                    if ($customer_email_yn != 'yesdetails') {
                        $this->drawText($page,$value, $address_line_x, $address_line_y, 'UTF-8');
                        $subheader_start -= ($font_size_label);
                    }
                    $subheader_start -= ($font_size_label);
                }
                //  print shipping barcode.
                if ($show_address_barcode_yn == 1 && $zipcode != '') {
                    $barcode_font_size = 16;
                    $barcode_y         = ($subheader_start - ($font_size_return_label / 2) - ($barcode_font_size / 1.6) - $line_addon - 5 * $font_size_label) + $barcode_nudge[1];
                    $barcode_x         = $temp_x + ($label_width * 0.9) - $barcodeWidth + $barcode_nudge[0];
                    $barcodeString     = Mage::helper('pickpack/barcode')->convertToBarcodeString($zipcode, $this->generalConfig['font_family_barcode']);
                    $barcodeWidth      = 1.35 * $this->parseString($zipcode, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                    $page->setLineColor($black_color);
                    $page->setFillColor($black_color);
                    $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                    $this->drawText($page,$barcodeString, $barcode_x, ($barcode_y - 15), 'CP1252');
                }

                /*************************** END BEGIN PRINT SHIPPING ADDRESS *******************************/

                if ($this->_config['label_return_address_yn'] === "yesside") {
                    $this->printReturnAddressOnSameLabel($page,$address_line_x, $address_line_y);
                }

                /*************************** PRINT TRACKING NUMBER *******************************/
                $tracking_number_yn = $this->_getConfig('tracking_number_yn', 0, false, $this->getWonder(), $this->getStoreId());
                if ($tracking_number_yn){
                    $this->showTrackingNumber($page,$order,10,10);
                }
                /*************************** END PRINT TRACKING NUMBER *******************************/
                /*************************** PRINT TRACKING NUMBER AS BARCODE *******************************/
                $tracking_number_barcode_yn = $this->_getConfig('tracking_number_barcode_yn', 0, false, $this->getWonder(), $this->getStoreId());
                if ($tracking_number_barcode_yn){
                    if (!isset($tracking_number_yn) || $tracking_number_yn == 0)
                        $tracking_number_fontsize = 0;
                    else{
                        $tracking_number_fontsize = $this->_getConfig('tracking_number_fontsize', 15, false, $this->getWonder(), $this->getStoreId());
                    }
                    $this->showTrackingNumberBarcode($page, $order, 10, 10 + $tracking_number_fontsize);
                }
                /*************************** END PRINT TRACKING NUMBER AS BARCODE *******************************/

                $next_y    = $subheader_start;
                //draw product
                $sku_item  = '';
                $name_item = '';
                $qty_item  = 0;
                if ($qtyX == 0)
                    $qtyX  = $temp_x;
                $start_product_posy = $this->_getConfig('start_product_posy', 0, false, "label_zebra");
                if($start_product_posy == 1){
                    $first_start_y = $this->_getConfig('first_start_y', 0, false, "label_zebra");
                    $temp_y    = $first_start_y;
                }else
                    $temp_y    = $next_y - $font_size_product;
                $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                //sort items
                $itemsCollection       = $this->sortitems($sort_packing, $shelving_real_attribute, $itemsCollection, $sort_packing_order);
                $product_qty_upsize_yn = $this->_getConfig('product_qty_upsize_yn', 1, false, 'label_zebra', $store_id);

                $product_qty_rectangle  = 0;
	          	if ($product_qty_upsize_yn == '1')
   		     		$product_qty_rectangle = 1;

                /**************/
                $store_view = $this->_getConfig('name_store_view', 'storeview', false, "label_zebra");
                $specific_store_id = $this->_getConfig('specific_store', '', false, "label_zebra", $store_id);
                $total_shipping_weight = 0;
                $has_attribute_priority = false;
                $total_items = 0;
                $combine_product_line = $this->_getConfig('combine_product_line', 0, false, "label_zebra");
                $right_margin_line = $this->_getConfig('right_margin_line', 10, false, "label_zebra");
                //if($show_product_qty == 1)
                //$show_x_qty = $this->_getConfig('show_x_qty', 0, false, "label_zebra");
                //get title column
                $product_qty_title = $this->_getConfig('product_qty_title', '', false, "label_zebra", $store_id);
                $product_name_title = $this->_getConfig('product_name_title', '', false, "label_zebra", $store_id);
                $product_sku_title = $this->_getConfig('product_sku_title', '', false, "label_zebra", $store_id);
                $shelving_title = $this->_getConfig('shelving_title', '', false, "label_zebra", $store_id);
                $price_title = $this->_getConfig('product_price_title', '', false, "label_zebra", $store_id);
                // hide separator line if we're not showing titles
                $make_product_line_separator = false;
                if(
                    ($show_product_name == 1 && $product_name_title != '')
                    || ($show_product_qty == 1 && $product_qty_title != '')
                    || ($show_product_sku == 1 && $product_sku_title != '')
                ) $make_product_line_separator = true;

                $order_shipping_title = $this->_getConfig('order_shipping_title', 'Shipping', false, "label_zebra", $store_id);
                $order_grand_title = $this->_getConfig('order_grand_title', 'Grand Total', false, "label_zebra", $store_id);
                $order_source_title = $this->_getConfig('order_source_title', 'Source', false, "label_zebra", $store_id);
                $key_priority_attribute = 9999;

                $addon_rotate = 0;

                if ($separate_zebra_page_yn) {
                    $page = $this->newPageZebra($settings);
                    $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                    $temp_y = $current_y - 10;
                    $temp_x = $current_x + 10;

                    if($show_order_id_yn == 0)
                        $font_size_order_id = 10;
                    $this->_setFont($page, "bold", ($font_size_order_id), $font_family_label, $non_standard_characters, $font_color_label);
                    $page->setFillColor($black_color);
                    $this->drawText($page,$order_id, $temp_x, $temp_y, 'UTF-8');
                    $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($store_id, $this->generalConfig['date_format']);
                    $order_date = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $this->generalConfig['date_format']);
                    $this->_setFont($page, $font_style_label, ($font_size_order_id), $font_family_label, $non_standard_characters, $font_color_label);
                    $this->drawText($page,$order_date, $padded_right - 60 + $addon_rotate, $temp_y, 'UTF-8');
                    $temp_y -= $font_size_order_id;
                    //show address 2nd

                    $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                    if($show_address_product_label == 1){
                        $shipping_address_string = implode(",", $shippingAddressArray);
                        $shipping_address_string_multi_line = wordwrap($shipping_address_string, 69, "\n");
                        $token = strtok($shipping_address_string_multi_line, "\n");
                        if ($token != false) {
                            while ($token != false) {
                                $this->drawText($page, $token, $temp_x, $temp_y, 'UTF-8');
                        		$temp_y -= $font_size_product + 2;
                                $token = strtok("\n");
                            }
                        }
                    }
                }

                if($show_product_list == 1 && ($separate_zebra_page_yn == 0 || $separate_zebra_page_yn == 1)){
                    //show title product list
                    $this->_setFont($page, "bold", $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                    if($show_product_qty == 1){
                        $qtyX += $addon_rotate;
                        $this->drawText($page,$product_qty_title, $qtyX, $temp_y, 'UTF-8');
                    }
                    if (($show_product_sku == 1 || $show_product_sku == 'configurable') && $combine_product_line == 0){
                        $this->drawText($page,$product_sku_title, $skuX, $temp_y, 'UTF-8');
                    }
                    if (($show_product_name == 1 || $show_product_name == 'configurable') && $combine_product_line == 0){
                        $productX += $addon_rotate;
                        $this->drawText($page,$product_name_title, intval($productX), $temp_y, 'UTF-8');
                    }
                    if ($show_product_price == 1 || $show_product_price == 2) {
                        $this->drawText($page,$price_title, intval($pricesN_priceX), $temp_y, 'UTF-8');
                    }
                    if($show_order_date == 1){
                        $this->drawText($page,$order_date_title, intval($order_dateX), $temp_y, 'UTF-8');
                    }

                    if ($this->zebralabelConfig['show_product_barcode'] && $this->zebralabelConfig['product_barcode_in_separate_line'] == 0){
                        $this->drawText($page,$this->zebralabelConfig['product_barcode_title'], intval($this->zebralabelConfig['product_barcode_xpos']), $temp_y, 'UTF-8');
                    }

                    $page->setLineWidth(0.5);
                    $page->setLineColor($black_color);
                    $page->setFillColor($black_color);
                    if($make_product_line_separator == true) $this->drawLine($page,$qtyX, $temp_y - 2, ($padded_right), $temp_y - 2);
                    $temp_y -= $font_size_product + 4;
                    $min_temp_y = $temp_y;
                    $flag_newPage = 0;
					$lineName = 0;
					$old_temp_y = $temp_y;

                    /** start print order comment at top product list */
                    if ($this->zebralabelConfig['show_order_notes'] && $this->zebralabelConfig['order_notes_position'] == "yesshipping"){
                        $orderNotesElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_labelzebra_ordernotes', array($this,$this->getOrder(),$this->generalConfig,$this->zebralabelConfig));
                        $orderNotesElement->y = $temp_y;
                        $orderNotesElement->page_padding = $this->page_padding;
                        $orderNotesElement->showOrderNotes();
                        $temp_y = $orderNotesElement->y;
                        $page = $orderNotesElement->getPage();
                    }
                    /** end print order comment at top product list */

                    foreach ($itemsCollection as $item) {
                        if ($temp_y < $font_size_product) {
                            $flag_newPage = 1;
                            $page = $this->newPageZebra($settings);
                            $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                            $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $current_x = $top_left_x;
                            $current_y = $top_left_y;
                            $min_temp_y = $temp_temp_y = $temp_y = $current_y;

                        }
                        if($font_family_product == 'traditional_chinese' || $font_family_product == 'simplified_chinese'){
                            $font_family_product_temp = $font_family_product;
                            $font_family_product = 'helvetica';
                        }
                        $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                        //$total_shipping_weight = ($item->getData()["weight"] * $item->getQtyOrdered());
                        $product_simple = Mage::helper('pickpack/product')->getProductFromItem($item);
                        $product_id = $product_simple->getId();
                        $total_shipping_weight = ($product_simple->getData("weight") * $item->getQtyOrdered());

                        $qty_item  = (int) $item->getQtyOrdered();
                        $total_items = $qty_item;
                        /*************/
                        if($show_product_qty == 1){
                            if ($product_qty_upsize_yn == 1 && $qty_item > 1) {
                                if ($product_qty_rectangle == 1) {

                                    $page->setLineWidth(1);
                                    $page->setLineColor($black_color);
                                    $page->setFillColor($black_color);

                                    if ($qty_item >= 100)
                                        $this->drawRectangle($page,($qtyX - 1), ($temp_y), ($qtyX  + (strlen($qty_item) * 2* $font_size_product/3)), ($temp_y - 3 + $font_size_product * 1.2));
                                    else if ($qty_item >= 10)
                                        $this->drawRectangle($page,($qtyX - 1), ($temp_y), ($qtyX + 1 + (strlen($qty_item) * 2* $font_size_product/3)), ($temp_y - 3 + $font_size_product * 1.2));
                                    else
                                        $this->drawRectangle($page,($qtyX - 1), ($temp_y), ($qtyX + 1 +(strlen($qty_item) * 2 * $font_size_product/3)), ($temp_y - 3 + $font_size_product * 1.2));
                                    $this->_setFont($page, 'bold', ($font_size_product + 1), $font_family_product, $non_standard_characters, 'white');
                                    $this->drawText($page,$qty_item, $qtyX, $temp_y, 'UTF-8');

                                } else {
                                    $this->_setFont($page, 'bold', ($font_size_product), $font_family_product, $non_standard_characters, $font_color_product);
                                    $this->drawText($page,$qty_item, $qtyX, $temp_y, 'UTF-8');
                                }
                                $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            } else
                                $this->drawText($page,$qty_item, $qtyX, $temp_y, 'UTF-8');
                        }
                        if(isset($font_family_product_temp)){
                            $font_family_product = $font_family_product_temp;
                            $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                        }
                        if($combine_product_line == 1){
                            $qtyX_real = $qtyX - 7 + (strlen($qty_item) * $font_size_product);
							if($product_qty_rectangle == 1) $qtyX_real += 4;
                            if($show_product_qty == 1) $this->drawText($page,'x', $qtyX_real + 10, $temp_y, 'UTF-8');
                            if ($store_view == "storeview")
                                $name_item = $item->getName();
                            elseif($store_view == "specificstore" && $specific_store_id != "") {
                                $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
                                if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                else $name_item = trim($item->getName());
                            }
                            else
                                $name_item = $this->getNameDefaultStore($item);
                            $array_line_item = array();
                            $tem_qtyX = $qtyX_real + 2*$font_size_product + 5;
                            $string_combined = '';
                            if($show_product_sku == 1 || $show_product_sku == 'configurable') $array_line_item[$skuX] = $item->getSku();
                            if($show_product_name == 1 || $show_product_name == 'configurable') $array_line_item[$productX] = $name_item;
                            $attributeValue = $this->getAttributeValueZebra($shelving_real_attribute, $item, $shelving_real_attribute_yn);
                            if (($shelving_real_attribute_yn == 1 || $shelving_real_attribute_yn == 'configurable') && $shelving_real_attribute != '' && $attributeValue != '')
                                $array_line_item[$shelving_attributeX] = '[' . $attributeValue . ']';

                            if($show_product_options_yn == 1){
                                $OrderProductItemOptions = $item->getProductOptions();
                                $attributeValue = '';
                                if(isset($OrderProductItemOptions['attributes_info'])){
                                    foreach ($OrderProductItemOptions['attributes_info'] as $productOptions) {
                                        $attributeValue .= '[' . $productOptions['label'] . ' : ' . $productOptions['value'] . '] ';
                                    }
                                }
                                if(isset($OrderProductItemOptions['options'])){
                                    foreach ($OrderProductItemOptions['options'] as $productOptions) {
                                        $attributeValue .= '[' . $productOptions['label'] . ' : ' . $productOptions['value'] . '] ';
                                    }
                                }
                                if(isset($OrderProductItemOptions['attributes_info']) || isset($OrderProductItemOptions['options'])){
                                    $array_line_item[$shelving_attributeX] = $attributeValue;
                                }
                            }

                            ksort($array_line_item);
                            foreach($array_line_item as $key=>$value){
                                if($string_combined == '')
                                    $string_combined = $array_line_item[$key];
                                else
                                    $string_combined = $string_combined . '  ' . $array_line_item[$key] ;
                            }
                            if(strlen($string_combined) > 0){
                                $max_line_product_length = $label_width - 2*$font_size_product - $right_margin_line;
                                $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                $temp_temp_y = $temp_y;
                                $line_width_name = $this->parseString($string_combined, $font_temp_shelf2, ($font_size_product));
                                $char_width_name = $line_width_name / strlen($string_combined);
                                $max_chars_name = round($max_line_product_length / $char_width_name);
                                $multiline_name = wordwrap($string_combined, $max_chars_name, "\n");
                                $token = strtok($multiline_name, "\n");
                                if ($token != false) {
                                    while ($token != false) {
                                        $this->drawText($page,$token, $tem_qtyX, $temp_temp_y, 'UTF-8');
                                        $temp_temp_y = $temp_temp_y - ($font_size_product);
                                        $token = strtok("\n");
                                    }
                                } else {
                                    $this->drawText($page,$token, $tem_qtyX, $temp_temp_y, 'UTF-8');
                                }
                                $temp_y = $temp_temp_y;
                                if($temp_y < $min_temp_y) $min_temp_y = $temp_y;
                            }
                        }

                        if (($show_product_sku == 1 || $show_product_sku == 'configurable') && ($combine_product_line == 0)) {
                            //$this->drawText($page,' x ', $qtyX + (strlen($qty_item) * $font_size_product), $temp_y, 'UTF-8');
							$xpos_x = 0;
							$xpos_x = ((($qtyX + $skuX)/2) - 2);
							if(($qty_item > 1) && ($product_qty_rectangle == 1)) {
								$xpos_x += ($font_size_product*0.75);
								$skuX += ($font_size_product*0.5);
								$productX += ($font_size_product*0.5);
								$shelving_attributeX += ($font_size_product*0.5);
							}
                            if($show_product_qty == 1) $this->drawText($page,' x ', $xpos_x, $temp_y, 'UTF-8');
							unset($xpos_x);
                            $sku_item         = $item->getSku();
                            $simple_sku       = $sku_item;
                            $_product_temp = Mage::getModel('catalog/product');
                            $simpleProductId = $_product_temp->getIdBySku($simple_sku);
                            $_product_temp->load($simpleProductId);
                            if ($_product_temp->getId() && $show_product_sku == 'configurable') {
                                $objConfigurableProduct = Mage::getModel('catalog/product_type_configurable');
                                $arrConfigurableProductIds = $objConfigurableProduct->getParentIdsByChild($simpleProductId);
                                if (is_array($arrConfigurableProductIds)) {
                                    $sku_temp = '';
                                    $sku_comma = '';
                                    foreach ($arrConfigurableProductIds as $key => $productId_temp) {
                                        $product_temp = '';
                                        $product_temp = Mage::getModel('catalog/product')->load($productId_temp);
                                        $sku_temp .= $sku_comma . $product_temp->getSku();
                                        $sku_comma = ', ';
                                    }
                                    if ($sku_temp != '') $sku_item = $sku_temp;
                                }
                            }
                            if($show_product_name ==1 || $show_product_name == 'configurable')
                                $max_name_length  = $productX - $skuX;
                            elseif (($shelving_real_attribute_yn == 1 || $shelving_real_attribute_yn == 'configurable') && $shelving_real_attribute != '')
                                $max_name_length = $shelving_attributeX - $skuX;
                            else
                                $max_name_length = $label_width - $skuX - $right_margin_line;
                            $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            $temp_temp_y = $temp_y;
                            $line_width_name = $this->parseString($sku_item, $font_temp_shelf2, ($font_size_product));
                            $char_width_name = $line_width_name / strlen($sku_item);


                            $max_chars_name = round($max_name_length / $char_width_name);
                            if($trim_product_sku_yn == 1){
                                $sku_item = str_trim($sku_item, 'WORDS', $max_chars_name - 2, '...');
                                $this->drawText($page,$sku_item, $skuX, $temp_temp_y, 'UTF-8');
                            }
                            else{
                                $old_temp_y = $temp_temp_y;
                                $lineName = 0;
                                $multiline_name = wordwrap($name_item, 58, "\n");
                                $token = strtok($multiline_name, "\n");
                                if ($token != false) {
                                    while ($token != false) {
                                        $this->drawText($page,$sku_item, $skuX, $temp_temp_y, 'UTF-8');
                                        $temp_temp_y = $temp_temp_y - ($font_size_product + 2);
                                        $token = strtok("\n");
                                        $lineName++;
                                    }
                                } else {
                                    $this->drawText($page,$sku_item, $skuX, $temp_temp_y, 'UTF-8');
                                }
                            }

                            if($lineName > 1)
                                $temp_y = $temp_temp_y;
                            else
                                $temp_y = $old_temp_y;

                            if($temp_temp_y < $min_temp_y)
								$min_temp_y = $temp_temp_y;
                            $line_width_sku = $this->parseString($sku_item, $font_temp_shelf2, ($font_size_product));
                        }
                        if (($show_product_name == 1 || $show_product_sku == 'configurable') && $combine_product_line == 0) {
                            if($show_product_sku == 0 && $show_product_qty == 1)
                                $this->drawText($page,' x ', ($qtyX + $productX)/2, $temp_y, 'UTF-8');
                            $temp_temp_y = $temp_y;
                            $simple_sku       = $item->getSku();
                            $_product_temp = Mage::getModel('catalog/product');
                            $simpleProductId = $_product_temp->getIdBySku($simple_sku);
                            if($show_product_name == 1){
                                switch ($store_view) {
                                    case 'itemname':
                                        $_newProduct =$helper->getProduct($simpleProductId);
                                        $name_item = trim($item->getName());
                                        break;
                                    case 'default':
                                        $_newProduct = $helper->getProduct($simpleProductId);
                                        if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                        if ($_newProduct->getData('name') == '') $name_item = trim($item->getName());
                                        break;
                                    case 'storeview':
                                        $_newProduct = $helper->getProductForStore($simpleProductId, $storeId);
                                        if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                        if ($_newProduct->getData('name') == '') $name_item = trim($item->getName());
                                        break;
                                    case 'specificstore':
                                        $_newProduct = $helper->getProductForStore($simpleProductId,$specific_store_id);
                                        if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                        if ($_newProduct->getData('name') == '') $name_item = trim($item->getName());
                                        break;
                                    default:
                                        $_newProduct =$helper->getProduct($simpleProductId);
                                        if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                        if ($_newProduct->getData('name') == '') $name_item = trim($item->getName());
                                        break;
                                }
                            }else{
                                if ($store_view == "storeview")
                                    $name_item = $item->getName();
                                elseif($store_view == "specificstore" && $specific_store_id != "") {
                                    $_newProduct = $helper->getProductForStore($simpleProductId, $specific_store_id);
                                    if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                    else $name_item = trim($item->getName());
                                }
                                else
                                    $name_item = $this->getNameDefaultStore($item);
                            }
                            $name_item        = trim(Mage::helper('pickpack/functions')->clean_method($name_item, 'pdf'));
                            if(isset($pricesN_priceX) && $pricesN_priceX > $productX)
                                $max_name_length  = $pricesN_priceX - $productX - 3;
                            else
                                $max_name_length  = $label_width - $productX - $right_margin_line;
                            $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                            if(strlen($name_item)){
                                $line_width_name = $this->parseString($name_item, $font_temp_shelf2, ($font_size_product));
                                $char_width_name = $line_width_name / strlen($name_item);
                                $max_chars_name  = round($max_name_length / $char_width_name);

                                if($show_product_options_yn == 1){
                                    $OrderProductItemOptions = $item->getProductOptions();
                                    $attributeValue = '';
                                    if(isset($OrderProductItemOptions['attributes_info'])){
                                        foreach ($OrderProductItemOptions['attributes_info'] as $productOptions) {
                                            $attributeValue .= ' [' . $productOptions['label'] . ' : ' . $productOptions['value'] . ']';
                                        }
                                    }
                                    if(isset($OrderProductItemOptions['options'])){
                                        foreach ($OrderProductItemOptions['options'] as $productOptions) {
                                            $attributeValue .= ' [' . $productOptions['label'] . ' : ' . $productOptions['value'] . ']';
                                        }
                                    }
                                    if(isset($OrderProductItemOptions['attributes_info']) || isset($OrderProductItemOptions['options'])){
                                        $name_item .= $attributeValue;
                                    }
                                }

                                if($trim_product_name_yn == 1){
                                    $name_item    = str_trim($name_item, 'WORDS', $max_chars_name - 2, '...');
                                    $this->drawText($page,$name_item, intval($productX), $temp_temp_y, 'UTF-8');
                                }
                                else{
                                    $multiline_name = wordwrap($name_item, $max_chars_name, "\n");
                                    $token = strtok($multiline_name, "\n");
                                    if ($token != false) {
                                        while ($token != false) {
                                            $this->drawText($page,$token, intval($productX), $temp_temp_y, 'UTF-8');
                                            $temp_temp_y = $temp_temp_y - ($font_size_product + 2);
                                            $token = strtok("\n");
                                        }
                                    } else {
                                        $this->drawText($page,$name_item, intval($productX), $temp_temp_y, 'UTF-8');
                                    }
                                }
                                if($temp_temp_y + ($font_size_product + 2)< $min_temp_y) $min_temp_y = $temp_temp_y + ($font_size_product + 2);
                            }
                        }
                        if (($show_product_price == 1 || $show_product_price == 2) && $combine_product_line == 0){
                            //$temp_temp_y = $temp_y;
                            if($show_product_price == 2){ //include tax
                                $price = $item->getData("tax_amount") + $item->getData('price');
                            }else
                                $price = $item->getprice();
                            $price = $this->formatPriceTxt($order, round($price,2));
                            $this->drawText($page,$price, $pricesN_priceX, $temp_y, 'UTF-8');
                        }
                        //order date
                        if ($show_order_date == 1 && $combine_product_line == 0){
                            //$temp_temp_y = $temp_y;
                            $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($store_id, $this->generalConfig['date_format']);
                            $order_date = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $this->generalConfig['date_format']);
                            //$this->_setFont($page, $font_style_label, ($font_size_order_id), $font_family_label, $non_standard_characters, $font_color_label);
                            $this->drawText($page,$order_date, intval($order_dateX) , $temp_y, 'UTF-8');
                        }
                        //custom attribute
                        if (($shelving_real_attribute_yn == 1 || $shelving_real_attribute_yn == 'configurable') && $shelving_real_attribute != '' && $combine_product_line == 0) {
                            $shelving_attributeX += $addon_rotate;
                            $attributeValue = $this->getAttributeValueZebra($shelving_real_attribute, $item, $shelving_real_attribute_yn);
                            if (isset($attributeValue) && $attributeValue != '') {
                                $this->drawText($page,'[' . $attributeValue . ']', $shelving_attributeX, $temp_y, 'UTF-8');
                            }
                        }
                        $temp_y = $temp_temp_y;
                        //TODO bundle options
                        $storeId = Mage::app()->getStore()->getId();
                        $options = $item->getProductOptions();
                        if (isset($options['bundle_options']) && is_array($options['bundle_options'])) {
                            $children = $item->getChildrenItems();
                            if (count($children)) {
                                $offset = 4;
                                $box_x  = ($qtyX - $offset);
                                $temp_y = $temp_y - $font_size_product;
                                $this->_setFont($page, $font_style_product, ($font_size_product - 2), $font_family_product, $non_standard_characters, $font_color_product);
                                $this->drawText($page,$helper->__('Bundle Options') . ' : ', $box_x, $temp_y, 'UTF-8');
                                foreach ($children as $child) {
                                    $sku_b         = $child->getSku();
                                    if ($store_view == "storeview")
                                        $name_b = $child->getName();
                                    elseif($store_view == "specificstore" && $specific_store_id != ""){
                                        $_product = $helper->getProductForStore($child->getProductId(), $specific_store_id);
                                        if ($_product->getData('name')) $name_b = trim($_product->getData('name'));
                                        if ($name_b == '') $name_b = trim($child->getName());
                                    }
                                    else
                                        $name_b = $this->getNameDefaultStore($child);
                                    $qty_b = (int) $child->getQtyOrdered();

                                    $temp_y = $temp_y - $font_size_product;
                                    if ($temp_y < $font_size_product) {
                                        $flag_newPage = 2;
                                        $page = $this->newPageZebra($settings);
                                        $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                                        $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                                        $current_x = $top_left_x;
                                        $current_y = $top_left_y;
                                        $min_temp_y = $temp_temp_y = $temp_y = $current_y;
                                    }
                                    //show qty bundle option
                                    if ($product_qty_upsize_yn == 1 && $qty_b > 1) {
                                        if ($product_qty_red == 1)
                                            $this->_setFont($page, 'bold', ($font_size_product - 1), $font_family_product, $non_standard_characters, 'darkRed');
                                        if ($product_qty_rectangle == 1) {

                                            $page->setLineWidth(1);
                                            $page->setLineColor($black_color);
                                            $page->setFillColor($black_color);
                                            if ($qty_b >= 100)
                                                $this->drawRectangle($page,($qtyX), ($temp_y), ($qtyX - 8 + (strlen($qty_b) * $font_size_product)), ($temp_y - 3 + $font_size_product * 1.2));
                                            else if ($qty_b >= 10)
                                                $this->drawRectangle($page,($qtyX - 1), ($temp_y), ($qtyX - 7 + (strlen($qty_b) * $font_size_product)), ($temp_y - 3 + $font_size_product * 1.2));
                                            else
                                                $this->drawRectangle($page,($qtyX - 1), ($temp_y), ($qtyX - 2 + (strlen($qty_b) * $font_size_product)), ($temp_y - 3 + $font_size_product * 1.2));
                                            $this->_setFont($page, 'bold', ($font_size_product - 1), $font_family_product, $non_standard_characters, 'white');
                                            $this->drawText($page,$qty_b, $qtyX, $temp_y, 'UTF-8');

                                        } else {
                                            $this->_setFont($page, 'bold', ($font_size_product - 2), $font_family_product, $non_standard_characters, $font_color_product);
                                            $this->drawText($page,$qty_b, $qtyX, $temp_y, 'UTF-8');
                                        }
                                        $this->_setFont($page, $font_style_product, $font_size_product - 2, $font_family_product, $non_standard_characters, $font_color_product);
                                    } else
                                        $this->drawText($page,$qty_b, $qtyX, $temp_y, 'UTF-8');
                                    $this->drawText($page,' x ', $qtyX + (strlen($qty_b) * $font_size_product), $temp_y, 'UTF-8');
                                    if ($show_product_sku == 1 || $show_product_sku == 'configurable') {
                                        $max_name_length = $productX - $skuX;
                                        $font_temp       = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                        $line_width_name = $this->parseString($sku_b, $font_temp, ($font_size_product));
                                        $char_width_name = $line_width_name / strlen($sku_b);
                                        $max_chars_name  = round($max_name_length / $char_width_name);
                                        $sku_b           = str_trim($sku_b, 'WORDS', $max_chars_name - 2, '...');
                                        $this->drawText($page,$sku_b, $skuX, $temp_y, 'UTF-8');
                                    }
                                    if ($show_product_name == 1 || $show_product_name == 'configurable') {
                                        $name_item       = trim(Mage::helper('pickpack/functions')->clean_method($name_item, 'pdf'));
                                        $max_name_length = $label_width - $productX;
                                        $font_temp       = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                                        $line_width_name = $this->parseString($name_b, $font_temp, ($font_size_product));
                                        $char_width_name = $line_width_name / strlen($name_b);
                                        $max_chars_name  = round($max_name_length / $char_width_name);
                                        if($trim_product_name_yn == 1)
                                            $name_b          = str_trim($name_b, 'WORDS', $max_chars_name - 2, '...');
                                        $this->drawText($page,$name_b, intval($productX), $temp_y, 'UTF-8');
                                    }
                                    //custom attribute
                                    if (($shelving_real_attribute_yn == 1 || $shelving_real_attribute_yn == 'configurable') && $shelving_real_attribute != '') {
                                        $attributeName = $shelving_real_attribute;
                                        $attributeValue = $this->getAttributeValueZebra($attributeName, $child, $shelving_real_attribute_yn);
                                        if (isset($attributeValue) && $attributeValue != "") {
                                            $this->drawText($page,'[' . $attributeValue . ']', $shelving_attributeX, $temp_y, 'UTF-8');
                                        }
                                    }
                                }
                            }
                        }

                        /** start print product barcode */
                        if ($this->zebralabelConfig['show_product_barcode']) {
                            $productsElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_labelzebra_products', array($this,$this->getOrder(),$this->generalConfig,$this->zebralabelConfig));
                            $productsElement->y = $temp_y;
                            $productsElement->order_item = $item;
                            $productsElement->page_padding = $this->page_padding;
                            $productsElement->showProductSkuBarcode();
                            $temp_y = $productsElement->y;
                        }
                        /** end print product barcode */

                        //$temp_y = $min_temp_y - $font_size_product;
                        $temp_y = $temp_y - $font_size_product;
                        if($final_zebra_summary == 1){
                            $summaryattributeValue = $this->getAttributeValueZebra($summary_custom_attribute, $item, $shelving_real_attribute_yn);//Mage::helper('pickpack')->getProductAttributeValue($product,$summary_custom_attribute);

                            $sku = $item->getSku();
                            if(isset($summaryattributeValue) && array_search(trim($summaryattributeValue), $priority_custom_attribute) !== false){
                                $has_attribute_priority = true;
                                $key = array_search(trim($summaryattributeValue), $priority_custom_attribute);
                                if( $key < $key_priority_attribute)
                                    $key_priority_attribute = $key;
                            }
                            //$item_zebra[$sku]["group"] = $this->getNameShippingLabel($order) . ' - ' . $summaryattributeValue ;
                            $item_zebra[$sku]["total_weight"] = $total_shipping_weight ;
                            $item_zebra[$sku]["total_items"] = $total_items;
                            $item_zebra[$sku]["total_orders"] = 1;
                        }


                    }

                    /** start print order comment bottom product list */
                    if ($this->zebralabelConfig['show_order_notes'] && $this->zebralabelConfig['order_notes_position'] == "yesunderproducts"){
                        $orderNotesElement = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_labelzebra_ordernotes', array($this,$this->getOrder(),$this->generalConfig,$this->zebralabelConfig));
                        $orderNotesElement->y = $temp_y;
                        $orderNotesElement->page_padding = $this->page_padding;
                        $orderNotesElement->showOrderNotes();
                        $this->y = $orderNotesElement->y;
                    }
                    /** end print order comment bottom product list */


                    if($subtotal_yn){
                        $page->setLineWidth(0.5);
                        $page->setLineColor($black_color);
                        $page->setFillColor($black_color);
                        $this->drawLine($page,$qtyX, $temp_y - $font_size_product, ($padded_right), $temp_y - $font_size_product);
                        $padding_left = 25 + $addon_rotate;
                        $padding_right = $padding_left + 90 + $addon_rotate;
                        $page->setLineWidth(0.5);
                        $page->setLineColor($black_color);
                        $page->setFillColor($white_color);
                        $line_count_subtotal = $subtotal_item_yn + $show_order_shipping + $show_order_grand;
                        $this->drawRectangle($page,$padding_left - 5, $temp_y - 2*$font_size_product, $padding_right + 50, $temp_y - ($line_count_subtotal + 3)*($font_size_product + 0.5));
                        $temp_y = $temp_y - 3*$font_size_product;
                        $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                        if($subtotal_item_yn == 1){
                            $this->drawText($page,$subtotal_item_title, $padding_left, $temp_y, 'UTF-8');
                            $subtotal         = $this->formatPriceTxt($order, round($order->getSubtotalInclTax(),2));
                            $this->drawText($page,$subtotal, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 1.5*$font_size_product;
                        }
                        if($show_order_shipping == 1){
                            $this->drawText($page,$order_shipping_title, $padding_left, $temp_y, 'UTF-8');
                            $shipping_price         = $this->formatPriceTxt($order, round($order->getShippingInclTax(),2));
                            $this->drawText($page,$shipping_price, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 1.5*$font_size_product;
                        }
                        if($show_order_grand){
                            $this->drawText($page,$order_grand_title, $padding_left, $temp_y, 'UTF-8');
                            $grand_total         = $this->formatPriceTxt($order, round($order->getData('grand_total'),2));
                            //$sku_item = str_trim($sku_item, 'WORDS', $max_chars_name - 2, '...');
                            $this->drawText($page,$grand_total, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 1.5*$font_size_product;
                        }
                        if($show_order_source){
                            $this->drawText($page,$order_source_title, $padding_left, $temp_y, 'UTF-8');
                            $store = Mage::getModel('core/store')->load($order->getStoreId());
                            $source_website         = $store->getName();
                            //$sku_item = str_trim($sku_item, 'WORDS', $max_chars_name - 2, '...');
                            $this->drawText($page,$source_website, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 1.5 * $font_size_product;
                        }
                    }
                }
                elseif($show_product_list == 1 && $separate_zebra_page_yn == 2){
                    $padding_left = 25;
                    $padding_right = $padding_left + 90;
                    $page_count = 0;
                    foreach ($itemsCollection as $item) {
                        if($page_count > 0){
                            $flag_newPage = 3;
                            $page = $this->newPageZebra($settings);
                            $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                            $current_x = $top_left_x;
                            $current_y = $top_left_y;
                            $min_temp_y = $temp_temp_y = $temp_y = $current_y;
                        }
                        //$temp_y = $temp_y - 2*$font_size_product;
                        $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                        //$total_shipping_weight = ($item->getData()["weight"] * $item->getQtyOrdered());
                        $product_simple = Mage::helper('pickpack/product')->getProductFromItem($item);
                        $total_shipping_weight = ($product_simple->getData("weight") * $item->getQtyOrdered());

                        $qty_item  = (int) $item->getQtyOrdered();
                        $total_items = $qty_item;
                        /*************/
                        if($show_product_qty == 1){
                            $this->_setFont($page, 'bold', $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,$product_qty_title, $padding_left, $temp_y, 'UTF-8');
                            if ($product_qty_upsize_yn == 1 && $qty_item > 1) {

                                if ($product_qty_red == 1)
                                    $this->_setFont($page, 'bold', ($font_size_product + 1), $font_family_product, $non_standard_characters, 'darkRed');
                                if ($product_qty_rectangle == 1) {

                                    $page->setLineWidth(1);
                                    $page->setLineColor($black_color);
                                    $page->setFillColor($black_color);

                                    if ($qty_item >= 100)
                                        $this->drawRectangle($page,($padding_right - 1), ($temp_y), ($padding_right  + (strlen($qty_item) * 2* $font_size_product/3)), ($temp_y - 3 + $font_size_product * 1.2));
                                    else if ($qty_item >= 10)
                                        $this->drawRectangle($page,($padding_right - 1), ($temp_y), ($padding_right + 1 + (strlen($qty_item) * 2* $font_size_product/3)), ($temp_y - 3 + $font_size_product * 1.2));
                                    else
                                        $this->drawRectangle($page,($padding_right - 1), ($temp_y), ($padding_right + 1 +(strlen($qty_item) * 2 * $font_size_product/3)), ($temp_y - 3 + $font_size_product * 1.2));
                                    $this->_setFont($page, 'bold', ($font_size_product + 1), $font_family_product, $non_standard_characters, 'white');
                                    $this->drawText($page,$qty_item, $padding_right, $temp_y, 'UTF-8');

                                } else {
                                    $this->_setFont($page, 'bold', ($font_size_product), $font_family_product, $non_standard_characters, $font_color_product);
                                    $this->drawText($page,$qty_item, $padding_right, $temp_y, 'UTF-8');
                                }
                                $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            } else
                                $this->drawText($page,$qty_item, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 3*$font_size_product;
                        }
                        if ($show_product_sku == 1 || $show_product_sku == 'configurable') {
                            $this->_setFont($page, 'bold', $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,$product_sku_title, $padding_left, $temp_y, 'UTF-8');

                            $sku_item         = $item->getSku();
                            $simple_sku       = $sku_item;
                            $_product_temp = Mage::getModel('catalog/product');
                            $simpleProductId = $_product_temp->getIdBySku($simple_sku);
                            $_product_temp->load($simpleProductId);
                            if ($_product_temp->getId() && $show_product_sku == 'configurable') {
                                $objConfigurableProduct = Mage::getModel('catalog/product_type_configurable');
                                $arrConfigurableProductIds = $objConfigurableProduct->getParentIdsByChild($simpleProductId);
                                if (is_array($arrConfigurableProductIds)) {
                                    $sku_temp = '';
                                    $sku_comma = '';
                                    foreach ($arrConfigurableProductIds as $key => $productId_temp) {
                                        $product_temp = '';
                                        $product_temp = Mage::getModel('catalog/product')->load($productId_temp);
                                        $sku_temp .= $sku_comma . $product_temp->getSku();
                                        $sku_comma = ', ';
                                    }
                                    if ($sku_temp != '') $sku_item = $sku_temp;
                                }
                            }
                            //$sku_item = str_trim($sku_item, 'WORDS', $max_chars_name - 2, '...');
                            $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,$sku_item, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 3*$font_size_product;
                        }
                        if ($show_product_name == 1 || $show_product_name == 'configurable') {
                            $this->_setFont($page, 'bold', $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,$product_name_title, $padding_left, $temp_y, 'UTF-8');
                            if($show_product_sku == 0 && $show_product_qty == 1)
                                $this->drawText($page,' x ', ($qtyX + $productX)/2, $temp_y, 'UTF-8');
                            $temp_temp_y = $temp_y;
                            $simple_sku       = $item->getSku();
                            $_product_temp = Mage::getModel('catalog/product');
                            $simpleProductId = $_product_temp->getIdBySku($simple_sku);
                            if($show_product_name == 1){
                                switch ($store_view) {
                                    case 'itemname':
                                        $_newProduct =$helper->getProduct($simpleProductId);
                                        $name_item = trim($item->getName());
                                        break;
                                    case 'default':
                                        $_newProduct = $helper->getProduct($simpleProductId);
                                        if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                        if ($name_item == '') $name_item = trim($item->getName());
                                        break;
                                    case 'storeview':
                                        $_newProduct = $helper->getProductForStore($simpleProductId, $storeId);
                                        if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                        if ($name_item == '') $name_item = trim($item->getName());
                                        break;
                                    case 'specificstore':
                                        $_newProduct = $helper->getProductForStore($simpleProductId,$specific_store_id);
                                        if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                        if ($name_item == '') $name_item = trim($item->getName());
                                        break;
                                    default:
                                        $_newProduct =$helper->getProduct($simpleProductId);
                                        if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                        if ($name_item == '') $name_item = trim($item->getName());
                                        break;
                                }
                            }else{
                                if ($store_view == "storeview")
                                    $name_item = $item->getName();
                                elseif($store_view == "specificstore" && $specific_store_id != "") {
                                    $_newProduct = $helper->getProductForStore($simpleProductId, $specific_store_id);
                                    if ($_newProduct->getData('name')) $name_item = trim($_newProduct->getData('name'));
                                    else $name_item = trim($item->getName());
                                }
                                else
                                    $name_item = $this->getNameDefaultStore($item);
                            }
                            $name_item        = trim(Mage::helper('pickpack/functions')->clean_method($name_item, 'pdf'));
                            $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $temp_y -= $font_size_product;
                            //if($trim_product_name_yn == 1){
                            //$name_item    = str_trim($name_item, 'WORDS', $max_chars_name - 2, '...');
                            $this->drawText($page,$name_item, $padding_left + 30, $temp_y, 'UTF-8');
                            //}
                            $temp_y = $temp_y - 3*$font_size_product;
                        }
                        if ($show_product_price == 1 || $show_product_price == 2) {
                            $this->_setFont($page, 'bold', $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,$price_title, $padding_left, $temp_y, 'UTF-8');

                            if($show_product_price == 2){
                                $price = $item->getData("tax_amount") + $item->getData('price');
                            }else
                                $price = $item->getprice();
                            $price_item = $this->formatPriceTxt($order, round($price,2));
                            //$sku_item = str_trim($sku_item, 'WORDS', $max_chars_name - 2, '...');
                            $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,$price_item, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 3*$font_size_product;
                        }
                        //order date
                        if ($show_order_date == 1){
                            $this->_setFont($page, 'bold', $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,$order_date_title, $padding_left, $temp_y, 'UTF-8');

                            $date_format_strftime = Mage::helper('pickpack/functions')->setLocale($store_id, $this->generalConfig['date_format']);
                            $order_date = Mage::helper('pickpack/functions')->createOrderDateByFormat($order, $date_format_strftime, $this->generalConfig['date_format']);
                            //$this->_setFont($page, $font_style_label, ($font_size_order_id), $font_family_label, $non_standard_characters, $font_color_label);
                            $this->drawText($page,$order_date, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 3*$font_size_product;
                        }
                        //custom attribute
                        if (($shelving_real_attribute_yn == 1 || $shelving_real_attribute_yn == 'configurable') && $shelving_real_attribute != '') {
                            $this->_setFont($page, 'bold', $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,$shelving_title, $padding_left, $temp_y, 'UTF-8');
                            $attributeValue = $this->getAttributeValueZebra($shelving_real_attribute, $item, $shelving_real_attribute_yn);
                            $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            if (isset($attributeValue) && $attributeValue != '') {
                                $this->drawText($page,$attributeValue , $padding_right, $temp_y, 'UTF-8');
                            }
                            $temp_y = $temp_y - 3*$font_size_product;
                        }
                        $page_count ++;
                    }
                    unset($page_count);
                    if($subtotal_yn){
                        if($show_order_shipping == 1){
                            $this->drawText($page,$order_shipping_title, $padding_left, $temp_y, 'UTF-8');
                            $shipping_price         = round($order->getShippingAmount(),2);
                            $this->drawText($page,$shipping_price, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 3*$font_size_product;
                        }
                        if($show_order_grand){
                            $this->drawText($page,$order_grand_title, $padding_left, $temp_y, 'UTF-8');
                            $grand_total         = round($order->getData('grand_total'),2);
                            //$sku_item = str_trim($sku_item, 'WORDS', $max_chars_name - 2, '...');
                            $this->drawText($page,$grand_total, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 3*$font_size_product;
                        }
                        if($show_order_source){
                            $this->drawText($page,$order_source_title, $padding_left, $temp_y, 'UTF-8');
                            $store = Mage::getModel('core/store')->load($order->getStoreId());
                            $source_website         = $store->getName();
                            //$sku_item = str_trim($sku_item, 'WORDS', $max_chars_name - 2, '...');
                            $this->drawText($page,$source_website, $padding_right, $temp_y, 'UTF-8');
                            $temp_y = $temp_y - 3 * $font_size_product;
                        }
                    }
                    //draw customer comment here
                    if($show_customer_comment == 1){
                        $customer_comments = $this->getCustomerComments($order);
                        if(count($customer_comments) > 0){
                            $this->_setFont($page, "bold", $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            $this->drawText($page,"Comment", $padding_left, $temp_y, 'UTF-8');
                            $this->_setFont($page, $font_style_product, $font_size_product, $font_family_product, $non_standard_characters, $font_color_product);
                            foreach ($customer_comments as $key => $comment) {
                                if(trim($comment['text']) != ''){
                                    $this->drawText($page,$comment['text'], $padding_right, $temp_y, 'UTF-8');
                                    $temp_y = $temp_y - $font_size_product - 2;
                                }
                            }
                        }
                    }
                }

                //check zebra summary_zebra
                if($final_zebra_summary == 1){
                    //if($has_attribute_priority){
                    $order_zebra["total_weight"] = 0;
                    $order_zebra["total_items"] = 0;
                    foreach($item_zebra as $item_each){
                        if($has_attribute_priority == 1)
                            $order_zebra["group"] = $this->getNameShippingLabel($order) . '- ' . $priority_custom_attribute[$key_priority_attribute] ;
                        else
                            $order_zebra["group"] = $this->getNameShippingLabel($order) . '- ' . $priority_custom_attribute[0] ;
                        $order_zebra["total_weight"] += $item_each["total_weight"] ;
                        $order_zebra["total_items"] += $item_each["total_items"];
                        $order_zebra["total_orders"] = 1;
                    }
                    if (isset($order_zebra["group"])){
                        if(isset($summary_zebra[$order_zebra["group"]])){
                            $summary_zebra[$order_zebra["group"]]["total_weight"] += $order_zebra["total_weight"];
                            $summary_zebra[$order_zebra["group"]]["total_items"] += $order_zebra["total_items"];
                            $summary_zebra[$order_zebra["group"]]["total_orders"] += $order_zebra["total_orders"];
                        }
                        else{
                            $summary_zebra[$order_zebra["group"]] = $order_zebra;
                        }
                    }
                }
                // logo on side
                $page->rotate(0, 0, 0 - $rotate_label);
            }

            /************************PRINTING AUTOCN22 PAGE**************************/
            if ($this->_config['add_cn22_page_yn']){
                $filter_shipping_zone_yn =  $this->_getConfig('filter_shipping_zone_yn','0', false,'custom_section',$store_id,true,'cn22_options');
                $print_cn22_page_yn = true;
                if ( $filter_shipping_zone_yn == 0 ){
                    $print_cn22_page_yn = true;
                }elseif ($filter_shipping_zone_yn == 1 && $this->isInShippingZone($order) == true){
                    $print_cn22_page_yn = false;
                }
                if ($print_cn22_page_yn){
                    $cn22 = new Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Cn22();
                    $page = $this->newPageZebra($settings);

                    $cn22->printOneLabelCN22($page,$order,$store_id, $this->_config['add_cn22_page_nudge'][0], - $this->_config['add_cn22_page_nudge'][1],$this->_config['add_cn22_page_rotate']);
                }
            }
            /************************END PRINTING AUTOCN22 PAGE**************************/
        }
        unset($category_label);
        $current_y = $temp_y;
        $i         = 0;

        //draw label summary
        if($final_zebra_summary == 1){
            $flag_newPage = 4;
            $page = $this->newPageZebra($settings);
            $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
            $current_x = $top_left_x;
            $current_y = $top_left_y;
            $min_temp_y = $temp_temp_y = $temp_y = $current_y;
            $this->_setFont($page, "regular" , $font_size_product + 5, $font_family_product, $non_standard_characters, $font_color_product);
            $this->drawLabelSummary($summary_zebra, $page, $temp_x, $temp_y, $font_size_product, count($orders), $padded_right, $unit_weight);
        }
        if ($return_address_yn === '1') {
            while ($i < $address_count) {
                // going top left down, then across
                // if last label bigger than 1 label, start on fresh

                if (($current_y - $temp_y) > $label_height)
                    $current_y = ($current_y - $label_height);
                if (($temp_y - $label_height) < 0) {
                    $current_y = $top_left_y;
                    if (($current_x + $label_width) > $paper_width) {
                        $flag_newPage = 5;
                        $page = $this->newPageZebra($settings);
                        $page->rotate($rotate_point[0], $rotate_point[1], $rotate_label);
                        $current_x = $top_left_x;
                        $current_y = $top_left_y;
                        $min_temp_y = $temp_temp_y = $temp_y = $current_y;
                    } else {
                        $current_x += $label_width;
                    }
                } else {
                    $current_y -= $label_height;
                }


                $temp_y = $current_y;
                $temp_x = $current_x;

                // footer store address
                $this->_setFont($page, $font_style_return_label, ($font_size_return_label + 2), $font_family_return_label, $non_standard_characters, $font_color_return_label);
                $this->_setFont($page, $font_style_return_label, $font_size_return_label, $font_family_return_label, $non_standard_characters, $font_color_return_label);
                $line_height = 15;
                foreach (explode("\n", $return_address) as $value) {
                    if ($value !== '') {
                        $this->drawText($page,trim(strip_tags($value)), $temp_x, ($temp_y - $line_height), 'UTF-8');
                        $line_height = ($line_height + $font_size_return_label);
                    }
                }
                $i++;
            }
        }

        $this->_afterGetPdf();
        return $pdf;
    }

    //this function will draw text with right position when we rotate pdf
    private function drawText($page,$text = '', $x = 0, $y = 0, $charEncoding = 'UTF-8'){
        if (isset($page)){
            $page->drawText($text, $x + $this->_base_position['X'], $y + $this->_base_position['Y'], $charEncoding);
        }
    }

    //this function will draw image with right position when we rotate pdf
    private function drawImage($page, $image, $x1, $y1, $x2, $y2) {
        if (isset($page)){

            $page->drawImage($image, $x1 + $this->_base_position['X'], $y1 + $this->_base_position['Y'], $x2 + $this->_base_position['X'], $y2 + $this->_base_position['Y']);
        }
    }

    //this function will draw Rectangle with right position when we rotate pdf
    private function drawRectangle($page,$x1, $y1, $x2, $y2, $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE){
        if (isset($page)){
            $page->drawRectangle($x1 + $this->_base_position['X'], $y1 + $this->_base_position['Y'], $x2 + $this->_base_position['X'], $y2 + $this->_base_position['Y'], $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
        }
    }

    //this function will draw Rectangle with right position when we rotate pdf
    private function drawLine($page, $x1, $y1, $x2, $y2){
        if (isset($page)){
            $page->drawLine($x1 + $this->_base_position['X'], $y1 + $this->_base_position['Y'], $x2 + $this->_base_position['X'], $y2 + $this->_base_position['Y']);
        }

    }

    private function setLabelZebraConfig($storeId) {
        if (!$storeId){
            $storeId = Mage::app()->getStore()->getId();
        }
        $this->_config['use_courierrules_shipping_label'] = $this->_getConfig('use_courierrules_shipping_label',0, false, 'label_zebra', $storeId);
        $this->_config['rotate_label'] = $this->_getConfig('rotate_label',0, false, 'label_zebra', $storeId);
        $this->_config['resolution_label'] = $this->_getConfig('resolution_label',0, false, 'label_zebra', $storeId);
        $this->_config['nudge_demension_zebra'] = explode(",", $this->_getConfig('nudge_demension_zebra', '432,288', false, 'label_zebra',$storeId));
        $this->_label_width = $this->_config['nudge_demension_zebra'][0];
        $this->_label_height = $this->_config['nudge_demension_zebra'][1];
        $this->_config['paper_margin_zebra'] = explode(",", $this->_getConfig('paper_margin_zebra', '13,5,5,5', false, 'label_zebra',$storeId));
        $this->_config['label_padding_zebra'] = explode(",", $this->_getConfig('label_padding_zebra', '13,5,5,5', false, 'label_zebra',$storeId));
        $this->_config['nudge_shipping_address_zebra'] = explode(",", $this->_getConfig('nudge_shipping_address_zebra', '0,0', false, 'label_zebra',$storeId));
        $this->_config['show_address_barcode_yn_zebra'] = $this->_getConfig('show_address_barcode_yn_zebra', 0, false, 'label_zebra',$storeId);
        $this->_config['nudge_barcode'] = explode(",", $this->_getConfig('nudge_barcode', '0,0', true, 'label_zebra', $storeId));
        $this->_config['show_order_id_barcode_yn'] = $this->_getConfig('show_order_id_barcode_yn', 1, false, 'label_zebra',$storeId);
        $this->_config['nudge_order_id_barcode'] = explode(",", $this->_getConfig('nudge_order_id_barcode', '0,0', true, 'label_zebra', $storeId));
        $this->_config['label_show_order_id_yn'] = $this->_getConfig('label_show_order_id_yn', 1, false, 'label_zebra',$storeId);
        $this->_config['nudge_order_id'] = explode(",", $this->_getConfig('nudge_order_id', '0,0', true, 'label_zebra', $storeId));
        $this->_config['font_size_order_id'] = $this->_getConfig('font_size_order_id', 9, false, 'label_zebra', $storeId);
        $this->_config['capitalize_zebra_yn'] = $this->_getConfig('capitalize_zebra_yn', '', false, 'label_zebra');

        $this->_config['add_cn22_page_yn'] = $this->_getConfig('add_cn22_page_yn', 0, false, 'label_zebra', $storeId);
        $this->_config['add_cn22_page_nudge'] = explode(",", $this->_getConfig('add_cn22_page_nudge', '0,0', false, 'label_zebra', $storeId));
        $this->_config['add_cn22_page_rotate'] = $this->_getConfig('add_cn22_page_rotate', 1, false, 'label_zebra', $storeId);

        /*return address config*/
        $this->_config['label_return_address_yn'] = $this->_getConfig('label_return_address_yn', 0, false, 'label_zebra'); // 0,1,yesside
        if ($this->_config['label_return_address_yn'] == 'yesside') {
            $this->_config['font_family_return_label_side'] = $this->_getConfig('font_family_return_label_side', 'helvetica', false, 'label_zebra', $storeId);
            $this->_config['font_style_return_label_side'] = $this->_getConfig('font_style_return_label_side', 'regular', false, 'label_zebra', $storeId);
            $this->_config['font_size_return_label_side'] = $this->_getConfig('font_size_return_label_side', 9, false, 'label_zebra', $storeId);
            $this->_config['font_color_return_label_side'] = trim($this->_getConfig('font_color_return_label_side', 'Black', false, 'label_zebra', $storeId));
            $this->_config['nudge_return_label'] = explode(",", $this->_getConfig('nudge_return_label', '0,0', true, 'label_zebra', $storeId));
            $this->_config['label_return_address_side'] = $this->_getConfig('label_return_address_side', '', false, 'label_zebra', $storeId);
            $this->_config['rotate_return_label_side'] = $this->_getConfig('rotate_return_label_side', 1, false, 'label_zebra', $storeId);
        } elseif ($this->_config['label_return_address_yn'] == '1') {
            $this->_config['label_return_address'] = $this->_getConfig('label_return_address', '', false, 'label_zebra', $storeId);
            $this->_config['font_family_return_label'] = $this->_getConfig('font_family_return_label', 'helvetica', false, 'label_zebra', $storeId);
            $this->_config['font_style_return_label'] = $this->_getConfig('font_style_return_label', 'regular', false, 'label_zebra', $storeId);
            $this->_config['font_size_return_label'] = $this->_getConfig('font_size_return_label', 9, false, 'label_zebra', $storeId);
            $this->_config['font_color_return_label'] = trim($this->_getConfig('font_color_return_label', 'Black', false, 'label_zebra', $storeId));
        } else {
            $this->_config['font_style_return_label'] = 15;
            $this->_config['rotate_return_label_side'] = 0;
        }
        /*end return address config*/
        /*set adddition position for every $page->draw method*/
        if (!isset($this->_base_position['X'])||!isset($this->_base_position['Y'])){
            if ($this->_config['rotate_label'] == 1){
                $this->_base_position['X'] = 0;
                $this->_base_position['Y'] = -intval($this->_config['nudge_demension_zebra'][1]);
            }elseif ($this->_config['rotate_label'] == 2){
                $this->_base_position['X'] = -intval($this->_config['nudge_demension_zebra'][0]);
                $this->_base_position['Y'] = 0;
            }else{
                $this->_base_position['X'] = 0;
                $this->_base_position['Y'] = 0;
            }
        }
        /*end set adddition position for every $page->draw method*/
    }

    //this function will set print position of return address base on rotate of ZebraLabel and rotate of ReturnAddressLabel
    private function getReturnAddressPosition($nudgeX, $nudgeY, $address_line_x = 20, $address_line_y = 100){
        $position =array();
        $position['X'] = $nudgeX;
        $position['Y'] = $nudgeY;
        if ($this->_config['rotate_return_label_side'] == 0) {
            $position['X'] += $this->_base_position['X'] + $address_line_x;
            $position['Y'] += $this->_base_position['Y'] + $address_line_y + 20;
        } elseif ($this->_config['rotate_return_label_side'] == 1) {
            if ($this->_config['rotate_label'] == 1){
                $position['X'] += - ($this->_label_height - 20);
                $position['Y'] += - ($this->_label_width - $this->_config['font_size_return_label_side'] * 2);
            }elseif ($this->_config['rotate_label'] == 2){
                $position['X'] += 20;
                $position['Y'] += $this->_config['font_size_return_label_side'] * 2;
            }else{
                $position['X'] += 20;
                $position['Y'] += - ($this->_label_width - $this->_config['font_size_return_label_side'] * 2);
            }
        } elseif ($this->_config['rotate_return_label_side'] == 2) {
            if ($this->_config['rotate_label'] == 1){
                $position['X'] += 20;
                $position['Y'] += $this->_config['font_size_return_label_side'] * 2;
            }elseif ($this->_config['rotate_label'] == 2){
                $position['X'] += - ($this->_label_height - 20);
                $position['Y'] += - ($this->_label_width - $this->_config['font_size_return_label_side'] * 2);
            }else{
                $position['X'] += - ($this->_label_height - 20);
                $position['Y'] += $this->_config['font_size_return_label_side'] * 2;
            }
        }
        return $position;
    }

    // this function will print ReturnAddress on same label with ZebraLabel
    private function printReturnAddressOnSameLabel($page,$address_line_x, $address_line_y) {
        $store_id = Mage::app()->getStore()->getId();
        $non_standard_characters = $this->_getConfig('non_standard_characters', 0, false, 'general', $store_id);
        $rotate = $this->getRotateReturnAddress($this->_config['rotate_return_label_side']);
        //this value will set print position of return address base on rotate of ZebraLabel and rotate of ReturnAddressLabel
        $return_address_position = $this->getReturnAddressPosition($this->_config['nudge_return_label'][0], $this->_config['nudge_return_label'][1], $address_line_x, $address_line_y);

        $max_chars            = $this->getMaxchars($this->_config['font_size_return_label_side'], $this->_config['label_return_address_side'], $this->_label_height);
        $return_address       = wordwrap($this->_config['label_return_address_side'], $max_chars, "\n", false);
        $return_address_lines = explode("\n", $return_address);
        $this->_setFont($page, $this->_config['font_style_return_label_side'], $this->_config['font_size_return_label_side'], $this->_config['font_family_return_label_side'], $non_standard_characters, $this->_config['font_color_return_label_side']);

        $return_address_title_fontsize = 0;
        $from_text = Mage::helper('pickpack')->__('From');

        $page->rotate(0, 0, $rotate);

        if (preg_match('~^' . $from_text . '~', $return_address_lines[0])) {
            $return_address_title_fontsize = -2;
            if ($this->_config['font_size_return_label_side'] > 10)
                $return_address_title_fontsize = 2;
            $this->_setFontRegular($page, ($this->_config['font_size_return_label_side'] - $return_address_title_fontsize));
            $this->_setFont($page, $this->_config['font_style_return_label_side'], ($this->_config['font_size_return_label_side'] - $return_address_title_fontsize), $this->_config['font_family_return_label_side'], $non_standard_characters, $this->_config['font_color_return_label_side']);
            $page->drawText(trim($return_address_lines[0]), $return_address_position['X'], $return_address_position['Y'], 'UTF-8');
        } else
            $page->drawText(trim($return_address_lines[0]), $return_address_position['X'], $return_address_position['Y'], 'UTF-8');

        $this->_setFont($page, $this->_config['font_style_return_label_side'], $this->_config['font_size_return_label_side'], $this->_config['font_family_return_label_side'], $non_standard_characters, $this->_config['font_color_return_label_side']);
        $line_height = ($this->_config['font_size_return_label_side'] - ($return_address_title_fontsize * 2));
        foreach ($return_address_lines as $key => $value) {
            if ($key > 0) {
                $page->drawText(trim(strip_tags($value)), $return_address_position['X'], ($return_address_position['Y'] - $line_height), 'UTF-8');
                $line_height += $this->_config['font_size_return_label_side'];
            }
        }
        unset($return_address_lines);
        unset($return_address);
        $page->rotate(0, 0, -$rotate);
    }

    public function newPageZebra(array $settings = array()) {
        $pageSize     = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page         = $this->_getPdf()->newPage($pageSize);

        $this->_getPdf()->pages[] = $page;
        $pageSize = explode(":", $pageSize);
        $this->y                  = ($pageSize[1] - 20);
        $this->setCurrentPage($page);

        return $page;
    }

    public function showTrackingNumber($page,$order,$x,$y) {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $tracking_number_nudge = explode(",", $this->_getConfig('tracking_number_nudge', '0,0', true, $wonder, $storeId));
        $tracking_number_fontsize = $this->_getConfig('tracking_number_fontsize', 15, false, $wonder, $storeId);
		
        $this->_setFont($page, $this->generalConfig['font_style_label'], $tracking_number_fontsize, $this->generalConfig['font_family_label'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_label']);
//        if(!isset($tracking_number_barcode_nudge))
//            $tracking_number_barcode_nudge = array(0,0);
        $tracking_number = $this->getTrackingNumber($order);

        if($tracking_number != '')
            $page->drawText($tracking_number, ($x + $tracking_number_nudge[0]), ($y + $tracking_number_nudge[1]), 'CP1252');
    }

    public function showTrackingNumberBarcode($page, $order, $x, $y) {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();

        $font_family_barcode = Mage::helper('pickpack/barcode')->getFontForType($this->generalConfig['font_family_barcode']);
        $whiteColor = Mage::helper('pickpack/config_color')->getPdfColor('white_color');

        $barcode_font_size = $this->_getConfig('tracking_number_barcode_fontsize', 15, false, $wonder, $storeId);
        $tracking_number_barcode_nudge = explode(",", $this->_getConfig('tracking_number_barcode_nudge', '0,0', true, $wonder, $storeId));

        $tracking_number = $this->getTrackingNumber($order);
        if($tracking_number != ''){
            $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($tracking_number, $this->generalConfig['font_family_barcode']);
            $barcode_font_size_action = $barcode_font_size;
            if($barcode_font_size > 18) $barcode_font_size = 15;

            $barcodeWidth = 1.35 * Mage::helper('pickpack/font')->parseString($tracking_number, Zend_Pdf_Font::fontWithPath(Mage::helper('pickpack')->getFontPath() . $font_family_barcode), $barcode_font_size);
            $page->setFillColor($whiteColor);
            $page->setLineColor($whiteColor);
            $page->drawRectangle(($x - 2 + $tracking_number_barcode_nudge[0]), ($y - 2 + $tracking_number_barcode_nudge[1] ), ($x + $barcodeWidth + 2 + $tracking_number_barcode_nudge[0]), ($y + ($barcode_font_size * 1.4) + $tracking_number_barcode_nudge[1]));
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
            $page->setFont(Zend_Pdf_Font::fontWithPath(Mage::helper('pickpack')->getFontPath() . $font_family_barcode), $barcode_font_size);
            $page->drawText($barcodeString, ($x + $tracking_number_barcode_nudge[0]), ($y + $tracking_number_barcode_nudge[1]), 'CP1252');
            if($barcode_font_size_action > 18)
            {
                if($barcode_font_size_action > 18 && $barcode_font_size_action <= 24)
                    $page->drawText($barcodeString, ($x + $tracking_number_barcode_nudge[0]), ($y + $tracking_number_barcode_nudge[1] + 19), 'CP1252');
                if($barcode_font_size_action >24 && $barcode_font_size_action <= 36){
                    $page->drawText($barcodeString, ($x + $tracking_number_barcode_nudge[0]), ($y + $tracking_number_barcode_nudge[1] + 19), 'CP1252');
                    $page->drawText($barcodeString, ($x + $tracking_number_barcode_nudge[0]), ($y + $tracking_number_barcode_nudge[1] + 38), 'CP1252');
                }
            }
        }
    }
}
