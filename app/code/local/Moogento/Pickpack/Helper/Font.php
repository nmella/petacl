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
* File        Data.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Helper_Font extends Mage_Core_Helper_Abstract
{
    protected $_font;
    protected $_fontSize;
    protected $font_path;
    protected $font_addon_path;
    protected $custom_path;
    protected $general_path;

    public function __construct() {
        $this->font_path = Mage::helper('pickpack')->getFontPath();
        $this->font_addon_path = Mage::helper('pickpack')->getFontAddonPath();
        $this->custom_path = Mage::helper('pickpack')->getFontCustomPath();
        $this->general_path = Mage::helper('pickpack')->getFontGeneralPath();
    }

    public function setFontRegular($object, $size = 10) {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $object->setFont($font, $size);
        return $font;
    }

    public function setFontBold($object, $size = 10) {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
        $object->setFont($font, $size);
        return $font;
    }

    public function setFontItalic($object, $size = 10) {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }

    public function setFontBoldItalic($object, $size = 10) {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
        $object->setFont($font, $size);
        return $font;
    }

    public function getFont($style = 'regular', $size = 10, $font = 'helvetica', $non_standard_characters = 0) {
		$font_file_path = '';
		$non_standard_characters = 0; // forcing this after new font system
        switch ($font) {
           
		    case 'hebrew':
                $font_file_path = $this->font_addon_path . 'SILEOTSR.ttf';
                break;
				
				//skin/adminhtml/default/default/moogento/pickpack/fonts/
				//OpenSans-Light-webfont.ttf
				//OpenSans-Regular-webfont.ttf
				//OpenSans-Italic-webfont.ttf
				//OpenSans-Bold-webfont.ttf
				//OpenSans-BoldItalic-webfont.ttf
				//OpenSans-ExtraBoldItalic-webfont.ttf
				
	        case 'opensans':
	            switch ($style) {
	                case 'light' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-Light-webfont.ttf';
	                    break;
					case 'regular' :
	                    $font_file_path = $this->general_path . 'OpenSans-Regular-webfont.ttf';
	                    break;
	                case 'semibold' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-Semibold-webfont.ttf';
	                    break;
					case 'bold' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-Bold-webfont.ttf';
	                    break;
	                case 'italic' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-Italic-webfont.ttf';
	                    break;						
	                case 'semibolditalic' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-SemiboldItalic-webfont.ttf';
	                    break;
	                case 'bolditalic' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-BoldItalic-webfont.ttf';
	                    break;
	                default:
	                    $font_file_path = $this->general_path . 'OpenSans-Regular-webfont.ttf';
	                    break;
	            }
	            break;

		        case 'noto':
		            switch ($style) {
		                case 'light' :
						case 'regular' :
		                    $font_file_path = $this->font_path . 'noto/NotoSans-Regular.ttf';
		                    break;
		                case 'semibold' :
						case 'bold' :
		                    $font_file_path = $this->font_path . 'noto/NotoSans-Bold.ttf';
		                    break;
		                case 'italic' :
		                    $font_file_path = $this->font_path . 'noto/NotoSans-Italic.ttf';
		                    break;						
		                case 'semibolditalic' :
		                case 'bolditalic' :
		                    $font_file_path = $this->font_path . 'noto/NotoSans-BoldItalic.ttf';
		                    break;
		                default:
		                    $font_file_path = $this->font_path . 'noto/NotoSans-Regular.ttf';
		                    break;
	            }
	            break;
				
		        case 'droid':
		            switch ($style) {
		                case 'light' :
						case 'regular' :
		                    $font_file_path = $this->font_path . 'droid/DroidSerif-Regular.ttf';
		                    break;
		                case 'semibold' :
						case 'bold' :
		                    $font_file_path = $this->font_path . 'droid/DroidSerif-Bold.ttf';
		                    break;
		                case 'italic' :
		                    $font_file_path = $this->font_path . 'droid/DroidSerif-Italic.ttf';
		                    break;						
		                case 'semibolditalic' :
		                case 'bolditalic' :
		                    $font_file_path = $this->font_path . 'droid/DroidSerif-BoldItalic.ttf';
		                    break;
		                default:
		                    $font_file_path = $this->font_path . 'droid/DroidSerif-Regular.ttf';
		                    break;
	            }
	            break;
				
		        case 'handwriting':
		            switch ($style) {
		                case 'light' :
						case 'regular' :
		                case 'italic' :
		                case 'bolditalic' :
		                case 'semibolditalic' :
							$font_file_path = $this->font_path . 'daniel/daniel.ttf';
		                    break;
		                case 'semibold' :
						case 'bold' :
		                    $font_file_path = $this->font_path . 'daniel/daniel-Bold.ttf';
		                    break;
		                default:
							$font_file_path = $this->font_path . 'daniel/daniel.ttf';
							break;
		            }
		            break;
											
	            case 'helvetica':
	                switch ($style) {
	                    case 'light':						
	                    case 'regular' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	                        else 
								$font_file_path = $this->font_addon_path . 'arial.ttf';
	                        break;
	                    case 'italic' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
	                        else 
								$font_file_path = $this->font_addon_path . 'ariali.ttf';
	                        break;
						case 'semibold':
	                    case 'bold' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
	                        else 
								$font_file_path = $this->font_addon_path . 'arialbd.ttf';
	                        break;
		                case 'semibolditalic' :
	                    case 'bolditalic' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
	                        else 
								$font_file_path = $this->font_addon_path . 'arialbi.ttf';
	                        break;
	                    default:
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	                        else 
								$font_file_path = $this->font_addon_path . 'arial.ttf';
	                        break;
	                }
	                break;

	            case 'courier':
	                switch ($style) {
	                    case 'light':						
	                    case 'regular' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
	                        else 
								$font_file_path = $this->font_addon_path . 'cour.ttf';
	                        break;
	                    case 'italic' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_ITALIC);
	                        else 
								$font_file_path = $this->font_addon_path . 'couri.ttf';
	                        break;
						case 'semibold':
	                    case 'bold' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD);
	                        else 
								$font_file_path = $this->font_addon_path . 'courbd.ttf';
	                        break;
		                case 'semibolditalic' :
	                    case 'bolditalic' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD_ITALIC);
	                        else 
								$font_file_path = $this->font_addon_path . 'courbi.ttf';
	                        break;
	                    default:
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
	                        else 
								$font_file_path = $this->font_addon_path . 'cour.ttf';
	                        break;
	                }
	                break;				

	            case 'times':
	                switch ($style) {
	                    case 'light':						
	                    case 'regular' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
	                        else 
								$font_file_path = $this->font_addon_path . 'times.ttf';
	                        break;
	                    case 'italic' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ITALIC);
	                        else 
								$font_file_path = $this->font_addon_path . 'timesi.ttf';
	                        break;
						case 'semibold':
	                    case 'bold' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
	                        else 
								$font_file_path = $this->font_addon_path . 'timesbd.ttf';
	                        break;
		                case 'semibolditalic' :
	                    case 'bolditalic' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC);
	                        else 
								$font_file_path = $this->font_addon_path . 'timesbi.ttf';
	                        break;
	                    default:
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
	                        else 
								$font_file_path = $this->font_addon_path . 'times.ttf';
	                        break;
	                }
	                break;

	            case 'msgothic':
	                $font_file_path = $this->font_addon_path . 'msgothic.ttf';
	                break;

	            case 'tahoma':
					$font_file_path = $this->font_addon_path . 'tahoma.ttf';
	                break;

	            case 'garuda':
	                switch ($style) {
	                    case 'light':						
	                    case 'regular' :
	                        $font_file_path = $this->font_addon_path . 'garuda.ttf';
	                        break;
	                    case 'italic' :
	                        $font_file_path = $this->font_addon_path . 'garudai.ttf';
	                        break;
						case 'semibold':
	                    case 'bold' :
	                        $font_file_path = $this->font_addon_path . 'garudabd.ttf';
	                        break;
		                case 'semibolditalic' :
	                    case 'bolditalic' :
	                        $font_file_path = $this->font_addon_path . 'garudabi.ttf';
	                        break;
	                    default:
	                        $font_file_path = $this->font_addon_path . 'garuda.ttf';
	                        break;
	                }
	                break;

	            case 'sawasdee':
	                switch ($style) {
	                    case 'light':						
	                    case 'regular' :
	                        $font_file_path = $this->font_addon_path . 'sawasdee.ttf';
	                        break;
	                    case 'italic' :
	                        $font_file_path = $this->font_addon_path . 'sawasdeei.ttf';
	                        break;
	                    case 'bold' :
	                        $font_file_path = $this->font_addon_path . 'sawasdeebd.ttf';
	                        break;
		                case 'semibolditalic' :							
	                    case 'bolditalic' :
	                        $font_file_path = $this->font_addon_path . 'sawasdeebi.ttf';
	                        break;
	                    default:
	                        $font_file_path = $this->font_addon_path . 'sawasdee.ttf';
	                        break;
	                }
	                break;

	            case 'kinnari':
	                switch ($style) {
	                    case 'light':						
	                    case 'regular' :
	                        $font_file_path = $this->font_addon_path . 'kinnari.ttf';
	                        break;
	                    case 'italic' :
	                        $font_file_path = $this->font_addon_path . 'kinnarii.ttf';
	                        break;
						case 'semibold':
						case 'bold' :
	                        $font_file_path = $this->font_addon_path . 'kinnaribd.ttf';
	                        break;
		                case 'semibolditalic' :
	                    case 'bolditalic' :
	                        $font_file_path = $this->font_addon_path . 'kinnaribi.ttf';
	                        break;
	                    default:
	                        $font_file_path = $this->font_addon_path . 'kinnari.ttf';
	                        break;
	                }
	                break;

	            case 'purisa':
	                $font_file_path = $this->font_addon_path . 'purisa.ttf';
	                break;
	            case 'traditional_chinese':
	                $font_file_path = $this->font_addon_path . 'traditional_chinese.ttf';
	                break;
	            case 'simplified_chinese':
	                $font_file_path = $this->font_addon_path . 'simplified_chinese.ttf';
	                break;
	            case 'custom':
	                $font_file_path = $this->custom_path . $style;
	                break;
					
	            default:
	                switch ($style) {
	                    case 'light':						
	                    case 'regular' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	                        else 
								$font_file_path = $this->font_addon_path . 'arial.ttf';
	                        break;
	                    case 'italic' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
	                        else 
								$font_file_path = $this->font_addon_path . 'ariali.ttf';
	                        break;
						case 'semibold':
	                    case 'bold' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
	                        else 
								$font_file_path = $this->font_addon_path . 'arialbd.ttf';
	                        break;
		                case 'semibolditalic' :
	                    case 'bolditalic' :
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
	                        else 
								$font_file_path = $this->font_addon_path . 'arialbi.ttf';
	                        break;
	                    default:
	                        if ($non_standard_characters == 0)
	                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	                        else 
								$font_file_path = $this->font_addon_path . 'arial.ttf';
	                        break;
	                }
	                break;
        }

		if(isset($font_file_path) && ($font_file_path != '')) {			
	    	try{
				if(file_exists($font_file_path))
					if(strstr($font_file_path,'chinese') !== false)
						$font = Zend_Pdf_Font::fontWithPath($font_file_path);
					else
						$font = Zend_Pdf_Font::fontWithPath($font_file_path, Zend_Pdf_Font::EMBED_DONT_COMPRESS);
				else {
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	           	 	Mage::log('Font not found at '.$font_file_path.' - substituted Helvetica', null, 'moogento_pickpack.log');
				}

	        } catch(Exception $e) {
				Mage::log($e->getMessage(). ' - Error, font not found at '.$font_file_path.' - switched Helvetica', null, 'moogento_pickpack.log');
			}
		}
        return $font;
    }

    public function parseString($string, $font = null, $fontsize = null) {
        if (is_null($font))
            $font = $this->_font;
        if (is_null($fontsize))
            $fontsize = $this->_fontsize;

        $drawingString = iconv('UTF-8', 'UTF-16BE//TRANSLIT//IGNORE', $string);
        $characters    = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        if (!is_object($characters)) {
            $glyphs      = $font->glyphNumbersForCharacters($characters);
            $widths      = $font->widthsForGlyphs($glyphs);
            $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontsize;
            return $stringWidth;
        } else {
            // fudge for other extensions bad characters
            return (strlen($string) * $fontsize);
        }
    }


    public function getMaxCharMessage($padded_right, $font_size_options, $font_temp, $padded_left=30) {
        $maxWidthPage_message = $padded_right - $padded_left;
        $font_temp_message = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $font_size_compare_message = $font_size_options;
        $line_width_message = $this->parseString('12345abcde', $font_temp, $font_size_compare_message);
        $char_width_message = $line_width_message / 10;
        $max_chars_message = round($maxWidthPage_message / $char_width_message);
        return $max_chars_message;
    }

    public function getFontName2($font = 'helvetica', $style = 'regular', $non_standard_characters = 0) {
		$font_file_path = '';
		$non_standard_characters = 0; // forcing this after new font system
		
        switch ($font) {

	        case 'opensans':
	            switch ($style) {
	                case 'light' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-Light-webfont.ttf';
	                    break;
					case 'regular' :
	                    $font_file_path = $this->general_path . 'OpenSans-Regular-webfont.ttf';
	                    break;
	                case 'semibold' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-Semibold-webfont.ttf';
	                    break;
					case 'bold' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-Bold-webfont.ttf';
	                    break;
	                case 'italic' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-Italic-webfont.ttf';
	                    break;						
	                case 'semibolditalic' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-SemiboldItalic-webfont.ttf';
	                    break;
	                case 'bolditalic' :
	                    $font_file_path = $this->font_path . 'opensans/OpenSans-BoldItalic-webfont.ttf';
	                    break;
	                default:
	                    $font_file_path = $this->general_path . 'OpenSans-Regular-webfont.ttf';
	                    break;
	            }
	            break;
				
	        case 'noto':
	            switch ($style) {
	                case 'light' :
					case 'regular' :
	                    $font_file_path = $this->font_path . 'noto/NotoSans-Regular.ttf';
	                    break;
	                case 'semibold' :
					case 'bold' :
	                    $font_file_path = $this->font_path . 'noto/NotoSans-Bold.ttf';
	                    break;
	                case 'italic' :
	                    $font_file_path = $this->font_path . 'noto/NotoSans-Italic.ttf';
	                    break;						
	                case 'semibolditalic' :
	                case 'bolditalic' :
	                    $font_file_path = $this->font_path . 'noto/NotoSans-BoldItalic.ttf';
	                    break;
	                default:
	                    $font_file_path = $this->font_path . 'noto/NotoSans-Regular.ttf';
	                    break;
            }
            break;
			
	        case 'droid':
	            switch ($style) {
	                case 'light' :
					case 'regular' :
	                    $font_file_path = $this->font_path . 'droid/DroidSerif-Regular.ttf';
	                    break;
	                case 'semibold' :
					case 'bold' :
	                    $font_file_path = $this->font_path . 'droid/DroidSerif-Bold.ttf';
	                    break;
	                case 'italic' :
	                    $font_file_path = $this->font_path . 'droid/DroidSerif-Italic.ttf';
	                    break;						
	                case 'semibolditalic' :
	                case 'bolditalic' :
	                    $font_file_path = $this->font_path . 'droid/DroidSerif-BoldItalic.ttf';
	                    break;
	                default:
	                    $font_file_path = $this->font_path . 'droid/DroidSerif-Regular.ttf';
	                    break;
            }
            break;
			
	        case 'handwriting':
	            switch ($style) {
	                case 'light' :
					case 'regular' :
	                case 'italic' :
	                case 'bolditalic' :
	                case 'semibolditalic' :
						$font_file_path = $this->font_path . 'daniel/daniel.ttf';
	                    break;
	                case 'semibold' :
					case 'bold' :
	                    $font_file_path = $this->font_path . 'daniel/daniel-Bold.ttf';
	                    break;
	                default:
						$font_file_path = $this->font_path . 'daniel/daniel.ttf';
						break;
	            }
	            break;
						
            case 'helvetica':
                switch ($style) {
                    case 'light':
                    case 'regular':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        else
                            $font_file_path = $this->font_addon_path . 'arial.ttf';
                        break;
                    case 'italic':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
                        else
                            $font_file_path = $this->font_addon_path . 'ariali.ttf';
                        break;
                    case 'semibold':
                    case 'bold':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                        else
                            $font_file_path = $this->font_addon_path . 'arialbd.ttf';
                        break;
                    case 'semibolditalic':
                    case 'bolditalic':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                        else
                            $font_file_path = $this->font_addon_path . 'arialbi.ttf';
                        break;
                    default:
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        else
                            $font_file_path = $this->font_addon_path . 'arial.ttf';
                        break;
                }
                break;

            case 'courier':
                switch ($style) {
                    case 'light':
                    case 'regular':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
                        else
                            $font_file_path = $this->font_addon_path . 'cour.ttf';
                        break;
                    case 'italic':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_ITALIC);
                        else
                            $font_file_path = $this->font_addon_path . 'couri.ttf';
                        break;
                    case 'semibold':
                    case 'bold':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD);
                        else
                            $font_file_path = $this->font_addon_path . 'courbd.ttf';
                        break;
                    case 'semibolditalic':
                    case 'bolditalic':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD_ITALIC);
                        else
                            $font_file_path = $this->font_addon_path . 'courbi.ttf';
                        break;
                    default:
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER);
                        else
                            $font_file_path = $this->font_addon_path . 'cour.ttf';
                        break;
                }
                break;

            case 'times':
                switch ($style) {
                    case 'light':
                    case 'regular':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                        else
                            $font_file_path = $this->font_addon_path . 'times.ttf';
                        break;
                    case 'italic':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_ITALIC);
                        else
                            $font_file_path = $this->font_addon_path . 'timesi.ttf';
                        break;
					case 'semibold':						
                    case 'bold':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
                        else
                            $font_file_path = $this->font_addon_path . 'timesbd.ttf';
                        break;
	                case 'semibolditalic' :
                    case 'bolditalic':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD_ITALIC);
                        else
                            $font_file_path = $this->font_addon_path . 'timesbi.ttf';
                        break;
                    default:
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
                        else
                            $font_file_path = $this->font_addon_path . 'times.ttf';
                        break;
                }
                break;

            case 'msgothic':
                $font_file_path = $this->font_addon_path . 'msgothic.ttf';
                break;

            case 'tahoma':
                $font_file_path = $this->font_addon_path . 'tahoma.ttf';
                break;

            case 'garuda':
                switch ($style) {
                    case 'light':
                    case 'regular':
                        $font_file_path = $this->font_addon_path . 'garuda.ttf';
                        break;
                    case 'italic':
                        $font_file_path = $this->font_addon_path . 'garudai.ttf';
                        break;
					case 'semibold':
                    case 'bold':
                        $font_file_path = $this->font_addon_path . 'garudabd.ttf';
                        break;
	                case 'semibolditalic' :
                    case 'bolditalic':
                        $font_file_path = $this->font_addon_path . 'garudabi.ttf';
                        break;
                    default:
                        $font_file_path = $this->font_addon_path . 'garuda.ttf';
                        break;
                }
                break;

            case 'sawasdee':
                switch ($style) {
                    case 'light':
                    case 'regular':
                        $font_file_path = $this->font_addon_path . 'sawasdee.ttf';
                        break;
                    case 'italic':
                        $font_file_path = $this->font_addon_path . 'sawasdeei.ttf';
                        break;
                    case 'bold':
					case 'semibold':
                        $font_file_path = $this->font_addon_path . 'sawasdeebd.ttf';
                        break;
	                case 'semibolditalic' :
                    case 'bolditalic':
                        $font_file_path = $this->font_addon_path . 'sawasdeebi.ttf';
                        break;
                    default:
                        $font_file_path = $this->font_addon_path . 'sawasdee.ttf';
                        break;
                }
                break;

            case 'kinnari':
                switch ($style) {
                    case 'light':
                    case 'regular':
                        $font_file_path = $this->font_addon_path . 'kinnari.ttf';
                        break;
                    case 'italic':
                        $font_file_path = $this->font_addon_path . 'kinnarii.ttf';
                        break;
                    case 'bold':
					case 'semibold':
                        $font_file_path = $this->font_addon_path . 'kinnaribd.ttf';
                        break;
	                case 'semibolditalic' :
                    case 'bolditalic':
                        $font_file_path = $this->font_addon_path . 'kinnaribi.ttf';
                        break;
                    default:
                        $font_file_path = $this->font_addon_path . 'kinnari.ttf';
                        break;
                }
                break;

            case 'purisa':
                $font_file_path = $this->font_addon_path . 'purisa.ttf';
                break;

            case 'custom':
	            $font_file_path = $this->custom_path . $style;
                break;

            default:
                switch ($style) {
                    case 'light':
                    case 'regular':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        else
                            $font_file_path = $this->font_addon_path . 'arial.ttf';
                        break;
                    case 'italic':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC);
						else
                            $font_file_path = $this->font_addon_path . 'ariali.ttf';
                        break;
                    case 'bold':
					case 'semibold':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD);
                        else
                            $font_file_path = $this->font_addon_path . 'arialbd.ttf';
                        break;
	                case 'semibolditalic' :
                    case 'bolditalic':
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD_ITALIC);
                        else
                            $font_file_path = $this->font_addon_path . 'arialbi.ttf';
                        break;
                    default:
                        if ($non_standard_characters == 0)
                            $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                        else
                            $font_file_path = $this->font_addon_path . 'arial.ttf';
                        break;
                }
                break;
        }

		if(isset($font_file_path) && ($font_file_path != '')) {		
	    	try{
				if(file_exists($font_file_path))
					if(strstr($font_file_path,'chinese') !== false)
						$font = Zend_Pdf_Font::fontWithPath($font_file_path);
					else
						$font = Zend_Pdf_Font::fontWithPath($font_file_path, Zend_Pdf_Font::EMBED_DONT_COMPRESS);
				else {
					$font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	           	 	Mage::log('Font not found at '.$font_file_path.' - substituted Helvetica', null, 'moogento_pickpack.log');
				}

	        } catch(Exception $e) {
				Mage::log($e->getMessage(). ' - Error, font not found at '.$font_file_path.' - switched Helvetica', null, 'moogento_pickpack.log');
			}
		}
        return $font;
    }
}
