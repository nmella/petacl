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
 * File        Label.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Label extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{

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
	end repeated	
	*/
	
	
    public function getLabel($orders = array()) {
        $helper = Mage::helper('pickpack');

        /*
        Paper Keywords and paper size in points

        Letter         612x792
        LetterSmall     612x792
        Tabloid         792x1224
        Ledger        1224x792
        Legal         612x1008
        Statement     396x612
        Executive     540x720
        A0               2384x3371
        A1              1685x2384
        A2        1190x1684
        A3         842x1190
        A4         595x842
        A4Small         595x842
        A5         420x595
        B4         729x1032
        B5         516x729
        Envelope     ???x???
        Folio         612x936
        Quarto         610x780
        10x14         720x1008
        */
        $store_id = Mage::app()->getStore()->getId();
        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();

        $page_size = $this->_getConfig('page_size', 'a4', false, 'label');

        if ($page_size == 'letter') {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $page_top = 770;
            $padded_right = 587;
            $paper_width = 612;
            $paper_height = 792;
            $label_width = $this->_getConfig('label_width_letter', 190, false, 'label');
            $label_height = $this->_getConfig('label_height_letter', 168, false, 'label');
            $paper_margin = explode(",", $this->_getConfig('paper_margin_letter', '22,30,22,30', false, 'label'));
            $label_padding = explode(",", $this->_getConfig('label_padding_letter', '5,5,5,5', false, 'label'));
            $nudge_shipping_address = explode(",", $this->_getConfig('nudge_shipping_address_letter', '0,0', false, 'label'));
            $show_address_barcode_yn = $this->_getConfig('show_address_barcode_yn_letter', 0, false, 'label');
        } elseif ($page_size == 'a4') {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $page_top = 820;
            $padded_right = 570;
            $paper_width = 595;
            $paper_height = 842;
            $label_width = $this->_getConfig('label_width', 190, false, 'label');
            $label_height = $this->_getConfig('label_height', 168, false, 'label');
            $paper_margin = explode(",", $this->_getConfig('paper_margin', '22,30,22,30', false, 'label'));
            $label_padding = explode(",", $this->_getConfig('label_padding', '5,5,5,5', false, 'label'));
            $nudge_shipping_address = explode(",", $this->_getConfig('nudge_shipping_address', '0,0', false, 'label'));
            $show_address_barcode_yn = $this->_getConfig('show_address_barcode_yn', 0, false, 'label');
        } elseif ($page_size == 'zebra') {
            $page = $pdf->newPage('288:432');
            $page_top = 430;
            $padded_right = 286;
            $paper_width = 288;
            $paper_height = 432;
            $label_width = $this->_getConfig('label_width_zebra', 190, false, 'label');
            $label_height = $this->_getConfig('label_height_zebra', 168, false, 'label');
            $paper_margin = explode(",", $this->_getConfig('paper_margin_zebra', '22,30,22,30', false, 'label'));
            $label_padding = explode(",", $this->_getConfig('label_padding_zebra', '10,5,5,5', false, 'label'));
            $nudge_shipping_address = explode(",", $this->_getConfig('nudge_shipping_address_zebra', '0,0', false, 'label'));
            $nudge_shipping_address[1] -= 85;
            $show_address_barcode_yn = $this->_getConfig('show_address_barcode_yn_zebra', 0, false, 'label');
            $ship_image1_yn = null;
            $ship_image1_nudge = null;
            $ship_image1_match = null;
            $ship_image2_yn = null;
            $ship_image2_nudge = null;
            $ship_image2_match = null;
            $ship_image3_yn = null;
            $ship_image3_nudge = null;
            $ship_image3_match = null;

            $ship_image1_yn = $this->_getConfig('label_shiplogo1_yn', 0, false, 'label');
            if ($ship_image1_yn == 1) {
                $ship_image1_nudge = explode(",", $this->_getConfig('label_nudge_shiplogo1', '0,0', false, 'label'));
                $ship_image1_match = trim(strtolower($this->_getConfig('shiplogo1_match', '', false, 'label')));

                $ship_image2_yn = $this->_getConfig('label_shiplogo2_yn', 0, false, 'label');
                if ($ship_image2_yn == 1) {
                    $ship_image2_nudge = explode(",", $this->_getConfig('label_nudge_shiplogo2', '0,0', false, 'label'));
                    $ship_image2_match = trim(strtolower($this->_getConfig('shiplogo2_match', '', false, 'label')));

                    $ship_image3_yn = $this->_getConfig('label_shiplogo3_yn', 0, false, 'label');
                    if ($ship_image3_yn == 1) {
                        $ship_image3_nudge = explode(",", $this->_getConfig('label_nudge_shiplogo3', '0,0', false, 'label'));
                        $ship_image3_match = trim(strtolower($this->_getConfig('shiplogo3_match', '', false, 'label')));
                    }
                }
            }
        } else {
            $label_width = 190;
            $label_height = 168;
            $paper_margin = explode(",", '22,30,22,30');
            $label_padding = explode(",", '5,5,5,5');
            $nudge_shipping_address = explode(",", $this->_getConfig('nudge_shipping_address', '0,0', true, 'label'));
            $show_address_barcode_yn = $this->_getConfig('show_address_barcode_yn', 0, false, 'label');
        }

        if ($page_size != 'zebra') {
            $ship_image1_yn = null;
            $ship_image1_nudge = null;
            $ship_image1_match = null;
            $ship_image2_yn = null;
            $ship_image2_nudge = null;
            $ship_image2_match = null;
            $ship_image3_yn = null;
            $ship_image3_nudge = null;
            $ship_image3_match = null;
        }

        $top_left_x = ($paper_margin[1] + $label_padding[1]); //40;

        $top_left_y = ($paper_height - ($paper_margin[0] + $label_padding[0])); //1

        $available_width = $label_width - $label_padding[1] - $label_padding[3];

        $margin = explode(',', Mage::getStoreConfig('pickpack_options/label/paper_margin'));

        if ($paper_margin[3] > $paper_margin[1] && (isset($margin[3]) && $margin[3] != '')) {
            $top_left_x = ($paper_margin[3] + $label_padding[3]);
        }
        if ($paper_margin[1] > $paper_margin[3] && (isset($margin[1]) && $margin[1] != '')) {
            $width = $page->getWidth();
            $top = $paper_margin[1] + $available_width + $label_padding[1];
            $top_left_x = ($width - $top) / 10;
        }


        $show_order_id_yn = $this->_getConfig('label_show_order_id_yn', 1, false, 'label');
        $show_barcode_yn = $this->_getConfig('show_order_id_barcode_yn', 0, false, 'label');
        $nudge_order_id_barcode = explode(",", $this->_getConfig('nudge_order_id_barcode', '0,0', true, 'label', $store_id));
        $nudge_order_id_barcode[0] += 40;
        $nudge_order_id_barcode[1] -= 50;
        $nudge_order_id = explode(",", $this->_getConfig('nudge_order_id', '0,0', true, 'label', $store_id));
        $font_family_label = $this->_getConfig('font_family_label', 'helvetica', false, 'label', $store_id);
        $font_style_label = $this->_getConfig('font_style_label', 'regular', false, 'label', $store_id);
        $font_size_label = $this->_getConfig('font_size_label', 15, false, 'label', $store_id);
        $font_color_label = trim($this->_getConfig('font_color_label', 'Black', false, 'label', $store_id));
        $show_product_list = $this->_getConfig('show_product_list', 0, false, 'label', $store_id);

        $non_standard_characters = $this->_getConfig('non_standard_characters', 0, false, 'general', $store_id);

        if ($non_standard_characters == 'msgothic') {
            $font_family_label = 'msgothic';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'tahoma') {
            $font_family_label = 'tahoma';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'garuda') {
            $font_family_label = 'garuda';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'sawasdee') {
            $font_family_label = 'sawasdee';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'kinnari') {
            $font_family_label = 'kinnari';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'purisa') {
            $font_family_label = 'purisa';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'traditional_chinese') {

            $font_family_label = 'traditional_chinese';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'simplified_chinese') {

            $font_family_label = 'simplified_chinese';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'hebrew') {
            $font_family_body = 'hebrew';
            $font_family_header = 'hebrew';
            $font_family_gift_message = 'hebrew';
            $font_family_comments = 'hebrew';
            $font_family_message = 'hebrew';
            $font_family_company = 'hebrew';
            $font_family_subtitles = 'hebrew';
            $non_standard_characters = 1;
        } elseif ($non_standard_characters == 'yes') {
            $non_standard_characters = 2;
        }
        $override_address_format_yn = 1;
        $customer_email_yn = $this->_getConfig('customer_email_yn', 'no', false, 'general', $store_id);
        $customer_phone_yn = $this->_getConfig('customer_phone_yn', 'no', false, 'general', $store_id);
        $address_format = $this->_getConfig('address_format', '', false, 'general'); //col/sku
        $address_countryskip = $this->_getConfig('address_countryskip', 0, false, 'general');
        $return_address_yn = $this->_getConfig('label_return_address_yn', 0, false, 'label'); // 0,1,yesside

        if ($return_address_yn == 'yesside') {
            $font_family_return_label = $this->_getConfig('font_family_return_label_side', 'helvetica', false, 'label', $store_id);
            $font_style_return_label = $this->_getConfig('font_style_return_label_side', 'regular', false, 'label', $store_id);
            $font_size_return_label = $this->_getConfig('font_size_return_label_side', 9, false, 'label', $store_id);
            $font_color_return_label = trim($this->_getConfig('font_color_return_label_side', 'Black', false, 'label', $store_id));
        } elseif ($return_address_yn == '1') {
            $font_family_return_label = $this->_getConfig('font_family_return_label', 'helvetica', false, 'label', $store_id);
            $font_style_return_label = $this->_getConfig('font_style_return_label', 'regular', false, 'label', $store_id);
            $font_size_return_label = $this->_getConfig('font_size_return_label', 9, false, 'label', $store_id);
            $font_color_return_label = trim($this->_getConfig('font_color_return_label', 'Black', false, 'label', $store_id));
        }
        $capitalize_label_yn = $this->_getConfig('capitalize_label_yn', 0, false, 'general', $store_id); // o,usonly,1

        $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $store_id);
        switch ($barcode_type) {
            case 'code128':
                $font_family_barcode = 'Code128bWin.ttf';
                break;

            case 'code39':
                $font_family_barcode = 'CODE39.ttf';

                break;

            case 'code39x':
                $font_family_barcode = 'CODE39X.ttf';

                break;

            default:
                $font_family_barcode = 'Code128bWin.ttf';
                break;
        }

        $label_logo_yn = $this->_getConfig('label_logo_yn2', 0, false, 'label', $store_id);
        $scale = $this->_getConfig('top_shipping_address_background_yn_scale', 0, false, 'label', $store_id);
        $label_nudgelogo = explode(",", $this->_getConfig('label_nudgelogo', '0,0', false, 'label', $store_id)); //0,0


        $order_id_barcode_nudge = explode(",", $this->_getConfig('nudge_order_id_barcode', '0,0', true, 'label', $store_id));
        $fontColorGrey = 0.6;

        $red_bkg_color = new Zend_Pdf_Color_Html('lightCoral');
        $grey_bkg_color = new Zend_Pdf_Color_GrayScale(0.7);
        $dk_grey_bkg_color = new Zend_Pdf_Color_GrayScale(0.3); //darkCyan
        $dk_cyan_bkg_color = new Zend_Pdf_Color_Html('darkCyan'); //darkOliveGreen
        $dk_og_bkg_color = new Zend_Pdf_Color_Html('darkOliveGreen'); //darkOliveGreen
        $black_color = new Zend_Pdf_Color_Rgb(0, 0, 0);
        $grey_color = new Zend_Pdf_Color_GrayScale(0.3);
        $greyout_color = new Zend_Pdf_Color_GrayScale(0.6);
        $white_color = new Zend_Pdf_Color_GrayScale(1);

        $font_size_titles = 14;

        $split_supplier_yn_default = 0;
        $supplier_attribute_default = 'supplier';
        $supplier_options_default = 'filter';

        $pdf->pages[] = $page;
        $current_column = 0;
        $current_row = 0;
        $address_count = 0;
        $count = 0;

        $current_x = $top_left_x;
        $current_y = $top_left_y;

        $temp_y = $current_y;
        $temp_x = $current_x;
        $subheader_start = 0;
        $next_y = $current_y;
        $flag_label_height = 0;
        foreach ($orders as $orderSingle) {
            $storeId = Mage::app()->getStore()->getId();
            $useGFSLabel = false;
            if (Mage::helper('pickpack')->isInstalled('Moogento_CourierRules') && ($this->_getConfig('use_courierrules_shipping_label', 0, false, 'label', $storeId) == 1)) {
                try {
                    if (mageFindClassFile('Moogento_CourierRules_Helper_Connector')) {

                        $show_courierrules_label_nudge[0] = $top_left_x;
                        $show_courierrules_label_nudge[1] = $top_left_y - $label_height;
                        $show_courierrules_label_dimension[0] = $label_width;
                        $show_courierrules_label_dimension[1] = $label_height;

                        $labels = Mage::helper('moogento_courierrules/connector')->getConnectorLabels($orderSingle);
                        if (count($labels)) {
                            $i = 0;
                            foreach ($labels as $label) {
                                if ($i > 0) {
                                    $page = $this->newPageLabel();
                                }
                                $tmpFile = Mage::helper('pickpack')->getConnectorLabelTmpFile($label);
                                $imageObj = Zend_Pdf_Image::imageWithPath($tmpFile);
                                $page->drawImage($imageObj, $show_courierrules_label_nudge[0], $show_courierrules_label_nudge[1], $show_courierrules_label_nudge[0] + $show_courierrules_label_dimension[0], $show_courierrules_label_nudge[1] + $show_courierrules_label_dimension[1]);
                                unset($tmpFile);
                                $i++;
                            }
                            $useGFSLabel = true;
                        }
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                    exit;
                }
            }
            if (!$useGFSLabel) {

                $font_size_adjust = 0;
                //var_dump($current_y);
                if ($address_count > 0) {
                    // going top left down, then across
                    // if last label bigger than 1 label, start on fresh
                    if (($current_y - $temp_y) > ($label_height - $label_padding[0] - $label_padding[3])) {
                        $current_y = ($current_y - $label_height);
                    }

                    //Calculate X pos.
                    if (($temp_y - $label_height - $paper_margin[2]) < 0) {
                        $current_y = $top_left_y;
                        $next_y = $top_left_y;
                        if (($current_x + $label_width + $paper_margin[3]) > $paper_width) {
                            $page = $this->newPageLabel();
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

                if (($current_y - $paper_margin[2]) < 0) {
                    $current_y = $top_left_y;
                    $next_y = $top_left_y;
                    if (($current_x + $label_width + $paper_margin[3]) > $paper_width) {
                        $page = $this->newPageLabel();
                        $current_x = $top_left_x;

                    } else {
                        $current_x += $label_width;
                    }
                }

                $temp_y = $current_y;

                $temp_x = $current_x;

                $order = $helper->getOrder($orderSingle);
                $order_id = $order->getRealOrderId();
                // store-specific options
                $store_id = $order->getStore()->getId();
                // itemcollection
                $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);

                $nudge_barcode = explode(",", $this->_getConfig('nudge_barcode_letter', '0,0', true, 'label', $store_id));
                if ($return_address_yn == 'yesside') {
                    $return_address = $this->_getConfig('label_return_address_side', '', false, 'label', $store_id);

                    if ($page_size == 'a4') {
                        $return_label_nudge_xy = explode(",", $this->_getConfig('nudge_return_label_side_a4', '0,0', true, 'label', $store_id));
                        $nudge_barcode = explode(",", $this->_getConfig('nudge_barcode_a4', '0,0', true, 'label', $store_id));
                    } elseif ($page_size == 'zebra') {
                        $return_label_nudge_xy = explode(",", $this->_getConfig('nudge_return_label_side_zebra', '0,0', true, 'label', $store_id));
                        $return_label_nudge_xy[0] -= 230;
                        $return_label_nudge_xy[1] -= 210;
                        $nudge_barcode = explode(",", $this->_getConfig('nudge_barcode_zebra', '0,0', true, 'label', $store_id));
                        $nudge_barcode[0] += 10;
                    } else {
                        $return_label_nudge_xy = explode(",", $this->_getConfig('nudge_return_label_side', '0,0', true, 'label', $store_id));
                    }
                } elseif ($return_address_yn == '1') {

                    $return_address = $this->_getConfig('label_return_address', '', false, 'label', $store_id);

                    if ($page_size == 'a4') {
                        $return_label_nudge_xy = explode(",", $this->_getConfig('nudge_return_label_a4', '0,0', true, 'label', $store_id));
                        $nudge_barcode = explode(",", $this->_getConfig('nudge_barcode_a4', '0,0', true, 'label', $store_id));
                    } elseif ($page_size == 'zebra') {
                        $return_label_nudge_xy = explode(",", $this->_getConfig('nudge_return_label_zebra', '0,0', true, 'label', $store_id));
                        $return_label_nudge_xy[0] -= 230;
                        $return_label_nudge_xy[1] -= 210;
                        $nudge_barcode = explode(",", $this->_getConfig('nudge_barcode_zebra', '0,0', true, 'label', $store_id));
                        $nudge_barcode[0] += 10;
                    } else {
                        $return_label_nudge_xy = explode(",", $this->_getConfig('nudge_return_label_letter', '0,0', true, 'label', $store_id));
                    }
                }

                /******************NEW SHIPPING ADDRESS DETAIL*********************/
                $has_shipping_address = false;
                // $has_billing_address = false;
                foreach ($order->getAddressesCollection() as $address) {
                    if ($address->getAddressType() == 'shipping' && !$address->isDeleted()) {
                        $has_shipping_address = true;
                        break;
                    }
                }

                if ($has_shipping_address !== false) {
                    if ($order->getShippingAddress()->getFax())
                        $customer_fax = trim($order->getShippingAddress()->getFax());
                    else
                        $customer_fax = '';
					
                    if ($order->getShippingAddress()->getTelephone())
                        $customer_phone = trim($order->getShippingAddress()->getTelephone());
                    else
                        $customer_phone = '';
					
                    if ($order->getCustomerEmail())
						$customer_email = trim($order->getCustomerEmail());
                    else 
						$customer_email = '';
					
                    if ($order->getShippingAddress()->getCompany())
                        $customer_company = trim($order->getShippingAddress()->getCompany());
                    else
                        $customer_company = '';
                    
					if ($order->getShippingAddress()->getName())
                        $customer_name = trim($order->getShippingAddress()->getName());
                    else
                        $customer_name = '';
                    
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
                    else
                        $customer_city = '';
                    
					if ($order->getShippingAddress()->getPostcode())
                        $customer_postcode = trim(strtoupper($order->getShippingAddress()->getPostcode()));
                    else
                        $customer_postcode = '';
                    
					if ($order->getShippingAddress()->getRegion())
                        $customer_region = trim($order->getShippingAddress()->getRegion());
                    else
                        $customer_region = '';

                    if ($order->getShippingAddress()->getRegionCode())
                        $customer_region_code = trim($order->getShippingAddress()->getRegionCode());
                    else
                        $customer_region_code = '';
                   
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
                    } else
                        $customer_country = '';
                }

                $shipping_address = array();
                $if_contents = array();
                $shipping_address['company'] = $customer_company;
                $shipping_address['firstname'] = ucwords($customer_firstname);
                $shipping_address['lastname'] = ucwords($customer_lastname);
                $shipping_address['name'] = $customer_name;
                $shipping_address['name'] = ucwords(trim(preg_replace('~^' . $shipping_address['company'] . '~i', '', $shipping_address['name'])));
                $shipping_address['city'] = ucwords($customer_city);
                $shipping_address['postcode'] = strtoupper($customer_postcode);
                $shipping_address['region_full'] = ucwords($customer_region);
                $shipping_address['region_code'] = strtoupper($customer_region_code);
                if ($customer_region_code != '') {
                    $shipping_address['region'] = $customer_region_code;
                } else {
                    $shipping_address['region'] = $customer_region;
                }
                $shipping_address['prefix'] = $customer_prefix;
                $shipping_address['suffix'] = $customer_suffix;
                $shipping_address['country'] = $customer_country;
                $shipping_address['street'] = ucwords($customer_street1);
                $shipping_address['street1'] = ucwords($customer_street1);
                $shipping_address['street2'] = ucwords($customer_street2);
                $shipping_address['street3'] = ucwords($customer_street3);
                $shipping_address['street4'] = ucwords($customer_street4);
                $shipping_address['street5'] = ucwords($customer_street5);

                if ($address_countryskip != '') {
                    if ($address_countryskip == 'usa' || $address_countryskip == 'united states' || $address_countryskip == 'united states of america') {
                        $address_countryskip = array(
                            'usa',
                            'united states of america',
                            'united states'
                        );
                    }
                    $shipping_address['country'] = str_ireplace($address_countryskip, '', $shipping_address['country']);

                    if (!is_array($address_countryskip) && (strtolower(trim($address_countryskip)) == "singapore" || strtolower(trim($address_countryskip)) == "monaco")) {
                        $shipping_address['city'] = str_ireplace($address_countryskip, '', $shipping_address['city']);
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
							$value = $this->checkCapsIntent($if_contents[1], $value);
                            $if_contents[1] = str_ireplace('{' . $key . '}', $value, $if_contents[1]);
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

                $address_format_set = trim(str_replace(array(
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

                $count = (count($shippingAddressArray) - 1);

                $addressLine = '';
                $line_height = (1 * $font_size_label);

                $stop_address = FALSE;

                if ($label_logo_yn == 1) {
                    //                         {
                    if ($order->getStoreId())
                        $store_id = $order->getStoreId();
                    else
                        $store_id = null;

                    if ($page_size == 'zebra') {
                        $x1 = 0;
                        //$x2 = $width;
                        $y1 = 0;
                        //$y2 = $height;
                    } else {
                        $x1 = $current_x;
                        //$x2 = ($current_x + $label_logo_xy[0] + $width);
                        $y1 = $current_y;
                        //$y2 = ($current_y + $label_logo_xy[1]);
                    }
                    $default_ship_image_x = 20;
                    $default_ship_image_y = 360;
                }

                if ($order->getStoreId())
                    $store = $order->getStoreId();
                else
                    $store = null;
                $shipping_method = clean_method($order->getShippingDescription(), 'shipping');
                $haystack = preg_replace("/[^\-\(\)\{\}\_a-z0-9\s]/", '', strtolower($shipping_method));
                if (isset($label_logo_yn) && $label_logo_yn == 1) {
                    $option_group = 'label';

                    $default_ship_image_x = 208;
                    $default_ship_image_y = 360;
                    //$ship_image_width     = $label_width-10;
                    //$ship_image_height    = $label_height-10;
                    $ship_image_width = 300;
                    $ship_image_height = 72;
                    $filename_folder = 'label_logo';
                    $sub_folder = 'logo_label';
                    $ship_image_nudge = $label_nudgelogo;

                    $packlogo_filename = Mage::getStoreConfig('pickpack_options/' . $option_group . '/' . $filename_folder, $store);
                    $last_folder = explode('/', $packlogo_filename);
                    $last_folder = $last_folder[count($last_folder) - 2];
                    if ($packlogo_filename) {
                        $packlogo_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $packlogo_filename;
                        $image_ext = explode('.', $packlogo_path);
                        $image_ext = array_pop($image_ext);
                        if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($packlogo_path))) {

                            $x1 = $temp_x + +$label_nudgelogo[0]; //$ship_image_nudge    = $ship_image1_nudge;
                            $x2 = $temp_x + $label_nudgelogo[0] + $ship_image_width;
                            $y1 = $temp_y + $label_nudgelogo[1] - $ship_image_height;
                            $y2 = $temp_y + $label_nudgelogo[1];
                            // var_dump($x2);

                            $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);

                            if ($scale && is_numeric($scale) && $scale != 100) {

                                if ($scale < 100) {
                                    $y1 = $y1 + (($y2 - $y1) * $scale / 100);
                                    $x2 = $x2 - (($x2 - $x1) * $scale / 100);
                                } else {
                                    $y1 = $y1 - (($y2 - $y1) * ($scale - 100) / 100);
                                    $x2 = $x2 + (($x2 - $x1) * ($scale - 100) / 100);
                                }
                            }
                            // var_dump($x2);
                            // var_dump($x1);

                            $page->drawImage($packlogo, $x1, $y1, $x2, $y2);
                        }
                    }
                    //exit;
                    unset($packlogo_filename);
                    unset($packlogo);
                    unset($packlogo_path);
                }
                if (isset($ship_image1_yn) && $ship_image1_yn == 1) {
                    $option_group = 'label';
                    $box1_shown = false;
                    $box2_shown = false;
                    $box3_shown = false;
                    $filename_folder = '';
                    $sub_folder = '';
                    $default_ship_image_x = 208;
                    $default_ship_image_y = 360;
                    $ship_image_width = 155; // pt @ 300dpi
                    $ship_image_height = 85; // pt @ 300dpi

                    if (strpos($haystack, $ship_image1_match) !== false) {
                        $filename_folder = 'label_shiplogo1';
                        $sub_folder = 'shiplogo1';
                        $ship_image_nudge = $ship_image1_nudge;
                    } elseif (strpos($haystack, $ship_image2_match) !== false) {
                        $filename_folder = 'label_shiplogo2';
                        $sub_folder = 'shiplogo2';
                        $ship_image_nudge = $ship_image2_nudge;
                    } elseif (strpos($haystack, $ship_image3_match) !== false) {
                        $filename_folder = 'label_shiplogo3';
                        $sub_folder = 'shiplogo3';
                        $ship_image_nudge = $ship_image3_nudge;
                    }

                    $packlogo_filename = Mage::getStoreConfig('pickpack_options/' . $option_group . '/' . $filename_folder, $store);
                    if ($packlogo_filename) {
                        // $packlogo_path = Mage::getStoreConfig('system/filesystem/media', $store) . '/moogento/pickpack/'.$sub_folder.'/' . $packlogo_filename;
                        $packlogo_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $packlogo_filename;
                        $image_ext = '';
                        $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);
                        $image_ext = array_pop(explode('.', $packlogo));
                        if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($packlogo))) {
                            $x1 = $default_ship_image_x + $ship_image_nudge[0] - ($ship_image_width / 2); //$ship_image_nudge    = $ship_image1_nudge;
                            $x2 = $default_ship_image_x + $ship_image_nudge[0] + ($ship_image_width / 2);
                            $y1 = $default_ship_image_y + $ship_image_nudge[1] - ($ship_image_height / 2);
                            $y2 = $default_ship_image_y + $ship_image_nudge[1] + ($ship_image_height / 2);

                            $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);

                            if ($scale && is_numeric($scale) && $scale != 100) {

                                if ($scale < 100) {
                                    $y1 = $y1 + (($y2 - $y1) * $scale / 100);
                                    $x2 = $x2 - (($x2 - $x1) * $scale / 100);
                                } else {
                                    $y1 = $y1 - (($y2 - $y1) * ($scale - 100) / 100);
                                    $x2 = $x2 + (($x2 - $x1) * ($scale - 100) / 100);
                                }
                            }
                            $page->drawImage($packlogo, $x1, $y1, $x2, $y2);
                        }
                    }
                    unset($packlogo_filename);
                    unset($packlogo);
                    unset($packlogo_path);
                }

                //Print order id barcode.
                if ($show_barcode_yn == 1) {
                    $barcode_font_size = 16;
                    //$barcode_y = ($subheader_start - ($font_size_return_label / 2) - ($barcode_font_size / 1.6));
                    //$barcode_x = $address_line_x;
                    $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($order_id, $barcode_type);
                    $barcodeWidth = 1.35 * $this->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                    $page->setFillColor($black_color);
                    $page->setLineColor($black_color);
                    $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
                    $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                    $order_barcode_x = ($temp_x + ($label_width * 0.9) - $barcodeWidth + $nudge_order_id_barcode[0]);
                    $order_barcode_y = ($temp_y + $nudge_order_id_barcode[1]);
                    $page->drawText($barcodeString, $order_barcode_x, $order_barcode_y, 'CP1252');
                    $page->setFillColor($white_color);
                    $page->setLineColor($white_color);
                    $page->drawRectangle(($order_barcode_x + 10 + $nudge_order_id[0]), ($order_barcode_y + 10 + $nudge_order_id[1]), ($order_barcode_x + $nudge_order_id[0] + ($barcodeWidth / 1.35) + 10), ($order_barcode_y + $nudge_order_id[1] - 10));

                    if ($show_order_id_yn == 1) {
                        $this->_setFont($page, $font_style_label, ($font_size_label - 2), $font_family_label, $non_standard_characters, $font_color_label);
                        $page->setFillColor($black_color);
                        $page->drawText('#' . $order_id, ($order_barcode_x + 15 + $nudge_order_id[0]), ($order_barcode_y + $nudge_order_id[1]), 'UTF-8');
                        $temp_y -= ($line_height * 0.6);
                    }
                } else if ($show_order_id_yn == 1) {
                    $this->_setFont($page, $font_style_label, ($font_size_label - 2), $font_family_label, $non_standard_characters, $font_color_label);
                    $page->setFillColor($black_color);
                    $page->drawText('#' . $order_id, ($temp_x + $nudge_order_id[0]), ($temp_y + $nudge_order_id[1]), 'UTF-8');
                    $temp_y -= ($line_height);
                }
                $this->_setFont($page, $font_style_label, $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);

                $i = 0;
                $line_height_top = (1.07 * $font_size_label);
                $line_height_bottom = (1.05 * $font_size_label);
                $i_space = 0;

                $shipping_line_count = (count($shippingAddressArray) - 1);
                $line_addon = 0;
                $token_addon = 0;


                $font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $max_line_width = ($label_width * 0.86);
				$this_address_line_y = 0;
				$address_line_x = 0;
                foreach ($shippingAddressArray as $i => $value) {
                    $value = trim($value);
                    if (strlen($value) < 1)
                        continue;
                    $line_width = $this->parseString($value, $font_temp, $font_size_label);
                    $max_chars = round($max_line_width / ($line_width / strlen($value)));
                    $skip = 0;
                    $line_bold = 0;
                    $i_space = ($i_space + 1);

                    $this->_setFont($page, $font_style_label, ($font_size_label - $font_size_adjust), $font_family_label, $non_standard_characters, $font_color_label);
                    $value_arr = wordwrap($value, $max_chars);
                    $address_line_x = $temp_x;
                    $address_line_y = $temp_y;
                    $returns_side_x = round($address_line_x + ($label_width * 0.8));
                    $returns_side_y = ($address_line_y - 12);

                    // shipping address nudge
                    $address_line_x = $address_line_x + $nudge_shipping_address[0];
                    $address_line_y = $address_line_y + $nudge_shipping_address[1];

                    $this_address_line_y = '';
                    $token = strtok($value_arr, "\n");
                    $flag = 0;
                    while ($token != false) {

                        $this_address_line_y = ($address_line_y - ($line_height_top * $i_space) - $line_addon - $token_addon);
                        if (($this_address_line_y - $paper_margin[2]) < 0) {
                            $this_address_line_y = $top_left_y;
                            if (($current_x + $label_width + $paper_margin[3]) > $paper_width) {
                                $page = $this->newPageLabel();
                                $current_x = $top_left_x;
                                $address_line_x = $top_left_x;
                                $temp_x = $top_left_x;
                            } else {
                                $current_x += $label_width;
                                $address_line_x += $label_width;
                                $temp_x += $label_width;
                            }
                            $current_y = $top_left_y;
                            $next_y = $top_left_y;
                            $temp_y = $top_left_y;

                            $address_line_y = $top_left_y;
                            $line_addon = 0;
                            $token_addon = 0;
                            $i_space = 0;
                            $this->_setFont($page, $font_style_label, $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);
                            $page->drawText(trim($token), $address_line_x, $this_address_line_y, 'UTF-8');
                            $token_addon += $font_size_label;
                        } else {
                            $page->drawText(trim($token), $address_line_x, $this_address_line_y, 'UTF-8');
                            $token_addon += $font_size_label;
                        }
                        $token = strtok("\n");
                    }
                    $i_space = ($i_space - 1);
                    $i++;

                }


                $subheader_start = $this_address_line_y - ($font_size_label * 1);
                $barcode_y = ($this_address_line_y - ($line_height_top * $i_space) - $line_addon - $token_addon);
                $barcode_x = $address_line_x;

                if ($order->getShippingAddress() && $order->getShippingAddress()->getPostcode()) {
                    $zipcode = trim(strtoupper($order->getShippingAddress()->getPostcode()));
                }
                if (($customer_phone_yn != 'no') && ($customer_phone != '')) {
                    $i_space += 1;
                    $value = $helper->__('T: ') . $customer_phone;
                    $line_addon = ($font_size_label * 0.5);
                    $address_line_x = $temp_x;
                    $address_line_y = $subheader_start;

                    $this->_setFont($page, 'regular', ($font_size_label - 1), $font_family_label, $non_standard_characters, 'Black');

                    if ($customer_phone_yn != 'yesdetails') {
                        $page->drawText($value, $address_line_x, $address_line_y, 'UTF-8');
                        $address_line_y -= ($line_height);
                    }
                    $subheader_start -= ($font_size_label);
                    $barcode_y = $address_line_y;
                    $barcode_x = $address_line_x;
                }
                if (($customer_email_yn != 'no') && ($customer_email != '')) {
                    $i_space += 1;
                    $value = $helper->__('E: ') . $customer_email;
                    $address_line_x = $temp_x;
                    $address_line_y = $subheader_start;
                    $this->_setFont($page, 'regular', ($font_size_label - 3), $font_family_label, $non_standard_characters, 'Gray');
                    if ($customer_email_yn != 'yesdetails') {
                        $page->drawText($value, $address_line_x, $address_line_y, 'UTF-8');
                        $subheader_start -= ($font_size_label);
                    }
                    $subheader_start -= ($font_size_label);
                    $barcode_y = $address_line_y;
                    $barcode_x = $address_line_x;
                }

                // logo on side
                $i = 0;
                if ($return_address_yn == 'yesside') {
                    while ($i < $address_count) {
                        // return_label_nudge_xy
                        $temp_r_y = ($returns_side_y + $return_label_nudge_xy[1]);
                        $temp_r_x = ($returns_side_x + $return_label_nudge_xy[0]);
                        $return_address_lines = explode("\n", $return_address);

                        $this->_setFont($page, $font_style_return_label, $font_size_return_label, $font_family_return_label, $non_standard_characters, $font_color_return_label);

                        $return_address_title_fontsize = 0;
                        $from_text = $helper->__('From');
                        if (preg_match('~^' . $from_text . '~', $return_address_lines[0])) {
                            $return_address_title_fontsize = -2;
                            if ($font_size_return_label > 10)
                                $return_address_title_fontsize = 2;
                            $this->_setFontRegular($page, ($font_size_return_label - $return_address_title_fontsize));
                            $this->_setFont($page, $font_style_return_label, ($font_size_return_label - $return_address_title_fontsize), $font_family_return_label, $non_standard_characters, $font_color_return_label);
                            $page->drawText($return_address_lines[0], $temp_r_x, $temp_r_y, 'UTF-8');
                        } else
                            $page->drawText($return_address_lines[0], $temp_r_x, $temp_r_y, 'UTF-8');

                        $this->_setFont($page, $font_style_return_label, $font_size_return_label, $font_family_return_label, $non_standard_characters, $font_color_return_label);

                        $line_height = ($font_size_return_label - ($return_address_title_fontsize * 2));
                        foreach ($return_address_lines as $key => $value) {
                            if ($value !== '' && $key > 0) {
                                $page->drawText(trim(strip_tags($value)), $temp_r_x, ($temp_r_y - $line_height), 'UTF-8');
                                $line_height += $font_size_return_label;
                            }
                        }
                        $i++;
                    }
                }

                if ($show_address_barcode_yn == 1 && $zipcode != '') {
                    $barcode_font_size = 28;
                    $barcode_y = ($subheader_start - ($font_size_return_label / 2) - ($barcode_font_size / 1.6));
                    $barcode_x = $address_line_x;
                    $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($zipcode, $barcode_type);
                    $barcodeWidth = 1.35 * $this->parseString($zipcode, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                    $page->setLineColor($black_color);
                    $page->setFillColor($black_color); //new Zend_Pdf_Color_Rgb(0, 0, 0));
                    $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);
                    $page->drawText($barcodeString, $barcode_x, ($barcode_y - 4), 'CP1252');
                    $page->setFillColor($white_color);
                    $page->setLineColor($white_color);
                    $page->drawRectangle(($barcode_x - $barcodeWidth / 2), ($barcode_y - ($barcode_font_size / 6)), ($barcode_x + ($label_width * 1)), ($barcode_y + ($barcode_font_size / 2)));
                }
                if ($show_product_list == 1) {
                    $this->_setFont($page, 'regular', ($font_size_label - 3), $font_family_label, $non_standard_characters, 'black');
                    //$subheader_start =  ($subheader_start - ($font_size_return_label / 2) - ($barcode_font_size / 1.6));
                    $current_x = $address_line_x;
                    $this->printProdcuctList($page, $itemsCollection, $current_x, $subheader_start, $non_standard_characters, $order_id);
                }
                $next_y = $subheader_start; //- 12;


                //$i = 0;

                if ($return_address_yn == 1) {
                    // while ($i < $address_count) {
                    // going top left down, then across
                    // if last label bigger than 1 label, start on fresh

                    if (($current_y - $temp_y) > $label_height)
                        $current_y = ($current_y - $label_height);
                    if (($temp_y - $label_height) < 0) {
                        $current_y = $top_left_y;
                        if (($current_x + $label_width) > $paper_width) {
                            $page = $this->newPageLabel();
                            $current_x = $top_left_x;
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
                    $page->drawText($helper->__('From') . ' :', $temp_x, $temp_y, 'UTF-8');
                    $this->_setFont($page, $font_style_return_label, $font_size_return_label, $font_family_return_label, $non_standard_characters, $font_color_return_label);
                    $line_height = 15;
                    // echo '<pre>';
                    //  var_dump($return_address);
                    //  echo '</pre>';
                    foreach (explode("\n", $return_address) as $value) {
                        if ($value !== '') {
                            $page->drawText(trim(strip_tags($value)), $temp_x, ($temp_y - $line_height), 'UTF-8');
                            $line_height = ($line_height + $font_size_return_label);
                        }
                    }
                    $i++;
                    // }
                }
            }
        }
        // exit;
        $this->_afterGetPdf();
        return $pdf;
    }
	
    public function newPageLabel(array $settings = array()) {
        $page_size = $this->_getConfig('page_size', 'a4', false, 'label');
        if ($page_size == 'letter') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_LETTER;
            $page_top = 770;
            $padded_right = 587;
        } else if ($page_size == 'a4') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_A4;

            // $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $page_top = 820;
            $padded_right = 570;
        } elseif ($page_size == 'zebra') {
            $settings['page_size'] = '288:432';

            // $page = $pdf->newPage('596:421');
            $page_top = 286;
            $padded_right = 430;
        }/*
        
        		elseif ($page_size == 'a5-landscape') {
                    $settings['page_size'] = '596:421';
                    $page_top = 395;
                    $padded_right = 573;
                } elseif ($page_size == 'a5-portrait') {
                    $settings['page_size'] = '421:596';
                    $page_top = 573;
                    $padded_right = 395;
                } */
        

        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page = $this->_getPdf()->newPage($pageSize);

        $this->_getPdf()->pages[] = $page;
        $this->y = ($page_top - 20);

        return $page;
    }

    public function printProdcuctList($page, $items = array(), $current_x, $current_y, $non_standard_characters, $order_id) {
        $store_id = Mage::app()->getStore()->getId();
        $font_family_label = $this->_getConfig('font_family_label', 'helvetica', false, 'label', $store_id);
        $font_style_label = $this->_getConfig('font_style_label', 'regular', false, 'label', $store_id);
        $font_size_label = $this->_getConfig('font_size_label', 15, false, 'label', $store_id);
        $font_color_label = trim($this->_getConfig('font_color_label', 'Black', false, 'label', $store_id));
        $rotate = $this->_getConfig('rotate_product_list', 0, false, 'label', $store_id);
        $nauge_rotate_product_list = explode(',', $this->_getConfig('nudge_rotate_product_list', '0,0', false, 'label', $store_id));

        $rotate_x = $current_x;
        $rotate_y = $current_y;

        if ($rotate == 1) {
            if ($nauge_rotate_product_list[0] < 0)
                $rotate_x += $nauge_rotate_product_list[0];//Move right
            else
                if ($nauge_rotate_product_list[0] > 0)
                    $rotate_x -= $nauge_rotate_product_list[0];//Move left

            if ($nauge_rotate_product_list[1] > 0)
                $rotate_y -= $nauge_rotate_product_list[1]; //Move top
            else
                if ($nauge_rotate_product_list[1] < 0)
                    $rotate_y -= $nauge_rotate_product_list[1]; //Move bottom

            $page->rotate($rotate_x, $rotate_y, 3.14 / 2);
        } else if ($rotate == 2) {
            if ($nauge_rotate_product_list[0] < 0)
                $rotate_x -= $nauge_rotate_product_list[0];//Move right
            else
                if ($nauge_rotate_product_list[0] > 0)
                    $rotate_x += $nauge_rotate_product_list[0];//Move left

            if ($nauge_rotate_product_list[1] > 0)
                $rotate_y -= $nauge_rotate_product_list[1]; //Move top
            else
                if ($nauge_rotate_product_list[1] < 0)
                    $rotate_y += $nauge_rotate_product_list[1]; //Move bottom

            $page->rotate($rotate_x, $rotate_y, -3.14 / 2);
        }

        $this->_setFont($page, $font_style_label, $font_size_label, $font_family_label, $non_standard_characters, $font_color_label);
        $page->drawText('#' . $order_id, $current_x, $current_y, 'UTF-8');
        $current_y -= 10;
        $this->_setFont($page, $font_style_label, $font_size_label - 2, $font_family_label, $non_standard_characters, $font_color_label);
        $page->drawText(Mage::helper('pickpack')->__('Your Items'), $current_x, $current_y, 'UTF-8');
        $this->_setFont($page, $font_style_label, $font_size_label - 3, $font_family_label, $non_standard_characters, $font_color_label);

        $bundle_array = array();
        $bundle_quantity = array();
        $array_remove_keys = array();
        $qty = '';
        foreach ($items as $key => $child) {
            if (in_array($child->getProductId(), $bundle_array)) {
                $qty = $child->getQtyOrdered() + $bundle_quantity[$child->getProductId()]['qty'];
                $child->setQtyOrdered($qty);
                $array_remove_keys[] = $bundle_quantity[$child->getProductId()]['key'];
            }
            $bundle_array[] = $child->getProductId();
            $bundle_quantity[$child->getProductId()] = array('qty' => $child->getQtyOrdered(), 'key' => $key);
        }
        foreach ($array_remove_keys as $val) unset($items[$val]);
        foreach ($items as $item) {
            // draw Product name.
            $current_y -= 10;
            $page->drawText(($item->getQtyOrdered() * 1) . ' X ' . $item->getName(), $current_x, $current_y, 'UTF-8');
        }

        if ($rotate == 1)
            $page->rotate($rotate_x, $rotate_y, 0 - (3.14 / 2));
        else if ($rotate == 2)
            $page->rotate($rotate_x, $rotate_y, 0 - (-3.14 / 2));

    }
/*
    protected function printShippingAddressBackground($order, $scale, $shipping_address_background, $page, $nudge = 0,$resolution,$image_zebra=null) {
        require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';
        $image_simple = new SimpleImage();
        $print_row = $this->getShippingAddressMaxPriority($order, $shipping_address_background);
        if ((($print_row != -1))) {
            $image_file_name = Mage::getBaseDir('media') . '/moogento/pickpack/image_background/' . $shipping_address_background[$print_row]['file'];
            if ($image_file_name) {
                $image_part                  = explode('.', $image_file_name);
                $image_ext                   = array_pop($image_part);
                $shipping_background_nudge_x = $shipping_address_background[$print_row]['xnudge'];
                $shipping_background_nudge_y = $shipping_address_background[$print_row]['ynudge'];


                if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) && (is_file($image_file_name))) {
                    $logo_shipping_maxdimensions[0] = $label_width - $nudge_shipping_addressX;
                    $logo_shipping_maxdimensions[1] = 300;

                    $imageObj        = Mage::helper('pickpack')->getImageObj($image_file_name);

                    $orig_img_width  = $imageObj->getOriginalWidth();
                    $orig_img_height = $imageObj->getOriginalHeight();

                    $img_height = $imageObj->getOriginalHeight();
                    $img_width  = $imageObj->getOriginalWidth();
                    if ($orig_img_width > ($logo_shipping_maxdimensions[0])) {
                        $img_height = ceil(($logo_shipping_maxdimensions[0] / $orig_img_width) * $orig_img_height);
                        $img_width  = $logo_shipping_maxdimensions[0];
                    }
                    if(isset($image_simple))
                    {
                        //Create new temp image
                        $final_image_path2 = $image_file_name;//$media_path . '/' . $image_url_after_media_path;
                        $image_source = $final_image_path2;
                        $io = new Varien_Io_File();
                        $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');

                        $img_width1 = intval($img_width*300/72);
                        $img_height1 = intval($img_height*300/72);

                        $filename = pathinfo($image_source, PATHINFO_FILENAME)."_".$img_width1."X".$img_height1.".jpeg";
                        $image_target = Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$filename;

                        if(!(file_exists($image_target))){
                            $image_simple->load($image_source);
                            $image_simple->resize($img_width1,$img_height1);
                            $image_simple->save($image_target, IMAGETYPE_JPEG, 100);
                        }
                        $image_file_name = $image_target;
                    }
                    $image = Zend_Pdf_Image::imageWithPath($image_file_name);
                    $x1 = $this->_padded_left + $shipping_background_nudge_x + $nudge_shipping_addressX;
                    $y1 = $page_top_or_bottom - $img_height + $shipping_background_nudge_y;
                    $x2 = $this->_padded_left + $img_width + $shipping_background_nudge_x + $nudge_shipping_addressX;
                    $y2 = $page_top_or_bottom + $shipping_background_nudge_y;
                    if($scale && is_numeric($scale) && $scale!= 100){
                        if($scale < 100){
                            $y1 =  $y1+(($y2-$y1)*$scale/100);
                            $x2 =  $x2-(($x2-$x1)*$scale/100);
                        }
                        else{
                            $y1 =  $y1-(($y2-$y1)*($scale-100)/100);
                            $x2 =  $x2+(($x2-$x1)*($scale-100)/100);
                        }
                    }
                    $page->drawImage($image, $x1 ,$y1 , $x2, $y2);
                }
            }
        }
        unset($image_zebra);
    }*/
}