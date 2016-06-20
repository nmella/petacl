<?php
/**
 * 
 * Date: 01.12.15
 * Time: 16:09
 */

class Moogento_Pickpack_Helper_Product extends Mage_Core_Helper_Abstract
{
    protected $_currencyMap = array(
        'from' => array(
            '₦',
            '₵',
			'₹',
        ),
        'to' => array(
            'N',
            '¢',
	        'R',
        ),
    );
	
	
    public function getGeneralConfig() {
        return Mage::helper('pickpack/config')->getGeneralConfigArray($this->getStoreId());
    }
			
    public function getOptionProductByStore($store_view, $helper, $product_id, $storeId, $specific_store_id, $options, $i) {
        $config    = Mage::getModel('eav/config');
        $options_store = array();
        if ($store_view == "storeview") {
            $_newProduct = $helper->getProductForStore($product_id, $storeId, $specific_store_id);
            if (isset($options['options'][$i]) && isset($options['options'][$i]['option_id'])){
                $_newOption = $_newProduct->getOptionById($options['options'][$i]['option_id']);
            }
            if(isset($_newOption) && is_object($_newOption)){
                $options['options'][$i]['label']  = $_newOption->getTitle();
                /*if($options['options'][$i]['option_type'] != "field" && $options['options'][$i]['option_type'] != "area")
                    $options['options'][$i]['value'] = $options['options'][$i]['option_value'];*/
            }else{
                $attribute = $config->getAttribute(Mage_Catalog_Model_Product::ENTITY, $options['options'][$i]['label']);

                if($attribute->getStoreLabels()){
                    $label_ar = $attribute->getStoreLabels();
                    $options['options'][$i]['label'] = $label_ar[$storeId];
                }
                else{
                    $label_ar = $attribute->getData('attribute_code');
                    $options['options'][$i]['label'] = $label_ar;
                }

                $option_id = $attribute->getSource()->getOptionId($options['options'][$i]['value']);
                $value_ar = $attribute->setStoreId($storeId)->getSource()->getAllOptions();

                foreach ($value_ar as $key => $value) {
                    if($value["value"] == $option_id && $option_id != "")
                        $options['options'][$i]['value'] = $value["label"];
                }
            }

        }
        if($store_view == "specificstore" && $specific_store_id != "") {
            $_newProduct = $helper->getProductForStore($product_id, $specific_store_id);
            if (isset($options['options'][$i]) && isset($options['options'][$i]['option_id'])) {
                $_newOption = $_newProduct->getOptionById($options['options'][$i]['option_id']);
            }
            if(isset($_newOption) && is_object($_newOption)){
                $options['options'][$i]['label']  = $_newOption->getTitle();
                if($options['options'][$i]['option_type'] != "field" && $options['options'][$i]['option_type'] != "area")
                    $options['options'][$i]['value']  = $_newOption->getValueById($options['options'][$i]['option_value'])->getTitle();
            }else{

                $attribute = $config->getAttribute(Mage_Catalog_Model_Product::ENTITY, $options['options'][$i]['label']);

                if($attribute->getStoreLabels()){
                    $label_ar = $attribute->getStoreLabels();
                    $options['options'][$i]['label'] = $label_ar[$specific_store_id];
                }
                else{
                    $label_ar = $attribute->getData('attribute_code');
                    $options['options'][$i]['label'] = $label_ar;
                }

                $option_id = $attribute->getSource()->getOptionId($options['options'][$i]['value']);
                $value_ar = $attribute->setStoreId($specific_store_id)->getSource()->getAllOptions();

                foreach ($value_ar as $key => $value) {
                    if($value["value"] == $option_id && $option_id != "")
                        $options['options'][$i]['value'] = $value["label"];
                }
            }
        }

        $options_store["label"] = $options['options'][$i]['label'];
        $options_store["value"] = $options['options'][$i]['value'];
        return $options_store;
    }

    public function getProductFromItem($item) {
        $helper = Mage::helper('pickpack');
        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && $item->getHasChildren()) {
            $children = $item->getChildrenItems();
            $child = $children[0];
            if ($child) {
                $product = $helper->getProduct($child->getProductId());
            } else {
                $product = $helper->getProduct($item->getProductId());
            }
        } else {
            $product = $helper->getProduct($item->getProductId());
        }

        return $product;
    }

    private function fixProblemCurrencyCharacters($str,$order) {
		// Depending on font chosen, change characters
		// Should switch characters if:
		// callout_special_font, if this is 1 then we use noto, and no need to switch
		// callout_special_font, if this is 0 then we use opensans, and need to switch
		// non_standard_characters == 1
		// or
		// font != noto
		// font != {to be added}
		$generalConfig = $order->getGeneralConfig();
		if($generalConfig['font_family_body'] == 'noto')
			return $str;
		else return str_replace($this->_currencyMap['from'], $this->_currencyMap['to'], $str);
    }
	
    public function formatPriceTxt($order, $price) {
        if (!is_numeric($price)) {
            $price = Mage::app()->getLocale()->getNumber($price);
        }
        $price = $order->formatPriceTxt($price);
        $price = $this->fixProblemCurrencyCharacters($price,$order);

        return $price;
    }

    public function formatPrice($order, $price, $currency=null,$isRtl=true) {
        return $price;
        if (is_null($price)) {
            return '';
        }
        $price = sprintf("%F", $price);
        if ($isRtl) {
            if ($currency == 'base') {
                $price = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                    ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            } else {
                $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                    ->toCurrency($price, array('position' => Zend_Currency::LEFT));
            }
        } else {
            if ($currency == 'base') {
                $price = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())
                    ->toCurrency($price, array());
            } else {
                $price = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())
                    ->toCurrency($price, array());
            }
        }
        return $price;
    }
}
