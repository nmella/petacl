<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

abstract class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract extends Varien_Object
{
    public $y;

    protected $action_path;
    protected $_pdfObject;
    protected $_order;
    protected $_isSplitSupplier;
    protected $_supplier;
	protected $_PdfPageCount = 1;

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

    public function __construct($arguments) {
        $this->action_path = Mage::helper('pickpack')->getFontPath();
        $this->_pdf = $arguments[0];
        $this->_order = $arguments[1];
    }

    public function isShowPrices() {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $prices_yn = $this->_getConfig('prices_yn', 0, false, $wonder, $storeId);
        $prices_hideforgift_yn = $this->_getConfig('prices_hideforgift_yn', 0, false, $wonder, $storeId);
        //if ($prices_hideforgift_yn == 1) $prices_yn = 0;

        return $prices_yn;
    }

    protected function sortMultiDimensional(&$array, $subKey, $subKey2, $sortorder_packing_bool=false, $sortorder_packing_secondary_bool=false) {
        return Mage::helper('pickpack')->sortMultiDimensional($array, $subKey, $subKey2, $sortorder_packing_bool, $sortorder_packing_secondary_bool);
    }

    public function isShowGiftWrap() {
        if(Mage::helper('pickpack')->isMageEnterprise())
            return $this->_getConfig('show_gift_wrap', 0, false, $this->getWonder(), $this->getStoreId());

        return 0;
    }

    public function isShowTax() {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $tax_yn = $this->_getConfig('tax_yn', 'no', false, $wonder, $storeId);
        if ($this->isShowPrices() == 0)
            $tax_yn = 'no';

        return $tax_yn;
    }

    public function getFirstItemTitleShift() {
        $first_item_title_shift = 0;
        if ($this->isShowPrices() != '0')
            $first_item_title_shift = -13;

        return $first_item_title_shift;
    }

    public function isShowTaxCol() {
        $tax_col_yn = 0;
        if ($this->isShowTax() == 'yesboth' || $this->isShowTax() == 'yescol')
            $tax_col_yn = 1;

        return $tax_col_yn;
    }

    /**
     * @return Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
     */
    public function getPdf() {
        return $this->_pdf;
    }

    public function setSupplier($supplier) {
        $this->_supplier = $supplier;
    }

    public function getSupplier() {
        return $this->_supplier;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() {
        return $this->_order;
    }

    /**
     * @return Zend_Pdf_Page
     */
    public function getPage($index = null) {
        return $this->getPdf()->getPage($index);
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStoreId() {
        return $this->getOrder()->getStoreId();
    }

    /**
     * @return varchar
     */
    public function getWonder() {
        return $this->getPdf()->getWonder();
    }

    /**
     * @return bool
     */
    public function isSplitSupplier() {
        if(is_null($this->_isSplitSupplier)) {
            if($this->getWonder() == 'wonder_invoice')
                $supplierKey = 'invoice';
            else
                $supplierKey = 'pack';
            $this->_isSplitSupplier = Mage::helper("pickpack/config_supplier")->isSplitSupplier($supplierKey, $this->getStoreId());
        }

        return $this->_isSplitSupplier;
    }

    public function getGeneralConfig() {
        return Mage::helper('pickpack/config')->getGeneralConfigArray($this->getStoreId());
    }

    public function getPageConfig() {
        return $this->getPdf()->getCurrentPageConfig();
    }

    public function getPackingsheetConfig($wonder = null, $storeId = null) {
        if (!isset($wonder))
            $wonder = $this->getWonder();
        if (!isset($storeId))
            $storeId = $this->getStoreId();
        return Mage::helper('pickpack/config')->getPackingsheetConfigArray($wonder, $storeId);
    }

    protected function _getConfig($field, $default = '', $add_default = true, $group = 'wonder', $store = null, $trim = true,$section = 'pickpack_options') {
        return Mage::helper('pickpack/config')->getConfig($field, $default, $add_default, $group, $store, $trim, $section);
    }

    /**
     * @param $object
     * @param string $style
     * @param int $size
     * @param string $font
     * @param int $non_standard_characters
     * @param string $color
     * @return string
     */
    protected function _setFont($object, $style = 'regular', $size = 10, $font = 'helvetica', $non_standard_characters = 0, $color = '') {
        $font = Mage::helper('pickpack/font')->getFont($style, $size, $font, $non_standard_characters);

        if(is_object($object)) {
			if( isset($color) && ($color != '') )
				$object->setFillColor(new Zend_Pdf_Color_Html($color));
	        if( isset($font) && ($font != '') )
				$object->setFont($font, $size);
		} else {
	        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
	        $this->getPage()->setFont($font, $size);
		}
        return $font;
    }

    /**
     * @return string
     */
    public function getPngTmpDir() {
        return Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'qrcode'.DS;
    }

    /**
     * @return bool
     */
    public function hasBillingAddress() {
        $hasBillingAddress = false;
        foreach ($this->getOrder()->getAddressesCollection() as $address) {
            if ($address->getAddressType() == 'billing' && !$address->isDeleted())
                $hasBillingAddress = true;
        }
        return $hasBillingAddress;
    }

    /**
     * @return bool
     */
    public function hasShippingAddress() {
        $hasShippingAddress = false;
        foreach ($this->getOrder()->getAddressesCollection() as $address) {
            if ($address->getAddressType() == 'shipping' && !$address->isDeleted())
                $hasShippingAddress = true;
        }
        return $hasShippingAddress;
    }

    public function getAddressFormat() {
        $override_address_format_yn = $this->_getConfig('override_address_format_yn', 0, false, 'general', $this->getStoreId());
        $custom_address_format = $this->_getConfig('address_format', '', false, 'general', $this->getStoreId());
        $default_address_format = Mage::getStoreConfig('customer/address_templates/pdf');
        $default_address_format = str_replace(array("depend", 'var ', '{{', '}}'), array("if", '', '{', '}'), $default_address_format);
        if ($override_address_format_yn == 1)
            $addressFormat = $custom_address_format;
        else
            $addressFormat = $default_address_format;

        return $addressFormat;
    }

    /**
     * @return Zend_Pdf_Page
     */
    public function newPage() {
        $page = $this->getPdf()->newPage();
        $this->y = $this->getPdf()->y;
        $this->getPdf()->_PdfPageCount +=1;
        return $page;
    }

    /**
     * @return int
     */
    public function getPageCount() {
		return $this->_PdfPageCount;
        // return $this->getPdf()->getPageCount();
    }

    /**
     * @param $shippingAddressArray
     * @param array $show_this_shipping_line
     * @return array
     */
    protected function getAddressLines($shippingAddressArray, $show_this_shipping_line = array()) {
        $ship_i = 0;
        foreach ($shippingAddressArray as $value) {

            $value = trim($value);
            $value = preg_replace('~^,$~', '', $value);
            $value = str_replace(',,', ',', $value);
            $value = str_ireplace(array('{if street}', '{street}', '{/if street}', '{if street1}', '{street1}', '{/if street1}', '{if street2}', '{street2}', '{/if street2}', '{if street3}', '{street3}', '{/if street3}', '{if street4}', '{street4}', '{/if street4}', '{if street5}', '{street5}', '{/if street5}', '{if street6}', '{street6}', '{/if street6}', '{if street7}', '{street7}', '{/if street7}', '{if street8}', '{street8}', '{/if street8}', '{if city}', '{city}', '{/if city}', '{if firstname}', '{firstname}', '{/if firstname}', '{if lastname}', '{lastname}', '{/if lastname}'), '', $value);
            if ($value != '') {
                $show_this_shipping_line[$ship_i] = $value;
                $ship_i++;
            }
        }
        return $show_this_shipping_line;
    }

    /**
     * @param $string
     * @param null $font
     * @param null $fontsize
     * @return string
     */
    public function parseString($string, $font = null, $fontsize = null) {
        return Mage::helper('pickpack/font')->parseString($string, $font, $fontsize);
    }

    /**
     * @param string $font
     * @param string $style
     * @param int $non_standard_characters
     * @return string
     */
    public function getFontName2($font = 'helvetica', $style = 'regular', $non_standard_characters = 0) {
        return Mage::helper('pickpack/font')->getFontName2($font, $style, $non_standard_characters);
    }

    /**
     * @param $order
     * @param $price
     * @return string
     */
    public function formatPriceTxt($order, $price) {
        return Mage::helper('pickpack/product')->formatPriceTxt($order, $price);
    }

    /**
     * @param $order
     * @param $price
     * @param null $currency
     * @param bool $isRtl
     * @return float
     */
    public function formatPrice($order, $price, $currency=null,$isRtl=true) {
        return Mage::helper('pickpack/product')->formatPrice($order, $price, $currency, $isRtl);
    }

    protected function getSkuBarcode2($product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute, $wonder, $storeId, $counter=1, $bundle_children=false, $product_id=null) {
        $order = $this->getOrder();

        if($counter == 2)
           $barcode_array = array();
        
		$new_product_barcode = '';
		$barcode_array['spacer'] = '';
        
		if($counter == 1) {
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_1', '', false, $wonder, $storeId);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_2', '', false, $wonder, $storeId);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_3', '', false, $wonder, $storeId);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_4', '', false, $wonder, $storeId);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_5', '', false, $wonder, $storeId);
            $product_sku_barcode_spacer = $this->_getConfig('product_sku_barcode_spacer', '', false, $wonder, $storeId);
        } else {
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_1', '', false, $wonder, $storeId);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_2', '', false, $wonder, $storeId);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_3', '', false, $wonder, $storeId);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_4', '', false, $wonder, $storeId);
            $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_2_attribute_5', '', false, $wonder, $storeId);
            $product_sku_barcode_spacer = $this->_getConfig('product_sku_barcode_2_spacer', '', false, $wonder, $storeId);
        }
        
		if ($product_sku_barcode_spacer != '')
            $barcode_array['spacer'] = $product_sku_barcode_spacer;

        $barcode_model = Mage::getModel('pickpack/sales_order_pdf_invoices_elements_barcode', array($this, $order));

		foreach ($product_sku_barcode_attributes as $product_sku_barcode_attribute) {
            if($bundle_children == true)
                $new_product_barcode = $barcode_model->getSkuBarcodeByAttribute2($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute,$bundle_children,$product_id);
            else {
				$new_product_barcode = $barcode_model->getSkuBarcodeByAttribute2($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute);
			}
        }

        $new_product_barcode = rtrim($new_product_barcode,$barcode_array['spacer']);

        return $new_product_barcode;
    }

    protected function getTrackingNumber($order) {
        $tracking_number = array();
        $tracking_number_string = '';
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($order)
            ->load();

        foreach ($shipmentCollection as $shipment){
            foreach($shipment->getAllTracks() as $tracknum)
            {
                $tracking_number[]=$tracknum->getNumber();
            }
        }
        $tracking_number_string = implode(',', $tracking_number);
        return $tracking_number_string;
    }

    protected function getEbaySaleNumber($order) {
        $result = '';
        if(Mage::helper('pickpack')->isInstalled('Ess_M2ePro')){
            $m2eproOrder = Mage::getModel('M2ePro/Order')->load($order->getId(), 'magento_order_id');
            if ($m2eproOrder->getId() && $m2eproOrder->getComponentMode() == 'ebay')
                $result .= "\n" . '(SM #' . $m2eproOrder->getChildObject()->getSellingManagerId() . ')';
        }
        $result = trim($result);
        return $result;
    }

    protected function getMarketPlaceId($order) {
        $ebay_order_id ='';
        if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
            if ((Mage::helper('core')->isModuleEnabled('Ess_M2ePro'))){
                $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
                $collection->addFieldToFilter('magento_order_id',$order->getData('entity_id'));
                $collection->setCurPage(1) // 2nd page
                    ->setPageSize(1);
                $collection_data = $collection->getData();

                if(is_array($collection_data) && isset($collection_data[0]['ebay_order_id']))
                    $ebay_order_id = $collection_data[0]['ebay_order_id'];
                else
                    $ebay_order_id ='';
            }

        }

        $amazon_order_id ='';
        if (Mage::helper('pickpack')->isInstalled('Ess_M2ePro')) {
            if ((Mage::helper('core')->isModuleEnabled('Ess_M2ePro'))){
                $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Order');
                $collection->addFieldToFilter('magento_order_id',$order->getData('entity_id'));
                $collection->setCurPage(1) // 2nd page
                    ->setPageSize(1);
                if(($collection->getData('amazon_order_id'))) {
                    $collection_data = $collection->getData();
                    if(is_array($collection_data))
                        $amazon_order_id = $collection_data[0]['amazon_order_id'];
                    else
                        $amazon_order_id ='';
                }
            }
        }
        if($ebay_order_id != '')
            $marketPlaceId = $ebay_order_id;
        elseif($amazon_order_id != '')
            $marketPlaceId = $amazon_order_id;
        else
            $marketPlaceId = $order->getRealOrderId();
        return $marketPlaceId;
    }

    protected function getPaymentOrder($order) {
        $allAvailablePaymentMethods = Mage::getModel('payment/config')->getAllMethods();
        $payment_order = $order->getPayment();
        foreach ($allAvailablePaymentMethods as $payment) {
            if ($payment->getId() == $payment_order->getMethod())
                return $payment_order;
        }
        return $payment_order = '';
    }

    public function widthForStringUsingFontSize($string, $font, $fontSize, $fontStyle = 'regular', $non_standard_characters = 0) {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        if(!$font || $font == 'helvetica')
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        if(!is_object($font))
            $font = Mage::helper('pickpack/font')->getFontName2($font, $fontStyle, $non_standard_characters);
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }

    /**
     * X nudge --- Y nudge
     * 1. Move top:
     * Increase Y 50px and Decrease X 50px
     * 2. Move bottom:
     * Decrease Y 50px and Increase X 50px
     * 3. Move left:
     * Decrease X 50px and Decrease Y 50px
     * 4. Move right:
     * Increase X 50px and Increase Y 50px
     * Move all to bototm 100px
     *
     * @param $case_rotate
     * @param $page
     * @param $page_top
     * @param $padded_right
     * @param $nudge_rotate_address_label
     */
    protected function rotateLabel($case_rotate, $page,$page_top, $padded_right) {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $wonder, $storeId));

        $x = -155;
        $y = -55;

        $x += $nudge_rotate_address_label[0];
        $x += $nudge_rotate_address_label[1];
        $y += $nudge_rotate_address_label[1];
        $y -= $nudge_rotate_address_label[0];
        $nudge_rotate_address_label[0] = $x;
        $nudge_rotate_address_label[1] = $y;

        switch ($case_rotate) {
            case 1:
                // //TODO Moo rotate 90
                $rotate = 3.14 / 2;
                break;
            case 2:
                //TODO Moo rotate 270
                $rotate = -3.14 / 2;
                break;
        }
        $page->rotate($page_top/2+$nudge_rotate_address_label[0],$padded_right/2 +$nudge_rotate_address_label[1], $rotate);
    }

    protected function reRotateLabel($case_rotate,&$page,$page_top,$padded_right) {
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','60,-80', false, $wonder, $storeId));

        $x = -155;
        $y = -55;

        $x += $nudge_rotate_address_label[0];
        $x += $nudge_rotate_address_label[1];
        $y += $nudge_rotate_address_label[1];
        $y -= $nudge_rotate_address_label[0];

        $nudge_rotate_address_label[0] = $x;
        $nudge_rotate_address_label[1] = $y;
        switch ($case_rotate) {
            case 1:
                $rotate = 3.14 / 2;
                break;
            case 2:
                $rotate = -3.14 / 2;
                break;
        }
        $page->rotate($page_top/2+$nudge_rotate_address_label[0],$padded_right/2 +$nudge_rotate_address_label[1], 0-$rotate);
    }

	// check if the address template part has been set as caps, indicating an intent to make that part caps
	private function checkCapsIntent($check_str,$value) {
		$check_caps_intent = array();
		$caps_intended = false;
		preg_match('~\{(.*)\}~i',$check_str,$check_caps_intent);
		
		if ( isset($check_caps_intent[1]) && (strtoupper($check_caps_intent[1]) == $check_caps_intent[1]) )
			$caps_intended = true;
		unset($check_caps_intent);
		return $this->getCapitalize($value,$caps_intended);
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
		if (function_exists('mb_strtoupper'))
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
		$generalConfig = $this->getGeneralConfig();
		if($generalConfig['remove_accents_from_chars_yn'] == 0)
			return $str;
		else
			return Mage::helper('pickpack/functions')->normalizeChars($str);
    }
	
    private function fixProblemCharacters($str) {
		// Depending on font chosen, change characters
		$generalConfig = $this->getGeneralConfig();
		if($generalConfig['font_family_body'] == 'noto')
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
     * This function use to split message/comment to array base on max width on real font size
     * This function can use on word wrap
     * $message_string:  can include '\n' but must be in string.
     * $max_width: max pt width of each line
     */
    public function splitWordsToArrayBasedOnMaxWidth($message_string, $max_width = 250, $font_size = 10, $font_temp = null) {
        if ($font_temp == null)
            $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        else
            $font_temp = $this->getFontName2($font_temp, 'regular', 0);

        $messages = explode ( "\n" , $message_string );
        $result = array();
        foreach($messages as $line){
            $array_worlds = explode ( " " , $line );
            $temp_line = "";
            foreach ($array_worlds as $world){
                $width = $this->parseString($temp_line.$world, $font_temp, $font_size);
                if ($width <= $max_width)
                    $temp_line .= $world." ";
                else {
                    $result[] = trim($temp_line);
                    $temp_line = $world.' ';
                }
            }
            if ($temp_line != '')
                $result[] = trim($temp_line);
        }
        return $result;
    }

    protected function _drawText($text, $x, $y, $charEncoding = 'UTF-8') {
		// @TODO next line, get the currently set font family (ie. only re-map characters for fonts that need it)
		$font = 'opensans';
		
		// Depending on font chosen, change characters
		$generalConfig = $this->getGeneralConfig();
		if(($generalConfig['non_standard_characters'] == 0) && ($generalConfig['callout_special_font'] == 0) || ($font == 'noto'))
			$text = str_replace($this->_charactedMap['from'], $this->_charactedMap['to'], $text);
        
		if( isset($text) && isset($x) && isset($y) )
			$this->getPage()->drawText($text, $x, $y, $charEncoding);
    }
}