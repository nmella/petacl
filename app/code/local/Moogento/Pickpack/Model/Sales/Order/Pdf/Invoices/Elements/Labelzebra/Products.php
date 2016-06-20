<?php

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Labelzebra_Products extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    protected $generalConfig = 0;
    protected $zebralabelConfig = 0;

    protected $product_line_array_xpos = array();

    public function __construct($arguments) {
        parent::__construct($arguments);
        $this->generalConfig = $arguments[2];
        $this->zebralabelConfig = $arguments[3];
    }

    public function caculateProductElementArrayXpos(){
        if ($this->zebralabelConfig['show_product_qty']){
            $product_line_array_xpos['qty_xpos'] = $this->zebralabelConfig['product_qty_xpos'];
        }

        if ($this->zebralabelConfig['show_product_name']){
            $product_line_array_xpos['name_xpos'] = $this->zebralabelConfig['product_name_xpos'];
        }

        if ($this->zebralabelConfig['show_product_sku']){
            $product_line_array_xpos['sku_xpos'] = $this->zebralabelConfig['product_sku_xpos'];
        }

        if ($this->zebralabelConfig['show_product_price']){
            $product_line_array_xpos['price_xpos'] = $this->zebralabelConfig['product_price_xpos'];
        }

        if ($this->zebralabelConfig['show_product_barcode']){
            $product_line_array_xpos['barcode_xpos'] = $this->zebralabelConfig['product_barcode_xpos'];
        }

        asort($product_line_array_xpos);
        return $product_line_array_xpos;
    }

    public function showProductQty(){

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
    }

    public function showProductName(){

    }

    public function showProductSku(){

    }

    public function showProductCustomAttribute(){

    }

    public function showProductSkuBarcode(){
        if ($this->zebralabelConfig['show_product_barcode']){
            $page = $this->getPage();
            $storeId = $this->getStoreId();
            $order = $this->getOrder();



            $barcode_type = $this->_getConfig('font_family_barcode', 'code128', false, 'general', $storeId);
            $font_family_barcode = Mage::helper('pickpack/barcode')->getFontForType($barcode_type);

            $after_print_barcode_y = $this->y;
            if ($this->zebralabelConfig['product_barcode_in_separate_line']){
                $padded_right = $this->page_padding['right'];
                $sku_barcodeY = $this->y - 1.2 * $this->generalConfig['font_size_body'];
                $after_print_barcode_y = $sku_barcodeY;
            }else{
                $padded_right = getPrevNext2($this->product_line_array_xpos, 'barcode_xpos', 'next',$this->page_padding['right']);
                $sku_barcodeY = $this->y;
                $after_print_barcode_y = $this->y - 0.2 * $this->generalConfig['font_size_body'];
            }

            $barcode = $this->order_item->getSku();

            $white_color = Mage::helper('pickpack/config_color')->getPdfColor('white_color');

            $this->printProductBarcode($page,$barcode,$barcode_type,$this->zebralabelConfig['show_product_barcode'],$this->zebralabelConfig['product_barcode_xpos'],$sku_barcodeY,$padded_right,$font_family_barcode,$this->generalConfig['font_size_barcode_product'],$white_color);
            $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->fontColorBodyItem);

            $this->y = $after_print_barcode_y;
        }
    }

    public function printProductBarcode($page,$barcode,$barcode_type,$product_sku_barcode_yn,$sku_barcodeX,$sku_barcodeY,$padded_right,$font_family_barcode,$barcode_font_size,$white_color) {
        $generalConfig = $this->getGeneralConfig();
        $nextCollumnX = getPrevNext2($this->product_line_array_xpos, 'sku_barcodeX', 'next');

        $after_print_barcode_y = ($sku_barcodeY - 2 - ($barcode_font_size * 2));
        $barcodeString = Mage::helper('pickpack/barcode')->convertToBarcodeString($barcode, $barcode_type);
        $barcodeWidth = $this->parseString($barcode, Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);

        $white_color = Mage::helper('pickpack/config_color')->getPdfColor('white_color');

        $page = $this->getPdf()->getPage();
        $pdf = $this->getPdf();

        // Print a white rectangle to make a usable barcode on pages with full image background
        $page->setFillColor($white_color);
        $page->setLineColor($white_color);
        $page->drawRectangle(($sku_barcodeX - 5), ($sku_barcodeY - 2), ($sku_barcodeX + $barcodeWidth + 5), ($sku_barcodeY - 2 - ($barcode_font_size * 1.6)));

        $page->setFillColor(new Zend_Pdf_Color_Rgb(0, 0, 0));
        $page->setFont(Zend_Pdf_Font::fontWithPath($this->action_path . $font_family_barcode), $barcode_font_size);

        if (($sku_barcodeX + $barcodeWidth) > $padded_right){
            $this->_setFont($page, $generalConfig['font_style_body'], $generalConfig['font_size_body'], $generalConfig['font_family_body'], $generalConfig['non_standard_characters'], '#FF3333');
            $page->drawText("!! TRIMMED BARCODE !!", ($sku_barcodeX), ($sku_barcodeY));
        }
        else if ( ($sku_barcodeX + $barcodeWidth) >= $nextCollumnX)
            $page->drawText($barcodeString, ($sku_barcodeX), ($sku_barcodeY - (1.3*$barcode_font_size)), 'CP1252');
        else
            $page->drawText($barcodeString, ($sku_barcodeX), ($sku_barcodeY), 'CP1252');

        return $after_print_barcode_y;
    }
}