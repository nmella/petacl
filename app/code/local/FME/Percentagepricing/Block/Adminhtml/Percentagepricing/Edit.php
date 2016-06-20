<?php

class FME_Percentagepricing_Block_Adminhtml_Percentagepricing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'percentagepricing';
        $this->_controller = 'adminhtml_percentagepricing';
        
        $this->_updateButton('save', 'label', Mage::helper('percentagepricing')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('percentagepricing')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('percentagepricing_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'percentagepricing_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'percentagepricing_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('percentagepricing_data') && Mage::registry('percentagepricing_data')->getId() ) {
            return Mage::helper('percentagepricing')->__("Edit Rule '%s'", $this->htmlEscape(Mage::registry('percentagepricing_data')->getName()));
        } else {
            return Mage::helper('percentagepricing')->__('Add Rule');
        }
    }
}