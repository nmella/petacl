<?php

class FME_Feed_Model_After extends Varien_Object
{
    static public function toOptionArray()
    {
        return array(
            '1'    => Mage::helper('feed')->__('1 Day'),
            '2'   => Mage::helper('feed')->__('2 Day'),
            '3'    => Mage::helper('feed')->__('3 Day'),
            '4'    => Mage::helper('feed')->__('1 Week'),
            '5'    => Mage::helper('feed')->__('1 Month'),
            '6'    => Mage::helper('feed')->__('3 Month'),
            '7'    => Mage::helper('feed')->__('6 Month'),
            '8'    => Mage::helper('feed')->__('1 Year')
        );
    }
}