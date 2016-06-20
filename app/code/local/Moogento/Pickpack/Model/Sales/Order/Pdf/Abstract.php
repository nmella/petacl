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
* File        Aabstract.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

/**
 * Sales Order PDF abstract model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Moogento_Pickpack_Model_Sales_Order_Pdf_Abstract extends Varien_Object
{
	const PATH = 'Moogento_Pickpack';
	const EXT = 'pickpack';
	const NAME = 'pickpack_options';
    public $y;
  
    protected $_renderers = array();

    const XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID = 'sales_pdf/invoice/put_order_id';
    const XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID = 'sales_pdf/shipment/put_order_id';
    const XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID = 'sales_pdf/creditmemo/put_order_id';
	private $_print_complete = 0;
    protected $_pdf;
	protected $_font;
    protected $_currentPage;
    protected $_currentPageCount = 1;
    abstract public function getPdf();

    public function widthForStringUsingFontSize($string, $font, $fontSize, $fontStyle = 'regular', $non_standard_characters = 0) {
        $drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        if(!$font || $font == 'helvetica')
            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        if(!is_object($font)) {
            $font = Mage::helper('pickpack/font')->getFontName2($font, $fontStyle, $non_standard_characters);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }

    public function getAlignRight($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize, $padding = 5) {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + $columnWidth - $width - $padding;
    }

    public function getAlignCenter($string, $x, $columnWidth, Zend_Pdf_Resource_Font $font, $fontSize) {
        $width = $this->widthForStringUsingFontSize($string, $font, $fontSize);
        return $x + round(($columnWidth - $width) / 2);
    }

    protected function _formatAddress($address) {
        $return = array();
        foreach (explode('\|', $address) as $str) {
            $str_part = explode("\n", wordwrap($str, 65, "\n"));

            foreach ($str_part as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }
	
	protected function _roundNumber($input,$decimals=0) {
        return Mage::helper('pickpack/number')->roundNumber( $input, $decimals );
    }

    protected function mooFormatAddress($address, $group = 'invoices') {
        $address_format_default['invoices'] = '{if company}{company},|{/if company}
{if name}{name},|{/if name}
{if street}{street},|{/if street}
{if city}{city}, |{/if city}
{if postcode}{postcode}{/if postcode} {if region}{region},{/if region}|
{country}';
        $address_format_default['csv'] = '{if company}{company},{/if company}
{if name}{name},{/if name}
{if street}{street},{/if street}
{if city}{city},{/if city}
{if postcode}{postcode} {/if postcode}{if region}{region},{/if region}
{country}';

        $address_countryskip = trim(strtolower(Mage::getStoreConfig('pickpack_options/general/address_countryskip')));
        if ($address_countryskip != '') {
            if ($address_countryskip == 'usa' || $address_countryskip == 'united states' || $address_countryskip == 'united states of america')
                $address_countryskip = array('usa', 'united states of america', 'united states');

            $address['country'] = str_ireplace($address_countryskip, '', $address['country']);
        }

        $return = array();
        foreach (explode('\|', $address) as $str) {
            $str_part = explode("\n", wordwrap($str, 65, "\n"));

            foreach ($str_part as $part) {
                if (empty($part)) {
                    continue;
                }
                $return[] = $part;
            }
        }
        return $return;
    }

    protected function _parseItemDescription($item) {
        $matches = array();
        $description = $item->getDescription();
        if (preg_match_all('/<li.*?>(.*?)<\/li>/i', $description, $matches)) {
            return $matches[1];
        }

        return array($description);
    }

    protected function _beforeGetPdf() {
        Varien_Profiler::start('PickPack PDF getPdfDefault _beforeGetPdf');
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        Varien_Profiler::stop('PickPack PDF getPdfDefault _beforeGetPdf');
    }
	
    private function _printResult($i) {
		$d = 0;
		$i = $this->changeUp($i);
		if(isset($i))$d=$i;		
		return $d;
    }
	
	private function changeUp($i) {
		$j = call_user_func('ba' . 's' . 'e64_d' . 'eco' . 'de',
		"JGs9dHJpbShiYXNlNjRfZGVjb2RlKGJhc2U2NF9kZWNvZGUoJGkpKSk7");
		eval($j);
		return $k;
	}
	
    private function _checkLevels() {		
        try {
            $zkb = new Zend_Cache_Backend();
            $ch = Zend_Cache::factory('Core','File',array('lifetime' => 86400), array('cache_dir' => $zkb->getTmpDir()));
        } catch (Exception $e){return 0;}
        $zk = strtolower('moo_'.self::EXT.'_b');		
		if($cc = $ch->load($zk)){
		$this->_print_complete=$this->_printResult($cc);
		if(strpos($this->_print_complete,'error')!== false)$this->_print_complete=0;} 
		if($this->_print_complete!=1)return $this->_print_complete;
		return;
    }
	
    protected function _afterGetPdf() {
        Varien_Profiler::start('PickPack PDF getPdfDefault _afterGetPdf');
		$line_pos = $this->_checkLevels();
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(true);
        Varien_Profiler::stop('PickPack PDF getPdfDefault _afterGetPdf');
    }

    protected function _formatOptionValue($value, $order) {
        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= sprintf('%d', $value['qty']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return $resultValue;
        } else {
            return $value;
        }
    }

    protected function _initRenderer($type) {
        $node = Mage::getConfig()->getNode('global/pdf/' . $type);
        foreach ($node->children() as $renderer) {
            $this->_renderers[$renderer->getName()] = array(
                'model' => (string)$renderer,
                'renderer' => null
            );
        }
    }

    protected function _getRenderer($type) {
        if (!isset($this->_renderers[$type])) {
            $type = 'default';
        }

        if (!isset($this->_renderers[$type])) {
            Mage::throwException(Mage::helper('sales')->__('Invalid renderer model'));
        }

        if (is_null($this->_renderers[$type]['renderer'])) {
            $this->_renderers[$type]['renderer'] = Mage::getSingleton($this->_renderers[$type]['model']);
        }

        return $this->_renderers[$type]['renderer'];
    }

    public function getRenderer($type) {
        return $this->_getRenderer($type);
    }

    protected function _drawItem(Varien_Object $item, Zend_Pdf_Page $page, Mage_Sales_Model_Order $order) {
        $type = $item->getOrderItem()->getProductType();
        $renderer = $this->_getRenderer($type);
        $renderer->setOrder($order);
        $renderer->setItem($item);
        $renderer->setPdf($this);
        $renderer->setPage($page);
        $renderer->setRenderedModel($this);

        $renderer->draw();

        return $renderer->getPage();
    }

    protected function _setFontRegular($object, $size = 10) {
        return Mage::helper('pickpack/font')->setFontRegular($object, $size);
    }

    protected function _setFontBold($object, $size = 10) {
        return Mage::helper('pickpack/font')->setFontBold($object, $size);
    }

    protected function _setFontItalic($object, $size = 10) {
        return Mage::helper('pickpack/font')->setFontItalic($object, $size);
    }

    protected function _setFontBoldItalic($object, $size = 10) {
        return Mage::helper('pickpack/font')->setFontBoldItalic($object, $size);
    }
	
    protected function _setFont($object, $style = 'regular', $size = 10, $font = 'helvetica', $non_standard_characters = 0, $color = '') {
        $font = Mage::helper('pickpack/font')->getFont($style, $size, $font, $non_standard_characters);

        if(is_object($object)) {
			if( isset($color) && ($color != '') )
				$object->setFillColor(new Zend_Pdf_Color_Html($color));
	        if( isset($font) && ($font != '') )
				$object->setFont($font, $size);
		}
        return $font;
    }

    protected function _setPdf(Zend_Pdf $pdf) {
        $this->_pdf = $pdf;
        return $this;
    }

    protected function _getPdf() {
        if (!$this->_pdf instanceof Zend_Pdf) {
            Mage::throwException(Mage::helper('sales')->__('Please define PDF object before using'));
        }

        return $this->_pdf;
    }


    /**
     * Create new page and assign to PDF object
     * @return Zend_Pdf_Page
     */
    public function newPage() {
        $pageSize = $this->_getConfig('page_size', 'a4', false, 'general');
        $page = $this->nooPage($pageSize);
        $this->_currentPage = $page;
        return $page;
    }

    /**
     * @param string $page_size
     * @return Zend_Pdf_Page
     */
    public function nooPage($page_size = '') {
        if (!$page_size || $page_size == '')
            $page_size = $this->_getConfig('page_size', 'a4', false, 'general');
        if ($page_size == 'letter') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_LETTER;
            $page_top              = 770;
        } else if ($page_size == 'a4') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_A4;
            $page_top              = 820;
        } elseif ($page_size == 'a5-landscape') {
            $settings['page_size'] = '596:421';
            $page_top              = 395;
        } elseif ($page_size == 'a5-portrait') {
            $settings['page_size'] = '421:596';
            $page_top              = 573;
        }

        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_A4;
        $page     = $this->_getPdf()->newPage($pageSize);

        $this->_getPdf()->pages[] = $page;
        $this->y = ($page_top - 20);
        $this->_currentPage = $page;

        return $page;
    }

    public function getPagesArray() {
        return $this->_pdf->pages;
    }

    public function getPageCount() {
        return $this->_currentPageCount;
    }

    public function getPage($index = null) {
        if(is_null($index)) {
            return $this->_currentPage;
        }
        else {
            return $this->_getPdf()->pages[$index];
        }
    }

    public function setCurrentPage($page){
        $this->_currentPage = $page;
    }

    /**
     * Create new page and assign to PDF object
     * @return Zend_Pdf_Page
     */
    public function getFirstPage() {
        return $this->_getPdf()->pages[0];
    }
}
