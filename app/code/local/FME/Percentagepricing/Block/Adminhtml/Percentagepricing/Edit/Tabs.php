<?php

class FME_Percentagepricing_Block_Adminhtml_Percentagepricing_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('percentagepricing_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('percentagepricing')->__('Rule Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('percentagepricing')->__('Description'),
          'title'     => Mage::helper('percentagepricing')->__('Description'),
          'content'   => $this->getLayout()->createBlock('percentagepricing/adminhtml_percentagepricing_edit_tab_formdecription')->toHtml(),
      ));
      $this->addTab('form_section2', array(
          'label'     => Mage::helper('percentagepricing')->__('Action'),
          'title'     => Mage::helper('percentagepricing')->__('Action'),
          'content'   => $this->getLayout()->createBlock('percentagepricing/adminhtml_percentagepricing_edit_tab_formaction')->toHtml(),
      ));
       $this->addTab('form_section3', array(
          'label'     => Mage::helper('percentagepricing')->__('Conditions'),
          'title'     => Mage::helper('percentagepricing')->__('Conditions'),
          'content'   => $this->getLayout()->createBlock('percentagepricing/adminhtml_percentagepricing_edit_tab_conditions')->toHtml(),
      ));
      return parent::_beforeToHtml();
  }
}