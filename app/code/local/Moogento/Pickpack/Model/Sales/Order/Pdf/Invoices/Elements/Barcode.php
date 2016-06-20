<?php
/**
 * 
 * Date: 29.11.15
 * Time: 13:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Barcode extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();
	const BARCODE_NUDGE_X = 0;
	const BARCODE_NUDGE_Y = 25;
	const BOTTOM_BARCODE_NUDGE_X = 0;
	const BOTTOM_BARCODE_NUDGE_Y = 0;

    public function showTopBarcode($y) {
        $order = $this->getOrder();
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPdf()->getCurrentPageConfig();
        $page = $this->getPage();
        $wonder = $this->getWonder();
        $generalConfig = $this->getGeneralConfig();

        //$generalConfig['barcode_type'] = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $storeId);
        $bottom_barcode_nudge = explode(",", $this->_getConfig('bottom_barcode_nudge', '0,0', true, $wonder, $storeId));
		$bottom_barcode_nudge[0] += self::BOTTOM_BARCODE_NUDGE_X;
		$bottom_barcode_nudge[1] += self::BOTTOM_BARCODE_NUDGE_Y;
        $barcode_nudge = explode(",", $this->_getConfig('barcode_nudge', '0,0', true, $wonder, $storeId));
		$barcode_nudge[0] += self::BARCODE_NUDGE_X;
		$barcode_nudge[1] += self::BARCODE_NUDGE_Y;
		
        $black_color = Mage::helper('pickpack/config_color')->getPdfColor('black_color');

        $showBarCode = $this->_getConfig('pickpack_packbarcode', 0, false, $wonder, $storeId);
        if ($showBarCode) {
            $config_values['barcode_type'] = $generalConfig['barcode_type'];
            $config_values['font_family_barcode'] = $generalConfig['font_family_barcode'];
            $config_values['barcode_nudge'] = $barcode_nudge;
            $config_values['black_color'] = $black_color;
            $config_values['padded_right'] = $pageConfig['padded_right'];
            $config_values['font_size_body'] = $generalConfig['font_size_body'];
            $barcode_text = '';
            if($showBarCode == 1)
                $barcode_text = $order->getRealOrderId();
            elseif($showBarCode == 2) {
                    if ($order->hasInvoices()) {
                        $invIncrementIDs = array();
                        foreach ($order->getInvoiceCollection() as $inv) {
                            $invIncrementIDs[] = $inv->getIncrementId();
                        }
                        $barcode_text = implode(',',$invIncrementIDs);
                    }
                }
                elseif($showBarCode == 3)
                        $barcode_text  = $this->getMarketPlaceId($order);
	            if($barcode_text != '')
	                $this->_showTopBarcode($page,$barcode_text,$config_values,$y, $pageConfig['padded_right']);
	            $page->setFillColor($black_color);
	            Mage::helper('pickpack/font')->setFontRegular($page, $generalConfig['font_size_body']);
        }
    }

    private function _showTopBarcode($page, $order_id, $config_values, $y, $padded_right) {
        $generalConfig = $this->getGeneralConfig();
		
        $barcode_font_size = $generalConfig['font_size_barcode_order']; //14
        $barcode_fontsize_shiftleft = 0;
        if(isset($config_values['black_color']))
            $black_color = $config_values['black_color'];
        if(isset($config_values['barcode_nudge']))
            $barcode_nudge = $config_values['barcode_nudge'];

        if(isset($config_values['show_top_logo_yn']))
            $show_top_logo_yn = $config_values['show_top_logo_yn'];

        if ($generalConfig['barcode_type'] !== 'code128') {
            $barcode_font_size += 12;
            $barcode_fontsize_shiftleft += 75;
        }
        $long_barcode_shiftup = 0;
        $barcodeString_pre = $order_id;
        $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($barcodeString_pre, $generalConfig['barcode_type']);
        $barcode_width_multiplier = 1.35;
        if (strlen($barcodeString_pre) > 11) {
            if ($generalConfig['barcode_type'] !== 'code128')
				$barcode_fontsize_shiftleft += 32;
            $barcode_width_multiplier = 1.19;
            $long_barcode_shiftup = 20;
            $barcode_fontsize_shiftleft += (((16 - ($barcode_font_size)) * 11) * 1);
        } else
            $barcode_fontsize_shiftleft += ((16 - $barcode_font_size) * 7);

        $barcodeWidth = (($barcode_width_multiplier * Mage::helper('pickpack/font')->parseString($order_id, Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size)) - 13.5 + $barcode_fontsize_shiftleft);

        $page->setFillColor($black_color);
        Mage::helper('pickpack/font')->setFontBold($page, 10);
		
        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $generalConfig['font_family_barcode']), $barcode_font_size);
		
        // pull the barcode out from under the titlebar
		if(isset($show_top_logo_yn) && ($show_top_logo_yn == 0))
            $barcode_nudge[1] -= 30;
        $page->drawText($barcodeString, ($padded_right - $barcodeWidth + $barcode_nudge[0]), ($y - 9 + $long_barcode_shiftup + $barcode_nudge[1]), 'CP1252');
    }

    protected function getSkuBarcodeByAttribute2($product_sku_barcode_attribute, $barcode_array, $new_product_barcode, $product_build_value, $shelving_real_attribute, $shelving_attribute, $shelving_2_attribute,$bundle_children = false,$product_id = null) {

        if ($product_sku_barcode_attribute != '') {
            if($bundle_children == true && $product_id != null) {
                $attributeName = $product_sku_barcode_attribute;
                $product = Mage::helper('pickpack')->getProduct($product_id);
                if ($product->getData($attributeName))
                    $barcode_array[$product_sku_barcode_attribute] = Mage::helper('pickpack')->getProductAttributeValue($product, $attributeName);
				else
                    $barcode_array[$product_sku_barcode_attribute] = '';
            } else {
                switch ($product_sku_barcode_attribute) {
                    case 'sku':
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['sku_print'];
                        break;
                    case 'name':
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['display_name'];
                        break;
                    case $shelving_real_attribute:
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['shelving_real'];
                        break;
                    case $shelving_attribute:
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['shelving'];
                        break;
                    case $shelving_2_attribute:
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['shelving2'];
                        break;
                    case 'category':
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['%category%'];
                        break;
                    case 'product_id':
                        $barcode_array[$product_sku_barcode_attribute] = $product_build_value['product_id'];
                        break;
                    default:
                        $attributeName = $product_sku_barcode_attribute;
                        $product_id = $product_build_value['product_id'];
                        $product = Mage::helper('pickpack')->getProduct($product_id);
                        if ($product->getData($attributeName))
                            $barcode_array[$product_sku_barcode_attribute] = Mage::helper('pickpack')->getProductAttributeValue($product, $attributeName);
                        else
                            $barcode_array[$product_sku_barcode_attribute] = '';
                        break;
                }
            }
            if($barcode_array[$product_sku_barcode_attribute])
                $new_product_barcode = $new_product_barcode . $barcode_array[$product_sku_barcode_attribute] . $barcode_array['spacer']. ' ';
        }

        $new_product_barcode = trim($new_product_barcode);
		
		return $new_product_barcode;
    }
}