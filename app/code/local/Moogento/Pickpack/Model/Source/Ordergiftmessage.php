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
* File        Ordergiftmessage.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Model_Source_Ordergiftmessage
{

    /**
     * Options getter
     *
     * @return array
     */     
    //Yes, combined under products list (default)]
    //[Yes, combined movable box]
    //[Yes, in combo new page]
    //[No]
    //[Yes, combined under products list (default)][Yes, combined in movable box][Yes, combined in new page]
    public function toOptionArray() {
        return array(
            array('value' => 'yesunder', 'label' => Mage::helper('pickpack')->__('Yes, combined under products list')),
            array('value' => 'yesundership', 'label' => Mage::helper('pickpack')->__('Yes, combined under shipping address')),
            array('value' => 'yesbox', 'label' => Mage::helper('pickpack')->__('Yes, combined in movable box')),
            array('value' => 'yesnewpage', 'label' => Mage::helper('pickpack')->__('Yes, combined in new page')),
            array('value' => 'no', 'label' => Mage::helper('pickpack')->__('No'))
        );
    }

}
