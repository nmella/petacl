<?php
/**
 * 
 * Date: 20.11.15
 * Time: 15:22
 */
class Moogento_Pickpack_Helper_Config extends Mage_Core_Helper_Abstract
{
    protected $_general = array();
    protected $_config = array();
    protected $_page = array();
	
	const PAGE_SIZE = 'a4';
	const SHIPMENT_DETAILS_BOLD_LABEL_YN = 1;
	const NON_STANDARD_CHARACTERS = 0;
	const REMOVE_ACCENTS_FROM_CHARS_YN = 0;
	const DATE_FORMAT = 'M j, Y';
	
	const LINE_WIDTH_COMPANY = 0;
	const FONT_FAMILY_COMPANY = 'opensans';
	const FONT_COLOR_COMPANY = '#111111';
	const FONT_STYLE_COMPANY = 'regular';
	const FONT_SIZE_COMPANY = 8;
	const COMPANY_ADDRESS_START_POINT = '320,15';
		
	const FONT_FAMILY_BODY = 'opensans';
	const FONT_COLOR_BODY = '#111111';
	const FONT_STYLE_BODY = 'regular';
	const FONT_SIZE_BODY = 10;
	const FONT_SIZE_OPTIONS = 8;
	const PRODUCT_NAME_BOLD_YN = 0;
	
	const FONT_FAMILY_HEADER = 'opensans';
	const FONT_COLOR_HEADER = '#556b2f';
	const FONT_STYLE_HEADER = 'regular';
	const FONT_SIZE_HEADER = 16;
	
	const FONT_FAMILY_LABEL = 'opensans';
	const FONT_COLOR_LABEL = '#000000';
	const FONT_STYLE_LABEL = 'regular';
	const FONT_SIZE_LABEL = 15;
	const TRACKING_NUMBER_FONTSIZE = 13;
	
	const FONT_FAMILY_SUBTITLES = 'opensans';
	const FONT_COLOR_SUBTITLES = '#111111';
	const FONT_STYLE_SUBTITLES = 'semibold';
	const FONT_SIZE_SUBTITLES = 13;
	const FILLBAR_PADDING = '6,4';
	const FILL_BARS_SUBTITLES = 1;
	const BOTTOM_LINE_WIDTH = '4,1';
	const TITLEBAR_PADDING_TOP = 0;
	const TITLEBAR_PADDING_BOT = 0;
	//const BKG_COLOR_SUBTITLES = '#8AC371'; // = callout color
	
	const GIFT_OVERRIDE_YN = 1;
	const FONT_FAMILY_GIFT_OVERRIDE = 'droid';
	const FONT_SIZE_GIFT_OVERRIDE = 13;
	const FONT_STYLE_GIFT_OVERRIDE = 'regular';
		
	const FONT_FAMILY_MESSAGE = 'opensans';
	const FONT_COLOR_MESSAGE = '#111111';
	const FONT_STYLE_MESSAGE = 'italic';
	const FONT_SIZE_MESSAGE = 10;
	const BKG_COLOR_MESSAGE = '#5BA638';
	const FILL_BKG_MESSAGE_YN = 0;
	
	const FONT_FAMILY_GIFT_MESSAGE = 'opensans';
	const FONT_COLOR_GIFT_MESSAGE = '#111111';
	const FONT_STYLE_GIFT_MESSAGE = 'italic';
	const FONT_SIZE_GIFT_MESSAGE = 10;
	const BKG_COLOR_GIFT_MESSAGE = '#5BA638';
	const FILL_BKG_GIFT_MESSAGE_YN = 0;
	const GIFT_IMAGE_YN = 1;
	const GIFT_IMAGE = '';
	const GIFT_IMAGE_NUDGE = '0,0';
	
	const FONT_FAMILY_COMMENTS = 'opensans';
	const FONT_COLOR_COMMENTS = '#111111';
	const FONT_STYLE_COMMENTS = 'italic';
	const FONT_SIZE_COMMENTS = 10;
	const BKG_COLOR_COMMENTS = '#5BA638';
	const FILL_BKG_COMMENTS_YN = 0;
	
	const BARCODE_TYPE = 'code128';
	const FONT_FAMILY_BARCODE = 'Code128bWin.ttf';
	const FONT_SIZE_BARCODE_ORDER = 11;
	const FONT_SIZE_BARCODE_PRODUCT = 11;
	
	const CALLOUT_COLOR = '#8AC371';
	const CALLOUT_SPECIAL_FONT = 0;
	const FONT_FAMILY_CALLOUT_SPECIAL = 'noto';
	
	private function validate_html_color($color) {
	  /* Validates hex color, adding #-sign if not found
	  *   $color: the color hex value stirng to Validates
	  */ 
	  $color = trim($color);
	  if (preg_match('/^#[a-f0-9]{6}$/i', $color)) {
	    // Verified OK
	  } elseif (preg_match('/^[a-f0-9]{6}$/i', $color))
		  $color = '#' . $color;
	  else
		  $color = '#CCCCCC';
	  
	  return $color;
	}

    public function getGeneralConfigArray($storeId = null) {
        if ($storeId === null) $storeId = Mage::app()->getStore()->getStoreId();
        if(!isset($this->_general[$storeId])) {
            $this->_general[$storeId]['sort_packing_yn'] = $this->getConfig('sort_packing_yn', 0, false, 'general', $storeId);
            if ($this->_general[$storeId]['sort_packing_yn']){
                $this->_general[$storeId]['sort_packing'] = $this->getConfig('sort_packing', 'sku', false, 'general', $storeId);
                $this->_general[$storeId]['sort_packing_attribute'] = $this->getConfig('sort_packing_attribute', '', false, 'general', $storeId);
                $this->_general[$storeId]['sort_packing_order'] = $this->getConfig('sort_packing_order', 'ascending', false, 'general', $storeId);
                $this->_general[$storeId]['sort_packing_secondary'] = $this->getConfig('sort_packing_secondary', 'sku', false, 'general', $storeId);
                $this->_general[$storeId]['sort_packing_secondary_attribute'] = $this->getConfig('sort_packing_secondary_attribute', '', false, 'general', $storeId);
                $this->_general[$storeId]['sort_packing_secondary_order'] = $this->getConfig('sort_packing_secondary_order', 'ascending', false, 'general', $storeId);

                $this->_general[$storeId]['sort_child_bundle_yn'] = $this->getConfig('sort_child_bundle_yn', 0, false, 'general', $storeId);
                if ($this->_general[$storeId]['sort_child_bundle_yn']){
                    $this->_general[$storeId]['sort_child_bundle_by'] = $this->getConfig('sort_child_bundle_by', 'sku', false, 'general', $storeId);
                    $this->_general[$storeId]['sort_child_bundle_order'] = $this->getConfig('sort_child_bundle_order', 'ascending', false, 'general', $storeId);
                }
            } else
                $this->_general[$storeId]['sort_child_bundle_yn'] = 0;

	        $this->_general[$storeId]['custom_fonts_yn'] = $this->getConfig('custom_fonts_yn', 0, false, 'general', $storeId);
	        $this->_general[$storeId]['callout_color'] = $this->getConfig('callout_color', self::CALLOUT_COLOR, false, 'general', $storeId);
            
			// This section not dependent on choosing 'use custom fonts?'
	        $this->_general[$storeId]['date_format'] = $this->getConfig('date_format', self::DATE_FORMAT, false, 'general', $storeId);
            $this->_general[$storeId]['filter_virtual_products_yn'] = $this->getConfig('filter_virtual_products_yn', 0, false, 'general', $storeId);
            $this->_general[$storeId]['csv_strip_linebreaks_yn'] = $this->getConfig('csv_strip_linebreaks_yn', 1, false, 'general', $storeId);
            $this->_general[$storeId]['second_page_start'] = $this->getConfig('second_page_start', 'top', false, 'general', $storeId); // top or as-first
            $this->_general[$storeId]['page_size'] = $this->getConfig('page_size', self::PAGE_SIZE, false, 'general', $storeId);
			$this->_general[$storeId]['barcode_type'] = $this->getConfig('font_family_barcode', self::BARCODE_TYPE, false, 'general', $storeId);
		        switch ($this->_general[$storeId]['barcode_type']) {
		            case 'code128':
		                $this->_general[$storeId]['font_family_barcode'] = 'Code128bWin.ttf';
		                break;

		            case 'code39':
		                $this->_general[$storeId]['font_family_barcode'] = 'CODE39.ttf';
		                break;

		            case 'code39x':
		                $this->_general[$storeId]['font_family_barcode'] = 'CODE39X.ttf';
		                break;

		            default:
		                self::FONT_FAMILY_BARCODE;
		                break;
		        }
			$this->_general[$storeId]['remove_accents_from_chars_yn'] = $this->getConfig('remove_accents_from_chars_yn', self::REMOVE_ACCENTS_FROM_CHARS_YN, false, 'general', $storeId);
			
			if($this->_general[$storeId]['custom_fonts_yn'] == 1) {
				$this->_general[$storeId]['font_size_barcode_product'] = $this->getConfig('font_size_barcode_product', self::FONT_SIZE_BARCODE_PRODUCT, false, 'general', $storeId);
				$this->_general[$storeId]['font_size_barcode_order'] = $this->getConfig('font_size_barcode_order', self::FONT_SIZE_BARCODE_ORDER, false, 'general', $storeId);
				
				$this->_general[$storeId]['non_standard_characters'] = self::NON_STANDARD_CHARACTERS;//$this->getConfig('non_standard_characters', self::NON_STANDARD_CHARACTERS, false, 'general', $storeId);
	            $this->_general[$storeId]['callout_special_font'] = 0;

	            $this->_general[$storeId]['font_style_subtitles'] = $this->getConfig('font_style_subtitles', self::FONT_STYLE_SUBTITLES, false, 'general', $storeId);
	            $this->_general[$storeId]['font_size_subtitles'] = $this->getConfig('font_size_subtitles', self::FONT_SIZE_SUBTITLES, false, 'general', $storeId);
	            $this->_general[$storeId]['font_family_subtitles'] = $this->getConfig('font_family_subtitles', self::FONT_FAMILY_SUBTITLES, false, 'general', $storeId);
	            $this->_general[$storeId]['font_color_subtitles'] = $this->validate_html_color($this->getConfig('font_color_subtitles', self::FONT_COLOR_SUBTITLES, false, 'general', $storeId));
	            $this->_general[$storeId]['background_color_subtitles'] = $this->validate_html_color($this->getConfig('background_color_subtitles', self::CALLOUT_COLOR, false, 'general', $storeId));
	            $this->_general[$storeId]['fillbar_padding'] = trim($this->getConfig('fillbar_padding', self::FILLBAR_PADDING, false, 'general', $storeId));
		        if ($this->_general[$storeId]['fillbar_padding'] == 0)
		            $this->_general[$storeId]['fillbar_padding'] = '0,0';
	            $this->_general[$storeId]['fill_bars_subtitles'] = trim($this->getConfig('fill_bars_subtitles', self::FILL_BARS_SUBTITLES, false, 'general', $storeId));
	            $this->_general[$storeId]['bottom_line_width'] = trim($this->getConfig('bottom_line_width', self::BOTTOM_LINE_WIDTH, false, 'general', $storeId));
	            $this->_general[$storeId]['titlebar_padding_top'] = trim($this->getConfig('titlebar_padding_top', self::TITLEBAR_PADDING_TOP, false, 'general', $storeId));
	            $this->_general[$storeId]['titlebar_padding_bot'] = trim($this->getConfig('titlebar_padding_bot', self::TITLEBAR_PADDING_BOT, false, 'general', $storeId));
				
	            $this->_general[$storeId]['font_style_body'] = $this->getConfig('font_style_body', self::FONT_STYLE_BODY, false, 'general', $storeId);
	            $this->_general[$storeId]['font_family_body'] = $this->getConfig('font_family_body', self::FONT_FAMILY_BODY, false, 'general', $storeId);
	            $this->_general[$storeId]['font_color_body'] = $this->validate_html_color($this->getConfig('font_color_body', self::FONT_COLOR_BODY, false, 'general', $storeId));
	            $this->_general[$storeId]['font_size_body'] = $this->getConfig('font_size_body', self::FONT_SIZE_BODY, false, 'general', $storeId);
	            $this->_general[$storeId]['font_size_options'] = $this->getConfig('font_size_options', self::FONT_SIZE_OPTIONS, false, 'general', $storeId);				
				
	            $this->_general[$storeId]['font_style_header'] = $this->getConfig('font_style_header', self::FONT_STYLE_HEADER, false, 'general', $storeId);
	            $this->_general[$storeId]['font_family_header'] = $this->getConfig('font_family_header', self::FONT_FAMILY_HEADER, false, 'general', $storeId);
	            $this->_general[$storeId]['font_color_header'] = $this->validate_html_color($this->getConfig('font_color_header', self::FONT_COLOR_HEADER, false, 'general', $storeId));
	            $this->_general[$storeId]['font_size_header'] = $this->getConfig('font_size_header', self::FONT_SIZE_HEADER, false, 'general', $storeId);
				
	            $this->_general[$storeId]['font_family_company'] = $this->getConfig('font_family_company', self::FONT_FAMILY_COMPANY, false, 'general', $storeId);
	            $this->_general[$storeId]['font_style_company'] = $this->getConfig('font_style_company', self::FONT_STYLE_COMPANY, false, 'general', $storeId);
	            $this->_general[$storeId]['font_size_company'] = $this->getConfig('font_size_company', self::FONT_SIZE_COMPANY, false, 'general', $storeId);
	            $this->_general[$storeId]['font_color_company'] = $this->validate_html_color($this->getConfig('font_color_company', self::FONT_COLOR_COMPANY, false, 'general', $storeId));
	            $this->_general[$storeId]['line_width_company'] = $this->getConfig('line_width_company', self::LINE_WIDTH_COMPANY, false, 'general', $storeId);
	            $this->_general[$storeId]['company_address_start_point'] = self::COMPANY_ADDRESS_START_POINT;
	            $this->_general[$storeId]['shipment_details_bold_label_yn'] = $this->getConfig('shipment_details_bold_label_yn', self::SHIPMENT_DETAILS_BOLD_LABEL_YN, false, 'general', $storeId);

				$this->_general[$storeId]['fill_bkg_message_yn'] = trim($this->getConfig('fill_bkg_message_yn', self::FILL_BKG_MESSAGE_YN, false, 'general', $storeId));
				$this->_general[$storeId]['bkg_color_message'] = $this->validate_html_color($this->getConfig('bkg_color_message', self::BKG_COLOR_MESSAGE, false, 'general', $storeId));
	            $this->_general[$storeId]['font_size_message'] = $this->getConfig('font_size_message', self::FONT_SIZE_MESSAGE, false, 'general', $storeId);
	            $this->_general[$storeId]['font_style_message'] = $this->getConfig('font_style_message', self::FONT_STYLE_MESSAGE, false, 'general', $storeId);
	            $this->_general[$storeId]['font_family_message'] = $this->getConfig('font_family_message', self::FONT_FAMILY_MESSAGE, false, 'general', $storeId);
	            $this->_general[$storeId]['font_color_message'] = $this->validate_html_color($this->getConfig('font_color_message', self::FONT_COLOR_MESSAGE, false, 'general', $storeId));

	            $this->_general[$storeId]['gift_image_yn'] = $this->getConfig('gift_image_yn', self::GIFT_IMAGE_YN, false, 'general', $storeId);
	            $this->_general[$storeId]['gift_override_yn'] = $this->getConfig('gift_override_yn', self::GIFT_OVERRIDE_YN, false, 'general', $storeId);
	            $this->_general[$storeId]['font_size_gift_override'] = $this->getConfig('font_size_gift_override', self::FONT_SIZE_GIFT_OVERRIDE, false, 'general', $storeId);
	            $this->_general[$storeId]['font_style_gift_override'] = $this->getConfig('font_style_gift_override', self::FONT_STYLE_GIFT_OVERRIDE, false, 'general', $storeId);
	            $this->_general[$storeId]['font_family_gift_override'] = $this->getConfig('font_family_gift_override', self::FONT_FAMILY_GIFT_OVERRIDE, false, 'general', $storeId);
				
				$this->_general[$storeId]['fill_bkg_gift_message_yn'] = trim($this->getConfig('fill_bkg_gift_message_yn', self::FILL_BKG_GIFT_MESSAGE_YN, false, 'general', $storeId));
				$this->_general[$storeId]['bkg_color_gift_message'] = $this->validate_html_color($this->getConfig('bkg_color_gift_message', self::BKG_COLOR_GIFT_MESSAGE, false, 'general', $storeId));
	            $this->_general[$storeId]['font_size_gift_message'] = $this->getConfig('font_size_gift_message', self::FONT_SIZE_GIFT_MESSAGE, false, 'general', $storeId);
	            $this->_general[$storeId]['font_style_gift_message'] = $this->getConfig('font_style_gift_message', self::FONT_STYLE_GIFT_MESSAGE, false, 'general', $storeId);
	            $this->_general[$storeId]['font_family_gift_message'] = $this->getConfig('font_family_gift_message', self::FONT_FAMILY_GIFT_MESSAGE, false, 'general', $storeId);
	            $this->_general[$storeId]['font_color_gift_message'] = $this->validate_html_color($this->getConfig('font_color_gift_message', self::FONT_COLOR_GIFT_MESSAGE, false, 'general', $storeId));

				$this->_general[$storeId]['fill_bkg_comments_yn'] = trim($this->getConfig('fill_bkg_comments_yn', self::FILL_BKG_COMMENTS_YN, false, 'general', $storeId));
				$this->_general[$storeId]['bkg_color_comments'] = $this->validate_html_color($this->getConfig('bkg_color_comments', self::BKG_COLOR_COMMENTS, false, 'general', $storeId));
	            $this->_general[$storeId]['font_size_comments'] = $this->getConfig('font_size_comments', self::FONT_SIZE_COMMENTS, false, 'general', $storeId);
	            $this->_general[$storeId]['font_style_comments'] = $this->getConfig('font_style_comments', self::FONT_STYLE_COMMENTS, false, 'general', $storeId);
	            $this->_general[$storeId]['font_family_comments'] = $this->getConfig('font_family_comments', self::FONT_FAMILY_COMMENTS, false, 'general', $storeId);
	            $this->_general[$storeId]['font_color_comments'] = $this->validate_html_color($this->getConfig('font_color_comments', self::FONT_COLOR_COMMENTS, false, 'general', $storeId));

	            $this->_general[$storeId]['font_family_forced_picklist'] = self::FONT_FAMILY_SUBTITLES;
	            $this->_general[$storeId]['font_size_forced_picklist'] = self::FONT_SIZE_SUBTITLES;
	            $this->_general[$storeId]['non_standard_characters_forced_picklist'] = self::NON_STANDARD_CHARACTERS;
			} else {
				$this->_general[$storeId]['non_standard_characters'] = self::NON_STANDARD_CHARACTERS;
				
	            $this->_general[$storeId]['callout_special_font'] = $this->getConfig('callout_special_font', self::CALLOUT_SPECIAL_FONT, false, 'general', $storeId);
				if($this->_general[$storeId]['callout_special_font'] == 1) {
					// Use and attach noto font - it contains extra characters for accents etc
					// http://www.google.com/get/noto/
		            $this->_general[$storeId]['font_family_subtitles'] = self::FONT_FAMILY_CALLOUT_SPECIAL;
		            $this->_general[$storeId]['font_family_body'] = self::FONT_FAMILY_CALLOUT_SPECIAL;
		            $this->_general[$storeId]['font_family_header'] = self::FONT_FAMILY_CALLOUT_SPECIAL;
		            $this->_general[$storeId]['font_family_company'] = self::FONT_FAMILY_CALLOUT_SPECIAL;
		            $this->_general[$storeId]['font_family_message'] = self::FONT_FAMILY_CALLOUT_SPECIAL;
		            $this->_general[$storeId]['font_family_gift_message'] = self::FONT_FAMILY_CALLOUT_SPECIAL;
		            $this->_general[$storeId]['font_family_comments'] = self::FONT_FAMILY_CALLOUT_SPECIAL;
					$this->_general[$storeId]['font_family_forced_picklist'] = self::FONT_FAMILY_CALLOUT_SPECIAL;
				} else {
					// Use OpenSans (smaller attachment)
		            $this->_general[$storeId]['font_family_subtitles'] = self::FONT_FAMILY_SUBTITLES;
		            $this->_general[$storeId]['font_family_body'] = self::FONT_FAMILY_BODY;
		            $this->_general[$storeId]['font_family_header'] = self::FONT_FAMILY_HEADER;
		            $this->_general[$storeId]['font_family_company'] = self::FONT_FAMILY_COMPANY;
		            $this->_general[$storeId]['font_family_message'] = self::FONT_FAMILY_MESSAGE;
		            $this->_general[$storeId]['font_family_gift_message'] = self::FONT_FAMILY_GIFT_MESSAGE;
		            $this->_general[$storeId]['font_family_comments'] = self::FONT_FAMILY_COMMENTS;
		            $this->_general[$storeId]['font_family_forced_picklist'] = self::FONT_FAMILY_SUBTITLES;
				}
	            $this->_general[$storeId]['font_style_subtitles'] = self::FONT_STYLE_SUBTITLES;
	            $this->_general[$storeId]['font_size_subtitles'] = self::FONT_SIZE_SUBTITLES;
	            $this->_general[$storeId]['font_color_subtitles'] = $this->validate_html_color(self::FONT_COLOR_SUBTITLES);
	            $this->_general[$storeId]['background_color_subtitles'] = $this->validate_html_color($this->_general[$storeId]['callout_color']);
	            $this->_general[$storeId]['fillbar_padding'] = self::FILLBAR_PADDING;
				$this->_general[$storeId]['fill_bars_subtitles'] = self::FILL_BARS_SUBTITLES;
				$this->_general[$storeId]['bottom_line_width'] = self::BOTTOM_LINE_WIDTH;
				$this->_general[$storeId]['titlebar_padding_top'] = self::TITLEBAR_PADDING_TOP;
				$this->_general[$storeId]['titlebar_padding_bot'] = self::TITLEBAR_PADDING_BOT;
				
				// For 
	            $this->_general[$storeId]['font_size_forced_picklist'] = self::FONT_SIZE_SUBTITLES;
	            $this->_general[$storeId]['non_standard_characters_forced_picklist'] = self::NON_STANDARD_CHARACTERS;
				
				$this->_general[$storeId]['font_size_barcode_product'] = self::FONT_SIZE_BARCODE_PRODUCT;
				$this->_general[$storeId]['font_size_barcode_order'] = self::FONT_SIZE_BARCODE_ORDER;
				
	            $this->_general[$storeId]['font_style_body'] = self::FONT_STYLE_BODY;
	            $this->_general[$storeId]['font_color_body'] = $this->validate_html_color(self::FONT_COLOR_BODY);
	            $this->_general[$storeId]['font_size_body'] = self::FONT_SIZE_BODY;
	            $this->_general[$storeId]['font_size_options'] = self::FONT_SIZE_OPTIONS;
				
	            $this->_general[$storeId]['font_style_header'] = self::FONT_STYLE_HEADER;
	            $this->_general[$storeId]['font_color_header'] = $this->validate_html_color(self::FONT_COLOR_HEADER);
	            $this->_general[$storeId]['font_size_header'] = self::FONT_SIZE_HEADER;
				
	            $this->_general[$storeId]['font_style_company'] = self::FONT_STYLE_COMPANY;
	            $this->_general[$storeId]['font_size_company'] = self::FONT_SIZE_COMPANY;
	            $this->_general[$storeId]['font_color_company'] = $this->validate_html_color(self::FONT_COLOR_COMPANY);
	            $this->_general[$storeId]['line_width_company'] = self::LINE_WIDTH_COMPANY;
	            $this->_general[$storeId]['company_address_start_point'] = self::COMPANY_ADDRESS_START_POINT;
	            $this->_general[$storeId]['shipment_details_bold_label_yn'] = self::SHIPMENT_DETAILS_BOLD_LABEL_YN;

				$this->_general[$storeId]['fill_bkg_message_yn'] = self::FILL_BKG_MESSAGE_YN;
				$this->_general[$storeId]['bkg_color_message'] = $this->validate_html_color($this->_general[$storeId]['callout_color']);
	            $this->_general[$storeId]['font_size_message'] = self::FONT_SIZE_MESSAGE;
	            $this->_general[$storeId]['font_style_message'] = self::FONT_STYLE_MESSAGE;
	            $this->_general[$storeId]['font_color_message'] = $this->validate_html_color(self::FONT_COLOR_MESSAGE);
				
	            $this->_general[$storeId]['gift_image_yn'] = self::GIFT_IMAGE_YN;
	            $this->_general[$storeId]['gift_override_yn'] = self::GIFT_OVERRIDE_YN;
	            $this->_general[$storeId]['font_size_gift_override'] = self::FONT_SIZE_GIFT_OVERRIDE;
	            $this->_general[$storeId]['font_style_gift_override'] = self::FONT_STYLE_GIFT_OVERRIDE;
	            $this->_general[$storeId]['font_family_gift_override'] = self::FONT_FAMILY_GIFT_OVERRIDE;

				$this->_general[$storeId]['fill_bkg_gift_message_yn'] = self::FILL_BKG_GIFT_MESSAGE_YN;
	            $this->_general[$storeId]['bkg_color_gift_message'] = $this->validate_html_color($this->_general[$storeId]['callout_color']);
	            $this->_general[$storeId]['font_size_gift_message'] = self::FONT_SIZE_GIFT_MESSAGE;
	            $this->_general[$storeId]['font_style_gift_message'] = self::FONT_STYLE_GIFT_MESSAGE;
	            $this->_general[$storeId]['font_color_gift_message'] = $this->validate_html_color(self::FONT_COLOR_GIFT_MESSAGE);
				
				$this->_general[$storeId]['fill_bkg_comments_yn'] = self::FILL_BKG_COMMENTS_YN;
				$this->_general[$storeId]['bkg_color_comments'] = $this->validate_html_color($this->_general[$storeId]['callout_color']);
	            $this->_general[$storeId]['font_size_comments'] = self::FONT_SIZE_COMMENTS;
	            $this->_general[$storeId]['font_style_comments'] = self::FONT_STYLE_COMMENTS;

	            $this->_general[$storeId]['font_color_comments'] = $this->validate_html_color($this->getConfig('font_color_comments', self::FONT_COLOR_COMMENTS, false, 'general', $storeId));
			}
			
            $this->_general[$storeId]['font_style_label'] = $this->getConfig('font_style_label', self::FONT_STYLE_LABEL, false, 'label_zebra', $storeId);
            $this->_general[$storeId]['font_family_label'] = $this->getConfig('font_family_label', self::FONT_FAMILY_LABEL, false, 'label_zebra', $storeId);
            $this->_general[$storeId]['font_color_label'] = $this->validate_html_color($this->getConfig('font_color_label', self::FONT_COLOR_LABEL, false, 'label_zebra', $storeId));
            $this->_general[$storeId]['font_size_label'] = $this->getConfig('font_size_label', self::FONT_SIZE_LABEL, false, 'label_zebra', $storeId);
            $this->_general[$storeId]['tracking_number_fontsize'] = $this->getConfig('tracking_number_fontsize', self::TRACKING_NUMBER_FONTSIZE, false, 'label_zebra', $storeId);
			
//			if ( ($this->_general[$storeId]['non_standard_characters'] !== 0) && ($this->_general[$storeId]['non_standard_characters'] !== 1) ) {
//	            $this->_general[$storeId]['font_family_body'] = $this->_general[$storeId]['non_standard_characters'];
//	            $this->_general[$storeId]['font_family_gift_message'] = $this->_general[$storeId]['non_standard_characters'];
//	            $this->_general[$storeId]['font_family_message'] = $this->_general[$storeId]['non_standard_characters'];
//	            $this->_general[$storeId]['font_family_comments'] = $this->_general[$storeId]['non_standard_characters'];
//	            $this->_general[$storeId]['font_family_company'] = $this->_general[$storeId]['non_standard_characters'];
//	            $this->_general[$storeId]['font_family_subtitles'] = $this->_general[$storeId]['non_standard_characters'];
//			}
	         
            if ($this->_general[$storeId]['font_family_company'] == 'custom') {
                $fontFilename = $this->getConfig('font_custom_company', '', false, 'general', $storeId);
                $subFolder = 'custom_font';
                if ($fontFilename) {
                    $font_path = Mage::getStoreConfig('system/filesystem/media', $storeId) . '/moogento/pickpack/' . $subFolder . '/' . $fontFilename;
                    if (is_file($font_path))
                        $this->_general[$storeId]['font_style_company'] = $font_path;
                    else
                        $this->_general[$storeId]['font_family_company'] = self::FONT_FAMILY_COMPANY;
                }
            }
        }

        return $this->_general[$storeId];
    }

    /**
     **************************** PAGE SIZE SETTING ***************************
     * Letter        612x792 587x770 (-25 -22)
     * A4             595x842 570x820 (-25 -22)
     * A5             420x595 395x573
     * A5(L)        595x420 573x395
     *
     * @param string $section
     * @param null $storeId
     * @return mixed
     */
    public function getPackingsheetConfigArray($wonder = 'wonder', $storeId = null) {
        if ($storeId === null) $storeId = Mage::app()->getStore()->getStoreId();
        if(!isset($this->_packingsheet[$storeId][$wonder])) {

            $pageConfig = $this->getPageConfigArray($wonder, $storeId);

            $this->_packingsheet[$storeId][$wonder]['pickpack_return_address_yn'] = $this->getConfig('pickpack_return_address_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['page_size'] = $this->getConfig('page_size', self::PAGE_SIZE, false, 'general', $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_name_bold_yn'] = $this->getConfig('product_name_bold_yn', self::PRODUCT_NAME_BOLD_YN, false, $wonder, $storeId);

            /*************************** TOP OF PAGE CONFIG SECTION *******************************/
            $this->_packingsheet[$storeId][$wonder]['letterhead'] = $this->getConfig('letterhead', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_packlogo'] = $this->getConfig('pickpack_packlogo', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_logo_position'] = $this->getConfig('pickpack_logo_position', 'left', false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['page_logo_nudge'] = explode(',', $this->getConfig('page_logo_nudge', '0,0', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['pickpack_company_address_yn'] = $this->getConfig('pickpack_company_address_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_title_pattern'] = $this->getConfig('pickpack_title_pattern', '', false, $wonder, $storeId, false);
            $this->_packingsheet[$storeId][$wonder]['billing_details_yn'] = $this->getConfig('billing_details_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['page_pad'] = explode(',', $this->getConfig('page_pad', '0,0,0', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['pickpack_packbarcode'] = $this->getConfig('pickpack_packbarcode', 0, false, $wonder, $storeId);

            /*************************** END TOP OF PAGE CONFIG SECTION *******************************/

            /*************************** MIDDLE OF PAGE CONFIG SECTION *******************************/
            $this->_packingsheet[$storeId][$wonder]['filter_items_by_status'] = $this->getConfig('filter_items_by_status', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['page_1_products_y_cutoff'] = trim($this->getConfig('page_1_products_y_cutoff', '0', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['page_background_image_yn'] = $this->getConfig('page_background_image_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['page_background_resize'] = $this->getConfig('page_background_resize', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['page_background_position'] = $this->getConfig('page_background_position', 'center_prolist', false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['page_background_nudge'] = explode(",", $this->getConfig('page_background_nudge', '0,0', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['packed_by_yn'] = $this->getConfig('packed_by_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['packed_by_text'] = trim($this->getConfig('packed_by_text', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['packed_by_nudge'] = explode(",", $this->getConfig('packed_by_nudge', '0,0', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['combine_custom_attribute_under_product'] = $this->getConfig('combine_custom_attribute_under_product', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_attribute_1'] = trim($this->getConfig('product_attribute_1', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_attribute_2'] = trim($this->getConfig('product_attribute_2', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_attribute_3'] = trim($this->getConfig('product_attribute_3', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_attribute_4'] = trim($this->getConfig('product_attribute_4', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_attribute_5'] = trim($this->getConfig('product_attribute_5', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['show_allowance_yn'] = $this->getConfig('show_allowance_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_allowance_title'] = trim($this->getConfig('show_allowance_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['show_allowance_xpos'] = explode(",", $this->getConfig('show_allowance_xpos', '0,0', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['show_allowance_multiple'] = trim($this->getConfig('show_allowance_multiple', '1', false, $wonder, $storeId));
            //start custom attribute 1
            $this->_packingsheet[$storeId][$wonder]['shelving_real_yn'] = $this->getConfig('shelving_real_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_real'] = trim($this->getConfig('shelving_real', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shelving_real_title'] = trim($this->getConfig('shelving_real_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['pricesN_shelfX'] = $this->getConfig('pricesN_shelfX', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_real_trim_content_yn'] = $this->getConfig('shelving_real_trim_content_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_real_star_specific_value_yn'] = $this->getConfig('shelving_real_star_specific_value_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_real_star_specific_value_filter'] = explode(",", $this->getConfig('shelving_real_star_specific_value_filter', '', false, $wonder, $storeId));
            //start custom attribute 2
            $this->_packingsheet[$storeId][$wonder]['shelving_yn'] = $this->getConfig('shelving_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pricesN_shelf2X'] = $this->getConfig('pricesN_shelf2X', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving'] = trim($this->getConfig('shelving', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shelving_title'] = trim($this->getConfig('shelving_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shelving_trim_content_yn'] = $this->getConfig('shelving_trim_content_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_2_star_specific_value_yn'] = $this->getConfig('shelving_2_star_specific_value_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_2_star_specific_value_filter'] = explode(",", $this->getConfig('shelving_2_star_specific_value_filter', '', false, $wonder, $storeId));
            //start custom attribute 3
            $this->_packingsheet[$storeId][$wonder]['shelving_2_yn'] = $this->getConfig('shelving_2_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pricesN_shelf3X'] = $this->getConfig('pricesN_shelf3X', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_2'] = trim($this->getConfig('shelving_2', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shelving_2_title'] = trim($this->getConfig('shelving_2_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shelving_2_trim_content_yn'] = $this->getConfig('shelving_2_trim_content_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_3_star_specific_value_yn'] = $this->getConfig('shelving_3_star_specific_value_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_3_star_specific_value_filter'] = explode(",", $this->getConfig('shelving_3_star_specific_value_filter', '', false, $wonder, $storeId));
            //start custom attribute 4
            $this->_packingsheet[$storeId][$wonder]['shelving_3_yn'] = $this->getConfig('shelving_3_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_3_Xpos'] = $this->getConfig('shelving_3_Xpos', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_3'] = trim($this->getConfig('shelving_3', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shelving_3_title'] = trim($this->getConfig('shelving_3_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shelving_3_trim_content_yn'] = $this->getConfig('shelving_3_trim_content_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_4_star_specific_value_yn'] = $this->getConfig('shelving_4_star_specific_value_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shelving_4_star_specific_value_filter'] = explode(",", $this->getConfig('shelving_4_star_specific_value_filter', '', false, $wonder, $storeId));
            //start combine custom attribute
            $this->_packingsheet[$storeId][$wonder]['combine_custom_attribute_yn'] = $this->getConfig('combine_custom_attribute_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['combine_custom_attribute_title'] = trim($this->getConfig('combine_custom_attribute_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['combine_custom_attribute_Xpos'] = $this->getConfig('combine_custom_attribute_Xpos', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['combine_custom_attribute_title_each'] = $this->getConfig('combine_custom_attribute_title_each', 10, false, $wonder, $storeId);
			
            // Print $ or USD
            $this->_packingsheet[$storeId][$wonder]['currency_codes_or_symbols'] = $this->getConfig('currency_codes_or_symbols', 'symbols', false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['currency_symbol_position'] = $this->getConfig('currency_symbol_position', 'auto', false, $wonder, $storeId);
			//"Round custom attribute numbers?"
            $this->_packingsheet[$storeId][$wonder]['custom_round_yn'] = $this->getConfig('custom_round_yn', 0, false, $wonder, $storeId);
            //start tickbox
            $this->_packingsheet[$storeId][$wonder]['tickbox_yn'] = $this->getConfig('tickbox_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tickboxX'] = $this->getConfig('tickboxX', 27, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tickbox_width'] = $this->getConfig('tickbox_width', 7, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tickbox_signature_line'] = $this->getConfig('tickbox_signature_line', 0, false, $wonder, $storeId);
            //start tickbox 2
            if ($this->_packingsheet[$storeId][$wonder]['tickbox_yn'])
                $this->_packingsheet[$storeId][$wonder]['tickbox_2_yn'] = $this->getConfig('tickbox_2_yn', 0, false, $wonder, $storeId);
            else $this->_packingsheet[$storeId][$wonder]['tickbox_2_yn'] = 0;
            $this->_packingsheet[$storeId][$wonder]['tickbox2X'] = $this->getConfig('tickbox2X', 54, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tickbox2_width'] = $this->getConfig('tickbox2_width', 7, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tickbox_2_signature_line'] = $this->getConfig('tickbox_2_signature_line', 0, false, $wonder, $storeId);
            //start qty
            $this->_packingsheet[$storeId][$wonder]['qty_title'] = trim($this->getConfig('qty_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['qty_x_pos'] = $this->getConfig('pricesN_qty_priceX', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_qty_upsize_yn'] = $this->getConfig('product_qty_upsize_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_qty_options'] = $this->getConfig('show_qty_options', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_subtotal_options'] = $this->getConfig('show_subtotal_options', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_zero_qty_options'] = $this->getConfig('show_zero_qty_options', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['center_value_qty'] = $this->getConfig('center_value_qty', 1, false, $wonder, $storeId);
            //start stock qty
            $this->_packingsheet[$storeId][$wonder]['product_stock_qty_yn'] = $this->getConfig('product_stock_qty_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_stock_qty_title'] = trim($this->getConfig('product_stock_qty_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_stock_qty_x_pos'] = $this->getConfig('pricesN_stockqtyX', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['location_specific_stock_yn'] = $this->getConfig('location_specific_stock_yn', 0, false, $wonder, $storeId);
            //start qty backordered
            $this->_packingsheet[$storeId][$wonder]['product_qty_backordered_yn'] = $this->getConfig('product_qty_backordered_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_qty_backordered_title'] = trim($this->getConfig('product_qty_backordered_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_qty_backordered_x_pos'] = $this->getConfig('prices_qtybackorderedX', 10, false, $wonder, $storeId);
            //start product ware house
            $this->_packingsheet[$storeId][$wonder]['product_warehouse_yn'] = $this->getConfig('product_warehouse_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_warehouse_title'] = trim($this->getConfig('product_warehouse_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['prices_warehouseX'] = $this->getConfig('prices_warehouseX', 10, false, $wonder, $storeId);
            //start image
            $this->_packingsheet[$storeId][$wonder]['product_images_yn'] = $this->getConfig('product_images_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_images_title'] = trim($this->getConfig('images_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_images_x_pos'] = $this->getConfig('pricesN_images_priceX', 50, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_images_source'] = $this->getConfig('product_images_source', 'image', false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_images_maxdimensions'] = explode(",", $this->getConfig('product_images_maxdimensions', '25,25', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_images_border_color'] = trim($this->getConfig('product_images_border_color', '#FFFFFF', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_images_y_nudge'] = trim($this->getConfig('product_images_y_nudge', '10', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_images_line_nudge'] = trim($this->getConfig('product_images_line_nudge', '0', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_parent_image_yn'] = $this->getConfig('parent_image_yn', 1, false, $wonder, $storeId);
            //start sku
            $this->_packingsheet[$storeId][$wonder]['product_sku_yn'] = $this->getConfig('product_sku_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_sku_title'] = trim($this->getConfig('sku_title', 'codes', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_x_pos'] = $this->getConfig('pricesN_skuX', 80, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_sku_trim_yn'] = $this->getConfig('product_sku_trim_yn', 0, false, $wonder, $storeId);
            //start product sku barcode
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_yn'] = $this->getConfig('product_sku_barcode_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_title'] = trim($this->getConfig('sku_barcode_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_x_pos'] = $this->getConfig('pricesN_barcodeX', 30, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_attribute_1'] = trim($this->getConfig('product_sku_barcode_attribute_1', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_attribute_2'] = trim($this->getConfig('product_sku_barcode_attribute_2', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_attribute_3'] = trim($this->getConfig('product_sku_barcode_attribute_3', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_attribute_4'] = trim($this->getConfig('product_sku_barcode_attribute_4', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_attribute_5'] = trim($this->getConfig('product_sku_barcode_attribute_5', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_spacer'] = trim($this->getConfig('product_sku_barcode_spacer', '', false, $wonder, $storeId));
            //start product sku barcode 2
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_yn'] = $this->getConfig('product_sku_barcode_2_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_title'] = trim($this->getConfig('sku_barcode_2_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_x_pos'] = $this->getConfig('pricesN_barcodeX_2', 30, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_attribute_1'] = trim($this->getConfig('product_sku_barcode_2_attribute_1', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_attribute_2'] = trim($this->getConfig('product_sku_barcode_2_attribute_2', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_attribute_3'] = trim($this->getConfig('product_sku_barcode_2_attribute_3', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_attribute_4'] = trim($this->getConfig('product_sku_barcode_2_attribute_4', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_attribute_5'] = trim($this->getConfig('product_sku_barcode_2_attribute_5', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_sku_barcode_2_spacer'] = trim($this->getConfig('product_sku_barcode_2_spacer', '', false, $wonder, $storeId));
            //start product name
            $this->_packingsheet[$storeId][$wonder]['show_product_name'] = $this->getConfig('show_product_name', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_name_title'] = trim($this->getConfig('items_title', 'items', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_name_x_pos'] = $this->getConfig('pricesN_productX', 200, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_configurable_name'] = $this->getConfig('pack_configname', 'simple', false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_name_store_view'] = $this->getConfig('name_store_view', 'storeview', false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_name_specific_store_id'] = $this->getConfig('specific_store', '', false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_name_trim_yn'] = $this->getConfig('trim_product_name_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_name_bold_yn'] = $this->getConfig('product_name_bold_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['new_pdf_per_name_yn'] = $this->getConfig('new_pdf_per_name_yn', 0, false, $wonder, $storeId);
            //start gift wrap
            $this->_packingsheet[$storeId][$wonder]['show_gift_wrap'] = $this->getConfig('show_gift_wrap', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_gift_wrap_title'] = trim($this->getConfig('show_gift_wrap_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['show_gift_wrap_xpos'] = $this->getConfig('show_gift_wrap_xpos', 560, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_gift_wrap_icon'] = $this->getConfig('show_gift_wrap_icon', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_gift_wrap_label'] = $this->getConfig('show_gift_wrap_label', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_gift_wrap_top_right'] = $this->getConfig('show_gift_wrap_top_right', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_gift_wrap_top_right_xpos'] = $this->getConfig('show_gift_wrap_top_right_xpos', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_gift_wrap_top_right_ypos'] = $this->getConfig('show_gift_wrap_top_right_ypos', 0, false, $wonder, $storeId);
            //---------
            $this->_packingsheet[$storeId][$wonder]['product_options_yn'] = $this->getConfig('product_options_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_bundle_parent'] = $this->getConfig('show_bundle_parent', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['bundle_children_yn'] = $this->getConfig('bundle_children_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['bundle_children_split'] = $this->getConfig('split_bundles', 0, false, $wonder, $storeId);
            if ($this->_packingsheet[$storeId][$wonder]['bundle_children_yn'] = 1)
                $this->_packingsheet[$storeId][$wonder]['shift_bundle_children_xpos'] = $this->getConfig('shift_bundle_children_xpos', 0, false, $wonder, $storeId);
            else $this->_packingsheet[$storeId][$wonder]['shift_bundle_children_xpos'] = 0;
            //start numberlist
            $this->_packingsheet[$storeId][$wonder]['numbered_product_list_yn'] = $this->getConfig('numbered_product_list_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['numbered_product_list_X'] = $this->getConfig('numbered_product_list_X', 17, false, $wonder, $storeId);
            if ($this->_packingsheet[$storeId][$wonder]['numbered_product_list_yn'] || $this->_packingsheet[$storeId][$wonder]['bundle_children_yn'])
                $this->_packingsheet[$storeId][$wonder]['numbered_product_list_bundle_children_yn'] = $this->getConfig('numbered_product_list_bundle_children_yn', 0, false, $wonder, $storeId);
            else $this->_packingsheet[$storeId][$wonder]['numbered_product_list_bundle_children_yn'] = 0;
            $this->_packingsheet[$storeId][$wonder]['numbered_product_list_bundle_children_X'] = $this->getConfig('numbered_product_list_bundle_children_X', 21, false, $wonder, $storeId);
            //---------
            $this->_packingsheet[$storeId][$wonder]['doubleline_yn'] = $this->getConfig('doubleline_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_individual_product_gift_message'] = $this->getConfig('show_individual_product_gift_message', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['individual_product_gift_message_xpos'] = $this->getConfig('individual_product_gift_message_xpos', 30, false, $wonder, $storeId);
            /*************************** END MIDDLE OF PAGE CONFIG SECTION *******************************/

            /*************************** PRICE CONFIG SECTION *******************************/
            $this->_packingsheet[$storeId][$wonder]['prices_yn'] = $this->getConfig('prices_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_prices_yn'] = $this->getConfig('product_line_prices_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_prices_title'] = trim($this->getConfig('product_line_prices_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_line_prices_title_xpos'] = $this->getConfig('product_line_prices_title_xpos', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_prices_with_tax_yn'] = $this->getConfig('product_line_prices_with_tax_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_prices_with_discount_yn'] = $this->getConfig('product_line_prices_with_discount_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_discount_yn'] = $this->getConfig('product_line_discount_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_discount_title'] = trim($this->getConfig('product_line_discount_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_line_discount_title_xpos'] = $this->getConfig('product_line_discount_title_xpos', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_shipping_yn'] = $this->getConfig('product_line_shipping_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_shipping_title'] = trim($this->getConfig('product_line_shipping_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_line_shipping_title_xpos'] = $this->getConfig('product_line_shipping_title_xpos', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_shipping_with_tax_yn'] = $this->getConfig('product_line_prices_with_tax_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_tax_yn'] = $this->getConfig('product_line_tax_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_tax_title'] = trim($this->getConfig('product_line_tax_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_line_tax_title_xpos'] = $this->getConfig('product_line_tax_title_xpos', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_total_yn'] = $this->getConfig('product_line_total_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_total_title'] = trim($this->getConfig('product_line_total_title', '', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['product_line_total_title_xpos'] = $this->getConfig('product_line_total_title_xpos', 10, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_total_with_tax_yn'] = $this->getConfig('product_line_total_with_tax_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['product_line_total_with_discount_yn'] = $this->getConfig('product_line_total_with_discount_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_subtotal'] = $this->getConfig('total_subtotal', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_subtotal_with_tax_yn'] = $this->getConfig('total_subtotal_with_tax_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_subtotal_with_discount_yn'] = $this->getConfig('total_subtotal_with_discount_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_shipping_yn'] = $this->getConfig('total_shipping_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_shipping_with_tax_yn'] = $this->getConfig('total_shipping_with_tax_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_discount_yn'] = $this->getConfig('total_discount_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_tax_yn'] = $this->getConfig('total_tax_yn', 1, false, $wonder, $storeId);
            if ($this->_packingsheet[$storeId][$wonder]['total_shipping_with_tax_yn'])
                $this->_packingsheet[$storeId][$wonder]['total_tax_breakdown_yn'] = 0;
            else
				$this->_packingsheet[$storeId][$wonder]['total_tax_breakdown_yn'] = $this->getConfig('total_tax_breakdown_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_tax_incl_yn'] = $this->getConfig('total_tax_incl_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_default_grandtotal_yn'] = $this->getConfig('total_default_grandtotal_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_paid_yn'] = $this->getConfig('total_paid_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['total_due_yn'] = $this->getConfig('total_due_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['hide_zero_tax_value'] = $this->getConfig('hide_zero_tax_value', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['hide_zero_shipping_value'] = $this->getConfig('hide_zero_shipping_value', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['hide_zero_discount_value'] = $this->getConfig('hide_zero_discount_value', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['subtotal_order'] = explode(",", $this->getConfig('subtotal_order', '1,2,3,4', false, $wonder, $storeId));
            


//            $this->_packingsheet[$storeId][$wonder]['prices_yn'] = $this->getConfig('prices_yn', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['price_title'] = trim($this->getConfig('price_title', '', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['pricesY_item_priceX'] = $this->getConfig('pricesY_item_priceX', 10, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['total_title'] = trim($this->getConfig('total_title', '', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['pricesY_priceX'] = $this->getConfig('pricesY_priceX', 10, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['rowtotal_include_tax_yn_yesboth'] = $this->getConfig('rowtotal_include_tax_yn_yesboth', 1, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['rowtotal_include_tax_yn_yescol'] = $this->getConfig('rowtotal_include_tax_yn_yescol', 1, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['prices_hideforgift_yn'] = $this->getConfig('prices_hideforgift_yn', 1, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['use_magento_subtotal'] = $this->getConfig('use_magento_subtotal', 1, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['subtotal_remove_tax_yn'] = $this->getConfig('subtotal_remove_tax_yn', 1, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_displayed_in_shipping_yn_noboth'] = $this->getConfig('tax_displayed_in_shipping_yn_no', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_displayed_in_shipping_yn_noboth'] = $this->getConfig('tax_displayed_in_shipping_yn_noboth', 1, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['remove_shipping_tax_from_tax_subtotal_yn_noboth'] = $this->getConfig('remove_shipping_tax_from_tax_subtotal_yn_noboth', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['remove_shipping_tax_from_subtotal_yn_noboth'] = $this->getConfig('remove_shipping_tax_from_subtotal_yn_noboth', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_displayed_in_shipping_yn_subtotal'] = $this->getConfig('tax_displayed_in_shipping_yn_subtotal', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_displayed_in_shipping_yn'] = $this->getConfig('tax_displayed_in_shipping_yn', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_yn'] = $this->getConfig('tax_yn', 'yesboth', false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_bands_yn_subtotal'] = $this->getConfig('tax_bands_yn_subtotal', 1, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_bracket_tax'] = $this->getConfig('show_bracket_tax', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_title'] = trim($this->getConfig('tax_title', '', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['pricesT_item_taxX'] = $this->getConfig('pricesT_item_taxX', 475, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_method_yescol'] = $this->getConfig('tax_method_yescol', 'b', false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_title_both'] = trim($this->getConfig('tax_title_both', '', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['pricesT_item_taxX_both'] = $this->getConfig('pricesT_item_taxX_both', 475, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['tax_method_yesboth'] = $this->getConfig('tax_method_yesboth', 'b', false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['multi_prices_yn'] = $this->getConfig('multi_prices_yn', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['multiplier_attribute'] = trim($this->getConfig('multiplier_attribute', '', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['discount_line_or_subtotal'] = $this->getConfig('discount_line_or_subtotal', 'line', false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['fix_subtotal_page'] = $this->getConfig('fix_subtotal_page', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['total_taxed_product_value'] = $this->getConfig('total_taxed_product_value', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['total_taxed_product_value_title'] = trim($this->getConfig('total_taxed_product_value_title', 'VAT Rateable', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['total_untaxed_product_value'] = $this->getConfig('total_untaxed_product_value', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['total_untaxed_product_value_title'] = trim($this->getConfig('total_untaxed_product_value_title', 'Zero-rated', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['total_paid_yn_subtotal'] = $this->getConfig('total_paid_yn_subtotal', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['total_due_yn_subtotal'] = $this->getConfig('total_due_yn_subtotal', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['subtotal_order_yessubtotal'] = explode(',', $this->getConfig('subtotal_order_yessubtotal', '10,20,30,40', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['subtotal_order_yesboth'] = explode(',', $this->getConfig('subtotal_order_yesboth', '10,20,30,40', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['subtotal_order_noboth'] = explode(',', $this->getConfig('subtotal_order_noboth', '10,20,30,40', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['subtotal_order_yescol'] = explode(',', $this->getConfig('subtotal_order_yescol', '10,20,40', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['use_default_magento_grand_total'] = $this->getConfig('use_default_magento_grand_total', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_zero_shipping_fee'] = $this->getConfig('show_zero_shipping_fee', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_zero_tax_value'] = $this->getConfig('show_zero_tax_value', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_zero_discount_value'] = $this->getConfig('show_zero_discount_value', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_rewardpoint_spent_yn'] = $this->getConfig('show_rewardpoint_spent_yn', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_rewardpoint_earn_yn'] = $this->getConfig('show_rewardpoint_earn_yn', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_rewardpoint_discount_yn'] = $this->getConfig('show_rewardpoint_discount_yn', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_base_currency_value'] = $this->getConfig('show_base_currency_value', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['show_currency_exchange_rate'] = $this->getConfig('show_currency_exchange_rate', 0, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['subtotal_align'] = $this->getConfig('subtotal_align', 1, false, $wonder, $storeId);
//            $this->_packingsheet[$storeId][$wonder]['subtotal_align_xpos'] = explode(',', $this->getConfig('subtotal_align_xpos', '410,460', false, $wonder, $storeId));
//            $this->_packingsheet[$storeId][$wonder]['subtotal_price_xpos_options'] = $this->getConfig('subtotal_price_xpos_options', 1, false, $wonder, $storeId);
            /*************************** END PRICE CONFIG SECTION *******************************/

            /*************************** BOTTOM OF PAGE CONFIG SECTION *******************************/
            $this->_packingsheet[$storeId][$wonder]['rotate_address_label'] = $this->getConfig('rotate_address_label', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_custom_code'] = $this->getConfig('pickpack_custom_code', 0, false, $wonder, $storeId);//don't see code
            $this->_packingsheet[$storeId][$wonder]['pickpack_custom_code_nudge'] = explode(",", $this->getConfig('pickpack_custom_code_nudge', '0,0', true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['pickpack_return_address_yn'] = $this->getConfig('pickpack_return_address_yn', 0, false, $wonder, $storeId);
            if ($this->_packingsheet[$storeId][$wonder]['pickpack_return_address_yn'] == 'yesgroup') {
                $this->_packingsheet[$storeId][$wonder]['pickpack_return_address_group1'] = $this->getConfig('pickpack_return_address_group1', '', false, $wonder, $storeId);
                $this->_packingsheet[$storeId][$wonder]['pickpack_return_address_group2'] = $this->getConfig('pickpack_return_address_group2', '', false, $wonder, $storeId);
                $this->_packingsheet[$storeId][$wonder]['pickpack_return_address_group3'] = $this->getConfig('pickpack_return_address_group3', '', false, $wonder, $storeId);
                $this->_packingsheet[$storeId][$wonder]['pickpack_return_address_group_default'] = $this->getConfig('pickpack_return_address_group_default', '', false, $wonder, $storeId);
                $this->_packingsheet[$storeId][$wonder]['pickpack_returnfont_group'] = $this->getConfig('pickpack_returnfont_group', 9, false, $wonder, $storeId);//need change config position
                $this->_packingsheet[$storeId][$wonder]['pickpack_returnaddress_group'] = explode(",", $this->getConfig('pickpack_returnaddress_group', $pageConfig['returnAddressFooterXYDefault'], true, $wonder, $storeId));
                $this->_packingsheet[$storeId][$wonder]['show_return_logo_yn'] = $this->getConfig('pickpack_returnlogo_group', '', false, $wonder, $storeId);// didn't have code to show this
                $this->_packingsheet[$storeId][$wonder]['pickpack_logo_dimension'] = 100;
                $this->_packingsheet[$storeId][$wonder]['show_return_logo2_yn'] = 0;
                $this->_packingsheet[$storeId][$wonder]['pickpack_logo2_dimension'] = 100;
            } elseif ($this->_packingsheet[$storeId][$wonder]['pickpack_return_address_yn'] == 1) {
                $this->_packingsheet[$storeId][$wonder]['pickpack_return_address'] = $this->getConfig('pickpack_return_address', '', false, $wonder, $storeId);
                $this->_packingsheet[$storeId][$wonder]['pickpack_returnfont'] = $this->getConfig('pickpack_returnfont', 9, false, $wonder, $storeId);
                $this->_packingsheet[$storeId][$wonder]['pickpack_returnaddress'] = explode(",", $this->getConfig('pickpack_returnaddress', $pageConfig['returnAddressFooterXYDefault'], true, $wonder, $storeId));//need change config name
                $this->_packingsheet[$storeId][$wonder]['rotate_return_address'] = $this->getConfig('rotate_return_address', 0, false, $wonder, $storeId);
                $this->_packingsheet[$storeId][$wonder]['show_return_logo_yn'] = $this->getConfig('pickpack_returnlogo', 0, false, $wonder, $storeId);//need change config name
                if ($this->_packingsheet[$storeId][$wonder]['show_return_logo_yn']){
                    $this->_packingsheet[$storeId][$wonder]['pickpack_logo_dimension'] = $this->getConfig('pickpack_logo_dimension', 100 , false, $wonder, $storeId);
                    $this->_packingsheet[$storeId][$wonder]['pickpack_nudgelogo'] = explode(",", $this->getConfig('pickpack_nudgelogo', $pageConfig['return_logo_XYDefault'], true, $wonder, $storeId));
                }
                $this->_packingsheet[$storeId][$wonder]['show_return_logo2_yn'] = $this->getConfig('pickpack_returnlogo2', 0, false, $wonder, $storeId);//need change config name
                if ($this->_packingsheet[$storeId][$wonder]['show_return_logo2_yn']){
                    $this->_packingsheet[$storeId][$wonder]['pickpack_logo2_dimension'] = $this->getConfig('pickpack_logo2_dimension', 100 , true, $wonder, $storeId);//need change config option
                    $this->_packingsheet[$storeId][$wonder]['pickpack_nudgelogo2'] = explode(",", $this->getConfig('pickpack_nudgelogo2', $pageConfig['return_logo2_XYDefault'], true, $wonder, $storeId));
                }
            } else {
                $this->_packingsheet[$storeId][$wonder]['show_return_logo_yn'] = 0;
                $this->_packingsheet[$storeId][$wonder]['show_return_logo2_yn'] = 0;
            }

            $this->_packingsheet[$storeId][$wonder]['pickpack_show_full_payment_yn'] = $this->getConfig('pickpack_show_full_payment_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_show_full_payment_nudge'] = explode(",", $this->getConfig('pickpack_show_full_payment_nudge', '0,0', true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['show_shipping_method_bottom_yn'] = $this->getConfig('show_shipping_method_bottom_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_shipping_method_bottom_nudge'] = explode(",", $this->getConfig('show_shipping_method_bottom_nudge', '0,0', true, $wonder, $storeId));//don't have code for this option
            $this->_packingsheet[$storeId][$wonder]['pickpack_bottom_shipping_address_yn'] = $this->getConfig('pickpack_bottom_shipping_address_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_shipaddress'] = explode(",", $this->getConfig('pickpack_shipaddress', $pageConfig['addressFooterXYDefault'], true, $wonder, $storeId));//need to change name of this option
            $this->_packingsheet[$storeId][$wonder]['capitalize_label2_yn'] = $this->getConfig('capitalize_label2_yn', 0, false, $wonder, $storeId); // o,usonly,1
            $this->_packingsheet[$storeId][$wonder]['pickpack_shipfont'] = $this->getConfig('pickpack_shipfont', 15, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_shipaddress_maxpoints'] = $this->getConfig('pickpack_shipaddress_maxpoints', 250, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_bottom_shipping_address_id_yn'] = $this->getConfig('pickpack_bottom_shipping_address_id_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_nudge_id_bottom_shipping_address'] = explode(",", $this->getConfig('pickpack_nudge_id_bottom_shipping_address', '0, 7', true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shipaddress_packbarcode_yn'] = $this->getConfig('shipaddress_packbarcode_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['bottom_barcode_nudge'] = explode(",", $this->getConfig('bottom_barcode_nudge', '0,7', true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['pickpack_bottom_movable_order_id_yn'] = $this->getConfig('pickpack_bottom_movable_order_id_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_nudge_bottom_movable_order_id'] = explode(",", $this->getConfig('pickpack_nudge_bottom_movable_order_id', '0, 0', true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['tracking_number_yn'] = $this->getConfig('tracking_number_yn', 1, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tracking_number_fontsize'] = $this->getConfig('tracking_number_fontsize', 15, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tracking_number_nudge'] = explode(",", $this->getConfig('tracking_number_nudge', '0,0', true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['tracking_number_barcode_yn'] = $this->getConfig('tracking_number_barcode_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tracking_number_barcode_fontsize'] = $this->getConfig('tracking_number_barcode_fontsize', 15, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['tracking_number_barcode_nudge'] = explode(",", $this->getConfig('tracking_number_barcode_nudge', '0,0', true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['shipping_address_background_yn'] = $this->getConfig('shipping_address_background_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['shipping_address_background_nudge'] = explode(',',$this->getConfig('shipping_address_background_nudge', '0,0', false, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['pickpack_second_bottom_shipping_address_yn'] = $this->getConfig('pickpack_second_bottom_shipping_address_yn', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_second_shipaddress'] = explode(",", $this->getConfig('pickpack_second_shipaddress', $pageConfig['addressFooterXYDefault'], true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['pickpack_bottom_shipping_address_yn_xtra'] = $this->getConfig('pickpack_bottom_shipping_address_yn_xtra', 0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_shipfont_xtra_2'] = $this->getConfig('pickpack_shipfont_xtra_2', 8, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['flat_address_margin_rt_xtra'] = $this->getConfig('flat_address_margin_rt_xtra', 0, true, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['flat_address_margin_rt_xtra_2'] = $this->getConfig('flat_address_margin_rt_xtra_2', 0, true, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['pickpack_shipaddress_xtra'] = explode(",", $this->getConfig('pickpack_shipaddress_xtra', $pageConfig['addressFooterXYDefault_xtra'], true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['show_customs_declaration'] = $this->getConfig('show_customs_declaration',0, false, $wonder, $storeId);
            $this->_packingsheet[$storeId][$wonder]['show_customs_declaration_nudge'] = explode(',',$this->getConfig('show_customs_declaration_nudge','340,250', true, $wonder, $storeId));
            $this->_packingsheet[$storeId][$wonder]['show_customs_declaration_dimension'] = explode(',',$this->getConfig('show_customs_declaration_dimension','279,245', false, $wonder, $storeId));
            /*************************** END BOTTOM OF PAGE CONFIG SECTION *******************************/

        }
        return $this->_packingsheet[$storeId][$wonder];
    }

    public function getZebraLabelConfigArray($storeId = null)
    {
        if ($storeId === null) $storeId = Mage::app()->getStore()->getStoreId();
        if(!isset($this->_zebralabel[$storeId])) {
            $this->_zebralabel = array();

            $this->_zebralabel[$storeId]['use_courierrules_shipping_label'] = $this->getConfig('use_courierrules_shipping_label',0, false, 'label_zebra', $storeId);
            $this->_zebralabel[$storeId]['rotate_label'] = $this->getConfig('rotate_label',0, false, 'label_zebra', $storeId);
            $this->_zebralabel[$storeId]['resolution_label'] = $this->getConfig('resolution_label',0, false, 'label_zebra', $storeId);
            $this->_zebralabel[$storeId]['nudge_demension_zebra'] = explode(",", $this->getConfig('nudge_demension_zebra', '432,288', false, 'label_zebra',$storeId));
            $this->_zebralabel[$storeId]['paper_margin_zebra'] = explode(",", $this->getConfig('paper_margin_zebra', '13,5,5,5', false, 'label_zebra',$storeId));
            $this->_zebralabel[$storeId]['label_padding_zebra'] = explode(",", $this->getConfig('label_padding_zebra', '13,5,5,5', false, 'label_zebra',$storeId));
            $this->_zebralabel[$storeId]['nudge_shipping_address_zebra'] = explode(",", $this->getConfig('nudge_shipping_address_zebra', '0,0', false, 'label_zebra',$storeId));
            $this->_zebralabel[$storeId]['show_address_barcode_yn_zebra'] = $this->getConfig('show_address_barcode_yn_zebra', 0, false, 'label_zebra',$storeId);
            $this->_zebralabel[$storeId]['nudge_barcode'] = explode(",", $this->getConfig('nudge_barcode', '0,0', true, 'label_zebra', $storeId));
            $this->_zebralabel[$storeId]['show_order_id_barcode_yn'] = $this->getConfig('show_order_id_barcode_yn', 1, false, 'label_zebra',$storeId);
            $this->_zebralabel[$storeId]['nudge_order_id_barcode'] = explode(",", $this->getConfig('nudge_order_id_barcode', '0,0', true, 'label_zebra', $storeId));
            $this->_zebralabel[$storeId]['label_show_order_id_yn'] = $this->getConfig('label_show_order_id_yn', 1, false, 'label_zebra',$storeId);
            $this->_zebralabel[$storeId]['nudge_order_id'] = explode(",", $this->getConfig('nudge_order_id', '0,0', true, 'label_zebra', $storeId));
            $this->_zebralabel[$storeId]['font_size_order_id'] = $this->getConfig('font_size_order_id', 9, false, 'label_zebra', $storeId);
            $this->_zebralabel[$storeId]['capitalize_zebra_yn'] = $this->getConfig('capitalize_zebra_yn', '', false, 'label_zebra');

            $this->_zebralabel[$storeId]['add_cn22_page_yn'] = $this->getConfig('add_cn22_page_yn', 0, false, 'label_zebra', $storeId);
            $this->_zebralabel[$storeId]['add_cn22_page_nudge'] = explode(",", $this->getConfig('add_cn22_page_nudge', '0,0', false, 'label_zebra', $storeId));
            $this->_zebralabel[$storeId]['add_cn22_page_rotate'] = $this->getConfig('add_cn22_page_rotate', 1, false, 'label_zebra', $storeId);

            /*return address config*/
            $this->_zebralabel[$storeId]['label_return_address_yn'] = $this->getConfig('label_return_address_yn', 0, false, 'label_zebra'); // 0,1,yesside
            if ($this->_zebralabel[$storeId]['label_return_address_yn'] == 'yesside') {
                $this->_zebralabel[$storeId]['font_family_return_label_side'] = $this->getConfig('font_family_return_label_side', 'helvetica', false, 'label_zebra', $storeId);
                $this->_zebralabel[$storeId]['font_style_return_label_side'] = $this->getConfig('font_style_return_label_side', 'regular', false, 'label_zebra', $storeId);
                $this->_zebralabel[$storeId]['font_size_return_label_side'] = $this->getConfig('font_size_return_label_side', 9, false, 'label_zebra', $storeId);
                $this->_zebralabel[$storeId]['font_color_return_label_side'] = trim($this->getConfig('font_color_return_label_side', 'Black', false, 'label_zebra', $storeId));
                $this->_zebralabel[$storeId]['nudge_return_label'] = explode(",", $this->getConfig('nudge_return_label', '0,0', true, 'label_zebra', $storeId));
                $this->_zebralabel[$storeId]['label_return_address_side'] = $this->getConfig('label_return_address_side', '', false, 'label_zebra', $storeId);
                $this->_zebralabel[$storeId]['rotate_return_label_side'] = $this->getConfig('rotate_return_label_side', 1, false, 'label_zebra', $storeId);
            } elseif ($this->_zebralabel[$storeId]['label_return_address_yn'] == '1') {
                $this->_zebralabel[$storeId]['label_return_address'] = $this->getConfig('label_return_address', '', false, 'label_zebra', $storeId);
                $this->_zebralabel[$storeId]['font_family_return_label'] = $this->getConfig('font_family_return_label', 'helvetica', false, 'label_zebra', $storeId);
                $this->_zebralabel[$storeId]['font_style_return_label'] = $this->getConfig('font_style_return_label', 'regular', false, 'label_zebra', $storeId);
                $this->_zebralabel[$storeId]['font_size_return_label'] = $this->getConfig('font_size_return_label', 9, false, 'label_zebra', $storeId);
                $this->_zebralabel[$storeId]['font_color_return_label'] = trim($this->getConfig('font_color_return_label', 'Black', false, 'label_zebra', $storeId));
            } else {
                $this->_zebralabel[$storeId]['font_style_return_label'] = 15;
                $this->_zebralabel[$storeId]['rotate_return_label_side'] = 0;
            }
            /*end return address config*/

            /******************* PRODUCT LIST *******************/
            $this->_zebralabel[$storeId]['show_product_list'] = $this->getConfig('show_product_list', 0, false, 'label_zebra',$storeId);
            $this->_zebralabel[$storeId]['font_family_product'] = $this->getConfig('font_family_product', 'helvetica', false, 'label_zebra', $storeId);
            $this->_zebralabel[$storeId]['font_style_product'] = $this->getConfig('font_style_product', 'regular', false, 'label_zebra', $storeId);
            $this->_zebralabel[$storeId]['font_size_product'] = $this->getConfig('font_size_product', 15, false, 'label_zebra', $storeId);
            $this->_zebralabel[$storeId]['font_color_product'] = trim($this->getConfig('font_color_product', 'Black', false, 'label_zebra', $storeId));
            $this->_zebralabel[$storeId]['start_product_posy'] = $this->getConfig('start_product_posy', 0, false, "label_zebra",$storeId);
            $this->_zebralabel[$storeId]['first_start_y'] = $this->getConfig('first_start_y', 0, false, "label_zebra",$storeId);
            //product qty
            if ($this->_zebralabel[$storeId]['show_product_list']){
                $this->_zebralabel[$storeId]['show_product_qty'] = $this->getConfig('show_product_qty', 0, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_qty_xpos'] = $this->getConfig('pricesN_qtyX', 0, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_qty_title'] = $this->getConfig('product_qty_title', 'Qty', false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_qty_upsize_yn'] = $this->getConfig('product_qty_upsize_yn', 1, false, 'label_zebra', $storeId);
            }else $this->_zebralabel[$storeId]['show_product_qty'] = 0;
            //product name
            if ($this->_zebralabel[$storeId]['show_product_list']){
                $this->_zebralabel[$storeId]['show_product_name'] = $this->getConfig('show_product_name', 1, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_name_title'] = $this->getConfig('product_name_title', 'Name', false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_name_xpos'] = $this->getConfig('pricesN_productX', 100, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_name_store_view'] = $this->getConfig('name_store_view', 'storeview', false, "label_zebra",$storeId);
                $this->_zebralabel[$storeId]['product_name_trim_yn'] = $this->getConfig('trim_product_name_yn', 1, false, 'label_zebra',$storeId);
            }else $this->_zebralabel[$storeId]['show_product_name'] = 0;
            //product option
            if ($this->_zebralabel[$storeId]['show_product_list']){
                $this->_zebralabel[$storeId]['show_product_options_yn'] = $this->getConfig('show_product_options_yn', '', false, 'label_zebra', $storeId);
            }else $this->_zebralabel[$storeId]['show_product_options_yn'] = 0;
            //product sku
            if ($this->_zebralabel[$storeId]['show_product_list']){
                $this->_zebralabel[$storeId]['show_product_sku'] = $this->getConfig('show_product_sku', 1, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_sku_title'] = $this->getConfig('product_sku_title', 'Qty', false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_sku_xpos'] = $this->getConfig('pricesN_skuX', 30, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_sku_trim_yn'] = $this->getConfig('trim_product_sku_yn', 1, false, 'label_zebra',$storeId);
            }else $this->_zebralabel[$storeId]['show_product_sku'] = 0;
            //product barcode
            if ($this->_zebralabel[$storeId]['show_product_list']){
                $this->_zebralabel[$storeId]['show_product_barcode'] = $this->getConfig('show_product_barcode', 1, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_barcode_in_separate_line'] = $this->getConfig('product_barcode_in_separate_line', 1, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_barcode_title'] = $this->getConfig('product_barcode_title', 'Barcode', false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_barcode_xpos'] = $this->getConfig('product_barcode_xpos', 30, false, 'label_zebra',$storeId);
            }else $this->_zebralabel[$storeId]['show_product_barcode'] = 0;
            $this->_zebralabel[$storeId]['product_sort'] = trim($this->getConfig('sort_packing', 0, false, 'label_zebra', $storeId));
            $this->_zebralabel[$storeId]['sort_packing_order'] = trim($this->getConfig('sort_packing_order', 0, false, 'label_zebra', $storeId));
            $this->_zebralabel[$storeId]['combine_product_line'] = $this->getConfig('combine_product_line', 0, false, "label_zebra",$storeId);
            $this->_zebralabel[$storeId]['right_margin_line'] = $this->getConfig('right_margin_line', 10, false, "label_zebra",$storeId);
            $this->_zebralabel[$storeId]['separate_zebra_page_yn']     = $this->getConfig('separate_zebra_page_yn', 0, false, 'label_zebra',$storeId);
            if ($this->_zebralabel[$storeId]['separate_zebra_page_yn']){
                $this->_zebralabel[$storeId]['show_orderid_product_label'] = $this->getConfig('show_orderid_product_label', 0, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['show_address_product_label'] = $this->getConfig('show_address_product_label', 0, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['show_customer_comment'] = $this->getConfig('show_customer_comment', 0, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['show_product_price'] = $this->getConfig('show_product_price', 0, false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['product_price_title'] = $this->getConfig('product_price_title', '', false, "label_zebra", $storeId);
                $this->_zebralabel[$storeId]['product_price_xpos'] = $this->getConfig('pricesN_priceX', 80, false, 'label_zebra',$storeId);
            }else{
                $this->_zebralabel[$storeId]['show_orderid_product_label'] = 0;
                $this->_zebralabel[$storeId]['show_address_product_label'] = 0;
                $this->_zebralabel[$storeId]['show_product_price'] = 0;
            }
            $this->_zebralabel[$storeId]['show_order_date'] = $this->getConfig('show_order_date', 0, false, 'label_zebra');

            $this->_zebralabel[$storeId]['show_order_notes'] = $this->getConfig('show_order_notes', 0, false, 'label_zebra');
            if ($this->_zebralabel[$storeId]['show_order_notes']){
                $this->_zebralabel[$storeId]['order_notes_title'] = trim($this->getConfig('order_notes_title', 'Order Comments', false, 'label_zebra', $storeId));
                $this->_zebralabel[$storeId]['order_notes_position'] = $this->getConfig('order_notes_position', 'yesshipping', false, 'label_zebra',$storeId);
                $this->_zebralabel[$storeId]['order_notes_filter_options'] = $this->getConfig('order_notes_filter_options', 'no', false, 'label_zebra',$storeId);
            }
        }
        return $this->_zebralabel[$storeId];
    }

    public function getPageConfigArray($wonder = 'wonder', $storeId = null) {
        if ($storeId === null) $storeId = Mage::app()->getStore()->getStoreId();
        if(!isset($this->_page[$storeId])) {
			$pageConfig = array();
			$pageConfig['page_size'] = $this->getConfig('page_size', self::PAGE_SIZE, false, 'general', $storeId);
			$pagePadLeftRight = 0;
			$pagePadTop = 0;
			$pagePadBottom = 0;
			
			$logo_position = 'left';
            $pagePad = explode(',', '0,0,0');
			
			if($wonder != 'default_page'){
				$logo_position = $this->getConfig('pickpack_logo_position', 'left', false, $wonder, $storeId);
	            $pagePad = explode(',', trim($this->getConfig('page_pad', '0,0,0', false, $wonder, $storeId)));
			}

            if(isset($pagePad[0]))
				$pagePadLeftRight = $pagePad[0];

            if(isset($pagePad[1]))
				$pagePadTop = $pagePad[1];
            
            if(isset($pagePad[2]))
				$pagePadBottom = $pagePad[2];
            
			$pageConfig['logo_maxdimensions'] = '269,41';
            $pageConfig['vertical_spacing']  = 12;

            if ($pageConfig['page_size'] == 'letter') {
                $pageConfig['full_page_width'] = 612;
                $pageConfig['full_page_height'] = 792;
                $pageConfig['page_top'] = 770 - $pagePadTop;
                $pageConfig['page_bottom'] = 20 + $pagePadBottom;
                $pageConfig['padded_right'] = 594 - $pagePadLeftRight;
                $pageConfig['padded_left'] = 20 + $pagePadLeftRight;

                $pageConfig['title2XYDefault'] = '465,733';
                $pageConfig['orderDateXYDefault'] = '465,733';
                $pageConfig['orderIdXYDefault'] = '30,695';

                $pageConfig['addressXYDefault'] = '40,645';
                $pageConfig['addressFooterXYDefault'] = '50,170';
                $pageConfig['addressFooterXYDefault_xtra'] = '325,175';
                $pageConfig['returnAddressFooterXYDefault'] = '320,90';
                $pageConfig['companyAddressXNudgeDefault'] = '0';

                $pageConfig['giftMessageXYDefault'] = '0,90';
                $pageConfig['notesXYDefault'] = '25,90';
                $pageConfig['packedByXYDefault'] = '520,20';
                $pageConfig['supplierXYDefault'] = '465,755';

                $pageConfig['return_logo_XYDefault'] = '320,40';
                $pageConfig['return_logo2_XYDefault'] = '20,40';

                if ($logo_position == 'fullwidth')
                    $pageConfig['logo_maxdimensions'] = '612,50';
            }
            elseif ($pageConfig['page_size'] == 'a4') {
                $pageConfig['full_page_width'] = 595;
                $pageConfig['full_page_height'] = 842;
                $pageConfig['page_top'] = 820 - $pagePadTop;
                $pageConfig['page_bottom'] = 20 + $pagePadBottom;
                $pageConfig['padded_right'] = 577 - $pagePadLeftRight;
                $pageConfig['padded_left'] = 20 + $pagePadLeftRight;

                $pageConfig['title2XYDefault'] = '465,783';
                $pageConfig['orderDateXYDefault'] = '160,745';
                $pageConfig['orderIdXYDefault'] = '30,745';

                $pageConfig['addressXYDefault'] = '40,695';
                $pageConfig['addressFooterXYDefault'] = '50,140';//'20,113';
                $pageConfig['addressFooterXYDefault_xtra'] = '325,205';
                $pageConfig['returnAddressFooterXYDefault'] = '320,120';
                $pageConfig['companyAddressXNudgeDefault'] = '0';

                $pageConfig['giftMessageXYDefault'] = '0,140';
                $pageConfig['notesXYDefault'] = '25,140';
                $pageConfig['packedByXYDefault'] = '520,20';
                $pageConfig['supplierXYDefault'] = '465,805';

                $pageConfig['return_logo_XYDefault'] = '320,70';
                $pageConfig['return_logo2_XYDefault'] = '20,70';

                if ($logo_position == 'fullwidth')
                    $pageConfig['logo_maxdimensions'] = '595,50';
            }
            /*
            elseif ($pageConfig['page_size'] == 'a5-landscape') {
                            $pageConfig['full_page_width'] = 577;
                            $pageConfig['page_top'] = 395 - $pagePadTopBottom;
                            $pageConfig['page_bottom'] = 20 + $pagePad[1];
                            $pageConfig['padded_right'] = 577 - $pagePadLeftRight;
                            $pageConfig['padded_left'] = 20 + $pagePadLeftRight;
            
                            $pageConfig['title2XYDefault'] = '465,358';
                            $pageConfig['orderDateXYDefault'] = '160,320';
                            $pageConfig['orderIdXYDefault'] = '30,320';
            
                            $pageConfig['addressXYDefault'] = '40,270';
                            $pageConfig['addressFooterXYDefault'] = '50,100';
                            $pageConfig['addressFooterXYDefault_xtra'] = '325,165';
                            $pageConfig['returnAddressFooterXYDefault'] = '320,80';
                            $pageConfig['companyAddressXNudgeDefault'] = '0';
            
                            $pageConfig['giftMessageXYDefault'] = '0,80';
                            $pageConfig['notesXYDefault'] = '25,80';
                            $pageConfig['packedByXYDefault'] = '520,20';
                            $pageConfig['supplierXYDefault'] = '465,379';
            
                            $pageConfig['return_logo_XYDefault'] = '320,70';
                            $pageConfig['return_logo2_XYDefault'] = '20,70';
            
                            if ($logo_position == 'fullwidth')
                                $pageConfig['logo_maxdimensions'] = '556,41';
                        }
                        elseif ($pageConfig['page_size'] == 'a5-portrait') {
                            $pageConfig['full_page_width'] = 395;
                            $pageConfig['page_top'] = 577 - $pagePadTopBottom;
                            $pageConfig['page_bottom'] = 20 + $pagePad[1];
                            $pageConfig['padded_right'] = 395 - $pagePadLeftRight;
                            $pageConfig['padded_left'] = 20 + $pagePadLeftRight;
            
                            $pageConfig['title2XYDefault'] = '325,545';
                            $pageConfig['orderDateXYDefault'] = '80,445';
                            $pageConfig['orderIdXYDefault'] = '30,445';
            
                            $pageConfig['addressXYDefault'] = '40,595';
                            $pageConfig['addressFooterXYDefault'] = '50,140';
                            $pageConfig['addressFooterXYDefault_xtra'] = '305,205';
                            $pageConfig['returnAddressFooterXYDefault'] = '300,120';
                            $pageConfig['companyAddressXNudgeDefault'] = '-100';
            
                            $pageConfig['giftMessageXYDefault'] = '0,100';
                            $pageConfig['notesXYDefault'] = '25,100';
                            $pageConfig['packedByXYDefault'] = '320,20';
                            $pageConfig['supplierXYDefault'] = '325,505';
            
                            $pageConfig['return_logo_XYDefault'] = '320,70';
                            $pageConfig['return_logo2_XYDefault'] = '20,70';
            
                            if ($logo_position == 'fullwidth')
                                $pageConfig['logo_maxdimensions'] = '556,41';
                        }*/
            
            $pageConfig['page_pad_leftright'] = $pagePadLeftRight;
            $pageConfig['page_pad_top'] = $pagePadTop;
            $pageConfig['page_pad_bottom'] = $pagePadBottom;

            $background_color_subtitles = $this->validate_html_color($this->getConfig('background_color_subtitles', self::CALLOUT_COLOR, false, 'general', $storeId));
            $orderIdXY = explode(',',  $pageConfig['orderIdXYDefault']);
            $addressXY = explode(",", $pageConfig['addressXYDefault']);
            $orderIdXY[1] = ($pageConfig['page_top'] - 5 - 41 - 32);
            if ($background_color_subtitles == '#FFFFFF') {
                $orderIdXY[0] -= 11;
                $orderIdXY[1] += 11;
                $addressXY[0] -= 15;
                $addressXY[1] += 10;
            }

            $pageConfig['addressX'] = $addressXY[0];
            $pageConfig['addressY'] = $addressXY[1];
            $pageConfig['orderIdX'] = $orderIdXY[0];
            $pageConfig['orderIdY'] = $orderIdXY[1];
            $pageConfig['backgroundImageX'] = $pageConfig['padded_left'];
            $pageConfig['backgroundImageY'] = $pageConfig['page_top'] - 5;

            $this->_page[$storeId] = $pageConfig;
        }

        return $this->_page[$storeId];
    }

    protected function _getConfigKey($field, $default = '', $add_default = true, $group = 'wonder', $store = null, $trim = true,$section = 'pickpack_options') {
        if(is_object($store)) {
            $store = $store->getId();
        }
        return $field . $default . ($add_default ? '1' : '0') . $group . $store . ($trim ? '1' : '0') . $section;
    }

    public function getConfig($field, $default = '', $add_default = true, $group = 'wonder', $store = null, $trim = true,$section = 'pickpack_options') {
        $key = $this->_getConfigKey($field, $default, $add_default, $group, $store, $trim, $section);
        if(!isset($this->_config[$key])) {
            if ($trim) {
                $value = trim(Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store));
            }
            else {
                $value = Mage::getStoreConfig($section.'/' . $group . '/' . $field, $store);
            }

            if (strstr($field, '_color') !== FALSE) {
                if (($value !== 0) && ($value !== 1) && ($value !== "0") && ($value !== "1"))
                    $value = $this->validate_html_color($value);
            }

            /* check for the page body font color white */
            if( $field == 'font_color_body'){
                if( strtolower($value) == '#ffffff')
                    $value = self::FONT_COLOR_BODY;
            }

            if (!isset($value) || is_null($value) || ($value == '')) {
                $value = $default;
            } elseif ($field == 'csv_field_separator' && $value == ',') {
                $value = $value;
            } elseif ((strpos($value, ',') !== false) && (strpos($default, ',') !== false)) {
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
            $this->_config[$key] = $value;
        }

        return $this->_config[$key];
    }
}