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
* File        Nonstandardcharacters.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Source_Nonstandardcharacters
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => '0', 'label' => Mage::helper('pickpack')->__('No')),
            array('value' => '1', 'label' => Mage::helper('pickpack')->__('Yes, standard fonts [large filesize, good for accented Roman]')),
            array('value' => 'msgothic', 'label' => Mage::helper('pickpack')->__('Yes, MS Gothic [large filesize, good for: Japanese, Roman]')),
            array('value' => 'tahoma', 'label' => Mage::helper('pickpack')->__('Yes, Tahoma [medium filesize, good for: Arabic, Cyrillic]')),
            array('value' => 'garuda', 'label' => Mage::helper('pickpack')->__('Yes, Garuda [good for: Thai, Roman]')),
            array('value' => 'sawasdee', 'label' => Mage::helper('pickpack')->__('Yes, Sawasdee [good for: Thai, Roman]')),
            array('value' => 'kinnari', 'label' => Mage::helper('pickpack')->__('Yes, Kinnari (similar to Georgia) [good for: Thai, Roman]')),
            array('value' => 'purisa', 'label' => Mage::helper('pickpack')->__('Yes, Purisa (similar to Comic Sans) [good for: Thai, Roman]')),
            array('value' => 'traditional_chinese', 'label' => Mage::helper('pickpack')->__('Yes, Traditional Chinese [good for: Chinese]')),
            array('value' => 'simplified_chinese', 'label' => Mage::helper('pickpack')->__('Yes, Simplified Chinese [good for: Chinese]')),
            array('value' => 'hebrew', 'label' => Mage::helper('pickpack')->__('Yes, Ezra SIL [good for: Hebrew]')),
        );
    }

}
