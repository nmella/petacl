<?php
class FME_Feed_Block_Adminhtml_Feed extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_feed';
    $this->_blockGroup = 'feed';
    $this->_headerText = Mage::helper('feed')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('feed')->__('Add Item');
    parent::__construct();
  }
}