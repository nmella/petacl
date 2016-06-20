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
* File        Fields.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_System_Config_Backend_Custom_Csv_Csvfieldseparator
    extends Mage_Core_Model_Config_Data
{
    public function _beforeSave() {
        $post = Mage::app()->getRequest()->getParam('groups');
        $value = $post['general_csv']['fields']['csv_field_separator_custom']['value'][0];

        if (!isset($value) || trim($value) == ""){
            $value = ',';
            $this->setValue($value);
        }

        return $this;
    }
}
