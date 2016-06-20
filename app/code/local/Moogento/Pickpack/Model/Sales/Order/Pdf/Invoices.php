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
 * File        Invoices.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */

/**
1.7
* Sales Order Invoice / Packing slip PDF model
*
* @category   Mage
* @package    Mage_Sales
* @author     Moogento.com <moo@moogento.com>
* This extension is only licensed for the single original Magento Instance that it was purchased for
*/
if (defined('COMPILER_INCLUDE_PATH')) {
    include_once "Moogento_Pickpack_Model_Sales_Order_Pdf_Functions.php";
} else {
    include_once "Functions.php";
}

define('LATIN1_UC_CHARS', 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝº');
define('LATIN1_LC_CHARS', 'àáâãäåæçèéêëìíîïðñòóôõöøùúûüýº');

//TODO Сделать более приличный вид загрузки
require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Zebra_Image.php';
require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/qrcode/qrlib.php';
require_once Mage::getBaseDir('app') . '/code/local/Moogento/Pickpack/Model/Sales/Order/Pdf/lib/Simple_Image.php';

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices extends Moogento_Pickpack_Model_Sales_Order_Pdf_Abstract
{
    protected $_nudgeY;
    protected $_itemsY;

    protected $_configObject;
    protected $_configPdfObject;
    protected $_configPageObject;

    protected $_wonder;
    protected $_storeId;

    protected $_printing_format = array();
    protected $_product_config = array();
    protected $_order_config = array();
    protected $_helper = '';
    protected $_logo_maxdimensions = array();
    protected $_columns_xpos_array = array();
    protected $_columns_xpos_array_order = array();

    protected $_general = array(); //general config for pickpack
    protected $_packingsheet = array(); //packing-sheet/invoice config for pickpack
    	
    public function __construct() {
        $this->action_path = Mage::helper('pickpack')->getFontPath();
        $this->setGeneralConfig();
    }

    public function setWonder($wonder) {
        $this->_wonder = $wonder;
    }

    public function getWonder() {
        return $this->_wonder;
    }

    public function setStoreId($storeId) {
        $this->_storeId = $storeId;
    }

    public function getStoreId() {
        return $this->_storeId;
    }

    public function getCurrentPage() {
        return $this->_currentPage;
    }

    public function getPdf() {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $this->_getPdf();
        $this->_afterGetPdf();
        return $pdf;
    }

    protected function _getConfig($field, $default = '', $add_default = true, $group = 'wonder', $store = null, $trim = true, $section = 'pickpack_options') {
        return Mage::helper('pickpack/config')->getConfig($field, $default, $add_default, $group, $store, $trim, $section);
    }

    public function parseString($string, $font = null, $fontsize = null) {
        return Mage::helper('pickpack/font')->parseString($string, $font, $fontsize);
    }

    protected function getShippingAddressMaxPriority($order, $shipping_address_background)  {
        $print_row                                = -1;
        $max_priority_row                         = 9999;
        $shipping_background_type                 = '';
        $find_shipping_pattern_in_shipping_detail = 0;
        $shipping_description                     = $order->getShippingDescription();

        if (is_array($shipping_address_background)) {
            foreach ($shipping_address_background as $rowId => $row_value) {
                $row_type = $row_value['type'][0];
                if (($row_type == 'shipping_method') && ($shipping_description != '')) {
                    $shipping_description   = strtolower($shipping_description);
                    $list_carriers_name_row = explode(",", strtolower($row_value['pattern']));

                    foreach ($list_carriers_name_row as $k => $v) {
                        $v = strtolower($v);
                        if (!empty($v))
                            $pos = strpos($shipping_description, $v);
						else
                            $pos = false;

                        if (($pos !== false) || ($v == '')) {
                            if ($row_value['priority'] == '')
                                $row_value['priority'] = 999;
							
                            if ($row_value['priority'] < $max_priority_row) {
                                $print_row                = $rowId;
                                $max_priority_row         = $row_value['priority'];
                                $shipping_background_type = $row_type;
                            }
                            $find_shipping_pattern_in_shipping_detail = 1;
                        }
                    }
                    unset($list_carriers_name_row);
                } else if ($row_type == 'courier_rules') {
                    if(Mage::helper('pickpack')->isInstalled('Moogento_CourierRules')) {
                        $courierrules_description = $order->getData('courierrules_description');
                        if(strlen(trim($courierrules_description)) > 0)
                            $shipping_description = $courierrules_description;

                        $shipping_description   = strtolower($shipping_description);
                        $list_carriers_name_row = explode(",", strtolower($row_value['pattern']));

                        foreach ($list_carriers_name_row as $k => $v) {
                            $v = strtolower($v);
                            if (!empty($v))
                                $pos = strpos($shipping_description, $v);
							else
                                $pos = false;

                            if (($pos !== false) || ($v == '')) {
                                if ($row_value['priority'] == '')
                                    $row_value['priority'] = 999;

                                if ($row_value['priority'] < $max_priority_row) {
                                    $print_row                = $rowId;
                                    $max_priority_row         = $row_value['priority'];
                                    $shipping_background_type = $row_type;
                                }

                                $find_shipping_pattern_in_shipping_detail = 1;
                            }
                        }
                        unset($list_carriers_name_row);
                    }
                } elseif ($row_type == 'shipping_zone') {
                    $customer_country_id  = $order->getShippingAddress()->getCountryId();
                    $zone_collection = mage::getModel("moogento_courierrules/zone")->getCollection();
                    foreach ($zone_collection as $item){
                        $item_data = $item->getData();
                        if ( in_array($customer_country_id,$item_data['countries']) ) {
                            if ($row_value['priority'] == '')
                                $row_value['priority'] = 999;

                            if ($row_value['priority'] < $max_priority_row) {
                                $print_row                = $rowId;
                                $max_priority_row         = $row_value['priority'];
                                $shipping_background_type = $row_type;
                            }
                        }

                    }

                } elseif ($row_type == 'country_group') {
                    $country_in_group     = 0;
                    $image_position_nudge = array();
                    $customer_country_id  = $order->getShippingAddress()->getCountryId();
                    if ((Mage::helper('pickpack')->isInstalled('Moogento_ShipEasy'))) {
                        $countryGroups                = Mage::getStoreConfig('moogento_shipeasy/country_groups');
                        $country_label_group          = $row_value['country_group'][0];
                        $country_group_list_key       = str_replace('label', 'countries', $country_label_group);
                        $country_group_list_value     = $countryGroups[$country_group_list_key];
                        $country_group_list_value_arr = explode(",", $country_group_list_value);

                        foreach ($country_group_list_value_arr as $k => $v) {
                            $pos = strpos($v, $customer_country_id);

                            if ($pos !== false) {
                                $country_in_group = 1;

	                            if ($row_value['priority'] == '')
	                                $row_value['priority'] = 999;

	                            if ($row_value['priority'] < $max_priority_row) {
	                                $print_row                = $rowId;
	                                $max_priority_row         = $row_value['priority'];
	                                $shipping_background_type = $row_type;
	                            }
	                        }
                        }

                    }
                }
            }
        }
        return $print_row;
    }

    protected function printShippingAddressBackground($order, $scale, $shipping_address_background, $padded_top, $padded_left, $page, $label_width = 0, $nudge_shipping_addressX = 0,$resolution,$image_zebra=null) {
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
					
					if(isset($image_simple)) {
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
                    $x1 = $padded_left + $shipping_background_nudge_x + $nudge_shipping_addressX;
                    $y1 = $padded_top - $img_height + $shipping_background_nudge_y;
                    $x2 = $padded_left + $img_width + $shipping_background_nudge_x + $nudge_shipping_addressX;
                    $y2 = $padded_top + $shipping_background_nudge_y;
                   
				    if($scale && is_numeric($scale) && $scale!= 100){
                        if($scale < 100) {
                            $y1 =  $y1+(($y2-$y1)*$scale/100);
                            $x2 =  $x2-(($x2-$x1)*$scale/100);
                        }
                        else {
                            $y1 =  $y1-(($y2-$y1)*($scale-100)/100);
                            $x2 =  $x2+(($x2-$x1)*($scale-100)/100);
                        }
                    }
                    $page->drawImage($image, $x1 ,$y1 , $x2, $y2);
                }
            }
        }
        unset($image_zebra);
    }

    protected function getConfigValue2($path) {
    	$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
    	$tableName4 = $resource->getTableName('core_config_data');
    	$query = 'SELECT * FROM '.$tableName4.' WHERE path like "%'.$path.'%"'.' LIMIT 1';
    	$data  = $readConnection->fetchAll($query);
		$config_value = $data[0]['value'];
		 try {
            $shipping_address_background = unserialize($config_value);
            return $shipping_address_background;
        }
        catch (Exception $e) {
			return '';
        }
    }

    protected function showShippingAddresBackground($order, $padded_top, $wonder = "", $store_id, $page, $padded_left, $scale = 100, $label_width = 250, $nudge_shipping_addressX = 0, $resolution = null) {

        $shipping_address_background = $this->_getConfig('shipping_address_background_shippingmethod', '', false, 'image_background', $store_id);
        if(strlen(trim($shipping_address_background)) == 0)
			return;

        try {
            $shipping_address_background = unserialize($shipping_address_background);
            if($shipping_address_background == false)
            	$shipping_address_background = $this->getConfigValue2('shipping_address_background_shippingmethod');
            $shipping_address_background = $this->checkCourrierrulesAndM2epro($shipping_address_background);
        } catch (Exception $e) {
        	return;
        }
        $this->printShippingAddressBackground($order, $scale, $shipping_address_background, $padded_top, $padded_left, $page, $label_width, $nudge_shipping_addressX, $resolution);
    }

    public function checkCourrierrulesAndM2epro($shipping_address_background) {
        if (Mage::helper('pickpack')->isInstalled("Moogento_CourierRules"))
            return $shipping_address_background;

        if (Mage::helper('pickpack')->isInstalled("Ess_M2ePro")){
            $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
            $_allShippingMethodDescription = array();
            foreach($methods as $_ccode => $_carrier)
            {
                if($_methods = $_carrier->getAllowedMethods())
                {
                    if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                        $_title = $_ccode;

                    foreach($_methods as $_mcode => $_method)
                    {
                        if ($_mcode == "m2eproshipping") 
							continue;
                        $_allShippingMethodDescription[] = $_title." - ".$_method;
                    }
                }
            }

            foreach ($shipping_address_background as $key => $item){
                if (trim($item['pattern'])=="") 
					continue;
                if (!in_array($item['pattern'] , $_allShippingMethodDescription))
                    unset($shipping_address_background[$key]);
            }
        }
        return $shipping_address_background;
    }

    protected function getNameShippingLabel($order) {
		$name_ship_label = "";
		$store_id = Mage::app()->getStore()->getId();
		$shipping_address_background = $this->_getConfig('shipping_address_background_shippingmethod', '', false, 'image_background', $store_id);
        try {
            $shipping_address_background = unserialize($shipping_address_background);
        }
        catch (Exception $e) {
        }
		$print_row = $this->getShippingAddressMaxPriority($order, $shipping_address_background);
		if($print_row != -1)
			$name_ship_label = $shipping_address_background[$print_row]['name'];
		return $name_ship_label;
	}

	protected function getShippingAddressFull($order, $font_size_label) {
        $address_full = '';
        $i            = 0;
        while ($i < 10) {
            if ($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i))) {
                $value             = trim($order->getShippingAddress()->getStreet($i));
                $max_chars         = 20;
                $font_temp         = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $font_size_compare = ($font_size_label * 0.8);
                $line_width        = $this->parseString('1234567890', $font_temp, $font_size_compare); // bigger = left
                $char_width        = $line_width / 10;
                $max_chars         = 200;
                $token             = strtok($value, "\n");
                while ($token !== false) {
                    if (trim(str_replace(',', '', $token)) != '')
                        $address_full .= trim($token) . ", ";
                    $token = strtok("\n");
                }
            }
            $i++;
        }

        $address_full = trim($address_full, ',');
        return $address_full;
    }

	protected function getShippingAddressOrder($order) {
		$shippingAddressFlat = '';
		$shippingAddressFlat = implode(',', $this->_formatAddress($order->getShippingAddress()->format('pdf')));
		$shipping_address = array();
		$shipping_address['company'] = $order->getShippingAddress()->getCompany();
		$shipping_address['name'] = $order->getShippingAddress()->getName();
		$shipping_address['firstname'] = $order->getShippingAddress()->getFirstname();
		$shipping_address['lastname'] = $order->getShippingAddress()->getLastname();
		$shipping_address['telephone'] = $order->getShippingAddress()->getTelephone();
		// $shipping_address['email'] = $order->getBillingAddress()->getEmail();
		$i = 0;
		while ($i < 10) {
			if ($order->getShippingAddress()->getStreet($i) && !is_array($order->getShippingAddress()->getStreet($i))) {
				if (isset($shipping_address['street'])) 
					$shipping_address['street'] .= ", \n";
				else 
					$shipping_address['street'] = '';
				$shipping_address['street'] .= $order->getShippingAddress()->getStreet($i);
				$street_key = 'street'.$i;
				$shipping_address[$street_key] = $order->getShippingAddress()->getStreet($i);
			}
			$i++;
		}
		$shipping_address['city'] = $order->getShippingAddress()->getCity();
		$shipping_address['postcode'] = $order->getShippingAddress()->getPostcode();
		$shipping_address['region'] = $order->getShippingAddress()->getRegion();
		$shipping_address['prefix'] = $order->getShippingAddress()->getPrefix();
		$shipping_address['suffix'] = $order->getShippingAddress()->getSuffix();
		$shipping_address['country'] = Mage::app()->getLocale()->getCountryTranslation($order->getShippingAddress()->getCountryId());
		return $shipping_address;
	}

	protected function getBillingAddressOrder($order) {
		$billingAddressFlat = '';
		$billingAddressFlat = implode(',', $this->_formatAddress($order->getBillingAddress()->format('pdf')));
		$billing_address = array();
		$billing_address['company'] = $order->getBillingAddress()->getCompany();
		$billing_address['name'] = $order->getBillingAddress()->getName();
		$billing_address['firstname'] = $order->getBillingAddress()->getFirstname();
		$billing_address['lastname'] = $order->getBillingAddress()->getLastname();
		$billing_address['telephone'] = $order->getBillingAddress()->getTelephone();
		// $shipping_address['email'] = $order->getBillingAddress()->getEmail();
		
		$i = 0;
		while ($i < 10) {
			if ($order->getBillingAddress()->getStreet($i) && !is_array($order->getBillingAddress()->getStreet($i))) {
				if (isset($billing_address['street']))
					$billing_address['street'] .= ", \n";
				else 
					$billing_address['street'] = '';
				$billing_address['street'] .= $order->getBillingAddress()->getStreet($i);
			}
			$i++;
		}
		$billing_address['city'] = $order->getBillingAddress()->getCity();
		$billing_address['postcode'] = $order->getBillingAddress()->getPostcode();
		$billing_address['region'] = $order->getBillingAddress()->getRegion();
		$billing_address['prefix'] = $order->getBillingAddress()->getPrefix();
		$billing_address['suffix'] = $order->getBillingAddress()->getSuffix();
		$billing_address['country'] = Mage::app()->getLocale()->getCountryTranslation($order->getBillingAddress()->getCountryId());
		return $billing_address;
	}

	protected function getAddressFormatByValue($key, $value, $address_format_set) {
		$value = trim($value);
		$if_contents = array();
		$value = preg_replace('~,$~', '', $value);
		$value = str_replace(',,', ',', $value);
		
		//check key in format address string
		$string_key_check = '{if '.$key.'}';
		$key_flag = strpos($address_format_set,$string_key_check);
		$search  = array($string_key_check,'{/if}');
		$replace = array('','');

		if($key_flag !== FALSE)
			$address_format_set = str_replace($search, $replace, $address_format_set);

		// end check key in format address string
		if ($value != '' && !is_array($value)) {
			$pre_value = '';
			preg_match('~\{if ' . $key . '\}(.*)\{\/if ' . $key . '\}~ims', $address_format_set, $if_contents);
			if (isset($if_contents[1]))
				$if_contents[1] = str_replace('{' . $key . '}', $value, $if_contents[1]);
			else 
				$if_contents[1] = '';
			$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~ims', $pre_value . $if_contents[1], $address_format_set);
			$address_format_set = str_ireplace('{' . $key . '}', $pre_value . $value, $address_format_set);
			$address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
			$address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
		} else {
			$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~i', '', $address_format_set);
			$address_format_set = str_ireplace('{' . $key . '}', '', $address_format_set);
			$address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
			$address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
		}
		return $address_format_set;
	}

	protected function addressPrintLine($shippingAddressArray, $black_color, $page, $sku_shipping_address_temp) {
		$i = 0;
		$stop_address = FALSE;
		$skip_entry = FALSE;

		foreach ($shippingAddressArray as $i => $value) {
			$value = trim($value);
			$skip_entry = FALSE;

			if (isset($value) && $value != '~') {
				// remove fax
				$value = preg_replace('!<(.*)$!', '', $value);
				if (preg_match('~T:~', $value)) {
					$value = str_replace('~', '', $value);
					$value = '[ ' . $value . ' ]';
				} elseif ($stop_address === FALSE) {
					if (!isset($shippingAddressArray[($i + 1)]) || preg_match('~T:~', $shippingAddressArray[($i + 1)]))
						$value = str_replace('~', '', $value); // last line, lets bold it and make it a bit bigger
					else {
						if ((!isset($shippingAddressArray[($i + 2)]) || preg_match('~T:~', $shippingAddressArray[($i + 2)])))
							$value = str_replace('~', '', $value);
						else 
							$value = str_replace('~', ',', $value);
					}
					$page->setFillColor($black_color);
				}
				if ($stop_address === FALSE && $skip_entry === FALSE)
					$sku_shipping_address_temp .= ',' . $value;
			}
			$i++;
		}
		$sku_shipping_address_temp = str_replace(
			array('  ', ',,', '<br />', '<br/>', "\n", "\p", ',,', ',,', ',', '-'),
			array(' ', ',', '', '', '', '', ',', ',', ', ', ''), $sku_shipping_address_temp);
		$sku_shipping_address_temp = preg_replace('~, $~', '', $sku_shipping_address_temp);
		$sku_shipping_address = preg_replace('~^\s?,\s?~', '', $sku_shipping_address_temp);
		return $sku_shipping_address;
	}

	protected function getItemGiftMessage($item,$max_chars_message) {
		$item_message_array = array();
		$_giftMessage = Mage::helper('giftmessage/message')->getGiftMessageForEntity($item);

		if(isset($_giftMessage)) {
			$item_message_from = 'From : ' . $_giftMessage->getRecipient();
			$item_message_from = wordwrap($item_message_from, $max_chars_message, "\n");
			$item_message_to = 'Message to : ' . $_giftMessage->getSender();
			$item_message_to = wordwrap($item_message_to, $max_chars_message, "\n");
			$item_message = $_giftMessage->getMessage();
			$item_message = wordwrap($item_message, $max_chars_message, "\n");
			$token = strtok($item_message, "\n");
			$msg_line_count = 2.5;
			if ($token != false) {
				while ($token != false) {
					$gift_msg_array[] = $token;
					$msg_line_count++;
					$token = strtok("\n");
				}
			} else
				$gift_msg_array[] = $item_message;
			$item_message_array[0] = $item_message_from;
			$item_message_array[1] = $item_message_to;
			$item_message_array[2] = $gift_msg_array;
		}
		return $item_message_array;
	}

	protected function getWidthString($message, $font_size) {
		$font_temp = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$line_width_message = $this->parseString($message, $font_temp, $font_size);
		return $line_width_message;
	}

	protected function getItemGiftMessageSeprated($item,$max_chars_message, $message_title_tofrom_yn) {
		$item_message_array = array();
		$_giftMessage = Mage::helper('giftmessage/message')->getGiftMessageForEntity($item);
		
		if(isset($_giftMessage)){
			$item_message_from = 'From : ' . $_giftMessage->getRecipient();
			$item_message_to = 'To : ' . $_giftMessage->getSender();
			$item_message = $_giftMessage->getMessage();
			
			if($message_title_tofrom_yn == 1)
				$item_message = $item_message_to . ' ' . $item_message_from . ' ' . "Message : " . $item_message;
			$item_message = wordwrap($item_message, $max_chars_message, "\n");
			$token = strtok($item_message, "\n");
			$msg_line_count = 2.5;
			
			if ($token != false) {
				while ($token != false) {
					$gift_msg_array[] = $token;
					$msg_line_count++;
					$token = strtok("\n");
				}
			} else
				$gift_msg_array[] = $item_message;
			$item_message_array = $gift_msg_array;
		}
		return $item_message_array;
	}

	protected function getMaxCharMessage($padded_right, $font_size_options, $font_temp, $padded_left=30) {
		return Mage::helper('pickpack/font')->getMaxCharMessage($padded_right, $font_size_options, $font_temp, $padded_left);
	}

	protected function showToFrom($message_title_tofrom_yn, $to_from, $msgX, $y, $to_from_from, $font_size_gift_message, $page) {
		if($message_title_tofrom_yn ==1) {
			$page->drawText(Mage::helper('sales')->__($to_from), ($msgX), $y, 'UTF-8');
			$y -= ($font_size_gift_message + 3);
			if (isset($to_from_from) && ($to_from_from != '')) {
				$page->drawText(Mage::helper('sales')->__($to_from_from), ($msgX), $y, 'UTF-8');
				$y -= ($font_size_gift_message + 3);
			}
		}
		return $y;
	}

    /**
     * Function for show qtys
     *
     * @param $gift_message_id
     * @param $gift_message_yn
     * @param $gift_message_item
     * @param $giftWrap_info
     * @param $gift_message_array
     * @return array
     */
    protected function getOrderGiftMessage($gift_message_id,$gift_message_yn, $gift_message_item, $giftWrap_info, $gift_message_array = array()) {
        return Mage::helper('pickpack/gift')->getOrderGiftMessage($gift_message_id,$gift_message_yn, $gift_message_item, $giftWrap_info, $gift_message_array = array());
	}

	protected function createMsgArray($gift_message) {
		$character_message_breakpoint = 96;
		$gift_message = wordwrap($gift_message, 96, "\n", false);
		$gift_msg_array = array();
		$token = strtok($gift_message, "\n");
		$msg_line_count = 2.5;
		while ($token != false) {
			$gift_msg_array[] = $token;
			$msg_line_count++;
			$token = strtok("\n");
		}
		return $gift_msg_array;
	}

    protected function drawOrderGiftMessage($gift_msg_array, $msgX, $font_size_gift_message, $y, $page, $message_line_spacing = null){
        if (is_null($message_line_spacing))
            $message_line_spacing = $font_size_gift_message + 3;

        foreach ($gift_msg_array as $gift_msg_line) {
            $page->drawText(trim($gift_msg_line), $msgX, $y, 'UTF-8');
            $y -= $message_line_spacing;
        }
        return $y;
    }

	protected function formatPriceTxt($order, $price) {
        return Mage::helper('pickpack/product')->formatPriceTxt($order, $price);
	}

	protected function createArraySort($sort_packing,$product_build, $sku,$product_id, $trim_names_yn, $product = null) {
		if ($sort_packing != 'none' && $sort_packing != '') {
			$product_build[$sku][$sort_packing] = '';
			$attributeName = $sort_packing;

			if ($attributeName == 'Mcategory')
				$product_build[$sku][$sort_packing] = $product_build[$sku]['%category%']; //$category_label;
			elseif ($sort_packing == 'sku')
				$product_build[$sku][$sort_packing] = $sku;
			else {
                if (!is_object($product) || is_null($product))
                    $product = Mage::helper('pickpack')->getProduct($product_id);

				if ($product->getData($attributeName)) {

                    $attributeValue = $product->getData($attributeName);
                    $attribute = $product->getResource()->getAttribute($attributeName);
                    
					if ($attribute->usesSource())
                        $return_value = $product->getAttributeText($attributeName, $attributeValue);
                    else
                        $return_value = $attributeValue;

                    $product_build[$sku][$sort_packing] = $return_value;
				}
			}
			unset($attributeName);
			unset($attribute);
			unset($attributeOptions);
			return $product_build[$sku][$sort_packing];
		}
	}

    protected function sortMultiDimensional(&$array, $subKey, $subKey2, $sortorder_packing_bool=false, $sortorder_packing_secondary_bool=false) {
        return Mage::helper('pickpack')->sortMultiDimensional($array, $subKey, $subKey2, $sortorder_packing_bool, $sortorder_packing_secondary_bool);
    }

	protected function _getTruncatedComment($comment) {
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
		$comment = str_replace('~','&#10;',$comment); //&#13;
		$comment = preg_replace('/Buyer:\s?$/i','',$comment); //&#13;
		$comment = preg_replace('/&#10;\s?$/i','',$comment); //&#13;

		return trim($comment);
    }

    protected function getOptionProductByStore($store_view, $helper, $product_id, $storeId, $specific_store_id, $options, $i) {
        return Mage::helper('pickpack/product')->getOptionProductByStore($store_view, $helper, $product_id, $storeId, $specific_store_id, $options, $i);
    }

	protected function printHeaderLogo($page, $store_id, $show_top_logo_yn, $page_top, $_logo_maxdimensions, $sub_folder, $option_group, $suffix_group, $x1 = 27, &$y2) {
        $image_simple = new SimpleImage();

		/***************************PRINTING 1 HEADER LOGO *******************************/
		$minY_logo = $page_top;
		if ($show_top_logo_yn == 1) {
			/*************************** PRINT HEADER LOGO *******************************/
			$packlogo_filename = Mage::getStoreConfig('pickpack_options/' . $option_group . $suffix_group, $store_id);
			$helper = Mage::helper('pickpack');
			if ($packlogo_filename) {
				$packlogo_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $packlogo_filename;
				if (is_file($packlogo_path)) {
					$img_width = $_logo_maxdimensions[0];
					$img_height = $_logo_maxdimensions[1];
					$imageObj = $helper->getImageObj($packlogo_path);
					$orig_img_width = $imageObj->getOriginalWidth();
					$orig_img_height = $imageObj->getOriginalHeight();
					$img_width = $orig_img_width;
					$img_height = $orig_img_height;

					/*************************** RESIZE IMAGE BY "AUTO-RESIZE" VALUE *******************************/
					if ($orig_img_width > $_logo_maxdimensions[0]) {
						$img_height = ceil(($_logo_maxdimensions[0] / $orig_img_width) * $orig_img_height);
						$img_width = $_logo_maxdimensions[0];
					} //Fix for auto height --> Need it?
					else
						if( isset($_logo_maxdimensions[2]) && ($_logo_maxdimensions[2] != 'fullwidth') ) {
                            if ($orig_img_height > $_logo_maxdimensions[1]) {
                                $temp_var = $_logo_maxdimensions[1] / $orig_img_height;
                                $img_height = $_logo_maxdimensions[1];
                                $img_width = $temp_var * $orig_img_width;
                            }
                        }

                    $y2 += 10;
					$y1 = ($y2 - $img_height);

					$x2 = ($x1 + $img_width);
					$image_ext = '';
					$temp_array_image = explode('.', $packlogo_path);
					$option_group_folder = str_replace('/','',$option_group);
					$suffix_group_folder = str_replace('/','',$suffix_group);

					$image_ext = array_pop($temp_array_image);
					if (($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) {
						if(isset($image_simple)) {
							$final_image_path2 = $packlogo_path;
                            $image_source = $final_image_path2;
							$io = new Varien_Io_File();
							$io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
							$io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage'.DS.$option_group_folder.DS.$suffix_group_folder.DS.'default');
							$ext = substr($image_source, strrpos($image_source, '.') + 1);
                            $image_source = $final_image_path2;
                            $packlogo_filename = str_replace($ext,'jpeg', $packlogo_filename);
                            $image_target= Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$option_group_folder.'/'. $suffix_group_folder.'/'.$packlogo_filename;
							if (!file_exists(dirname($image_target)))
                                mkdir(dirname($image_target), 0777, true);
                            if(($orig_img_width > $img_width*300/72) || ($orig_img_height > $img_height*300/72))
							{
                                if(!(file_exists($image_target))) {
									$size_1 = $img_width*360/72;
									$size_2 = $img_height*360/72;
	                                $image_simple->load($image_source);
	                                $image_simple->resize($size_1,$size_2);
	                                $image_simple->save($image_target);
                                }
								$packlogo_path = $image_target;
							}

						}
                        $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);
						$page->drawImage($packlogo, $x1, $y1, $x2, $y2);
						unset($packlogo);
						unset($packlogo_filename);
						unset($packlogo_path);
					}
					$minY_logo = $y1 - 20;
				}
			}

			/*************************** END PRINT HEADER LOGO ***************************/
		}
		unset($image_zebra);
		return $minY_logo;
	}

    protected function getArrayShippingAddress($shipping_address, $capitalize_label_yn, $address_format_set) {
		$if_contents = array();
		foreach ($shipping_address as $key => $value) {
			$value = trim($value);
            if (($capitalize_label_yn == 1) && ($key != 'postcode') && ($key != 'region_code') && ($key != 'region'))
				$value = $this->reformatAddress($value,'uppercase');
            elseif ($capitalize_label_yn == 2)
				$value = $this->reformatAddress($value,'capitals');
			
			$value = str_replace(array(',,', ', ,', ', ,'), ',', $value);
            $value = str_replace(array('N/a', 'n/a', 'N/A'), '', $value);
			$value = trim(preg_replace('~\-$~', '', $value));
			//check key in format address string
			$string_key_check = '{if ' . $key . '}';
			$key_flag = strpos($address_format_set, $string_key_check);
			$search = array($string_key_check, '{/if}');
			$replace = array('', '');
			if ($key_flag !== FALSE)
				$address_format_set = str_replace($search, $replace, $address_format_set);

			// end check key in format address string

			if ($value != '' && !is_array($value)) {
				$pre_value = '';
				preg_match('~\{if ' . $key . '\}(.*)\{\/if ' . $key . '\}~ims', $address_format_set, $if_contents);

				if (isset($if_contents[1]))
					$if_contents[1] = str_replace('{' . $key . '}', $value, $if_contents[1]);
				else 
					$if_contents[1] = '';

				$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~ims', $if_contents[1], $address_format_set);
				$address_format_set = str_ireplace('{' . $key . '}', $pre_value . $value, $address_format_set);
				$address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{/if ' . '}', '', $address_format_set);
			} else {
				$pre_value = '';
				$address_format_set = preg_replace('~\{if ' . $key . '\}(.*)\{/if ' . $key . '\}~i', '', $address_format_set);
				$address_format_set = str_replace('{' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{' . $key . '}', $pre_value . $value, $address_format_set);
				$address_format_set = str_ireplace('{/' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{/if ' . $key . '}', '', $address_format_set);
				$address_format_set = str_ireplace('{/if ' . '}', '', $address_format_set);
                //$address_format_set = str_ireplace(', ', '', $address_format_set);
			}

			$from_date = "{if telephone}";
			$end_date = "{telephone}";
			$from_date_pos = strpos($address_format_set, $from_date);
			if ($from_date_pos !== false) {
				$end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
				$date_length = $end_date_pos - $from_date_pos;
				$date_str = substr($address_format_set, $from_date_pos, $date_length);
				$address_format_set = str_replace($date_str, '', $address_format_set);
			}

			$from_date = "{if fax}";
			$end_date = "{fax}";
			$from_date_pos = strpos($address_format_set, $from_date);
			if ($from_date_pos !== false) {
				$end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
				$date_length = $end_date_pos - $from_date_pos;
				$date_str = substr($address_format_set, $from_date_pos, $date_length);
				$address_format_set = str_replace($date_str, '', $address_format_set);
			}

			$from_date = "{if vat_id}";
			$end_date = "{vat_id}";
			$from_date_pos = strpos($address_format_set, $from_date);
			if ($from_date_pos !== false) {
				$end_date_pos = strpos($address_format_set, $end_date) + strlen($end_date);
				$date_length = $end_date_pos - $from_date_pos;
				$date_str = substr($address_format_set, $from_date_pos, $date_length);
				$address_format_set = str_replace($date_str, '', $address_format_set);
			}
		}
		$address_format_set = trim(str_replace(array('||', '|'), "\n", trim($address_format_set)));
        $address_format_set = str_replace("\n\n", "\n", $address_format_set);
        $address_format_set = str_replace("  ", " ", $address_format_set);
        $address_format_set = trim(ltrim($address_format_set,','));
		return $address_format_set;
	}

	protected function getAddressLines($shippingAddressArray, $show_this_shipping_line) {
		$ship_i = 0;
		foreach ($shippingAddressArray as $key => $value) {
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

    protected function groupOptionProduct($options_splits) {
        $group_options = array();
        $group_options_temp = array();
        $group_options_f = array();
        foreach ($options_splits as $key => $options_split) {
            $temp_str1 = substr($options_split, strpos($options_split, 'qty_ordered'), strlen($options_split));
            $temp_str1 = str_replace('qty_ordered', '', $temp_str1);
            $temp_str2 = substr($options_split, 0, strpos($options_split, 'qty_ordered'));
            if(isset($group_options[$temp_str2]))
                $group_options[$temp_str2] = ($temp_str1 + $group_options[$temp_str2]) . ' x';
			else {
                $group_options[$temp_str2] = $temp_str1;
                $group_options_temp[] = $temp_str2;
            }
        }
        $group_options_f = $this->naturalSort($group_options, $group_options_temp);
        return $group_options_f;
    }

    protected function naturalSort($group_options, $group_options_temp) {
        $group_options_f = array();
        natcasesort($group_options_temp);
        foreach ($group_options_temp as $key => $value) {
            $group_options_f[$value] = $group_options[$value];
        }
        return $group_options_f;
    }

    // protected function capitalAddress($str)
    // {
    //     $str = strtoupper(strtr($str, LATIN1_LC_CHARS, LATIN1_UC_CHARS));
    //     return strtr($str, array("ß" => "SS"));
    // }
    
    public function convertCurrency($price, $from, $to) {
        if ($from == $to)
            return $price;

        $from = Mage::getModel('directory/currency')->load($from);
        $to = Mage::getModel('directory/currency')->load($to);

        if ($rate = $from->getRate($to))
            return $price*$rate;
        elseif ($rate = $to->getRate($from))
            return $price / $rate;
        else
            throw new Exception(Mage::helper('directory')->__('Undefined rate from "%s-%s".', $from->getCode(), $to->getCode()));
    }

    protected function getSkuBarcode($sku, $product_id, $store_id) {
        $barcode_array = array();
        $config_group = 'messages';
        $new_product_barcode = '';
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_1', '', false, $config_group, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_2', '', false, $config_group, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_3', '', false, $config_group, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_4', '', false, $config_group, $store_id);
        $product_sku_barcode_attributes[] = $this->_getConfig('product_sku_barcode_attribute_5', '', false, $config_group, $store_id);
        $product_sku_barcode_spacer = $this->_getConfig('product_sku_barcode_spacer', '', false, $config_group, $store_id);
        if ($product_sku_barcode_spacer != '')
            $barcode_array['spacer'] = $product_sku_barcode_spacer;
        else
            $barcode_array['spacer'] = '';
        foreach ($product_sku_barcode_attributes as $product_sku_barcode_attribute)
            $new_product_barcode = $this->getSkuBarcodeByAttribute($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $sku, $product_id);
        return $new_product_barcode;
    }
    
    protected function getSkuBarcodeByAttribute($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $sku, $product_id) {
        if ($product_sku_barcode_attribute != '') {
            switch ($product_sku_barcode_attribute) {
                case 'sku':
                    $barcode_array[$product_sku_barcode_attribute] = $sku;
                    break;
                case 'product_id':
                    $barcode_array[$product_sku_barcode_attribute] = $product_id;
                    break;
                default:
                    $attributeName = $product_sku_barcode_attribute;
                    $product = Mage::helper('pickpack')->getProduct($product_id);
                    if ($product->getData($attributeName))
                        $barcode_array[$product_sku_barcode_attribute] = Mage::helper('pickpack')->getProductAttributeValue($product, $attributeName);
                    else
                        $barcode_array[$product_sku_barcode_attribute] = '';
                    break;
            }
            $new_product_barcode = $new_product_barcode . $barcode_array[$product_sku_barcode_attribute] . $barcode_array['spacer'];
        }
        return $new_product_barcode;
    }

    public function getOrderProductAttributeTextByCode($order, $attributeCode) {
        try
        {
            $productAttributeValueArray = array();

            $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $attributeCode);
            if($attribute->usesSource()){
                $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
                $storeId = $order->getStore()->getId();
                foreach($itemsCollection as $item)
                {
                    if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                        $configurable_id = $item->getProductId();
                        $sku = $item->getProductOptionByCode('simple_sku');
                        $product_id = Mage::getModel('catalog/product')->setStoreId($storeId)->getIdBySku($sku);
                    }
                    else {
                        $sku = $item->getSku();
                        $product_id = $item->getProductId(); // get it's ID
                    }
                    $_product = Mage::getModel("catalog/product")->load($product_id);

                    if($attribute->getFrontendInput() == 'text') {
                        $productAttributeValueArray[] = $_product->getData($attributeCode);
                    }
                    else if($attribute->getFrontendInput() == 'multiselect') {
                        $multiSelectArray = $_product->getAttributeText($attributeCode);
                        $productAttributeValueArray[] = implode(', ',$multiSelectArray);
                    }
                    else{
                        $productAttributeValueArray[] = $_product->getAttributeText($attributeCode);
                    }

                }
            }

            return implode('|', $productAttributeValueArray);
        }
        catch(Exception $e)
        {
            return '';
        }
    }

    public function getOrderDescription($order,$description_code) {
		// Description:%description%;
		// ^ same as %description_products%
		// List product names, separated by |
		// Include each once only.
		// //
		// Description:%description_category%;
		// //^ same as %description_categories%
		// List product category names, separated by |
		// Include each once only.
		// //
		// Description:%description_qty%;
		// //^ same as %description_products_qty%
		// List product names, separated by |
		// Include each once only, with qty prefix *eg. 2 x White shirt
		// 
		// Description:%description_category_qty%;
		// ^ same as %description_categories_qty%
		// List product category names, separated by |
		// Include each once only, with qty prefix *eg. 2 x Shirt
		$description_detail = array();

		$itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
			$storeId = $order->getStore()->getId();
			foreach($itemsCollection as $item)
			{
				if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
					$configurable_id = $item->getProductId();
					$sku = $item->getProductOptionByCode('simple_sku');
					$product_id = Mage::getModel('catalog/product')->setStoreId($storeId)->getIdBySku($sku);
				} else {
					$sku = $item->getSku();
					$product_id = $item->getProductId(); // get it's ID

				}
				$product_name = $item->getName();
				$product_sku = $sku;
				$product_qty = round($item->getQtyOrdered(),2);
				if($description_code=='description')
					$description_detail[] = $product_name;
				else
					if($description_code=='description_qty')
						$description_detail[] = $product_qty.' x '.$product_name;
					else
						if($description_code=='description_categories')
						{
							$product = Mage::getModel('catalog/product')->setStoreId($storeId)->load($product_id);
							$product_categories = $this->getProductCategories($product);
							$description_detail[] =$product_categories;
						}

			}

		return implode('|',$description_detail);

    }

    public function getProductCategories($product) {
        $catCollection = $product->getCategoryCollection();
        $categs = $catCollection->exportToArray();
        $categsToLinks = array();
        foreach ($categs as $cat) {
            $categsToLinks [] = Mage::getModel('catalog/category')->load($cat['entity_id'])->getName();
        }
        $category_label = '';
        foreach ($categsToLinks as $ind => $cat) {
            if (isset($category_map[strtolower($cat)]))
				$cat = $category_map[strtolower($cat)];
           
		    if (!empty($category_label))
				$category_label = $category_label . ', ' . $cat;
            else $category_label = $cat;
        }
        return $category_label;
    }


    public function getProductWebsites($product) {
        $websiteIds = $product->getWebsiteIds();
        $website_name = "";
        foreach ($websiteIds as $key => $websiteId) {
            $website_name = $website_name . Mage::app()->getWebsite($websiteId)->getName() . ",";
        }
        $website_name = trim($website_name, ',');
        return $website_name;
    }

    public function getProductStores($product) {
        $storeIds = $product->getStoreIds();
        $store_name = '';
        foreach ($storeIds as $key => $storeId) {
            $store_name = $store_name . Mage::app()->getStore($storeId)->getName() . ',';
        }
        $store_name = trim($store_name, ',');
        return $store_name;
    }

    public function setGeneralCsvConfig($store_id = null){
        if ($store_id === null)
            $store_id = Mage::app()->getStore()->getStoreId();
        $this->_general['csv_field_separator'] = $this->_getConfig('csv_field_separator', ',', false, 'general_csv',$store_id);
        if ($this->_general['csv_field_separator'] == "custom")
            $this->_general['csv_field_separator'] = trim ($this->_getConfig('csv_field_separator_custom', ',', false, 'general_csv',$store_id));
        $this->_general['csv_quote_values_yn'] = $this->_getConfig('csv_quote_values_yn', 'double', false, 'general_csv',$store_id);
        $this->_general['csv_strip_linebreaks_yn'] = $this->_getConfig('csv_strip_linebreaks_yn', 1, false, 'general_csv', $store_id);
    }
	
    protected function getNameDefaultStore($item) {
        $product_id      = $item->getProductId();
        $default_storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        $_newProduct     = Mage::helper('pickpack')->getProductForStore($product_id, $default_storeId);
        $name            = trim($_newProduct->getName());
        return $name;
    }

    public function getAllSupplier($order, $supplier_attribute) {
        $is_warehouse_supplier = 0;
        if((Mage::helper('pickpack')->isInstalled('Innoexts_Warehouse')))
        {
            if($supplier_attribute == 'warehouse')
                $is_warehouse_supplier = 1;
        }
        $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
        $supplier_array = array();
        foreach ($itemsCollection as $item) {
            if($is_warehouse_supplier == 1) {
                $warehouse_title = $item->getWarehouseTitle();
                $warehouse = $item->getWarehouse();
                $warehouse_code = $warehouse->getData('code');
                $supplier = $warehouse_code;
                $warehouse_code = trim(strtoupper($supplier));
                $this->warehouse_title[$warehouse_code] = $warehouse_title;
            }
            else {
                $product = Mage::helper('pickpack/product')->getProductFromItem($item);
                $supplier = Mage::helper('pickpack')->getProductAttributeValue($product, $supplier_attribute);
            }
            if (is_array($supplier))
				$supplier = implode(',', $supplier);
            if (!$supplier) 
				$supplier = '~Not Set~';
            $supplier_array[] = trim(strtoupper($supplier));
        }
        return array_unique($supplier_array);
    }

    /**
     * Needs for CN22
     *
     * @param $item
     * @return mixed
     */
    protected function _getProductFromItem($item) {
        $helper = Mage::helper('pickpack');
        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && $item->getHasChildren()) {
            $children = $item->getChildrenItems();
            $child = $children[0];
            if ($child)
                $product = $helper->getProduct($child->getProductId());
            else
                $product = $helper->getProduct($item->getProductId());
        } else
            $product = $helper->getProduct($item->getProductId());

        return $product;
    }

    public function isInShippingZone($order){
        if (Mage::helper('pickpack')->isInstalled("Moogento_CourierRules")) {
            $store_id = $order->getStore()->getId();
            $shipping_zone_collection = Mage::getModel('moogento_courierrules/zone')->getCollection();
            $filter_shipping_zone_yn =  $this->_getConfig('filter_shipping_zone_yn','0', false,'custom_section',$store_id,true,'cn22_options');
            $filter_shipping_zone =  explode(',',$this->_getConfig('filter_shipping_zone','', false,'custom_section',$store_id,true,'cn22_options'));
            $shippingId = $order->getShippingAddress()->getId();
            $address = Mage::getModel('sales/order_address')->load($shippingId);
            $country_id = $address->getData('country_id');
            if($filter_shipping_zone_yn == 1){
                foreach($filter_shipping_zone as $k => $v){
                    foreach($shipping_zone_collection as $zone){
                        if($v == $zone->getData('id')){
                            foreach($zone->getData('countries') as $key => $value){
                                if($country_id == $value)
                                    return true;
                            }
                        }
                    }

                }
            }
        }
        return false;
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
}