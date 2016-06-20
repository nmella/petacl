<?php
/**
 * 
 * Date: 04.12.15
 * Time: 11:57
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Storeaddress extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $x;
    public $y;
    public $generalConfig = array();
    public $packingsheetConfig = array();

    public function showAddress() {
        $order = $this->getOrder();
        $wonder = $this->getWonder();
        $storeId = $this->getStoreId();
        $generalConfig = $this->getGeneralConfig();
        $pageConfig = $this->getPageConfig();
        $packingsheetConfig = Mage::helper('pickpack/config')->getPackingsheetConfigArray($wonder, $storeId);

        $custom_fonts_yn = $generalConfig['custom_fonts_yn'];
        $callout_color = $generalConfig['callout_color'];
        $company_address = '';

        $letterhead_yn = $this->_getConfig('letterhead', 1, false, $wonder, $storeId);
		if($letterhead_yn == 0) {
			$show_top_logo_yn = 0;
			$font_color_company = null;
			$company_address_nudge = null;
			$line_width_company = null;
			$company_vert_line = false;
			$font_family_company = null;
			$font_style_company = null;
			$font_size_company = null;
			$company_address_yn = null;
		} else {
			$show_top_logo_yn = $this->_getConfig('pickpack_packlogo', 0, false, $wonder, $storeId);
			
			$headerLogo = null;
			if($show_top_logo_yn == 1)
				$headerLogo = $this->getHeaderLogo();

	        $company_address_yn = $this->_getConfig('pickpack_company_address_yn', 0, false, $wonder, $storeId);
			if($company_address_yn == 1) {
		        $company_address_nudge = explode(',', $this->_getConfig('company_address_nudge', '0,0', false, $wonder, $storeId));
	           
			    if ($show_top_logo_yn == 1)
	                $company_address = $this->_getConfig('pickpack_company_address', '', false, $wonder, $storeId);
	            else
	                $company_address = $this->_getConfig('pickpack_company_address_no_logo', '', false, $wonder, $storeId);

	            $company_address_group1 = '';
	            $company_address_group2 = '';
	            $company_address_group3 = '';
				
				$font_color_company = $generalConfig['font_color_company'];
				$company_vert_line = true;
	            $line_width_company = $generalConfig['line_width_company'];
				if(($line_width_company == 0) || ($line_width_company == ''))
					$company_vert_line = false;
		        $font_family_company = $generalConfig['font_family_company'];
		        $font_style_company = $generalConfig['font_style_company'];
		        $font_size_company = $generalConfig['font_size_company'];
	       
			    if ($font_family_company == 'custom') {
		            $font_filename = $this->_getConfig('font_custom_company', '', false, 'general', $storeId);
		            $sub_folder = 'custom_font';
		            $option_group = 'general';
		            if ($font_filename) {
		                $font_path = Mage::getStoreConfig('system/filesystem/media', $this->getStoreId()) . '/moogento/pickpack/' . $sub_folder . '/' . $font_filename;
						// gonna pass the font file path through the style attribute?
		                if (is_file($font_path))
		                    $font_style_company = $font_path;
		                else
							$font_family_company = $generalConfig['font_family_company'];
		            }
		        } else
					$font_family_company = $generalConfig['font_family_company'];			
		
			} elseif ($company_address_yn == 'yesgroup') {
	            $company_address_group1 = $this->_getConfig('pickpack_company_address_group1', '', false, $wonder, $storeId);
	            $company_address_group2 = $this->_getConfig('pickpack_company_address_group2', '', false, $wonder, $storeId);
	            $company_address_group3 = $this->_getConfig('pickpack_company_address_group3', '', false, $wonder, $storeId);
	            $company_address = $this->_getConfig('pickpack_company_address_group_default', '', false, $wonder, $storeId);
        	}
           
        }
		
        $float_top_address_yn = $this->_getConfig('float_top_address_yn', 0, false, $wonder, $storeId);

        if ($packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup') {
            $return_address_group1 = $this->_getConfig('pickpack_return_address_group1', '', false, $wonder, $storeId);
            $return_address_group2 = $this->_getConfig('pickpack_return_address_group2', '', false, $wonder, $storeId);
            $return_address_group3 = $this->_getConfig('pickpack_return_address_group3', '', false, $wonder, $storeId);
        } else {
            $return_address_group1 = '';
            $return_address_group2 = '';
            $return_address_group3 = '';
        }

        //New TODO Moo: company address
        if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy') && is_object($order->getShippingAddress()) && $order->getShippingAddress()->getCountryId()) {
            $customer_country = trim(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()));

            $us_array = array('usa', 'u.s.a.', 'united states', 'united states of america');
            $eu_array = array('uk', 'united kingdom', 'england', 'great britain', 'belgium', 'bulgaria', 'czech republic', 'denmark', 'germany', 'estonia', 'ireland', 'greece', 'spain', 'france', 'italy', 'cyprus', 'latvia', 'lithuania', 'luxembourg', 'hungary', 'malta', 'netherlands', 'austria', 'poland', 'portugal', 'romania', 'slovenia', 'slovakia', 'finland', 'sweden');
            $non_eu_array = array('albania', 'andorra', 'armenia', 'azerbaijan', 'belarus', 'bosnia and herzegovina', 'georgia', 'liechtenstein', 'moldova', 'monaco', 'norway', 'russia', 'san marino', 'serbia', 'switzerland', 'ukraine', 'vatican', 'vatican city state');

            if (in_array(strtolower($customer_country), $eu_array)) {
                if ($company_address_yn == 'yesgroup') 
					$company_address = $company_address_group2; //EU
                if (   $packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup') 
					$return_address = $return_address_group2; //EU
            } elseif (in_array(strtolower($customer_country), $non_eu_array)) {
                if ($company_address_yn == 'yesgroup') 
					$company_address = $company_address_group2; // non_eu
                if ($packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup') 
					$return_address = $return_address_group2; // non_eu
            } elseif (in_array(strtolower($customer_country), $us_array)) {
                if ($company_address_yn == 'yesgroup') 
					$company_address = $company_address_group1; // USA
                if ($packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup') 
					$return_address = $return_address_group1; // USA
            } elseif (stripos('australia', $customer_country) !== FALSE) {
                if ($company_address_yn == 'yesgroup') 
					$company_address = $company_address_group3; //AUS
                if ($packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup') 
					$return_address = $return_address_group3; //AUS
            }
        }

        if (($company_address != '') && (($company_address_yn == 1) || ($company_address_yn == 'yesgroup'))) {
            $this->_setFont($this->getPage(), $font_style_company, $font_size_company, $font_family_company, $generalConfig['non_standard_characters'], $font_color_company);

		    if(isset($headerLogo) && ($headerLogo->getLogoPosition() == 'right'))
                $company_address_nudge[0] += $this->x;
			$company_address_nudge_start_point = explode(',',$generalConfig['company_address_start_point']);
            $y_temp_2 = $this->y;
			
            foreach (explode("\n", $company_address) as $value) {
                $this->getPage()->drawText(trim(strip_tags($value)), ($company_address_nudge[0] + $company_address_nudge_start_point[0]), ($this->y + $company_address_nudge[1] + $company_address_nudge_start_point[1]), 'UTF-8');
                $this->y -= ($font_size_company);
            }

            $y_temp_1 = $this->y + ($font_size_company + 10);
            $y_temp_2 = $y_temp_2 + $font_size_company;

            if(is_object($headerLogo)) {
                if ($y_temp_1 > $headerLogo->getY1())
                    $y_temp_1 = $headerLogo->getY1();

                if ($y_temp_2 < $headerLogo->getY2())
                    $y_temp_2 = $headerLogo->getY2();
            }

            if ($float_top_address_yn == 1)
                $float_top_address_y = ($this->y + ($generalConfig['font_size_body'] * 2.5));

	        $background_color_subtitles = $generalConfig['background_color_subtitles'];
	        $background_color_subtitles_zend = new Zend_Pdf_Color_Html($background_color_subtitles);

            if (isset($headerLogo) && $company_vert_line === true && (strtoupper($background_color_subtitles) != '#FFFFFF') ) {
                if(isset($headerLogo) && $headerLogo->getLogoPosition() == 'left') {
                    $company_vert_line_x1 = 304 + $company_address_nudge[0];
					$company_vert_line_x2 = 304 + $company_address_nudge[0] + ($line_width_company-0.5);
                    $company_vert_line_y1 = $y_temp_1 + $company_address_nudge[1];
                    $company_vert_line_y2 = $y_temp_2 + $company_address_nudge[1];
                }
                elseif(isset($headerLogo) && $headerLogo->getLogoPosition() == 'right') {
                    $company_vert_line_x1 = $pageConfig['logo_x1'] - 10 - $line_width_company;
                    $company_vert_line_x2 = $pageConfig['logo_x1'] - 10;
                    $company_vert_line_y1 = $y_temp_1 + $company_address_nudge[1];
                    $company_vert_line_y2 = $y_temp_2 + $company_address_nudge[1];
                }
                $this->getPage()->setFillColor($background_color_subtitles_zend);
                $this->getPage()->setLineColor($background_color_subtitles_zend);
                $this->getPage()->setLineWidth(0);
                $this->getPage()->drawRectangle($company_vert_line_x1, $company_vert_line_y1, $company_vert_line_x2, $company_vert_line_y2);
                $this->setVertLineX1($company_vert_line_x1);
                $this->setVertLineY1($company_vert_line_y1);
                $this->setVertLineX2($company_vert_line_x2);
                $this->setVertLineY2($company_vert_line_y2);

                $this->y = $company_vert_line_y1;

            }
        }
    }
}