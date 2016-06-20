<?php

class FME_Feed_Block_Adminhtml_Feed_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('feed_form', array('legend'=>Mage::helper('feed')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('feed')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('feed')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('feed')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('feed')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('feed')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('feed')->__('Content'),
          'title'     => Mage::helper('feed')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getFeedData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFeedData());
          Mage::getSingleton('adminhtml/session')->setFeedData(null);
      } elseif ( Mage::registry('feed_data') ) {
          $form->setValues(Mage::registry('feed_data')->getData());
      }
      return parent::_prepareForm();
  }
}