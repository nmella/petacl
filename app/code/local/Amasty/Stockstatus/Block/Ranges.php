<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Block_Ranges extends Mage_Core_Block_Template
{
    protected $_optionsCollection;
    protected $_ruleCollection;
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amstockstatus/ranges.phtml');
    }
    
    public function getOptionsCollection()
    {
        if (!$this->_optionsCollection)
        {
            $this->_optionsCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                            ->setAttributeFilter(Mage::registry('entity_attribute')->getId())
                                            ->setPositionOrder('desc', true)
                                            ->load();
        }
        return $this->_optionsCollection;
    }
    
    public function getRuleCollection()
    {
        if (!$this->_ruleCollection)
        {
            $attributes = Mage::getModel("catalog/product")->getAttributes();
            $attributeCode = "custom_stock_status_qty_rule";
            if(isset($attributes[$attributeCode])){
                  $attrObject = $attributes[$attributeCode];
                  $this->_ruleCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                                            ->setAttributeFilter($attrObject->getId())
                                            ->setPositionOrder('asc', true)
                                            ->load();
            }
        }
        return $this->_ruleCollection;
    }
    
    public function getRanges()
    {
        $collection = Mage::getModel('amstockstatus/range')->getCollection();
        $collection->getSelect()->order('qty_from');
        return $collection;
    }
}