<?php

class Moogento_Pickpack_Model_Source_Pricetax
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 0, 'label' =>Mage::helper('pickpack')->__('No')),
            array('value' => 1, 'label' => Mage::helper('pickpack')->__('Yes (Before tax)')),
            array('value' => 2, 'label' => Mage::helper('pickpack')->__('Yes (After tax)')),
        );
    }

}
