<?php
/**
 * 
 * Date: 23.10.15
 * Time: 9:45
 */


class Moogento_Pickpack_Helper_Error extends Mage_Core_Helper_Abstract
{
    function showError($error_code, $source_path, $target_path) {
        // if there was an error, let's see what the error is about
        switch ($error_code) {
            case 1:
                echo 'Source file "' . $source_path . '" could not be found!';
                break;
            case 2:
                echo 'Source file "' . $source_path . '" is not readable!';
                break;
            case 3:
                echo 'Could not write target file "' . $source_path . '"!';
                break;
            case 4:
                echo $source_path . '" is an unsupported source file format!';
                break;
            case 5:
                echo $target_path . '" is an unsupported target file format!';
                break;
            case 6:
                echo 'GD library version does not support target file format!';
                break;
            case 7:
                echo 'GD library is not installed!';
                break;
            case 8:
                echo '"chmod" command is disabled via configuration!';
                break;

        }
        exit;
    }
}