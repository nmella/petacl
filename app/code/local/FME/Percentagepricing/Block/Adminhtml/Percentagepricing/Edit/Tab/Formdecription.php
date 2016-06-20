<?php

class FME_Percentagepricing_Block_Adminhtml_Percentagepricing_Edit_Tab_Formdecription extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('percentagepricing_form', array('legend'=>Mage::helper('percentagepricing')->__('Rule information')));
    
	 
      $fieldset->addField('name', 'text', array(
          'label'     => Mage::helper('percentagepricing')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'name',
      ));

      $fieldset->addField('priorty', 'text', array(
          'label'     => Mage::helper('percentagepricing')->__('Priorty'),
          'required'  => true,
		  'class'     => 'required-entry validate-zero-or-greater',
          'name'      => 'priorty',
	  ));
	  /*
	    $model = Mage::registry('percentagepricing_data');
	  if (!Mage::app()->isSingleStoreMode())
	  {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name'      => 'website_ids[]',
                'label'     => Mage::helper('percentagepricing')->__('Websites'),
                'title'     => Mage::helper('percentagepricing')->__('Websites'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
            ));
        }
        else
		{
            $fieldset->addField('website_ids', 'hidden', array(
                'name'      => 'website_ids[]',
                'value'     => Mage::app()->getStore(true)->getWebsiteId()
            ));
            $model->setWebsiteIds(Mage::app()->getStore(true)->getWebsiteId());
        }
		*/
	  $fieldset->addField('website_ids','multiselect',array(
			'name'      => 'website_ids[]',
			'label'     => Mage::helper('percentagepricing')->__('Store View'),
			'title'     => Mage::helper('percentagepricing')->__('Store View'),
			'required'  => true,
			'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
		));
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group)
		{
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found)
		{
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('catalogrule')->__('NOT LOGGED IN')));
        }

	  $fieldset->addField('customer_group_ids', 'multiselect', array(
		  'name'      => 'customer_group_ids[]',
		  'label'     => Mage::helper('percentagepricing')->__('Customer Groups'),
		  'title'     => Mage::helper('percentagepricing')->__('Customer Groups'),
		  'required'  => true,
		  'values'    => $customerGroups,
	  ));
		
      $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => Mage::helper('percentagepricing')->__('Description'),
          'title'     => Mage::helper('percentagepricing')->__('Description'),
          'style'     => 'width:600px; height:300px;',
          'wysiwyg'   => false,
          'required'  => false,
      ));
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('percentagepricing')->__('Status'),
          'name'      => 'status',
		  'required'  => true,
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('percentagepricing')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('percentagepricing')->__('Disabled'),
              ),
          ),
      ));
     

     
      if ( Mage::getSingleton('adminhtml/session')->getPercentagepricingData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getPercentagepricingData());
          Mage::getSingleton('adminhtml/session')->setPercentagepricingData(null);
      }
	  elseif ( Mage::registry('percentagepricing_data') )
	  {
          $form->setValues(Mage::registry('percentagepricing_data')->getData());
      }
      return parent::_prepareForm();
  }
}