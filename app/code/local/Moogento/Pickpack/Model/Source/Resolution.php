<?php

class Moogento_Pickpack_Model_Source_Resolution
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 0, 'label' =>Mage::helper('pickpack')->__('203 dpi')),
            array('value' => 1, 'label' => Mage::helper('pickpack')->__('300 dpi')),
        );
    }

}
