<?php


class Moogento_Pickpack_Block_Adminhtml_System_Config_Fieldset_Name
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $element->setReadonly(true, true);
        return parent::render($element);
    }
}