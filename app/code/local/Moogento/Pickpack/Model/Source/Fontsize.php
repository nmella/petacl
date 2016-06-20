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
* File        Fontsize.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Source_Fontsize
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 6, 'label' => Mage::helper('pickpack')->__('6pt')),
            array('value' => 7, 'label' => Mage::helper('pickpack')->__('7pt')),
            array('value' => 8, 'label' => Mage::helper('pickpack')->__('8pt')),
            array('value' => 9, 'label' => Mage::helper('pickpack')->__('9pt')),
            array('value' => 10, 'label' => Mage::helper('pickpack')->__('10pt')),
            array('value' => 11, 'label' => Mage::helper('pickpack')->__('11pt')),
            array('value' => 12, 'label' => Mage::helper('pickpack')->__('12pt')),
            array('value' => 13, 'label' => Mage::helper('pickpack')->__('13pt')),
            array('value' => 14, 'label' => Mage::helper('pickpack')->__('14pt')),
            array('value' => 15, 'label' => Mage::helper('pickpack')->__('15pt')),
            array('value' => 16, 'label' => Mage::helper('pickpack')->__('16pt')),
            array('value' => 17, 'label' => Mage::helper('pickpack')->__('17pt')),
            array('value' => 18, 'label' => Mage::helper('pickpack')->__('18pt')),
            array('value' => 24, 'label' => Mage::helper('pickpack')->__('24pt'))
        );
    }

}
