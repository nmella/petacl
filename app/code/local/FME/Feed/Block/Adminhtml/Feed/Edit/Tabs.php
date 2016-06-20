<?php

class FME_Feed_Block_Adminhtml_Feed_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('feed_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('feed')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('feed')->__('Item Information'),
          'title'     => Mage::helper('feed')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('feed/adminhtml_feed_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}