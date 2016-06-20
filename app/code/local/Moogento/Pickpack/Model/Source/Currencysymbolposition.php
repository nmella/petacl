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
* File        Yesno.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

/**
 * Used in creating options for Yes|No config value selection
 *
 */
class Moogento_Pickpack_Model_Source_Currencysymbolposition
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 'auto', 'label' => Mage::helper('pickpack')->__('Auto (eg. $10.00)')),
            array('value' => 'left', 'label' => Mage::helper('pickpack')->__('Left (eg. $10.00)')),
            array('value' => 'right', 'label' => Mage::helper('pickpack')->__('Right (eg. 10.00$)')),
            array('value' => 'magento', 'label' => Mage::helper('pickpack')->__('--Magento Format--')),
        );
    }

}
