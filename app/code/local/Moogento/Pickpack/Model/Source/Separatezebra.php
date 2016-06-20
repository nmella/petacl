<?php

class Moogento_Pickpack_Model_Source_Separatezebra
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 0, 'label' =>Mage::helper('pickpack')->__('No')),
            array('value' => 1, 'label' => Mage::helper('pickpack')->__('Yes, all products listed')),
            array('value' => 2, 'label' => Mage::helper('pickpack')->__('Yes, one product per label')),
        );
    }

}
