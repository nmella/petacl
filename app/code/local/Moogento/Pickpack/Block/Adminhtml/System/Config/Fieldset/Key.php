<?php


class Moogento_Pickpack_Block_Adminhtml_System_Config_Fieldset_Key
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if (Mage::getStoreConfig('pickpack_options/moodetails/license')) {
            $html = <<<HTML
           <script defer src="{$this->getMarkJs()}?element={$element->getHtmlId()}"></script>
HTML;
            $element->setComment($html);
        }
        return parent::render($element);
    }

    public function getMarkJs()
    {
        return Mage::getStoreConfig('moogento/general/url') . 'media/moo_key/' . Mage::helper('pickpack/moo')->m() . '/moogento_pickpack.js';
    }
} 