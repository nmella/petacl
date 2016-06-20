<?php

class Moogento_Pickpack_Model_Source_Giftcardtemplate
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 0, 'label' =>Mage::helper('pickpack')->__('No')),
            array('value' => 1, 'label' => Mage::helper('pickpack')->__('Top-left Ribbon')),
            array('value' => 2, 'label' => Mage::helper('pickpack')->__('Angled Ribbon')),
            array('value' => 3, 'label' => Mage::helper('pickpack')->__('Custom Image')),
        );
    }

}
