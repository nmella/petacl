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
* File        Setname.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_System_Config_Backend_Import_Setname extends Mage_Core_Model_Config_Data
{
    public function afterLoad() {
        $sv_name1 = Mage::getModel('pickpack/adminhtml_system_config_backend_import_name')->getTheName();
        $sv_name2 = Mage::getModel('pickpack/adminhtml_system_config_backend_import_name')->getThatName();
        if ($sv_name1 == $sv_name2) {
            $this->setValue(sprintf('%s', $sv_name1));
        } else {
            $this->setValue(sprintf('%s (Primary: %s)', $sv_name1, $sv_name2));
        }
    }
}
