<?php
class FME_Percentagepricing_Block_Adminhtml_Percentagepricing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_percentagepricing';
    $this->_blockGroup = 'percentagepricing';
    $this->_headerText = Mage::helper('percentagepricing')->__('Rule Manager');
    $this->_addButtonLabel = Mage::helper('percentagepricing')->__('Add Rule');
    parent::__construct();
  }
}