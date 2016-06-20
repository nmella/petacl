<?php

class Moogento_Pickpack_Model_Source_Productwrapstyle
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 'yesshipping', 'label' => Mage::helper('pickpack')->__('Under shipping details')),
            array('value' => 'yesunderproducts', 'label'=>Mage::helper('pickpack')->__('Under product list')),
            array('value' => 'yesunderinvi', 'label' => Mage::helper('pickpack')->__('Under individual product')),
            array('value' => 'yesbox', 'label' => Mage::helper('pickpack')->__('In movable box (handy for tear-off labels)')),
        );
    }
} 