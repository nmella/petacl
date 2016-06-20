<?php
class FME_Feed_Block_Feed extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getFeed()     
     { 
        if (!$this->hasData('feed')) {
            $this->setData('feed', Mage::registry('feed'));
        }
        return $this->getData('feed');
        
    }
}