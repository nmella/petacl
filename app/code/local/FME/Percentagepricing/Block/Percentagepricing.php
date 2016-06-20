<?php
class FME_Percentagepricing_Block_Percentagepricing extends Mage_Core_Block_Template
{
	protected $_priority = 0;
	
	public function __construct()
	{
		$this->_priority = 1;
	}
	
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getPercentagepricing()     
     { 
        if (!$this->hasData('percentagepricing')) {
            $this->setData('percentagepricing', Mage::registry('percentagepricing'));
        }
        return $this->getData('percentagepricing');
        
    }
	    protected function getPercentagepricingRules()
	{
		
		if ($this->_addpercentagepricingRules === null)
		{
			//$collection = Mage::getModel('percentagepricing/percentagepricing')->getCollection();
			/*$collection->addStoreFilter()
				->addStatusFilter()
				->addDateFilter()*/;
            $customergroupId = (int) Mage::getSingleton('customer/session')->getCustomerGroupId();
            $storeId = (int) Mage::app()->getStore()->getStoreId();
			
			$collection = Mage::getModel('percentagepricing/percentagepricing')->getCollection();
            $collection->addFieldToFilter('status', 1);
            $collection->addFieldToFilter('customer_group_ids', array('finset'=>$customergroupId));
            $collection->addFieldToFilter('website_ids', array('finset'=>$storeId));
				
			
			$this->_addpercentagepricingRules = $collection;
		}
	
		return $this->_addpercentagepricingRules;
	}
	
	public function getPercentagepricingHtml($id=null)
	{
		
		
		$out = '';
		$ruleIds = array();
		
		//Get Current Product ID
		//$productId = $this->getRequest()->getParam('id'); //echo $productId;
	   
		if ($id != null)
		{
			$productId = $id;
		}
		
		foreach ($this->getPercentagepricingRules() as $bgiRule)
		{	
			$model = Mage::getModel('percentagepricing/percentagepricing_product_rulecss');
			$model->setWebsiteIds(Mage::app()->getStore()->getWebsite()->getId());
			
			//$this->_afterLoad();
			
			$conditions =  $this->getData('percentagepricing_rule')->getConditions(); //echo '<pre>';print_r($conditions['css']);
			if (isset($conditions['css']))
			{
				$model->getConditions()->loadArray($conditions, 'css');
				$match = $model->getMatchingProductIds(); //echo '<pre>';print_r($match);
				if (in_array($productId, $match))
				{
					$ruleIds[] = $bgiRule["percentagepricing_id"];
				}
			}
		}
		
		
		//Rebuild collection to get the rule that has to be applied on current product
		$collection = Mage::getModel('percentagepricing/percentagepricing')->getCollection();
		$collection->addIdsFilter($ruleIds); //echo (string) $collection->getSelect();exit;
		if ($this->_priority)
		{
			$collection->setPriorityOrder()
			->setPageSize(1);
		}
		$collection->getData();

		return $collection->getData();
    }
    protected function _afterLoad()
    {
   
        if ($this->getData('percentagepricing_rule') && is_string($this->getData('percentagepricing_rule')))
            $this->setData('percentagepricing_rule', @unserialize(Mage::registeyr('percentagepricing_rule')));
             if ($this->getData('from_date') && is_empty_date($this->getData('from_date')))
            $this->setData('from_date', '');
        if ($this->getData('to_date') && is_empty_date($this->getData('to_date')))
            $this->setData('to_date', '');
             if ($this->getData('store') !== null && is_string($this->getData('store')))
            $this->setData('store', @explode(',', $this->getData('store')));
	 
        $this->humanizeData();
        
        
    }



    public function getTypeById($id)
    {
        $block = $this->load($id);
        
        return $block->getType();
    }
	
	public function humanizeData()
    {
          
        if (is_array(Mage::registry('percentagepricing_rule')))
            $this->setData('percentagepricing_rule', new Varien_Object(Mage::registry('percentagepricing_rule')));
            
        return $this;
    }

    public function callAfterLoad()
    {
        return $this->_afterLoad();
    }
}