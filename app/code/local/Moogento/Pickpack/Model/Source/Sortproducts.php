<?php
class Moogento_Pickpack_Model_Source_Sortproducts
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 0, 'label' => Mage::helper('pickpack')->__('None')),
            array('value' => 'sku', 'label' => Mage::helper('pickpack')->__('SKU')),
            array('value' => 'attribute', 'label' => Mage::helper('pickpack')->__('Custom Attribute')),
        );
    }

}
