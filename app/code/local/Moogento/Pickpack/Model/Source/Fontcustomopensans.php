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
* File        Fontcustomopensans.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Source_Fontcustomopensans
{

    /**
     * Options getter
     *
     * @return array
     */
	
	/*
		MS Gothic **** [Japanese, Roman]
		Tahoma *** [Arabic, Cyrillic]
		Garuda ** [Thai, Roman]
		Sawasdee ** [Thai, Roman]
		Kinnari ** (similar to Georgia) [Thai, Roman]
		Traditional Chinese *****
		Simplified Chinese *****
	*/
    public function toOptionArray() {
        return array(
            array('value' => 'opensans', 'label' => Mage::helper('pickpack')->__('OpenSans *')),
            array('value' => 'droid', 'label' => Mage::helper('pickpack')->__('DroidSerif 2*')),
            array('value' => 'noto', 'label' => Mage::helper('pickpack')->__('Noto 2*')),
            array('value' => 'handwriting', 'label' => Mage::helper('pickpack')->__('Handwriting *')),
            array('value' => 'helvetica', 'label' => Mage::helper('pickpack')->__('Helvetica')),
            array('value' => 'times', 'label' => Mage::helper('pickpack')->__('Times')),
            array('value' => 'courier', 'label' => Mage::helper('pickpack')->__('Courier')),
            array('value' => 'msgothic', 'label' => Mage::helper('pickpack')->__('MS Gothic 4*')),
            array('value' => 'tahoma', 'label' => Mage::helper('pickpack')->__('Tahoma 3*')),
            array('value' => 'garuda', 'label' => Mage::helper('pickpack')->__('Garuda 2*')),
            array('value' => 'sawasdee', 'label' => Mage::helper('pickpack')->__('Sawasdee 2*')),
            array('value' => 'kinnari', 'label' => Mage::helper('pickpack')->__('Kinnari 2*')),
            array('value' => 'traditional_chinese', 'label' => Mage::helper('pickpack')->__('Chinese (T) 5*')),
            array('value' => 'simplified_chinese', 'label' => Mage::helper('pickpack')->__('Chinese (S) 5*')),
            // array('value' => 'hebrew', 'label' => Mage::helper('pickpack')->__('Ezra SIL *** [Hebrew]')),
            array('value' => 'custom', 'label' => Mage::helper('pickpack')->__('-[custom]-'))
        );
    }

}
