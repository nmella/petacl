<?php
/**
 * 
 * Date: 01.12.15
 * Time: 18:49
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Returnaddress extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $returnAddress = '';
    public $return_logo_dimension = false;
    public $generalConfig = array();
    public $packingsheetConfig = array();
    const DEFAULT_RETURN_ADDRESS_BACKGROUND_IMAGE_MAX_SIZE = '750,500';

    public function __construct($arguments) {
        parent::__construct($arguments);
    }

    public function showLogo1() {
        $page = $this->getPage();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $return_logo_XYDefault = $pageConfig['return_logo_XYDefault'];

        $sub_folder = 'bottom_return_address_logo_pack';
        $option_group = 'wonder';
        if ($this->getWonder() != 'wonder') {
            $sub_folder = 'bottom_return_address_logo_invoice';
            $option_group = 'wonder_invoice';
        }

        if ($this->packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup') {
            $image = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pickpack_logo_group', $storeId);
            $return_logo_XY = explode(",", $this->_getConfig('pickpack_nudgelogo_group', $return_logo_XYDefault, true, $wonder, $storeId));
        }
        else {
            $image = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pickpack_logo', $storeId);
            $return_logo_XY = $this->packingsheetConfig['pickpack_nudgelogo'];
        }

        if ($image) {
            $image_simple = new SimpleImage();
            $return_image_dimension = explode(",", $this->_getConfig('pickpack_logo_demension','180,120', false, $this->getWonder(), $storeId));
            $filename = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $image;
            $temp_array_image = explode('.', $image);
            $image_ext = array_pop($temp_array_image);
            if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png') || ($image_ext == 'PNG')) && (is_file($filename))) {

                $default_cache_size = explode(',', self::DEFAULT_RETURN_ADDRESS_BACKGROUND_IMAGE_MAX_SIZE);

                $imageObj        = Mage::helper('pickpack')->getImageObj($filename);
                $orig_img_width  = $imageObj->getOriginalWidth();
                $orig_img_height = $imageObj->getOriginalHeight();

                //this code will caculate if image need resize or not
                if ($orig_img_width > $default_cache_size[0]){
                    $cache_size[1] = round(($orig_img_height * $default_cache_size[0]) / $orig_img_width);
                    $cache_size[0]  = $default_cache_size[0];
                }
                if ($orig_img_height > $default_cache_size[1]){
                    $cache_size[0] = round(($orig_img_width * $default_cache_size[1]) / $orig_img_height);
                    $cache_size[1]  = $default_cache_size[1];
                }

                //save image with size
                if(isset($cache_size)){
                    $image_simple->load($filename);
                    $image_simple->resize($cache_size[0],$cache_size[1]);
                    $image_simple->save($filename);
                    $orig_img_width = $cache_size[0];
                    $orig_img_height = $cache_size[1];
                }

                // note print_size = origin_size * (72 / 300);
                $img_print_size_width = $orig_img_width * (72 / 300);
                $img_print_size_height  = $orig_img_height * (72 / 300);

                if($this->packingsheetConfig['pickpack_logo_dimension']){
                    $img_print_size_height = $img_print_size_height * $this->packingsheetConfig['pickpack_logo_dimension'] / 100;
                    $img_print_size_width = $img_print_size_width * $this->packingsheetConfig['pickpack_logo_dimension']/100;
                }

                $x1 = $return_logo_XY[0];
                $x2 = $return_logo_XY[0] + $img_print_size_width;
                $y1 = $return_logo_XY[1] ;
                $y2 = $return_logo_XY[1] + $img_print_size_height;

                $image = Zend_Pdf_Image::imageWithPath($filename);
                $page->drawImage($image, $x1, $y1 , $x2, $y2);
                $minY[] = $return_logo_XY[1];
                $minY[] = $return_logo_XY[1] + $return_image_dimension[1];
            }
            unset($image);
            unset($image_ext);
            unset($temp_array_image);
            unset($image_ext);
        }
    }

    public function showLogo2() {
        $page = $this->getPage();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $return_logo2_XYDefault = $pageConfig['return_logo2_XYDefault'];

        if ($this->getReturnAddressMode() == 'yesgroup') {
            $return_logo2_XY = explode(",", $this->_getConfig('pickpack_nudgelogo2_group', $return_logo2_XYDefault, true, $wonder, $storeId));
        }
        else {
            $return_logo2_XY = $this->packingsheetConfig['pickpack_nudgelogo2'];
        }

        $sub_folder = 'bottom_return_address_logo_pack';
        $option_group = 'wonder';
        if ($this->getWonder() != 'wonder') {
            $sub_folder = 'bottom_return_address_logo_invoice';
            $option_group = 'wonder_invoice';
        }

        if ($this->packingsheetConfig['pickpack_return_address_yn'] == 'yesgroup') {
            $image = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pickpack_logo_group', $storeId);
        }
        else{
            $image = Mage::getStoreConfig('pickpack_options/' . $option_group . '/pickpack_logo', $storeId);
        }


        if ($image) {
            $image_simple = new SimpleImage();
            $return_image_dimension = explode(",", $this->_getConfig('pickpack_logo_demension','180,120', false, $this->getWonder(), $storeId));
            $filename = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $image;
            $temp_array_image = explode('.', $image);
            $image_ext = array_pop($temp_array_image);
            if ((($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png') || ($image_ext == 'PNG')) && (is_file($filename))) {

                $default_cache_size = explode(',', self::DEFAULT_RETURN_ADDRESS_BACKGROUND_IMAGE_MAX_SIZE);

                $imageObj        = Mage::helper('pickpack')->getImageObj($filename);
                $orig_img_width  = $imageObj->getOriginalWidth();
                $orig_img_height = $imageObj->getOriginalHeight();

                //this code will caculate if image need resize or not
                if ($orig_img_width > $default_cache_size[0]){
                    $cache_size[1] = round(($orig_img_height * $default_cache_size[0]) / $orig_img_width);
                    $cache_size[0]  = $default_cache_size[0];
                }
                if ($orig_img_height > $default_cache_size[1]){
                    $cache_size[0] = round(($orig_img_width * $default_cache_size[1]) / $orig_img_height);
                    $cache_size[1]  = $default_cache_size[1];
                }

                //save image with size
                if(isset($cache_size)){
                    $image_simple->load($filename);
                    $image_simple->resize($cache_size[0],$cache_size[1]);
                    $image_simple->save($filename);
                    $orig_img_width = $cache_size[0];
                    $orig_img_height = $cache_size[1];
                }

                // note print_size = origin_size * (72 / 300);
                $img_print_size_width = $orig_img_width * (72 / 300);
                $img_print_size_height  = $orig_img_height * (72 / 300);

                if($this->packingsheetConfig['pickpack_logo_dimension']){
                    $img_print_size_height = $img_print_size_height * $this->packingsheetConfig['pickpack_logo_dimension'] / 100;
                    $img_print_size_width = $img_print_size_width * $this->packingsheetConfig['pickpack_logo_dimension']/100;
                }

                $x1 = $return_logo2_XY[0];
                $x2 = $return_logo2_XY[0] + $img_print_size_width;
                $y1 = $return_logo2_XY[1] ;
                $y2 = $return_logo2_XY[1] + $img_print_size_height;

                $image = Zend_Pdf_Image::imageWithPath($filename);
                $page->drawImage($image, $x1, $y1 , $x2, $y2);
                $minY[] = $return_logo2_XY[1];
                $minY[] = $return_logo2_XY[1] + $return_image_dimension[1];
            }
            unset($image);
            unset($image_ext);
            unset($temp_array_image);
            unset($image_ext);
        }
    }

    public function showReturnAddress() {
        $order = $this->getOrder();
        $page = $this->getPage();
        $storeId = $this->getStoreId();
        $wonder = $this->getWonder();
        $pageConfig = $this->getPageConfig();
        $returnAddressFooterXYDefault = $pageConfig['returnAddressFooterXYDefault'];
		$return_address_yn = $this->packingsheetConfig['pickpack_return_address_yn'];

		if ($return_address_yn == 0)
			return;
		
        if ($return_address_yn == 'yesgroup') {
            $font_size_returnaddress = $this->packingsheetConfig['pickpack_returnfont_group'];
            $returnAddressFooterXY = $this->packingsheetConfig['pickpack_returnaddress_group'];
			
	        if (Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy') && is_object($order->getShippingAddress()) && $order->getShippingAddress()->getCountryId()) {
	            $customer_country = trim(Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId()));

	            $us_array = array('usa', 'u.s.a.', 'united states', 'united states of america');
	            $eu_array = array('uk', 'united kingdom', 'england', 'great britain', 'belgium', 'bulgaria', 'czech republic', 'denmark', 'germany', 'estonia', 'ireland', 'greece', 'spain', 'france', 'italy', 'cyprus', 'latvia', 'lithuania', 'luxembourg', 'hungary', 'malta', 'netherlands', 'austria', 'poland', 'portugal', 'romania', 'slovenia', 'slovakia', 'finland', 'sweden');
	            $non_eu_array = array('albania', 'andorra', 'armenia', 'azerbaijan', 'belarus', 'bosnia and herzegovina', 'georgia', 'liechtenstein', 'moldova', 'monaco', 'norway', 'russia', 'san marino', 'serbia', 'switzerland', 'ukraine', 'vatican', 'vatican city state');

	            if (in_array(strtolower($customer_country), $eu_array)) {
	                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') $this->returnAddress = $this->packingsheetConfig['pickpack_return_address_group2']; //EU
	            } elseif (in_array(strtolower($customer_country), $non_eu_array)) {
	                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') $this->returnAddress = $this->packingsheetConfig['pickpack_return_address_group2']; // non_eu
	            } elseif (in_array(strtolower($customer_country), $us_array)) {
	                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') $this->returnAddress = $this->packingsheetConfig['pickpack_return_address_group1']; // USA
	            } elseif (stripos('australia', $customer_country) !== FALSE) {
	                if ($this->_packingsheet['pickpack_return_address_yn'] == 'yesgroup') $this->returnAddress = $this->packingsheetConfig['pickpack_return_address_group3']; //AUS
	            }
	        }else{
                $this->returnAddress = $this->packingsheetConfig['pickpack_return_address_group_default'];
            }
        } else {
            $font_size_returnaddress = $this->packingsheetConfig['pickpack_returnfont'];
            $returnAddressFooterXY = $this->packingsheetConfig['pickpack_returnaddress'];
            $this->returnAddress = $this->packingsheetConfig['pickpack_return_address'];
        }

        $return_address_lines = explode("\n", $this->returnAddress);
        $i = 0;
        foreach ($return_address_lines as $index => $line_value) {
            $line_value = Mage::helper("pickpack/functions")->getVariable($line_value);
            if(is_array($line_value)){
                foreach ($line_value as $key => $value) {
                    $value = ltrim($value, ",");
                    $value = ltrim($value, ".");
                    $value = trim($value);
                    $return_address_lines[$i] = $value;
                    $i++;
                }
            }else{
                $line_value = ltrim($line_value, ",");
                $line_value = ltrim($line_value, ".");
                $line_value = trim($line_value);
                $return_address_lines[$i] = $line_value;
                $i++;
            }
        }
        unset($i);
		
        $rotate_return_address = $this->packingsheetConfig['rotate_return_address'];
        $rotate = $this->getRotateReturnAddress($rotate_return_address);
        if($rotate > 0)
			$page->rotate($returnAddressFooterXY[0], $returnAddressFooterXY[1], $rotate);
		
        $i = 1;
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.2, 0.2, 0.2));
        /*
        if (preg_match('~^From~i', $this->returnAddress)) {
                    $return_address_title_fontsize = -2;
                    if ($font_size_returnaddress > 10) $return_address_title_fontsize = 2;
                    $this->_setFontRegular($page, ($font_size_returnaddress - $return_address_title_fontsize));

                    $page->drawText($return_address_lines[0], $returnAddressFooterXY[0], $returnAddressFooterXY[1], 'UTF-8');
                    $i = 0;
         }*/
        
        $this->_setFont($page, $this->generalConfig['font_style_body'], $font_size_returnaddress, $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
		
        $line_height = 20; // note : why set line_height = 20 here?

        $bottom_return_address_pos = array();
        $bottom_return_address_pos['x'] = $returnAddressFooterXY[0];
        $bottom_return_address_pos['y'] = $returnAddressFooterXY[1];
        $bottom_return_address_pos = preg_replace('~[^.0-9]~', '', $bottom_return_address_pos);
        if (trim($bottom_return_address_pos['x']) == '') $bottom_return_address_pos['x'] = 0;
        if (trim($bottom_return_address_pos['y']) == '') $bottom_return_address_pos['y'] = 0;
	
        foreach ($return_address_lines as $value) {
            if ($value !== '' && $i > 0) {
                $bottom_return_address_pos['y'] = ($returnAddressFooterXY[1] - $line_height);
                $page->drawText(trim(strip_tags($value)), $bottom_return_address_pos['x'], $bottom_return_address_pos['y'], 'UTF-8');
                $line_height = ($line_height + ($font_size_returnaddress + 1));
            }
            $i++;
        }

        if($rotate > 0)
			$page->rotate($returnAddressFooterXY[0], $returnAddressFooterXY[1], 0-$rotate);
        /***************************PRINTING BOTTOM RETURN ADDRESS *******************************/
    }

    private function getRotateReturnAddress($rotate_return_address) {
        switch ($rotate_return_address) {
            case 0:
                $rotate = 0;
                break;
            case 1:
                $rotate = 3.14 / 2;
                break;
            case 2:
                $rotate = -3.14 / 2;
                break;
            default:
                $rotate = 0;
        }
        return $rotate;
    }
}