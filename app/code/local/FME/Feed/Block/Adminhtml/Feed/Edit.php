<?php

class FME_Feed_Block_Adminhtml_Feed_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'feed';
        $this->_controller = 'adminhtml_feed';
        
        $this->_updateButton('save', 'label', Mage::helper('feed')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('feed')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('feed_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'feed_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'feed_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('feed_data') && Mage::registry('feed_data')->getId() ) {
            return Mage::helper('feed')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('feed_data')->getTitle()));
        } else {
            return Mage::helper('feed')->__('Add Item');
        }
    }
}