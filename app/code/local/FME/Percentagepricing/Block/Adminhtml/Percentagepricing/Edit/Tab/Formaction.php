<?php

class FME_Percentagepricing_Block_Adminhtml_Percentagepricing_Edit_Tab_Formaction extends Mage_Adminhtml_Block_Widget_Form 
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      
      $fieldset = $form->addFieldset('percentagepricing_form', array('legend'=>Mage::helper('percentagepricing')->__('Rule information')));
      
	  $fieldset->addField('apply', 'select', array(
          'label'     => Mage::helper('percentagepricing')->__('Apply'),
          'name'      => 'apply',
          'values'    => array(
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('percentagepricing')->__('Please Select The Action...'),
              ), 
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('percentagepricing')->__('By Percentage Of Cost'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('percentagepricing')->__('By Fixed Amount'),
              ),
          ),
      ));
	  $fieldset->addField('action', 'select', array(
          'label'     => Mage::helper('percentagepricing')->__('Action'),
          'name'      => 'action',
          'values'    => array(
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('percentagepricing')->__('Add'),
              ), 
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('percentagepricing')->__('Subtract'),
              ),
          ),
      ));
	  $fieldset->addField('amount', 'text', array(
          'label'     => Mage::helper('percentagepricing')->__('Amount'),
          'required'  => true,
		  'class'     => 'required-entry validate-zero-or-greater',
          'name'      => 'amount',
      ));

        
		  
	 $this->setForm($form);
      if ( Mage::getSingleton('adminhtml/session')->getPercentagepricingData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPercentagepricingData());
          Mage::getSingleton('adminhtml/session')->setPercentagepricingData(null);
      } elseif ( Mage::registry('percentagepricing_data') ) {
          $form->setValues(Mage::registry('percentagepricing_data')->getData());
      }
      return parent::_prepareForm();
	  
  }
}
