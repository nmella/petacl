<?php

class FME_Percentagepricing_Model_Percentagepricing extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('percentagepricing/percentagepricing');
    }
      	protected function _afterLoad()
    {
    
        if ($this->getData('percentagepricing_rule') && is_string($this->getData('percentagepricing_rule')))
            $this->setData('percentagepricing_rule', @unserialize($this->getData('percentagepricing_rule')));
             if ($this->getData('from_date') && is_empty_date($this->getData('from_date')))
            $this->setData('from_date', '');
        if ($this->getData('to_date') && is_empty_date($this->getData('to_date')))
            $this->setData('to_date', '');
             if ($this->getData('store') !== null && is_string($this->getData('store')))
            $this->setData('store', @explode(',', $this->getData('store')));
        $this->humanizeData();
        
        return parent::_afterLoad();
    }
 protected function _beforeSave()
    {
      
        
        if ($this->getData('percentagepricing_rule') && is_array($this->getData('percentagepricing_rule')))
            $this->setData('percentagepricing_rule', @serialize($this->getData('percentagepricing_rule')));
   
        if ($this->getData('store') !== null && is_array($this->getData('store')))
            $this->setData('store', @implode(',', $this->getData('store')));
            
        return parent::_beforeSave();
    }

    public function getTypeById($id)
    {
        $block = $this->load($id);
        
        return $block->getType();
    }
	
	public function humanizeData()
    {
       
        if (is_array($this->getData('percentagepricing_rule')))
            $this->setData('percentagepricing_rule', new Varien_Object($this->getData('percentagepricing_rule')));
            
        return $this;
    }

    public function callAfterLoad()
    {
        return $this->_afterLoad();
    }
}