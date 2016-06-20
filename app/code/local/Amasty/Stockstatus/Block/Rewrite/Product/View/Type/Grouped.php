<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */
class Amasty_Stockstatus_Block_Rewrite_Product_View_Type_Grouped extends Mage_Catalog_Block_Product_View_Type_Grouped
{
    protected function _toHtml()
    {
        if ('true' != (string)Mage::getConfig()->getNode('modules/Amasty_Xnotif/active'))
        {
            if(strpos($this->getTemplate(), "availability") <= 0){                
                $this->setTemplate('amasty/amstockstatus/grouped.phtml');
            }
        }
        return parent::_toHtml();
    }
    
    public function getStockStatus($product)
    {
        return $this->helper('amstockstatus')->getCustomStockStatusText($product);
    }
}