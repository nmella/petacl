<?php
/**
 * 
 * Date: 18.11.15
 * Time: 12:25
 */

class Moogento_Pickpack_Helper_Number extends Mage_Core_Helper_Abstract
{
    public function _containsDecimal( $input ) {
        if ( strpos( $input, "." ) !== false ) {
            return true;
        }
        return false;
    }

    public function roundNumber($input,$decimals=0) {

        if($this->_containsDecimal($input) == false) return $input;
        if(is_numeric($input) == false) return $input;

        switch ($decimals) {
            case 'yes2':
                $decimals=2;
                break;
            case 'yes0':
                $decimals=0;
                break;
            default:
                $decimals=4;
                break;
        }

        // @TODO option for formatting the decimal/thousands
        // number_format ( float $number , int $decimals = 0 , string $dec_point = "." , string $thousands_sep = "," )
        return number_format($input , $decimals , "." , "" );
    }
}
