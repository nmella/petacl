<?php
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Products_Price extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Products
{
    protected $product_price = array();
    public $sub_total_data = array();

    public function showProductPrice($product,$order_item){
        $order = $this->getOrder();
        $this->getProductPrice($product,$order_item);
        //later we will have option to hide product price if product is gift
        $is_gift = $this->checkIsGiftProdduct($product,$order_item);
        // we should have one function in other class that scan all item in order to check if this is a gift order or not,
        // that function will run at start of pdf processing to check if we even need to add price to titlebar
        $this->printValueLine($this->product_price['print_price'],$this->packingsheetConfig['product_line_prices_title_xpos'],$this->y);
    }

    private function getProductPrice($product,$order_item){
        $qty = floatval($order_item->getData('qty_ordered'));
        $tax_amount = floatval($order_item->getData('tax_amount')) / $qty;
        $price_calculated = $order_item->getData('price');
        $original_price = $order_item->getData('original_price');
        if ($price_calculated < $original_price)
            $this->product_price['original_price'] = $original_price - $tax_amount;
        else
            $this->product_price['original_price'] = $original_price;

        $this->product_price['print_price'] = null;
		//get value from magento order_item
        if ($this->packingsheetConfig['product_line_prices_yn'] == 1)
            $this->product_price['print_price'] = $order_item->getPrice();
        elseif ($this->packingsheetConfig['product_line_prices_yn'] == 2){
            $qty = floatval($order_item->getData('qty_ordered'));
            $tax_amount = 0;
            $discount_amount = 0;

			//get value tax
            if ($this->packingsheetConfig['product_line_prices_with_tax_yn'] == 1)
                $tax_amount = floatval($order_item->getData('tax_amount')) / $qty;

            //get value discount
            if ($this->packingsheetConfig['product_line_prices_with_discount_yn'] == 1)
                $discount_amount = floatval($order_item->getData('discount_amount')) / $qty;

            $this->product_price['print_price'] = $this->product_price['original_price'] - $discount_amount + $tax_amount;
        }
    }

    private function checkIsGiftProdduct($product,$order_item){
        return false;
    }

    public function showProductDiscount($product,$order_item){
        $this->getProductDiscount($product,$order_item);
        $discount_included = false;
        if ($this->packingsheetConfig['product_line_prices_with_discount_yn'] == 1)
			$discount_included = true;
        $this->printValueLine($this->product_price['print_discount'],$this->packingsheetConfig['product_line_discount_title_xpos'],$this->y,$discount_included);
    }

    private function getProductDiscount($product,$order_item){
        $qty = floatval($order_item->getData('qty_ordered'));
        $this->product_price['total_discount'] = -floatval($order_item->getData('discount_amount'));
        $this->product_price['discount'] = $this->product_price['total_discount'] / $qty;
        $this->product_price['print_discount'] = null;
        if ($this->packingsheetConfig['product_line_discount_yn'] == 1){
            //get value from magento order_item
            $value = $this->product_price['discount'];
            if ($this->packingsheetConfig['hide_zero_discount_value'] == 0 || $value != 0)
                $this->product_price['print_discount'] = $value;
        }elseif ($this->packingsheetConfig['product_line_discount_yn'] == 2){
            //get tax from calculated value
            $value = $this->product_price['discount'];
            if ($this->packingsheetConfig['hide_zero_discount_value'] == 0 || $value != 0)
                $this->product_price['print_discount'] = $value;
        }
    }

    public function showProductTax($product,$order_item){
        $this->getProductTax($product,$order_item);
        $tax_included = false;
        if ($this->packingsheetConfig['product_line_prices_with_tax_yn'] == 1)
			$tax_included = true;
        $this->printValueLine($this->product_price['print_tax'],$this->packingsheetConfig['product_line_tax_title_xpos'],$this->y,$tax_included);
    }

    private function getProductTax($product,$order_item){
        $qty = floatval($order_item->getData('qty_ordered'));
        $this->product_price['total_tax'] = floatval($order_item->getData('tax_amount'));
        $this->product_price['tax'] = $this->product_price['total_tax'] / $qty;
        $this->product_price['print_tax'] = null;
        if ($this->packingsheetConfig['product_line_tax_yn'] == 1){
            //get value from magento order_item
            $value = $this->product_price['tax'];
            if ($this->packingsheetConfig['hide_zero_tax_value'] == 0 || $value != 0)
                $this->product_price['print_tax'] = $value;
        } elseif ($this->packingsheetConfig['product_line_tax_yn'] == 2){
            //get tax from calculated value
            $value = $this->product_price['tax'];
            if ($this->packingsheetConfig['hide_zero_tax_value'] == 0 || $value != 0)
                $this->product_price['print_tax'] = $value;
        }
    }

    public function showProductTotal($product,$order_item){
        $this->getProductTotal($product,$order_item);
        $this->printValueLine($this->product_price['total_print'],$this->packingsheetConfig['product_line_total_title_xpos'],$this->y);
    }

    private function getProductTotal($product,$order_item){
        $qty = floatval($order_item->getData('qty_ordered'));
        //this function will get total from magento_order_item or will caculate from $this->product_price
        $this->product_price['total_original_price'] = $this->product_price['original_price'] * $qty;
        $this->product_price['total_print'] = null;
	
		//get value from magento order_item
        if ($this->packingsheetConfig['product_line_total_yn'] == 1)
            $this->product_price['total_print'] = $order_item->getData('row_total');
        elseif ($this->packingsheetConfig['product_line_total_yn'] == 2){
            $tax_amount = 0;
            $discount_amount = 0;

			//get value total tax
            if ($this->packingsheetConfig['product_line_total_with_tax_yn'] == 1)
                $tax_amount = $this->product_price['total_tax'];

            //get value total discount
            if ($this->packingsheetConfig['product_line_total_with_discount_yn'] == 1)
                $discount_amount = $this->product_price['total_discount'];

            $this->product_price['total_print'] = $this->product_price['total_original_price'] + $discount_amount + $tax_amount;
        }
    }

    private function printValueLine($print_value,$x,$y,$was_include = false){
        
	    if (!is_null($print_value)){
	        $page = $this->getPage();
	        $order = $this->getOrder();
			
			$print_value_display = Mage::getModel('directory/currency')->setData('currency_code', Mage::app()->getStore(null)->getCurrentCurrency()->getCode())
					->format($print_value, array('display' =>Zend_Currency::NO_SYMBOL), false);
            
			$print_symbol = '';
			if(($this->packingsheetConfig['currency_codes_or_symbols'] == 'codes') 
			|| ($this->packingsheetConfig['currency_codes_or_symbols'] == 'symbols') 
			|| ($this->packingsheetConfig['currency_codes_or_symbols'] == 'both')) {
				$order_currency_code = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getShortname();
				
				if($this->packingsheetConfig['currency_codes_or_symbols'] == 'codes')
					$print_symbol = $order_currency_code;
				elseif($this->packingsheetConfig['currency_codes_or_symbols'] == 'symbols')
					$print_symbol = Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol();
				elseif($this->packingsheetConfig['currency_codes_or_symbols'] == 'both')
					$print_symbol = $order_currency_code.Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol();
      
		  		switch ($this->packingsheetConfig['currency_symbol_position']) {
					case 'right':
						$print_value_display = $print_value_display.$print_symbol;
						break;

					case 'auto':
						switch ($order_currency_code) {

							case 'EUR':
								$print_value_display = $print_value_display.$print_symbol;
								break;
				
							case 'USD':
							case 'CAD':
							case 'GBP':		
							default:
								$print_value_display = $print_symbol.$print_value_display;
								break;
						}
						break;
						
					case 'left':
					case 'magento':
					default:
						$print_value_display = $print_symbol.$print_value_display;
						break;
				}
			}
			
	  	  	// may fix locale specific currency placement
			if($this->packingsheetConfig['currency_codes_or_symbols'] == 'magento')
				$print_value_display = Mage::helper('pickpack/product')->formatPriceTxt($order, $print_value);
			
		    if ($was_include)
                $print_value_display = '('.$print_value_display.')';
			
			$this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->fontColorBodyItem);
            $this->_drawText($print_value_display, $x, $y);
        }
    }

    public function calucateSubtotalData(&$subtotal_data){
        if (isset($subtotal_data['subtotal_original_price']))
			$subtotal_data['subtotal_original_price'] += floatval($this->product_price['total_original_price']);
        else
			$subtotal_data['subtotal_original_price'] = floatval($this->product_price['total_original_price']);
      
	    if (isset($subtotal_data['subtotal_tax']))
			$subtotal_data['subtotal_tax'] += floatval($this->product_price['total_tax']);
        else
			$subtotal_data['subtotal_tax'] = floatval($this->product_price['total_tax']);
       
	    if (isset($subtotal_data['subtotal_discount']))
			$subtotal_data['subtotal_discount'] += floatval($this->product_price['total_discount']);
        else
			$subtotal_data['subtotal_discount'] = floatval($this->product_price['total_discount']);
    }
}