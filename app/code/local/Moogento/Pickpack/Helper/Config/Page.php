<?php
/**
 * 
 * Date: 18.11.15
 * Time: 13:40
 */

class Moogento_Pickpack_Helper_Config_Color extends Moogento_Pickpack_Helper_Config
{
    public function getPdfPageCoordinates($pageSize, $name) {
        switch($name) {
            case 'red_bkg_color':
                $color = new Zend_Pdf_Color_Html('lightCoral');
                break;
            case 'grey_bkg_color':
                $color = new Zend_Pdf_Color_GrayScale(0.7);
                break;
            case 'dk_cyan_bkg_color':
                $color = new Zend_Pdf_Color_Html('darkCyan'); //darkOliveGreen
                break;
            case 'bk_og_bkg_color':
                $color = new Zend_Pdf_Color_Html('darkOliveGreen');
                break;
            case 'black_color':
                $color = new Zend_Pdf_Color_Rgb(0, 0, 0);
                break;
            case 'red_color':
                $color = new Zend_Pdf_Color_Html('darkRed');
                break;
            case 'dk_grey_bkg_color':
            case 'grey_color':
                $color = new Zend_Pdf_Color_GrayScale(0.3);
                break;
            case 'greyout_color':
                $color = new Zend_Pdf_Color_GrayScale(0.6);
                break;
            case 'white_color':
                $color = new Zend_Pdf_Color_GrayScale(1);
                break;
            case 'grayout_color':
                $color = "#888888";
                break;
            default:
                Mage::throwException('PDF color with name "'.$name.'" not found.');
                break;
        }

        return $color;
    }
}